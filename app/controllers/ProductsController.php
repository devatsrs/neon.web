<?php
use Illuminate\Support\Facades\Input;
class ProductsController extends \BaseController {

    var $model = 'Product';
	/**
	 * Display a listing of the resource.
	 * GET /products
	 *
	 * @return Response

	  */

    public function ajax_datagrid($type) {
        $data = Input::all();
        $data['SearchStock']=!empty($data['SearchStock'])?$data['SearchStock']:'';
        $data['SearchDynamicFields']=!empty($data['SearchDynamicFields'])?$data['SearchDynamicFields']:'';
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = ['ProductID','title','Name','Code','BuyingPrice','Amount','Quantity','updated_at','Active','Description','Note','AppliedTo','LowStockLevel','ItemTypeID'];
        $sort_column = $columns[$data['iSortCol_0']];
        if($data['AppliedTo'] == ''){
            $data['AppliedTo'] = 'null';
        }

        $query = "call prc_getProducts (".$CompanyID.", '".$data['Name']."','".$data['Code']."','".$data['Active']."',".$data['AppliedTo'].", ".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."','".$data['ItemTypeID']."'";
        $Type =  Product::DYNAMIC_TYPE;
        $DynamicFields = $this->getDynamicFields($CompanyID,$Type);

        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1,"'.$data['SearchStock'].'","'.$data['SearchDynamicFields'].'")');
            if($DynamicFields['totalfields'] > 0){
                foreach ($excel_data as $key => $value) {
                    foreach ($DynamicFields['fields'] as $field) {
                        $DynamicFieldsID = $field->DynamicFieldsID;
                        $DynamicFieldsValues = DynamicFieldsValue::getDynamicColumnValuesByProductID($DynamicFieldsID,$excel_data[$key]->ProductID);
                        $FieldName = $field->FieldName;
                        if($DynamicFieldsValues->count() > 0){
                            foreach ($DynamicFieldsValues as $DynamicFieldsValue) {
                                $excel_data[$key]->$FieldName = $DynamicFieldsValue->FieldValue;
                            }
                        } else {
                            $excel_data[$key]->$FieldName = "";
                        }
                    }
                    unset($excel_data[$key]->ProductID);
                }
            }
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Item.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Item.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Item', function ($excel) use ($excel_data) {
                $excel->sheet('Item', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0,"'.$data['SearchStock'].'","'.$data['SearchDynamicFields'].'")';
        $data = DataTableSql::of($query,'sqlsrv2')->make(false);

        if($DynamicFields['totalfields'] > 0){
            for($i=0;$i<count($data['aaData']);$i++) {
                foreach ($DynamicFields['fields'] as $field) {
                    $DynamicFieldsID = $field->DynamicFieldsID;
                    $DynamicFieldsValues = DynamicFieldsValue::getDynamicColumnValuesByProductID($DynamicFieldsID,$data['aaData'][$i][0]);

                    if($DynamicFieldsValues->count() > 0){
                        foreach ($DynamicFieldsValues as $DynamicFieldsValue) {
                            $data['aaData'][$i]['DynamicFields'][$field->DynamicFieldsID] = $DynamicFieldsValue->FieldValue;
                        }
                    } else {
                        $data['aaData'][$i]['DynamicFields'][$field->DynamicFieldsID] = "";
                    }
                }
            }
        }
        return Response::json($data);
//        return DataTableSql::of($query,'sqlsrv2')->make();
    }


    public function index()
    {
        $id=0;
        $Type =  Product::DYNAMIC_TYPE;
        $companyID = User::get_companyID();
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        $DynamicFields = $this->getDynamicFields($companyID,$Type);
        $itemtypes 	= 	ItemType::getItemTypeDropdownList($companyID);
        return View::make('products.index', compact('id','gateway','DynamicFields','itemtypes'));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /products/create
	 *
	 * @return Response
	 */
    public function create(){
        $data = Input::all();
        $companyID = User::get_companyID();
        $roundplaces = $RoundChargesAmount = get_round_decimal_places();
        $data ["CompanyID"] = $companyID;
        $data['Active'] = isset($data['Active']) ? 1 : 0;
        $data["CreatedBy"] = User::get_user_full_name();
        $data["AppliedTo"] = empty($data['AppliedTo']) ? Product::Customer : $data['AppliedTo'];
        $data['Quantity']=($data['Quantity']!='')?$data['Quantity']:NULL;
        $data['LowStockLevel']=($data['LowStockLevel']!='')?$data['LowStockLevel']:NULL;
        $data['BuyingPrice']=empty($data['BuyingPrice'])?0:$data['BuyingPrice'];

        unset($data['ProductID']);
        unset($data['ProductClone']);

        if(isset($data['Quantity']) && $data['Quantity']!=''){
            $data['EnableStock']=1;
        }

        if(isset($data['DynamicFields'])) {
            $j=0;
            foreach($data['DynamicFields'] as $key => $value) {
                $key = (int) $key;
                if(isset($_FILES["DynamicFields"]["name"][$key])){
                    $dynamicImage = $_FILES["DynamicFields"]["name"][$key];
                    if($dynamicImage){
                        $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID)."/";
                        $fileUrl=$companyID."/dynamicfields/";
                        if (!file_exists($upload_path.$fileUrl)) {
                            mkdir($upload_path.$fileUrl, 0777, true);
                        }
                        $dynamicImage=time().$dynamicImage;
                        $success=move_uploaded_file($_FILES["DynamicFields"]["tmp_name"][$key],$upload_path.$fileUrl.$dynamicImage);
                        if($success){
                            $DynamicFields[$j]['FieldValue']=$fileUrl.$dynamicImage;
                        }else{
                            $DynamicFields[$j]['FieldValue']="";
                            return Response::json(array("status" => "failed", "message" => "Error: There was a problem uploading your file. Please try again."));
                        }
                    }
                }else{
                    if(!empty($data['DynamicFields'][$key])){
                        $DynamicFields[$j]['FieldValue'] = trim($data['DynamicFields'][$key]);
                    } else {
                        $DynamicFields[$j]['FieldValue'] = "";
                    }
                }

                $DynamicFields[$j]['DynamicFieldsID'] = $key;
                $DynamicFields[$j]['CompanyID'] = $companyID;
                $DynamicFields[$j]['created_at'] = date('Y-m-d H:i:s.000');
                $DynamicFields[$j]['created_by'] = User::get_user_full_name();
                $j++;
            }
            unset($data['DynamicFields']);
        }
        if(isset($data['hDynamicFields'])){
            unset($data['hDynamicFields']);
        }
        if(isset($DynamicFields)) {
            if ($error = DynamicFieldsValue::validate($DynamicFields)) {
                return $error;
            }
        }

        $rules = array(
            'CompanyID' => 'required',
            'ItemTypeID' => 'required',
            'Name' => 'required',
            'Amount' => 'required|numeric',
            'Description' => 'required',
            'Code' => 'required|unique:tblProduct,Code,NULL,ProductID,CompanyID,'.$data['CompanyID'].',AppliedTo,'.$data['AppliedTo'],
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv2');

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        $data["Amount"] = number_format(str_replace(",","",$data["Amount"]),$roundplaces,".","");

        //Product Upload Start
        $Attachment = !empty($data['Image']) ? 1 : 0;
        unset($data['Image']);

        if (Input::hasFile('Image') && $Attachment==1){
            $upload_path = CompanyConfiguration::get('UPLOAD_PATH');
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['PRODUCT_ATTACHMENTS'],'',$data['CompanyID']) ;
            $destinationPath = $upload_path . '/' . $amazonPath;
            $proof = Input::file('Image');

            $ext = $proof->getClientOriginalExtension();
            if (in_array(strtolower($ext), array('jpeg','png','jpg','gif'))) {

                $filename = rename_upload_file($destinationPath,$proof->getClientOriginalName());

                $proof->move($destinationPath,$filename);
                if(!AmazonS3::upload($destinationPath.$filename,$amazonPath,$data['CompanyID'])){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $data['Image'] = $amazonPath . $filename;
            }else{
                return Response::json(array("status" => "failed", "message" => "Please Upload file with given extensions."));
            }
        }else{
            unset($data['Image']);
        }

        //Product Upload End
        if ($product = Product::create($data)) {
            //Create Entry For Stock History
            if(isset($data['Quantity']) && $data['Quantity']!='' && intval($data['Quantity'])>0){
                $Reason='Stock Arrival With '.$data['Quantity'].' Quantity.';
                $historyData=array(
                    'CompanyID'=>$companyID,
                    'ProductID'=>$product->ProductID,
                    'Stock'=>intval($data['Quantity']),
                    'Quantity'=>intval($data['Quantity']),
                    'Reason'=>$Reason,
                    'created_at'=>date('Y-m-d H:i:s'),
                    'created_by'=>$data["CreatedBy"]
                );
                StockHistory::create($historyData);

            }
            if(isset($DynamicFields) && count($DynamicFields)>0) {
                for($k=0; $k<count($DynamicFields); $k++) {
                    $DynamicFields[$k]['ParentID'] = $product->ProductID;
                    DB::table('tblDynamicFieldsValue')->insert($DynamicFields[$k]);
                }
            }
            return Response::json(array("status" => "success", "message" => "Product Successfully Created",'newcreated'=>$product));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Product."));
        }
    }


	/**
	 * Update the specified resource in storage.
	 * PUT /products/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function update($id)
    {
        if( $id > 0 ) {
            $data = Input::all();
            $Product = Product::findOrFail($id);
            $oldQuantity=$Product->Quantity;
            $OldCode=$Product->Code;
            if($OldCode=='topup'){
                return Response::json(array("status" => "failed", "message" => "This Product Can not update."));
            }
            $user = User::get_user_full_name();
            $roundplaces = $RoundChargesAmount = get_round_decimal_places();

            $companyID = User::get_companyID();
            $data["CompanyID"] = $companyID;
            $data['Active'] = isset($data['Active']) ? 1 : 0;
            $data["ModifiedBy"] = $user;
            $data['Quantity']=($data['Quantity']!='')?$data['Quantity']:NULL;
            $data['LowStockLevel']=($data['LowStockLevel']!='')?$data['LowStockLevel']:NULL;
            $data['BuyingPrice']=empty($data['BuyingPrice'])?0:$data['BuyingPrice'];
            unset($data['ProductClone']);

            if(isset($data['Quantity']) && $data['Quantity']!=''){
                $data['EnableStock']=1;
            }else{
                $data['EnableStock']=0;
            }


            if(isset($data['DynamicFields']) && count($data['DynamicFields']) > 0) {
                $CompanyID = User::get_companyID();
                foreach ($data['DynamicFields'] as $key => $value) {
                    $key = (int) $key;

                    if(isset($_FILES["DynamicFields"]["name"][$key])){
                        $dynamicImage = $_FILES["DynamicFields"]["name"][$key];
                        if($dynamicImage){
                            $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID)."/";
                            $fileUrl=$companyID."/dynamicfields/";
                            if (!file_exists($upload_path.$fileUrl)) {
                                mkdir($upload_path.$fileUrl, 0777, true);
                            }
                            $dynamicImage=time().$dynamicImage;
                            $success=move_uploaded_file($_FILES["DynamicFields"]["tmp_name"][$key],$upload_path.$fileUrl.$dynamicImage);
                            if($success){
                                $DynamicFields['FieldValue']=$fileUrl.$dynamicImage;
                                $value=$fileUrl.$dynamicImage;
                            }else{
                                $DynamicFields['FieldValue']="";
                                return Response::json(array("status" => "failed", "message" => "Error: There was a problem uploading your file. Please try again."));
                            }
                        }
                    }else{
                        if(!empty($data['DynamicFields'][$key])){
                            $DynamicFields['FieldValue'] = $value;
                        } else {
                            $DynamicFields['FieldValue'] = "";
                        }
                    }
                    $isDynamicFields = DB::table('tblDynamicFieldsValue')
                        ->where('CompanyID',$CompanyID)
                        ->where('ParentID',$data['ProductID'])
                        ->where('DynamicFieldsID',$key);

                    if($isDynamicFields->count() > 0){
                        $isDynamicFields = $isDynamicFields->first();

                        $DynamicFields['DynamicFieldsID'] = $key;
                        //$DynamicFields['FieldValue'] = $value;
                        $DynamicFields['DynamicFieldsValueID'] = $isDynamicFields->DynamicFieldsValueID;

                        if($error = DynamicFieldsValue::validateOnUpdate($DynamicFields)){
                            return $error;
                        }

                        $getdynamicField=DynamicFields::where('DynamicFieldsID',$key)->get();
                        if($getdynamicField[0]->FieldDomType=='file' && $value==''){

                        }else {
                            DynamicFieldsValue::where('CompanyID', $CompanyID)
                                ->where('ParentID', $data['ProductID'])
                                ->where('DynamicFieldsID', $key)
                                ->update(['FieldValue' => $value, 'updated_at' => date('Y-m-d H:i:s.000'), 'updated_by' => $user]);
                        }
                    } else {
                        $DynamicFields['CompanyID'] = $companyID;
                        $DynamicFields['ParentID'] = $data['ProductID'];
                        $DynamicFields['DynamicFieldsID'] = $key;
                        $DynamicFields['FieldValue'] = $value;
                        $DynamicFields['DynamicFieldsValueID'] = 'NULL';
                        $DynamicFields['created_at'] = date('Y-m-d H:i:s.000');
                        $DynamicFields['created_by'] = $user;
                        $DynamicFields['updated_at'] = date('Y-m-d H:i:s.000');
                        $DynamicFields['updated_by'] = $user;

                        if($error = DynamicFieldsValue::validateOnUpdate($DynamicFields)){
                            return $error;
                        }

                        DynamicFieldsValue::insert($DynamicFields);
                    }
                }
                unset($data['DynamicFields']);
            }

            $rules = array(
                'CompanyID' => 'required',
                'ItemTypeID' => 'required',
                'Name' => 'required',
                'Amount' => 'required|numeric',
                'Description' => 'required',
                'Code' => 'required|unique:tblProduct,Code,'.$id.',ProductID,CompanyID,'.$data['CompanyID'].',AppliedTo,'.$data['AppliedTo'],
            );
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv2');

            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            $data["Amount"] = number_format(str_replace(",","",$data["Amount"]),$roundplaces,".","");
            if(isset($data['hDynamicFields'])){
                unset($data['hDynamicFields']);
            }

            //Product Upload Start
            $Attachment = !empty($data['Image']) ? 1 : 0;
            unset($data['Image']);

            if (Input::hasFile('Image') && $Attachment==1){
                $upload_path = CompanyConfiguration::get('UPLOAD_PATH');
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['PRODUCT_ATTACHMENTS'],'',$data['CompanyID']) ;
                $destinationPath = $upload_path . '/' . $amazonPath;
                $proof = Input::file('Image');

                $ext = $proof->getClientOriginalExtension();
                if (in_array(strtolower($ext), array('jpeg','png','jpg','gif'))) {

                    $filename = rename_upload_file($destinationPath,$proof->getClientOriginalName());

                    $proof->move($destinationPath,$filename);
                    if(!AmazonS3::upload($destinationPath.$filename,$amazonPath,$data['CompanyID'])){
                        return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                    }
                    $data['Image'] = $amazonPath . $filename;

                }else{
                    return Response::json(array("status" => "failed", "message" => "Please Upload file with given extensions."));
                }
            }else{
                unset($data['Image']);
            }

            //Product Upload End
            //print_r($data);exit();
            if ($Product->update($data)) {
                if( !empty($data['Quantity']) && $oldQuantity!=$data['Quantity']){
                    $Reason='Stock Received – qty '.$data['Quantity'];
                    $historyData=array(
                        'CompanyID'=>$companyID,
                        'ProductID'=>$id,
                        'Stock'=>intval($data['Quantity']),
                        'Quantity'=>intval($data['Quantity']),
                        'Reason'=>$Reason,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'created_by'=>$data["ModifiedBy"]
                    );
                    StockHistory::create($historyData);
                }
                return Response::json(array("status" => "success", "message" => "Product Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Product."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Product."));
        }
    }

	/**
	 * Remove the specified resource from storage.
	 * DELETE /products/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function delete($id) {
        if( intval($id) > 0){
            if(!Product::checkForeignKeyById($id)) {
                try {
                    $result = Product::find($id);
                    if(!empty($result->Image)){
                        AmazonS3::delete($result->Image);
                    }
                    $result->delete();
                    if ($result) {
                        $Type =  Product::DYNAMIC_TYPE;
                        $companyID = User::get_companyID();
                        $action = "delete";
                        $DynamicFields = $this->getDynamicFields($companyID,$Type,$action);
                        if($DynamicFields['totalfields'] > 0){
                            $DynamicFieldsIDs = array();
                            foreach ($DynamicFields['fields'] as $field) {
                                $DynamicFieldsIDs[] = $field->DynamicFieldsID;
                            }
                            //Image Delete
                            $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$companyID)."/";
                            $getDynamicValues=DynamicFieldsValue::where('ParentID',$id)->get();
                            if($getDynamicValues){
                                foreach($getDynamicValues as $key =>$val){
                                    if (file_exists($upload_path.$val->FieldValue) && $val->FieldValue!='') {
                                        unlink($upload_path.$val->FieldValue);
                                    }
                                }
                            }
                           // DynamicFieldsValue::deleteDynamicValuesByProductID($companyID,$id,$DynamicFieldsIDs);
                            Log::info("delete DynamicFieldValue ProductID=".$id);
                            DynamicFieldsValue::deleteDynamicValuesByProductID($companyID,$id);
                        }
                        Log::info("==Delete StockHistory productId=".$id);
                        StockHistory::where('ProductID',$id)->delete();
                        return Response::json(array("status" => "success", "message" => "Product Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Product."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Product is in Use, You cant delete this Product."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Product is in Use, You cant delete this Product."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Product is in Use, You cant delete this Product."));
        }
    }

    /**
     * Get product Field Value
     */
    /*public function get($id,$field){
        if($id>0 && !empty($field)){
            return json_encode(Product::where(["ProductID"=>$id])->pluck($field));
        }
        return json_encode('');
    }*/

    /**
     * Show the form for uploading items.
     * GET /products/upload
     *
     * @return View
     */
    public function upload(){
        $Type =  Product::DYNAMIC_TYPE;
        $CompanyID = User::get_companyID();
        $UploadTemplate = FileUploadTemplate::getTemplateIDList(FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_ITEM));
        $DynamicFields = $this->getDynamicFields($CompanyID,$Type);

        return View::make('products.upload',compact('UploadTemplate','DynamicFields'));
    }

    /**
     * @return mixed
     */
    public function check_upload()
    {
        try {
            $data = Input::all();
            $rules = array(
//                'Authentication' => 'required',
            );
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if (Input::hasFile('excel')) {
                $upload_path = CompanyConfiguration::get('TEMP_PATH');
                $excel = Input::file('excel');
                $ext = $excel->getClientOriginalExtension();
                if (in_array(strtolower($ext), array("csv", "xls", "xlsx"))) {
                    $file_name = GUID::generate() . '.' . $excel->getClientOriginalExtension();
                    $excel->move($upload_path, $file_name);
                    $file_name = $upload_path . '/' . $file_name;
                } else {
                    return Response::json(array("status" => "failed", "message" => "Please select excel or csv file."));
                }
            } else if (isset($data['TemplateFile'])) {
                $file_name = $data['TemplateFile'];
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select a file."));
            }
            if ($data['FileUploadTemplateID'] > 0) {
                $FileUploadTemplate = FileUploadTemplate::find($data['FileUploadTemplateID']);
                $options = json_decode($FileUploadTemplate->Options, true);
                $data['Delimiter'] = $options['option']['Delimiter'];
                $data['Enclosure'] = $options['option']['Enclosure'];
                $data['Escape'] = $options['option']['Escape'];
                $data['Firstrow'] = $options['option']['Firstrow'];
            }

            if (!empty($file_name)) {
                $grid = getFileContent($file_name, $data);
                $grid['tempfilename'] = $file_name;
                $grid['filename'] = $file_name;
                if (!empty($FileUploadTemplate)) {
                    $grid['FileUploadTemplate'] = json_decode(json_encode($FileUploadTemplate), true);
                    $grid['FileUploadTemplate']['Options'] = json_decode($FileUploadTemplate->Options, true);
                }
                return Response::json(array("status" => "success", "message" => "file uploaded", "data" => $grid));
            }
        } catch (Exception $e) {
            return Response::json(array("status" => "failed", "message" => $e->getMessage()));
        }
    }

    /**
     * @return mixed
     */
    public function ajaxfilegrid(){
        try {
            $data = Input::all();
            $file_name = $data['TemplateFile'];
            $grid = getFileContent($file_name, $data);
            if ($data['FileUploadTemplateID'] > 0) {
                $FileUploadTemplate = FileUploadTemplate::find($data['FileUploadTemplateID']);
                $grid['FileUploadTemplate'] = json_decode(json_encode($FileUploadTemplate), true);
                //$grid['FileUploadTemplate']['Options'] = json_decode($FileUploadTemplate->Options,true);
            }
            $grid['FileUploadTemplate']['Options'] = array();
            $grid['FileUploadTemplate']['Options']['option'] = $data['option'];
            $grid['FileUploadTemplate']['Options']['selection'] = $data['selection'];
            return Response::json(array("status" => "success", "message" => "data refreshed", "data" => $grid));
        }catch (Exception $e){
            return Response::json(array("status" => "failed", "message" => $e->getMessage()));
        }
    }

    /**
     * @return mixed
     */
    public function storeTemplate() {
        $data = json_decode(str_replace('Skip loading','',json_encode(Input::all(),true)),true);//Input::all();
        $CompanyID = User::get_companyID();
        if(isset($data['FileUploadTemplateID']) && $data['FileUploadTemplateID']>0) {
            $rules = array('TemplateName' => 'required|unique:tblFileUploadTemplate,Title,'.$data['FileUploadTemplateID'].',FileUploadTemplateID',
                'TemplateFile' => 'required',
            );
        }else{
            $rules = array('TemplateName' => 'required|unique:tblFileUploadTemplate,Title,NULL,FileUploadTemplateID',
                'TemplateFile' => 'required',
            );
        }
        if(!empty($data['selection']['Name'])){
            $data['Name'] = $data['selection']['Name'];
        }else{
            $rules['Name'] = 'required';
        }
        if(!empty($data['selection']['Code'])){
            $data['Code'] = $data['selection']['Code'];
        }else{
            $rules['Code'] = 'required';
        }
        if(!empty($data['selection']['Description'])){
            $data['Description'] = $data['selection']['Description'];
        }else{
            $rules['Description'] = 'required';
        }
        if(!empty($data['selection']['Amount'])){
            $data['Amount'] = $data['selection']['Amount'];
        }else{
            $rules['Amount'] = 'required';
        }
        if(!empty($data['selection']['Note'])){
            $data['Note'] = $data['selection']['Note'];
        }else{
            $data['Note'] = '';
        }
        if(!empty($data['selection']['AppliedTo'])){
            $data['AppliedTo'] = $data['selection']['AppliedTo'];
        }else{
            $data['AppliedTo'] = '';
        }
        /*if(!empty($data['selection']['Active'])){
            $data['Active'] = $data['selection']['Active'];
        }else{
            $rules['Active'] = 'required';
        }*/
        /*if(!empty($data['selection']['BarCode'])){
            $data['BarCode'] = $data['selection']['BarCode'];
        }else{
            $rules['BarCode'] = 'required';
        }*/

        $DynamicFields = $this->getDynamicFields($CompanyID, Product::DYNAMIC_TYPE);
        if($DynamicFields['totalfields'] > 0) {
            foreach ($DynamicFields['fields'] as $dynamicField) {
                if(!empty($data['selection']['DynamicFields-'.$dynamicField->DynamicFieldsID])) {
                    $data['DynamicFields-'.$dynamicField->DynamicFieldsID] = $data['selection']['DynamicFields-'.$dynamicField->DynamicFieldsID];
                } else {
                    $data['DynamicFields-'.$dynamicField->DynamicFieldsID] = "";
                }
            }
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $file_name = basename($data['TemplateFile']);

        $temp_path = CompanyConfiguration::get('TEMP_PATH').'/';
        $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['TEMPLATE_FILE']);
        $amazonItemPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ITEM_UPLOAD']);
        $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
        $destinationItemPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonItemPath;
        copy($temp_path.$file_name,$destinationPath.$file_name);
        copy($temp_path.$file_name,$destinationItemPath.$file_name);
        if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
            return Response::json(array("status" => "failed", "message" => "Failed to upload template sample file."));
        }
        $save = ['CompanyID'=>$CompanyID,'Title'=>$data['TemplateName'],'TemplateFile'=>$amazonPath.$file_name];
        $save['created_by'] = User::get_user_full_name();
        $option["option"]= $data['option'];//['Delimiter'=>$data['Delimiter'],'Enclosure'=>$data['Enclosure'],'Escape'=>$data['Escape'],'Firstrow'=>$data['Firstrow']];
        $option["selection"] = filterArrayRemoveNewLines($data['selection']);//['connect_time'=>$data['connect_time'],'disconnect_time'=>$data['disconnect_time'],'billed_duration'=>$data['billed_duration'],'duration'=>$data['duration'],'cld'=>$data['cld'],'cli'=>$data['cli'],'Account'=>$data['Account'],'cost'=>$data['cost']];
        $save['Options'] = str_replace('Skip loading','',json_encode($option));//json_encode($option);
        $save['FileUploadTemplateTypeID'] = FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_ITEM);
        if(isset($data['FileUploadTemplateID']) && $data['FileUploadTemplateID']>0) {
            $template = FileUploadTemplate::find($data['FileUploadTemplateID']);
            $template->update($save);
        }else {/**/
            $template = FileUploadTemplate::create($save);
        }
        if ($template) {
            //Inserting Job Log
            $data['FileUploadTemplateID'] = $template->FileUploadTemplateID;
            $fullPath = $amazonPath . $file_name; //$destinationPath . $file_name;
            $data['full_path'] = $fullPath;
            $jobType = JobType::where(["Code" => 'IU'])->get(["JobTypeID", "Title"]);
            $jobStatus = JobStatus::where(["Code" => "P"])->get(["JobStatusID"]);
            $histdata['CompanyID']= $jobdata["CompanyID"] = $CompanyID;
            $jobdata["JobTypeID"] = isset($jobType[0]->JobTypeID) ? $jobType[0]->JobTypeID : '';
            $jobdata["JobStatusID"] = isset($jobStatus[0]->JobStatusID) ? $jobStatus[0]->JobStatusID : '';
            $jobdata["JobLoggedUserID"] = User::get_userID();
            $jobdata["Title"] =  (isset($jobType[0]->Title) ? $jobType[0]->Title : '');
            $jobdata["Description"] = isset($jobType[0]->Title) ? $jobType[0]->Title : '';
            $histdata['CreatedBy']= $jobdata["CreatedBy"] = User::get_user_full_name();
            $jobdata["Options"] = json_encode($data);
            $jobdata["created_at"] = date('Y-m-d H:i:s');
            $jobdata["updated_at"] = date('Y-m-d H:i:s');
            $JobID = Job::insertGetId($jobdata);
            /*$histdata['CompanyGatewayID'] = $data['CompanyGatewayID'];
            $histdata['StartDate'] = $data['StartDate'];
            $histdata['EndDate'] = $data['EndDate'];
            $histdata['created_at'] = date('Y-m-d H:i:s');

            CDRUploadHistory::insert($histdata);*/


            $jobfiledata["JobID"] = $JobID;
            $jobfiledata["FileName"] = basename($fullPath);
            $jobfiledata["FilePath"] = $fullPath;
            $jobfiledata["HttpPath"] = 0;
            $jobfiledata["Options"] = json_encode($data);
            $jobfiledata["CreatedBy"] = User::get_user_full_name();
            $jobfiledata["updated_at"] = date('Y-m-d H:i:s');
            $JobFileID = JobFile::insertGetId($jobfiledata);
            return Response::json(array("status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed."));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Template."));
        }
    }

    /**
     * @param $CompanyID
     * @param $Type
     * @return mixed
     */

    public function getDynamicFields($CompanyID, $Type=Product::DYNAMIC_TYPE, $action=''){

        if($action && $action == 'delete') {
            $dynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->get();
        } else {
            $dynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->where('Status',1)->get();
        }

        $dynamicFields['totalfields'] = count($dynamicFields['fields']);

        return $dynamicFields;
    }

    /**
     * @return mixed
     */
    public static function getDynamicFieldsIDBySlug() {
        return DB::table('tblDynamicFields')->where('FieldSlug',DynamicFieldsValue::BARCODE_SLUG)->pluck('DynamicFieldsID');
    }

    /**
     * @param $BarCode
     * @return mixed
     */
    public function getProductByBarCode($BarCode) {
        $ColumnID = $this->getDynamicFieldsIDBySlug();

        if($ColumnID) {
            $product = DB::connection('sqlsrv2')->select("CALL  prc_getProductByBarCode ('" . $BarCode . "','" . $ColumnID . "')");

            if($product) {
                return Response::json(array("status" => "success", "message" => "Product found.", "data" => $product[0]));
            } else {
                return Response::json(array("status" => "failed", "message" => "Product not found."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "BarCode column not found."));
        }
    }

    public function download_sample_excel_file(){
        $filePath =  public_path() .'/uploads/sample_upload/ItemUploadSample.csv';
        download_file($filePath);
    }

    function UpdateBulkProductStatus()
    {
        $data 		= Input::all();
        $CompanyID 	= User::get_companyID();
        $UserName   = User::get_user_full_name();

        if(isset($data['type_active_deactive']) && $data['type_active_deactive']!='')
        {
            if($data['type_active_deactive']=='active'){
                $data['status_set']  = 1;
            }else if($data['type_active_deactive']=='deactive'){
                $data['status_set']  = 0;
            }else{
                return Response::json(array("status" => "failed", "message" => "No item status selected"));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "No item status selected"));
        }

        if($data['criteria_ac']=='criteria'){ //all item checkbox checked
            $userID = User::get_userID();

            if(!isset($data['Active']) || $data['Active'] == '') {
                $data['Active'] = 9;
            } else {
                $data['Active'] = (int) $data['Active'];
            }

            if($data['AppliedTo'] == ''){
                $data['AppliedTo'] = 'null';
            }

            $query = "call prc_UpdateProductsStatus (".$CompanyID.",'".$UserName."','".$data['Name']."','".$data['Code']."',".$data['Active'].",".$data['AppliedTo'].",".$data['status_set'].")";

            $result = DB::connection('sqlsrv2')->select($query);
            return Response::json(array("status" => "success", "message" => "Items Status Updated"));
        }

        if($data['criteria_ac']=='selected'){ //selceted ids from current page
            if(isset($data['SelectedIDs']) && count($data['SelectedIDs'])>0){
//                foreach($data['SelectedIDs'] as $SelectedID){
                    Product::whereIn('ProductID',$data['SelectedIDs'])->where('Active','!=',$data['status_set'])->update(["Active"=>intval($data['status_set'])]);
//                    Product::find($SelectedID)->where('Active','!=',$data['status_set'])->update(["Active"=>intval($data['status_set']),'ModifiedBy'=>$UserName,'updated_at'=>date('Y-m-d H:i:s')]);
//                }
                return Response::json(array("status" => "success", "message" => "Items Status Updated"));
            }else{
                return Response::json(array("status" => "failed", "message" => "No Items selected"));
            }

        }


    }

    public function change_type($itemTypeid){
        if($itemTypeid > 0){
            $Type =  Product::DYNAMIC_TYPE;
            $data=Input::all();
            $CompanyID = User::get_companyID();
            $DynamicFields['fields'] = DynamicFields::where('Type',$Type)->where('CompanyID',$CompanyID)->where('ItemTypeID',$itemTypeid)->where('Status','1')->orderByRaw('case FieldOrder when 0 then 2 else 1 end, FieldOrder')->get();
            $DynamicFields['totalfields'] = count($DynamicFields['fields']);
            if(count($DynamicFields) > 0 ){
               return View::make('products.ajax_dynamicfield_html',compact('DynamicFields','data'));
            }
        }
    }

    public function download_dynamicfield($id){
        if($id > 0){
            $get=DynamicFieldsValue::where('DynamicFieldsValueID',$id)->get();
            if($get){
                $CompanyID = User::get_companyID();
                $upload_path = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID)."/";
                $FieldValue=$get[0]->FieldValue;
                $FilePath=$upload_path.$FieldValue;
                if(file_exists($FilePath)){
                    download_file($FilePath);
                }
            }

        }
    }

    public function  download_attachment($id){
        $FileName = Product::where(["ProductID"=>$id])->pluck('Image');
        $FilePath =  AmazonS3::preSignedUrl($FileName);
        download_file($FilePath);

    }

    public function ajax_datagrid_total()
    {
        $data 						 = 	Input::all();
        $data['SearchStock']=!empty($data['SearchStock'])?$data['SearchStock']:'';
        $data['SearchDynamicFields']=!empty($data['SearchDynamicFields'])?$data['SearchDynamicFields']:'';
        if($data['AppliedTo'] == ''){
            $data['AppliedTo'] = 'null';
        }
        $data['iDisplayStart'] 		 =	0;
        $data['iDisplayStart'] 		+=	1;
        $data['iSortCol_0']			 =  0;
        $data['sSortDir_0']			 =  'desc';
        $companyID 					 =  User::get_companyID();
        $columns = ['ProductID','title','Name','Code','BuyingPrice','Amount','Quantity','updated_at','Active','Description','Note','AppliedTo','LowStockLevel','ItemTypeID'];
        $sort_column 				 =  $columns[$data['iSortCol_0']];

        $query = "call prc_getProducts (".$companyID.", '".$data['Name']."','".$data['Code']."','".$data['Active']."',".$data['AppliedTo'].", ".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."','".$data['ItemTypeID']."'";

        if(isset($data['Export']) && $data['Export'] == 1)
        {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1,"'.$data['SearchStock'].'","'.$data['SearchDynamicFields'].'")');
            $excel_data = json_decode(json_encode($excel_data),true);
            Excel::create('Invoice', function ($excel) use ($excel_data)
            {
                $excel->sheet('Invoice', function ($sheet) use ($excel_data)
                {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');
        }

        $query .=',0,"'.$data['SearchStock'].'","'.$data['SearchDynamicFields'].'")';

        $result   = DataTableSql::of($query,'sqlsrv2')->getProcResult(array('ResultCurrentPage','Total_grand'));

        $result2  = $result['data']['Total_grand'][0]->total_grand;
        $result4  = array(
            "total_grand"=>$result2,
        );

        return json_encode($result4,JSON_NUMERIC_CHECK);
    }


}


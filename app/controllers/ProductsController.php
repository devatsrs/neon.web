<?php

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
        $CompanyID = User::get_companyID();
        $data['iDisplayStart'] +=1;
        $columns = ['Name','Code','Amount','updated_at','Active'];
        $sort_column = $columns[$data['iSortCol_0']];
        $query = "call prc_getProducts (".$CompanyID.", '".$data['Name']."','".$data['Code']."','".$data['Active']."', ".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";
        if(isset($data['Export']) && $data['Export'] == 1) {
            $excel_data  = DB::connection('sqlsrv2')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOADPATH') .'/Item.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOADPATH') .'/Item.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }
            /*Excel::create('Item', function ($excel) use ($excel_data) {
                $excel->sheet('Item', function ($sheet) use ($excel_data) {
                    $sheet->fromArray($excel_data);
                });
            })->download('xls');*/
        }
        $query .=',0)';
        return DataTableSql::of($query,'sqlsrv2')->make();
    }


    public function index()
    {
        $id=0;
        $companyID = User::get_companyID();
        $gateway = CompanyGateway::getCompanyGatewayIdList();
        return View::make('products.index', compact('id','gateway'));
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

        unset($data['ProductID']);
        if($error = Product::validate($data)){
            return $error;
        }
        $data["Amount"] = number_format(str_replace(",","",$data["Amount"]),$roundplaces,".","");
        if ($product = Product::create($data)) {
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
            $roundplaces = $RoundChargesAmount = get_round_decimal_places();

            $companyID = User::get_companyID();
            $data["CompanyID"] = $companyID;
            $data['Active'] = isset($data['Active']) ? 1 : 0;
            $data["ModifiedBy"] = User::get_user_full_name();

            if($error = Product::validate($data)){
                return $error;
            }
            $data["Amount"] = number_format(str_replace(",","",$data["Amount"]),$roundplaces,".","");
            if ($Product->update($data)) {
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
                    $result = Product::find($id)->delete();
                    if ($result) {
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

}
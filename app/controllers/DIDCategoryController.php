<?php

class DIDCategoryController extends \BaseController {

    var $model = 'DIDCategory';
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
        $columns = ['DIDCategoryID','CategoryName','updated_at'];
        $sort_column = $columns[$data['iSortCol_0']];

        $query = "call prc_getDIDCategory (".$CompanyID.", '".$data['CategoryName']."',".( ceil($data['iDisplayStart']/$data['iDisplayLength']) )." ,".$data['iDisplayLength'].",'".$sort_column."','".$data['sSortDir_0']."'";

        if(isset($data['Export']) && $data['Export'] == 1) {

            $excel_data  = DB::connection('sqlsrv')->select($query.',1)');
            $excel_data = json_decode(json_encode($excel_data),true);
            if($type=='csv'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AccessCategory.csv';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_csv($excel_data);
            }elseif($type=='xlsx'){
                $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/AccessCategory.xls';
                $NeonExcel = new NeonExcelIO($file_path);
                $NeonExcel->download_excel($excel_data);
            }

        }
        $query .=',0)';
        $data = DataTableSql::of($query,'sqlsrv')->make(false);

        return Response::json($data);

    }


    public function index()
    {
        return View::make('DIDCategory.index');
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

        $data ["CompanyID"] = $companyID;
        $data["created_by"] = User::get_user_full_name();
        unset($data['DIDCategoryID']);

        $rules = array(
            'CompanyID' => 'required',
            'CategoryName' => 'required',
        );

        $verifier = App::make('validation.presence');
        $verifier->setConnection('sqlsrv');

        $validator = Validator::make($data, $rules);
        $validator->setPresenceVerifier($verifier);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $checkduplicate=DIDCategory::where('CategoryName',$data['CategoryName'])->get()->count();
        if($checkduplicate > 0){
            return Response::json(array("status" => "failed", "message" => "Category Name Already Exists."));
        }

        if ($itemtype = DIDCategory::create($data)) {
            return Response::json(array("status" => "success", "message" => "Access Category Successfully Created",'newcreated'=>$itemtype));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Access Category."));
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
            $itemtype = DIDCategory::findOrFail($id);

            $companyID = User::get_companyID();
            $data["CompanyID"] = $companyID;
            $user = User::get_user_full_name();
            $data["updated_by"] = $user;

            $rules = array(
                'CompanyID' => 'required',
                'CategoryName' => 'required',
            );
            $verifier = App::make('validation.presence');
            $verifier->setConnection('sqlsrv');

            $validator = Validator::make($data, $rules);
            $validator->setPresenceVerifier($verifier);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if ($itemtype->update($data)) {
                return Response::json(array("status" => "success", "message" => "Access Category Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Access Category."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Access Category."));
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
            if(DIDCategory::checkForeignKeyById($id)) {
                try {
                    $result = DIDCategory::find($id)->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Access Category Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Access Category."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Problem Deleting Access Category."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Access Category is in Use."));
            }
        }else{
            return Response::json(array("status" => "failed", "message" => "Access Category not Found."));
        }
    }


}
<?php

class ServerInfoController extends \BaseController {

    var $model = 'ServerInfo';
	/**
	 * Display a listing of the resource.
	 * GET /products
	 *
	 * @return Response

	  */

    public function ajax_getdata() {
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $UserActilead = UserActivity::UserActivitySaved($data,'View','Server Monitor');
        $ServerInfo = ServerInfo::where(['CompanyID'=>$CompanyID])->select(['ServerInfoID','ServerInfoTitle','ServerInfoUrl'])->get();
        return Response::json(array("status" => "success", "data" => $ServerInfo));
    }



    public function index()
    {
        return View::make('serverinfo.index', compact('id','gateway'));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /products/create
	 *
	 * @return Response
	 */
    public function store(){

        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $rules = ['ServerInfoTitle'=>'required',
                    'ServerInfoUrl'=>'required|url'
        ];
        $message = ['ServerInfoTitle.required'=>'Name is required field',
                    'ServerInfoUrl.required'=>'URL is required field',
                    'ServerInfoUrl.url'=>'Add valid URL',];
        $validator = Validator::make($data, $rules ,$message);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['ServerInfoID']);
        if (ServerInfo::create($data)) {
            $UserActilead = UserActivity::UserActivitySaved($data,'Add','Server Monitor',$data['ServerInfoTitle']);
            return Response::json(array("status" => "success", "message" => "Server info Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Server info."));
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
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            $rules = ['ServerInfoTitle'=>'required',
                'ServerInfoUrl'=>'required|url'
            ];
            $message = ['ServerInfoTitle.required'=>'Name is required field',
                'ServerInfoUrl.required'=>'URL is required field',
                'ServerInfoUrl.url'=>'Add valid URL',];
            $validator = Validator::make($data, $rules ,$message);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['ServerInfoID']);
            if (ServerInfo::where(['ServerInfoID'=>$id])->update($data)) {
                $UserActilead = UserActivity::UserActivitySaved($data,'Edit','Server Monitor',$data['ServerInfoTitle']);
                return Response::json(array("status" => "success", "message" => "Server info Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating Server info."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Server info."));
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
        $data['id'] = $id;
        if($id>0){
                try {
                    $result = ServerInfo::where(['ServerInfoID'=>$id])->delete();
                    if ($result) {
                        $UserActilead = UserActivity::UserActivitySaved($data,'Delete','Server Monitor');
                        return Response::json(array("status" => "success", "message" => "Server Info Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Server Info."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
                }
        }else{
            return Response::json(array("status" => "failed", "message" => "Problem Deleting Server Info."));
        }
    }

}
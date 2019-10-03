<?php

class NodesController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /nodes
	 *
	 * @return Response
	 */

	public function ajax_datagrid(){
		$data = Input::all();
        $Nodes = Nodes::select('ServerName','ServerIP','LocalIP','Username','Status','ServerID','Type','ServerStatus','MaintananceStatus');
		$Status = $data['status'] == 'true' ? 1 : 0;

        if(!empty($data['ServerName'])){
            $Nodes->where('tblNode.ServerName','like','%'.$data['ServerName'].'%');
		}
		
		if(!empty($data['ServerIP'])){
            $Nodes->where('tblNode.ServerIP','like','%'.$data['ServerIP'].'%');
		}
		
		$Nodes->where("tblNode.Status" , $Status);
		
		return Datatables::of($Nodes)->make();
	}
	 
	public function index()
	{
		return View::make('nodes.index');
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /nodes/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /nodes
	 *
	 * @return Response
	 */
	public function store()
	{
		$data = Input::all();

		if(isset($data['status'])){
			$data['status'] = 1;
		}else{
			$data['status'] = 0;
		}

		if(isset($data['MaintananceStatus'])){
			$data['MaintananceStatus'] = 1;
		}else{
			$data['MaintananceStatus'] = 0;
		}
		

		 Nodes::$rules["ServerName"] = 'required|unique:tblNode,ServerName,NULL,ServerID';

		 $validator = Validator::make($data, Nodes::$rules + array('Password' => 'required'));

		 if ($validator->fails()) {
			 return json_validator_response($validator);
		 }

		 $data['Password'] = Crypt::encrypt($data['Password']);
		 
		 $validServerIp = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $data['ServerIP']);
		 $validLocalIp = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $data['LocalIP']);
		 
		 if(!$validServerIp){
			return  Response::json(array("status" => "failed", "message" => "Server IP Format Is Invalid"));
		 }
		 if(!$validLocalIp){
			return  Response::json(array("status" => "failed", "message" => "Server IP Format Is Invalid"));
		 }

		 if($Package = Nodes::create($data)){
			 return  Response::json(array("status" => "success", "message" => "Node Successfully Created"));
		 } else {
			 return  Response::json(array("status" => "failed", "message" => "Problem Creating Package."));
		 }
	}

	/**
	 * Display the specified resource.
	 * GET /nodes/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /nodes/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /nodes/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{

		$data = Input::all();
        if(isset($data['status'])){
            $data['status'] = 1;
        }else{
            $data['status'] = 0;
		}
		
		if(isset($data['MaintananceStatus'])){
			$data['MaintananceStatus'] = 1;
		}else{
			$data['MaintananceStatus'] = 0;
		}
		
		

        $CompanyID = User::get_companyID();
        $Node = Nodes::find($id);
		Nodes::$rules["ServerName"] = 'required|unique:tblNode,ServerName,'.$id.',ServerID';
		Nodes::$rules["ServerIP"] = 'required|unique:tblNode,ServerIP,'.$id.',ServerID';
		Nodes::$rules["LocalIP"] = 'required|unique:tblNode,LocalIP,'.$id.',ServerID';
		Nodes::$rules["Username"] = 'required|unique:tblNode,ServerIP,'.$id.',ServerID';


        $validator = Validator::make($data, Nodes::$rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
		}

		if($data['Password'] != ''){

			$data['Password'] = Crypt::encrypt($data['Password']);
		}else{
			unset($data['Password']);
		}

		
		$validServerIp = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $data['ServerIP']);
		$validLocalIp = preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $data['LocalIP']);
		
		if(!$validServerIp){
		   return  Response::json(array("status" => "failed", "message" => "Server IP Format Is Invalid"));
		}
		if(!$validLocalIp){
		   return  Response::json(array("status" => "failed", "message" => "Server IP Format Is Invalid"));
		}

        if($Node->update($data)){
            return  Response::json(array("status" => "success", "message" => "Node Successfully Updated"));
        } else {
            return  Response::json(array("status" => "failed", "message" => "Problem Updating Package."));
        }
	}

	public function export($type){

		$data = Input::all();
        $Nodes = Nodes::select('ServerName','ServerIP','Status');
		$Status = $data['status'] == 'true' ? 1 : 0;

        if(!empty($data['ServerName'])){
            $Nodes->where('tblNode.ServerName','like','%'.$data['ServerName'].'%');
		}
		
		if(!empty($data['ServerIP'])){
            $Nodes->where('tblNode.ServerIP','like','%'.$data['ServerIP'].'%');
		}
		
		$Nodes->where("tblNode.Status" , $Status);

        $NodeExports = $Nodes->get();

        $NodeExports = json_decode(json_encode($NodeExports),true);
        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Nodes.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($NodeExports);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Nodes.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($NodeExports);
        }

    }

	/**
	 * Remove the specified resource from storage.
	 * DELETE /nodes/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function delete($id)
	{
		$result = Nodes::find($id)->delete();
		if ($result) {
			return Response::json(array("status" => "success", "message" => "Node Successfully Deleted"));
		} else {
			return Response::json(array("status" => "failed", "message" => "Problem Deleting Node."));
		}
		
	}

}
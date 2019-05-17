<?php

class ActivityFeedsController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /activityfeeds
	 *
	 * @return Response
	 */
	public function ajax_datagrid() {
		$data = Input::all();
		$Email_TemplateActilead = UserActivity::UserActivitySaved($data,'View','Activity');

		$ActivityFeeds = ActivityFeeds::select('created_by','created_at','Type','Action','TypeName','ActionValue','UserActivityID');
		
		if(isset($data['User']) && !empty($data['User'])){
			$ActivityFeeds->where('created_by', $data['User']);
		}
		if(isset($data['DateFrom']) && !empty($data['DateFrom']) && isset($data['DateTo']) && !empty($data['DateTo'])){
			$start_date = $data['DateFrom'];
			$end_date = $data['DateTo'];
			$ActivityFeeds->whereBetween('created_at',array($start_date,$end_date));
		}
		if(isset($data['Search']) && !empty($data['Search'])){
			$ActivityFeeds->where('ActionValue', 'like', '%' . $data['Search'] . '%');
		}
		$ExcelFile = json_decode(json_encode($ActivityFeeds),true);
		if(isset($data['Export']) && $data['Export'] == 1){
			$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/ActivityFeed.xls';
			$NeonExcel = new NeonExcelIO($file_path);
			$NeonExcel->download_excel($ExcelFile);
		}
        return Datatables::of($ActivityFeeds)->make();
	}
	

	public function index()
	{
		$users = array('' => 'All') + User::getUserIDListByName(0);
		return View::make('activityfeeds.index',compact('users'));
	}

	public function get_details($id){
		$activity = ActivityFeeds::where('UserActivityID',$id)->first();
		$actionvalue = $activity->ActionValue;
		$actionvalue = json_decode($actionvalue,true);

		if(!empty($actionvalue)){
            return Response::json($actionvalue);
        }else{
            return Response::json(array("status" => "failed", "message" => "Details Not Found!"));
        }
		
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /activityfeeds/create
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /activityfeeds
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 * GET /activityfeeds/{id}
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
	 * GET /activityfeeds/{id}/edit
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
	 * PUT /activityfeeds/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /activityfeeds/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
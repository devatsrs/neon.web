<?php

class OpportunityBoardColumnController extends \BaseController {

    var $model = 'CRMBoardColumn';
	/**
	 * Display a listing of the resource.
	 * GET /opportunityboardcolumn
	 *
	 * @return Response

	  */

    public function ajax_datacolumn($id){
        $response = NeonAPI::request('opportunityboardcolumn/'.$id.'/get_columns',[],false);
        return json_response_api($response,true,true,true);
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /opportunityboardcolumn/create
	 *
	 * @return Response
	 */
    public function create(){
        $data = Input::all();
        $response = NeonAPI::request('opportunityboardcolumn/add_column',$data);
        return json_response_api($response);
    }


	/**
	 * Update the specified resource in storage.
	 * PUT /opportunityboardcolumn/{id}/update
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function update($id)
    {
        $data = Input::all();
        $response = NeonAPI::request('opportunityboardcolumn/'.$id.'/update_column',$data);
        return json_response_api($response);
    }

    function updateColumnOrder($id){
        $data = Input::all();
        $response = NeonAPI::request('opportunityboardcolumn/'.$id.'/update_columnOrder',$data);
        return json_response_api($response);
    }
}
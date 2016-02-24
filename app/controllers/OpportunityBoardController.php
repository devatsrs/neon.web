<?php

class OpportunityBoardController extends \BaseController {

    var $model = 'OpportunityBoard';
	/**
	 * Display a listing of the resource.
	 * GET /Opportunity board
	 *
	 * @return Response

	  */

    public function ajax_datagrid(){
        $companyID = User::get_companyID();
        $data = Input::all();
        $select = ['OpportunityBoardName' ,'Status','CreatedBy','OpportunityBoardID'];
        $OpportunityBoard = OpportunityBoard::select($select)->where(['CompanyID'=>$companyID]);

        if($data['Active'] != '' ) {
            $OpportunityBoard->where('Status', $data['Active']);
        }
        if(trim($data['OpportunityBoardName']) != '') {
            $OpportunityBoard->where('OpportunityBoardName', 'like','%'.trim($data['OpportunityBoardName']).'%');
        }
        return Datatables::of($OpportunityBoard)->make();
    }


    public function index(){
        return View::make('opportunityboards.index', compact(''));
    }


    public function configure($id){
        $OpportunityBoard = OpportunityBoard::find($id);
        return View::make('opportunityboards.configure', compact('id','OpportunityBoard'));
    }

    public function manage($id){
        $OpportunityBoard = OpportunityBoard::find($id);
        $account_owners = User::getOwnerUsersbyRole();
        $leads = Lead::getLeadList();
        $boards = OpportunityBoard::getBoards();
        return View::make('opportunityboards.manage', compact('id','OpportunityBoard','account_owners','leads','boards'));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /Opportunityboard/create
	 *
	 * @return Response
	 */
    public function create(){

        $data = Input::all();
        $companyID = User::get_companyID();
        $data ["CompanyID"] = $companyID;
        $rules = array(
            'CompanyID' => 'required',
            'OpportunityBoardName' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data['Status'] = isset($data['Status']) ? 1 : 0;
        $data["CreatedBy"] = User::get_user_full_name();
        unset($data['OpportunityBoardID']);
        if (OpportunityBoard::create($data)) {
            return Response::json(array("status" => "success", "message" => "Opportunity Board Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Opportunity Board."));
        }
    }


	/**
	 * Update the specified resource in storage.
	 * PUT /Opportunityboard/{id}/update
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function update($id)
    {
        if( $id > 0 ) {
            $data = Input::all();
            $OpportunityBoard = OpportunityBoard::findOrFail($id);

            $companyID = User::get_companyID();
            $data["CompanyID"] = $companyID;
            $data['Status'] = isset($data['Status']) ? 1 : 0;
            $data["ModifiedBy"] = User::get_user_full_name();
            $rules = array(
                'CompanyID' => 'required',
                'OpportunityBoardName' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if ($OpportunityBoard->update($data)) {
                return Response::json(array("status" => "success", "message" => "OpportunityBoard Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating OpportunityBoard."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating OpportunityBoard."));
        }
    }
}
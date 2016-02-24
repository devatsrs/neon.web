<?php

class OpportunityBoardColumnController extends \BaseController {

    var $model = 'OpportunityBoardColumn';
	/**
	 * Display a listing of the resource.
	 * GET /opportunityboardcolumn
	 *
	 * @return Response

	  */

    public function ajax_datacolumn($id){

        $companyID = User::get_companyID();
        $data = Input::all();
        $select = ['OpportunityBoardColumnName' ,'Order','OpportunityBoardColumnID'];
        $result = OpportunityBoardColumn::select($select)->where(['CompanyID'=>$companyID,'OpportunityBoardID'=>$id])->orderBy('Order')->get();
        return json_encode($result);
    }

    public function index(){
        return View::make('Opportunityboards.Opportunityboardcolumn', compact(''));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /opportunityboardcolumn/create
	 *
	 * @return Response
	 */
    public function create(){

        $data = Input::all();
        $companyID = User::get_companyID();
        $count = OpportunityBoardColumn::where(['CompanyID'=>$companyID,'OpportunityBoardID'=>$data['OpportunityBoardID']])->count();
        $data ["CompanyID"] = $companyID;
        $rules = array(
            'CompanyID' => 'required',
            'OpportunityBoardColumnName' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $data["CreatedBy"] = User::get_user_full_name();
        $data['Order'] = $count;
        unset($data['OpportunityBoardColumnID']);
        if (OpportunityBoardColumn::create($data)) {
            return Response::json(array("status" => "success", "message" => "Opportunity Board Column Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Opportunity Board Column."));
        }
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
        if( $id > 0 ) {
            $data = Input::all();
            $OpportunityBoardColumn = OpportunityBoardColumn::findOrFail($id);

            $companyID = User::get_companyID();
            $data["CompanyID"] = $companyID;
            $data["ModifiedBy"] = User::get_user_full_name();
            $rules = array(
                'CompanyID' => 'required',
                'OpportunityBoardColumnName' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            if ($OpportunityBoardColumn->update($data)) {
                return Response::json(array("status" => "success", "message" => "Opportunity Board Column Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Opportunity Board Column."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Opportunity Board Column."));
        }
    }

    function updateColumnOrder($id){
        $data = Input::all();
        try {
            $columnorder = explode(',', $data['columnorder']);
            foreach ($columnorder as $index => $key) {
                OpportunityBoardColumn::where(['OpportunityBoardColumnID' => $key])->update(['Order' => $index]);
            }
            return Response::json(array("status" => "success", "message" => "Opportunity Board Column Updated"));
        }
        catch(Exception $ex){
            return Response::json(array("status" => "failed", "message" => $ex->getMessage()));
        }
    }
}
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
        $data['iDisplayStart'] +=1;
        //unset($data['iDisplayStart']);// +=1;
        //unset($data['iDisplayLength']);
        $response = NeonAPI::request('opportunityboard/get_boards',$data,false);
        return json_response_api($response);
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
        $where['Status']=1;
        if(User::is('AccountManager')){
            $where['Owner'] = User::get_userID();
        }
        $leadOrAccount = Account::where($where)->select(['AccountName', 'AccountID'])->orderBy('AccountName')->lists('AccountName', 'AccountID');
        if(!empty($leadOrAccount)){
            $leadOrAccount = array(""=> "Select a Company")+$leadOrAccount;
        }
        $boards = OpportunityBoard::getBoards();
        $opportunitytags = json_encode(Tags::getTagsArray(Tags::Opportunity_tag));
        $OpportunityBoardID = $id;
        return View::make('opportunityboards.manage', compact('OpportunityBoardID','OpportunityBoard','account_owners','leadOrAccount','boards','opportunitytags'));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /Opportunityboard/create
	 *
	 * @return Response
	 */
    public function create(){

        $data = Input::all();
        $response = NeonAPI::request('opportunityboard/add_board',$data);
        if($response->status_code==200){
            return Response::json(array("status" => "success", "message" => "Opportunity Board Successfully Created"));
        }else{
            return Response::json(array("status" => "failed", "message" => $response->message));
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
            $response = NeonAPI::request('opportunityboard/'.$id.'/update_board',$data);
            if($response->status_code==200){
                return Response::json(array("status" => "success", "message" => "Opportunity Board Successfully Updated"));
            }else{
                return Response::json(array("status" => "failed", "message" => $response->message));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating OpportunityBoard."));
        }
    }
}
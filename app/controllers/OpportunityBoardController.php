<?php


class OpportunityBoardController extends \BaseController {

    var $model = 'CRMBoard';
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
        $response = NeonAPI::request('opportunityboard/get_boards',$data,false);
        return json_response_api($response,true,true,true);
    }


    public function index(){
        return View::make('opportunityboards.index', compact(''));
    }


    public function configure($id){
        $TaskBoard = CRMBoard::getTaskBoard();
        $Board = CRMBoard::find($id);
        $urlto = 'opportunityboards';
        if(isset($TaskBoard[0]->BoardID) && $TaskBoard[0]->BoardID==$id){
            $urlto = 'task';
        }
        return View::make('opportunityboards.configure', compact('id','Board','urlto'));
    }

    public function manage($id){
        $message = '';
        $companyID    =  User::get_companyID();
        $Board = CRMBoard::find($id);
        $account_owners = User::getUserIDList();
        $DefaultCurrencyID  =  Company::where("CompanyID",$companyID)->pluck("CurrencyId");
        $currency    =  Currency::getCurrencyDropdownIDList();
        $where['Status']=1;
        if(User::is('AccountManager')){
            $where['Owner'] = User::get_userID();
        }
        $leadOrAccount = Account::where($where)->select(['AccountName', 'AccountID'])->orderBy('AccountName')->lists('AccountName', 'AccountID');
        if(!empty($leadOrAccount)){
            $leadOrAccount = array(""=> "Select a Company")+$leadOrAccount;
        }
        $boards = CRMBoard::getBoards();
        $opportunitytags = json_encode(Tags::getTagsArray(Tags::Opportunity_tag));
        $BoardID = $id;
        $response     =  NeonAPI::request('get_allowed_extensions',[],false);
        $response_extensions = [];

        if($response->status=='failed'){
            $message = $response->message['error'][0];
        }else{
            $response_extensions = json_response_api($response,true,true);
        }
        $token    = get_random_number();
        $max_file_size = get_max_file_size();
        return View::make('opportunityboards.manage', compact('BoardID','Board','account_owners','leadOrAccount','boards','opportunitytags','response_extensions','token','max_file_size','message','DefaultCurrencyID','currency','id'));
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /Opportunityboard/create
	 *
	 * @return Response
	 */
    public function create(){

        $data = Input::all();
        unset($data["BoardID"]);
        $response = NeonAPI::request('opportunityboard/add_board',$data);
        return json_response_api($response);
    }


	/**
	 * Update the specified resource in storage.
	 * PUT /Opportunityboard/{id}/update
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function update($id){
            $data = Input::all();
            $response = NeonAPI::request('opportunityboard/'.$id.'/update_board',$data);
            return json_response_api($response);
    }
}
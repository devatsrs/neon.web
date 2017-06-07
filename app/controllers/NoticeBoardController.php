<?php

class NoticeBoardController extends BaseController{

    public function __construct()    {

    }

    public function index(){
        $CompanyID = User::get_companyID();
        $LastUpdated = NoticeBoardPost::where("CompanyID", $CompanyID)->limit(1)->orderBy('NoticeBoardPostID','Desc')->pluck('updated_at');
        $extends = 'layout.main_only_sidebar';

        return View::make('noticeboard.index', compact('LastUpdated','extends'));

    }

    /**
     * @return mixed
     */
    public function store(){

        $data = Input::all();
        $CompanyID = User::get_companyID();
        $data['CompanyID'] = $CompanyID;
        $rules = array(
            'Title' => 'required',
            'Type' => 'required',
            'Detail' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $html = '';
        if($data['NoticeBoardPostID'] && NoticeBoardPost::where('NoticeBoardPostID',$data['NoticeBoardPostID'])->count()){
            $NoticeBoardPost = NoticeBoardPost::find($data['NoticeBoardPostID']);
            if($NoticeBoardPost->update($data)){
                $message = 'Post Successfully Updated';
                $status = 'success';
            }else{
                $message = 'Problem Updating Post.';
                $status = 'failed';
            }
        }else{
            if($NoticeBoardPost = NoticeBoardPost::create($data)){
                $message = 'Post Successfully Created';
                $status = 'success';
                $html = View::make('noticeboard.single', compact('NoticeBoardPost'))->render();
            }else{
                $message = 'Problem Creating Post';
                $status = 'failed';
            }
        }

        return Response::json(array("status" => $status, "message" => $message, 'LastID' => $NoticeBoardPost->NoticeBoardPostID,'html'=>$html));

    }


    public function delete($id){
        if(NoticeBoardPost::where('NoticeBoardPostID',$id)->delete()){
            return Response::json(array("status" => 'success', "message" => 'Post Successfully Deleted'));
        }else{
            return Response::json(array("status" => 'failed', "message" => 'Problem Deleting Post'));
        }
    }

    public static function get_mor_updates(){

        $data 					   = 	Input::all();
        $data['iDisplayStart'] 	   =	$data['scrol'];
        $data['iDisplayLength']    =    10;
        $CompanyID = User::get_companyID();
        $NoticeBoardPosts = NoticeBoardPost::where("CompanyID", $CompanyID)->limit(10)->offset($data['iDisplayStart'])->orderBy('NoticeBoardPostID','Desc')->get();
        if(count($NoticeBoardPosts) == 0){
            return  Response::json(array("status" => "failed", "message" => "No Result Found","scroll"=>"end"));
        }
        return View::make('noticeboard.list', compact('NoticeBoardPosts'));
    }


}
<?php

class NoticeBoardCustomerController extends BaseController{

    public function __construct()    {

    }

    public function index(){
        $CompanyID = User::get_companyID();
        $LastUpdated = NoticeBoardPost::where("CompanyID", $CompanyID)->limit(1)->orderBy('NoticeBoardPostID','Desc')->pluck('updated_at');

        return View::make('noticeboard.index', compact('LastUpdated'));
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
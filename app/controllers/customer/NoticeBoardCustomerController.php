<?php

class NoticeBoardCustomerController extends BaseController{

    public function __construct(){

    }

    public function index(){
        $CompanyID = User::get_companyID();
        $LastUpdated = NoticeBoardPost::where("CompanyID", $CompanyID)->limit(1)->orderBy('NoticeBoardPostID', 'Desc')->pluck('updated_at');
        $extends = 'layout.customer.main_only_sidebar';

        return View::make('noticeboard.index', compact('LastUpdated', 'extends'));
    }

    public static function get_mor_updates(){

        $data = Input::all();
        $data['iDisplayStart'] = $data['scrol'];
        $data['iDisplayLength'] = 10;
        $CompanyID = User::get_companyID();
        $NoticeBoardPosts = NoticeBoardPost::where("CompanyID", $CompanyID)->limit(10)->offset($data['iDisplayStart'])->orderBy('NoticeBoardPostID', 'Desc')->get();
        if (count($NoticeBoardPosts) == 0) {
            return Response::json(array("status" => "failed", "message" => "No Result Found", "scroll" => "end"));
        }
        return View::make('noticeboard.list', compact('NoticeBoardPosts'));
    }

    public static function get_next_update($id){

        $data = Input::all();
        $CompanyID = User::get_companyID();
        if($data['next']) {
            $NoticeBoardPost = NoticeBoardPost::where("CompanyID", $CompanyID)->where('NoticeBoardPostID', '>', $id)->limit(1)->orderBy('NoticeBoardPostID', 'asc')->first();
        }else{
            $NoticeBoardPost = NoticeBoardPost::where("CompanyID", $CompanyID)->where('NoticeBoardPostID', '<', $id)->limit(1)->orderBy('NoticeBoardPostID', 'Desc')->first();
        }
        if(isset($NoticeBoardPost->updated_at)){
            $NoticeBoardPost->LastUpdated = \Carbon\Carbon::createFromTimeStamp(strtotime($NoticeBoardPost->updated_at))->diffForHumans();
        }
        if (count($NoticeBoardPost) == 0) {
            return Response::json(array("status" => "failed", "message" => "No Result Found", "scroll" => "end"));
        }
        return $NoticeBoardPost;
    }

}
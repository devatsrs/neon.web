<?php

class OpportunityCommentsController extends \BaseController {

    var $model = 'OpportunityComments';

    /** Return opportunity comment and its attachments.
     * @param $id
     * @return mixed
     */
    public function ajax_opportunitycomments($id){
        $response = NeonAPI::request('opportunitycomments/'.$id.'/get_comments',[],false,true);

        if($response['status']=='failed'){
            return json_response_api($response,false);
        }else{
            $Comments = json_response_api($response,true,false,false);
        }
        return View::make('crmcomments.comments', compact('Comments','commentcount'))->render();
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /dealboard/create
	 *
	 * @return Response
	 */
    public function create(){
        $data = Input::all();
        $files_array	=	Session::get("email_attachments");

        if(isset($files_array[$data['token_attachment']])) {
            $FilesArray = array();
            foreach($files_array[$data['token_attachment']] as $key=> $array_file_data){
                $file_name = basename($array_file_data['filepath']);
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['OPPORTUNITY_ATTACHMENT']);
                $destinationPath = getenv("UPLOAD_PATH") . '/' . $amazonPath;
                copy($array_file_data['filepath'], $destinationPath . $file_name);

                $deleteFilesArray[] = $array_file_data['filepath'];

                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload file." ));
                }
                $FilesArray[] = array ("filename"=>$array_file_data['filename'],"filepath"=>$destinationPath . $file_name);
                unlink($array_file_data['filepath']);
            }
            $data['file']		=	json_encode($FilesArray);
        }
        $response = NeonAPI::request('opportunitycomment/add_comment',$data,true,false,true);

        if($response->status!='failed'){
            unset($files_array[$data['token_attachment']]);
            Session::set("email_attachments", $files_array);
        }

        return json_response_api($response);
    }

    public function getAttachment($commentdID,$attachmentID){
        echo $commentdID.$attachmentID;
        $response = NeonAPI::request('opportunitycomment/'.$commentdID.'/getattachment/'.$attachmentID,[],true,true,true);
    }

}
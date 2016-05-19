<?php

class OpportunityCommentsController extends \BaseController {

    var $model = 'OpportunityComments';

    /** Return opportunity comment and its attachments.
     * @param $id
     * @return mixed
     */
    public function ajax_opportunitycomments($id){
        $response = NeonAPI::request('opportunitycomments/'.$id.'/get_comments',[],false);

        if($response->status=='failed'){
            return json_response_api($response,false);
        }else{
            $result = json_response_api($response,true,false,false);
        }

        $Comments=[];
        $commentcount = 0;
        if(!empty($result)) {
            foreach ($result as $comment) {
                $attachments = '';
                $attachmentPaths = json_decode($comment->AttachmentPaths);
                if (!empty($attachmentPaths)) {
                    foreach ($attachmentPaths as $item) {
                        $path = validfilepath($item->filepath);
                        $attachments[] = ['filename'=>$item->filename,'filepath'=>$path];
                    }
                }
                $Comments[] = [
                    'CommentText' => $comment->CommentText,
                    'AttachmentPaths' => $attachments,
                    'created_at' => \Carbon\Carbon::createFromTimeStamp(strtotime($comment->created_at))->diffForHumans(),
                    'CreatedBy'=>$comment->CreatedBy
                ];
                $commentcount++;
            }
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
        $file = get_uploaded_files('email_attachments',$data);
        if(!empty($file)){
            $data['file'] = $file;
        }
        $response = NeonAPI::request('opportunitycomment/add_comment',$data,true,false,true);

        if($response->status!='failed'){
            unset($files_array[$data['token_attachment']]);
            Session::set("email_attachments", $files_array);
        }

        return json_response_api($response);
    }

}
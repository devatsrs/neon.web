<?php

class TaskCommentsController extends \BaseController {

    var $model = 'TaskComments';

    /** Return task comment and its attachments.
     * @param $id
     * @return mixed
     */
    public function ajax_taskcomments($id){
        $response = NeonAPI::request('taskcomments/'.$id.'/get_comments',[],false);
        $comments ='';
        if(isset($response->status_code)) {
            if ($response->status_code == 200) {
                $result = $response->data->result;
            }else{
                return json_response_api($response);
            }
        }else{
            return json_response_api($response);
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
        $file = get_uploaded_files('email_attachments',$data);
        if(!empty($file)){
            $data['file'] = $file;
        }
        $response = NeonAPI::request('taskcomment/add_comment',$data,true,false,true);
        return json_response_api($response);
    }

}
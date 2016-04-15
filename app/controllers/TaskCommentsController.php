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
                $comments = $response->data->result;
            }else{
                return json_response_api($response);
            }
        }else{
            return json_response_api($response);
        }
        $taskComments=[];
        $commentcount = 0;
        if(!empty($comments)) {
            foreach ($comments as $comment) {
                $attachments = '';
                $attachmentPaths = json_decode($comment->AttachmentPaths);
                if (!empty($attachmentPaths)) {
                    foreach ($attachmentPaths as $item) {
                        $path = validfilepath($item->filepath);
                        $attachments[] = ['filename'=>$item->filename,'filepath'=>$path];
                    }
                }
                $taskComments[] = ['CommentText' => $comment->CommentText,
                                            'AttachmentPaths' => $attachments,
                                            'created_at' => \Carbon\Carbon::createFromTimeStamp(strtotime($comment->created_at))->diffForHumans(),
                ];
                $commentcount++;
            }
        }
        return View::make('taskcomments.comments', compact('taskComments','commentcount'))->render();
    }

	/**
	 * Show the form for creating a new resource.
	 * GET /dealboard/create
	 *
	 * @return Response
	 */
    public function create(){
        $data = Input::all();
        if (Input::hasFile('commentattachment')) {
            $commentattachment = Input::file('commentattachment');
            $data['file'] = NeonAPI::base64byte($commentattachment);
        }
        $response = NeonAPI::request('taskcomment/add_comment',$data,true,false,true);
        return json_response_api($response);
    }

}
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
        //////////////////////////
        $emailattachments  =   $data['commentattachment'];
        $email_files_sent  = array();
        if($emailattachments!='')
        {
            $emailattachments_array = explode(",",$emailattachments);
            if(is_array($emailattachments_array))
            {
                foreach($emailattachments_array as $emailattachments_data)
                {

                    $temp_path    = getenv('TEMP_PATH').'/email_attachment/'.$emailattachments_data;
                    $email_files_sent[]  =  $temp_path;
                }

            }
        }

        $data['file']   = NeonAPI::base64byte($email_files_sent);
        /////////////////
        /*if (Input::hasFile('commentattachment')) {
            $commentattachment = Input::file('commentattachment');
            $data['file'] = NeonAPI::base64byte($commentattachment);
        }*/
        $response = NeonAPI::request('taskcomment/add_comment',$data,true,false,true);
        return json_response_api($response);
    }

}
<?php

class OpportunityCommentsController extends \BaseController {

    var $model = 'OpportunityComments';

    /** Return opportunity comment and its attachments.
     * @param $id
     * @return mixed
     */
    public function ajax_opportunitycomments($id){
        $response = NeonAPI::request('opportunitycomments/'.$id.'/get_comments',[],false);
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
        if (Input::hasFile('commentattachment')) {
            $commentattachment = Input::file('commentattachment');
            $data['file'] = NeonAPI::base64byte($commentattachment);
        }
        $response = NeonAPI::request('opportunitycomment/add_comment',$data,true,false,true);
        return json_response_api($response);
    }

}
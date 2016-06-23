<div class="col-md-6">
    <h4>Total Comments: {{!empty($Comments)?count($Comments):0}}</h4>
</div>
<div class="col-md-12 perfect-scrollbar" style="max-height:600px; overflow-y:auto">
    <div class="panel panel-primary">
        <div class="panel-body no-padding">

            <!-- List of Comments -->
            <ul class="comments-list">
                @if(!empty($Comments) && count($Comments)>0)
                    @foreach($Comments as $comment)
                        <li class="countComments" id="comment-1">
                            <div class="name">{{$comment['CreatedBy']}}</div>
                            <div class="comment-details">
                                <p class="comment-text">
                                    {{nl2br($comment['CommentText'])}}
                                </p>
                                <div class="comment-footer">
                                    <div class="comment-time">
                                        {{\Carbon\Carbon::createFromTimeStamp(strtotime($comment['created_at']))->diffForHumans()}}
                                    </div>
                                    <div class="comment-time pull-left">
                                        @if(!empty($comment['AttachmentPaths']))
                                            <a href="javascript:void(0)" title="View attachments" class="viewattachments btn-sm btn-default btn-xs">
                                                <i class="entypo-attach"></i>
                                            </a>
                                            @foreach(json_decode($comment['AttachmentPaths'],true) as $index=>$attachment)
                                                <div class="comment-attachment btn-default hidden"><a href="{{ URL::to($type.'/'.$comment['CommentID'].'/getattachment/'.$index)}}" target="_blank">{{basename($attachment['filename'])}}</a></div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </li>
                    @endforeach
                @endif
            </ul>

        </div>
    </div>
</div>

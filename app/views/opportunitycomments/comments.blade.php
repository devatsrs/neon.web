<div class="col-md-12">
    <h4>Total Comments: {{$commentcount}}</h4>
</div>
<div class="col-md-12" style="max-height:600px; overflow-y:auto">
    <div class="panel panel-primary">
        <div class="panel-body no-padding">

            <!-- List of Comments -->
            <ul class="comments-list">
                @if(!empty($opportunityComments))
                    @foreach($opportunityComments as $comment)
                        <li class="countComments" id="comment-1">
                            <div class="comment-details">
                                <p class="comment-text">
                                    {{nl2br($comment['CommentText'])}}
                                </p>
                                <div class="comment-footer">
                                    <div class="comment-time">
                                        {{$comment['created_at']}}
                                    </div>
                                    <div class="comment-time pull-left">
                                        @if(!empty($comment['AttachmentPaths']))
                                            @foreach($comment['AttachmentPaths'] as $attachment)
                                                <p class="comment-attachment"><a href="{{$attachment['filepath']}}" target="_blank">{{basename($attachment['filename'])}}</a></p>
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

@if(isset($response['data'])  && count($response['data'])>0)
<div class="col-md-12 perfect-scrollbar" style="max-height:600px; overflow-y:auto">
  <div class="panel panel-primary">
    <div class="panel-body no-padding"> 
      <!-- List of Comments -->
      <ul class="comments-list">
        @foreach($response['data'] as $rows)
        <li class="countComments" id="comment-1">
          <div class="comment-details">
            <p class="comment-text"> {{nl2br($rows['body'])}} </p>
            <div class="comment-footer">
              <div class="comment-time"> {{\Carbon\Carbon::createFromTimeStamp(strtotime($rows['created_at']))->diffForHumans()}} </div>
            </div>
          </div>
        </li>
        @endforeach       
      </ul>
    </div>
  </div>
</div>
@else
@if(isset($response['message']))<h3>{{ $response['message'] }}</h3>@endif 
@endif 
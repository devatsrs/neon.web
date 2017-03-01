 <a href="#" class="dropdown-toggle msgs" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"> <i class="entypo-mail"></i> @if($dropdownData['data']['totalNonVisitedJobs'][0]->totalNonVisitedJobs > 0)<span class="badge badge-warning">{{$dropdownData['data']['totalNonVisitedJobs'][0]->totalNonVisitedJobs;}}</span>@endif </a>
<ul class="dropdown-menu">
  <li class="top">
    <p>You have {{$dropdownData['data']['totalNonVisitedJobs'][0]->totalNonVisitedJobs}}  unread Message(s)</p>
  </li>
  <li>
    <ul class="dropdown-menu-list scroller">
      @if(count($dropdownData['data']['jobs'])>0)
      @foreach ($dropdownData['data']['jobs'] as $job)
      <?php
            $HasReadClass="";
            if($job->HasRead == 0){
                $HasReadClass = "bold";
            }

            ?>
            <!--<a href="Javascript:;" onclick="return showEmailMessageAjaxModal('{{$job->MsgID}}');"> -->
      <li> <a href="{{URL::to('/')}}/emailmessages/{{$job->EmailID}}/detail" > <span class="task <?php echo $HasReadClass;?>"> <span class="desc">{{$job->Title}}</span> </span>  <span class="task <?php echo $HasReadClass;?>"><span class="desc">{{$job->Description}}</span> <span class="percent">{{\Carbon\Carbon::createFromTimeStamp(strtotime($job->created_at))->diffForHumans() }}</span> </span> </a> </li>
      
      @endforeach
      @endif
    </ul>
  </li>
  <li class="external"> <a href="{{URL::to('/emailmessages')}}">See all Messages</a> </li>
</ul>
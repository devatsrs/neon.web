
<a href="#" class="dropdown-toggle jobs" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
    <i class="entypo-list"></i>


    @if($dropdownData['data']['totalNonVisitedJobs'][0]->totalNonVisitedJobs > 0)<span class="badge badge-warning">{{$dropdownData['data']['totalNonVisitedJobs'][0]->totalNonVisitedJobs;}}</span>@endif
</a>

<ul class="dropdown-menu">
    <li class="top">
        <p>You have {{$dropdownData['data']['totalPendingJobs'][0]->totalPendingJobs}} pending jobs</p>
    </li>
    <li>
        <ul class="dropdown-menu-list scroller">
            @if(count($dropdownData['data']['jobs'])>0)
            @foreach ($dropdownData['data']['jobs'] as $job)

            <?php
            $j_class ="";
            $class_striped ="";
            if($job->Status == 'Success')        { $j_class=  'progress-bar-success'; }
            if($job->Status == 'Failed')       { $j_class=  'progress-bar-danger'; }
            if($job->Status == 'In Progress')   { $j_class=  'progress-bar-warning'; }
            if($job->Status == 'Completed')     { $j_class=  'progress-bar-info'; }
            if($job->Status == 'Pending')       { $j_class=  'progress-bar-important'; }
            if($job->Status == 'Partially Failed')       { $j_class=  'progress-bar-danger';$class_striped =  'progress-striped'; }

            $HasReadClass="";
            if($job->HasRead == 0){
                $HasReadClass = "bold";
            }

            ?>

            <li>
                <a href="Javascript:;" onclick="return showJobAjaxModal('{{$job->JobID}}');">
                                            <span class="task <?php echo $HasReadClass;?>">
                                                <span class="desc">{{$job->Title}}</span>
                                                <span class="percent">{{$job->Status}}</span>
                                            </span>

                                            <span class="progress {{$class_striped}}">
                                                <span style="width: 100%;" class="progress-bar {{$j_class}}">
                                                    <span class="sr-only">{{$job->Status}}</span>
                                                </span>
                                            </span>
                                            <span class="task <?php echo $HasReadClass;?>">
                                                     <span class="desc">{{$job->JobType}}</span>
                                                     <span class="percent">{{\Carbon\Carbon::createFromTimeStamp(strtotime($job->created_at))->diffForHumans() }}</span>

                                             </span>
                </a>
            </li>
            @endforeach
            @endif


        </ul>
    </li>
    <li class="external">
        <a href="{{URL::to('/jobs')}}">See all jobs</a>
    </li>
</ul>
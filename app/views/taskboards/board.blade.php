<ul class="board-inner no-select" id="deals-dashboard">
    @if(count($boardsWithTask)>0)
        @foreach($boardsWithTask as $index=>$board )
            <?php//$style=(empty($columns[$index]['Hieght'])&&empty($columns[$index]['Width']))?'':'style="'.(empty($columns[$index]['Height'])?'':'Height:'.$columns[$index]['Height'].';').(empty($columns[$index]['Width'])?'':'Width:'.$columns[$index]['Width'].';').'"'; ?>
        <li data-id="{{$index}}" class="board-column count-li">
            <header>
                <h5>{{$columns[$index]['Name']}} {{(!empty($board[0])?'('.count($board).')':'')}}</h5>
            </header>
            <ul class="sortable-list board-column-list list-unstyled ui-sortable" data-name="closedwon">
                    @foreach($board as $curent=>$task)
                        @if(!empty($task))
                            <?php
                        $taggedUser = $task['TaggedUser'];
                        $task = $task['task'];
                        $priority = !empty($task['Priority'])?'<i style="color:red;font-size:15px;" class="edit-deal entypo-record"></i>':'';
                        $hidden = '';
                        $datediff = '';
                        $date = '';
                        $badgeClass = '';
                        $seconds = '';
                        if($task['DueDate']!='0000-00-00'){
                            $datediff=\Carbon\Carbon::createFromTimeStamp(strtotime($task['DueDate'].' '.$task['StartTime']))->diffInDays();
                            if($datediff>2){
                                $date =  \Carbon\Carbon::createFromTimeStamp(strtotime($task['DueDate'].' '.$task['StartTime']))->toFormattedDateString();
                            }else{
                                $date = \Carbon\Carbon::createFromTimeStamp(strtotime($task['DueDate'].' '.$task['StartTime']))->diffForHumans();
                            }
                            if(strpos($date,'ago')){
                                $datediff=-1;
                            }
                            switch (TRUE) {
                                case ($datediff<0  && $task['SetCompleted']!=1):
                                    $badgeClass = "badge badge-danger badge-roundless";
                                    break;
                                case ($datediff==0):
                                    $badgeClass = "badge badge-success badge-roundless";
                                    break;
                                case ($datediff>0):
                                    $badgeClass = "badge badge-warning badge-roundless";
                                    break;
                                case ($datediff<0  && $task['SetCompleted']==1):
                                    $badgeClass = "badge badge-roundless";
                                    break;
                            }
                        }
                        foreach($task as $i=>$val){
                            $hidden.='<input type="hidden" name="'.$i.'" value="'.$val.'" >';
                        }
                        ?>
                            <li class="tile-stats sortable-item count-cards" data-name="{{$task['Subject']}}" data-id="{{$task['TaskID']}}">
                                <button type="button" title="Edit Task" class="btn btn-default btn-xs edit-deal pull-right"> <i class="entypo-pencil"></i> </button>
                                <div class="row-hidden">
                                    {{$hidden}}
                                </div>
                                <div class="info">
                                    <p class="title">{{$task['Subject']}}</p>
                                    <p class="name"><span class="{{$badgeClass}} pull-left">{{$date}}</span><span class="pull-right">{{$priority}}</span></p>
                                </div>
                                <div class="bottom pull-right">
                                    @if(count($taggedUser)>0)
                                        @foreach($taggedUser as $user)
                                            <?php $color=!empty($user['Color'])?'style="background-color:'.$user['Color'].'"':''; ?>
                                            <span class="badge badge-warning badge-roundless tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="{{$user['FirstName'].' '.$user['LastName']}}">{{strtoupper(substr($user['FirstName'],0,1)).strtoupper(substr($user['LastName'],0,1))}}</span>
                                        @endforeach
                                    @endif
                                        <span class="badge badge-success badge-roundless tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="{{$task['userName']}}">{{strtoupper(substr($task['userName'],0,1))}}</span>
                                </div>

                            </li>
                        @endif
                    @endforeach
            </ul>
        </li>
        @endforeach
    @endif
</ul>

<script>
    @if(!empty($message))
        toastr.error({{'"'.$message.'"'}}, "Error", toastr_opts);
    @endif
</script>
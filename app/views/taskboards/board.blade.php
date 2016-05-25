<ul class="board-inner no-select" id="deals-dashboard">
    @if(count($columnsWithITask)>0)
        @foreach($columnsWithITask as $index=>$column )
            <?php//$style=(empty($columns[$index]['Hieght'])&&empty($columns[$index]['Width']))?'':'style="'.(empty($columns[$index]['Height'])?'':'Height:'.$columns[$index]['Height'].';').(empty($columns[$index]['Width'])?'':'Width:'.$columns[$index]['Width'].';').'"'; ?>
        <li data-id="{{$index}}" class="board-column count-li">
            <header>
                <h5>{{$columns[$index]['Name']}} {{(!empty($column[0])?'('.count($column).')':'')}}</h5>
            </header>
            <ul class="sortable-list board-column-list list-unstyled ui-sortable" data-name="closedwon">
                    @foreach($column as $task)
                        @if(!empty($task))
                            <?php
                        $taggedUsers = $task['TaggedUsers'];
                        $task = $task['task'];
                        //$priority = !empty($task['Priority'])?'<i style="color:#cc2424;font-size:15px;" class="edit-deal entypo-record"></i>':'';
                        $priorityborder = !empty($task['Priority'])?'priority':'normal';
                        $hidden = '';
                        $datediff = '';
                        $date = '';
                        $badgeClass = '';
                        $seconds = '';
                        if($task['DueDate']!='0000-00-00'){
                            $yeardiff=\Carbon\Carbon::createFromTimeStamp(strtotime($task['DueDate']))->diffInYears();
                            $datediff=\Carbon\Carbon::createFromTimeStamp(strtotime($task['DueDate']))->diffInDays();
                            $datediffhuman=\Carbon\Carbon::createFromTimeStamp(strtotime($task['DueDate'].' '.$task['StartTime']))->diffForHumans();
                            $date = \Carbon\Carbon::createFromTimeStamp(strtotime($task['DueDate'].' '.$task['StartTime']))->toFormattedDateString();
                            if(strpos($datediffhuman,'ago')){
                                $datediff=-1;
                            }
                            if($yeardiff ==0){
                                $arry = explode(',',$date);
                                $date = $arry[0];
                            }
                            $date = '<i class="entypo-clock"></i>'.$date;
                            switch (TRUE) {
                                case ($datediff<0):
                                    $badgeClass = "badge badge-danger badge-roundless";
                                    break;
                                case ($datediff==0):
                                    $badgeClass = "badge badge-warning badge-roundless";
                                    break;
                                case ($datediff>0):
                                    $badgeClass = "badge badge-roundless";
                                    break;
                            }
                        }
                        foreach($task as $i=>$val){
                            $hidden.='<input type="hidden" name="'.$i.'" value="'.$val.'" >';
                        }
                        ?>
                            <li class="tile-stats sortable-item count-cards {{$priorityborder}}" data-name="{{$task['Subject']}}" data-id="{{$task['TaskID']}}">
                                <button type="button" title="Edit Task" class="btn btn-default btn-xs edit-deal pull-right"> <i class="entypo-pencil"></i> </button>
                                <span class="{{$badgeClass}} pull-right">{{$date}}</span>
                                <div class="row-hidden">
                                    {{$hidden}}
                                </div>
                                <div class="info">
                                    <p class="title">{{$task['Subject']}}</p>
                                </div>
                                <div class="bottom pull-right">
                                    @if(count($taggedUsers)>0)
                                        @foreach($taggedUsers as $user)
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
        toastr.error("{{$message}}", "Error", toastr_opts);
    @endif
</script>
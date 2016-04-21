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
                        $priorityarray = [Task::High=>'entypo-up-bold',Task::Medium=>'entypo-record',Task::Low=>'entypo-down-bold'];
                        $taggedUser = $task['TaggedUser'];
                        $task = $task['task'];
                        $backgroundcolour = '';//$task['BackGroundColour']==''?'':'style="background-color:'.$task['BackGroundColour'].';"';
                        $textcolour = '';//$task['TextColour']==''?'':'style="color:'.$task['TextColour'].';"';
                        switch ($task['Priority']) {
                            case 1:
                                $priority='style="color:red;font-size:15px;"';
                                break;
                            case 2:
                                $priority='style="color:#FAD839;font-size:15px;"';
                                break;
                            case 3:
                                $priority='style="color:green;font-size:15px;"';
                                break;
                        }
                        $hidden = '';
                        foreach($task as $i=>$val){
                            $hidden.='<input type="hidden" name="'.$i.'" value="'.$val.'" >';
                        }

                        /*$ContactName = '';
                        $Owner = '';
                        if(!empty($task['ContactName'])){
                            $ContactNameArray = explode(" ", $task['ContactName']);
                            foreach ($ContactNameArray as $w) {
                                $ContactName .= $w[0];
                            }
                        }
                        if(!empty($task['Owner'])){
                            $OwnerArray = explode(" ", $task['Owner']);
                            foreach ($OwnerArray as $w) {
                                $Owner .= $w[0];
                            }
                        }*/
                        ?>
                            <li class="tile-stats sortable-item count-cards" {{$backgroundcolour}} data-name="{{$task['Subject']}}" data-id="{{$task['TaskID']}}">
                                <div class="pull-left"><i class="edit-deal {{$priorityarray[$task['Priority']]}}" {{$priority}}></i></div>
                                <div class="pull-right"><i class="edit-deal entypo-pencil" {{$textcolour}}></i></div>
                                <div class="row-hidden">
                                    {{$hidden}}
                                </div>
                                <div class="info">
                                    <p {{$textcolour}} class="title">{{$task['Subject']}}</p>
                                </div>
                                <div class="pull-right bottom">
                                    @if(count($taggedUser)>0)
                                        @foreach($taggedUser as $user)
                                            <?php $color=!empty($user['Color'])?'style="background-color:'.$user['Color'].'"':''; ?>
                                            <span {{$color}} class="badge badge-warning tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="{{$user['FirstName'].' '.$user['LastName']}}">{{strtoupper(substr($user['FirstName'],0,1)).strtoupper(substr($user['LastName'],0,1))}}</span>
                                        @endforeach
                                    @endif
                                        <span class="badge badge-success tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="{{$task['userName']}}">{{strtoupper(substr($task['userName'],0,1))}}</span>
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
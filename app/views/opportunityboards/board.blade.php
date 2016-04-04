<div class="header">
    <ul>
        @foreach($columns as $index=>$column)
            <?php //$style=(empty($columns[$index]['Width']))?'':'style="'.(empty($columns[$index]['Width'])?'':'Width:'.$columns[$index]['Width'].';').'"'; ?>
        <li>{{$columns[$index]['Name']}}</li>
        @endforeach
    </ul>
</div>
<ul class="board-inner no-select" id="deals-dashboard">
    @if(count($boradsWithOpportunities)>0)
        @foreach($boradsWithOpportunities as $index=>$board )
            <?php //$style=(empty($columns[$index]['Hieght'])&&empty($columns[$index]['Width']))?'':'style="'.(empty($columns[$index]['Height'])?'':'Height:'.$columns[$index]['Height'].';').(empty($columns[$index]['Width'])?'':'Width:'.$columns[$index]['Width'].';').'"'; ?>
        <li data-id="{{$index}}" class="board-column count-li">
            <ul class="sortable-list board-column-list list-unstyled ui-sortable" data-name="closedwon">
                    @foreach($board as $opportunity)
                        @if(!empty($opportunity))
                            <?php
                        $taggedUser = $opportunity['TaggedUser'];
                        $opportunity = $opportunity['opportunity'];
                        $backgroundcolour = $opportunity['BackGroundColour']==''?'':'style="background-color:'.$opportunity['BackGroundColour'].';"';
                        $textcolour = $opportunity['TextColour']==''?'':'style="color:'.$opportunity['TextColour'].';"';
                        $hidden = '';
                        foreach($opportunity as $i=>$val){
                            $hidden.='<input type="hidden" name="'.$i.'" value="'.$val.'" >';
                        }

                        $ContactName = '';
                        $Owner = '';
                        if(!empty($opportunity['ContactName'])){
                            $ContactNameArray = explode(" ", $opportunity['ContactName']);
                            foreach ($ContactNameArray as $w) {
                                $ContactName .= $w[0];
                            }
                        }
                        if(!empty($opportunity['Owner'])){
                            $OwnerArray = explode(" ", $opportunity['Owner']);
                            foreach ($OwnerArray as $w) {
                                $Owner .= $w[0];
                            }
                        }
                        ?>
                            <li class="tile-stats sortable-item count-cards" {{$backgroundcolour}} data-name="{{$opportunity['OpportunityName']}}" data-id="{{$opportunity['OpportunityID']}}">
                                <div class="pull-right"><i class="edit-deal entypo-pencil" {{$textcolour}}></i></div>
                                <div class="row-hidden">
                                    {{$hidden}}
                                </div>
                                <div class="info">
                                    <p {{$textcolour}} class="title">{{$opportunity['OpportunityName']}}</p>
                                    <p {{$textcolour}} class="name">{{$opportunity['Company']}}</p>
                                </div>
                                <div class="pull-right">
                                    @if(count($taggedUser)>0)
                                        @foreach($taggedUser as $user)
                                            <?php $color=!empty($user['Color'])?'style="background-color:'.$user['Color'].'"':''; ?>
                                            <span {{$color}} class="badge badge-warning tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="{{$user['FirstName'].' '.$user['LastName']}}">{{strtoupper(substr($user['FirstName'],0,1)).strtoupper(substr($user['LastName'],0,1))}}</span>
                                        @endforeach
                                    @endif
                                    <span class="badge badge-info tooltip-primary">{{strtoupper($Owner)}}</span>
                                    <span class="badge badge-success tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="{{$opportunity['ContactName']}}">{{strtoupper($ContactName)}}</span>
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
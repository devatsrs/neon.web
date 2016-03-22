<ul class="board-inner no-select" id="deals-dashboard">
    @if(count($boradsWithOpportunities)>0)
        @foreach($boradsWithOpportunities as $index=>$board )
            <?php $style=(empty($columns[$index]['Hieght'])&&empty($columns[$index]['Width']))?'':'style="'.(empty($columns[$index]['Height'])?'':'Height:'.$columns[$index]['Height'].';').(empty($columns[$index]['Width'])?'':'Width:'.$columns[$index]['Width'].';').'"'; ?>
        <li data-id="{{$index}}" {{$style}} class="board-column count-li">
            <header>
                <h5>{{$columns[$index]['Name']}} {{empty($board[0])?'':'('.count($board).')'}}</h5>
            </header>
            <ul class="sortable-list board-column-list list-unstyled ui-sortable" data-name="closedwon">
                    @foreach($board as $opportunity)
                        @if(!empty($opportunity))
                            <?php
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
                                    <h3 {{$textcolour}}>{{$opportunity['OpportunityName']}}</h3>
                                    <p {{$textcolour}}>{{$opportunity['Company']}} Company Name</p>
                                </div>
                                <div class="pull-right">
                                    <span class="badge badge-info">{{$Owner}}</span>
                                    <span class="badge badge-success">{{$ContactName}}</span>
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
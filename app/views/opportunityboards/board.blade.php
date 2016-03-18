<ul class="board-inner no-select" id="deals-dashboard">
    @if(count($boradsWithOpportunities)>0)
        @foreach($boradsWithOpportunities as $index=>$board )
        <li data-id="{{$index}}" class="board-column count-li">
            <header>
                <h5>{{$columns[$index]}} {{empty($board[0])?'':'('.count($board).')'}}</h5>
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
                            $style=(empty($opportunity['Hieght'])&&empty($opportunity['Width']))?'':'style="'.(empty($opportunity['Hieght'])?'':'Height:'.$opportunity['Hieght'].';').(empty($opportunity['Width'])?'':'Width:'.$opportunity['Width'].';').'"';
                        ?>
                            <li class="board-column-item sortable-item count-cards" data-name="{{$opportunity['OpportunityName']}}" data-id="{{$opportunity['OpportunityID']}}">
                            <div class="row-hidden">
                                {{$hidden}}
                            </div>
                                <div class="tile-stats" {{$backgroundcolour}}>
                                    <i class="edit-deal entypo-pencil pull-right"></i>
                                    <div class="margin-top-15" id="card-1-info">
                                        <h3 id="card-1-name" {{$textcolour}}>{{$opportunity['OpportunityName']}}</h3>
                                        <h3 id="card-1-company" {{$textcolour}}>{{$opportunity['Company']}}</h3>
                                        <h3 id="card-1-contract" {{$textcolour}}>{{(empty($opportunity['ContactName'])?'.':$opportunity['ContactName'])}}</h3>
                                        <h3 id="card-1-owner" {{$textcolour}}>Account Owner: {{$opportunity['Owner']}}</h3>
                                    </div>
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
<ul class="board-inner no-select" id="deals-dashboard">
    @if(count($columnsWithOpportunities)>0)
        @foreach($columnsWithOpportunities as $index=>$column )
        <li data-id="{{$index}}" class="board-column count-li">
            <header>
                <h5>{{$columns[$index]['Name']}} {{(!empty($column[0])?'('.count($column).')':'')}}</h5>
            </header>
            <ul class="sortable-list board-column-list list-unstyled ui-sortable" data-name="closedwon">
                    @foreach($column as $opportunity)
                        @if(!empty($opportunity))
                            <?php 
                        $taggedUsers = $opportunity['TaggedUsers'];
                        $opportunity = $opportunity['opportunity'];
                        $backgroundcolour = '';//$opportunity['BackGroundColour']==''?'':'style="background-color:'.$opportunity['BackGroundColour'].';"';
                        $textcolour = '';//$opportunity['TextColour']==''?'':'style="color:'.$opportunity['TextColour'].';"';
                        $hidden = '';
                        foreach($opportunity as $i=>$val){
                            $hidden.='<input type="hidden" name="'.$i.'" value="'.$val.'" >';
                        }

                        $Owner = '';
                        if(!empty($opportunity['Owner'])){
                            $OwnerArray = explode(" ", $opportunity['Owner']);
                            foreach ($OwnerArray as $w) {
                                $Owner .= $w[0];
                            }
                        }
                        ?>
                            <li class="tile-stats sortable-item count-cards" {{$backgroundcolour}} data-name="{{$opportunity['OpportunityName']}}" data-id="{{$opportunity['OpportunityID']}}">
                                @if(User::checkCategoryPermission('Opportunity','Edit'))
                                <button type="button" title="Edit" class="btn btn-default btn-xs edit-deal pull-right"> <i class="entypo-pencil"></i> </button>
                                @endif
                                <div class="row-hidden">
                                    {{$hidden}}
                                </div>
                                <div class="info">
                                    <p {{$textcolour}} class="title">{{$opportunity['OpportunityName']}}</p>
                                    <p {{$textcolour}} class="name">{{$opportunity['Company']}}</p>
                                </div>
                                <div class="pull-right bottom">
                                    @if(count($taggedUsers)>0)
                                        @foreach($taggedUsers as $user)
                                            <!--style="background-color:{$user['Color']}}"-->
                                            <span class="badge badge-warning badge-roundless tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="{{$user['FirstName'].' '.$user['LastName']}}">{{strtoupper(substr($user['FirstName'],0,1)).strtoupper(substr($user['LastName'],0,1))}}</span>
                                        @endforeach
                                    @endif
                                    <span class="badge badge-success badge-roundless tooltip-primary" data-toggle="tooltip" data-placement="top" data-original-title="{{$opportunity['FirstName'].' '.$opportunity['LastName']}}">{{strtoupper(substr($opportunity['FirstName'],0,1)).strtoupper(substr($opportunity['LastName'],0,1))}}</span>
                                </div>
                            </li> 
                        @endif
                    @endforeach
            </ul>
        </li>
        @endforeach
    @endif
</ul> 
<input type="hidden" name="Worth_hidden" id="Worth_hidden" value="<?php   echo !empty($WorthTotal)?$WorthTotal:0; ?>" />
<input type="hidden" name="Currency_hidden" id="Currency_hidden" value="<?php  echo $Currency; ?>" />
<script>
    @if(!empty($message))
        toastr.error({{'"'.$message.'"'}}, "Error", toastr_opts);
    @endif
</script>
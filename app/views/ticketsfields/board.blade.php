<ul class="board-inner no-select" id="deals-dashboard">
  <li data-id="1" class="board-column count-li">
    <header>
      <h5>Ticket Fields</h5>
    </header>
    <ul class="sortable-list board-column-list list-unstyled ui-sortable" data-name="closedwon">
      @foreach($Ticketfields as $TicketfieldsData) 
      @if(!empty($TicketfieldsData))
      <?php    
			$hidden = '';
			foreach($TicketfieldsData as $i=>$val){
				if($i!='field_options' && $i!='choices')
				{
					$hidden.='<input type="hidden" name="'.$i.'" value="'.$val.'" >';
				}else{
					$hidden.="<input type='hidden' name='".$i."' value='".json_encode($val)."' >";
				}
			}
			?>
      <li class="tile-stats sortable-item count-cards" field_type="{{$TicketfieldsData['field_type']}}"  data-name="{{$TicketfieldsData['label']}}" data-id="{{$TicketfieldsData['id']}}"> @if($TicketfieldsData['FieldStaticType']==Ticketfields::FIELD_TYPE_DYNAMIC)
        <button type="button" delete_main_field_id="{{$TicketfieldsData['id']}}" title="Delete Field" class="btn btn-red btn-xs delete_main_field pull-right"> <i class="entypo-trash"></i> </button>
        @endif
        <button type="button" title="Edit Field" class="btn btn-default btn-xs edit-deal pull-right"> <i class="entypo-pencil"></i> </button>
        <div class="row-hidden"> {{$hidden}} </div>
        <div class="info">
          <p  class="title">{{$TicketfieldsData['label']}}</p>
          <p  class="name">{{Ticketfields::TicketFeildsGridText($TicketfieldsData['type'])}}</p>
        </div>
      </li>
      @endif
      @endforeach
    </ul>
  </li>
</ul>

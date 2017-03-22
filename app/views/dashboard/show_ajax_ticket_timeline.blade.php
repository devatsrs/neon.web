 @if(count($response)>0)
     @foreach($response as $row)
     <li>
         <div class="thumb">{{ucfirst($row->UserName[0])}}</div>
         <div class="description">
             <a href="{{URL::to('accounts/'.$row->UserID.'/show')}}" target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
             @if($row->TimelineType == 1)
                @if($row->TicketSubmit=1)
                     <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail')}}">Submitted a new ticket</a>
                    @else
                     <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail#message').$row->RecordID}}">Reply</a>
                     <span>to the ticket</span>
                 @endif
             @elseif($row->TimelineType == 2)
                 <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail#note').$row->RecordID}}">add note</a>
                 <span>to the ticket</span>
             @else
                @if($row->TicketFieldID == Ticketfields::default_agent)
                    <span>assigned the ticket</span>
                 @else
                     <span>updated ticket {{Ticketfields::$defaultTicketFields[$row->TicketFieldID]}} of</span>
                @endif
             @endif
             <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail')}}" target="_blank" class="notelink">{{$row->Subject}}</a>
             @if($row->TimelineType == 2)
                 <span>to {{$fieldValues[$row->TicketFieldValueToID]}}</span>
             @endif
             <br>
             <span class="time">{{\Carbon\Carbon::createFromTimeStamp(strtotime($row->created_at))->diffForHumans()}}</span>
         </div>
     </li>
     @endforeach
 @endif

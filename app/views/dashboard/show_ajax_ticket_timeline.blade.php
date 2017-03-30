 @if(count($response)>0)
     @foreach($response as $row)
     <li>
         <div class="thumb">{{ucfirst($row->UserName[0])}}</div>
         <div class="description">
             <a href="{{($row->TimelineType == 3 && $row->TicketFieldID == 0 && $row->CustomerID != 0)?(URL::to('accounts/'.$row->UserID.'/show')):URL::to('users/edit/'.$row->UserID)}}" target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
             @if($row->TimelineType == 1)
                 @if($row->TicketSubmit == 1)
                     <span>Submitted a new ticket</span>
                 @else
                     <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail#message').$row->RecordID}}" target="_blank" class="notelink">Replied</a>
                     <span>to the ticket</span>
                 @endif
             @elseif($row->TimelineType == 2)
                 <span>added a</span>
                 <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail#note').$row->RecordID}}" target="_blank" class="notelink">note</a>
                 <span>to the ticket</span>
             @else
                @if($row->TicketFieldID == Ticketfields::default_agent)
                    <span>assigned the ticket</span>
                 @elseif($row->TicketFieldID == 0)
                     <span>created the ticket</span>
                 @else
                     <span>updated ticket {{Ticketfields::$defaultTicketFields[$row->TicketFieldID]}} of</span>
                @endif
             @endif
             <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail')}}" target="_blank" class="notelink">{{$row->Subject}}</a>
             @if($row->TimelineType == 3)
                 @if($row->TicketFieldID == Ticketfields::default_priority)
                     <span>to {{$fieldPriority[$row->TicketFieldValueToID]}}</span>
                 @elseif($row->TicketFieldID == Ticketfields::default_agent)
                     <span>to</span>
                     <a href="{{URL::to('users/edit/'.$row->TicketFieldValueToID)}}" target="_blank" class="notelink">{{$agents[$row->TicketFieldValueToID]}}</a>
                 @elseif($row->TicketFieldID == 0)

                 @else
                     <span>to {{$fieldValues[$row->TicketFieldValueToID]}}</span>
                 @endif
             @endif
             <br>
             <span class="time">{{\Carbon\Carbon::createFromTimeStamp(strtotime($row->created_at))->diffForHumans()}}</span>
         </div>
     </li>
     @endforeach
 @endif

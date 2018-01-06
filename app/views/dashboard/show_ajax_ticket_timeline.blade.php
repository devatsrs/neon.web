 @if(count($response)>0)
     @foreach($response as $row)
     <li>
         <div class="thumb">{{ucfirst($row->UserName[0])}}</div>
         <div class="description">

             @if($row->ParentType == TicketLog::TICKET_USER_TYPE_ACCOUNT)
                 <a href="{{get_account_view_url($row->ParentID)}}" target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
             @elseif($row->ParentType == TicketLog::TICKET_USER_TYPE_CONTACT)
                 <a href="{{get_contact_view_url($row->ParentID)}}" target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
             @elseif($row->ParentType == TicketLog::TICKET_USER_TYPE_USER)
                 <a href="{{get_user_edit_url($row->ParentID)}}" target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
             @elseif($row->ParentType == TicketLog::TICKET_USER_TYPE_SYSTEM)
                 <a href="#" target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
             @endif

             <span>
                 @if($row->Action == TicketLog::TICKET_ACTION_CREATED)
                     Created Ticket
                 @elseif($row->Action == TicketLog::TICKET_ACTION_ASSIGNED_TO)
                     Assigned Ticket
                 @elseif($row->Action == TicketLog::TICKET_ACTION_AGENT_REPLIED)
                     Replied To Ticket
                 @elseif($row->Action == TicketLog::TICKET_ACTION_CUSTOMER_REPLIED)
                     Replied To Ticket
                 @elseif($row->Action == TicketLog::TICKET_ACTION_STATUS_CHANGED)
                     Changed Status
                 @elseif($row->Action == TicketLog::TICKET_ACTION_NOTE_ADDED)
                     Note Added
                 @elseif($row->Action == TicketLog::TICKET_ACTION_FIELD_CHANGED)
                     {{$row->ActionText}}
                 @endif
             </span>


             <br>
             <span class="time">{{\Carbon\Carbon::createFromTimeStamp(strtotime($row->created_at))->diffForHumans()}}</span>







        {{-- ------------------------------------------------------------------------------------------------------}}
                     <!-- First half of text show in 1 line -->
             <!-- Ticket Create or submit by user or customer, showing name with link -->
            {{-- @if($row->TimelineType == 3 && $row->TicketFieldID == 0 && $row->CustomerID != 0)
                 @if($row->CustomerType == 1)
                     <a href="{{(URL::to('accounts/'.$row->CustomerID.'/show'))}}"
                        target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
                 @elseif($row->CustomerType == 2)
                     <a href="{{(URL::to('contacts/'.$row->CustomerID.'/show'))}}"
                        target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
                 @endif
             @else
                 <a href="{{(URL::to('users/edit/'.$row->UserID))}}"
                    target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
                 @endif
             <!--TimelineType: Email -->
             @if($row->TimelineType == 1)
                 <!--Ticket Submit by Email -->
                 @if($row->TicketSubmit == 1)
                     <span>Submitted a new ticket</span>
                 @else
                         <!--Email reply to -->
                     <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail#message').$row->RecordID}}" target="_blank" class="notelink">Replied</a>
                     <span>to the ticket</span>
                 @endif
              <!--TimelineType: Note -->
             @elseif($row->TimelineType == 2)
                 <span>added a</span>
                 <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail#note').$row->RecordID}}" target="_blank" class="notelink">note</a>
                 <span>to the ticket</span>
              <!--TimelineType: Ticketlog -->
             @else
                @if($row->TicketFieldID == Ticketfields::default_agent)
                    <span>assigned the ticket</span>
                 @elseif($row->TicketFieldID == 0)
                     <span>created the ticket</span>
                 @else
                     <span>updated ticket {{Ticketfields::$defaultTicketFields[$row->TicketFieldID]}} of</span>
                @endif
             @endif
             <!-- Ticket Name with link -->
             <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail')}}" target="_blank" class="notelink">{{$row->Subject}}</a>
             <!-- Second half of text show in 1 line -->
             @if($row->TimelineType == 3)
                 @if($row->TicketFieldID == Ticketfields::default_priority)
                    @if($row->TicketFieldValueToID > 0)
                       <span>to {{$fieldPriority[$row->TicketFieldValueToID]}}</span>
                    @else
                      <span>none</span>
                    @endif
                     <!-- assigned the ticket to agent name -->
                 @elseif($row->TicketFieldID == Ticketfields::default_agent)
                     <span>to</span>
                     @if($row->TicketFieldValueToID > 0)
                        <a href="{{URL::to('users/edit/'.$row->TicketFieldValueToID)}}" target="_blank" class="notelink">{{$agents[$row->TicketFieldValueToID]}}</a>
                     @else
                         <span>none</span>
                     @endif
                 @elseif($row->TicketFieldID == Ticketfields::default_group)
                      <span>to</span>
                     @if($row->TicketFieldValueToID > 0)
                         <span>to {{$groups[$row->TicketFieldValueToID]}}</span>
                     @else
                         <span>none</span>
                     @endif
                 @elseif($row->TicketFieldID == 0)
                     @if($row->TicketSubmit == 0 && $row->CustomerType != 0)
                         <span>on the behalf of</span>
                         <!-- CustomerType = 1 ?'Account':'Contact'-->
                         @if($row->CustomerType == 1)
                             <a href="{{URL::to('accounts/'.$row->CustomerID.'/show')}}" target="_blank" class="notelink">{{$accounts[$row->CustomerID]}}</a>
                         @elseif($row->CustomerType == 2)
                             <a href="{{URL::to('contacts/'.$row->CustomerID.'/show')}}" target="_blank" class="notelink">{{$contacts[$row->CustomerID]}}</a>
                         @endif
                     @endif
                 @elseif($row->TicketFieldID == Ticketfields::default_description)

                 @else
                     @if($row->TicketFieldValueToID > 0)
                        <span>to {{array_key_exists($row->TicketFieldValueToID,$fieldValues)? $fieldValues[$row->TicketFieldValueToID]:''}}</span>
                     @else
                         <span>none</span>
                     @endif
                 @endif
             @endif
             <br>
             <span class="time">{{\Carbon\Carbon::createFromTimeStamp(strtotime($row->created_at))->diffForHumans()}}</span>--}}
         </div>
     </li>
     @endforeach
 @endif

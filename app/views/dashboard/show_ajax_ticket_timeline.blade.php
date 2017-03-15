 @if(count($response)>0)
     @foreach($response as $row)
     <li>
         <div class="thumb">{{ucfirst($row->UserName[0])}}</div>
         <div class="description">
             <a href="{{ URL::to('accounts/'.$row->UserID.'/show')}}" class="username">{{ucfirst($row->UserName)}}</a>
             <span>{{$row->EmailParent==0?'Submitted a new ticket':'sent an'}}</span>
             @if($row->EmailParent > 0)
                 <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail#message').$row->AccountEmailLogID}}">email response</a>
                 <span>to the ticket</span>
             @endif
             <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail')}}" class="notelink">{{$row->Subject}}</a>
             <br>
             <span class="time">{{\Carbon\Carbon::createFromTimeStamp(strtotime($row->created_at))->diffForHumans()}}</span>
         </div>
     </li>
     @endforeach
 @endif

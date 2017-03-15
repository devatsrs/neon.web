 @if(count($response)>0)
     @foreach($response as $row)
         <?php
         $target = ($row->TimeLineType==1?'#message':'#note').$row->ID;
         $text = $row->TimeLineType==1?($row->EmailParent==0?'Submitted a new ticket':'Reply'):'add note';
         ?>
     <li>
         <div class="thumb">{{ucfirst($row->UserName[0])}}</div>
         <div class="description">
             @if($row->TimeLineType==1)
                <a href="{{URL::to('accounts/'.$row->UserID.'/show')}}" target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
             @else
                 <span>{{ucfirst($row->UserName)}}</span>
             @endif

             @if($row->TimeLineType == 1)
                @if($row->EmailParent=0)
                     <span>{{$text}}</span>
                    @else
                     <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail').$target}}">{{$text}}</a>
                     <span>to the ticket</span>
                 @endif
             @else
                 <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail').$target}}">{{$text}}</a>
                 <span>to the ticket</span>
             @endif
             <a href="{{URL::to('/tickets/'.$row->TicketID.'/detail')}}" target="_blank" class="notelink">{{$row->Subject}}</a>
             <br>
             <span class="time">{{\Carbon\Carbon::createFromTimeStamp(strtotime($row->created_at))->diffForHumans()}}</span>
         </div>
     </li>
     @endforeach
 @endif

 @if(count($response)>0)
     @foreach($response as $row)
         <?php
                 $text = $row->TimeLineType==1?'email response':'add note';
                 $action = $row->TimeLineType==1?($row->EmailParent==0?'Submitted a new ticket':'Reply to'):'add note';
                 $target = ($row->TimeLineType==1?'#message':'#note').$row->ID
         ?>
     <li>
         <div class="thumb">{{ucfirst($row->UserName[0])}}</div>
         <div class="description">
             <a href="{{ $row->TimeLineType==1? URL::to('accounts/'.$row->UserID.'/show'):'#'}}" target="_blank" class="username">{{ucfirst($row->UserName)}}</a>
             <span>{{$action}}</span>
             @if($row->EmailParent > 0)
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

<table id="table-4" class="table mail-table">
  <thead>
    <tr>
      <th width="5%"> <div class="checkbox checkbox-replace">
          <input type="checkbox" />
        </div>
      </th>
      <th colspan="4"> <div class="mail-select-options">Mark as Read</div>
        <div class="mail-pagination" colspan="2"> <strong>
        <?php   $current = ($data['currentpage']*$iDisplayLength); echo $current+1; ?>-
       <?php  echo $current+count($result); ?>
        
        </strong> <span>of {{$totalResults}}</span>
          <div class="btn-group"> <a  movetype="back" class="move_mail btn btn-sm btn-white"><i class="entypo-left-open"></i></a> <a  movetype="next" class="move_mail btn btn-sm btn-white"><i class="entypo-right-open"></i></a> </div>
        </div>
      </th>
    </tr>
  </thead>  
  <tbody>
    <?php
		 foreach($result as $result_data){ 
			$attachments  =  unserialize($result_data[3]);
			 ?>
    <tr class="<?php if($result_data[5]==0){echo "unread";} ?>"><!-- new email class: unread -->
      <td><div class="checkbox checkbox-replace">
          <input value="<?php  echo $result_data[0]; ?>" type="checkbox" />
        </div></td>
      <td class="col-name"><a href="#" class="star stared"> <i class="entypo-star"></i> </a> <a href="{{URL::to('/')}}/emailmessages/{{$result_data[0]}}/detail" class="col-name"><?php echo $result_data[1]; ?></a></td>
      <td class="col-subject"><a href="{{URL::to('/')}}/emailmessages/{{$result_data[0]}}/detail"> <?php echo $result_data[2]; ?> </a></td>
      <td class="col-options"><a href="{{URL::to('/')}}/emailmessages/{{$result_data[0]}}/detail">
        <?php if(count($attachments)>0){ ?>
        <i class="entypo-attach"></i></a>
        <?php } ?></td>
      <td class="col-time"><?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($result_data[4]))->diffForHumans();  ?></td>
    </tr>
    <?php } ?>
  </tbody>  
  <tfoot>
    <tr>
      <th width="5%"> <div class="checkbox checkbox-replace">
          <input type="checkbox" />
        </div>
      </th>
      <th colspan="4"> <div class="mail-pagination" colspan="2"> 
      <strong>
        <?php echo $current+1; ?>-
       <?php  echo $current+count($result); ?>
        
        </strong>
       <span>of {{$totalResults}}</span>
          <div class="btn-group"> <a movetype="back" class="move_mail btn btn-sm btn-white"><i class="entypo-left-open"></i></a> <a movetype="next" class="move_mail btn btn-sm btn-white"><i class="entypo-right-open"></i></a> </div>
        </div>
      </th>
    </tr>
  </tfoot>
</table>
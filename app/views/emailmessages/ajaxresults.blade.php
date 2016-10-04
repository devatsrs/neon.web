<table id="table-4" class="table mail-table">
  <thead>
    <tr>
      <th width="5%"> <div class="hidden checkbox checkbox-replace">
          <input type="checkbox" />
        </div>
      </th>
      <th colspan="4"> <div class="hidden mail-select-options">Mark as Read</div>
        <div class="mail-pagination" colspan="2">
        <?php if(count($result)>0){ ?>   
         <strong>
        <?php   $current = ($data['currentpage']*$iDisplayLength); echo $current+1; ?>-
       <?php  echo $current+count($result); ?>        
        </strong> <span>of {{$totalResults}}</span>         
          <div class="btn-group">
        <?php if($data['clicktype']=='back'){ ?>
    	<?php if(count($result)>=$iDisplayLength){ ?>        
        	<a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a> 
		<?php } }else{ ?>
			<a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a> 
		<?php } ?>
         <?php if($data['clicktype']=='next'){ ?>
    	<?php if(count($result)>=$iDisplayLength){ ?>        
        	<a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a> 
		<?php } }else{ ?>
			<a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a> 
		<?php } ?>
          </div>
          <?php } ?>
        </div>
      </th>
    </tr>
  </thead>  
  <tbody>
    <?php
		if(count($result)>0){
		 foreach($result as $result_data){ 
			$attachments  =  unserialize($result_data[3]);
			$AccountName  =  Account::where(array('AccountID'=>$result_data[5]))->pluck('AccountName');   
			 ?>
    <tr class="<?php if(isset($result_data[6]) && $result_data[6]==0){echo "unread";} ?>"><!-- new email class: unread -->
      <td><div class="hidden checkbox checkbox-replace">
          <input value="<?php  echo $result_data[0]; ?>" type="checkbox" />
        </div></td>
      <td class="col-name"><a href="{{URL::to('/')}}/emailmessages/{{$result_data[0]}}/detail" class="col-name"><?php if($boxtype=='inbox'){ echo $result_data[1]; }else{echo $AccountName;} ?></a></td>
      <td class="col-subject"><a href="{{URL::to('/')}}/emailmessages/{{$result_data[0]}}/detail">@if($boxtype == 'inbox' && $result_data[5]==0)<span class="label label-info">Unmatched</span> @endif<?php echo $result_data[2]; ?> </a></td>
      <td class="col-options"><a href="{{URL::to('/')}}/emailmessages/{{$result_data[0]}}/detail">
        <?php if(count($attachments)>0){ ?>
        <i class="entypo-attach"></i></a>
        <?php } ?></td>
      <td class="col-time"><?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($result_data[4]))->diffForHumans();  ?></td>
    </tr>
    <?php } }else{?>
    <tr><td align="center" colspan="5">No Result Found.</td></tr>
    <?php } ?>
  </tbody>  
  <tfoot>
    <tr>
      <th width="5%"> <div class="hidden checkbox checkbox-replace">
          <input type="checkbox" />
        </div>
      </th>
      <th colspan="4"> <div class="mail-pagination" colspan="2"> 
  	 <?php if(count($result)>0){ ?>
      <strong>
        <?php echo $current+1; ?>-
       <?php  echo $current+count($result); ?>
        
        </strong>
       <span>of {{$totalResults}}</span>    
         <div class="btn-group">
        <?php if($data['clicktype']=='back'){ ?>
    	<?php if(count($result)>=$iDisplayLength){ ?>        
        	<a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a> 
		<?php } }else{ ?>
			<a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a> 
		<?php } ?>
        
         <?php if($data['clicktype']=='next'){ ?>
    	<?php if(count($result)>=$iDisplayLength){ ?>        
        	<a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a> 
		<?php } }else{ ?>
			<a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a> 
		<?php } ?>
          </div>
        <?php } ?>  
        </div>
      </th>
    </tr>
  </tfoot>
</table>
<table id="table-4" class="table mail-table">
  <thead>
    <tr>
      <th width="5%"> <div class="@if($boxtype == 'sentbox')hidden @endif checkbox checkbox-replace">
          <input class="mail_select_checkbox" type="checkbox" />
        </div>
      </th>
      <th colspan="4"> <?php if(count($result)>0){ ?>
        <div class="mail-select-options">@if($boxtype == 'inbox') Mark as Read @elseif($boxtype == 'draftbox') Mark to Delete @endif</div>
        <div class="mail-pagination"> <strong>
          <?php   $current = ($data['currentpage']*$iDisplayLength); echo $current+1; ?>
          -
          <?php  echo $current+count($result); ?>
          </strong> <span>of {{$totalResults}}</span>
          <div class="btn-group">
            <?php if($data['clicktype']=='back'){ ?>
            <?php if(($current+1)>1){ ?>
            <a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a>
            <?php } ?>
            <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a>
            <?php } ?>
            <?php if($data['clicktype']=='next'){ ?>
             <a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a>
              <?php  if($totalResults!=($current+count($result))){ ?>           
            <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a>
            <?php } } ?>
          </div>
        </div>
        @if($boxtype == 'inbox' || $boxtype == 'draftbox')
        <div class="mail-pagination margin-left-mail dropdown">
          <button type="submit" data-toggle="dropdown" data-loading-text="Loading..." submit_value="{{Messages::Sent}}" class="btn btn-success submit_btn btn-icon dropdown-toggle"> Options <i class="entypo-mail"></i> </button>
          <ul class="dropdown-menu dropdown-red">
          @if($boxtype == 'inbox')
            <li> <a action_type="HasRead" action_value="1" class="clickable mailaction"> Mark as Read </a> </li>
            <li> <a action_type="HasRead" action_value="0" class="clickable mailaction" > Mark as Unread </a> </li>
            @elseif($boxtype == 'draftbox')
            <li> <a action_type="Delete" action_value="1" class="clickable mailaction" > Delete </a> </li>
            @endif
          </ul>
        </div>
         @endif
        <?php } ?>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
		if(count($result)>0){
		 foreach($result as $result_data){ 
			$attachments  =  !empty($result_data->AttachmentPaths)?unserialize($result_data->AttachmentPaths):array();
			//$AccountName  =  Account::where(array('AccountID'=>$result_data[5]))->pluck('AccountName');   
			if(isset($result_data->EmailTo)){ //sentbox
				$AccountName  =  Messages::GetAccountTtitlesFromEmail($result_data->EmailTo);
			}
			if($boxtype == 'draftbox'){
				$url = URL::to('/').'/emailmessages/'.$result_data->AccountEmailLogID.'/compose';
			} else {
				$url = URL::to('/').'/emailmessages/'.$result_data->AccountEmailLogID.'/detail';
			}
			 ?>
    <tr class="<?php if(isset($result_data->HasRead) && $result_data->HasRead==0){echo "unread";} ?>"><!-- new email class: unread -->
      <td><div class="@if($boxtype == 'sentbox')hidden @endif checkbox checkbox-replace">
          <input value="<?php  echo $result_data->AccountEmailLogID; ?>" class="mailcheckboxes" type="checkbox" />
        </div></td>
      <td class="col-name"><a target="_blank" href="{{$url}}" class="col-name">
        <?php if($boxtype=='inbox'){ echo ShortName($result_data->EmailfromName,20); }else{echo ShortName($AccountName,20);} ?>
        </a></td>
      <td class="col-subject"><a target="_blank" href="{{$url}}">@if($boxtype == 'inbox' && $result_data->AccountID==0)<span class="label label-info">Unmatched</span> @endif<?php echo ShortName($result_data->Subject,40); ?> </a></td>
      <td class="col-options"><?php if(count($attachments)>0 && is_array($attachments)){ ?>
        <a target="_blank" href="{{$url}}"><i class="entypo-attach"></i></a>
        <?php } ?></td>
      <td class="col-time"><?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($result_data->created_at))->diffForHumans();  ?></td>
    </tr>
    <?php } }else{?>
    <tr>
      <td align="center" colspan="5">No Result Found.</td>
    </tr>
    <?php } ?>
  </tbody>
  <tfoot>
    <tr>
      <th colspan="5"> 
          <?php if(count($result)>0){ ?>
          <div class="mail-pagination" colspan="2">
          <strong> <?php echo $current+1; ?>-
          <?php  echo $current+count($result); ?>
          </strong> <span>of {{$totalResults}}</span>
          <div class="btn-group">
            <?php if($data['clicktype']=='back'){ ?>
            <?php if(($current+1)>1){ ?>
            <a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a>
            <?php } ?>
            <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a>
            <?php } ?>
            <?php if($data['clicktype']=='next'){ ?>
             <a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a>
              <?php  if($totalResults!=($current+count($result))){ ?>           
            <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a>
            <?php } } ?>
          </div>
          </div>
          <?php } ?>
        
      </th>
    </tr>
  </tfoot>
</table>
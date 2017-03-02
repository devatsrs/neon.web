<table id="table-4" class="table mail-table">
  <thead>
    <tr>
      <th width="5%"> <div class="@if($boxtype == Messages::sentbox)hidden @endif checkbox checkbox-replace">
          <input class="mail_select_checkbox" type="checkbox" />
        </div>
      </th>
      <th colspan="4">@if($boxtype == Messages::inbox)<div class="mail-select-options"> <a show_all_read="0" class="btn-apply btn @if(isset($data['show_all_read']) && $data['show_all_read']==0) btn-blue @else btn-default @endif show_all_read">All</a> <a show_all_read="1" class="btn-apply btn @if(isset($data['show_all_read']) && $data['show_all_read']==1) btn-blue @else btn-default @endif show_all_read">Unread</a> </div>@endif  
       <?php if(count($result)>0){ ?>        
        <div class="mail-pagination">
      @if($boxtype == Messages::inbox)  <button type="button" class="btn btn-success mailaction tooltip-primary" data-toggle="tooltip" data-placement="top"  data-original-title="Apply"><i class="entypo-check"></i></button> @endif
      @if($boxtype == Messages::draftbox)  
      <a  action_type="Delete" action_value="1" data-toggle="tooltip" data-placement="top"  data-original-title="Delete" class="btn btn-default mailaction tooltip-primary"> <i class="fa fa-trash"></i> </a>
       @endif
         <strong>
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
            <?php if(($current+1)>1){ ?>
             <a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a>
              <?php }  if($totalResults!=($current+count($result))){ ?>           
            <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a>
            <?php } } ?>
          </div>
        </div>
        @if($boxtype == Messages::inbox)
         <div class="mail-pagination margin-left-mail">
                  <select id="selectmailaction" name="selectmailaction" action_type="HasRead" class="select2 selectmailaction">
                  <option value="">Select</option>
                  <option value="1">Mark as Read</option>
                  <option value="0">Mark as Unread</option>
                  </select> 
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
			if($boxtype == Messages::draftbox){
				$url = URL::to('/').'/emailmessages/'.$result_data->AccountEmailLogID.'/compose';
			} else {
				$url = URL::to('/').'/emailmessages/'.$result_data->AccountEmailLogID.'/detail';
			}
			 ?>
    <tr class="<?php if(isset($result_data->HasRead) && $result_data->HasRead==0){echo "unread";} ?>"><!-- new email class: unread -->
      <td><div class="@if($boxtype == Messages::sentbox)hidden @endif checkbox checkbox-replace">
          <input value="<?php  echo $result_data->AccountEmailLogID; ?>" class="mailcheckboxes" type="checkbox" />
        </div></td>
      <td class="col-name"><a target="_blank" href="{{$url}}" class="col-name">
        <?php if($boxtype==Messages::inbox){ echo ShortName($result_data->EmailfromName,30); }else{echo ShortName($AccountName,30);} ?>
        </a></td>
      <td class="col-subject"><a target="_blank" href="{{$url}}">@if($boxtype == Messages::inbox && $result_data->AccountID==0)<span class="label label-info">Unmatched</span> @endif<?php echo ShortName($result_data->Subject,50); ?> </a></td>
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
            <?php if(($current+1)>1){ ?>
             <a  movetype="back" class="move_mail back btn btn-sm btn-white"><i class="entypo-left-open"></i></a>
              <?php }  if($totalResults!=($current+count($result))){ ?>           
            <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a>
            <?php } } ?>
          </div>
          </div>
          <?php } ?>
        
      </th>
    </tr>
  </tfoot>
</table>
<input type="hidden" name="SidebarCounterInbox" id="SidebarCounterInbox" value="{{$TotalUnreads}}" />
<input type="hidden" name="SidebarCounterDraft" id="SidebarCounterDraft" value="{{$TotalDraft}}" />
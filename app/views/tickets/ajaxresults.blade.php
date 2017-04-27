<table id="table-4" class="table table-bordered datatable dataTable">
  <!-- mail table header -->
  <thead>
          <tr>
            <th width="1%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
            <th colspan="3"> <?php if(count($result)>0){ ?>                  
            <div class="mail-select-options"><span class="pull-left paginationTicket">{{Form::select('page',$pagination,$iDisplayLength,array("class"=>"select2 small","id"=>"per_page"))}} </span><span class="pull-left per_page">records per page</span> </div>
              <div class="pull-right">
                <div class="hidden mail-pagination"> <strong>
                  <?php   $current = ($data['currentpage']*$iDisplayLength); echo $current+1; ?>
                  -
                  <?php  echo $current+count($result); ?>
                  </strong> <span>of {{$totalResults}}</span>
                  <div class="btn-group">
                    <?php if(count($result)>=$iDisplayLength){ ?>
                    <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a>
                    <?php } ?>
                  </div>
                </div>
                <div class="pull-left btn-group">
                <button type="button" data-toggle="dropdown" class="btn  dropdown-toggle  btn-green">Export <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown_sort dropdown-green" role="menu">    
                    <li><a class="export_btn export_type" action_type="csv" href="#"> CSV</a> </li>
                    <li><a class="export_btn export_type" action_type="xlsx"  href="#">  EXCEL</a> </li>
                  </ul>
                </div>
                <div class="pull-right sorted btn-group">
                  <button type="button" class="btn btn-green dropdown-toggle" data-toggle="dropdown"> Sorted by {{$Sortcolumns[$data['iSortCol_0']]}} <span class="caret"></span> </button>
                  <ul class="dropdown-menu dropdown_sort dropdown-green" role="menu">
                    <?php foreach($Sortcolumns as $key => $SortcolumnsData){ ?>
                    <li><a class="sort_fld @if($key==$data['iSortCol_0']) checked @endif" action_type="sort_field" action_value="{{$key}}"   href="#"> <i class="entypo-check" @if($key!=$data['iSortCol_0']) style="visibility:hidden;" @endif ></i> {{@$SortcolumnsData}}</a></li>
                    <?php } ?>
                    <li class="divider"></li>
                    <li><a class="sort_type @if($data['sSortDir_0']=='asc') checked @endif" action_type="sort_type" action_value="asc" href="#"> <i class="entypo-check" @if($data['sSortDir_0']!='asc') style="visibility:hidden;" @endif  ></i> Ascending</a> </li>
                    <li><a class="sort_type @if($data['sSortDir_0']=='desc') checked @endif" action_type="sort_type" action_value="desc" href="#"> <i class="entypo-check" @if($data['sSortDir_0']!='desc') style="visibility:hidden;" @endif  ></i> Descending</a> </li>
                  </ul>
                </div>
              </div>
              <?php } ?>
            </th>
          </tr>
        </thead>
  <!-- email list -->
  <tbody>
    <?php
		  if(count($result)>0){
		 foreach($result as $result_data){ 
			 ?>
          <tr><!-- new email class: unread -->
              <td class="@if(!empty($result_data->PriorityValue)) borderside borderside{{$result_data->PriorityValue}} @endif"><div class="checkbox "><input type="checkbox" name="checkbox[]" value="{{$result_data->TicketID}}" class="rowcheckbox" ></div></td>
            <td class="col-name"><a target="_blank" href="{{URL::to('/')}}/tickets/{{$result_data->TicketID}}/detail" class="col-name"> <span class="blue_link"> <?php echo ShortName($result_data->Subject,100); ?></span> </a>
            <span class="ticket_number"> #<?php echo $result_data->TicketID; ?></span>
                {{get_ticket_response_due_label($result_data,[ "skip"=>[$ResolvedTicketStatus,$ResolvedTicketStatus]])}}
              <br>
             <a target="_blank" href="@if(!empty($result_data->ACCOUNTID)) {{URL::to('/')}}/accounts/{{$result_data->ACCOUNTID}}/show @elseif(!empty($result_data->ContactID)) contacts/{{$result_data->ContactID}}/show @else # @endif" class="col-name">Requester: <?php echo $result_data->Requester; ?></a><br>
              <span> Created: <?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($result_data->created_at))->diffForHumans();  ?>
                  @if($result_data->DueDate != '')
                  , {{get_ticket_due_date_human_readable($result_data, [ "skip"=>[$ResolvedTicketStatus,$ResolvedTicketStatus]])}}
                  @endif
                  {{SowCustomerAgentRepliedDate($result_data)}}
              </span> </td>
            <td  align="left" class="col-time"><div>Status:<span>&nbsp;&nbsp;&nbsp;<?php echo $result_data->TicketStatus; ?></span></div>
              <div>Priority:<span>&nbsp;&nbsp;<?php echo $result_data->PriorityValue; ?></span></div>
              <div>Agent:<span>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $result_data->Agent; ?></span></div>
              <div>Group:<span>&nbsp;&nbsp;&nbsp;<?php echo $result_data->GroupName; ?></span></div></td>
          </tr>
          <?php } }else{ ?>
    <tr>
      <td align="center" colspan="3">No Result Found.</td>
    </tr>
    <?php } ?>
  </tbody>
  <!-- mail table footer -->
  <tfoot>
    <tr>
      <th colspan="3"> 
          <?php if(count($result)>0){ ?>
          <div class="mail-pagination">
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
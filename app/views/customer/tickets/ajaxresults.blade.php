<table id="table-4" class="table mail-table">
  <!-- mail table header -->
  <thead>
          <tr>
            <th colspan="2"> <?php if(count($result)>0){ ?>              
              <div class="mail-select-options"> <span class="pull-left paginationTicket"> {{Form::select('page',$pagination,$iDisplayLength,array("class"=>"select2 small","id"=>"per_page"))}} </span><span class="pull-right per_page">{{str_replace("_MENU_","", cus_lang("TABLE_LBL_RECORDS_PER_PAGE"))}}</span> </div>
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
                <div class="pull-left  btn-group">
                <button type="button" data-toggle="dropdown" class="btn  dropdown-toggle  btn-green">@lang('routes.BUTTON_EXPORT_CAPTION') <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown_sort dropdown-green" role="menu">    
                    <li><a class="export_btn export_type" action_type="csv" href="#"> @lang('routes.BUTTON_EXPORT_CSV_CAPTION')</a> </li>
                    <li><a class="export_btn export_type" action_type="xlsx"  href="#">  @lang('routes.BUTTON_EXPORT_EXCEL_CAPTION')</a> </li>
                  </ul>
                </div>
                <div class="pull-right sorted btn-group">
                  <button type="button" class="btn btn-green dropdown-toggle" data-toggle="dropdown"> @lang('routes.CUST_PANEL_PAGE_TICKETS_TABLE_SORTED_BY') @lang('routes.CUST_PANEL_PAGE_TICKETS_TABLE_SORTED_BY_COLUMNS_'. strtoupper($data['iSortCol_0'])) <span class="caret"></span> </button>
                  <ul class="dropdown-menu dropdown_sort dropdown-green" role="menu">
                    <?php foreach($Sortcolumns as $key => $SortcolumnsData){ ?>
                    <li><a class="sort_fld @if($key==$data['iSortCol_0']) checked @endif" action_type="sort_field" action_value="{{$key}}"   href="#"> <i class="entypo-check" @if($key!=$data['iSortCol_0']) style="visibility:hidden;" @endif ></i> @lang('routes.CUST_PANEL_PAGE_TICKETS_TABLE_SORTED_BY_COLUMNS_'.strtoupper($key))</a></li>
                    <?php } ?>
                    <li class="divider"></li>
                    <li><a class="sort_type @if($data['sSortDir_0']=='asc') checked @endif" action_type="sort_type" action_value="asc" href="#"> <i class="entypo-check" @if($data['sSortDir_0']!='asc') style="visibility:hidden;" @endif  ></i> @lang('routes.CUST_PANEL_PAGE_TICKETS_TABLE_SORTED_BY_COLUMNS_ASCENDING')</a> </li>
                    <li><a class="sort_type @if($data['sSortDir_0']=='desc') checked @endif" action_type="sort_type" action_value="desc" href="#"> <i class="entypo-check" @if($data['sSortDir_0']!='desc') style="visibility:hidden;" @endif  ></i> @lang('routes.CUST_PANEL_PAGE_TICKETS_TABLE_SORTED_BY_COLUMNS_DESCENDING')</a> </li>
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
                $ticket_data = TicketsTable::find($result_data->TicketID);
                $TicketfieldsValues = TicketfieldsValues::find($ticket_data->Status);
			 ?>
          <tr><!-- new email class: unread -->
            <td class="col-name @if(!empty($result_data->PriorityValue)) borderside borderside{{$result_data->PriorityValue}} @endif">
                <a target="_blank" href="{{URL::to('/')}}/customer/tickets/{{$result_data->TicketID}}/detail" class="col-name"> <span class="blue_link">
                    <?php echo ShortName(emailHeaderDecode($result_data->Subject),100); ?></span>
                    <span class="ticket_number"> #<?php echo $result_data->TicketID; ?></span>
                </a>
            <br>
             <a class="col-name">{{cus_lang('CUST_PANEL_PAGE_TICKETS_TAB_REQUESTER')}} <?php echo $result_data->Requester; ?></a><br>
              <span> {{cus_lang('CUST_PANEL_PAGE_TICKETS_TAB_CREATED')}} <?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($result_data->created_at))->diffForHumans();  ?></span></td>
            <td  align="left" class="col-time">
                <div>{{cus_lang('CUST_PANEL_PAGE_TICKETS_TAB_STATUS')}}<span>&nbsp;&nbsp;{{cus_lang("CUST_PANEL_PAGE_TICKET_FIELDS_".$TicketfieldsValues->FieldsID."_VALUE_".$TicketfieldsValues->ValuesID)}}</span></div>
                <div>{{cus_lang('CUST_PANEL_PAGE_TICKETS_TAB_PRIORITY')}}<span>&nbsp;&nbsp;{{cus_lang("CUST_PANEL_PAGE_TICKET_FIELDS_PRIORITY_VAL_".$result_data->PriorityValue)}}</span></div>
              </td>
          </tr>
          <?php } }else{ ?>
    <tr>
      <td align="center" colspan="2">@lang('routes.MESSAGE_DATA_NOT_AVAILABLE')</td>
    </tr>
    <?php } ?>
  </tbody>
  <!-- mail table footer -->
  <tfoot>
    <tr>
      <th colspan="2"> 
          <?php if(count($result)>0){ ?>
          <div class="mail-pagination leftsideview">
          <span>
              <?php
                  $showing_records = cus_lang("TABLE_LBL_SHOWING_RECORDS");
                  $showing_records = str_replace("_START_",$current+1, $showing_records);
                  $showing_records = str_replace("_END_",$current+count($result), $showing_records);
                  $showing_records = str_replace("_TOTAL_",$totalResults, $showing_records);
                 echo $showing_records;
              ?>
          </span>

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
<table id="table-4" class="table mail-table">
  <!-- mail table header -->
  <thead>
          <tr>
            <th colspan="3"> <?php if(count($result)>0){ ?>
              <div class="mail-select-options" style="width:80px;s"> <span> {{Form::select('page',array("10"=>"10","25"=>"25","50"=>"50","100"=>"100"),$iDisplayLength,array("class"=>"select2","id"=>"per_page"))}} </span> </div>
              <span>records per page</span>
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
                <div class="pull-right btn-group">
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
      <td class="col-name  @if(!empty($result_data->PriorityValue)) borderside borderside{{$result_data->PriorityValue}} @endif"><a target="_blank" href="{{URL::to('/')}}/customer/tickets/{{$result_data->TicketID}}/detail" class="col-name"> <span class="blue_link"> <?php echo ShortName($result_data->Subject,100); ?></span> <span class="ticket_number"> #<?php echo $result_data->TicketID; ?></span><br>       
       </a>
         <a href="{{URL::to('/')}}/customer/tickets/user/{{$result_data->TicketID}}/detail" class="col-name">Requester: <?php echo $result_data->Requester; ?></a><br>
        Created: <?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($result_data->created_at))->diffForHumans();  ?> 
       </td>
          <td><?php if($result_data->CustomerResponse==$result_data->Requester){echo "<span class='customerresponded'>CUSTOMER RESPONDED</span>";}else{echo "<span class='responsedue'>RESPONSE DUE</span>";} ?></td>
      <td align="left" class="col-time">
        <div>Status:<span>&nbsp;&nbsp;<?php echo $result_data->TicketStatus; ?></span></div>
        <div>Priority:<span>&nbsp;&nbsp;<?php echo $result_data->PriorityValue; ?></span></div>        
       </td>
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
@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Emails</strong> </li>
</ol>
<h3>Emails</h3>
@include('includes.errors')
@include('includes.success')
<div class="mail-env"> 
  
  <!-- compose new email button -->
  <div class="mail-sidebar-row visible-xs"> <a href="#" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i> </a> </div>
  
  <!-- Mail Body -->
  <div class="mail-body">
    <div class="mail-header"> 
      <!-- title -->
      <h3 class="mail-title"> Inbox <span class="count">({{$TotalUnreads}})</span> </h3>
      
      <!-- search -->
      <form method="get" role="form" class="mail-search">
        <div class="input-group">
          <input type="text" class="form-control" name="s" placeholder="Search for mail..." />
          <div class="input-group-addon"> <i class="entypo-search"></i> </div>
        </div>
      </form>
    </div>
    
    <!-- mail table -->
    <div class="inbox">
      <table id="table-4" class="table mail-table">
        <!-- mail table header -->
        <thead>
          <tr>
            <th width="5%"> <div class="checkbox checkbox-replace">
                <input  type="checkbox" />
              </div>
            </th>
            <th colspan="4"> <div class="mail-select-options">Mark as Read</div>
              <div class="mail-pagination" colspan="2"> <strong>
                <?php   $current = ($data['currentpage']*$iDisplayLength); echo $current+1; ?>
                -
                <?php  echo $current+count($result); ?>
                </strong> <span>of {{$totalResults}}</span>
                <div class="btn-group"> <a  movetype="back" class="move_mail btn btn-sm btn-white"><i class="entypo-left-open"></i></a> <a  movetype="next" class="move_mail btn btn-sm btn-white"><i class="entypo-right-open"></i></a> </div>
              </div>
            </th>
          </tr>
        </thead>
        
        <!-- email list -->
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
        
        <!-- mail table footer -->
        <tfoot>
          <tr>
            <th width="5%"> <div class="checkbox checkbox-replace">
                <input type="checkbox" />
              </div>
            </th>
            <th colspan="4"> <div class="mail-pagination" colspan="2"> <?php echo $current+1; ?>-
                <?php  echo $current+count($result); ?>
                <span>of {{$totalResults}}</span>
                <div class="btn-group"> <a  movetype="back" class="move_mail btn btn-sm btn-white"><i class="entypo-left-open"></i></a> <a  movetype="next" class="move_mail btn btn-sm btn-white"><i class="entypo-right-open"></i></a> </div>
              </div>
            </th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  
  <!-- Sidebar -->
  <div class="mail-sidebar"> 
    
    <!-- compose new email button -->
    <div style="visibility:hidden;">
      <div  class="mail-sidebar-row hidden-xs"> <a href="#" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i> </a> </div>
    </div>
    <!-- menu -->
    <ul class="mail-menu">
      <li class="active"> <a href="#"> <span class="badge badge-danger pull-right">{{$TotalUnreads}}</span> Inbox </a> </li>
      <li> <a href="#"> <span class="badge badge-gray pull-right">1</span> Sent </a> </li>
      <li class="hidden"> <a href="#"> Drafts </a> </li>
      <li class="hidden"> <a href="#"> <span class="badge badge-gray pull-right">1</span> Spam </a> </li>
      <li class="hidden"> <a href="#"> Trash </a> </li>
    </ul>
    <!-- menu --> 
  </div>
</div>
<script>
$(document).ready(function(e) {
	var currentpage 	= 	0;
	var next_enable 	= 	1;
	var back_enable 	= 	1;
	var per_page 		= 	<?php echo $iDisplayLength; ?>;
	var total			=	<?php echo $totalResults; ?>;
	var clicktype		=	'';
	var ajax_url 		= 	baseurl+'/emailmessages/ajex_result';
	
	$(document).on('click','.move_mail',function(){
		var clicktype = $(this).attr('movetype');	
        ShowResult(clicktype);
    });
	
	function ShowResult(clicktype){
	
		 $.ajax({
					url: ajax_url,
					type: 'POST',
					dataType: 'html',
					async :false,
					data:{currentpage:currentpage,per_page:per_page,total:total,clicktype:clicktype},
					success: function(response) {
						if(response)
						{
							 $('.inbox').html('');
							 $('.inbox').html(response);	
							 if(clicktype=='next')
							 {
								currentpage =  currentpage+1;
							 }
							 else
							 {
								currentpage =  currentpage-1;
							 } 		 console.log(currentpage);			 	
							 replaceCheckboxes();
						}
					
					}
				});	
			
	}
});
</script> 
@stop 
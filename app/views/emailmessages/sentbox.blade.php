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
      <h3 class="mail-title"> Sent Mail</h3>
      
      <!-- search -->
      <form method="get" role="form" id="mail-search" class="mail-search">
        <div class="input-group">
          <input type="text" class="form-control"  name="MailSearchStr" id="MailSearchStr" placeholder="Search for mail..." />
          <div class="input-group-addon clickable"> <i class="entypo-search"></i> </div>
        </div>
      </form>
    </div>
    
    <!-- mail table -->
    <div class="sentbox">
      <table id="table-4" class="table mail-table">
        <!-- mail table header -->
        <thead>
          <tr>
            <th width="5%"> <div class="hidden checkbox checkbox-replace">
                <input  type="checkbox" />
              </div>
            </th>
            <th colspan="4"> <div class="hidden mail-select-options">Mark as Read</div>
             <?php if(count($result)>0){ ?>
              <div class="mail-pagination" colspan="2"> <strong>
                <?php   $current = ($data['currentpage']*$iDisplayLength); echo $current+1; ?>
                -
                <?php  echo $current+count($result); ?>
                </strong> <span>of {{$totalResults}}</span>
                <div class="btn-group">              
              <?php if(count($result)>=$iDisplayLength){ ?>  <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a> <?php } ?>
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
			$attachments  =  unserialize($result_data[3]);
			//$AccountName  =  Account::where(array('AccountID'=>$result_data[6]))->pluck('AccountName');   
			$AccountName  =  Messages::GetAccountTtitlesFromEmail($result_data[7]);
			 ?>
          <tr><!-- new email class: unread -->
            <td><div class="hidden checkbox checkbox-replace">
                <input value="<?php  echo $result_data[0]; ?>" type="checkbox" />
              </div></td>
            <td class="col-name"><a target="_blank" href="{{URL::to('/')}}/emailmessages/{{$result_data[0]}}/detail" class="col-name"><?php echo $AccountName; ?></a></td>
            <td class="col-subject"><a target="_blank" href="{{URL::to('/')}}/emailmessages/{{$result_data[0]}}/detail"> <?php echo $result_data[2]; ?> </a></td>
            <td class="col-options">
            <?php if(count($attachments)>0 && is_array($attachments)){ ?>
            <a target="_blank" href="{{URL::to('/')}}/emailmessages/{{$result_data[0]}}/detail"><i class="entypo-attach"></i></a>              
              <?php } ?></td>
            <td class="col-time"><?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($result_data[4]))->diffForHumans();  ?></td>
          </tr>
          <?php } }else{ ?>
          <tr><td align="center" colspan="5">No Result Found.</td></tr>
          <?php } ?>
        </tbody>
        
        <!-- mail table footer -->
        <tfoot>
          <tr>
            <th width="5%"> <div class="hidden checkbox checkbox-replace">
                <input type="checkbox" />
              </div>
            </th>
            <th colspan="4">
            <?php if(count($result)>0){ ?>
             <div class="mail-pagination" colspan="2"> <?php echo $current+1; ?>-
                <?php  echo $current+count($result); ?>
                <span>of {{$totalResults}}</span>
                <div class="btn-group">
			 <?php if(count($result)>=$iDisplayLength){ ?>  <a  movetype="next" class="move_mail next btn btn-sm btn-white"><i class="entypo-right-open"></i></a> <?php } ?>
                </div>
              </div>
              <?php } ?>
            </th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  
  <!-- Sidebar -->
  @include("emailmessages.mail_sidebar") 
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
	var boxtype			=	'sentbox';
	var EmailCall		=	"{{Messages::Sent}}";
	var SearchStr		=	'';
	$(document).on('click','.move_mail',function(){
		var clicktype = $(this).attr('movetype');	
        ShowResult(clicktype);
    });
	
	$(document).on('click','.input-group-addon',function(e){
		$('#mail-search').submit();		
    });
	
	$(document).on('submit','#mail-search',function(e){		 
		e.stopImmediatePropagation();
		e.preventDefault();
		SearchStr   = $('#MailSearchStr').val();
		currentpage = -1;
		clicktype   = 'next';
		ShowResult(clicktype);
		return false;		
    });	
	
	function ShowResult(clicktype){
	SearchStr   = $('#MailSearchStr').val();
	
		 $.ajax({
					url: ajax_url,
					type: 'POST',
					dataType: 'html',
					async :false,
					data:{currentpage:currentpage,per_page:per_page,total:total,clicktype:clicktype,boxtype:boxtype,SearchStr:SearchStr,EmailCall:EmailCall},
					success: function(response) {
						if(response.length>0)
						{
							if(isJson(response))
							{
								jsonstr =  JSON.parse(response);
								$('#table-4 tbody').html('<tr><td align="center" colspan="5">'+jsonstr.result+'</td></tr>');
								
								if(clicktype=='next')
								 {
									$('.next').addClass('disabled');
								 }
								 else
								 {
									$('.back').addClass('disabled');
								 }
								 $('.mail-pagination').hide();
								return false;
							}
							
							 $('.sentbox').html('');
							 $('.sentbox').html(response);	
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
						else
						{
							if(clicktype=='next')
							 {
								$('.next').addClass('disabled');
							 }
							 else
							 {
								$('.back').addClass('disabled');
							 }						
						}
					
					}
				});	
			
	}
});
</script> 
@stop 
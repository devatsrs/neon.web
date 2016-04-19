@extends('layout.main')
@section('content')
<link rel="stylesheet" href="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.css">
<div  style="min-height: 1050px;">
  <ol class="breadcrumb bc-3">
    <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
    <li> <a href="{{URL::to('accounts')}}">Accounts</a> </li>
    <li class="active"> <strong>View Account</strong> </li>
  </ol>
  @include('includes.errors')
  @include('includes.success')
  <p style="text-align: right;">
      <a href="{{ URL::to('accounts/'.$account->AccountID.'/edit')}}" class="btn btn-primary btn-sm btn-icon icon-left">
        <i class="entypo-floppy"></i>
        Edit Account
    </a>
   </p>
  <?php $Account = $account;?>
  @include('accounts.errormessage')
  <div id="account-timeline">
    <div class="row">
      <div class="col-md-10 clearfix">
        <h2>{{$account->AccountName}} </h2>
      </div>
    </div>
    <section>
      <div id="contact-column" class="about-account col-md-3 col-sm-12 col-xs-12 pull-left">
		<!--Account card start -->
        @if(isset($Account_card) && count($Account_card)>0)
        <div class="gridview">
        <ul class="clearfix grid col-md-12">
    <li>
      <div class="box clearfix ">
        <div class="col-sm-12 header padding-left-1"> <span class="head">{{$Account_card[0]->AccountName}}</span><br>
          <span class="meta complete_name"> </span></div>
        <div class="col-sm-6 padding-0">
          <div class="block">
            <div class="meta">Email</div>
            <div><a class="sendemail" href="javascript:void(0)">{{$Account_card[0]->Email}}</a></div>
          </div>
          <div class="cellNo">
            <div class="meta">Phone</div>
            <div><a href="tel:{{$Account_card[0]->Phone}}">{{$Account_card[0]->Phone}}</a></div>
          </div>
          <div>
            <div class="meta">Outstanding</div>
            <div>{{$Account_card[0]->OutStandingAmount}}</div>
          </div>
        </div>
        <div class="col-sm-6 padding-0">
          <div class="block">
            <div class="meta">Address</div>
            <div class="address account-address">
           <?php isset($Account_card[0]->Address1) ? $Account_card[0]->Address1."<br>":""; ?>
           <?php isset($Account_card[0]->Address2) ? $Account_card[0]->Address2."<br>":""; ?>
           <?php isset($Account_card[0]->Address3) ? $Account_card[0]->Address3."<br>":""; ?>           
           <?php isset($Account_card[0]->City) ? $Account_card[0]->City."<br>":""; ?>
           <?php isset($Account_card[0]->PostCode) ? $Account_card[0]->PostCode."<br>":""; ?>
           <?php isset($Account_card[0]->Country) ? $Account_card[0]->Country."<br>":""; ?>
		</div>
          </div>
        </div>
        <div class="col-sm-11 padding-0 action"> <a class="btn-default btn-sm label padding-3" href="{{ URL::to('accounts/'.$account->AccountID.'/edit')}}">Edit </a>&nbsp;<a class="btn-default btn-sm label padding-3" href="{{ URL::to('accounts/'.$account->AccountID.'/show')}}">View </a>&nbsp;<a class="btn-warning btn-sm label padding-3" href="{{ URL::to('customers_rates/'.$account->AccountID)}}">Customer</a>&nbsp;<a class="btn-info btn-sm label padding-3" href="{{ URL::to('vendor_rates/'.$account->AccountID)}}">Vendor</a>

        </div>
      </div>
    </li>
  </ul>
</div>
@endif
        <!--Account card end -->       
        
      </div>      
      <div id="text-boxes" class="timeline col-md-9 col-sm-12 col-xs-12  upper-box">
        <div class="row">
          <ul id="tab-btn" class="interactions-list">
            <li id="1" class="interactions-tab"> <a href="#Note" class="interaction-link note" onclick="showDiv('box-1',1)"><i class="entypo-doc-text"></i>New Note</a> </li>
            <li id="2" class="interactions-tab"> <a href="#task" class="interaction-link activity" onclick="showDiv('box-3',2)"><i class="entypo-doc-text"></i>Create Task</a> </li>
            <li id="3" class="interactions-tab"> <a href="#schedule" class="interaction-link task" onclick="showDiv('box-4',3)"><i class="entypo-phone"></i>Log Activity</a> </li>
            <li id="4" class="interactions-tab"> <a href="#email" class="interaction-link task" onclick="showDiv('box-2',4)"><i class="entypo-mail"></i>Email</a> </li>
          </ul>
        </div>
        <div class="row margin-top-5" id="box-1">
          <div class="col-md-12">
            <form role="form" id="notes-from" action="{{URL::to('accounts/'.$account->AccountID.'/store_note/')}}" method="post">
              <div class="form-group">
                <textarea id="note-content" class="form-control wysihtml5" data-stylesheet-url="<?php echo URL::to('/'); ?>/assets/css/wysihtml5-color.css" name="Note">
                </textarea>
              </div>
              <div class="form-group end-buttons-timeline"> <!-- <a data-loading-text="Loading..." id="save-note" class=" pull-right save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Save</a>-->
                <button id="save-note" class="pull-right save btn btn-primary btn-sm btn-icon icon-left" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
              </div>
            </form>
          </div>
        </div>
        <div class="row no-display margin-top-5" id="box-2" style="margin-bottom: 5px;">
          <div class="col-md-12">
            <div class="mail-compose">
              <form method="post" id="email-from" role="form" enctype="multipart/form-data">
                <div class="form-group">
                  <label for="to">To:</label>
                  <!--{{ Form::select('email-to', USer::getUserIDList(), '', array("class"=>"select2","id"=>"email-to","tabindex"=>"1")) }}-->
                  <input type="text" class="form-control" value="{{$account->Email}}" id="email-to" name="email-to" tabindex="1"  />
                  <div class="field-options"> <a href="javascript:;" onclick="$(this).hide(); $('#cc').parent().removeClass('hidden'); $('#cc').focus();">CC</a> <a href="javascript:;" onclick="$(this).hide(); $('#bcc').parent().removeClass('hidden'); $('#bcc').focus();">BCC</a> </div>
                </div>
                <div class="form-group hidden">
                  <label for="cc">CC:</label>
                  {{ Form::select('cc[]', USer::getUserIDListOnly(), '', array("class"=>"select2","Multiple","id"=>"cc","tabindex"=>"2")) }}
                </div>
                <div class="form-group hidden">
                  <label for="bcc">BCC:</label>
                   {{ Form::select('bcc[]', USer::getUserIDListOnly(), '', array("class"=>"select2","Multiple","id"=>"bcc","tabindex"=>"3")) }}
                </div>
                <div class="form-group">
                  <label for="subject">Subject:</label>
                  <input type="text" class="form-control" id="subject" name="Subject" tabindex="4" />
                </div>
                <div class="compose-message-editor">
                  <label for="Email">Email:</label>
                  <textarea name="Message" id="Message" class="form-control autogrow" id="Textarea4" placeholder="I will grow as you type new lines." style="height: 48px; overflow: hidden; word-wrap: break-word; resize: none;"></textarea>
                  <p class="comment-box-options">
                                        <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)">
                                            <i class="entypo-attach"></i>
                                        </a>
                               </p>        
                </div>
                <div class="form-group">
                  <input id="filecontrole" type="file" name="emailattachment[]" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden" multiple data-label="<i class='entypo-attach'></i>Attachments" />

                </div>
                <div class="form-group end-buttons-timeline">
                                                                                          
                 <a href="#" id="save-mail" class=" pull-right save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-mail"></i>Send</a> </div>
              </form>
            </div>
          </div>
        </div>
        <div class="row no-display margin-top-5" id="box-3">
          <div class="col-md-12">
            <form role="form" method="post">
              <div class="form-group">
                <label for="to">Task Name:</label>
                <input type="text" id="task-name" class="form-control" id="Text2" tabindex="1" />
              </div>
              <div class="form-group">
                <label for="to">Task Assign to:</label>
                <!--<input type="text" class="form-control" id="Text1" value="Sumera Saeed" tabindex="1" />-->
                <select id="task-assign-to" class="form-control">
                  <option></option>
                  <option selected>Sumera Saeed</option>
                  <option>Aamir Saaed</option>
                </select>
              </div>
              <div class="form-group">
                <label for="to">Description:</label>
                <textarea class="form-control autogrow" id="task-description" placeholder="I will grow as you type new lines." style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 48px;"></textarea>
              </div>
              <div class="form-group"> <a style="margin-bottom: 10px;" href="#" id="save-task" class="pull-right save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Save</a> </div>
            </form>
          </div>
        </div>
        <div class="row no-display margin-top-5" id="box-4">
          <div class="col-md-12">
            <form role="form" method="post">
              <div class="form-group">
                <label for="to">Log Call:</label>
                <input type="text" class="form-control" id="Log-call-number" tabindex="1" />
              </div>
              <div class="form-group">
                <label for="to">Describe Call:</label>
                <textarea class="form-control autogrow" id="call-description" placeholder="I will grow as you type new lines." style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 48px;"></textarea>
              </div>
              <div class="form-group end-buttons-timeline"> <a href="#" id="save-log" class="pull-right save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Save</a> </div>
            </form>
          </div>
        </div>
      </div>
    </section>
    <section>
    <div class="row">
      <!--<div class="timeline col-md-11 col-sm-12 col-xs-12">-->
      <div class="timeline col-md-9 col-sm-10 col-xs-10 pull-right"> @if(count($response)>0 && $message=='')
        <ul class="cbp_tmtimeline" id="timeline-ul">
          <?php  foreach($response as $key => $rows){ ?>
          @if(isset($rows[0]) && $rows[0]==2)
          <li id="timeline-{{$key}}" class="count-li">
            <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows[18])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows[18])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows[18]));  ?></span> <span>
              <?php if(date("d",strtotime($rows[18])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows[18]));} ?>
              </span>
              <?php } ?>
            </time>
            <div class="cbp_tmicon bg-success"> <i class="entypo-mail"></i> </div>
            <div class="cbp_tmlabel">
              <a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a>
             <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less">  &#x2B; </a>
              <h2 onclick="expandTimeLine({{$key}})">@if($rows[17]==$current_user_title) You @else {{$rows[17]}}  @endif <span>sent an email to</span> @if($rows[8]==$current_user_title) You @else {{$rows[8]}}  @endif</h2>

              <div id="hidden-timeline-{{$key}}" class="details no-display">
                <p>CC: {{$rows[11]}}</p>
                <p>BCC: {{$rows[12]}}</p>
                <p>Subject: {{$rows[9]}}</p>
                 <?php
	  if($rows[13]!='')
	  {
    		$attachments = unserialize($rows[13]);
			
			if(count($attachments)>0)
			{
				 echo "<p>Attachments: ";
				foreach($attachments as $key_acttachment => $attachments_data)
				{
					//
					 if(is_amazon() == true)
					{
						$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
					}
					else
					{
						$Attachmenturl = Config::get('app.upload_path')."/".$attachments_data['filepath'];
					}			
					if($key_acttachment==(count($attachments)-1)){
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a>";
					}else{
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a>,";
					}
					
				}
				echo "</p>";
			}			
	  }	 
	   ?>
                <p>Email : {{$rows[10]}}. </p>
               </div>
            </div>
          </li>
          @elseif(isset($rows[0]) && $rows[0]==1)
          <li id="timeline-{{$key}}" class="count-li">
           <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows[18])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows[18])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows[18]));  ?></span> <span>
              <?php if(date("d",strtotime($rows[18])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows[18]));} ?>
              </span>
              <?php } ?>
            </time>
            <div class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
            <div class="cbp_tmlabel">
              <a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a>
              <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less">  &#x2B; </a>
              <h2 onclick="expandTimeLine({{$key}})">@if($rows[17]==$current_user_title) You @else $current_user_title  @endif <span>tagged @if($rows[8]==$current_user_title) You @else {{$current_user_title}} @endif in a</span>Task</h2>
              <div id="hidden-timeline-{{$key}}"  class="details no-display">
                <p>Change hospitality weather widget.</p>
                 </div>
            </div>
          </li>
          @elseif(isset($rows[0]) && $rows[0]==3)
          <li id="timeline-{{$key}}" class="count-li">
            <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows[18])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows[18])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows[18]));  ?></span> <span>
              <?php if(date("d",strtotime($rows[18])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows[18]));} ?>
              </span>
              <?php } ?>
            </time>
            <div class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
            <div class="cbp_tmlabel">
            <a id="show-less-{{$key}}" class="pull-right show-less no-display" onclick="hideDetail({{$key}})"> &#45; </a>
             <a id="show-more-{{$key}}" onclick="expandTimeLine({{$key}})" class="pull-right show-less">  &#x2B; </a>
             <h2 onclick="expandTimeLine({{$key}})">@if($rows[17]==$current_user_title) You @else {{$rows[17]}}  @endif <span>added a note</span></h2>              <div id="hidden-timeline-{{$key}}" class="details no-display">
                <p>{{$rows[16]}}</p>
                </div>
            </div>
          </li>
          @endif
          <?php  } ?>
        </ul>
        @endif 
        <div id="last_msg_loader"></div>
        </div>
        <div class="col-md-3 col-sm-2 col-xs-2 pull-left">
          <p>
         
      <a href="{{ URL::to('contacts/create?AccountID='.$account->AccountID)}}" class="btn btn-primary btn-sm btn-icon icon-left">
        <i class="entypo-floppy"></i>
        Add Contact
    </a>
   </p>
		<div class="list-contact-slide" style="height:500px; overflow-x:scroll;">
          
           		<!--Account card start -->
        @if(isset($contacts) && count($contacts)>0)
        <div class="gridview">
        <ul class="clearfix grid col-md-12">
        @foreach($contacts as $contacts_row) 
    <li>
      <div class="box clearfix ">
        <div class="col-sm-12 headerSmall padding-left-1"> <span class="head">{{$contacts_row['NamePrefix']}} {{$contacts_row['FirstName']}} {{$contacts_row['LastName']}}</span><br>
          <span class="meta complete_name"> </span></div>
        <div class="col-sm-12 padding-0">
          <div class="block blockSmall">
            <div class="meta">Email: <a class="sendemail" href="javascript:void(0)">{{$contacts_row['Email']}}</a></div>
          </div>
          <div class="cellNo cellNoSmall">
            <div class="meta">Phone: <a href="tel:{{$Account_card[0]->Phone}}">{{$contacts_row['Phone']}}</a></div>
          </div>
             <div class="cellNo cellNoSmall">
            <div class="meta">Fax:{{$contacts_row['Fax']}}</div>
          </div>
        </div>

        <div class="col-sm-11 padding-0 action"> <a class="btn-default btn-sm label padding-3" href="{{ URL::to('contacts/'.$contacts_row['ContactID'].'/edit')}}">Edit </a>&nbsp;<a class="btn-default btn-sm label padding-3" href="{{ URL::to('contacts/'.$contacts_row['ContactID'].'/show')}}">View </a>

        </div>
      </div>
    </li>
     @endforeach
  </ul>
</div>
@endif
        <!--Account card end -->             
     
            
        </div>
        </div>
        </div>
    </section>
  </div>
</div>
@include('includes.submit_note_script',array("controller"=>"accounts")) 
<script type="text/javascript">
    jQuery(document).ready(function ($) {
		var per_scroll 		= 	{{$per_scroll}};
		var per_scroll_inc  = 	per_scroll;
		
    // When Lead is converted to account.
    @if(Session::get('is_converted'))

        var toastr_opts = {
            "closeButton": true,
            "debug": false,
            "positionClass": "toast-top-right",
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        toastr.success('{{Session::get('is_converted')}}', "Success", toastr_opts);
    @endif
	//////////
	function last_msg_funtion() 
{  
	var count = 0;
	var getClass = $(".count-li");
    getClass.each(function () {count++;}); 	
	per_scroll 		= 	per_scroll_inc+per_scroll;	
	var ID			=	$(".message_box:last").attr("id");
	var url_scroll 	= 	"{{ URL::to('accounts/{id}/GetTimeLineSrollData')}}";
	url_scroll 	   	= 	url_scroll.replace("{id}",{{$account->AccountID}});
	
	$('div#last_msg_loader').html('<img src="'+baseurl+'/assets/images/bigLoader.gif">');
	
	/////////////
	
			 $.ajax({
                url: url_scroll+'/'+per_scroll+"?scrol="+count,
                type: 'POST',
                dataType: 'html',
				async :false,
                success: function(response1) {
					if (response1 != "") {
						$("#timeline-ul").append(response1); 
					}
						$('div#last_msg_loader').empty();
					
					},
			});	
		
	//////////////
	
}

$(window).scroll(function(){
if ($(window).scrollTop() == $(document).height() - $(window).height()){

setTimeout(function() {
   last_msg_funtion();
}, 1000);
}
});
	//////////
    });
</script> 
<script type="text/javascript">
        function showDiv(divName, ctrl) {
            $("#box-1").addClass("no-display");
            $("#box-2").addClass("no-display");
            $("#box-3").addClass("no-display");
			
            $("#box-4").addClass("no-display");            
            $("#" + divName).removeClass("no-display");
            $("#tab-btn").children("li").removeClass("active");
            $("#" + ctrl).addClass("active");
        }
        $(document).ready(function () {
            if (window.location.href.indexOf("#box-2") >= 0) {
                debugger;
                showDiv("box-2", "2");
            }
            else {
                showDiv("box-1", "1");
            }
        });
        
        $(document).ready(function ($) {
		 $('#addTtachment').click(function(){
                $('#filecontrole').click();
            });

            $(document).on('change','#filecontrole',function(e){
                var files = e.target.files;
                var fileText = '';
                for(i=0;i<files.length;i++){
                    fileText+=files[i].name+'<br>';
                }
                $('.file-input-name').html(fileText);
            });
				
			
            //if ($(window).width() < 992)
            //{
            //    $("#contact-column").addClass('no-display');
            //}
			
			////////////
			
            
			//////////////
        });
        $("#check-lead").click(function () {
            var checkVal = $("#check-lead").val();
            if (checkVal == "No") {
                $("#new-deal-fields-timeline").removeClass('no-display');
                $("#exsiting-lead-timeline").addClass('no-display');
            }
            else if (checkVal == "Yes") {
                $("#exsiting-lead-timeline").removeClass('no-display');
                $("#new-deal-fields-timeline").addClass('no-display');
            }
        });
        $("#lead-company").click(function () {

            var companyName = $("#lead-company").val();
            debugger;
            if (companyName == "Code Desk") {

                $('#lead-contact-list').append('<option>Abdul</option><option>Aamir</option>');

                $("#lead-phone").val('123456');
                $("#lead-email").val('contact@code-desk.com');
            }
            else if (companyName == "Wave-Tel") {
                $('#lead-contact-list').append('<option>Sumera</option><option>Abubakar</option>');
                $("#lead-phone").val('123456');
                $("#lead-email").val('contact@wave-tel.com');
            }
        });
        $("#deal-add").click(function () {

            var dealName = $("#dealName").val();
            var dealOwner = $("#dealOwner").val();
            var checklead = $("#check-lead").val();
            var dealCompany;
            var dealContact;
            var dealPhone;
            var dealEmail;
            if (checklead == "No") {
                dealCompany = $("#dealCompany").val();
                dealContact = $("#dealContact").val();
                dealPhone = $("#dealPhone").val();
                dealEmail = $("#dealEmail").val();

            }
            else if (checklead == "Yes") {
                dealCompany = $("#lead-company").val();
                dealContact = $("#lead-contact").val();
                dealPhone = $("#lead-phone").val();
                dealEmail = $("#lead-email").val();
            }
            var Html = '<div class="col-md-6" ><div class="board-column-item-inner deal" style="border: 1px solid #333; margin-top: 5px;"><div class="deal-details text-center"> <h6 class="m-top-0"><a href="#"> <strong>' + dealCompany + '</strong></a></h6><p><strong>' + dealName + '</strong></p><p><strong>Deal Owner:</strong> ' + dealOwner + '</p></div></div></div>'
            $("#first-deal").append(Html);
            var dialogDeal = document.getElementById('window-deal');
            dialogDeal.close('destroy');


        });
        $("#save-note").click(function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            var formData = new FormData($('#notes-from')[0]);
		    var getClass = $(".count-li");
            var count = 0;
            getClass.each(function () {count++;}); 	
          // showAjaxScript($("#notes-from").attr("action")+"?scrol="+count, formData, FnAddNoteSuccess);
		   var formData = $($('#notes-from')[0]).serializeArray();
		   
		   	 $.ajax({
                url: $("#notes-from").attr("action")+"?scrol="+count,
                type: 'POST',
                dataType: 'html',
				data:formData,
				async :false,
                success: function(response) {
					
			  $(".save.btn").button('reset');
            if (response.message) {
				 ShowToastr("error",response.message);
            } else {
				per_scroll = count;
                ShowToastr("success","Note Successfully Created");                     
                $('#timeline-ul li:eq(0)').before(response);
				document.getElementById('notes-from').reset();
            }
      			},
			});

        });
        $("#save-task").click(function () {
            var getClass = $(".count-li");
            var count = 0;
            getClass.each(function () {
                count++;
            });
            var addCount = count + 1;
            var taskname = $("#task-name").val();
            var taskDescription = $("#task-description").val();
            var taskAssignTo = $("#task-assign-to").val();
            var html = '<li id="timeline-' + addCount + '" class="count-li"><time class="cbp_tmtime" datetime="2014-03-27T03:45"><span>Now</span></time><div class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div><div class="cbp_tmlabel"><h2 onclick="expandTimeLine(' + addCount + ')">You <span>assigned a task </span>' + taskname + '</h2><a id="show-more-' + addCount + '" onclick="expandTimeLine(' + addCount + ')" class="pull-right show-less">Show More<i class="entypo-down-open"></i></a><div id="hidden-timeline-' + addCount + '"   class="details no-display"><p>Assign To:&nbsp; '+taskAssignTo+'</p><p>' + taskDescription + '</p><a class="pull-right show-less" onclick="hideDetail(' + addCount + ')">Show Less<i class="entypo-up-open"></i></a></div></div></li>';
            $('#timeline-ul li:eq(0)').before(html);
        });

        $("#save-log").click(function () {
            var getClass = $(".count-li");
            var count = 0;
            getClass.each(function () {
                count++;
            });
            var addCount = count + 1;
            var callNumber = $("#Log-call-number").val();
            var callDescription = $("#call-description").val();
            var html = '<li id="timeline-' + addCount + '" class="count-li"><time class="cbp_tmtime" datetime="2014-03-27T03:45"><span>Now</span></time><div class="cbp_tmicon bg-success"><i class="entypo-phone"></i></div><div class="cbp_tmlabel"><h2 onclick="expandTimeLine(' + addCount + ')">You <span>made a call to </span>' + callNumber + '</h2><a id="show-more-' + addCount + '" onclick="expandTimeLine(' + addCount + ')" class="pull-right show-less">Show More<i class="entypo-down-open"></i></a><div id="hidden-timeline-' + addCount + '"   class="details no-display"><p>' + callDescription + '</p><a class="pull-right show-less" onclick="hideDetail(' + addCount + ')">Show Less<i class="entypo-up-open"></i></a></div></div></li>';
            $('#timeline-ul li:eq(0)').before(html);
        });
        $("#save-deal").click(function () {
            var getClass = $(".count-li");
            var count = 0;
            getClass.each(function () {
                count++;
            });
            var addCount = count + 1;
            var dealOwner = $("#dealOwner").val();
            var dealName = $("#dealName").val();
            var selectBoard = $("#select-board").val();
            var checklead = $("#check-lead").val();
            var dealCompany;
            var dealContact;
            var dealPhone;
            var dealEmail;
            if (checklead == "No") {
                dealCompany = $("#dealCompany").val();
                dealContact = $("#dealContact").val();
                dealPhone = $("#dealPhone").val();
                dealEmail = $("#dealEmail").val();

            }
            else if (checklead == "Yes") {
                dealCompany = $("#lead-company").val();
                dealContact = $("#lead-contact").val();
                dealPhone = $("#lead-phone").val();
                dealEmail = $("#lead-email").val();
            }
            var html = '<li id="timeline-' + addCount + '" class="count-li"><time class="cbp_tmtime" datetime="2014-03-27T03:45"><span>Now</span></time><div class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div><div class="cbp_tmlabel"><h2 a onclick="dealsDialog()" >You <span>added a new opportunity </span>' + dealName + '</h2><a id="show-more-' + addCount + '" onclick="expandTimeLine(' + addCount + ')" class="pull-right show-less">Show More<i class="entypo-down-open"></i></a><div id="hidden-timeline-' + addCount + '" class="details no-display"><p>Company: &nbsp; ' + dealCompany + '</p><p>Contact Person: &nbsp; ' + dealContact + '</p><p>Phone Number: &nbsp;' + dealPhone + '</p><p>Email Address: &nbsp; ' + dealEmail + '</p><a class="pull-right show-less" onclick="hideDetail('+addCount+')">Show Less<i class="entypo-up-open"></i></a></div></div></li>';
            $('#timeline-ul li:eq(0)').before(html);
        });
        $("#save-mail").click(function (event) {
		    var getClass = $(".count-li");
            var count = 0;
            getClass.each(function () {count++;}); 			
			var email_url 	= 	"{{ URL::to('/accounts/'.$account->AccountID.'/activities/sendemail/api/')}}?scrol="+count;
          	event.stopImmediatePropagation();
            event.preventDefault();			
			var formData = new FormData($('#email-from')[0]);
			// formData.push({ name: "emailattachment", value: $('#emailattachment').val() });
			console.log(formData);
			// showAjaxScript(email_url, formData, FnAddEmailSuccess);
			
			 $.ajax({
                url: email_url,
                type: 'POST',
                dataType: 'json',
				data:formData,
				async :false,
				cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
					
			  $(".save.btn").button('reset');
            if (response.message) {
				 ShowToastr("error",response.message);
            } else {
				per_scroll = count;
                ShowToastr("success","Email Sent Successfully");                         
                $('#timeline-ul li:eq(0)').before(response);
				document.getElementById('email-from').reset();
            }
      			},
			});	
		 });
		 
		 
		 
		 /////////        
        function expandTimeLine(id)
        {
            $("#hidden-timeline-" + id).removeClass('no-display');
            $("#show-more-" + id).addClass('no-display');
			$("#show-less-" + id).removeClass('no-display');
        }
        function hideDetail(id) {
			$("#show-less-" + id).addClass('no-display');
            $("#hidden-timeline-" + id).addClass('no-display');
            $("#show-more-" + id).removeClass('no-display');
        }
    </script> 
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script> 
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script> 
<style>
#last_msg_loader{text-align:center;} .file-input-name{text-align:right; display:block;} ul.grid li div.headerSmall{min-height:31px;} ul.grid li div.box{height:auto;}
ul.grid li div.blockSmall{min-height:20px;} ul.grid li div.cellNoSmall{min-height:20px;}
</style>
@stop
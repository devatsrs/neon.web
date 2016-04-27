<?php $disabled='';$leadOrAccountExist = 'No';$leadOrAccountID = '';$leadOrAccountCheck='';  $BoardID = $Board[0]->BoardID; ?>
@extends('layout.main')
@section('content')
<div  style="min-height: 1050px;">
  <ol class="breadcrumb bc-3">
    <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
    <li> <a href="{{URL::to('accounts')}}">Accounts</a> </li>
    <li class="active"> <strong>View Account</strong> </li>
  </ol>
  @include('includes.errors')
  @include('includes.success')
  <?php $Account = $account;?>
  @include('accounts.errormessage')
  <div id="account-timeline">
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
                  <div class="block blockSmall">
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
                <div class="col-sm-11 padding-0 action"> <a class="btn-default btn-sm label padding-3" href="{{ URL::to('accounts/'.$account->AccountID.'/edit')}}">Edit </a>&nbsp; <a class="btn-default btn-sm label padding-3" href="{{ URL::to('accounts/'.$account->AccountID.'/show')}}">View </a>&nbsp;
                  @if($account->IsCustomer==1 && $account->VerificationStatus==Account::VERIFIED) <a class="btn-warning btn-sm label padding-3" href="{{ URL::to('customers_rates/'.$account->AccountID)}}">Customer</a>&nbsp;
                  @endif
                  @if($account->IsVendor==1 && $account->VerificationStatus==Account::VERIFIED) <a class="btn-info btn-sm label padding-3" href="{{ URL::to('vendor_rates/'.$account->AccountID)}}">Vendor</a> @endif </div>
              </div>
            </li>
          </ul>
        </div>
        @endif 
        <!--Account card end --> 
                <div class="">
          <button style="margin:8px 25px 0 0;" redirecto="{{ URL::to('contacts/create?AccountID='.$account->AccountID)}}" type="button" class="btn btn-black btn-xs pull-right">
						<i class="entypo-plus"></i>
					</button>
   <h3>Contacts</h3>
   
   </div>
   <div class="clearfix"></div>
        
                     <!--<div class="list-contact-slide" style="height:500px; overflow-x:scroll;"> -->
            <div class="list-contact-slide">
            <!--contacts card start --> 
            
            <div class="gridview">
              <ul class="clearfix grid col-md-12">
              @if(isset($contacts) && count($contacts)>0)
                @foreach($contacts as $contacts_row)
                <li>
                  <div class="box clearfix ">
                    <div class="col-sm-12 headerSmall padding-left-1"> <span class="head">{{$contacts_row['NamePrefix']}} {{$contacts_row['FirstName']}} {{$contacts_row['LastName']}}</span><br>
                      <span class="meta complete_name"> </span></div>
                    <div class="col-sm-12 padding-0">
                      <div class="block blockSmall">
                        <div class="meta">Department: <a class="sendemail">{{$contacts_row['Department']}}</a></div>
                      </div>
                      <div class="block blockSmall">
                        <div class="meta">Job Title: <a class="sendemail" href="javascript:void(0)">{{$contacts_row['Title']}}</a></div>
                      </div>
                      <div class="block blockSmall">
                        <div class="meta">Email: <a class="sendemail" href="javascript:void(0)">{{$contacts_row['Email']}}</a></div>
                      </div>
                      <div class="cellNo cellNoSmall">
                        <div class="meta">Phone: <a href="tel:{{$Account_card[0]->Phone}}">{{$contacts_row['Phone']}}</a></div>
                      </div>
                      <div class="cellNo cellNoSmall">
                        <div class="meta">Fax:{{$contacts_row['Fax']}}</div>
                      </div>
                      <div class="block blockSmall">
                        <div class="meta">Skype: <a class="sendemail" href="javascript:void(0)">{{$contacts_row['Skype']}}</a></div>
                      </div>
                    </div>
                    <div class="col-sm-11 padding-0 action"> <a class="btn-default btn-sm label padding-3" href="{{ URL::to('contacts/'.$contacts_row['ContactID'].'/edit')}}">Edit </a>&nbsp;<a class="btn-default btn-sm label padding-3" href="{{ URL::to('contacts/'.$contacts_row['ContactID'].'/show')}}">View </a> </div>
                  </div>
                </li>
                @endforeach
                @endif 
              </ul>
            </div>
            
            <!--contacts card end --> 
            
          </div>
        
      </div>
      <div id="text-boxes" class="timeline col-md-9 col-sm-12 col-xs-12  upper-box">
        <div class="row">
          <ul id="tab-btn" class="interactions-list">
            <li id="1" class="interactions-tab"> <a href="#Note" class="interaction-link note" onclick="showDiv('box-1',1)"><i class="entypo-doc-text"></i>New Note</a> </li>
            <li id="2" class="interactions-tab"> <a href="#task" class="interaction-link activity" onclick="showDiv('box-3',2)"><i class="entypo-doc-text"></i>Create Task</a> </li>
    <!--        <li id="3" class="interactions-tab"> <a href="#schedule" class="interaction-link task" onclick="showDiv('box-4',3)"><i class="entypo-phone"></i>Log Activity</a> </li>-->
            <li id="4" class="interactions-tab"> <a href="#email" class="interaction-link task" onclick="showDiv('box-2',4)"><i class="entypo-mail"></i>Email</a> </li>
          </ul>
        </div>
        <div class="row margin-top-5 box-min" id="box-1">
          <div class="col-md-12">
            <form role="form" id="notes-from" action="{{URL::to('accounts/'.$account->AccountID.'/store_note/')}}" method="post">
              <div class="form-group ">
             
                  <textarea name="Note" id="note-content" class="form-control autogrow editor-note"  placeholder="I will grow as you type new lines." style="height: 175px; overflow: hidden; word-wrap: break-word; resize: none;"></textarea>
             


                       
              </div>
              <div class="form-group end-buttons-timeline"> 
                <button value="save" id="save-note" class="pull-right save btn btn-primary btn-sm btn-icon icon-left save-note-btn hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
                
                 <button style="margin-right:10px;" value="save_follow" id="save-note-follow" class="pull-right save btn btn-primary btn-sm btn-icon icon-left save-note-btn hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save and create follow up task</button>
              </div>
            </form>
          </div>
        </div>
        <div class="row no-display margin-top-5 box-min" id="box-2" style="margin-bottom: 5px;">
          <div class="col-md-12">
            <div class="mail-compose">
              <form method="post" id="email-from" role="form" enctype="multipart/form-data">
                <div class="form-group">
                  <label for="to">To:</label>
                  <!--{{ Form::select('email-to', USer::getUserIDList(), '', array("class"=>"select2","id"=>"email-to","tabindex"=>"1")) }}-->
                  <input type="text" class="form-control" value="{{$account->Email}}" id="email-to" name="email-to" tabindex="1"  />
                  <div class="field-options"> <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#cc').parent().removeClass('hidden'); $('#cc').focus();">CC</a> <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#bcc').parent().removeClass('hidden'); $('#bcc').focus();">BCC</a> </div>
                </div>
                <div class="form-group hidden">
                  <label for="cc">CC:</label>
                  {{ Form::select('cc[]', USer::getUserIDListOnly(), '', array("class"=>"select2","Multiple","id"=>"cc","tabindex"=>"2")) }} </div>
                <div class="form-group hidden">
                  <label for="bcc">BCC:</label>
                  {{ Form::select('bcc[]', USer::getUserIDListOnly(), '', array("class"=>"select2","Multiple","id"=>"bcc","tabindex"=>"3")) }}
                  </div>
                   
                <div class="form-Group" style="margin-bottom: 15px;">
                    <label >Email Template</label>                               
                        {{Form::select('email_template',$emailTemplates,'',array("class"=>"select2"))}}                                
                </div>
                        
                <div class="form-group">
                  <label for="subject">Subject:</label>
                  <input type="text" class="form-control" id="subject" name="Subject" tabindex="4" />
                </div>
                <div class="form-group">  
                <label for="subject">Email:</label>                            
                 <textarea id="Message" class="form-control message"    name="Message"></textarea>
                </div>
                <div class="form-group no_margin_bt">
                <p class="comment-box-options-activity"> <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
                </div>
                <div class="form-group email_attachment">
               <!--   <input id="filecontrole" type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden" multiple data-label="<i class='entypo-attach'></i>Attachments" />-->
               
               <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
                    
                  <span class="file-input-names"></span>
                </div>
                <div class="form-group end-buttons-timeline">                 
                                 <button name="mail_submit" value="save_mail" id="save-mail" class="pull-right save btn btn-primary btn-sm btn-icon btn-send-mail icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-mail"></i>Send</button>                
                 <button name="mail_submit" value="save_mail_follow" id="save-email-follow" style="margin-right:10px;" class="pull-right save btn btn-primary btn-sm btn-icon btn-send-mail icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-mail"></i>Send and Create follow up task</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="row no-display margin-top-5 box-min" id="box-3">
          <div class="col-md-12">
            <form id="save-task-form" role="form" method="post">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="to">Task Status:</label>
                  {{Form::select('TaskStatus',CRMBoardColumn::getTaskStatusList($BoardID),'',array("class"=>"selectboxit"))}} </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="to">Task Assign to:</label>
                  {{Form::select('UsersIDs',$account_owners,'',array("class"=>"selectboxit"))}} </div>
              </div>
             </div> 
              <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="to">Priority:</label>
                  {{Form::select('Priority',$priority,'',array("class"=>"selectboxit"))}} </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="to">Due Date:</label>
                  <input autocomplete="off" type="text" name="DueDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                </div>
              </div>
			</div>
            <div class="row">              
              <div class="col-md-12">
                <div class="form-group">
                  <label for="to">Task Subject:</label>
                  <input type="text" id="Subject" name="Subject" class="form-control"  tabindex="1" />
                </div>
              </div>
             </div>
             <div class="row"> 
              <div class="col-md-12">
                <div class="form-group">
                  <label for="to">Description:</label>
                  <textarea class="form-control autogrow" id="Description" name="Description" placeholder="I will grow as you type new lines." style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 48px;"></textarea>
                </div>
              </div>
              </div>
              <div class="row">
              <div class="col-md-12">
                <div class="form-group end-buttons-timeline">
                  <button id="save-task" class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
                  <input type="hidden" value="{{$BoardID}}" name="BoardID">
                  <input type="hidden" value="{{$account->AccountID}}" name="AccountIDs[]">
                </div>
              </div>
              </div>
            </form>
          </div>
        </div>
        <div class="row no-display margin-top-5 box-min" id="box-4">
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
      
  <!--  </section>
    <section>-->
    
     
      <!-- -->
            
      <!-- -->
        <!--<div class="timeline col-md-11 col-sm-12 col-xs-12">-->
        <div class="timeline col-md-9 col-sm-10 col-xs-10 big-col pull-right"> 
          <ul class="cbp_tmtimeline" id="timeline-ul">
          <li></li>
          @if(count($response)>0 && $message=='')
            <?php  foreach($response as $key => $rows){
			  $rows = json_decode(json_encode($rows), True); //convert std array to simple array
			   ?>
            @if(isset($rows['Timeline_type']) && $rows['Timeline_type']==2)
            <li id="timeline-{{$key}}" class="count-li">
              <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
                <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
                <span>Now</span>
                <?php }else{ ?>
                <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
                <?php if(date("d",strtotime($rows['created_at'])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
                </span>
                <?php } ?>
              </time>
              <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"> <i class="entypo-mail"></i> </div>
              <div class="cbp_tmlabel">  
                <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else {{$rows['CreatedBy']}}  @endif <span>sent an email to</span> @if($rows['EmailToName']==$current_user_title) You @else {{$rows['EmailToName']}}  @endif</h2>
                <div id="hidden-timeline-{{$key}}" class="details no-display">
                  @if($rows['EmailCc'])<p>CC: {{$rows['EmailCc']}}</p>@endif
                  @if($rows['EmailBcc'])<p>BCC: {{$rows['EmailBcc']}}</p>@endif
                  <p>Subject: {{$rows['EmailSubject']}}</p>
                  <?php
	  if($rows['EmailAttachments']!='')
	  {
    		$attachments = unserialize($rows['EmailAttachments']);
			
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
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a><br><br>";
					}else{
						echo "<a target='_blank' href=".$Attachmenturl.">".$attachments_data['filename']."</a><br>";
					}
					
				}
				echo "</p>";
			}			
	  }	 
	   ?>
                  <div>Message:<br>{{$rows['EmailMessage']}}. </div>
                </div>
              </div>
            </li>
            @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==1)
            <li id="timeline-{{$key}}" class="count-li">
              <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
                <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
                <span>Now</span>
                <?php }else{ ?>
                <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
                <?php if(date("d",strtotime($rows['created_at'])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
                </span>
                <?php } ?>
              </time>
              <div id_toggle="{{$key}}" class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
              <div class="cbp_tmlabel">  
                <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else $current_user_title  @endif <span>tagged @if($rows['TaskName']==$current_user_title) You @else {{$rows['TaskName']}} @endif in a</span> @if($rows['followup_task']) follow up @endif Task</h2>
                <div id="hidden-timeline-{{$key}}"  class="details no-display">
                  <p>Subject: {{$rows['TaskTitle']}}</p>
                  <p>Assign To: {{$rows['TaskName']}}</p>
                  <p>priority: {{$rows['TaskPriority']}}</p>
                  <p>Due Date: {{$rows['DueDate']}}</p>
                  <p>Status: {{$rows['TaskStatus']}}. </p>
                  <p>Description: {{$rows['TaskDescription']}} </p>
                </div>
              </div>
            </li>
            @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==3)
            <li id="timeline-{{$key}}" class="count-li">
              <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
                <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
                <span>Now</span>
                <?php }else{ ?>
                <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
                <?php if(date("d",strtotime($rows['created_at'])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
                </span>
                <?php } ?>
              </time>
              <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
              <div class="cbp_tmlabel">  
                <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else {{$rows['CreatedBy']}}  @endif <span>added a note</span></h2>
                <div id="hidden-timeline-{{$key}}" class="details no-display">
                  <p>{{$rows['Note']}}</p>
                </div>
              </div>
            </li>
            @endif
            <?php  } ?>
            @endif
          </ul>
          
          <div id="last_msg_loader"></div>
        </div>
 
        
     
    </section>
  </div>
</div>
@include('includes.submit_note_script',array("controller"=>"accounts")) 
@include("accounts.taskmodal") 

<script type="text/javascript">
var show_popup		  = 	 	0;
var rowData 		  = 	 	[];
var scroll_more 	  =  		1;
var file_count 		  =  		0;
var current_tab       =  		'';
var allow_extensions  = 		{{$response_extensions}};
var email_file_list	  =  		new Array();

    jQuery(document).ready(function ($) {
		var per_scroll 		= 	{{$per_scroll}};
		var per_scroll_inc  = 	per_scroll;
		
		  $("#email-from [name=email_template]").change(function(e){
            var templateID = $(this).val();
            if(templateID>0) {
                var url = baseurl + '/accounts/' + templateID + '/ajax_template';
                $.get(url, function (data, status) {
                    if (Status = "success") {
                        editor_reset(data);
                    } else {
                        toastr.error(status, "Error", toastr_opts);
                    }
                });
            }
        });

		        function editor_reset(data){
				var doc = $('.mail-compose');
		  		doc.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
        		doc.find('.message').show();
						
								
            var doc = $(".mail-compose");
            if(!Array.isArray(data)){				
                var EmailTemplate = data['EmailTemplate'];
                doc.find('[name="Subject"]').val(EmailTemplate.Subject);
                doc.find('.message').val(EmailTemplate.TemplateBody);
            }else{
                doc.find('[name="Subject"]').val('');
                doc.find('.message').val('');
            }
			
			doc.find('.message').wysihtml5({
				"font-styles": true,
				"leadoptions":false,
				"Crm":true,
				"emphasis": true,
				"lists": true,
				"html": true,
				"link": true,
				"image": true,
				"color": false,
				parser: function(html) {
					return html;
				}
			});
           
        }
		
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

	if(scroll_more==0)
	{
		return false;
	}
	var count = 0;
	var getClass = $(".count-li");
    getClass.each(function () {count++;}); 	
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
						if (isJson(response1)) {
							
					var response_json  =  JSON.parse(response1);
					if(response_json.message=='infinity')
					{
						var html_end  ='<li><time class="cbp_tmtime"></time><div class="cbp_tmicon bg-info end_timeline_logo "><i class="entypo-infinity"></i></div><div class="end_timeline cbp_tmlabel"><h2></h2><div class="details no-display"></div></div></li>';
						$("#timeline-ul").append(html_end);	
						scroll_more= 0;	
						$('div#last_msg_loader').empty();
						console.log("Results completed");
						return false;
					}
					ShowToastr("error",response_json.message);
				} else {
						per_scroll 		= 	per_scroll_inc+per_scroll;	
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

        function showDiv(divName, ctrl) {
			
			if(divName== current_tab)
			{return false;}
			
            $("#box-1").addClass("no-display");
            $("#box-2").addClass("no-display");
            $("#box-3").addClass("no-display");
			
            $("#box-4").addClass("no-display");            
            $("#" + divName).removeClass("no-display");
            $("#tab-btn").children("li").removeClass("active");
            $("#" + ctrl).addClass("active");
			if(divName=='box-2')
			{				
        	var doc = $('.mail-compose');
       	 doc.find('.message').wysihtml5({
				"font-styles": true,
				"leadoptions":false,
				"Crm":true,
				"emphasis": true,
				"lists": true,
				"html": true,
				"link": true,
				"image": true,
				"color": false,
				parser: function(html) {
					return html;
				}
			});

			}else{
				 var doc = $('.mail-compose');
		  		doc.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
        		doc.find('.message').show();
			}
			
			if(divName=='box-1')
			{	
				var doc = $('#box-1');
			 doc.find('#note-content').wysihtml5({
					"font-styles": true,
					"leadoptions":false,
					"Crm":false,
					"emphasis": true,
					"lists": true,
					"html": true,
					"link": true,
					"image": true,
					"color": false,
					parser: function(html) {
						return html;
					}
				});
			}
			else
			{
				var doc = $('#box-1');
		  		doc.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
        		doc.find('#note-content').show();
			
			}
			current_tab = divName;
			
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
			$( document ).on("click",".cbp_tmicon" ,function(e) {
				var id_toggle = $(this).attr('id_toggle');
				if(id_toggle)
				{
               		$('#hidden-timeline-'+id_toggle).toggle();
				}
            });
			
			$(document).on("click",".toggle_open", function(e) {
				var id_toggle = $(this).attr('id_toggle');
				if(id_toggle)
				{
					if( $('#hidden-timeline-'+id_toggle).css('display').toLowerCase() != 'block') {
							$('#hidden-timeline-'+id_toggle).css('display','block');	
					}
				}
                
            });
			
		 $('#addTtachment').click(function(){
			 file_count++;                
				var html_img = '<input id="filecontrole'+file_count+'" multiple type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"  />';
				$('.email_attachment').append(html_img);
				$('#filecontrole'+file_count).click();
				
            });
			
			$(document).on("click",".del_attachment",function(ee){
				var file_delete_url 	= 	baseurl + '/account/delete_actvity_attachment_file';
			
				
				var del_file_name   =  $(this).attr('del_file_name');
				$(this).parent().remove();
				var index_file = email_file_list.indexOf(del_file_name);
				 email_file_list.splice(index_file, 1);
				 
				$.ajax({
                url: file_delete_url,
                type: 'POST',
                dataType: 'html',
				data:{file:del_file_name},
				async :false,
                success: function(response1) {},
			});	
				
			});
			

            $(document).on('change','.fileUploads',function(e){
				
				var current_input_id =  $(this).attr('id');
                var files 			 = e.target.files;				
                var fileText 		 = '';
				
				///////
        var filesArr = Array.prototype.slice.call(files);
        filesArr.forEach(function(f) {          
		var ext_current_file  = f.name.split('.').pop();
            if(allow_extensions.indexOf(ext_current_file) > -1 )			
			{            
            
            var reader = new FileReader();
			
 
            reader.onload = function (e) {
				var base_64   = e.target.result;
				var name_file = f.name;
				
				var index_file = email_file_list.indexOf(f.name);
				if(index_file>0)
				{
					ShowToastr("error",f.name+" file already selected.");	
					return;
				}
				
				var file_upload_url 	= 	baseurl + '/account/upload_file';
				setTimeout(
				function() 
 				 {
				$.ajax({
                url: file_upload_url,
                type: 'POST',
                dataType: 'html',
				async :false,
				data:{name_file:name_file,file_data:base_64,file_ext:ext_current_file},
				async :false,
                success: function(response) {					
					 fileText ='<span class="file_upload_span imgspan_'+current_input_id+'">'+f.name+' <a class="del_attachment" del_file_name = "'+f.name+'" del_img_id="'+current_input_id+'"> X </a><br></span>';
					 $('.file-input-names').append(fileText);
					email_file_list.push(f.name);
					},
			}) }, 1000);
				
                    
           		 }
			}
			else
			{
				ShowToastr("error",ext_current_file+" file type not allowed.");
				return;
			}
			
            reader.readAsDataURL(f); 
        });
        
    
				

            });
				
				
			
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
        $("#notes-from").submit(function (event) {
            event.stopImmediatePropagation();
            event.preventDefault();			
			var type_submit  = $(this).val();			

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
					
			   $(".save-note-btn").button('reset');
			   $(".save-note-btn").removeClass('disabled');
					
			  $(".save.btn").button('reset');
            	if (isJson(response)) {
					var response_json  =  JSON.parse(response);
					ShowToastr("error",response_json.message);
				} else {
				per_scroll = count;
                ShowToastr("success","Note Successfully Created");                     
                $('#timeline-ul li:eq(0)').before(response);
				document.getElementById('notes-from').reset();
				$('#box-1 .wysihtml5-sandbox').contents().find('body').html('');
				if(show_popup==1)
				{				
					document.getElementById('add-task-form').reset();
					$('#Task_type').val(3);
					$('#Task_ParentID').val($('#timeline-ul li:eq(0)').attr('row-id'));					
					$('#add-modal-task').modal('show');        	
				}

            } show_popup=0;
      			},
			});

        });
        $("#save-task-form").submit(function (e) {
			
			//////////////
			 $('#save-task').addClass('disabled');  $('#save-task').button('loading');
			
            e.preventDefault();
			e.stopImmediatePropagation();
            var formid 			= 	$(this).attr('id');            
            var formData 		= 	new FormData($('#'+formid)[0]);
			var count 			= 	0;
			var getClass 		= 	$(".count-li");
            getClass.each(function () {count++;}); 	
			var update_new_url 	= 	baseurl + '/task/create?scrol='+count;
			
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'html',
                success: function (response) {                  
					
					if (isJson(response)) {
						var response_json  =  JSON.parse(response);
						 ShowToastr("error",response_json.message);
					} else {
						per_scroll = count;
						ShowToastr("success","Task Successfully Created");                     
						$('#timeline-ul li:eq(0)').before(response);
						document.getElementById('save-task-form').reset();
					}
                    show_popup=0;
				    $("#save-task").button('reset');
			   	    $("#save-task").removeClass('disabled');
                    //getOpportunities();
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        
			//////////////
        });
		
		function isJson(str) {
		try {
			JSON.parse(str);
		} catch (e) {			
			return false;
		}
    	return true;
}

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
		
		$('#save-mail').click(function(e) {  empty_images_inputs();  $('.btn-send-mail').addClass('disabled'); $(this).button('loading');            show_popup = 0; });
		$('#save-email-follow').click(function(e) { empty_images_inputs();  $('.btn-send-mail').addClass('disabled'); $(this).button('loading');    show_popup = 1; });
		
		$('#save-note').click(function(e) {       $('.save-note-btn').addClass('disabled'); $(this).button('loading');      show_popup = 0; });
		$('#save-note-follow').click(function(e) {  $('.save-note-btn').addClass('disabled'); $(this).button('loading');    show_popup = 1; });
		
		
		function empty_images_inputs()
		{
			$('.fileUploads').val();
			$('#emailattachment_sent').val(email_file_list);
		}
		
        $("#email-from").submit(function (event) {
		    var getClass = $(".count-li");
            var count = 0;
            getClass.each(function () {count++;}); 			
			var email_url 	= 	"{{ URL::to('/accounts/'.$account->AccountID.'/activities/sendemail/api/')}}?scrol="+count;
          	event.stopImmediatePropagation();
            event.preventDefault();			
			var formData = new FormData($('#email-from')[0]);
			console.log(rowData);
			
			// formData.push({ name: "emailattachment", value: $('#emailattachment').val() });
			// showAjaxScript(email_url, formData, FnAddEmailSuccess);
			
			 $.ajax({
                url: email_url,
                type: 'POST',
                dataType: 'html',
				data:formData,
				async :false,
				cache: false,
                contentType: false,
                processData: false,
                success: function(response) {		
			   $(".btn-send-mail").button('reset');
			   $(".btn-send-mail").removeClass('disabled');			   
 	           if (isJson(response)) {
					var response_json  =  JSON.parse(response);
					
					ShowToastr("error",response_json.message);
				} else {
				//reset file upload	
				file_count = 0;
				email_file_list = [];
				$('.fileUploads').remove();
				$('.file_upload_span').remove();
				 $('#box-2 .wysihtml5-sandbox').contents().find('body').html('');
				per_scroll = count;
                ShowToastr("success","Email Sent Successfully");                         
                $('#timeline-ul li:eq(0)').before(response);
				document.getElementById('email-from').reset();
				
				if(show_popup==1)
				{				
					document.getElementById('add-task-form').reset();
					$('#Task_type').val(2);
					$('#Task_ParentID').val($('#timeline-ul li:eq(0)').attr('row-id'));					
					$('#add-modal-task').modal('show');        	
				}
            } show_popup=0;
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
 <link rel="stylesheet" href="{{ URL::asset('assets/js/wysihtml5/bootstrap-wysihtml5.css') }}">   
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script> 
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script>



<style>
#last_msg_loader{text-align:center;} .file-input-names{text-align:right; display:block;} ul.grid li div.headerSmall{min-height:31px;} ul.grid li div.box{height:auto;}
ul.grid li div.blockSmall{min-height:20px;} ul.grid li div.cellNoSmall{min-height:20px;} ul.grid li div.action{position:inherit;}
.col-md-3{padding-right:5px;}.big-col{padding-left:5px;}.box-min{min-height:225px;} .del_attachment{cursor:pointer;}  .no_margin_bt{margin-bottom:0;}
#account-timeline ul li.follow::before{background:#f5f5f6 none repeat scroll 0 0;}
</style>
@stop
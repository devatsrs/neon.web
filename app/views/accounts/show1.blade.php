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
              <div class="cbp_tmlabel normal">  
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
            <li id="timeline-{{$key}}" class="count-li @if($rows['followup_task']) followup_task @endif">
              <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
                <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
                <span>Now</span>
                <?php }else{ ?>
                <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
                <?php if(date("d",strtotime($rows['created_at'])) == date('d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
                </span>
                <?php } ?>
              </time>
              
              @if(!$rows['followup_task'])<div id_toggle="{{$key}}" class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div> @endif
              <div class="cbp_tmlabel @if(!$rows['followup_task'])normal @endif ">  
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
              <div class="cbp_tmlabel normal">  
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
<div class="followup_task_data hidden">
<ul>
<li></li>
</ul>

</div>
@include('includes.submit_note_script',array("controller"=>"accounts")) 
@include("accounts.taskmodal") 
@include("accounts.activity_jscode",array("response_extensions"=>$response_extensions,"AccountID"=>$account->AccountID,"per_scroll"=>$per_scroll)) 

 <link rel="stylesheet" href="{{ URL::asset('assets/js/wysihtml5/bootstrap-wysihtml5.css') }}">   
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script> 
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script>



<style>
#last_msg_loader{text-align:center;} .file-input-names{text-align:right; display:block;} ul.grid li div.headerSmall{min-height:31px;} ul.grid li div.box{height:auto;}
ul.grid li div.blockSmall{min-height:20px;} ul.grid li div.cellNoSmall{min-height:20px;} ul.grid li div.action{position:inherit;}
.col-md-3{padding-right:5px;}.big-col{padding-left:5px;}.box-min{min-height:225px;} .del_attachment{cursor:pointer;}  .no_margin_bt{margin-bottom:0;}
#account-timeline ul li.follow::before{background:#f5f5f6 none repeat scroll 0 0;}
.cbp_tmtimeline > li.followup_task .cbp_tmlabel::before{margin:0;right:93%;top:-27px;border-color:transparent #f1f1f1 #fff transparent; position:absolute; border-style:solid; border-width:14px;  content: " ";} footer.main{clear:both;}

</style>
@stop
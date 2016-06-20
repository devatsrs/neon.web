<?php //$BoardID = $Boards->BoardID;  ?>
@extends('layout.main')
@section('content')
<div  style="min-height: 1050px;">
  <ol class="breadcrumb bc-3">
  @if($leadOrAccountCheck=='account')
    <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
    <li> <a href="{{URL::to('accounts')}}">Accounts</a> </li>
    <li class="active"> <strong>View Account</strong> </li>
  @elseif($leadOrAccountCheck=='lead')  
      <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>

        <a href="{{URL::to('/leads')}}">Leads</a>
    </li>
    <li class="active">
        <strong>View Lead</strong>
    </li>
    @endif
  </ol>
  
  @include('includes.errors')
  @include('includes.success')
  <?php $Account = $account;?>
  @if($leadOrAccountCheck=='account')
  @include('accounts.errormessage')
  @endif
   <div id="account-timeline">
    <section>
      <div id="contact-column" class="about-account col-md-3 col-sm-12 col-xs-12 pull-left"> 
        <!--Account card start --> 
        @if(isset($Account_card) && count($Account_card)>0)
        <div class="gridview">
          <ul class="clearfix grid col-md-12">
            <li>
              <div class="box clearfix ">
                <div class="col-sm-12 header padding-left-1"> <span class="head">
                @if(strlen($Account_card->AccountName)>22) {{substr($Account_card->AccountName,0,22)."..."}} @else {{$Account_card->AccountName}} @endif</span><br>
                  <span class="meta complete_name">@if(strlen($Account_card->Ownername)>40) {{substr($Account_card->Ownername,0,40)."..."}} @else {{$Account_card->Ownername}} @endif </span></div>
                <div class="col-sm-6 padding-0">
                  <div class="block">
                    <div class="meta">Email</div>
                    <div><a class="sendemail" href="javascript:void(0)">{{$Account_card->Email}}</a></div>
                  </div>
                  <div class="cellNo">
                    <div class="meta">Phone</div>
                    <div><a href="tel:{{$Account_card->Phone}}">{{$Account_card->Phone}}</a></div>
                  </div>
                  @if($leadOrAccountCheck=='account')
                  <div class="block blockSmall">
                    <div class="meta">Outstanding</div>
                    <div>{{$Account_card->OutStandingAmount}}</div>
                  </div>
                  @endif
                </div>
                <div class="col-sm-6 padding-0">
                  <div class="block">
                    <div class="meta">Address</div>
                    <div class="address account-address">
                      <?php  if(!empty($Account_card->Address1)){ echo $Account_card->Address1."<br>";} ?>                      
                      <?php  if(!empty($Account_card->Address2)){ echo $Account_card->Address2."<br>";} ?>
                      <?php  if(!empty($Account_card->Address3)){ echo $Account_card->Address3."<br>";} ?>
                      <?php  if(!empty($Account_card->City)){ echo $Account_card->City."<br>";} ?>
                      <?php  if(!empty($Account_card->PostCode)){ echo $Account_card->PostCode."<br>";} ?>
                      <?php  if(!empty($Account_card->Country)){ echo $Account_card->Country."<br>";} ?>
                    </div>
                  </div>
                </div>
                <div class="col-sm-11 padding-0 action">                
                  <button type="button" data-id="{{$account->AccountID}}" title="Add Opportunity" class="btn btn-default btn-xs opportunity"> <i class="entypo-ticket"></i> </button>
                  <button type="button" href_id="edit_account" data-id="{{$account->AccountID}}"  title="Edit Account" class="btn btn-default btn-xs redirect_link" > <i class="entypo-pencil"></i> </button>
                    @if(User::checkCategoryPermission('AccountExpense','View'))
                        <a  href="{{Url::to('accounts/expense/'.$account->AccountID)}}"  data-id="{{$account->AccountID}}"  title="Account Expense Chart" class="btn btn-default btn-xs redirect_link" > <i class="fa fa-bar-chart"></i> </a>
                    @endif
                   @if($leadOrAccountCheck=='account')
                  <a href="{{ URL::to('accounts/'.$account->AccountID.'/edit')}}" id="edit_account" target="_blank" class="hidden">Add Contact</a>                	@elseif($leadOrAccountCheck=='lead')  
                  <a href="{{ URL::to('leads/'.$account->AccountID.'/edit')}}" id="edit_account" target="_blank" class="hidden">Add Contact</a>
                  @endif
                                    
                <!--  <button type="button" data-id="{{$account->AccountID}}" title="View Account" class="btn btn-default btn-xs" redirecto="{{ URL::to('accounts/'.$account->AccountID.'/show1')}}"> <i class="entypo-search"></i> </button> -->
                 @if($leadOrAccountCheck=='account')
                  @if($account->IsCustomer==1 && $account->VerificationStatus==Account::VERIFIED) <a class="btn-warning btn-sm label padding-3" href="{{ URL::to('customers_rates/'.$account->AccountID)}}">Customer</a>&nbsp;
                  @endif
                  @if($account->IsVendor==1 && $account->VerificationStatus==Account::VERIFIED) <a class="btn-info btn-sm label padding-3" href="{{ URL::to('vendor_rates/'.$account->AccountID)}}">Vendor</a> @endif
                  @endif
                
                  
                   </div>
              </div>
            </li>
          </ul>
        </div>
        @endif 
        <!--Account card end --> 
                <div class="">
          <button style="margin:8px 25px 0 0;"  href_id="create_contact" id="redirect_add_link" type="button" class="btn btn-black redirect_link btn-xs pull-right">
						<i class="entypo-plus"></i>
					</button>
                  <a href="{{ URL::to('contacts/create?AccountID='.$account->AccountID)}}" id="create_contact" target="_blank" class="hidden">Add Contact</a>
   <span class="head_title">Contacts</span>
   
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
                        <div class="meta">Phone: <a href="tel:{{$Account_card->Phone}}">{{$contacts_row['Phone']}}</a></div>
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
             
                  <textarea name="Note" id="note-content" class="form-control autogrow editor-note"   style="height: 175px; overflow: hidden; word-wrap: break-word; resize: none;"></textarea>
             


                       
              </div>
              <div class="form-group end-buttons-timeline"> 
                <button value="save" id="save-note" class="pull-right save btn btn-primary btn-sm btn-icon icon-left save-note-btn hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
                 @if(count($boards)>0)
                 <button style="margin-right:10px;" value="save_follow" id="save-note-follow" class="pull-right save btn btn-primary btn-sm btn-icon icon-left save-note-btn hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save and Create follow up task</button>
                 @endif
              </div>
            </form>
          </div>
        </div>
        <div class="row no-display margin-top-5 box-min" id="box-2" style="margin-bottom: 5px;">
          <div class="col-md-12">
            <div class="mail-compose">
              <form method="post" id="email-from" role="form" enctype="multipart/form-data">
                <div class="form-group">
                  <label for="to">To *</label>
                  <!--{{ Form::select('email-to', USer::getUserIDList(), '', array("class"=>"select2","id"=>"email-to","tabindex"=>"1")) }}-->
                  <input type="text" class="form-control" value="{{$account->Email}}" id="email-to" name="email-to" tabindex="1"  />
                  <div class="field-options"> <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#cc').parent().removeClass('hidden'); $('#cc').focus();">CC</a> <a href="javascript:;" class="email-cc-text" onclick="$(this).hide(); $('#bcc').parent().removeClass('hidden'); $('#bcc').focus();">BCC</a> </div>
                </div>
                <div class="form-group hidden">
                  <label for="cc">CC</label>
                  <input type="text" name="cc"  class="form-control tags"  id="cc" />
                  </div>
                <div class="form-group hidden">
                  <label for="bcc">BCC</label>
                  <input type="text" name="bcc"  class="form-control tags"  id="bcc" />
                  </div>
                   
                <div class="form-Group" style="margin-bottom: 15px;">
                    <label >Email Template</label>                               
                        {{Form::select('email_template',$emailTemplates,'',array("class"=>"select2 email_template"))}}                                
                </div>
                        
                <div class="form-group">
                  <label for="subject">Subject *</label>
                  <input type="text" class="form-control" id="subject" name="Subject" tabindex="4" />
                  <input  hidden="" name="token_attachment" value="{{$random_token}}" />
                </div>
                <div class="form-group">  
                <label for="subject">Email *</label>                            
                 <textarea id="Message" class="form-control message"    name="Message"></textarea>
                </div>
                <div class="form-group no_margin_bt">
                <p class="comment-box-options-activity"> <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
                </div>
                <div class="form-group email_attachment">
                <input type="hidden" value="1" name="email_send" id="email_send"  />
               <!--   <input id="filecontrole" type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden" multiple data-label="<i class='entypo-attach'></i>Attachments" />-->
               
               <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
                    
                  <span class="file-input-names"></span>
                </div>
                <div class="form-group end-buttons-timeline">                 
                                 <button name="mail_submit" value="save_mail" id="save-mail" class="pull-right save btn btn-primary btn-sm btn-icon btn-send-mail icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-mail"></i>Send</button>                
                  @if(count($boards)>0)
                 <button name="mail_submit" value="save_mail_follow" id="save-email-follow" style="margin-right:10px;" class="pull-right save btn btn-primary btn-sm btn-icon btn-send-mail icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-mail"></i>Send and Create follow up task</button> @endif
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
                  <label for="to">Task Status *</label>
                  @if(count($boards)>0)
                  {{Form::select('TaskStatus',CRMBoardColumn::getTaskStatusList($boards->BoardID),'',array("class"=>"select2"))}}
                    @endif
                   </div>
              </div>
            
              <div class="col-md-6">
                <div class="form-group">
                  <label for="to">Task Assign to *</label>
                  {{Form::select('UsersIDs',$account_owners,User::get_userID(),array("class"=>"select2"))}} </div>
              </div>
             </div> 
              <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label col-sm-12" for="to">Priority</label>
                   <p class="make-switch switch-small">
                                        <input name="Priority" type="checkbox" value="1" >
                                    </p>
                   </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label col-sm-12 " for="to">Due Date</label>
                  <div class="col-sm-8">
                  <input autocomplete="off" type="text" name="DueDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                  </div>
                  <div class="col-sm-4">
                                 <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                  </div>
                  
                </div>
              </div>
			</div>
            <div class="row">              
              <div class="col-md-12">
                <div class="form-group">
                  <label for="to">Task Subject *</label>
                  <input type="text" id="Subject" name="Subject" class="form-control"  tabindex="1" />
                </div>
              </div>
             </div>
             <div class="row"> 
              <div class="col-md-12">
                <div class="form-group">
                  <label for="to">Description</label>
                  <textarea class="form-control autogrow" id="Description" name="Description" placeholder="I will grow as you type new lines." style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 48px;"></textarea>
                </div>
              </div>
              </div>
              <div class="row">
              <div class="col-md-12">
                <div class="form-group end-buttons-timeline">
                   @if(count($boards)>0)
                  <button id="save-task" class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
                  
                   <input type="hidden" value="{{$boards->BoardID}}" name="BoardID"> @endif
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
      <!-- -->
      <!-- -->
        <!--<div class="timeline col-md-11 col-sm-12 col-xs-12">-->
        <div class="timeline timeline_start col-md-9 col-sm-10 col-xs-10 big-col pull-right"> 
          @if(count($response_timeline)>0 && $message=='')
           <ul class="cbp_tmtimeline" id="timeline-ul">
          <li></li>
            <?php  foreach($response_timeline as $key => $rows){
			 // $rows = json_decode(json_encode($rows), True); //convert std array to simple array
			   ?>
            @if(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Mail)
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
              <div class="cbp_tmlabel normal_tag">  
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
            @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Tasks)
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
              
              <div id_toggle="{{$key}}" class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
              <div class="cbp_tmlabel @if(!$rows['followup_task']) normal_tag @endif ">  
                
           <h2 class="toggle_open" id_toggle="{{$key}}"> 
         @if($rows['TaskPriority']=='High')  <i class="edit-deal entypo-record" style="color:#cc2424;font-size:15px;"></i> @endif
           
                @if($rows['CreatedBy']==$current_user_title && $rows['TaskName']==$current_user_title)<span>You created a @if($rows['followup_task']) follow up @endif task</span>
                 @elseif ($rows['CreatedBy']==$current_user_title && $rows['TaskName']!=$current_user_title)<span>You assigned @if($rows['followup_task']) follow up @endif task to {{$rows['TaskName']}} </span> 
                 @elseif ($rows['CreatedBy']!=$current_user_title && $rows['TaskName']==$current_user_title)<span> {{$rows['CreatedBy']}} assigned @if($rows['followup_task']) follow up @endif task to  You </span>
                 @else  <span> {{$rows['CreatedBy']}} assigned @if($rows['followup_task']) follow up @endif task to  {{$rows['TaskName']}} </span> 
                 @endif
</h2>                
                
                <div id="hidden-timeline-{{$key}}"  class="details no-display">
                  <p>Subject: {{$rows['TaskTitle']}}</p>
                  <p>Assigned To: {{$rows['TaskName']}}</p>
                  <p>priority: {{$rows['TaskPriority']}}</p>
                 @if($rows['DueDate']!=''  && $rows['DueDate']!='0000-00-00 00:00:00')<p>Due Date: {{$rows['DueDate']}}</p>@endif
                  <p>Status: {{$rows['TaskStatus']}}. </p>
                  <p>Description: {{$rows['TaskDescription']}} </p>
                </div>
              </div>
            </li>
            @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Note)
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
              <div class="cbp_tmlabel normal_tag">  
                <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else {{$rows['CreatedBy']}}  @endif <span>added a note</span></h2>
                <div id="hidden-timeline-{{$key}}" class="details no-display">
                  <p>{{$rows['Note']}}</p>
                </div>
              </div>
            </li>
            @endif
            <?php  }
			if(count($response_timeline)<10)
			{
			?>
            <li class="timeline-end"><time class="cbp_tmtime"></time><div class="cbp_tmicon bg-info end_timeline_logo "><i class="entypo-infinity"></i></div><div class="end_timeline cbp_tmlabel"><h2></h2><div class="details no-display"></div></div></li>
            <?php
			}
			 ?>
            </ul>
            @if(count($response_timeline)>($data['iDisplayLength'])-1)
          <div id="last_msg_loader"></div>
			@endif
            @else
            <span style="padding:1px;"><h3>No Activity Found.</h3></span>
            @endif
          
        </div> 
    </section>
  </div>
</div>
<div class="followup_task_data hidden">
<ul>
<li></li>
</ul>

</div>
<form id="emai_attachments_form" class="hidden" name="emai_attachments_form">
<span class="emai_attachments_span">
<input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole1">
</span>
<input  hidden="" name="account_id" value="{{$account->AccountID}}" />
<input  hidden="" name="token_attachment" value="{{$random_token}}" />

  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
</form>

@include('includes.submit_note_script',array("controller"=>"accounts")) 
@include("accounts.taskmodal") 
<?php unset($BoardID); ?>
@include('opportunityboards.opportunitymodal')
@include("accounts.activity_jscode",array("response_extensions"=>$response_extensions,"AccountID"=>$account->AccountID,"per_scroll"=>$per_scroll,"token"=>$random_token)) 



 <link rel="stylesheet" href="{{ URL::asset('assets/js/wysihtml5/bootstrap-wysihtml5.css') }}">   
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script> 
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script>

<script src="<?php echo URL::to('/'); ?>/assets/js/select2/select2.js"></script>

<script>
	$(".tags").select2({
                        tags:<?php echo $users; ?>

        });
		
		 $('.opportunityTags').select2({
            tags:{{$opportunitytags}}
        });
</script>

<style>
#last_msg_loader{text-align:center;} .file-input-names{text-align:right; display:block;} ul.grid li div.headerSmall{min-height:31px;} ul.grid li div.box{height:auto;}
ul.grid li div.blockSmall{min-height:20px;} ul.grid li div.cellNoSmall{min-height:20px;} ul.grid li div.action{position:inherit;}
.col-md-3{padding-right:5px;}.big-col{padding-left:5px;}.box-min{margin-top:15px; min-height:225px;} .del_attachment{cursor:pointer;}  .no_margin_bt{margin-bottom:0;}
#account-timeline ul li.follow::before{background:#f5f5f6 none repeat scroll 0 0;}
.cbp_tmtimeline > li.followup_task .cbp_tmlabel::before{margin:0;right:93%;top:-27px;border-color:transparent #f1f1f1 #fff transparent; position:absolute; border-style:solid; border-width:14px;  content: " ";} footer.main{clear:both;} .followup_task {margin-top:-30px;}
</style>
@stop
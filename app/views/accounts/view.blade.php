<?php //$BoardID = $Boards->BoardID;  ?>

@extends('layout.main')
@section('content')
    <style>
        ul.grid li div.box{
            min-height:16.5em;
        }
		#card-features-details .modal-footer{clear:both !important;}
    </style>
<div  style="min-height: 1050px;">
  <ol class="breadcrumb bc-3">
    @if($leadOrAccountCheck=='account')
    <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
    <li> <a href="{{URL::to('accounts')}}">Accounts</a> </li>
    <li>
      <a><span>{{customer_dropbox($account->AccountID)}}</span></a>
    </li>
    <li class="active"> <strong>View Account</strong> </li>
    @elseif($leadOrAccountCheck=='lead')
    <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
    <li> <a href="{{URL::to('/leads')}}">Leads</a> </li>
    <li class="active"> <strong>View Lead</strong> </li>
    @endif
  </ol>
  <h3>View {{$leadOrAccountCheck}}
    <div style="float: right; text-align: right; padding-right:0px; " class="col-sm-6"> @if($leadOrAccountCheck =='lead' &&  User::checkCategoryPermission('Leads','Convert')) <a href="{{ URL::to('leads/'.$account->AccountID.'/convert')}}" class="save btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-floppy"></i>Convert to Account</a> @endif </div>
  </h3>
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
                <div class="col-sm-12 header padding-left-1"> <span class="head"> @if(strlen($Account_card->AccountName)>22) {{substr($Account_card->AccountName,0,22)."..."}} @else {{$Account_card->AccountName}} @endif</span><br>
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

                   <?php

                    $popup_html =  "<label class='col-sm-6' >Invoice Outstanding:</label><div class='col-sm-6'>".$Account_card->OutStandingAmount."</div>
                    <div class='clear'></div><label class='col-sm-6' >Customer Unbilled Amount:</label><div class='col-sm-6' >".$Account_card->CUA."</div>
                    <div class='clear'></div><label class='col-sm-6' >Vendor Unbilled Amount:</label><div class='col-sm-6' >".$Account_card->VUA."</div>
                    <div class='clear'></div><label class='col-sm-6' >Account Exposure:</label><div class='col-sm-6' >".$Account_card->AE."</div>
                    <div class='clear'></div><label class='col-sm-6' >Available Credit Limit:</label><div class='col-sm-6' >".$Account_card->ACL."</div>
                    <div class='clear'></div><label class='col-sm-6' >Balance Threshold:</label><div class='col-sm-6' >".$Account_card->BalanceThreshold."</div>";

                    ?>



                    <div class="meta clear pull-left tooltip-primary" data-original-title="Invoice OutStanding" title="" data-placement="right" data-toggle="tooltip">OS : </div><div class="pull-left"><div class="pull-left" data-toggle="popover3"  data-trigger="hover" data-original-title="" data-content="{{$popup_html}}">{{$Account_card->OutStandingAmount}}</div></div>
                    <div class="meta clear pull-left tooltip-primary" data-original-title="Unbilled Amount" title="" data-placement="right" data-toggle="tooltip">UA : </div><div class="pull-left"><a href="#" class="unbilled_report" data-id="{{$account->AccountID}}">{{$Account_card->UnbilledAmount}}</a></div>
                    <div class="meta clear pull-left tooltip-primary" data-original-title="Credit Limit" title="" data-placement="right" data-toggle="tooltip">CL : </div><div class="pull-left">{{$Account_card->PermanentCredit}}</div>
                  </div>
                  @endif </div>
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
                  <button type="button" data-id="{{$account->AccountID}}" title="Add Opportunity" class="btn btn-default btn-xs opportunity"> <i class="fa fa-line-chart"></i> </button>

                  @if($leadOrAccountCheck=='account') <a href="{{ URL::to('accounts/'.$account->AccountID.'/edit')}}" id="edit_account" target="_blank" class="hidden">Add Contact</a> @elseif($leadOrAccountCheck=='lead') <a href="{{ URL::to('leads/'.$account->AccountID.'/edit')}}" id="edit_account" target="_blank" class="hidden">Add Contact</a> @endif 
                    @if($leadOrAccountCheck=='account' && User::checkCategoryPermission('AccountActivityChart','View'))
                    <a  href="{{Url::to('accounts/activity/'.$account->AccountID)}}"  data-id="{{$account->AccountID}}"  title="Account Activity Chart" class="btn btn-default btn-xs redirect_link" > <i class="fa fa-bar-chart"></i> </a>
                    @endif
                  @if($leadOrAccountCheck=='account' && User::checkCategoryPermission('CreditControl','View'))
                    <a  href="{{Url::to('account/get_credit/'.$account->AccountID)}}"  data-id="{{$account->AccountID}}"  title="Credit Control" class="btn btn-default btn-xs redirect_link" > <i class="fa fa-credit-card"></i> </a>
                  @endif
                  <button type="button" href_id="edit_account" data-id="{{$account->AccountID}}"  title="Edit" class="btn btn-default btn-xs redirect_link" > <i class="entypo-pencil"></i> </button>
                  @if(User::checkCategoryPermission('AccountSubscription','View') && CompanyConfiguration::get('ACCOUNT_SUB') == 1)
                     <a class="btn btn-default btn-xs redirect_link"  title="View Account Subscriptions" href="{{ URL::to('/account_subscription?id='.$account->AccountID)}}"><i class="fa fa-refresh"></i></a>
                  @endif
                  
                  @if($account->IsCustomer==1 || $account->IsVendor==1)
                     <a class="btn btn-default btn-xs redirect_link" title="Authentication Rule" href="{{ URL::to('accounts/authenticate/'.$account->AccountID)}}"><i class="entypo-lock"></i></a>
                  @endif

                  <button type="button" data-id="{{$account->AccountID}}" title="View Account Logs" redirecto="{{ URL::to('accounts/'.$account->AccountID.'/log')}}" class="btn btn-default btn-xs"> <i class="fa fa-file-text-o"></i></button>

                  @if($leadOrAccountCheck=='account')
                  @if($account->IsCustomer==1 && $account->VerificationStatus==Account::VERIFIED)
                     <a class="btn-warning btn btn-default btn-xs" href="{{ URL::to('customers_rates/'.$account->AccountID)}}"><i class="entypo-user"></i></a>
                  @endif
                  @if($account->IsVendor==1 && $account->VerificationStatus==Account::VERIFIED)
           <a class="btn-info btn btn-default btn-xs" href="{{ URL::to('vendor_rates/'.$account->AccountID)}}"><i class="fa fa-slideshare"></i></a>
                   @endif
                  @endif
                   </div>
              </div>
            </li>
          </ul>
        </div>
        @endif 
        <!--Account card end -->
        <div class="">
          <button style="margin:8px 25px 0 0;"  href_id="create_contact" id="redirect_add_link" type="button" class="btn btn-black redirect_link btn-xs pull-right"> <i class="entypo-plus"></i> </button>
          <a href="{{ URL::to('contacts/create?AccountID='.$account->AccountID)}}" id="create_contact" target="_blank" class="hidden">Add Contact</a> <span class="head_title">Contacts</span> </div>
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
                      <div class="meta">Phone: <a href="tel:{{$contacts_row['Phone']}}">{{$contacts_row['Phone']}}</a></div>
                    </div>
                    <div class="cellNo cellNoSmall">
                      <div class="meta">Fax:{{$contacts_row['Fax']}}</div>
                    </div>
                    <div class="block blockSmall">
                      <div class="meta">Skype: <a class="sendemail" href="javascript:void(0)">{{$contacts_row['Skype']}}</a></div>
                    </div>
                  </div>
                  <div class="col-sm-11 padding-0 action"> <a class="btn-default btn-sm label padding-3" href="{{ URL::to('contacts/'.$contacts_row['ContactID'].'/edit')}}"><i class="entypo-pencil"></i>&nbsp;</a>&nbsp;<a class="btn-default btn-sm label padding-3" href="{{ URL::to('contacts/'.$contacts_row['ContactID'].'/show')}}"><i class="entypo-search"></i> </a> </div>
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
                @endif </div>
            </form>
          </div>
        </div>
        <div class="row no-display margin-top-5 box-min" id="box-2" style="margin-bottom: 5px;">
          <div class="col-md-12">
            <div class="mail-compose">
              <form method="post" id="email-from" role="form" enctype="multipart/form-data">
              <div class="form-Group" >                  
                  <div class=" @if($SystemTickets) col-md-10 pull-left @else col-md-12 @endif" style="padding-left:0px; @if(!$SystemTickets) padding-right:0px; @endif" >
                  <label >From</label>
                  {{Form::select('email-from',$FromEmails,User::get_user_email(),array("class"=>"select2"))}}
                  </div>
                  @if($SystemTickets)
                  <div class="col-md-2 pull-right">
                   <label class="control-label" >Open ticket</label>
                    <p class="make-switch switch-small">
                      <input name="createticket" type="checkbox" value="1" >
                    </p>
                  </div>
                  @endif
                  </div>
                  <div class="clearfix" style="margin-bottom: 15px;"></div>
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
                  {{Form::select('email_template',$emailTemplates,'',array("class"=>"select2 email_template","parent_box"=>"mail-compose"))}}
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
                <p class="comment-box-options-activity"> <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)"> <i class="entypo-attach"></i> </a> </p>
                <div class="form-group email_attachment">
                  <input type="hidden" value="1" name="email_send" id="email_send"  />
                  <!--   <input id="filecontrole" type="file" name="emailattachment[]" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden" multiple data-label="<i class='entypo-attach'></i>Attachments" />-->
                  
                  <input id="emailattachment_sent" type="hidden" name="emailattachment_sent" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden"   />
                  <input id="info2" type="hidden" name="attachmentsinfo" />
                  <span class="file-input-names"></span> </div>                
                <div class="form-group end-buttons-timeline">
                  <button name="mail_submit" value="save_mail" id="save-mail" class="pull-right save btn btn-primary btn-sm btn-icon btn-send-mail icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-mail"></i>Send</button>
                  @if(count($boards)>0)
                  <button name="mail_submit" value="save_mail_follow" id="save-email-follow" style="margin-right:10px;" class="pull-right save btn btn-primary btn-sm btn-icon btn-send-mail icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-mail"></i>Send and Create follow up task</button>
                  @endif </div>
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
                    @endif </div>
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
                  <div class="form-group end-buttons-timeline"> @if(count($boards)>0)
                    <button id="save-task" class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden-print" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
                    <input type="hidden" value="{{$boards->BoardID}}" name="BoardID">
                    @endif
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
      <div class="timeline timeline_start col-md-9 col-sm-12 col-xs-12 big-col pull-right"> @if(count($response_timeline)>0 && $message=='')
        <div class="row" style="padding:9px 7px 0;">
          <div class="col-sm-12">
            <ul class="icheck-list">
              <li>
                <form id="form_timeline_filter">
                  <div class="pull-left">
                    <label >Show</label>
                    &nbsp;&nbsp;&nbsp;&nbsp;</div>
                  <div class="radio radio-replace color-primary pull-left">
                    <input class="icheck-11 timeline_filter" show_data="all" type="radio" id="minimal-radio-1" name="timeline_filter" checked>
                    <label for="minimal-radio-1">All</label>
                    &nbsp;&nbsp;</div>
                  <div class="radio radio-replace color-green pull-left">
                    <input class="icheck-11 timeline_filter" show_data="timeline_note_entry" type="radio" id="minimal-radio-2" name="timeline_filter">
                    <label for="minimal-radio-2">Notes</label>
                    &nbsp;&nbsp;</div>
                  <div class="radio radio-replace color-blue pull-left">
                    <input class="icheck-11 timeline_filter" show_data="timeline_task_entry" type="radio" id="minimal-radio-3" name="timeline_filter">
                    <label for="minimal-radio-3">Tasks</label>
                    &nbsp;&nbsp;</div>
                  <div class="radio radio-replace color-gold pull-left">
                    <input class="icheck-11 timeline_filter" show_data="timeline_mail_entry" type="radio" id="minimal-radio-4" name="timeline_filter">
                    <label for="minimal-radio-4">Emails</label>
                  </div>
                  @if($ShowTickets)
                    <div class="radio radio-replace color-red pull-left">
                    <input class="icheck-11 timeline_filter" show_data="timeline_ticket_entry" type="radio" id="minimal-radio-5" name="timeline_filter">
                    <label for="minimal-radio-5">Tickets</label>
                  </div>
                  @endif
                </form>
              </li>
            </ul>
          </div>
        </div>
        <ul class="cbp_tmtimeline" id="timeline-ul">
          <li></li>
          <?php  foreach($response_timeline as $key => $rows){
			 // $rows = json_decode(json_encode($rows), True); //convert std array to simple array
			   ?>
          @if(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Mail)
          <li id="timeline-{{$key}}" class="count-li timeline_mail_entry">
            <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
              <?php if(date("Y-m-d",strtotime($rows['created_at'])) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key}}" class="cbp_tmicon bg-gold"> <i class="entypo-mail"></i> </div>
            <div class="cbp_tmlabel normal_tag">
         <a email_number="{{$rows['AccountEmailLogID']}}" action_type="forward" class="pull-right edit-deal email_action" title="Forward"><i class="entypo-forward"></i></a>            
         <a email_number="{{$rows['AccountEmailLogID']}}" action_type="reply-all" class=" pull-right edit-deal email_action" title="Reply All"><i class="entypo-reply-all"></i></a>           
         <a email_number="{{$rows['AccountEmailLogID']}}" action_type="reply" class="pull-right edit-deal email_action" title="Reply"><i class="entypo-reply"></i></a>
              <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else {{$rows['CreatedBy']}}  @endif <span>sent an email to</span> @if($rows['EmailToName']==$current_user_title) You @else {{$rows['EmailToName']}}  @endif <br> Email From : {{$rows['Emailfrom']}} <br> <p class="mail_subject">Subject: {{$rows['EmailSubject']}}</p></h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display"> @if($rows['EmailCc'])
                <p>CC: {{$rows['EmailCc']}}</p>
                @endif
                @if($rows['EmailBcc'])
                <p>BCC: {{$rows['EmailBcc']}}</p>
                @endif
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
//					if(is_amazon() == true)
//					{
//						$Attachmenturl =  AmazonS3::preSignedUrl($attachments_data['filepath']);
//					}
//					else
//					{
//						$Attachmenturl = CompanyConfiguration::get('UPLOAD_PATH')."/".$attachments_data['filepath'];
//					}
                    $Attachmenturl = URL::to('emails/'.$rows['AccountEmailLogID'].'/getattachment/'.$key_acttachment);
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
                <div class="mail_message">Message:<br>
                  {{$rows['EmailMessage']}}</div><br>
                    <p><a data_fetch_id="{{$rows['AccountEmailLogID']}}" conversations_type="mail"  class="ticket_conversations">View Conversation</a></p>
              </div>
            </div>
          </li>
          @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Tasks)
          <li id="timeline-{{$key}}" class="count-li timeline_task_entry @if($rows['followup_task']) followup_task  @endif">
            <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
              <?php if(date("Y-m-d",strtotime($rows['created_at'])) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key}}" class="cbp_tmicon bg-info"> <i class="entypo-tag"></i> </div>
            <div class="cbp_tmlabel @if(!$rows['followup_task']) normal_tag @endif "> 
            <a id="edit_task_{{$rows['TaskID']}}" task-id="{{$rows['TaskID']}}"  key_id="{{$key}}" class="pull-right edit-deal edit_task_link"><i class="entypo-pencil"></i>&nbsp;</a>
             <a id="delete_task_{{$rows['TaskID']}}" task-id="{{$rows['TaskID']}}"  key_id="{{$key}}" class="pull-right edit-deal delete_task_link"><i class="entypo-trash"></i></a>             
              <h2 class="toggle_open" id_toggle="{{$key}}"> @if($rows['TaskPriority']=='High') <i class="edit-deal entypo-record" style="color:#cc2424;font-size:15px;"></i> @endif
                
                @if($rows['CreatedBy']==$current_user_title && $rows['TaskName']==$current_user_title)<span>You created a @if($rows['followup_task']) follow up @endif task</span> @elseif ($rows['CreatedBy']==$current_user_title && $rows['TaskName']!=$current_user_title)<span>You assigned @if($rows['followup_task']) follow up @endif task to {{$rows['TaskName']}} </span> @elseif ($rows['CreatedBy']!=$current_user_title && $rows['TaskName']==$current_user_title)<span> {{$rows['CreatedBy']}} assigned @if($rows['followup_task']) follow up @endif task to  You </span> @else <span> {{$rows['CreatedBy']}} assigned @if($rows['followup_task']) follow up @endif task to  {{$rows['TaskName']}} </span> @endif </h2>
              <div id="hidden-timeline-{{$key}}"  class="details no-display">
                <p>Subject: {{$rows['TaskTitle']}}</p>
                <p>Assigned To: {{$rows['TaskName']}}</p>
                <p>priority: {{$rows['TaskPriority']}}</p>
                @if($rows['DueDate']!=''  && $rows['DueDate']!='0000-00-00 00:00:00')
                <p>Due Date: {{$rows['DueDate']}}</p>
                @endif
                <p>Status: {{$rows['TaskStatus']}}. </p>
                <p>Description: {{$rows['TaskDescription']}} </p>
              </div>
            </div>
          </li>
          @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Note)
          <li id="timeline-{{$key}}" class="count-li timeline_note_entry">
            <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
              <?php if(date("Y-m-d",strtotime($rows['created_at'])) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key}}" class="cbp_tmicon bg-success"><i class="entypo-doc-text"></i></div>
            <?php
				 $note_type 	= isset($rows['NoteID'])?'NoteID':'ContactNote'; 
				 $noteID		= isset($rows['NoteID'])?$rows['NoteID']:$rows['ContactNoteID'];
			?>
            <div class="cbp_tmlabel normal_tag"> <a id="edit_note_{{$noteID}}" note_type="{{$note_type}}" note-id="{{$noteID}}"  key_id="{{$key}}" class="pull-right edit-deal edit_note_link"><i class="entypo-pencil"></i></a> <a id="delete_note_{{$noteID}}" note_type="{{$note_type}}" note-id="{{$noteID}}"  key_id="{{$key}}" class="pull-right edit-deal delete_note_link"><i class="entypo-trash"></i></a>
              <h2 class="toggle_open" id_toggle="{{$key}}">@if($rows['CreatedBy']==$current_user_title) You @else {{$rows['CreatedBy']}}  @endif <span>added a note</span></h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display">
                <p>{{$rows['Note']}}</p>
              </div>
            </div>
            
          </li>          
           @elseif(isset($rows['Timeline_type']) && $rows['Timeline_type']==Task::Ticket)
          <li id="timeline-{{$key}}" class="count-li timeline_ticket_entry">
            <time class="cbp_tmtime" datetime="<?php echo date("Y-m-d h:i",strtotime($rows['created_at'])); ?>">
              <?php if(date("Y-m-d h:i",strtotime($rows['created_at'])) == date('Y-m-d h:i')) { ?>
              <span>Now</span>
              <?php }else{ ?>
              <span><?php echo date("h:i a",strtotime($rows['created_at']));  ?></span> <span>
              <?php if(date("Y-m-d",strtotime($rows['created_at'])) == date('Y-m-d')){echo "Today";}else{echo date("Y-m-d",strtotime($rows['created_at']));} ?>
              </span>
              <?php } ?>
            </time>
            <div id_toggle="{{$key}}" class="cbp_tmicon bg-danger"><i class="entypo-ticket"></i></div>
            <div class="cbp_tmlabel normal_tag">  
              <h2 class="toggle_open" id_toggle="{{$key}}">Ticket<br><p>Subject: {{$rows['TicketSubject']}}</p></h2>
              <div id="hidden-timeline-{{$key}}" class="details no-display">
                <p>Status: {{$rows['TicketStatus']}}</p>
                <p>Requester: {{$rows['RequestEmail']}}</p>
                <p>Priority: {{$rows['TicketPriority']}}</p>
                <p>Type: {{$rows['TicketType']}}</p>
                <p>Group: {{$rows['TicketGroup']}}</p>
                <p>Date Created: {{$rows['created_at']}}</p>
                <p>Description: {{$rows['TicketDescription']}}</p>
                <p><a data_fetch_id="{{$rows['TicketID']}}" conversations_type="ticket" class="ticket_conversations">View Ticket Conversations</a></p>
              </div>
            </div>
          </li>
          @endif
          
          <?php  }
			if(count($response_timeline)<10)
			{
			?>
          <li class="timeline-end">
            <time class="cbp_tmtime"></time>
            <div class="cbp_tmicon bg-info end_timeline_logo "><i class="entypo-infinity"></i></div>
            <div class="end_timeline cbp_tmlabel">
              <h2></h2>
              <div class="details no-display"></div>
            </div>
          </li>
          <?php
			}
			 ?>
        </ul>
        @if(count($response_timeline)>($data['iDisplayLength'])-1)
        <div id="last_msg_loader"></div>
        @endif
        @else <span style="padding:1px;">
        <h3>No Activity Found.</h3>
        </span> @endif </div>
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
  <input id="info1" type="hidden" name="attachmentsinfo" />
  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
</form>
<form id="emai_attachments_reply_form" class="hidden" name="emai_attachments_form">
  <span class="emai_attachments_span">
  <input type="file" class="fileUploads form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" name="emailattachment[]" multiple id="filecontrole2">
  </span>
  <input  hidden="" name="account_id" value="{{$account->AccountID}}" />
  <input  hidden="" name="token_attachment" value="{{$random_token}}" />
  <input id="info3" type="hidden" name="attachmentsinfo" />
  <button  class="pull-right save btn btn-primary btn-sm btn-icon icon-left hidden" type="submit" data-loading-text="Loading..."><i class="entypo-floppy"></i>Save</button>
</form>

@include('includes.submit_note_script',array("controller"=>"accounts")) 
@include("accounts.taskmodal")
<?php unset($BoardID); ?>
@include('opportunityboards.opportunitymodal')
@include('accounts.unbilledreportmodal')
@include("accounts.activity_jscode",array("response_extensions"=>$response_extensions,"AccountID"=>$account->AccountID,"per_scroll"=>$per_scroll,"token"=>$random_token))
@include('accounts.view_edit_models')
<script>
  jQuery(document).ready(function ($) {
    $("body").popover({
      selector: '[data-toggle="popover3"]',
      trigger:'hover',
      html:true,
      template:'<div class="popover3" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
      //template:'<div class="popover3" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
    });
  });
	$(".tags").select2({
                        tags:<?php echo $users; ?>

        });
		
		 $('.opportunityTags').select2({
            tags:{{$opportunitytags}}
        });
</script>
@stop
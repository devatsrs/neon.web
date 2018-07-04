@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Company</strong>
    </li>
</ol>
<h3>Company</h3>

<div class="panel-title">
    @include('includes.errors')
    @include('includes.success')
</div>
<br>
@if( isset($LicenceApiResponse) && $LicenceApiResponse['Status'] != 1 )
<div  class="clear  toast-container-fix toast-top-full-width margin no-margin-left  ">
        <div class="toast toast-error" style="">
        <div class="toast-title">Licence</div>
        <div class="toast-message">
        {{$LicenceApiResponse['Message']}}
        </div>
    </div>
</div>
<br class="">
@endif


<div class="float-right">
    @if(User::checkCategoryPermission('Company','Edit'))
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>
    @endif
    <!--<a href="{{URL::to('/')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>-->
</div>
<br>
<br>
<div class="row">
    <div class="col-md-12">
        <form role="form" id="form-user-add"  method="post" action="{{URL::current()}}"  class="form-horizontal form-groups-bordered">
            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        Company Information
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">


                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Company Name</label>

                        <div class="col-sm-4">
                            <input type="text" name='CompanyName' class="form-control" id="Text1" placeholder="Company Name" value="{{$company->CompanyName}}">
                        </div>

                         <label for="field-1" class="col-sm-2 control-label">VAT</label>

                        <div class="col-sm-4">
                            <input type="text" name='VAT' class="form-control" id="Text2" placeholder="VAT" value="{{$company->VAT}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Default Customer Trunk Prefix</label>

                        <div class="col-sm-4">
                                 <input name='CustomerAccountPrefix' type="text" class="form-control" placeholder="Default Customer Trunk Prefix" value="{{$company->CustomerAccountPrefix}}">
                         </div>
                        <label class="col-sm-2 control-label">Last Customer Trunk Prefix</label>
                            <div class="col-sm-4">
                                    <input type="text" name='LastPrefixNo' class="form-control" id="Text2" placeholder="Last Customer Trunk Prefix" value="{{$LastPrefixNo}}">
                            </div>    
                    </div>
                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Currency</label>
                                        <div class="col-sm-4">
                                                @if(empty($company->CurrencyId))
                                                    {{Form::SelectControl('currency',1,'',0,'CurrencyId')}}
                                                <!--{Form::select('CurrencyId', $currencies, $company->CurrencyId ,array("class"=>"form-control select2"))}}-->
                                                @else
                                                {{Form::SelectControl('currency',1,$company->CurrencyId,1,'CurrencyId')}}
                                                <!--{Form::select('CurrencyId', $currencies, $company->CurrencyId ,array("class"=>"form-control select2","disabled"))}}-->
                                                {{Form::hidden('CurrencyId', ($company->CurrencyId))}}
                                                @endif
                                        </div>
                                         <label for="field-1" class="col-sm-2 control-label">Timezone</label>
                                         <div class="col-sm-4">
                                             {{Form::select('Timezone', $timezones, $company->TimeZone ,array("class"=>"form-control select2"))}}
                                         </div>
                                        

                                    </div>
                    <div class="form-group"><!--Form Group Added by Abubakar -->
                        <label for="field-1" class="col-sm-2 control-label">Default DashBoard</label>

                        <div class="col-sm-4">
                            {{Form::select('DefaultDashboard', $dashboardlist, $DefaultDashboard ,array("class"=>"form-control select2 small"))}}
                        </div>
                        <!--<label for="field-1" class="col-sm-2 control-label">Pincode/Ext. Widget</label>

                        <p class="make-switch switch-small">
                            <input id="PincodeWidget" name="PincodeWidget" type="checkbox" value="1" if($PincodeWidget == 1) checked="checked" endif>
                        </p>-->

                    </div>

                </div>

            </div>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Contact Person Information
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">


                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">First Name</label>

                        <div class="col-sm-4">
                            <input type="text" name='FirstName' class="form-control" id="Text1" placeholder="First Name" value="{{$company->FirstName}}">
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Last Name</label>

                        <div class="col-sm-4">
                            <input type="text" name='LastName' class="form-control" id="Text2" placeholder="Last Name" value="{{$company->LastName}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Email</label>

                        <div class="col-sm-4">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="entypo-mail"></i></span>
                                <input name='Email' type="text" class="form-control" placeholder="Email" value="{{$company->Email}}">
                            </div>
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Phone</label>

                        <div class="col-sm-4">
                                  <input name='Phone' type="text" class="form-control" placeholder="Phone" value="{{$company->Phone}}">
                         </div>
                    </div>


                </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Address Information
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Address Line 1</label>
                        <div class="col-sm-4">
                            <input type="text" name="Address1" class="form-control" id="field-1" placeholder="Address Line 1" value="{{$company->Address1}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">City</label>
                        <div class="col-sm-4">
                            <input type="text" name="City" class="form-control" id="field-1" placeholder="City" value="{{$company->City}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Address Line 2</label>
                        <div class="col-sm-4">
                            <input type="text" name="Address2" class="form-control" id="field-1" placeholder="Address Line 2" value="{{$company->Address2}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Post/Zip Code</label>
                        <div class="col-sm-4">
                            <input type="text" name="PostCode" class="form-control" id="field-1" placeholder="Post/Zip Code" value="{{$company->PostCode}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Address Line 3</label>
                        <div class="col-sm-4">
                            <input type="text" name="Address3" class="form-control" id="field-1" placeholder="Address Line 3" value="{{$company->Address3}}" />
                        </div>
                        <label for=" field-1" class="col-sm-2 control-label">Country</label>
                        <div class="col-sm-4">
                            {{Form::select('Country', $countries, $company->Country ,array("class"=>"form-control select2 small"))}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Setting
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>
                            <div class="panel-body">

                                <div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">Invoice Status</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="InvoiceStatus" name="InvoiceStatus" value="{{$company->InvoiceStatus}}" />
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">Use Prefix In CDR</label>
                                    <p class="make-switch switch-small">
                                        <input id="UseInBilling" name="UseInBilling" type="checkbox" value="1" @if($UseInBilling == 1) checked="checked" @endif>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">Decimal Places (123.45) </label>
                                    <div class="col-sm-4">
                                        <div class="input-spinner">
                                            <button type="button" class="btn btn-default">-</button>
                                            {{Form::text('RoundChargesAmount', $RoundChargesAmount,array("class"=>"form-control", "maxlength"=>"1", "data-min"=>0,"data-max"=>6,"Placeholder"=>"Add Numeric value" , "data-mask"=>"decimal"))}}
                                            <button type="button" class="btn btn-default">+</button>
                                        </div>
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label"> Account Verification </label>
                                    <p class="make-switch switch-small">
                                        <input id="AccountVerification" name="AccountVerification" type="checkbox" value="1" @if($AccountVerification == 1) checked="checked" @endif>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">Rate Sheet Template <br/> allowed extensions (.xls,.xlsx) </label>
                                    <div class="col-sm-4">
                                        <input name="RateSheetTemplateFile" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;Browse" />
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">Your Rate Sheet Template</label>
                                    <div class="col-sm-4">
                                        @if(isset($RateSheetTemplateFile) && $RateSheetTemplateFile != '')
                                            <a href="{{URL::to('company/download_rate_sheet_template')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>
                                        @else
                                            <a href="#" class="btn btn-default btn-sm btn-icon icon-left disabled"><i class="entypo-down"></i>Download</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">No of Header Rows <span data-original-title="No of Header Rows" data-content="If your header has 4 rows occupied in template file than you have to put 4 here and if template file doesn't have header than put 0 here" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></label>
                                    <div class="col-sm-4">
                                        {{Form::text('RateSheetTemplate[HeaderSize]', $RateSheetTemplate['HeaderSize'],array("class"=>"form-control","Placeholder"=>"Add Numeric value"))}}
                                    </div>
                                    <div class="col-sm-4 pull-right">
                                        <a href="{{URL::to('company/download_rate_sheet_default_template')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label pull-right">Sample Rate Sheet Template</label>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">No of Footer Rows <span data-original-title="No of Footer Rows" data-content="If your footer has 4 rows occupied in template file than you have to put 4 here and if template file doesn't have footer than put 0 here" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span> </label>
                                    <div class="col-sm-4">
                                        {{Form::text('RateSheetTemplate[FooterSize]', $RateSheetTemplate['FooterSize'],array("class"=>"form-control","Placeholder"=>"Add Numeric value"))}}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">Email invoice as an attachment</label>
                                    <div class="col-sm-4">
                                        <p class="make-switch switch-small">
                                            <input id="invoicePdfSend" name="invoicePdfSend" type="checkbox" value="1" @if($invoicePdfSend == 1) checked="checked" @endif>
                                        </p>
                                    </div>
                                </div>
                                {{--<div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">RateSheet excel Note</label>
                                    <div class="col-sm-10">
                                        <textarea type="text" name="RateSheetExcellNote" rows="5" class="form-control" id="field-1" placeholder="Rate Sheet Excell Note">{{$company->RateSheetExcellNote}}</textarea>
                                    </div>
                                </div>--}}
                            </div>
                        </div>

            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Digital signature PDF
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Image</label>
                        <div class="col-sm-4">
                            <input name="signatureImage" type="file" accept=".png" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;Browse" />
                            @if(isset($DigitalSignature["image"]) &&  !empty($DigitalSignature["image"]))
                                <a href="{{URL::to('company/download_digitalSignature/image')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>
                            @endif
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Use Certificate</label>
                        <p class="make-switch switch-small">
                            <input name="UseDigitalSignature" type="checkbox" value="1" @if($UseDigitalSignature == 1) checked="checked" @endif>
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Image position Left</label>
                        <div class="col-sm-4">
                            <div class="input-spinner pull-left">
                                <button type="button" class="btn btn-default">-</button>
                                <input class="form-control" placeholder="" data-mask="decimal" name="signatureCertpPositionLeft" value="{{$DigitalSignature['positionLeft']}}" type="text">
                                <button type="button" class="btn btn-default">+</button>
                            </div>
                        </div>
                        <label class="col-sm-2 control-label">Image position Top</label>
                        <div class="col-sm-4">
                            <div class="input-spinner pull-left">
                                <button type="button" class="btn btn-default">-</button>
                                <input class="form-control" placeholder="" data-mask="decimal" name="signatureCertpPositionTop" value="{{$DigitalSignature['positionTop']}}" type="text">
                                <button type="button" class="btn btn-default">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Mail Settings  <button data-loading-text="Loading..." title="Validate Mail Settings"  type="button" class="ValidateSmtp btn btn-primary">Test</button> 
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">SMTP Server</label>
                        <div class="col-sm-4">
                            <input type="text" name="SMTPServer" class="form-control" id="field-1" placeholder="SMTP Server" value="{{$company->SMTPServer}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Email From</label>
                        <div class="col-sm-4">
                            <input type="text" name="EmailFrom" class="form-control" id="field-1" placeholder="Email From" value="{{$company->EmailFrom}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">SMTP User</label>
                        <div class="col-sm-4">
                            <input type="text" name="SMTPUsername" class="form-control" id="field-1" placeholder="SMTP User" value="{{$company->SMTPUsername}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Password</label>
                        <div class="col-sm-4">
                            <input type="password" name="SMTPPassword" class="form-control" id="field-1" placeholder="Password" value="" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Port</label>
                        <div class="col-sm-4">
                            <input type="text" name="Port" class="form-control" id="field-1" placeholder="Port" value="{{$company->Port}}" />
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Enable SSL</label>
                        <div class="col-sm-4">
                            <div class="make-switch switch-small" data-on-label="ON" data-off-label="OFF">



                                <input type="checkbox" name="IsSSL" @if($company->IsSSL == 1 )checked=""@endif value="1">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            @if(empty(is_reseller()))

            @if(isset($COMPANY_SSH_VISIBLE) && $COMPANY_SSH_VISIBLE == 1)
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        SSH Details
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Host</label>
                        <div class="col-sm-10">
                            <input type="text" name="SSH[host]" class="form-control" placeholder="Host" value="{{$SSH['host']}}" />
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Username</label>
                        <div class="col-sm-10">
                            <input type="text" name="SSH[username]" class="form-control" placeholder="username" value="{{$SSH['username']}}" />
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Password</label>
                        <div class="col-sm-10">
                            <input type="password" name="SSH[password]" class="form-control" placeholder="password" />
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="panel panel-primary" data-collapsed="0">
                  <div class="panel-heading">
                        <div class="panel-title">
                                Licence Information
                        </div>
                        <div class="panel-options">
                              <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                  </div>
                  <div class="panel-body">
                          <div class="form-group">
                              <label for="field-1" class="col-sm-2 control-label">License key</label>
                              <div class="col-sm-10">
                                    <span class="col-sm-12 form-control">{{$LicenceApiResponse['LicenceKey']}}</span>
                              </div>
                          </div>
                          <div class="clear"></div>
                          <div class="form-group">
                              <label for="field-1" class="col-sm-2 control-label">Expiry Date</label>
                              <div class="col-sm-10">
                                    <span class="col-sm-12 form-control">{{$LicenceApiResponse['ExpiryDate']}}</span>
                              </div>
                          </div>
                          <div class="clear"></div>
                          <div class="form-group">
                              <label for="field-1" class="col-sm-2 control-label">Host</label>
                              <div class="col-sm-10">
                                    <span class="col-sm-12 form-control">{{$LicenceApiResponse['LicenceHost']}}</span>
                              </div>
                          </div>
                          <div class="clear"></div>
                          <div class="form-group">
                              <label for="field-1" class="col-sm-2 control-label">IP</label>
                              <div class="col-sm-10">
                                    <span class="col-sm-12 form-control">{{$LicenceApiResponse['LicenceIP']}}</span>
                              </div>
                          </div>
                  </div>
            </div>

            @endif

        </form>
    </div>
</div>

<div class="modal fade" id="Test_smtp_mail_modal">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="Test_smtp_mail_form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="model-title-set modal-title">Test Mail Settings</h4>
        </div>
        <div class="modal-body">
          <div class="row">            
            <div class="col-md-10 margin-top">
              <div class="form-group">
                <label for="SampleEmail" class="control-label col-sm-3">Send Test Email To *</label>
                <div class="col-sm-5">
                  <input type="email" required name="SampleEmail" id="SampleEmail" class="form-control"  placeholder="">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">           
          <button type="submit"   class="btn_smtp_submit btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Send </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {

        // Replace Checboxes
        $(".save.btn").click(function(ev) {
            $('#form-user-add').submit();
           // $(this).attr('disabled', 'disabled'); 
        });
		
		$('#Test_smtp_mail_form').submit(function(e) {
			$('.model-title-set').html('Sending Test Email...');
			 $('.btn_smtp_submit').button('loading');
			 console.log('form submitted');
			e.preventDefault();
			e.stopImmediatePropagation();
				var SampleEmail 	=  $("#Test_smtp_mail_form [name='SampleEmail']").val();				
				var SMTPServer 		=  $("#form-user-add [name='SMTPServer']").val();
				var EmailFrom 		=  $("#form-user-add [name='EmailFrom']").val();
				var SMTPUsername 	=  $("#form-user-add [name='SMTPUsername']").val();
				var SMTPPassword 	=  $("#form-user-add [name='SMTPPassword']").val();
				var Port 			=  $("#form-user-add [name='Port']").val();
				var IsSSL 			=  $("#form-user-add [name='IsSSL']").prop("checked");
				
					
			
				var ValidateUrl 			=  "<?php echo URL::to('/company/validatesmtp'); ?>";

				 $.ajax({
					url: ValidateUrl,
					type: 'POST',
					dataType: 'json',
					data:{SampleEmail:SampleEmail,SMTPServer:SMTPServer,EmailFrom:EmailFrom,SMTPUsername:SMTPUsername,SMTPPassword:SMTPPassword,Port:Port,IsSSL:IsSSL},
					success: function(Response) {
				    $('.ValidateSmtp').button('reset');
					$('.btn_smtp_submit').button('reset');
					$('.ValidateSmtp').removeAttr('disabled');
						 if (Response.status == 'failed') {
	                           toastr.error(Response.message, "Error", toastr_opts);
							   return false;
                          }
						  alert(Response.response);
						  $('#Test_smtp_mail_modal').modal('hide'); 
						  //$('.SmtpResponse').html(Response.response);
						  $('.model-title-set').html('Test Mail Settings');
						  
						}
				});	
        
            	
        });
		
		$('.ValidateSmtp').click(function(e) {
        	$(this).attr('disabled', 'disabled');  
			
				$('#Test_smtp_mail_modal').modal('show'); return false;
				
        });
		
		
		 $('#Test_smtp_mail_modal').on('shown.bs.modal', function(event){
			  $('.model-title-set').html('Test Mail Settings');
		 });
		 
		  $('#Test_smtp_mail_modal').on('hidden.bs.modal', function(event){
			  $('.model-title-set').html('Test Mail Settings');
			  $('.ValidateSmtp').button('reset');
			  $('.ValidateSmtp').removeAttr('disabled');
		 });
		 
        $('select[name="BillingCycleType"]').on( "change",function(e){
                var selection = $(this).val();
                $(".billing_options input, .billing_options select").attr("disabled", "disabled");
                $(".billing_options").hide();
                console.log(selection);
                switch (selection){
                    case "weekly":
                            $("#billing_cycle_weekly").show();
                            $("#billing_cycle_weekly select").removeAttr("disabled");
                            break;
                    case "monthly_anniversary":
                            $("#billing_cycle_monthly_anniversary").show();
                            $("#billing_cycle_monthly_anniversary input").removeAttr("disabled");
                            break;
                    case "in_specific_days":
                            $("#billing_cycle_in_specific_days").show();
                            $("#billing_cycle_in_specific_days input").removeAttr("disabled");
                            break;
                }
            });
            $('select[name="BillingCycleType"]').trigger( "change" );
        $("#InvoiceStatus").select2({
            tags:{{json_encode(explode(',',$company->InvoiceStatus))}}
        });
    });
  
</script>
<style>
    .popover{
        min-width:300px !important;
    }
</style>
@include('includes.ajax_submit_script', array('formID'=>'form-user-add' , 'url' => 'company/update'))
@include('currencies.currencymodal')
@stop
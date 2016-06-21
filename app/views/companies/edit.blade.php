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
                                                {{Form::select('CurrencyId', $currencies, $company->CurrencyId ,array("class"=>"form-control select2"))}}
                                                @else
                                                {{Form::select('CurrencyId', $currencies, $company->CurrencyId ,array("class"=>"form-control select2","disabled"))}}
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
                            {{Form::select('DefaultDashboard', $dashboardlist, $DefaultDashboard ,array("class"=>"form-control selectboxit"))}}
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Pincode/Ext. Widget</label>

                        <p class="make-switch switch-small">
                            <input id="PincodeWidget" name="PincodeWidget" type="checkbox" value="1" @if($PincodeWidget == 1) checked="checked" @endif>
                        </p>

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
                        Cron Job Email Setup
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Rate Generation Emails (Multiple Email addresses with comma separated)</label>
                        <div class="col-sm-4">
                            <input type="text" name="RateGenerationEmail" class="form-control" id="field-1" placeholder="Rate Generation Emails" value="{{$RateGenerationEmail}}" />
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Invoice Generation Emails (Multiple Email addresses with comma separated)</label>
                        <div class="col-sm-4">
                            <input type="text" name="InvoiceGenerationEmail" class="form-control" id="field-1" placeholder="Invoice Generation Emails" value="{{$InvoiceGenerationEmail}}" />
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
                            {{Form::select('Country', $countries, $company->Country ,array("class"=>"form-control selectboxit"))}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-primary" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Billing Setting
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">Billing Timezone</label>
                                    <div class="col-sm-4">
                                        {{Form::select('BillingTimezone', $timezones,$BillingTimezone,array("class"=>"form-control select2"))}}
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">CDR Format</label>
                                    <div class="col-sm-4">
                                        {{Form::select('CDRType', Account::$cdr_type, $CDRType,array("class"=>"selectboxit"))}}
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label for="field-1" class="col-sm-2 control-label">Round Charged Amount (123.45) </label>
                                   <div class="col-sm-4">
                                       <div class="input-spinner">
                                           <button type="button" class="btn btn-default">-</button>
                                           {{Form::text('RoundChargesAmount', $RoundChargesAmount,array("class"=>"form-control", "maxlength"=>"1", "data-min"=>0,"data-max"=>4,"Placeholder"=>"Add Numeric value" , "data-mask"=>"decimal"))}}
                                           <button type="button" class="btn btn-default">+</button>
                                       </div>
                                   </div>
                                   <label for="field-1" class="col-sm-2 control-label">Payment is expected within (Days)</label>
                                   <div class="col-sm-4">
                                       <div class="input-spinner">
                                           <button type="button" class="btn btn-default">-</button>
                                           {{Form::text('PaymentDueInDays',$PaymentDueInDays,array("class"=>"form-control","data-min"=>0, "maxlength"=>"2", "data-max"=>30,"Placeholder"=>"Add Numeric value", "data-mask"=>"decimal"))}}
                                           <button type="button" class="btn btn-default">+</button>
                                       </div>
                                   </div>
                                </div>
                                <div class="form-group">
                                                    <label for="field-1" class="col-sm-2 control-label">Billing cycle</label>
                                                    <div class="col-sm-4">
                                                        {{Form::select('BillingCycleType', SortBillingType(), $BillingCycleType,array("class"=>"form-control select2"))}}
                                                    </div>
                                                    <div id="billing_cycle_weekly" class="billing_options" style="display: none">
                                                        <label for="field-1" class="col-sm-2 control-label">Billing cycle - Start of Day</label>
                                                        <div class="col-sm-4">
                                                            <?php $Days = array( ""=>"Please Start of Day",
                                                                "monday"=>"Monday",
                                                                "tuesday"=>"Tuesday",
                                                                "wednesday"=>"Wednesday",
                                                                "thursday"=>"Thursday",
                                                                "friday"=>"Friday",
                                                                "saturday"=>"Saturday",
                                                                "sunday"=>"Sunday");?>
                                                            {{Form::select('BillingCycleValue',$Days, $BillingCycleValue ,array("class"=>"form-control select2"))}}
                                                        </div>
                                                    </div>
                                                    <div id="billing_cycle_in_specific_days" class="billing_options" style="display: none">
                                                    <label for="field-1" class="col-sm-2 control-label">Billing cycle - for Days</label>
                                                        <div class="col-sm-4">
                                                            {{Form::text('BillingCycleValue', $BillingCycleValue ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Billing Days"))}}
                                                        </div>
                                                    </div>
                                                    <div id="billing_cycle_monthly_anniversary" class="billing_options" style="display: none">
                                                        <label for="field-1" class="col-sm-2 control-label">Billing cycle - Monthly Anniversary Date</label>
                                                        <div class="col-sm-4">
                                                            {{Form::text('BillingCycleValue', $BillingCycleValue,array("class"=>"form-control datepicker","Placeholder"=>"Anniversary Date" , "data-start-date"=>"" ,"data-date-format"=>"dd-mm-yyyy", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
                                                        </div>
                                                    </div>
                                                </div>
                                <div class="form-group">

                                        <label for="field-1" class="col-sm-2 control-label">Invoice Template</label>
                                        <div class="col-sm-4">
                                            {{Form::select('InvoiceTemplateID', $InvoiceTemplates, $InvoiceTemplateID,array("class"=>"form-control select2"))}}
                                        </div>

                                    <label for="field-1" class="col-sm-2 control-label">Invoice Status</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="InvoiceStatus" name="InvoiceStatus" value="{{$company->InvoiceStatus}}" />
                                    </div>
                                </div>
                                <div class="form-group">

                                    <label for="field-1" class="col-sm-2 control-label">Payment Request Email (Multiple Email addresses with comma separated)</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="PaymentRequestEmail" class="form-control" id="field-1" placeholder="Payment Request Emails" value="{{$company->PaymentRequestEmail}}" />
                                    </div>

                                    <label for="field-1" class="col-sm-2 control-label">Due Sheet Email</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="DueSheetEmail" class="form-control" id="field-1" placeholder="Due Sheet Email" value="{{$company->DueSheetEmail}}" />
                                    </div>
                                </div>
                                <div class="form-group" >
                                <label for="field-1" class="col-sm-2 control-label">SalesBoard Timezone</label>
                                    <div class="col-sm-4">
                                        {{Form::select('SalesTimeZone', $timezones,$SalesTimeZone,array("class"=>"form-control select2"))}}
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">Use Prefix In CDR</label>
                                    <p class="make-switch switch-small">
                                        <input id="UseInBilling" name="UseInBilling" type="checkbox" value="1" @if($UseInBilling == 1) checked="checked" @endif>
                                    </p>
                                </div>
                                <div class="form-group" >
                                    <label for="field-1" class="col-sm-2 control-label">Default Tax Rate</label>
                                    <div class="col-sm-4">
                                        {{Form::select('DefaultTextRate[]', $taxrates, (isset($DefaultTextRate)? explode(',',$DefaultTextRate) : '' ) ,array("class"=>"form-control select2",'multiple'))}}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">RateSheet excel Note</label>
                                    <div class="col-sm-10">
                                        <textarea type="text" name="RateSheetExcellNote" rows="5" class="form-control" id="field-1" placeholder="Rate Sheet Excell Note">{{$company->RateSheetExcellNote}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Mail Settings
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
                            <input type="password" name="SMTPPassword" class="form-control" id="field-1" placeholder="Password" value="{{$company->SMTPPassword}}" />
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
					<div class="form-group"> 
                    <label  class="col-sm-2 control-label" style="visibility:hidden;">Enable SSL</label>
                        <div class="col-sm-1">
                        <button data-loading-text="Loading..."  type="button" class="ValidateSmtp btn btn-primary">Test</button>
                        </div>
                          <div class="col-sm-1 SmtpResponse">
                          </div>
                    </div>
                </div>
            </div>

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

        </form>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function($) {

        // Replace Checboxes
        $(".save.btn").click(function(ev) {
            $('#form-user-add').submit();
            $(this).attr('disabled', 'disabled'); 
        });
		
		$('.ValidateSmtp').click(function(e) {
        	$(this).attr('disabled', 'disabled');  
			
				var ValidateUrl 	=  "<?php echo URL::to('/company/validatesmtp'); ?>";
				var form_data 		=  $('#form-user-add').serialize();
				$('.SmtpResponse').html('');
				 $.ajax({
					url: ValidateUrl,
					type: 'POST',
					dataType: 'json',
					async :false,
					data:form_data,
					success: function(Response) {
				    $('.ValidateSmtp').button('reset');
						 if (Response.status == 'failed') {
	                           toastr.error(Response.message, "Error", toastr_opts);
							   return false;
                          }
						  $('.SmtpResponse').html(Response.response);
						},
				});	
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
@include('includes.ajax_submit_script', array('formID'=>'form-user-add' , 'url' => 'company/update'))
@stop
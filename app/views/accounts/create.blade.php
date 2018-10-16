@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>

        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li class="active">
        <strong>New Account</strong>
    </li>
</ol>
<h3>New Account</h3>
@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..." id="save_account">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('/accounts')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</p>
<br>
<div class="row">
    <div class="col-md-12">
             <form role="form" id="account-from" method="post" action="{{URL::to('accounts/store')}}" class="form-horizontal form-groups-bordered">

            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Account Details
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Account Owner</label>
                        <div class="col-md-4">
                            {{Form::select('Owner',$account_owners,User::get_userID(),array("class"=>"select2"))}}
                        </div>

                        <label class="col-md-2 control-label">Ownership</label>
                        <div class="col-md-4">
                            <?php $ownership_array = array( ""=>"None", "Private"=>"Private" , "Public"=>"Public" ,"Subsidiary"=>"Subsidiary","Other"=>"Other" ); ?>
                            {{Form::select('Ownership', $ownership_array, Input::old('Ownership') ,array("class"=>"form-control"))}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">First Name</label>
                        <div class="col-md-4">
                            <input type="text" name="FirstName" class="form-control" id="field-1" placeholder="" value="{{Input::old('FirstName')}}" />
                        </div>

                        <label class="col-md-2 control-label">Last Name</label>
                        <div class="col-md-4">
                            <input type="text" name="LastName" class="form-control" id="field-1" placeholder="" value="{{Input::old('LastName')}}" />
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Account Number</label>
                        <div class="col-md-4">
                            <input type="text" name="Number" class="form-control" id="field-1" placeholder="AUTO" value="{{ $LastAccountNo   }}" />
                        </div>

                        <label class="col-md-2 control-label">Website</label>
                        <div class="col-md-4">
                            <input type="text" name="Website" class="form-control" id="field-1" placeholder="" value="{{Input::old('Website')}}" />
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">*Account Name</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="AccountName" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder="" value="{{Input::old('AccountName')}}" />
                        </div>

                        <label class="col-md-2 control-label">Phone</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control"  name="Phone" id="field-1" placeholder="" value="{{Input::old('Phone')}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Vendor</label>
                        <div class="col-md-4">
                            <div class="make-switch switch-small" id="desablevendor">
                                <input type="checkbox" name="IsVendor"  @if(Input::old('IsVendor') == 1 )checked=""@endif value="1">
                            </div>
                        </div>

                        <label class="col-md-2 control-label">Fax</label>
                        <div class="col-md-4">
                            <input type="text" name="Fax" class="form-control" id="field-1" placeholder="" value="{{Input::old('Fax')}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Customer</label>
                        <div class="col-md-4">
                            <div class="make-switch switch-small" id="desablecustomer">
                                <input type="checkbox" name="IsCustomer"  @if(Input::old('IsCustomer') == 1 )checked=""@endif value="1">
                            </div>
                        </div>

                        <label class="col-md-2 control-label">Employee</label>
                        <div class="col-md-4">
                            <input type="text" name="Employee" class="form-control" id="field-1" placeholder="" value="{{Input::old('Employee')}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Reseller</label>
                        <div class="col-md-4">
                            <div class="make-switch switch-small" id="desablereseller">
                                <input type="checkbox" name="IsReseller"  @if(Input::old('IsReseller') == 1 )checked=""@endif value="1">
                            </div>
                        </div>

                        <label class="col-md-2 control-label">Account Reseller</label>
                        <div class="col-md-4" id="disableresellerowner">
                            {{Form::select('ResellerOwner',$reseller_owners,'',array("class"=>"select2"))}}
                        </div>

                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Email</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="Email" data-validate="required" data-message-required="" id="field-1" placeholder="" value="{{Input::old('Email')}}" />
                        </div>

                        <label class="col-md-2 control-label">Billing Email</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control"  name="BillingEmail" id="field-1" placeholder="" value="{{Input::old('BillingEmail')}}" />
                        </div>

                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">Active</label>
                        <div class="col-md-4">
                            <div class="make-switch switch-small" >
                                <input type="checkbox" name="Status" checked value="1">
                            </div>
                        </div>

                        <label class="col-md-2 control-label">VAT Number</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control"  name="VatNumber" id="field-1" placeholder="" value="{{Input::old('VatNumber')}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Currency</label>
                        <div class="col-md-4">
                                <!--{Form::select('CurrencyId', $currencies, '' ,array("class"=>"form-control select2"))}}-->
                                {{Form::SelectControl('currency',0,$company->CurrencyId,0,'CurrencyId')}}
                        </div>

                        <label class="col-md-2 control-label">Timezone</label>
                        <div class="col-md-4">
                            {{Form::select('TimeZone', $timezones, '' ,array("class"=>"form-control select2"))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Verification Status</label>
                        <div class="col-md-4">
                            {{Form::select('VerificationStatus', Account::$doc_status,Account::NOT_VERIFIED,array("class"=>"select2 small",'disabled'=>'disabled'))}}
                             <input type="hidden" class="form-control"  name="VerificationStatus" value="{{Account::NOT_VERIFIED}}">
                        </div>
                        <label class="col-md-2 control-label">Nominal Code</label>
                        <div class="col-md-4">
                            <input type="text" class="form-control"  name="NominalAnalysisNominalAccountNumber" id="field-1" placeholder="" value="{{Input::old('NominalAnalysisNominalAccountNumber')}}" />
                        </div>
                    </div>
                    @if(!empty($dynamicfields) && count($dynamicfields)>0)
                        <div class="form-group">
                            @foreach($dynamicfields as $dynamicfield)
                                @if(!empty($dynamicfield['FieldSlug']))
                                    @if($dynamicfield['FieldSlug']=='accountgateway')
                                        <label class="col-md-2 control-label">{{$dynamicfield['FieldName']}}</label>
                                        <div class="col-md-4">
                                            {{Form::select('accountgateway[]', CompanyGateway::getCompanyGatewayIdList(), '' ,array("class"=>"form-control select2",'multiple'))}}
                                        </div>
                                    @endif
                                    @if($dynamicfield['FieldSlug']=='vendorname')
                                        <label class="col-md-2 control-label">{{$dynamicfield['FieldName']}}</label>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" autocomplete="off"  name="vendorname" id="field-1" value="" />
                                        </div>
                                    @endif
                                        @if($dynamicfield['FieldSlug']=='pbxaccountstatus')
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">{{$dynamicfield['FieldName']}}</label>
                                        <div class="col-md-4">
                                            {{Form::select('pbxaccountstatus', array('0'=>'Unblock','1'=>'Block'),'',array("class"=>"form-control select2"))}}
                                        </div>
                                        @endif
                                        @if($dynamicfield['FieldSlug']=='autoblock')
                                            <label class="col-md-2 control-label">{{$dynamicfield['FieldName']}} <span id="tooltip_lowstock" data-content="If Auto block OFF then Cron job will not change the status of this Account in PBX." data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary" data-original-title="" title="">?</span></label>
                                            <div class="col-md-4">
                                                <div class="make-switch switch-small">
                                                    <input type="checkbox" name="autoblock" value="1">
                                                </div>
                                            </div>
                                        @endif
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <div class="form-group">
                        <label class="col-md-2 control-label">Languages</label>
                        <div class="col-md-4">
                            {{ddl_language("", "LanguageID", ( isset($AccountBilling->Language)?$AccountBilling->Language:Translation::$default_lang_id ),"", "id")}}
                        </div>
                    </div>

                    <div class="panel-title desc clear">
                        Description
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <textarea class="form-control" name="Description" id="events_log" rows="5" placeholder="Description">{{Input::old('Description')}}</textarea>
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
                                    <label class="col-md-2 control-label">Address Line 1</label>
                                    <div class="col-md-4">
                                        <input type="text" name="Address1" class="form-control" id="field-1" placeholder="" value="{{Input::old('Address1')}}" />
                                    </div>

                                    <label class="col-md-2 control-label">City</label>
                                    <div class="col-md-4">
                                        <input type="text" name="City" class="form-control" id="field-1" placeholder="" value="{{Input::old('City')}}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Address Line 2</label>
                                    <div class="col-md-4">
                                        <input type="text" name="Address2" class="form-control" id="field-1" placeholder="" value="{{Input::old('Address2')}}" />
                                    </div>

                                    <label class="col-md-2 control-label">Post/Zip Code</label>
                                    <div class="col-md-4">
                                        <input type="text" name="PostCode" class="form-control" id="field-1" placeholder="" value="{{Input::old('PostCode')}}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label">Address Line 3</label>
                                    <div class="col-md-4">
                                        <input type="text" name="Address3" class="form-control" id="field-1" placeholder="" value="{{Input::old('Address3')}}" />
                                    </div>

                                    <label for=" field-1" class="col-md-2 control-label">*Country</label>
                                    <div class="col-md-4">

                                        {{Form::select('Country', $countries,Input::old('Country', $company->Country),array("class"=>"form-control select2"))}}

                                    </div>
                                </div>
                            </div>
                        </div>
            <div class="panel panel-primary billing-section-hide" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Billing
                    </div>

                    <div class="panel-options">
                        <div class="make-switch switch-small">
                            <input type="checkbox" name="Billing" value="1">
                        </div>
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body billing-section">
                    <div class="form-group">
                        <label class="col-md-2 control-label">Billing Class*</label>
                        <div class="col-md-4">
                            {{Form::SelectControl('billing_class',1)}}
                            <!--{Form::select('BillingClassID', $BillingClass, '' ,array("class"=>"select2 small form-control1"));}}-->
                        </div>
                        <label class="col-md-2 control-label">Billing Type*</label>
                        <div class="col-md-4">
                            {{Form::select('BillingType', AccountApproval::$billing_type, '1',array('id'=>'billing_type',"class"=>"select2 small"))}}
                        </div>

                    </div>
                    <div class="form-group">

                        <label class="col-md-2 control-label">Billing Timezone*</label>
                        <div class="col-md-4">
                            {{Form::select('BillingTimezone', $timezones, '' ,array("class"=>"form-control select2"))}}
                        </div>
                        <label class="col-md-2 control-label">Billing Start Date*</label>
                        <div class="col-md-2">
                            {{Form::text('BillingStartDate','',array('class'=>'form-control datepicker billing_start_date',"data-date-format"=>"yyyy-mm-dd"))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Billing Cycle*</label>
                        <div class="col-md-4">
                            {{Form::select('BillingCycleType', SortBillingType(1), '' ,array("class"=>"form-control select2"))}}
                        </div>
                        <div id="billing_cycle_weekly" class="billing_options" style="display: none">
                            <label class="col-md-2 control-label">Billing Cycle - Start of Day*</label>
                            <div class="col-md-4">
                                <?php $Days = array( ""=>"Select",
                                    "monday"=>"Monday",
                                    "tuesday"=>"Tuesday",
                                    "wednesday"=>"Wednesday",
                                    "thursday"=>"Thursday",
                                    "friday"=>"Friday",
                                    "saturday"=>"Saturday",
                                    "sunday"=>"Sunday");?>
                                {{Form::select('BillingCycleValue',$Days,''  ,array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                        <div id="billing_cycle_in_specific_days" class="billing_options" style="display: none">
                        <label class="col-md-2 control-label">Billing Cycle - for Days*</label>
                            <div class="col-md-4">
                                {{Form::text('BillingCycleValue', '' ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Billing Days"))}}
                            </div>
                        </div>
                        <div id="billing_cycle_subscription" class="billing_options" style="display: none">
                        <label class="col-md-2 control-label">Billing Cycle - Subscription Qty*</label>
                            <div class="col-md-4">
                                {{Form::text('BillingCycleValue', '' ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Subscription Qty"))}}
                            </div>
                        </div>
                        <div id="billing_cycle_monthly_anniversary" class="billing_options" style="display: none">
                            <label class="col-md-2 control-label">Billing Cycle - Monthly Anniversary Date*</label>
                            <div class="col-md-4">
                                {{Form::text('BillingCycleValue', '' ,array("class"=>"form-control datepicker","Placeholder"=>"Anniversary Date" , "data-start-date"=>"" ,"data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Next Invoice Date</label>
                        <div class="col-md-4">
                            {{Form::text('NextInvoiceDate', '',array('class'=>'form-control datepicker next_invoice_date',"data-date-format"=>"yyyy-mm-dd"))}}
                        </div>
                        <label class="col-md-2 control-label">Next Charge Date
                            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This is period End Date. e.g. if Billing Cycle is monthly then Next Charge date will be last day of the month  i-e 30/04/2018" data-original-title="Next Charge Date">?</span>
                        </label>
                        <div class="col-md-4">
                            {{Form::text('NextChargeDate', '',array('class'=>'form-control datepicker next_charged_date',"data-date-format"=>"yyyy-mm-dd",'disabled'))}}
                        </div>
                    </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Auto Pay</label>
                    <div class="col-md-4">
                        {{Form::select('AutoPaymentSetting', BillingClass::$AutoPaymentSetting, "never" ,array("class"=>"form-control select2 small"))}}
                    </div>
                    <label class="col-md-2 control-label">Auto Pay Method</label>
                    <div class="col-md-4">
                        {{Form::select('AutoPayMethod', BillingClass::$AutoPayMethod, ( isset($AccountBilling->AutoPayMethod)?$AccountBilling->AutoPayMethod:'0' ),array("class"=>"form-control select2 small"))}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Send Invoice via Email</label>
                    <div class="col-md-4">
                        {{Form::select('SendInvoiceSetting', BillingClass::$SendInvoiceSetting, "after_admin_review" ,array("class"=>"form-control select2"))}}
                    </div>
                </div>

                </div>
                </div>
        </form>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $("#save_account").click(function (ev) {
            $('#save_account').button('loading');
            $("#account-from").submit();


        });
        $('select[name="BillingCycleType"]').on( "change",function(e){
            var selection = $(this).val();
            $(".billing_options input, .billing_options select").attr("disabled", "disabled");// This is to avoid not posting same name hidden elements
            $(".billing_options").hide();
            $(".billing_start_date").removeAttr('readonly');
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
                case "subscription":
                        $("#billing_cycle_subscription").show();
                        $("#billing_cycle_subscription input").removeAttr("disabled");
                        break;
                case "manual":
                    $(".billing_start_date").attr('readonly','true');
                    break;
            }
            if(selection=='weekly' || selection=='monthly_anniversary' || selection=='in_specific_days' || selection=='subscription' || selection=='manual'){
                changeBillingDates('');
            }else{
                changeBillingDates('');
            }
        });


        $('select[name="BillingCycleType"]').trigger( "change" );


        $('[name="Billing"]').on( "change",function(e){
            if($('[name="Billing"]').prop("checked") == true){
                $(".billing-section").show();
                $('.billing-section .select2-container').css('visibility','visible');
            }else{
                $(".billing-section").hide();
            }
        });
        $('[name="BillingClassID"]').on( "change",function(e){
            if($(this).val()>0) {
                $.ajax({
                    url: baseurl+'/billing_class/getInfo/' + $(this).val(),
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $(this).button('reset');
                        if (response.status == 'success') {
                            $("[name='BillingTimezone']").select2().select2('val',response.data.BillingTimezone);
                            $("[name='SendInvoiceSetting']").select2().select2('val',response.data.SendInvoiceSetting);
                            if(response.data.AutoPaymentSetting == null || response.data.AutoPaymentSetting == '') {
                                $("[name='AutoPaymentSetting']").select2().select2('val', 'never');
                            }
                            else{
                                $("[name='AutoPaymentSetting']").select2().select2('val', response.data.AutoPaymentSetting);
                            }
                            $("[name='AutoPayMethod']").select2().select2('val', response.data.AutoPayMethod);
                        } else {
                            $("[name='BillingTimezone']").select2().select2('val','');
                            $("[name='SendInvoiceSetting']").select2().select2('val','');
                        }
                    }
                });
            }

        });
        $('[name="Billing"]').trigger('change');

        $('[name="IsReseller"]').on("change",function(e){
            if($('[name="IsReseller"]').prop("checked") == true){
                $('[name="IsCustomer"]').prop("checked", false).trigger('change');
                $('[name="IsVendor"]').prop("checked", false).trigger('change');
                $("#desablecustomer").addClass('deactivate');
                $("#desablevendor").addClass('deactivate');
                $('#disableresellerowner select').attr("disabled", "disabled");
            }else{
                $("#desablecustomer").removeClass('deactivate');
                $("#desablevendor").removeClass('deactivate');
                $("#desablereseller").removeClass('deactivate');
                $('#disableresellerowner select').removeAttr("disabled");
            }
        });

        $('[name="ResellerOwner"]').on( "change",function(e){
            if($(this).val()>0) {
                $("#desablereseller").addClass('deactivate');
            }else{
                $("#desablereseller").removeClass('deactivate');
            }

        });

        $('[name="BillingStartDate"]').on("change",function(e){
            changeBillingDates('');
        });

        $('[name="BillingCycleValue"]').on( "change",function(e){
            changeBillingDates($(this).val());
        });

        function changeBillingDates(BillingCycleValue){
            var BillingStartDate;
            var BillingCycleType;
            //var BillingCycleValue;
            BillingStartDate = $('[name="BillingStartDate"]').val();
            BillingCycleType = $('select[name="BillingCycleType"]').val();
            if(BillingCycleValue==''){
                BillingCycleValue = $('[name="BillingCycleValue"]').val();
            }
            //alert(BillingCycleValue);
            if(BillingStartDate=='' || BillingCycleType==''){
                return true;
            }

            getNextBillingDatec_url =  '{{ URL::to('accounts/getNextBillingDate')}}';
            $.ajax({
                url: getNextBillingDatec_url,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $('[name="NextInvoiceDate"]').val(response.NextBillingDate);
                    $('[name="NextChargeDate"]').val(response.NextChargedDate);
                },
                data: {
                    "BillingStartDate":BillingStartDate,
                    "BillingCycleType":BillingCycleType,
                    "BillingCycleValue":BillingCycleValue
                }

            });

            return true;
        }

    });
function ajax_form_success(response){
    if(typeof response.redirect != 'undefined' && response.redirect != ''){
        window.location = response.redirect;
    }
 }
</script>
@include('currencies.currencymodal')
@include('billingclass.billingclassmodal')
@include('includes.ajax_submit_script', array('formID'=>'account-from' , 'url' => 'accounts/store','update_url'=>'accounts/update/{id}' ))
@stop
@section('footer_ext')
@parent

@stop
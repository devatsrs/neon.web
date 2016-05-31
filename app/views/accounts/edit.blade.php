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
        <strong>Edit Account</strong>
    </li>
</ol>
<h3>Edit Account</h3>
@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">

    <a href="javascript:void(0)" class="btn btn-primary btn-sm btn-icon icon-left opportunity">
        <i class="entypo-plus"></i>
        Add Opportunity
    </a>

@if($account->VerificationStatus == Account::NOT_VERIFIED)
     <a data-id="{{$account->AccountID}}"  class="btn btn-success btn-sm btn-icon icon-left change_verification_status">
        <i class="entypo-check"></i>
        Verify
    </a>
    @endif

    <a href="{{URL::to('accounts/authenticate/'.$account->AccountID)}}" class="btn btn-primary btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Authentication Rule
    </a>
    <button type="button" id="save_account" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('/accounts')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</p>
<?php $Account = $account;?>
@include('accounts.errormessage')
<br>
<div class="row">
<div class="col-md-12">
    <form role="form" id="account-from" method="post" action="{{URL::to('accounts/update/'.$account->AccountID)}}" autocomplete="off" class="form-horizontal form-groups-bordered">
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
                    <label for="field-1" class="col-sm-2 control-label">Account Owner</label>
                    <div class="col-sm-4">
                    <?php
                    $disable = '';
                    if(User::is('RateManager') && !User::is_admin() && !User::is('AccountManager')){
                    $disable = 'disabled';

                    }?>
                       {{Form::select('Owner',$account_owners,$account->Owner,array("class"=>"select2",$disable))}}
                        @if(User::is('RateManager') && !User::is_admin() && !User::is('AccountManager'))
                            <input type="hidden" value="{{$account->Owner}}" name="Owner">
                        @endif
                    </div>

                    <label class="col-sm-2 control-label">Ownership</label>
                    <div class="col-sm-4">
                        <?php $ownership_array = array( ""=>"None", "Private"=>"Private" , "Public"=>"Public" ,"Subsidiary"=>"Subsidiary","Other"=>"Other" ); ?>
                        {{Form::select('Ownership', $ownership_array, $account->Ownership ,array("class"=>"form-control select2"))}}
                    </div>

                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">First Name</label>
                    <div class="col-sm-4">
                        <input type="text" name="FirstName" class="form-control" id="field-1" placeholder="" value="{{$account->FirstName}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Last Name</label>
                    <div class="col-sm-4">
                        <input type="text" name="LastName" class="form-control" id="field-1" placeholder="" value="{{$account->LastName}}" />
                    </div>

                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Account Number</label>
                    <div class="col-sm-4">
                        <input type="text" name="Number" class="form-control" id="field-1" placeholder="AUTO" value="{{$account->Number}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Website</label>
                    <div class="col-sm-4">
                        <input type="text" name="Website" class="form-control" id="field-1" placeholder="" value="{{$account->Website}}" />
                    </div>

                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">*Account Name</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="AccountName" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder=""  value="{{$account->AccountName}}"/>
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Phone</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="Phone" id="field-1" placeholder="" value="{{$account->Phone}}" />
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Vendor</label>
                    <div class="col-sm-4">
                        <div class="make-switch switch-small">
                            <input type="checkbox" name="IsVendor"  @if($account->IsVendor == 1 )checked=""@endif value="1">
                        </div>
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Fax</label>
                    <div class="col-sm-4">
                        <input type="text" name="Fax" class="form-control" id="field-1" placeholder="" value="{{$account->Fax}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Customer</label>
                    <div class="col-sm-4">
                        <div class="make-switch switch-small">
                            <input type="checkbox" @if($account->IsCustomer == 1 )checked="" @endif name="IsCustomer" value="1">
                        </div>
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Employee</label>
                    <div class="col-sm-4">
                        <input type="text" name="Employee" class="form-control" id="field-1" placeholder="" value="{{$account->Employee}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Email</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="Email" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder="" value="{{$account->Email}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Secondary Email</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="SecondaryEmail" id="field-1" placeholder="" value="{{$account->SecondaryEmail}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Billing Email</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="BillingEmail" id="field-1" placeholder="" value="{{$account->BillingEmail}}" />
                    </div>

                    <!--<label for="field-1" class="col-sm-2 control-label">Rate Email</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="RateEmail" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder="" value="{$account->RateEmail}" />
                    </div>-->

                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Active</label>
                    <div class="col-sm-4">
                        <div class="make-switch switch-small">
                            <input type="checkbox" name="Status"  @if($account->Status == 1 )checked=""@endif value="1">
                        </div>
                    </div>

                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Account Tags</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="tags" name="tags" value="{{$account->tags}}" />
                    </div>
                    
                     <label for="field-1" class="col-sm-2 control-label">VAT Number</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="VatNumber" id="field-1" placeholder="" value="{{$account->VatNumber}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Currency</label>
                    <div class="col-sm-4">
                            @if($invoice_count == 0)
                            {{Form::select('CurrencyId', $currencies, $account->CurrencyId ,array("class"=>"form-control selectboxit"))}}
                            @else
                            {{Form::select('CurrencyId', $currencies, $account->CurrencyId ,array("class"=>"form-control selectboxit",'disabled'))}}
                            {{Form::hidden('CurrencyId', ($account->CurrencyId))}}
                            @endif
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Timezone</label>
                    <div class="col-sm-4">
                        {{Form::select('Timezone', $timezones, $account->TimeZone ,array("class"=>"form-control select2"))}}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Verification Status</label>
                    <div class="col-sm-4">
                        {{Account::$doc_status[$account->VerificationStatus]}}
                    </div>
 <label for="NominalAnalysisNominalAccountNumber" class="col-sm-2 control-label">Nominal Code</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" autocomplete="off"  name="NominalAnalysisNominalAccountNumber" id="NominalAnalysisNominalAccountNumber" placeholder="" value="{{$account->NominalAnalysisNominalAccountNumber}}" />
                    </div>

                </div>
                <script>
                    $(document).ready(function() {
                        $(".btn-toolbar .btn").first().button("toggle");
                    });
                </script>
                <div class="form-group">
                                  

                    <label for="field-1" class="col-sm-2 control-label">CLI</label>
                    <?php  $CLIList = array_filter(explode(',',$account->CustomerCLI));?>
                    <div class="desc col-sm-4 table_{{count($CLIList)}}" >

                        <table class="table table-bordered datatable dataTable acountclitable ">
                        <thead>
                        <tr>

                        <th>CLI</th><th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($CLIList))
                        @foreach($CLIList as $row2)
                            <tr>
                                <td>
                                    {{$row2}}
                                </td>
                                <td>
                                    <a class="btn  btn-danger btn-sm btn-icon icon-left delete-cli"  href="javascript:;" ><i class="entypo-cancel"></i>Delete</a>
                                </td>
                            </tr>
                        @endforeach
                        @endif
                        </tbody>
                        </table>


                    <a class="btn btn-primary  btn-sm btn-icon icon-left add-cli"  href="javascript:;" ><i class="entypo-plus"></i>Add</a>
                    </div>
                    <input type="hidden" class="form-control"  name="CustomerCLI" id="field-1" placeholder="" value="{{$account->CustomerCLI}}" />
                </div>
                
                <div class="panel-title desc clear">
                    Description
                </div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <textarea class="form-control" name="Description" id="events_log" rows="5" placeholder="Description">{{$account->Description}}</textarea>
                    </div>
                </div>
                
                        <div class="form-group">            
                    <label for="CustomerPassword" class="col-sm-2 control-label">Customer Panel Password</label>
                    <div class="col-sm-4">
        <input type="password" class="form-control"    id="CustomerPassword_hide" autocomplete="off" placeholder="******" value="" />
                            <input type="password" class="form-control"   name="password" id="CustomerPassword" autocomplete="off" placeholder="******" value="" />
                    </div>  
                    </div>
                
            </div>
        </div>
        @if( ($account->IsVendor == 1 || $account->IsCustomer == 1) && count($AccountApproval) > 0)
            <div class="panel panel-primary" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Account Verification Document
                            </div>
                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                        @foreach($AccountApproval as $row)
                            <div class="form-group ">
                                <div class="panel-title desc col-sm-3 ">
                                    @if($row->Required == 1)
                                    *
                                    @endif
                                    {{$row->Key}}
                                </div>
                                <div class="panel-title desc col-sm-4 table_{{$row->AccountApprovalID}}" >
                                <?php
                                 $AccountApprovalList = AccountApprovalList::select('AccountApprovalID','AccountApprovalListID','FileName')->where(["AccountID"=> $account->AccountID,'AccountApprovalID'=>$row->AccountApprovalID])->get();
                                 ?>
                                    @if(count($AccountApprovalList))
                                        <table class="table table-bordered datatable dataTable ">
                                        <thead>
                                        <tr>

                                        <th>File Name</th><th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody class="doc_{{$row->AccountApprovalID}}">
                                        @foreach($AccountApprovalList as $row2)
                                            <tr>
                                                <td>
                                                    {{basename($row2->FileName)}}
                                                </td>

                                                <td>
                                                    <a class="btn btn-success btn-sm btn-icon icon-left"  href="{{URL::to('accounts/download_doc/'.$row2->AccountApprovalListID)}}" title="" ><i class="entypo-down"></i>Download</a>
                                                    <a class="btn  btn-danger btn-sm btn-icon icon-left delete-doc"  href="{{URL::to('accounts/delete_doc/'.$row2->AccountApprovalListID)}}" ><i class="entypo-cancel"></i>Delete</a>

                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        </table>

                                    @endif


                                </div>
                                <div class="col-sm-5">
                                    <ul class="icheck-list">
                                        <li>
                                        <a class="btn btn-primary upload-doc" data-title="{{$row->Key}}" data-id="{{$row->AccountApprovalID}}"  href="javascript:;">
                                                <i class="entypo-upload"></i>
                                                Upload Document
                                        </a>
                                        @if($row->DocumentFile !='')
                                            <a class="btn btn-success btn-sm btn-icon icon-left"  href="{{URL::to('accounts/download_doc_file/'.$row->AccountApprovalID)}}" title="" ><i class="entypo-down"></i>Download Attached File</a>
                                        @endif
                                        </li>
                                        <li>
                                            {{$row->Infomsg}}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
        @endif
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
                        <input type="text" name="Address1" class="form-control" id="field-1" placeholder="" value="{{$account->Address1}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">City</label>
                    <div class="col-sm-4">
                        <input type="text" name="City" class="form-control" id="field-1" placeholder="" value="{{$account->City}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Address Line 2</label>
                    <div class="col-sm-4">
                        <input type="text" name="Address2" class="form-control" id="field-1" placeholder="" value="{{$account->Address2}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Post/Zip Code</label>
                    <div class="col-sm-4">
                        <input type="text" name="PostCode" class="form-control" id="field-1" placeholder="" value="{{$account->PostCode}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Address Line 3</label>
                    <div class="col-sm-4">
                        <input type="text" name="Address3" class="form-control" id="field-1" placeholder="" value="{{$account->Address3}}" />
                    </div>

                    <label for=" field-1" class="col-sm-2 control-label">*Country</label>
                    <div class="col-sm-4">

                    {{Form::select('Country', $countries, $account->Country ,array("class"=>"form-control select2"))}}
                    </div>
                </div>
            </div>
        </div>
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Billing
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Tax Rate</label>
                    <div class="col-sm-4">
                        {{Form::select('TaxRateId[]', $taxrates, (isset($account->TaxRateId)? explode(',',$account->TaxRateId) : explode(',',$DefaultTextRate) ) ,array("class"=>"form-control select2",'multiple'))}}
                    </div>
                    <label for="field-1" class="col-sm-2 control-label">Billing Type*</label>
                    <div class="col-sm-4">
                        {{Form::select('BillingType', AccountApproval::$billing_type, $account->BillingType,array('id'=>'billing_type',"class"=>"selectboxit"))}}
                    </div>
                </div>

                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Billing Timezone*</label>
                    <div class="col-sm-4">
                        {{Form::select('BillingTimezone', $timezones, ($account->BillingTimezone != ''?$account->BillingTimezone:CompanySetting::getKeyVal('BillingTimezone') ),array("class"=>"form-control select2"))}}
                    </div>
                    <label for="field-1" class="col-sm-2 control-label">Send Invoice via Email</label>
                    <div class="col-sm-4">
                        <?php $SendInvoiceSetting = array(""=>"Please Select an Option", "automatically"=>"Automatically", "after_admin_review"=>"After Admin Review" , "never"=>"Never");?>
                        {{Form::select('SendInvoiceSetting', $SendInvoiceSetting, ($account->SendInvoiceSetting != ''?$account->SendInvoiceSetting:'never' ),array("class"=>"form-control select2"))}}
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Payment is expected within (Days)</label>
                    <div class="col-sm-4">
                        <div class="input-spinner">
                            <button type="button" class="btn btn-default">-</button>
                            {{Form::text('PaymentDueInDays',($account->PaymentDueInDays != ''?$account->PaymentDueInDays:CompanySetting::getKeyVal('PaymentDueInDays') )  ,array("class"=>"form-control","data-min"=>0, "maxlength"=>"2", "data-max"=>30,"Placeholder"=>"Add Numeric value", "data-mask"=>"decimal"))}}
                            <button type="button" class="btn btn-default">+</button>
                        </div>

                    </div>
                    <label for="field-1" class="col-sm-2 control-label">Round Charged Amount (123.45) </label>
                    <div class="col-sm-4">
                        <div class="input-spinner">
                            <button type="button" class="btn btn-default">-</button>
                            {{Form::text('RoundChargesAmount', ($account->RoundChargesAmount != ''?$account->RoundChargesAmount:CompanySetting::getKeyVal('RoundChargesAmount') ),array("class"=>"form-control", "maxlength"=>"1", "data-min"=>0,"data-max"=>4,"Placeholder"=>"Add Numeric value" , "data-mask"=>"decimal"))}}
                            <button type="button" class="btn btn-default">+</button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Billing cycle</label>
                    <div class="col-sm-4">
                        {{Form::select('BillingCycleType', SortBillingType(), ($account->BillingCycleType != ''?$account->BillingCycleType:CompanySetting::getKeyVal('BillingCycleType') ),array("class"=>"form-control select2"))}}
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
                            {{Form::select('BillingCycleValue',$Days, ($account->BillingCycleType=='weekly'?$account->BillingCycleValue:'') ,array("class"=>"form-control select2"))}}
                        </div>
                    </div>
                    <div id="billing_cycle_in_specific_days" class="billing_options" style="display: none">
                    <label for="field-1" class="col-sm-2 control-label">Billing cycle - for Days</label>
                        <div class="col-sm-4">
                            {{Form::text('BillingCycleValue', ($account->BillingCycleType=='in_specific_days'?$account->BillingCycleValue:'') ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Billing Days"))}}
                        </div>
                    </div>
                    <div id="billing_cycle_subscription" class="billing_options" style="display: none">
                    <label for="field-1" class="col-sm-2 control-label">Billing cycle - Subscription Qty</label>
                        <div class="col-sm-4">
                            {{Form::text('BillingCycleValue', ($account->BillingCycleType=='subscription'?$account->BillingCycleValue:'') ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Subscription Qty"))}}
                        </div>
                    </div>
                    <div id="billing_cycle_monthly_anniversary" class="billing_options" style="display: none">
                        <label for="field-1" class="col-sm-2 control-label">Billing cycle - Monthly Anniversary Date</label>
                        <div class="col-sm-4">
                            {{Form::text('BillingCycleValue', ($account->BillingCycleType=='monthly_anniversary'?$account->BillingCycleValue:'') ,array("class"=>"form-control datepicker","Placeholder"=>"Anniversary Date" , "data-start-date"=>"" ,"data-date-format"=>"dd-mm-yyyy", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Last Invoice Date</label>
                        <div class="col-sm-4">
                                <?php
                                $LastInvoiceDate = $account->LastInvoiceDate;
                                if(empty($LastInvoiceDate)){
                                    $LastInvoiceDate =Invoice::getLastInvoiceDate($account->AccountID);
                                }
                                ?>
                                {{Form::hidden('LastInvoiceDate', $LastInvoiceDate)}}
                                {{$LastInvoiceDate}}
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Next Invoice Date</label>
                        <div class="col-sm-4">
                                    <?php $NextInvoiceDate =Invoice::getNextInvoiceDate($account->AccountID); ?>
                                    {{Form::hidden('NextInvoiceDate', $NextInvoiceDate)}}
                                    {{$NextInvoiceDate}}
                        </div>
                </div>
                <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Invoice Format</label>
                        <div class="col-sm-4">
                            {{Form::select('CDRType', Account::$cdr_type, ($account->CDRType != ''?$account->CDRType:CompanySetting::getKeyVal('CDRType') ),array("class"=>"selectboxit"))}}
                        </div>
                        <label for="field-1" class="col-sm-2 control-label">Invoice Template*</label>

                        <div class="col-sm-4">
                            {{Form::select('InvoiceTemplateID', $InvoiceTemplates, ($account->InvoiceTemplateID != ''?$account->InvoiceTemplateID:CompanySetting::getKeyVal('InvoiceTemplateID') ),array("class"=>"form-control select2"))}}
                        </div>
                </div>
                <?php
                    $BillingStartDate = $account->BillingStartDate;
                    if($account->BillingStartDate == ''){
                        $BillingStartDate = date('Y-m-d',strtotime($account->created_at));
                    }
                ?>
                 <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Billing Start Date</label>
                        <div class="col-sm-4">
                                @if($invoice_count == 0)
                                    {{Form::text('BillingStartDate', date('Y-m-d',strtotime($BillingStartDate)),array('class'=>'form-control datepicker',"data-date-format"=>"yyyy-mm-dd"))}}
                                @else
                                    {{Form::hidden('BillingStartDate', date('Y-m-d',strtotime($BillingStartDate)))}}
                                    {{$BillingStartDate}}
                                @endif
                        </div>
                </div>
            </div>
        </div>
        @include('accountsubscription.index')
        @include('accountoneoffcharge.index')
        <div class="panel panel-primary" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    Payment Information
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">
                <div class="form-group">
                    <div class="panel-title desc col-sm-6">
                        Preferred Payment Method
                    </div>
                    <script>
                        var ajax_url = baseurl + "/accounts/{{$account->AccountID}}/ajax_datagrid_PaymentProfiles";
                    </script>
                    <div class="col-sm-9" style="float: right;">
                        @if (is_authorize())
                            @include('customer.paymentprofile.paymentGrid')
                        @endif
                    </div>
                    <div class="col-sm-3">
                        <ul class="icheck-list">
                            <li>
                                <input class="icheck-11" type="radio" id="minimal-radio-1-11" name="PaymentMethod" value="Paypal" @if( $account->PaymentMethod == 'Paypal' ) checked="" @endif />
                                <label for="minimal-radio-1-11">Paypal</label>
                            </li>
                            <li>
                                <input tabindex="8" class="icheck-11" type="radio" id="minimal-radio-2-11" name="PaymentMethod" value="Wire Transfer" @if( $account->PaymentMethod == 'Wire Transfer' ) checked="" @endif />
                                <label for="minimal-radio-2-11">Wire Transfer</label>
                            </li>
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-disabled-2-11" name="PaymentMethod" value="AuthorizeNet" @if( $account->PaymentMethod == 'AuthorizeNet' ) checked="" @endif />
                                <label for="minimal-radio-2-11">AuthorizeNet</label>
                            </li>
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-disabled-2-11" name="PaymentMethod" value="Other" @if( $account->PaymentMethod == 'Other' ) checked="" @endif />
                                <label for="minimal-radio-2-11">Other</label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</div>
<script type="text/javascript">
    var accountID = '{{$account->AccountID}}';
    var readonly = ['Company','Phone','Email','ContactName'];
    jQuery(document).ready(function ($) {
		//account status start
		$(".change_verification_status").click(function(e) {
		if (!confirm('Are you sure you want to change verification status?')) {
			return false;
		}
		

		var id = $(this).attr("data-id");
		varification_url =  '{{ URL::to('accounts/{id}/change_verifiaction_status')}}/'+{{Account::VERIFIED}}
		varification_url = varification_url.replace('{id}',id);

		$.ajax({
			url: varification_url,
			type: 'POST',
			dataType: 'json',
			success: function(response) {
				$(this).button('reset');
				if (response.status == 'success') {
					$('.toast-error').remove();
					$('.change_verification_status').remove();
					toastr.success(response.message, "Success", toastr_opts);					
				} else {
					toastr.error(response.message, "Error", toastr_opts);
				}
			},

			// Form data
			//data: {},
			cache: false,
			contentType: false,
			processData: false
		});
		return false;
	});
		//account status end
		
		
		
        $('#add-credit-card-form').find("[name=AccountID]").val('{{$account->AccountID}}');
        $("#save_account").click(function (ev) {
            ev.preventDefault();

            //Subscription , Additional charge filter fields should not in account save.
            $('#subscription_filter').find('input').attr("disabled", "disabled");
            $('#oneofcharge_filter').find('input').attr("disabled", "disabled");
            $('#oneofcharge_filter').find('select').attr("disabled", "disabled");

            url= baseurl + '/accounts/update/{{$account->AccountID}}';
            var data =$('#account-from').serialize();
            ajax_json(url,data,function(response){

              //Subscription , Additional charge filter fields to enable again.
              $('#subscription_filter').find('input').removeAttr("disabled");
              $('#oneofcharge_filter').find('input').removeAttr("disabled");
              $('#oneofcharge_filter').find('select').removeAttr("disabled");

              if(response.status =='success'){
                     toastr.success(response.message, "Success", toastr_opts);
              }else{
                       toastr.error(response.message, "Error", toastr_opts);
              }
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
                case "subscription":
                        $("#billing_cycle_subscription").show();
                        $("#billing_cycle_subscription input").removeAttr("disabled");
                        break;
            }
        });
        $('select[name="BillingCycleType"]').trigger( "change" );


        $('.upload-doc').click(function(ev){
                    ev.preventDefault();

                    $("#form-upload [name='AccountApprovalID']").val($(this).attr('data-id'));
                    $('#upload-modal-account h4').html('Upload '+$(this).attr('data-title')+' Document');
                    $('#upload-modal-account').modal('show');
                });
                $('#form-upload').submit(function(ev){
                ev.preventDefault();
                 var formData = new FormData($('#form-upload')[0]);
                    $.ajax({
                        url: baseurl + '/accounts/upload/{{$account->AccountID}}',  //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function(){
                            $('.btn.upload').button('loading');
                        },
                        afterSend: function(){
                            console.log("Afer Send");
                        },
                        success: function (response) {
                            if(response.status =='success'){
                                toastr.success(response.message, "Success", toastr_opts);
                                $('#upload-modal-account').modal('hide');
                                var url3 = baseurl+'/accounts/download_doc/'+response.LastID;
                                var delete_doc_url = baseurl+'/accounts/delete_doc/'+response.LastID;
                                var filename = response.Filename;

                                if($('.table_'+$("#form-upload [name='AccountApprovalID']").val()).html().trim() === ''){
                                    $('.table_'+$("#form-upload [name='AccountApprovalID']").val()).html('<table class="table table-bordered datatable dataTable "><thead><tr><th>File Name</th><th>Action</th></tr></thead><tbody class="doc_'+$("#form-upload [name='AccountApprovalID']").val()+'"></tbody></table>');
                                }
                                var down_html = $('.doc_'+$("#form-upload [name='AccountApprovalID']").val()).html()+'<tr><td>'+filename+'</td><td><a class="btn btn-success btn-sm btn-icon icon-left"  href="'+url3+'" title="" ><i class="entypo-down"></i>Download</a> <a class="btn  btn-danger delete-doc btn-sm btn-icon icon-left"  href="'+delete_doc_url+'" title="" ><i class="entypo-cancel"></i>Delete</a></td></tr>';
                                $('.doc_'+$("#form-upload [name='AccountApprovalID']").val()).html(down_html);
                                if(response.refresh){
                                    setTimeout(function(){window.location.reload()},1000);
                                }

                            }else{
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $('.btn.upload').button('reset');
                        },
                        // Form data
                        data: formData,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                });
                @if($account->Status != Account::VERIFIED)
                $(document).ajaxSuccess(function( event, jqXHR, ajaxSettings, ResponseData ) {
                    //Reload only when success message.
                    if (ResponseData.status != undefined &&  ResponseData.status == 'success' && ResponseData.refresh) {
                        setTimeout(function(){window.location.reload()},1000);
                    }
                });
                @endif

            $('body').on('click', '.delete-doc', function(e) {
                e.preventDefault();
                result = confirm("Are you Sure?");
                if(result){
                    submit_ajax($(this).attr('href'),'AccountID=AccountID')
                    $(this).parent().parent('tr').remove();
                }
            });

            $('body').on('click', '.delete-cli', function(e) {
                e.preventDefault();
                result = confirm("Are you Sure?");
                if(result){
                    $(this).parent().parent('tr').remove();
                    var nameIDs = $('table.acountclitable tr td:first-child').map(function () {
                                                                          return this.innerHTML.trim();
                                                                      }).get().join(',');
                    $("#account-from [name='CustomerCLI']").val(nameIDs);
                }
            });

            $('body').on('click', '.add-cli', function(e) {
                $("#form-addcli-modal")[0].reset();
                $("#addcli-modal").modal('show');
            });

            $("#form-addcli-modal").submit(function(e){
                e.preventDefault();
                var cli=$(this).find("[name='CustomerCLI']").val();
                var val_cli=0
                $('table.acountclitable tr td:first-child').each(function(){

                    if(this.innerHTML.trim()==cli){
                      toastr.error("Already Cli exits.", "Error", toastr_opts);
                        val_cli=1;
                    }
                });
                if(val_cli==0){
                $.ajax({
                    url: baseurl + '/accounts/validate_cli',
                    type:'POST',
                    data:{cli:cli},
                    dataType: 'json',
                    success: function(response) {

                    if (response.status == 'success') {
                            var accoutiphtml = '<tr><td>'+cli+'</td><td><a class="btn  btn-danger btn-sm btn-icon icon-left delete-cli"  href="javascript:;" ><i class="entypo-cancel"></i>Delete</a></td></tr>';
                                            $('.acountclitable').children('tbody').append(accoutiphtml);

                                            var nameIDs = $('table.acountclitable tr td:first-child').map(function () {
                                                              return this.innerHTML.trim();
                                                          }).get().join(',');
                                            $("#account-from [name='CustomerCLI']").val(nameIDs);
                                            $('.acountclitable').children('tbody').children('tr').children('td');

                                            $("#addcli-modal").modal('hide');
                        }else{
                             toastr.error(response.message, "Error", toastr_opts);
                        }

                     }

                });
                }

            });
            setTimeout(function(){
                $('select[name="CDRType"]').trigger( "change" );
            },500)

            @if ($account->VerificationStatus == Account::NOT_VERIFIED)
                $(".btn-toolbar .btn").first().button("toggle");
            @elseif ($account->VerificationStatus == Account::VERIFIED)
                $(".btn-toolbar .btn").last().button("toggle");
            @endif

            $("#tags").select2({
                tags:{{$tags}}
             });

        $('#createopportunity').on('click',function(){
            $('#add-edit-opportunity-form').trigger('reset');
            $('#add-edit-opportunity-form [name="AccountID"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption(accountID);
            if(accountID) {
                var url = baseurl + '/opportunity/' + accountID + '/getlead';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        setunsetreadonly(response[0],true);
                        $('#add-edit-modal-opportunity').modal('show');
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }
        });

        $('#add-edit-modal-opportunity .reset').click(function(){
            var colorPicker = $(this).parents('.form-group').find('[type="text"].colorpicker');
            var color = $(this).attr('data-color');
            setcolor(colorPicker,color);
        });

        $(document).on('mouseover','#rating i',function(){
            var currentrateid = $(this).attr('rate-id');
            setrating(currentrateid);
        });
        $(document).on('click','#rating i',function(){
            var currentrateid = $(this).attr('rate-id');
            $('#rating input[name="Rating"]').val(currentrateid);
            setrating(currentrateid);
        });
        $(document).on('mouseleave','#rating',function(){
            var defultrateid = $('#rating input[name="Rating"]').val();
            setrating(defultrateid);
        });

        $('#add-edit-opportunity-form').submit(function(e){
            e.preventDefault();
            var url = baseurl + '/accounts/'+accountID+'/createOpportunity';
            var formData = new FormData($('#add-edit-opportunity-form')[0]);
            $.ajax({
                url: url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#add-edit-modal-opportunity').modal('hide');
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $("#opportunity-update").button('reset');
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });

        $('.opportunityTags').select2({
            tags:{{$opportunityTags}}
        });

        function setunsetreadonly(data,status){
            for(var i = 0 ; i< readonly.length; i++){
                $('#add-edit-opportunity-form [name="'+readonly[i]+'"]').val('');
                $('#add-edit-opportunity-form [name="'+readonly[i]+'"]').prop('readonly', status);
                if(data){
                    $('#add-edit-opportunity-form [name="'+readonly[i]+'"]').val(data[readonly[i]]);
                }
            }
        }

        function setcolor(elem,color){
            elem.colorpicker('destroy');
            elem.val(color);
            elem.colorpicker({color:color});
            elem.siblings('.input-group-addon').find('.color-preview').css('background-color', color);
        }

        function setrating(currentrateid){
            $('#rating i').css('color','black');
            $('#rating i').each(function(){
                var rateid = $(this).attr('rate-id');
                if(currentrateid<rateid){
                    return false;
                }
                $(this).css('color','#e9dc3c');
            });
        }
    });
</script>

<!--@include('includes.ajax_submit_script', array('formID'=>'account-from' , 'url' => ('accounts/update/'.$account->AccountID)))-->
    @include('opportunityboards.opportunitymodal',array('leadOrAccountID'=>$leadOrAccountID))

@stop
@section('footer_ext')
@parent
<div class="modal fade" id="upload-modal-account" >
    <div class="modal-dialog">
        <div class="modal-content">
        <form role="form" id="form-upload" method="post" action="{{URL::to('accounts/upload/'.$account->AccountID)}}"
              class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Upload Code Decks</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label">File Select</label>
                    <div class="col-sm-5">
                        <input type="file" id="excel" name="excel" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                        <input name="AccountApprovalID" value="" type="hidden" >
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit"  class="btn upload btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                    <i class="entypo-upload"></i>
                     Upload
                </button>
                <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                     <i class="entypo-cancel"></i>
                     Close
                </button>
             </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addcli-modal" >
    <div class="modal-dialog">
        <div class="modal-content">
        <form role="form" id="form-addcli-modal" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add CLI</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label">CLI</label>
                    <div class="col-sm-5">
                        <input name="CustomerCLI" type="text" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit"  class="btn btn-primary btn-sm btn-icon icon-left">
                    <i class="entypo-floppy"></i>
                     Add
                </button>
                <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                     <i class="entypo-cancel"></i>
                     Close
                </button>
             </div>
        </form>
        </div>
    </div>
</div>
<div class="modal fade" id="add-edit-modal-opportunity">
    <div class="modal-dialog" style="width: 70%;">
        <div class="modal-content">
            <form id="add-edit-opportunity-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Opportunity</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-2">Account Owner *</label>
                                <div class="col-sm-4">
                                    {{Form::select('UserID',$account_owners,User::get_userID(),array("class"=>"selectboxit",'disabled'))}}
                                </div>
                                <label for="field-5" class="control-label col-sm-2">Opportunity Name *</label>
                                <div class="col-sm-4">
                                    <input type="text" name="OpportunityName" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="leads">
                                    <label for="field-5" class="control-label col-sm-2">Account</label>
                                    <div class="col-sm-4">
                                        {{Form::select('AccountID',$accounts,'',array("class"=>"selectboxit",'disabled'))}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-2">Company</label>
                                <div class="col-sm-4">
                                    <input type="text" name="Company" class="form-control" id="field-5">
                                </div>
                                <label for="field-5" class="control-label col-sm-2">Contact Name</label>
                                <div class="col-sm-4">
                                    <input type="text" name="ContactName" class="form-control" id="field-5">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-2">Phone Number</label>
                                <div class="col-sm-4">
                                    <input type="text" name="Phone" class="form-control" id="field-5">
                                </div>
                                <label for="field-5" class="control-label col-sm-2">Email Address</label>
                                <div class="col-sm-4">
                                    <input type="text" name="Email" class="form-control" id="field-5">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-2">Select Board</label>
                                <div class="col-sm-4">
                                    {{Form::select('BoardID',$boards,'',array("class"=>"selectboxit"))}}
                                </div>
                                <label for="field-5" class="control-label col-sm-2">Select Background</label>
                                <div class="col-sm-3 input-group">
                                    <input name="BackGroundColour" type="text" class="form-control colorpicker" value="#303641" />
                                    <div class="input-group-addon">
                                        <i class="color-preview"></i>
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <button class="btn btn-xs btn-danger reset" data-color="#303641" type="button">Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label col-sm-2">Text Color</label>
                                <div class="col-sm-3 input-group">
                                    <input name="TextColour" type="text" class="form-control colorpicker" value="#ffffff" />
                                    <div class="input-group-addon">
                                        <i class="color-preview"></i>
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <button class="btn btn-xs btn-danger reset" data-color="#ffffff" type="button">Reset</button>
                                </div>
                                <label for="field-5" class="control-label col-sm-2">Tags</label>
                                <div class="col-sm-4 input-group">
                                    <input class="form-control opportunityTags" name="Tags" type="text" >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="input-1" class="control-label col-sm-2">Rate This</label>
                                <div id="rating" class="col-sm-4">
                                    <i rate-id="1" class="entypo-star"></i>
                                    <i rate-id="2" class="entypo-star"></i>
                                    <i rate-id="3" class="entypo-star"></i>
                                    <i rate-id="4" class="entypo-star"></i>
                                    <i rate-id="5" class="entypo-star"></i>
                                    <input type="hidden" name="Rating" value="1" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="OpportunityID">
                    <button type="submit" id="opportunity-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
setTimeout(function(){
	$('#CustomerPassword_hide').hide();
	},1000);
</script>
@stop
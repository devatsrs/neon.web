@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>

        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li>
        <a><span>{{customer_dropbox($account->AccountID)}}</span></a>
    </li>
    <li class="active">
        <strong>Edit Account</strong>
    </li>
</ol>
<h3>Edit Account</h3>
@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
    @if(User::checkCategoryPermission('CreditControl','View'))
    <a href="{{URL::to('account/get_credit/'.$account->AccountID)}}" class="btn btn-primary btn-sm btn-icon icon-left">
        <i class="fa fa-credit-card"></i>
        Credit Control
    </a>
    @endif
    @if(User::checkCategoryPermission('Opportunity','Add'))
    <a href="javascript:void(0)" class="btn btn-primary btn-sm btn-icon icon-left opportunity">
        <i class="fa fa-line-chart"></i>
        Add Opportunity
    </a>

    @endif
    @if($account->VerificationStatus == Account::NOT_VERIFIED)
     <a data-id="{{$account->AccountID}}"  class="btn btn-success btn-sm btn-icon icon-left change_verification_status">
        <i class="entypo-check"></i>
        Verify
    </a>
    @endif
    @if($account->IsCustomer==1 || $account->IsVendor==1)
    <a href="{{URL::to('accounts/authenticate/'.$account->AccountID)}}" class="btn btn-primary btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Authentication Rule
    </a>
    @endif
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
    <form role="form" id="account-from" method="post" action="{{URL::to('accounts/update/'.$account->AccountID)}}" class="form-horizontal form-groups-bordered">

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
                    <label for="field-1" class="col-sm-2 control-label">Billing Email</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="BillingEmail" id="field-1" placeholder="" value="{{$account->BillingEmail}}" />
                    </div>
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
                            {{Form::select('CurrencyId', $currencies, $account->CurrencyId ,array("class"=>"form-control select2 small"))}}
                            @else
                            {{Form::select('CurrencyId', $currencies, $account->CurrencyId ,array("class"=>"form-control select2 small",'disabled'))}}
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
                                   <label for="field-1" class="col-sm-2 control-label">Nominal Code</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control"  name="NominalAnalysisNominalAccountNumber" id="field-1" placeholder="" value="{{$account->NominalAnalysisNominalAccountNumber}}" />
                    </div>
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
        <input type="password" class="form-control"    id="CustomerPassword_hide" autocomplete="off" placeholder="Enter Password" value="" />
                            <input type="password" class="form-control"   name="password" id="CustomerPassword" autocomplete="off" placeholder="Enter Password" value="" />
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
        <?php
            $billing_disable = $hiden_class= '';
        if($invoice_count > 0){
            $billing_disable = 'disabled';
        }
            if(isset($AccountBilling->BillingCycleType)){
                $hiden_class= 'hidden';
                $billing_disable = 'disabled';
            }
        $Days = array( ""=>"Please Start of Day",
                "monday"=>"Monday",
                "tuesday"=>"Tuesday",
                "wednesday"=>"Wednesday",
                "thursday"=>"Thursday",
                "friday"=>"Friday",
                "saturday"=>"Saturday",
                "sunday"=>"Sunday");
        ?>
        <div class="panel panel-primary billing-section-hide"   data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Billing
                </div>

                <div class="panel-options">
                    <div class="make-switch switch-small">
                        <input type="checkbox" @if($account->Billing == 1 )checked="" @endif name="Billing" value="1">
                    </div>
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body billing-section">
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Billing Class*</label>
                    <div class="col-sm-4">
                        {{Form::select('BillingClassID', $BillingClass, (  isset($AccountBilling->BillingClassID)?$AccountBilling->BillingClassID:'' ) ,array("class"=>"selectboxit form-control1"));}}
                    </div>
                    <label for="field-1" class="col-sm-2 control-label">Billing Type*</label>
                    <div class="col-sm-4">
                        {{Form::select('BillingType', AccountApproval::$billing_type, AccountBilling::getBillingKey($AccountBilling,'BillingType'),array('id'=>'billing_type',"class"=>"select2 small"))}}
                    </div>

                </div>
                <div class="form-group">

                    <label for="field-1" class="col-sm-2 control-label">Billing Timezone*</label>
                    <div class="col-sm-4">
                        {{Form::select('BillingTimezone', $timezones, (isset($AccountBilling->BillingTimezone)?$AccountBilling->BillingTimezone:'' ),array("class"=>"form-control select2",$billing_disable))}}
                        @if($billing_disable)
                            <input type="hidden" value="{{isset($AccountBilling->BillingTimezone)?$AccountBilling->BillingTimezone:''}}" name="BillingTimezone">
                        @endif
                    </div>
                    <?php
                    $BillingStartDate = isset($AccountBilling->BillingStartDate)?$AccountBilling->BillingStartDate:'';
                    if(!empty($BillingStartDate)){
                        $BillingStartDate = date('Y-m-d',strtotime($BillingStartDate));
                    }
                    /*if(empty($BillingStartDate)){
                        $BillingStartDate = date('Y-m-d',strtotime($account->created_at));
                    }*/
                    ?>
                    <label for="field-1" class="col-sm-2 control-label">Billing Start Date*</label>
                    <div class="col-sm-4">
                        @if($hiden_class == '')
                            {{Form::text('BillingStartDate', $BillingStartDate,array('class'=>'form-control datepicker',"data-date-format"=>"yyyy-mm-dd"))}}
                        @else
                            {{Form::hidden('BillingStartDate', $BillingStartDate)}}
                            {{$BillingStartDate}}
                        @endif
                    </div>
                </div>
                @if(!empty($AccountNextBilling))
                    <?php
                    if($AccountBilling->BillingCycleType == 'weekly'){
                        $oldBillingCycleValue = $Days[$AccountBilling->BillingCycleValue];
                    }else{
                        $oldBillingCycleValue = $AccountBilling->BillingCycleValue;
                    }
                    ?>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Current Billing Cycle</label>
                        <div class="col-sm-4">{{SortBillingType()[$AccountBilling->BillingCycleType]}}@if(!empty($oldBillingCycleValue)) {{'('.$oldBillingCycleValue.')'}} @endif</div>
                        <label for="field-1" class="col-sm-2 control-label">New Billing Cycle Effective From</label>
                        <div class="col-sm-4">{{$AccountNextBilling->LastInvoiceDate}}</div>
                    </div>
                @endif
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">@if(!empty($AccountNextBilling)) New @endif Billing Cycle*</label>
                    <div class="col-sm-3">
                        <?php
                        if(!empty($AccountNextBilling)){
                            $BillingCycleType = $AccountNextBilling->BillingCycleType;
                        }elseif(!empty($AccountBilling)){
                            $BillingCycleType = $AccountBilling->BillingCycleType;
                        }else{
                            $BillingCycleType = '';
                        }
                        ?>
                        @if($hiden_class != '' && isset($AccountBilling->BillingCycleType) )
                            <div class="billing_edit_text"> {{SortBillingType()[$BillingCycleType]}} </div>
                        @endif

                        {{Form::select('BillingCycleType', SortBillingType(), $BillingCycleType ,array("class"=>$hiden_class." form-control select2"))}}

                    </div>
                    <div class="col-sm-1">
                        @if($hiden_class != '')
                        <button class="btn btn-sm btn-primary tooltip-primary" id="billing_edit" data-original-title="Edit Billing Cycle" title="" data-placement="top" data-toggle="tooltip">
                            <i class="fa fa-pencil"></i>
                        </button>
                        @endif
                    </div>
                    <?php
                    if(!empty($AccountNextBilling)){
                        $BillingCycleValue = $AccountNextBilling->BillingCycleValue;
                    }elseif(!empty($AccountBilling)){
                        $BillingCycleValue = $AccountBilling->BillingCycleValue;
                    }elseif(empty($AccountBilling)){
                        $BillingCycleValue = '';
                    }
                    ?>
                    <div id="billing_cycle_weekly" class="billing_options" >
                        <label for="field-1" class="col-sm-2 control-label">Billing Cycle - Start of Day*</label>
                        <div class="col-sm-4">
                            @if($hiden_class != '' && $BillingCycleType =='weekly' )
                                <div class="billing_edit_text"> {{$Days[$BillingCycleValue]}} </div>
                            @endif

                            {{Form::select('BillingCycleValue',$Days, ($BillingCycleType =='weekly'?$BillingCycleValue:'') ,array("class"=>"form-control select2"))}}

                        </div>
                    </div>
                    <div id="billing_cycle_in_specific_days" class="billing_options" style="display: none">
                    <label for="field-1" class="col-sm-2 control-label">Billing Cycle - for Days*</label>
                        <div class="col-sm-4">
                            @if($hiden_class != '' && $BillingCycleType =='in_specific_days' )
                                <div class="billing_edit_text"> {{$BillingCycleValue}} </div>
                            @endif
                            {{Form::text('BillingCycleValue', ($BillingCycleType =='in_specific_days'?$BillingCycleValue:'') ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Billing Days"))}}
                        </div>
                    </div>
                    <div id="billing_cycle_subscription" class="billing_options" style="display: none">
                    <label for="field-1" class="col-sm-2 control-label">Billing Cycle - Subscription Qty</label>
                        <div class="col-sm-4">
                            @if($hiden_class != '' && $BillingCycleType =='subscription' )
                                <div class="billing_edit_text"> {{$BillingCycleValue}} </div>
                            @endif
                            {{Form::text('BillingCycleValue', ($BillingCycleType =='subscription'?$BillingCycleValue:'') ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Subscription Qty"))}}
                        </div>
                    </div>
                    <div id="billing_cycle_monthly_anniversary" class="billing_options" style="display: none">
                        <label for="field-1" class="col-sm-2 control-label">Billing Cycle - Monthly Anniversary Date*</label>
                        <div class="col-sm-4">
                            @if($hiden_class != '' && $BillingCycleType =='monthly_anniversary' )
                                <div class="billing_edit_text"> {{$BillingCycleValue}} </div>
                            @endif
                            {{Form::text('BillingCycleValue', ($BillingCycleType =='monthly_anniversary'?$BillingCycleValue:'') ,array("class"=>"form-control datepicker","Placeholder"=>"Anniversary Date" , "data-start-date"=>"" ,"data-date-format"=>"dd-mm-yyyy", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Invoice Template*</label>

                    <div class="col-sm-4">
                        {{Form::select('InvoiceTemplateID', $InvoiceTemplates, ( isset($AccountBilling->InvoiceTemplateID)?$AccountBilling->InvoiceTemplateID:CompanySetting::getKeyVal('InvoiceTemplateID') ),array("class"=>"form-control select2"))}}
                    </div>
                        <label for="field-1" class="col-sm-2 control-label">Invoice Format*</label>
                        <div class="col-sm-4">
                            {{Form::select('CDRType', Account::$cdr_type, ( isset($AccountBilling->CDRType)?$AccountBilling->CDRType:CompanySetting::getKeyVal('CDRType') ),array("class"=>"select2 small"))}}
                        </div>

                </div>

                 <div class="form-group">
                     <label for="field-1" class="col-sm-2 control-label">Send Invoice via Email</label>
                     <div class="col-sm-4">
                         <?php $SendInvoiceSetting = array(""=>"Please Select an Option", "automatically"=>"Automatically", "after_admin_review"=>"After Admin Review" , "never"=>"Never");?>
                         {{Form::select('SendInvoiceSetting', $SendInvoiceSetting, ( isset($AccountBilling->SendInvoiceSetting)?$AccountBilling->SendInvoiceSetting:'never' ),array("class"=>"form-control select2"))}}
                     </div>


                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Last Invoice Date</label>
                    <div class="col-sm-4">
                        <?php
                        $LastInvoiceDate = isset($AccountBilling->LastInvoiceDate)?$AccountBilling->LastInvoiceDate:'';
                        ?>
                        {{Form::hidden('LastInvoiceDate', $LastInvoiceDate)}}
                        {{$LastInvoiceDate}}
                    </div>
                    <label for="field-1" class="col-sm-2 control-label">Next Invoice Date</label>
                    <div class="col-sm-4">
                        <?php
                        $NextInvoiceDate = isset($AccountBilling->NextInvoiceDate)?$AccountBilling->NextInvoiceDate:''; ?>
                        {{Form::hidden('NextInvoiceDate', $NextInvoiceDate)}}
                        {{$NextInvoiceDate}}
                    </div>
                </div>

            </div>
        </div>
        @include('accountdiscountplan.index')
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
                    <script>
                        var ajax_url = baseurl + "/accounts/{{$account->AccountID}}/ajax_datagrid_PaymentProfiles";
                    </script>
                    <div class="col-md-3">

                        <h4>Preferred Payment Method</h4>

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
                    <div class="col-md-9">
                        @if (is_authorize())
                            @include('customer.paymentprofile.paymentGrid')
                        @endif
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
    var BillingChanged;
    var FirstTimeTrigger = true;
    jQuery(document).ready(function ($) {
		//account status start
        $('.acountclitable').DataTable({"aaSorting":[[1, 'asc']],"fnDrawCallback": function() {
            $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });
        }});
		$(".change_verification_status").click(function(e) {
            if (!confirm('Are you sure you want to change verification status?')) {
                return false;
            }


            var id = $(this).attr("data-id");
            varification_url =  "{{ URL::to('accounts/{id}/change_verifiaction_status')}}/{{Account::VERIFIED}}";
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
                  if($('[name="Billing"]').prop("checked") == true && BillingChanged) {
                      setTimeout(function () {
                          window.location.reload()
                      }, 1000);
                  }
              }else{
                       toastr.error(response.message, "Error", toastr_opts);
              }
            });
                
        });

        $('select[name="BillingCycleType"]').on( "change",function(e){
            var selection = $(this).val();
            var hidden = false;
            if($(this).hasClass('hidden')){
                hidden = true;
            }
            $(".billing_options input, .billing_options select").attr("disabled", "disabled");
            $(".billing_options").hide();
            console.log(selection);
            switch (selection){
                case "weekly":
                        $("#billing_cycle_weekly").show();
                        $("#billing_cycle_weekly select").removeAttr("disabled");
                        $("#billing_cycle_weekly select").addClass('billing_options_active');
                        if(hidden){
                            $("#billing_cycle_weekly select").addClass('hidden');
                        }
                        break;
                case "monthly_anniversary":
                        $("#billing_cycle_monthly_anniversary").show();
                        $("#billing_cycle_monthly_anniversary input").removeAttr("disabled");
                        $("#billing_cycle_monthly_anniversary input").addClass('billing_options_active');
                        if(hidden){
                            $("#billing_cycle_monthly_anniversary input").addClass('hidden');
                        }
                        break;
                case "in_specific_days":
                        $("#billing_cycle_in_specific_days").show();
                        $("#billing_cycle_in_specific_days input").removeAttr("disabled");
                        $("#billing_cycle_in_specific_days input").addClass('billing_options_active');
                        if(hidden){
                            $("#billing_cycle_in_specific_days input").addClass('hidden');
                        }
                        break;
                case "subscription":
                        $("#billing_cycle_subscription").show();
                        $("#billing_cycle_subscription input").removeAttr("disabled");
                        $("#billing_cycle_subscription input").addClass('billing_options_active');
                        if(hidden){
                            $("#billing_cycle_subscription input").addClass('hidden');
                        }
                        break;
            }
            if(FirstTimeTrigger == true) {
                BillingChanged = false;
                FirstTimeTrigger= false;
            }else{
                BillingChanged = true;
            }
        });
        $('[name="BillingStartDate"]').on( "change",function(e){
            BillingChanged = true;
        });
        $('[name="BillingCycleValue"]').on( "change",function(e){
            BillingChanged = true;
        });
        $('[name="Billing"]').on( "change",function(e){
            if($('[name="Billing"]').prop("checked") == true){
                $(".billing-section").show();
                $(".billing-section-hide").nextAll('.panel').attr('data-collapsed',0);
                $(".billing-section-hide").nextAll('.panel').find('.panel-body').show();
            }else{
                $(".billing-section").hide();
                $(".billing-section-hide").nextAll('.panel').attr('data-collapsed',1);
                $(".billing-section-hide").nextAll('.panel').find('.panel-body').hide();
            }
        });
        $('[name="Billing"]').trigger('change');

        $('#billing_edit').on( "click",function(e){
            e.preventDefault();
            $('[name="BillingCycleType"]').removeClass('hidden');
            $('body').find(".billing_options_active").removeClass('hidden');
            $('.billing_edit_text').addClass('hidden');
            $(this).addClass('hidden');
            return false;
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

        $('[name="BillingClassID"]').on( "change",function(e){
            if($(this).val()>0) {
                $.ajax({
                    url: baseurl+'/billing_class/getInfo/' + $(this).val(),
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $(this).button('reset');
                        if (response.status == 'success') {
                            if($("select[name='BillingTimezone']").val() == '') {
                                $("select[name='BillingTimezone']").select2().select2('val', response.data.BillingTimezone);
                            }
                            $("[name='SendInvoiceSetting']").select2().select2('val',response.data.SendInvoiceSetting);
                        }
                    },
                });
            }

        });



        @if ($account->VerificationStatus == Account::NOT_VERIFIED)
        $(".btn-toolbar .btn").first().button("toggle");
        @elseif ($account->VerificationStatus == Account::VERIFIED)
        $(".btn-toolbar .btn").last().button("toggle");
        @endif
    });
</script>

<!--@include('includes.ajax_submit_script', array('formID'=>'account-from' , 'url' => ('accounts/update/'.$account->AccountID)))-->
    @include('opportunityboards.opportunitymodal',array('leadOrAccountID'=>$leadOrAccountID))

@stop
@section('footer_ext')
@parent
@include('accountdiscountplan.discountplanmodal')
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
    <div class="modal-dialog" style="width: 30%;">
        <div class="modal-content">
        <form role="form" id="form-addcli-modal" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add CLI</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label">CLI</label>
                    <div class="col-sm-9">
                        <textarea name="CustomerCLI" class="form-control autogrow"></textarea>
                        *Adding multiple CLIs ,Add one CLI in each line.
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
<script>
setTimeout(function(){
	$('#CustomerPassword_hide').hide();
	},1000);
</script>
@stop
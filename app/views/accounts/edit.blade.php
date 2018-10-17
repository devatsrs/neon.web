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
<style>
    .account_number_disable .label_disable{
        display:none;
    }
</style>
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
    @if( User::checkCategoryPermission('AuthenticationRule','View'))
        @if($account->IsCustomer==1 || $account->IsVendor==1)
        <a href="{{URL::to('accounts/authenticate/'.$account->AccountID)}}" class="btn btn-primary btn-sm btn-icon icon-left">
            <i class="entypo-lock"></i>
            Authentication Rule
        </a>
        @endif
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
                    <label class="col-md-2 control-label">Account Owner</label>
                    <div class="col-md-4">
                       {{Form::select('Owner',$account_owners,$account->Owner,array("class"=>"select2"))}}
                    </div>

                    <label class="col-md-2 control-label">Ownership</label>
                    <div class="col-md-4">
                        <?php $ownership_array = array( ""=>"None", "Private"=>"Private" , "Public"=>"Public" ,"Subsidiary"=>"Subsidiary","Other"=>"Other" ); ?>
                        {{Form::select('Ownership', $ownership_array, $account->Ownership ,array("class"=>"form-control select2"))}}
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">First Name</label>
                    <div class="col-md-4">
                        <input type="text" name="FirstName" class="form-control" id="field-1" placeholder="" value="{{$account->FirstName}}" />
                    </div>

                    <label class="col-md-2 control-label">Last Name</label>
                    <div class="col-md-4">
                        <input type="text" name="LastName" class="form-control" id="field-1" placeholder="" value="{{$account->LastName}}" />
                    </div>

                </div>
                <div class="form-group ">
                    <label class="col-md-2 control-label">Account Number</label>
                    <div class="col-md-4 account_number_disable">
                        <input type="text" name="Number" class="form-control" id="field-1" placeholder="AUTO" value="{{$account->Number}}" />
                        <label class="label_disable form-control" disabled="disabled">{{$account->Number}}</label>
                    </div>

                    <label class="col-md-2 control-label">Website</label>
                    <div class="col-md-4">
                        <input type="text" name="Website" class="form-control" id="field-1" placeholder="" value="{{$account->Website}}" />
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">*Account Name</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="AccountName" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder=""  value="{{$account->AccountName}}"/>
                    </div>

                    <label class="col-md-2 control-label">Phone</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control"  name="Phone" id="field-1" placeholder="" value="{{$account->Phone}}" />
                    </div>

                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Vendor</label>
                    <div class="col-md-4">
                        <div class="make-switch switch-small" id="desablevendor">
                            <input type="checkbox" name="IsVendor"  @if($account->IsVendor == 1 )checked=""@endif value="1">
                        </div>
                    </div>

                    <label class="col-md-2 control-label">Fax</label>
                    <div class="col-md-4">
                        <input type="text" name="Fax" class="form-control" id="field-1" placeholder="" value="{{$account->Fax}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Customer</label>
                    <div class="col-md-4">
                        <div class="make-switch switch-small" id="desablecustomer">
                            <input type="checkbox" @if($account->IsCustomer == 1 )checked="" @endif name="IsCustomer" value="1">
                        </div>
                    </div>

                    <label class="col-md-2 control-label">Employee</label>
                    <div class="col-md-4">
                        <input type="text" name="Employee" class="form-control" id="field-1" placeholder="" value="{{$account->Employee}}" />
                    </div>
                </div>
                @if(is_reseller())
                @else
                <div class="form-group">
                    <label class="col-md-2 control-label">Reseller</label>
                    <div class="col-md-4">
                        <div class="make-switch switch-small" id="desablereseller">
                            <input type="checkbox" @if($account->IsReseller == 1 )checked="" @endif name="IsReseller" value="1">
                        </div>
                    </div>

                    <label class="col-md-2 control-label">Account Reseller</label>
                    <div class="col-md-4" id="disableresellerowner">
                      {{Form::select('ResellerOwner',$reseller_owners,(isset($accountreseller)?$accountreseller:'') ,array("class"=>"select2"))}}
                    </div>
                </div>
                @endif
                <div class="form-group">
                    <label class="col-md-2 control-label">Email</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="Email" data-validate="required" data-message-required="This is custom message for required field." id="field-1" placeholder="" value="{{$account->Email}}" />
                    </div>
                    <label class="col-md-2 control-label">Billing Email</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control"  name="BillingEmail" id="field-1" placeholder="" value="{{$account->BillingEmail}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Active</label>
                    <div class="col-md-4">
                        <div class="make-switch switch-small">
                            <input type="checkbox" name="Status"  @if($account->Status == 1 )checked=""@endif value="1">
                        </div>
                    </div>
 
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Account Tags</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="tags" name="tags" value="{{$account->tags}}" />
                    </div>
                    
                     <label class="col-md-2 control-label">VAT Number</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control"  name="VatNumber" id="field-1" placeholder="" value="{{$account->VatNumber}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Currency</label>
                    <div class="col-md-4">
                            @if($all_invoice_count == 0)
                            {{Form::SelectControl('currency',0,$account->CurrencyId,0,'CurrencyId')}}
                            <!--{Form::select('CurrencyI d', $currencies, $account->CurrencyId ,array("class"=>"form-control select2 small"))}}-->
                            @else
                            {{Form::SelectControl('currency',0,$account->CurrencyId,1,'CurrencyId')}}
                            <!--{Form::select('CurrencyId', $currencies, $account->CurrencyId ,array("class"=>"form-control select2 small",'disabled'))}}-->
                            {{Form::hidden('CurrencyId', ($account->CurrencyId))}}
                            @endif
                    </div>

                    <label class="col-md-2 control-label">Timezone</label>
                    <div class="col-md-4">
                        {{Form::select('TimeZone', $timezones, $account->TimeZone ,array("class"=>"form-control select2"))}}
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Verification Status</label>
                    <div class="col-md-4">
                        {{Account::$doc_status[$account->VerificationStatus]}}
                    </div>
 <label for="NominalAnalysisNominalAccountNumber" class="col-md-2 control-label">Nominal Code</label>
                    <div class="col-md-4">
                        <input type="text" class="form-control" autocomplete="off"  name="NominalAnalysisNominalAccountNumber" id="NominalAnalysisNominalAccountNumber" placeholder="" value="{{$account->NominalAnalysisNominalAccountNumber}}" />
                    </div>

                </div>

                @if(!empty($dynamicfields) && count($dynamicfields)>0)
                    <div class="form-group">
                @foreach($dynamicfields as $dynamicfield)
                    @if(!empty($dynamicfield['FieldSlug']))
                        @if($dynamicfield['FieldSlug']=='accountgateway')
                            <label class="col-md-2 control-label">{{$dynamicfield['FieldName']}}</label>
                            <div class="col-md-4">
                                {{Form::select('accountgateway[]', CompanyGateway::getCompanyGatewayIdList(), (isset($dynamicfield['FieldValue'])? explode(',',$dynamicfield['FieldValue']) : array() ) ,array("class"=>"form-control select2",'multiple'))}}
                            </div>
                        @endif
                        @if($dynamicfield['FieldSlug']=='vendorname')
                            <label class="col-md-2 control-label">{{$dynamicfield['FieldName']}}</label>
                            <div class="col-md-4">
                                <input type="text" class="form-control" autocomplete="off"  name="vendorname" id="field-1" value="{{$dynamicfield['FieldValue']}}" />
                            </div>
                        @endif
                        @if($dynamicfield['FieldSlug']=='pbxaccountstatus')
                            </div>
                    <div class="form-group">
                            <label class="col-md-2 control-label">{{$dynamicfield['FieldName']}}</label>
                            <div class="col-md-4">
                                {{Form::select('pbxaccountstatus', array('0'=>'Unblock','1'=>'Block'), (isset($dynamicfield['FieldValue'])? explode(',',$dynamicfield['FieldValue']) : array() ) ,array("class"=>"form-control select2"))}}
                            </div>
                        @endif
                        @if($dynamicfield['FieldSlug']=='autoblock')
                            <label class="col-md-2 control-label">{{$dynamicfield['FieldName']}} <span id="tooltip_lowstock" data-content="If Auto block OFF then Cron job will not change the status of this Account in PBX." data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary" data-original-title="" title="">?</span></label>
                            <div class="col-md-4">
                                <div class="make-switch switch-small">
                                    <input type="checkbox" @if($dynamicfield['FieldValue'] == 1 )checked="" @endif name="autoblock" value="1">
                                </div>
                            </div>
                        @endif
                    @endif
                @endforeach
                    </div>
                @endif
                <div class="form-group">
                    <label class="col-md-2 control-label">Language</label>
                    <div class="col-md-4">
                        {{ddl_language("", "LanguageID", ( isset($account->LanguageID)?$account->LanguageID:Translation::$default_lang_id ),"", "id")}}
                    </div>
                </div>

                <script>
                    $(document).ready(function() {
                        $(".btn-toolbar .btn").first().button("toggle");
                    });
                </script>               
                
                <div class="panel-title desc clear">
                    Description
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <textarea class="form-control" name="Description" id="events_log" rows="5" placeholder="Description">{{$account->Description}}</textarea>
                    </div>
                </div>
                
                            <div class="form-group">            
                    <label for="CustomerPassword" class="col-md-2 control-label">Customer Panel Password</label>
                    <div class="col-md-4">
        <input type="password" class="form-control"    id="CustomerPassword_hide" autocomplete="off" placeholder="Enter Password" value="" />
                            <input type="password" class="form-control"   name="password" id="CustomerPassword" autocomplete="off" placeholder="Enter Password" value="" />
                    </div>
                                <label class="col-md-2 control-label">Display Rate</label>
                    <div class="col-md-4">
                        <div class="make-switch switch-small">
                            <input type="checkbox" @if($account->DisplayRates == 1 )checked="" @endif name="DisplayRates" value="1">
                        </div>
                    </div>
                    </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Customer Payment Add</label>
                    <div class="col-md-4">
                        <div class="make-switch switch-small">
                            <input type="checkbox" @if(isset($accountdetails->CustomerPaymentAdd) && $accountdetails->CustomerPaymentAdd == 1 )checked="" @endif name="CustomerPaymentAdd" value="1">
                        </div>
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
                                <div class="panel-title desc col-md-3 ">
                                    @if($row->Required == 1)
                                    *
                                    @endif
                                    {{$row->Key}}
                                </div>
                                <div class="panel-title desc col-md-4 table_{{$row->AccountApprovalID}}" >
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
                                                    <a class="btn  btn-danger btn-sm btn-icon icon-left delete-doc"  href="{{URL::to('accounts/delete_doc/'.$row2->AccountApprovalListID)}}" ><i class="entypo-trash"></i>Delete</a>

                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        </table>

                                    @endif


                                </div>
                                <div class="col-md-5">
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
                    <label class="col-md-2 control-label">Address Line 1</label>
                    <div class="col-md-4">
                        <input type="text" name="Address1" class="form-control" id="field-1" placeholder="" value="{{$account->Address1}}" />
                    </div>

                    <label class="col-md-2 control-label">City</label>
                    <div class="col-md-4">
                        <input type="text" name="City" class="form-control" id="field-1" placeholder="" value="{{$account->City}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Address Line 2</label>
                    <div class="col-md-4">
                        <input type="text" name="Address2" class="form-control" id="field-1" placeholder="" value="{{$account->Address2}}" />
                    </div>

                    <label class="col-md-2 control-label">Post/Zip Code</label>
                    <div class="col-md-4">
                        <input type="text" name="PostCode" class="form-control" id="field-1" placeholder="" value="{{$account->PostCode}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Address Line 3</label>
                    <div class="col-md-4">
                        <input type="text" name="Address3" class="form-control" id="field-1" placeholder="" value="{{$account->Address3}}" />
                    </div>

                    <label for=" field-1" class="col-md-2 control-label">*Country</label>
                    <div class="col-md-4">

                    {{Form::select('Country', $countries, $account->Country ,array("class"=>"form-control select2"))}}
                    </div>
                </div>
            </div>
        </div>
        <?php
        if(AccountDiscountPlan::checkDiscountPlan($account->AccountID)){
            $BillingCycleTypeArray = SortBillingType();
        }else{
            $BillingCycleTypeArray = SortBillingType(1);
        }

        $Days = array( ""=>"Select",
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
                    <label class="col-md-2 control-label">Billing Class*</label>
                    <div class="col-md-4">
                        {{Form::select('BillingClassID', $BillingClass, (  isset($AccountBilling->BillingClassID)?$AccountBilling->BillingClassID:'' ) ,array("class"=>"select2 small form-control1"));}}
                    </div>
                    <label class="col-md-2 control-label">Billing Type*</label>
                    <div class="col-md-4">
                        {{Form::select('BillingType', AccountApproval::$billing_type, AccountBilling::getBillingKey($AccountBilling,'BillingType'),array('id'=>'billing_type',"class"=>"select2 small"))}}
                    </div>

                </div>
                <div class="form-group">

                    <label class="col-md-2 control-label">Billing Timezone*</label>
                    <div class="col-md-4">
                        {{Form::select('BillingTimezone', $timezones, (isset($AccountBilling->BillingTimezone)?$AccountBilling->BillingTimezone:'' ),array("class"=>"form-control select2"))}}

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
                    <label class="col-md-2 control-label">Billing Start Date*</label>
                    <div class="col-md-2">
                        @if($billing_disable == '' || ($billing_disable == '' && isset($AccountBilling->BillingCycleType) && $AccountBilling->BillingCycleType != 'manual'))
                            {{Form::text('BillingStartDate', $BillingStartDate,array('class'=>'form-control datepicker billing_start_date',"data-date-format"=>"yyyy-mm-dd"))}}
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
                        <label class="col-md-2 control-label">Current Billing Cycle</label>
                        <div class="col-md-4">{{$BillingCycleTypeArray[$AccountBilling->BillingCycleType]}}@if(!empty($oldBillingCycleValue)) {{'('.$oldBillingCycleValue.')'}} @endif</div>
                        <label class="col-md-2 control-label">New Billing Cycle Effective From</label>
                        <div class="col-md-4">{{$AccountNextBilling->LastInvoiceDate}}</div>
                    </div>
                @endif
                <div class="form-group">
                    <label class="col-md-2 control-label">@if(!empty($AccountNextBilling)) New @endif Billing Cycle*</label>
                    <div class="col-md-3">
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
                            <div class="billing_edit_text"> {{$BillingCycleTypeArray[$BillingCycleType]}} </div>
                        @endif

                        {{Form::select('BillingCycleType', $BillingCycleTypeArray, $BillingCycleType ,array("class"=>'form-control '.$hiden_class.' select2 '))}}

                    </div>
                    <div class="col-md-1">
                        @if($hiden_class != '')
                        <button class="btn btn-sm btn-primary tooltip-primary" id="billing_edit" data-original-title="Edit Billing Cycle" title="" data-placement="top" data-toggle="tooltip">
                            <i class="entypo-pencil"></i>
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
                        <label class="col-md-2 control-label">Billing Cycle - Start of Day*</label>
                        <div class="col-md-4">
                            @if($hiden_class != '' && $BillingCycleType =='weekly' )
                                <div class="billing_edit_text"> {{$Days[$BillingCycleValue]}} </div>
                            @endif

                            {{Form::select('BillingCycleValue',$Days, ($BillingCycleType =='weekly'?$BillingCycleValue:'') ,array("class"=>"form-control select2"))}}

                        </div>
                    </div>
                    <div id="billing_cycle_in_specific_days" class="billing_options" style="display: none">
                    <label class="col-md-2 control-label">Billing Cycle - for Days*</label>
                        <div class="col-md-4">
                            @if($hiden_class != '' && $BillingCycleType =='in_specific_days' )
                                <div class="billing_edit_text"> {{$BillingCycleValue}} </div>
                            @endif
                            {{Form::text('BillingCycleValue', ($BillingCycleType =='in_specific_days'?$BillingCycleValue:'') ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Billing Days"))}}
                        </div>
                    </div>
                    <div id="billing_cycle_subscription" class="billing_options" style="display: none">
                    <label class="col-md-2 control-label">Billing Cycle - Subscription Qty</label>
                        <div class="col-md-4">
                            @if($hiden_class != '' && $BillingCycleType =='subscription' )
                                <div class="billing_edit_text"> {{$BillingCycleValue}} </div>
                            @endif
                            {{Form::text('BillingCycleValue', ($BillingCycleType =='subscription'?$BillingCycleValue:'') ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Subscription Qty"))}}
                        </div>
                    </div>
                    <div id="billing_cycle_monthly_anniversary" class="billing_options" style="display: none">
                        <?php
                        $BillingCycleValue=date('Y-m-d',strtotime($BillingCycleValue));
                        ?>
                        <label class="col-md-2 control-label">Billing Cycle - Monthly Anniversary Date*</label>
                        <div class="col-md-4">
                            @if($hiden_class != '' && $BillingCycleType =='monthly_anniversary' )
                                <div class="billing_edit_text"> {{$BillingCycleValue}} </div>
                            @endif
                            {{Form::text('BillingCycleValue', ($BillingCycleType =='monthly_anniversary'?$BillingCycleValue:'') ,array("class"=>"form-control datepicker","Placeholder"=>"Anniversary Date" , "data-start-date"=>"" ,"data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Auto Pay</label>
                    <div class="col-md-4">
                        {{Form::select('AutoPaymentSetting', BillingClass::$AutoPaymentSetting, ( isset($AccountBilling->AutoPaymentSetting)?$AccountBilling->AutoPaymentSetting:'never' ),array("class"=>"form-control select2 small"))}}
                    </div>
                    <label class="col-md-2 control-label">Auto Pay Method</label>
                    <div class="col-md-4">
                        {{Form::select('AutoPayMethod', BillingClass::$AutoPayMethod, ( isset($AccountBilling->AutoPayMethod)?$AccountBilling->AutoPayMethod:'0' ),array("class"=>"form-control select2 small"))}}
                    </div>
                 </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Send Invoice via Email</label>
                    <div class="col-md-4">
                        {{Form::select('SendInvoiceSetting', BillingClass::$SendInvoiceSetting, ( isset($AccountBilling->SendInvoiceSetting)?$AccountBilling->SendInvoiceSetting:'after_admin_review' ),array("class"=>"form-control select2"))}}
                    </div>
                </div>
                @if($hiden_class != '')
                <div class="form-group">
                    <label class="col-md-2 control-label">Last Invoice Date</label>
                    <div class="col-md-4">
                        <?php
                        $LastInvoiceDate = isset($AccountBilling->LastInvoiceDate)?$AccountBilling->LastInvoiceDate:'';
                        ?>
                        {{Form::hidden('LastInvoiceDate', $LastInvoiceDate)}}
                        {{$LastInvoiceDate}}
                    </div>
                    <label class="col-md-2 control-label">Next Invoice Date</label>
                    <div class="col-md-3">
                        <?php
                        $NextInvoiceDate = isset($AccountBilling->NextInvoiceDate)?$AccountBilling->NextInvoiceDate:'';
                        ?>
                        @if($hiden_class != '' && isset($NextInvoiceDate) )
                            <div class="next_invoice_edit_text"> {{$NextInvoiceDate}} </div>
                        @endif
                            {{Form::text('NextInvoiceDate', $NextInvoiceDate,array('class'=>'form-control '.$hiden_class.' datepicker next_invoice_date',"data-date-format"=>"yyyy-mm-dd"))}}
                    </div>
                    <div class="col-md-1">
                        @if($hiden_class != '')
                        <button class="btn btn-sm btn-primary tooltip-primary" id="next_invoice_edit" data-original-title="Edit Next Invoice Date" title="" data-placement="top" data-toggle="tooltip">
                            <i class="entypo-pencil"></i>
                        </button>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2 control-label">Last Charge Date</label>
                    <div class="col-md-4">
                        <?php
                        $LastChargeDate = isset($AccountBilling->LastChargeDate)?$AccountBilling->LastChargeDate:'';
                        ?>
                        {{Form::hidden('LastChargeDate', $LastChargeDate)}}
                        {{$LastChargeDate}}
                    </div>
                    <label class="col-md-2 control-label">Next Charge Date
                        <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This is period End Date. e.g. if Billing Cycle is monthly then Next Charge date will be last day of the month  i-e 30/04/2018" data-original-title="Next Charge Date">?</span>
                    </label>
                    <div class="col-md-3">
                        <?php
                        $NextChargeDate = isset($AccountBilling->NextChargeDate)?$AccountBilling->NextChargeDate:'';
                        ?>
                       @if($hiden_class != '' && isset($NextChargeDate) )
                           <div class="next_charged_edit_text"> {{$NextChargeDate}} </div>
                       @endif
                           {{Form::text('NextChargeDate', $NextChargeDate,array('class'=>'form-control '.$hiden_class.' datepicker next_charged_date',"data-date-format"=>"yyyy-mm-dd"))}}
                    </div>
                    {{--
                    <div class="col-md-1">
                        @if($hiden_class != '')
                        <button class="btn btn-sm btn-primary tooltip-primary" id="next_charged_edit" data-original-title="Edit Next charged Date" title="" data-placement="top" data-toggle="tooltip">
                            <i class="entypo-pencil"></i>
                        </button>
                        @endif
                    </div>--}}
                </div>
                @else
                    <div class="form-group">
                        <label class="col-md-2 control-label">Next Invoice Date</label>
                        <div class="col-md-3">
                            <?php
                            $NextInvoiceDate = isset($AccountBilling->NextInvoiceDate)?$AccountBilling->NextInvoiceDate:'';
                            ?>
                            {{Form::text('NextInvoiceDate', $NextInvoiceDate,array('class'=>'form-control '.$hiden_class.' datepicker next_invoice_date',"data-date-format"=>"yyyy-mm-dd"))}}
                        </div>
                        <label class="col-md-2 control-label">Next Charge Date
                            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This is period End Date. e.g. if Billing Cycle is monthly then Next Charge date will be last day of the month  i-e 30/04/2018" data-original-title="Next Charge Date">?</span>
                        </label>
                        <div class="col-md-3">
                            <?php
                            $NextChargeDate = isset($AccountBilling->NextChargeDate)?$AccountBilling->NextChargeDate:'';
                            ?>
                            {{Form::text('NextChargeDate', $NextChargeDate,array('class'=>'form-control '.$hiden_class.' datepicker next_charged_date',"data-date-format"=>"yyyy-mm-dd",'disabled'))}}
                        </div>
                    </div>
                @endif

            </div>
        </div>
        @if(AccountBilling::where(array('AccountID'=>$account->AccountID,'BillingCycleType'=>'manual'))->count() == 0 || !empty($BillingCycleType))
            @include('accountdiscountplan.index')
        @endif
        @if(User::checkCategoryPermission('AccountService','View'))
            @include('accountservices.index')
        @endif
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
                    <label for="field-1s" class="col-md-3 control-label">Show All Available Payment Methods On Invoice
                        <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="if ON then customer can pay invoice by selecting any Available payment method, if OFF then customer can pay by only selected Preferred Payment Method" data-original-title="Show All Payment Methods On Invoice" class="label label-info popover-primary">?</span>
                    </label>
                    <div class="col-md-4">
                        <div class="make-switch switch-small">
                            <input type="checkbox" @if($account->ShowAllPaymentMethod == 1 )checked="" @endif name="ShowAllPaymentMethod" value="1">
                        </div>
                    </div>


                </div>
                <div class="form-group">
                    <div class="col-md-3">

                        <h4>Preferred Payment Method</h4>

                        <ul class="icheck-list">
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-3-11" name="PaymentMethod" value="AuthorizeNet" @if( $account->PaymentMethod == 'AuthorizeNet' ) checked="" @endif />
                                <label for="minimal-radio-3-11">AuthorizeNet</label>
                            </li>
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-12-11" name="PaymentMethod" value="AuthorizeNetEcheck" @if( $account->PaymentMethod == 'AuthorizeNetEcheck' ) checked="" @endif />
                                <label for="minimal-radio-12-11">AuthorizeNet Echeck</label>
                            </li>
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-9-11" name="PaymentMethod" value="FideliPay" @if( $account->PaymentMethod == 'FideliPay' ) checked="" @endif />
                                <label for="minimal-radio-9-11">FideliPay</label>
                            </li>
                            <li>
                                <input class="icheck-11" type="radio" id="minimal-radio-1-11" name="PaymentMethod" value="Paypal" @if( $account->PaymentMethod == 'Paypal' ) checked="" @endif />
                                <label for="minimal-radio-1-11">Paypal</label>
                            </li>
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-10-11" name="PaymentMethod" value="PeleCard" @if( $account->PaymentMethod == 'PeleCard' ) checked="" @endif />
                                <label for="minimal-radio-10-11">PeleCard</label>
                            </li>
                            <li>
                                <input class="icheck-11" type="radio" id="minimal-radio-7-11" name="PaymentMethod" value="SagePay" @if( $account->PaymentMethod == 'SagePay' ) checked="" @endif />
                                <label for="minimal-radio-7-11">SagePay</label>
                            </li>
                            <li>
                                <input class="icheck-11" type="radio" id="minimal-radio-8-11" name="PaymentMethod" value="SagePayDirectDebit" @if( $account->PaymentMethod == 'SagePayDirectDebit' ) checked="" @endif />
                                <label for="minimal-radio-8-11">SagePay Direct Debit</label>
                            </li>
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-4-11" name="PaymentMethod" value="Stripe" @if( $account->PaymentMethod == 'Stripe' ) checked="" @endif />
                                <label for="minimal-radio-4-11">Stripe</label>
                            </li>
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-6-11" name="PaymentMethod" value="StripeACH" @if( $account->PaymentMethod == 'StripeACH' ) checked="" @endif />
                                <label for="minimal-radio-6-11">Stripe ACH</label>
                            </li>
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-11-11" name="PaymentMethod" value="MerchantWarrior" @if( $account->PaymentMethod == 'MerchantWarrior' ) checked="" @endif />
                                <label for="minimal-radio-11-11">MerchantWarrior</label>
                            </li>
                            <li>
                                <input tabindex="8" class="icheck-11" type="radio" id="minimal-radio-2-11" name="PaymentMethod" value="Wire Transfer" @if( $account->PaymentMethod == 'Wire Transfer' ) checked="" @endif />
                                <label for="minimal-radio-2-11">Wire Transfer</label>
                            </li>
                            <li>
                                <input type="radio" class="icheck-11" id="minimal-radio-5-11" name="PaymentMethod" value="Other" @if( $account->PaymentMethod == 'Other' ) checked="" @endif />
                                <label for="minimal-radio-5-11">Other</label>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-9">
                        @include('customer.paymentprofile.mainpaymentGrid')
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
    var ResellerCount = '{{$ResellerCount}}';
    var AccountResellerCount = '{{$accountreseller}}';
    jQuery(document).ready(function ($) {
        if(AccountResellerCount>0 || ResellerCount>0){
            $("#desablereseller").addClass('deactivate');
            $('#disableresellerowner select').attr("disabled", "disabled");
        }else {
            if ($('[name="IsReseller"]').prop("checked") == true) {
                $('[name="IsCustomer"]').prop("checked", false).trigger('change');
                $('[name="IsVendor"]').prop("checked", false).trigger('change');
                $("#desablecustomer").addClass('deactivate');
                $("#desablevendor").addClass('deactivate');
                //$("#desablereseller").addClass('deactivate');
                $('#disableresellerowner select').attr("disabled", "disabled");
            } else {
                $("#desablecustomer").removeClass('deactivate');
                $("#desablevendor").removeClass('deactivate');
                $("#desablereseller").removeClass('deactivate');
                //$('#disableresellerowner select').removeAttr("disabled");
            }
        }

        if(ResellerCount==0){
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
                    $('#disableresellerowner select').removeAttr("disabled");
                }
            });
        }
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

        $("#save_account").click(function (ev) {
            ev.preventDefault();
            //Subscription , Additional charge filter fields should not in account save.
            $('#service_filter').find('input').attr("disabled","disabled");
            $('#subscription_filter').find('input').attr("disabled", "disabled");
            $('#oneofcharge_filter').find('input').attr("disabled", "disabled");
            $('#oneofcharge_filter').find('select').attr("disabled", "disabled");

            url= baseurl + '/accounts/update/{{$account->AccountID}}';
            var data =$('#account-from').serialize();
            ajax_json(url,data,function(response){

              //Subscription , Additional charge filter fields to enable again.
              $('#service_filter').find('input').attr("disabled","disabled");
              $('#subscription_filter').find('input').removeAttr("disabled");
              $('#oneofcharge_filter').find('input').removeAttr("disabled");
              $('#oneofcharge_filter').find('select').removeAttr("disabled");

              if(response.status =='success'){
                     toastr.success(response.message, "Success", toastr_opts);
                      setTimeout(function () {
                          window.location.reload()
                      }, 1000);
                  /*if($('[name="Billing"]').prop("checked") == true && BillingChanged) {
                      setTimeout(function () {
                          window.location.reload()
                      }, 1000);
                  }*/
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
            $(".billing_start_date").removeAttr('readonly');
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
                case "manual":
                    $(".billing_start_date").attr('readonly','true');
                    break;
            }
            if(FirstTimeTrigger == true) {
                BillingChanged = false;
                FirstTimeTrigger= false;
            }else{
                BillingChanged = true;
            }
            if(selection=='weekly' || selection=='monthly_anniversary' || selection=='in_specific_days' || selection=='subscription' || selection=='manual'){
                changeBillingDates('');
            }else{
                changeBillingDates('');
            }
        });
        $('[name="BillingStartDate"]').on( "change",function(e){
            BillingChanged = true;
            billing_disable='{{$billing_disable}}';
            if(billing_disable==''){
                $('#billing_edit').trigger("click");
                $('#next_invoice_edit').trigger("click");
                $('#next_charged_edit').trigger("click");
            }
            changeBillingDates('');
        });
        $('[name="BillingCycleValue"]').on( "change",function(e){
            BillingChanged = true;
            changeBillingDates($(this).val());
        });
        $('[name="Billing"]').on( "change",function(e){
            if($('[name="Billing"]').prop("checked") == true){
                $(".billing-section").show();
                $(".billing-section-hide").nextAll('.panel').attr('data-collapsed',0);
                $(".billing-section-hide").nextAll('.panel').find('.panel-body').show();
                $('.billing-section .select2-container').css('visibility','visible');
                $("#subscription_filter").find('.panel-body').hide();
                $("#oneofcharge_filter").find('.panel-body').hide();
                $("#clitable_filter").find('.panel-body').hide();
                $("#service_filter").find('.panel-body').hide();
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
            $('#next_invoice_edit').trigger("click");
            $('#next_charged_edit').trigger("click");
            return false;
        });

        $('select[name="BillingCycleType"]').trigger( "change" );

        $('#next_invoice_edit').on( "click",function(e){
            e.preventDefault();
            $('[name="NextInvoiceDate"]').removeClass('hidden');
            $('.next_invoice_edit_text').addClass('hidden');
            $(this).addClass('hidden');
            return false;
        });

        $('#next_charged_edit').on( "click",function(e){
            e.preventDefault();
            $('[name="NextChargeDate"]').removeClass('hidden');
            $('.next_charged_edit_text').addClass('hidden');
            $(this).addClass('hidden');
            return false;
        });

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
                        var down_html = $('.doc_'+$("#form-upload [name='AccountApprovalID']").val()).html()+'<tr><td>'+filename+'</td><td><a class="btn btn-success btn-sm btn-icon icon-left"  href="'+url3+'" title="" ><i class="entypo-down"></i>Download</a> <a class="btn  btn-danger delete-doc btn-sm btn-icon icon-left"  href="'+delete_doc_url+'" title="" ><i class="entypo-trash"></i>Delete</a></td></tr>';
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
                            if(response.data.AutoPaymentSetting == null || response.data.AutoPaymentSetting == '') {
                                $("[name='AutoPaymentSetting']").select2().select2('val', 'never');
                            }
                            else{
                                $("[name='AutoPaymentSetting']").select2().select2('val', response.data.AutoPaymentSetting);
                            }
                            $("[name='AutoPayMethod']").select2().select2('val', response.data.AutoPayMethod);

                        }
                    },
                });
            }

        });

        $('[name="ResellerOwner"]').on( "change",function(e){
            if($(this).val()>0) {
                $("#desablereseller").addClass('deactivate');
            }else{
                $("#desablereseller").removeClass('deactivate');
            }

        });

        @if ($account->VerificationStatus == Account::NOT_VERIFIED)
        $(".btn-toolbar .btn").first().button("toggle");
        @elseif ($account->VerificationStatus == Account::VERIFIED)
        $(".btn-toolbar .btn").last().button("toggle");
        @endif

        function changeBillingDates(BillingCycleValue){
            var BillingStartDate;
            var BillingCycleType;
            var billing_disable;
            //var BillingCycleValue;

            billing_disable = '{{$billing_disable}}';
            //BillingStartDate = $('[name="LastInvoiceDate"]').val();
            if(billing_disable==''){
                BillingStartDate = $('[name="BillingStartDate"]').val();
            }else{
                BillingStartDate = $('[name="LastInvoiceDate"]').val();
            }
            BillingCycleType = $('select[name="BillingCycleType"]').val();
            if(BillingCycleValue==''){
                BillingCycleValue = $('[name="BillingCycleValue"]').val();
            }
            if(BillingStartDate=='' || BillingCycleType==''){
                return true;
            }

            updatenextchargedate=1;
            if(billing_disable!=''){
                LastChargeDate = $('[name="LastChargeDate"]').val();
                if(BillingStartDate!=LastChargeDate){
                    updatenextchargedate=0;
                }

            }

            getNextBillingDatec_url =  '{{ URL::to('accounts/getNextBillingDate')}}';
            $.ajax({
                url: getNextBillingDatec_url,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $('[name="NextInvoiceDate"]').val(response.NextBillingDate);
                    if(updatenextchargedate==1) {
                        $('[name="NextChargeDate"]').val(response.NextChargedDate);
                    }
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
                    <label class="col-md-3 control-label">File Select</label>
                    <div class="col-md-5">
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
                    <label class="col-md-3 control-label">CLI</label>
                    <div class="col-md-9">
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
@include('accountdiscountplan.discountplanmodal')
<script>
setTimeout(function(){
	$('#CustomerPassword_hide').hide();
	},1000);
</script>
@stop
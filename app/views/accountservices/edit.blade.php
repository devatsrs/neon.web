@extends('layout.main')

@section('content')
    <style>
        .panel-heading.active {
            border-left: 3px solid #00cc00;
            padding-left: 7px;
        }
        .panel-header-btn {
            padding: 5px;
            cursor: pointer;
            display: inline-block;
        }
    </style>
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>

            <a href="{{URL::to('accounts')}}">Account</a>
        </li>
        <li><a href="{{URL::to('accounts/'.$account->AccountID.'/edit')}}">Edit Account({{$account->AccountName}})</a></li>
        <li>
            <a><span>{{accountservice_dropbox_new($account->AccountID,$AccountServiceID)}}</span></a>
        </li>
        <li class="active">
            <strong>Account Service</strong>
        </li>
    </ol>
    <h3>Account Service</h3>
    @include('includes.errors')
    @include('includes.success')
    <p style="text-align: right;">
        @if( User::checkCategoryPermission('AuthenticationRule','View'))
            @if($account->IsCustomer==1 || $account->IsVendor==1)
                <a href="{{URL::to('accounts/authenticate/'.$account->AccountID.'-'.$AccountService->AccountServiceID)}}" class="btn btn-primary btn-sm btn-icon icon-left">
                    <i class="entypo-lock"></i>
                    Authentication Rule
                </a>
            @endif
        @endif
        <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..." id="save_service">
            <i class="entypo-floppy"></i>
            Save
        </button>

        <a href="{{URL::to('/accounts/'.$account->AccountID.'/edit')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    <br>
    <div class="row">
        <div class="col-md-12">
            <form id="service-edit-form" method="post" class="form-horizontal form-groups-bordered">

                <div class="panel panel-primary " data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Invoice Description
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-md-2 control-label">Service Title
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This Service Title will appear on the invoice" data-original-title="Service Title">?</span>
                            </label>
                            <div class="col-md-4">
                                <input type="text" name="ServiceTitle" value="{{$ServiceTitle}}" class="form-control" id="field-5" placeholder="">
                            </div>
                            <label for="field-1" class="col-md-2 control-label">Show Service Title
                            </label>
                            <div class="col-md-4">
                                <div class="make-switch switch-small">
                                    <input type="checkbox" name="ServiceTitleShow"  @if($ServiceTitleShow == 1 )checked=""@endif value="1">
                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-md-2 control-label">Service Description
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This Service Description will appear on the invoice" data-original-title="Service Description">?</span></label>
                            </label>
                            <div class="col-md-4">
                                <textarea class="form-control" name="ServiceDescription" rows="5" placeholder="Description">{{$ServiceDescription}}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

                @if($ROUTING_PROFILE =='1')
                    <div class="panel panel-primary" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Routing
                            </div>

                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>

                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Routing Profile</label>
                                <div class="col-md-4">
                                    {{Form::select('routingprofile', [null=>'Select'] + $routingprofile, (isset($RoutingProfileToCustomer->RoutingProfileID)?$RoutingProfileToCustomer->RoutingProfileID:'' ) ,array("class"=>"select2 small form-control1"));}}
                                </div>


                            </div>


                        </div>
                    </div>
                @endif
                <div class="panel panel-primary auto-payment-hide" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Contract
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>



                    <div class="panel-body ">
                        @if($AccountService->CancelContractStatus)
                            <div class="col-md-3"></div>
                            <div class="form-group col-md-6 center-block alert alert-danger text-center">
                                <h3 class="text-danger">Contract Is Cancel</h3>
                            </div>
                            <div class="col-md-3"></div>
                        @endif
                        <div class="form-group">
                            <div class="col-md-12 text-right">
                                @if(!$AccountService->CancelContractStatus)
                                    <a title="Cancel Contract" class="btn btn-danger btn-sm"  data-toggle="modal" data-target="#add-new-modal-accounts"> <i class="entypo-cancel"></i> </a>
                                @else
                                    <a title="Renew Contract"  class="btn btn-info" id="renewal"> <i class="entypo-info"></i> </a>
                                @endif
                                <a title="History" class="btn btn-default btn-sm" data-toggle="modal" data-target="#history-modal" data-dismiss="modal" onclick="load_history()"> <i class="entypo-back-in-time"></i> </a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Contract Start Date</label>
                            <div class="col-md-4">
                                <input type="text" data-date-format="yyyy-mm-dd" @if(isset($AccountServiceContract->ContractStartDate)) value="{{$AccountServiceContract->ContractStartDate}}" @endif  class="form-control datepicker" id="StartDate" name="StartDate">
                            </div>

                            <label class="col-md-2 control-label">Contract End Date</label>
                            <div class="col-md-4">
                                <input type="text" data-date-format="yyyy-mm-dd" @if(isset($AccountServiceContract->ContractEndDate)) value="{{$AccountServiceContract->ContractEndDate}}" @endif  class="form-control datepicker" id="EndDate" name="EndDate">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="panel-options">
                                <label class="col-md-2 control-label">Duration(months)</label>
                                <div class="col-md-4">
                                    <input type="number"  min="0" @if(isset($AccountServiceContract->Duration)) value="{{$AccountServiceContract->Duration}}" @endif class="form-control" name="Duration">
                                </div>
                            </div>
                            <label class="col-md-2 control-label">Auto Renewal</label>
                            <div class="col-md-4">
                                <div class="panel-options">
                                    <div class="make-switch switch-small" >
                                        <input type="checkbox" @if(isset($AccountServiceContract->AutoRenewal) && $AccountServiceContract->AutoRenewal == 1) checked @endif   name="AutoRenewal">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="panel-group cancelRadio" id="accordion">
                                            <div class="panel panel-default">
                                                <div class="panel-heading" style="background-color:white;text-align: center;">
                                                    <label for='r11'>
                                                        <input type='radio' @if(!isset($AccountServiceContract->ContractTerm) || $AccountServiceContract->ContractTerm == 1 ) checked  @endif id='r11' name='ContractTerm' value='1' required />
                                                        No Fee
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-group cancelRadio" id="accordion">
                                            <div class="panel panel-default">
                                                <div class="panel-heading" style="background-color:white;text-align: center;">
                                                    <label for='r12'>
                                                        <input type='radio' @if(isset($AccountServiceContract->ContractTerm) && $AccountServiceContract->ContractTerm == 2 ) checked  @endif id='r12' name='ContractTerm' value='2' required />
                                                        Remaining Term Of Contract
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="table-4_processing" class="dataTables_processing process">Processing...</div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="panel-group cancelRadio" id="accordion">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading" style="background-color:white;text-align: center;">
                                                        <label for='r13'>
                                                            <i></i>
                                                            <input type='radio' @if(isset($AccountServiceContract->ContractTerm) && $AccountServiceContract->ContractTerm == 3 ) checked  @endif  id='r13' name='ContractTerm' value='3' required />
                                                            Fixed Fee
                                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree"></a>
                                                        </label>
                                                    </div>
                                                    <div id="collapseThree" class="panel-collapse collapse in">
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="panel-options">
                                                                    <div class="col-md-12">
                                                                        <label class="control-label">Fixed Fee</label>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <input type="number"  min="0" @if(isset($AccountServiceContract->ContractReason) && $AccountServiceContract->ContractTerm == 3 ) value="{{$AccountServiceContract->ContractReason}}"  @endif class="form-control" name="FixedFee">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="panel-group cancelRadio" id="accordion">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading" style="background-color:white;text-align: center;">
                                                        <label for='r14'>
                                                            <input type='radio' @if(isset($AccountServiceContract->ContractTerm) && $AccountServiceContract->ContractTerm == 4 ) checked  @endif id='r14' name='ContractTerm' value='4' required />
                                                            Remaining Term Of Contract(%)
                                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour"></a>
                                                        </label>
                                                    </div>
                                                    <div id="collapseFour" class="panel-collapse collapse in">
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="panel-options">
                                                                    <div class="col-md-12">
                                                                        <label class="control-label">Percentage</label>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <input type="number" min="0" @if(isset($AccountServiceContract->ContractReason) && $AccountServiceContract->ContractTerm == 4 ) value="{{$AccountServiceContract->ContractReason}}"  @endif class="form-control" name="Percentage">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="panel-group cancelRadio" id="accordion">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading" style="background-color:white;text-align: center;">
                                                        <label for='r15'>
                                                            <input type='radio' @if(isset($AccountServiceContract->ContractTerm) && $AccountServiceContract->ContractTerm == 5 ) checked  @endif id='r15' name='ContractTerm' value='5' required />
                                                            Fixed Fee + Remaining Term Of Contract
                                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFive"></a>
                                                        </label>
                                                    </div>
                                                    <div id="collapseFive" class="panel-collapse collapse in">
                                                        <div class="panel-body">
                                                            <div class="form-group">
                                                                <div class="panel-options">
                                                                    <div class="col-md-12">
                                                                        <label class="control-label">Fixed Fee</label>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <input type="number"  min="0" @if(isset($AccountServiceContract->ContractReason) && $AccountServiceContract->ContractTerm == 5 ) value="{{$AccountServiceContract->ContractReason}}"  @endif class="form-control" name="FixedFeeContract">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{--<div class="row">--}}
                                    {{--<div class="col-md-4"></div>--}}
                                    {{----}}
                                    {{--<div class="col-md-4"></div>--}}

                                    {{--</div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Service Title For Invoice -->
                    <!-- Service subscription billing cycle start-->

                    <div class="panel panel-primary " data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Service Billing Cycle
                            </div>
                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i> </a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="field-1" class="col-md-2 control-label">
                                    Billing Cylce
                                </label>
                                <div class="col-md-4">
                                    {{Form::select('SubscriptionBillingCycleType',SortBillingType(3),$AccountService->SubscriptionBillingCycleType,array("class"=>"form-control select2"))}}
                                    <input type="hidden" name="SubscriptionBillingCycleValue" value="">
                                    <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Package Section Start -->

                    <div class="panel panel-primary hidden" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Package
                            </div>
                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i> </a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="field-1" class="col-md-2 control-label">
                                    Package
                                </label>
                                <div class="col-md-4">
                                    {{ Form::select('PackageId', $Packages, $PackageId, array("class"=>"select2")) }}
                                </div>
                                <label for="field-1" class="col-md-2 control-label">
                                    RateTable
                                </label>
                                <div class="col-md-4">
                                    {{ Form::select('RateTableID', $RateTable, $RateTableID, array("class"=>"select2")) }}
                                </div>

                            </div>


                        </div>
                    </div>

                    <!-- Package Section End -->

                    <!-- Service subscription billing cycle end-->
                    @include('accountsubscription.index')
                    @include('accountoneoffcharge.index')
                    @include('accounts.cli_tables')

                            <!-- Account Option start -->

                    <div class="panel panel-primary additional-optional-section-hide" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Additional Options
                            </div>
                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>

                        <div class="panel-body">

                            <!-- Account Tarrif start -->
                            <div class="panel panel-primary tarrif-section-hide" data-collapsed="0">
                                <div class="panel-heading">
                                    <div class="panel-title">
                                        Rate Table
                                    </div>

                                    <div class="panel-options">
                                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                    </div>
                                </div>

                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="field-1" class="col-md-2 control-label">Access</label>
                                        <div class="col-md-4">
                                            {{ Form::select('InboundTariffID', $rate_table , $InboundTariffID , array("class"=>"select2")) }}
                                        </div>

                                        <label class="col-md-2 control-label">Termination</label>
                                        <div class="col-md-4">
                                            {{ Form::select('OutboundTariffID', $termination_rate_table , $OutboundTariffID , array("class"=>"select2")) }}
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Account Tarrif end -->

                            <?php
                            $billing_disable = $hiden_class= '';
                            /*if($invoice_count > 0){
                                $billing_disable = 'disabled';
                            }*/
                            if(isset($AccountBilling->BillingCycleType)){
                                $hiden_class= 'hidden';
                                $billing_disable = 'disabled';
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
                                    <!--
        <div class="panel panel-primary billing-section-hide"   data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Billing
                </div>

                <div class="panel-options">
                    <div class="make-switch switch-small">
                        <input type="checkbox" @if(isset($AccountBilling->ServiceBilling) && $AccountBilling->ServiceBilling == 1 )checked="" @endif name="ServiceBilling" value="1">
                    </div>
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body billing-section">
                <div class="form-group">
                    <?php
                            $BillingStartDate = isset($AccountBilling->BillingStartDate)?$AccountBilling->BillingStartDate:'';
                            if(!empty($BillingStartDate)){
                                $BillingStartDate = date('Y-m-d',strtotime($BillingStartDate));
                            }
                            /*if(empty($BillingStartDate)){
                                $BillingStartDate = date('Y-m-d',strtotime($account->created_at));
                            }*/
                            ?>
                                    <label for="field-1" class="col-md-2 control-label">Billing Class</label>
                                    <div class="col-md-4">
                                        {{Form::select('BillingClassID', $BillingClass, (  isset($AccountBilling->BillingClassID)?$AccountBilling->BillingClassID:'' ) ,array("class"=>"select2 small form-control1"));}}
                                    </div>
                                    <label for="field-1" class="col-md-2 control-label">Billing Start Date</label>
                                    <div class="col-md-4">
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
                                        <label for="field-1" class="col-md-2 control-label">Current Billing Cycle</label>
                                        <div class="col-md-4">{{SortBillingType()[$AccountBilling->BillingCycleType]}}@if(!empty($oldBillingCycleValue)) {{'('.$oldBillingCycleValue.')'}} @endif</div>
                        <label for="field-1" class="col-md-2 control-label">New Billing Cycle Effective From</label>
                        <div class="col-md-4">{{$AccountNextBilling->LastInvoiceDate}}</div>
                    </div>
                @endif
                                    <div class="form-group">
                                        <label for="field-1" class="col-md-2 control-label">@if(!empty($AccountNextBilling)) New @endif Billing Cycle</label>
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
                                    <div class="billing_edit_text"> {{SortBillingType()[$BillingCycleType]}} </div>
                        @endif

                            {{Form::select('BillingCycleType', SortBillingType(), $BillingCycleType ,array("class"=>'form-control '.$hiden_class.' select2 '))}}

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
                                        <label for="field-1" class="col-md-2 control-label">Billing Cycle - Start of Day</label>
                                        <div class="col-md-4">
                                            @if($hiden_class != '' && $BillingCycleType =='weekly' )
                                    <div class="billing_edit_text"> {{$Days[$BillingCycleValue]}} </div>
                            @endif

                            {{Form::select('BillingCycleValue',$Days, ($BillingCycleType =='weekly'?$BillingCycleValue:'') ,array("class"=>"form-control select2"))}}

                                    </div>
                                </div>
                                <div id="billing_cycle_in_specific_days" class="billing_options" style="display: none">
                                    <label for="field-1" class="col-md-2 control-label">Billing Cycle - for Days</label>
                                    <div class="col-md-4">
                                        @if($hiden_class != '' && $BillingCycleType =='in_specific_days' )
                                    <div class="billing_edit_text"> {{$BillingCycleValue}} </div>
                            @endif
                            {{Form::text('BillingCycleValue', ($BillingCycleType =='in_specific_days'?$BillingCycleValue:'') ,array("data-mask"=>"decimal", "data-min"=>1, "maxlength"=>"3", "data-max"=>365, "class"=>"form-control","Placeholder"=>"Enter Billing Days"))}}
                                    </div>
                                </div>
                                <div id="billing_cycle_subscription" class="billing_options" style="display: none">
                                    <label for="field-1" class="col-md-2 control-label">Billing Cycle - Subscription Qty</label>
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
                                    <label for="field-1" class="col-md-2 control-label">Billing Cycle - Monthly Anniversary Date</label>
                                    <div class="col-md-4">
                                        @if($hiden_class != '' && $BillingCycleType =='monthly_anniversary' )
                                    <div class="billing_edit_text"> {{$BillingCycleValue}} </div>
                            @endif
                            {{Form::text('BillingCycleValue', ($BillingCycleType =='monthly_anniversary'?$BillingCycleValue:'') ,array("class"=>"form-control datepicker","Placeholder"=>"Anniversary Date" , "data-start-date"=>"" ,"data-date-format"=>"yyyy-mm-dd", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
                                    </div>
                                </div>
                            </div>
                            @if($hiden_class != '')
                                    <div class="form-group">
                                        <label for="field-1" class="col-md-2 control-label">Last Invoice Date</label>
                                        <div class="col-md-4">
                                            <?php
                            $LastInvoiceDate = isset($AccountBilling->LastInvoiceDate)?$AccountBilling->LastInvoiceDate:'';
                            ?>
                            {{Form::hidden('LastInvoiceDate', $LastInvoiceDate)}}
                            {{$LastInvoiceDate}}
                                    </div>
                                    <label for="field-1" class="col-md-2 control-label">Next Invoice Date</label>
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
                                    <label for="field-1" class="col-md-2 control-label">Last Charge Date</label>
                                    <div class="col-md-4">
                                        <?php
                            $LastChargeDate = isset($AccountBilling->LastChargeDate)?$AccountBilling->LastChargeDate:'';
                            ?>
                            {{Form::hidden('LastChargeDate', $LastChargeDate)}}
                            {{$LastChargeDate}}
                                    </div>
                                    <label for="field-1" class="col-md-2 control-label">Next Charge Date</label>
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
                    </div>
                    --}}
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
                                -->
                            @if(AccountBilling::where(array('AccountID'=>$AccountID,'BillingCycleType'=>'manual'))->count() == 0 || !empty($BillingCycleType))
                                @include('accountdiscountplan.index')
                            @endif

                        </div>
                    </div>
                    <!-- account options end -->
            </form>
        </div>
    </div>


    <script type="text/javascript">
        var BillingChanged;
        var FirstTimeTrigger = true;
        jQuery(document).ready(function ($) {
            $('.dataTables_processing').css("visibility","hidden");

            $("#StartDate").datepicker({
                todayBtn:  1,
                autoclose: true
            }).on('changeDate', function (selected) {
                var minDate = new Date(selected.date.valueOf());
                var endDate = $('#EndDate');
                endDate.datepicker('setStartDate', minDate);
                if(endDate.val() && new Date(endDate.val()) != undefined) {
                    if(minDate > new Date(endDate.val()))
                        endDate.datepicker("setDate", minDate)
                }
            });

            $("#EndDate").datepicker({autoclose: true})
                    .on('changeDate', function (selected) {
                        var maxDate = new Date(selected.date.valueOf());
                        //$('#StartDate').datepicker('setEndDate', maxDate);
                    });

            if(new Date($('#StartDate').val()) != undefined){
                $("#EndDate").datepicker('setStartDate', new Date($('#StartDate').val()))
            }

            var AccountBilling = '{{$AccountBilling}}';

            if(AccountBilling == false){
                $(".billing-section-hide").addClass('panel-collapse');
                $(".billing-section-hide").find('.panel-body').hide();
            }

            $('[name="ServiceBilling"]').on( "change",function(e){
                if($('[name="ServiceBilling"]').prop("checked") == true){
                    $(".billing-section").show();
                    $(".billing-section-hide").nextAll('.panel').attr('data-collapsed',0);
                    $(".billing-section-hide").nextAll('.panel').find('.panel-body').show();
                    $('.billing-section .select2-container').css('visibility','visible');
                }else{
                    $(".billing-section").hide();
                    $(".billing-section-hide").nextAll('.panel').attr('data-collapsed',1);
                    $(".billing-section-hide").nextAll('.panel').find('.panel-body').hide();
                }
            });
            $('[name="ServiceBilling"]').trigger('change');

            var InTariffID = '{{$InboundTariffID}}';
            var OutTariffID = '{{$OutboundTariffID}}';
            var OutDiscountPlanID = '{{$DiscountPlanID}}';
            var InDiscountPlanID = '{{$InboundDiscountPlanID}}';

            if(AccountBilling == false && InTariffID =='' && OutTariffID =='' && OutDiscountPlanID =='' && InDiscountPlanID ==''){
                $(".additional-optional-section-hide").addClass('panel-collapse');
                $(".additional-optional-section-hide").find('.panel-body').hide();
            }


            if(InTariffID =='' && OutTariffID ==''){
                $(".tarrif-section-hide").addClass('panel-collapse');
                $(".tarrif-section-hide").find('.panel-body').hide();
            }

            if(OutDiscountPlanID =='' && InDiscountPlanID ==''){
                $(".discount-section-hide").addClass('panel-collapse');
                $(".discount-section-hide").find('.panel-body').hide();
            }

            $("#save_service").click(function (ev) {
                ev.preventDefault();
                $(this).button('loading');

                var ServiceID = '{{$ServiceID}}';
                var AccountServiceID = '{{$AccountService->AccountServiceID}}';
                //Subscription , Additional charge filter fields should not in account save.
                $('#subscription_filter').find('input').attr("disabled", "disabled");
                $('#oneofcharge_filter').find('input').attr("disabled", "disabled");
                $('#oneofcharge_filter').find('select').attr("disabled", "disabled");

                url= baseurl + '/accountservices/{{$account->AccountID}}/update/'+AccountServiceID;
                var data =$('#service-edit-form').serialize();
                ajax_json(url,data,function(response){
                    $(".btn").button('reset');
                    //Subscription , Additional charge filter fields to enable again.
                    $('#subscription_filter').find('input').removeAttr("disabled");
                    $('#oneofcharge_filter').find('input').removeAttr("disabled");
                    $('#oneofcharge_filter').find('select').removeAttr("disabled");

                    if(response.status =='success'){
                        toastr.success(response.message, "Success", toastr_opts);
                        if(BillingChanged) {
                            setTimeout(function () {
                                window.location.reload()
                            }, 1000);
                        }
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                });

            });

            //billing
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
                if(selection=='weekly' || selection=='monthly_anniversary' || selection=='in_specific_days' || selection=='subscription' || selection=='manual'){
                    //nothing
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

            /*Account service breadcum*/
            $('#drp_accountservice_jump').on('change',function(){
                var val = $(this).val();
                if(val!="") {
                    var accountid = '{{$account->AccountID}}';
                    var url ='/accountservices/'+ accountid + '/edit/'+val;
                    window.location.href = baseurl + url;
                }
            });

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
            function hideCancelCollapse(){
                $('.cancelRadio .panel-collapse').removeClass('in');
                $('.cancelRadio .panel-heading').removeClass('active');
                var selected = $('.cancelRadio input[type="radio"]:checked');
                selected.parent().parent()
                        .addClass('active')
                        .parent()
                        .parent()
                        .find('.panel-collapse')
                        .addClass('in');
            }
            $(function(){
                hideCancelCollapse()
            });
            $('input[name="ContractTerm"]').click(function(){
                hideCancelCollapse()
            });

            $('#renewal').click(function(e) {
                e.preventDefault()
                $('#renewal').attr('disabled',true);
                $('.dataTables_processing').css("visibility","visible");
                var AccountServiceID = '{{$AccountService->AccountServiceID}}';
                showAjaxScript(baseurl + '/accountservices/contract_status/'+AccountServiceID, new FormData(($('#add-new-account-service-cancel-contract-form')[0])), function (response) {
                    //console.log(response);
                    $(".btn").button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        $('.dataTables_processing').css("visibility","hidden");
                        setTimeout(function () {
                            window.location.reload()
                        }, 1000);
                        $('#renewal').attr('disabled',false);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                        $('.dataTables_processing').css("visibility","hidden");
                        $('#renewal').attr('disabled',false);
                    }
                });
            });

        });
        function ajax_form_success(response){
            if(typeof response.redirect != 'undefined' && response.redirect != ''){
                window.location = response.redirect;
            }
        }
    </script>
    <style>
        #drp_accountservice_jump{
            border: 0px solid #fff;
            background-color: rgba(255,255,255,0);
            padding: 0px;
        }
        #drp_accountservice_jump option{
            -webkit-appearance: none;
            -moz-appearance: none;
            border: 0px;
        }


    </style>
@stop
@section('footer_ext')
    @parent
    @include('accountdiscountplan.discountplanmodal')
    @include('accountservices.modal')
    @include('accountservices.history')
@stop

@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>

        <a href="{{URL::to('accounts')}}">Account</a>
    </li>
    <li><a href="{{URL::to('accounts/'.$account->AccountID.'/edit')}}">Edit Account({{$account->AccountName}})</a></li>
    <li>
        <a><span>{{accountservice_dropbox($account->AccountID,$ServiceID)}}</span></a>
    </li>
    <li class="active">
        <strong>Account Service</strong>
    </li>
</ol>
<h3>Account Service</h3>
@include('includes.errors')
@include('includes.success')
<p style="text-align: right;">
    @if($account->IsCustomer==1 || $account->IsVendor==1)
        <a href="{{URL::to('accounts/authenticate/'.$account->AccountID.'-'.$ServiceID)}}" class="btn btn-primary btn-sm btn-icon icon-left">
            <i class="entypo-lock"></i>
            Authentication Rule
        </a>
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
                            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This Service Title will appear on the invoice" data-original-title="Service Title">?</span></label>
                        </label>
                        <div class="col-md-4">
                            <input type="text" name="ServiceTitle" value="{{$ServiceTitle}}" class="form-control" id="field-5" placeholder="">
                        </div>

                    </div>
                </div>
            </div>
            <!-- Service Title For Invoice -->

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
                        Tariff
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-md-2 control-label">Inbound Tariff</label>
                        <div class="col-md-4">
                            {{ Form::select('InboundTariffID', $rate_table , $InboundTariffID , array("class"=>"select2")) }}
                        </div>

                        <label class="col-md-2 control-label">Outbound Tariff</label>
                        <div class="col-md-4">
                            {{ Form::select('OutboundTariffID', $rate_table , $OutboundTariffID , array("class"=>"select2")) }}
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
        <div class="panel panel-primary billing-section-hide"   data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Billing
                </div>

                <div class="panel-options">
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
                        <label for="field-1" class="col-md-2 control-label">Billing Cycle - Monthly Anniversary Date</label>
                        <div class="col-md-4">
                            @if($hiden_class != '' && $BillingCycleType =='monthly_anniversary' )
                                <div class="billing_edit_text"> {{$BillingCycleValue}} </div>
                            @endif
                            {{Form::text('BillingCycleValue', ($BillingCycleType =='monthly_anniversary'?$BillingCycleValue:'') ,array("class"=>"form-control datepicker","Placeholder"=>"Anniversary Date" , "data-start-date"=>"" ,"data-date-format"=>"dd-mm-yyyy", "data-end-date"=>"+1w", "data-start-view"=>"2"))}}
                        </div>
                    </div>
                </div>
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
                    <div class="col-md-4">
                        <?php
                        $NextInvoiceDate = isset($AccountBilling->NextInvoiceDate)?$AccountBilling->NextInvoiceDate:''; ?>
                        {{Form::hidden('NextInvoiceDate', $NextInvoiceDate)}}
                        {{$NextInvoiceDate}}
                    </div>
                </div>

            </div>
        </div>
        @include('accountdiscountplan.index')

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

        var AccountBilling = '{{$AccountBilling}}';

        if(AccountBilling == false){
            $(".billing-section-hide").find('.panel-body').hide();
        }

        var InTariffID = '{{$InboundTariffID}}';
        var OutTariffID = '{{$OutboundTariffID}}';
        var OutDiscountPlanID = '{{$DiscountPlanID}}';
        var InDiscountPlanID = '{{$InboundDiscountPlanID}}';

        if(AccountBilling == false && InTariffID =='' && OutTariffID =='' && OutDiscountPlanID =='' && InDiscountPlanID ==''){
            $(".additional-optional-section-hide").find('.panel-body').hide();
        }


        if(InTariffID =='' && OutTariffID ==''){
            $(".tarrif-section-hide").find('.panel-body').hide();
        }

        if(OutDiscountPlanID =='' && InDiscountPlanID ==''){
            $(".discount-section-hide").find('.panel-body').hide();
        }

        $("#save_service").click(function (ev) {
            ev.preventDefault();
            $(this).button('loading');

            var ServiceID = '{{$ServiceID}}';
            //Subscription , Additional charge filter fields should not in account save.
            $('#subscription_filter').find('input').attr("disabled", "disabled");
            $('#oneofcharge_filter').find('input').attr("disabled", "disabled");
            $('#oneofcharge_filter').find('select').attr("disabled", "disabled");

            url= baseurl + '/accountservices/{{$account->AccountID}}/update/'+ServiceID;
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
        });
        $('[name="BillingStartDate"]').on( "change",function(e){
            BillingChanged = true;
        });
        $('[name="BillingCycleValue"]').on( "change",function(e){
            BillingChanged = true;
        });

        $('#billing_edit').on( "click",function(e){
            e.preventDefault();
            $('[name="BillingCycleType"]').removeClass('hidden');
            $('body').find(".billing_options_active").removeClass('hidden');
            $('.billing_edit_text').addClass('hidden');
            $(this).addClass('hidden');
            return false;
        });

        $('select[name="BillingCycleType"]').trigger( "change" );

        /*Account service breadcum*/
        $('#drp_accountservice_jump').on('change',function(){
            var val = $(this).val();
            if(val!="") {
                var accountid = '{{$account->AccountID}}';
                var url ='/accountservices/'+ accountid + '/edit/'+val;
                window.location.href = baseurl + url;
            }
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
@stop
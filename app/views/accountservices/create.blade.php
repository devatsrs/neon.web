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
        <li><a href="{{URL::to('accounts/'.$account->AccountID.'/edit')}}">Account Account({{$account->AccountName}})</a></li>
        <li>
            <a><span></span></a>
        </li>
        <li class="active">
            <strong>Create Service</strong>
        </li>
    </ol>
    <h3>Account Service</h3>
    @include('includes.errors')
    @include('includes.success')
    <p style="text-align: right;">
        @if( User::checkCategoryPermission('AuthenticationRule','View'))
            @if($account->IsCustomer==1 || $account->IsVendor==1)
                
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
            <form id="service-add-form" action="{{url('accountservices/insertservice')}}" method="post" class="form-horizontal form-groups-bordered">
                <input type="hidden" name="accountid" value="{{$AccountID}}">
                <input type="hidden" name="companyid" value="{{$CompanyID}}">
               
               <div class="panel panel-primary " data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Services
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                     <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-md-2 control-label">Select Service
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This Service Title will appear on the invoice" data-original-title="Service Title">?</span>
                            </label>
                            <div class="col-md-4">
                                <select id="serviceid"  name="serviceid" class="form-control">
                                    <option value="">Select</option>
                                    @foreach($allservices as $service)
                                    <option value="{{$service->ServiceID}}">{{$service->ServiceName}}</option>

                                    @endforeach
                                </select>
                            </div>
                     </div>
                 </div>


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
                                <input type="text" name="ServiceTitle" id="servicetitle" value="" class="form-control" id="field-5" placeholder="">
                            </div>
                            <label for="field-1" class="col-md-2 control-label">Show Service Title
                            </label>
                            <div class="col-md-4">
                                <div class="make-switch switch-small">
                                    <input type="checkbox" name="ServiceTitleShow" checked id="serviceshowtitle" value="1">
                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-md-2 control-label">Service Description
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This Service Description will appear on the invoice" data-original-title="Service Description">?</span></label>
                            </label>
                            <div class="col-md-4">
                                <textarea class="form-control" id="servicedesc" name="ServiceDescription" rows="5" placeholder="Description"></textarea>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- Package Section End -->

                <!-- Service subscription billing cycle end-->
                

                        <!-- Account Option start -->

                
                <!-- account options end -->
            </form>
        </div>
    </div>
    
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
    <script>
        $("select#serviceid").change(function(){
        $("form#service-add-form").submit();
         /*var companyid = '{{$AccountID}}';
         var accountid = '{{$CompanyID}}';

         var serviceid = $("#serviceid").val();
         var servicetitle = $('#servicetitle').val();
         var servicedesc = $("#servicedesc").val();
         var servicetitleshow = $("input[id='serviceshowtitle']").attr('checkded');
          return false;
         $.post("{{}}",{}, function(){

         });*/
        });
        
    </script>
@stop

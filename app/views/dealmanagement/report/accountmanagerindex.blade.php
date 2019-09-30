@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate="novalidate" class="form-horizontal form-groups-bordered filter validate" method="post" id="analysis_manager">
                <div class="form-group">
                    <label class="control-label" for="field-1">Start Date</label>
                    <input type="text" name="StartDate"  class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}"/>
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">End Date</label>
                    <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d' )}}" />

                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Currency</label>
                    {{ Form::select('CurrencyID',$currency,$DefaultCurrencyID,array("class"=>"select2")) }}
                    <input type="hidden" name="Admin" value="{{$isAdmin}}">
                    <input type="hidden" name="Admin1" value="{{$isAdmin}}">
                    <input type="hidden" name="chart_type" value="destination">
                </div>
                <div class="form-group">
                    @if(User::is('AccountManager'))
                        <input type="hidden" name="UsersID[]" value="{{User::get_userID()}}">
                    @else
                        <label class="control-label" for="field-1">Account Manager</label>
                        {{Form::select('UsersID[]', $users, '' ,array("class"=>"select2","multiple"=>"multiple"))}}
                    @endif
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')
<br />
<style>
    .small_fld{width:80.6667%;}
</style>
{{--<link rel="stylesheet" type="text/css" href="assets/js/daterangepicker/daterangepicker-bs3.css" />--}}

    <ul class="nav nav-tabs">
        @if(User::checkCategoryPermission('Analysis','Customer') || User::checkCategoryPermission('Analysis','All'))
            <li ><a href="{{ URL::to('/analysis') }}">Customer</a></li>
        @endif
        @if(User::checkCategoryPermission('Analysis','Vendor') || User::checkCategoryPermission('Analysis','All'))
            <li ><a href="{{ URL::to('/vendor_analysis') }}">Vendor</a></li>
        @endif
        @if(User::checkCategoryPermission('Analysis','AccountManager') || User::checkCategoryPermission('Analysis','All'))
            <li class="active"><a href="#">Account Manager</a></li>
        @endif
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="customer" >
            <br>
            <div class="row">
                <div class="col-sm-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="panel-title"><strong>Accounts</strong></div>
                            <div class="panel-options">
                                <div class="btn-group custom_btn_group" data-toggle="buttons">
                                    <label class="btn btn-white active">
                                        <input type="radio" name="ActiveAccount" value="Yes" checked="checked">Active
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="ActiveAccount" value="No" >Inactive
                                    </label>
                                </div>
                                <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                            </div>
                        </div>
                        <div class="panel-body with-table">
                            <table class="table table-bordered table-responsive" id="accounts">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Account Manager</th>
                                    <th>Created Date</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="panel-title"><strong>Leads</strong></div>
                            <div class="panel-options">
                                <div class="btn-group custom_btn_group" data-toggle="buttons">
                                    <label class="btn btn-white active">
                                        <input type="radio" name="ActiveLead" value="Yes" checked="checked">Active
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="ActiveLead" value="No" >Inactive
                                    </label>
                                </div>
                                <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                            </div>
                        </div>
                        <div class="panel-body with-table">
                            <table class="table table-bordered table-responsive" id="leads">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Account Manager</th>
                                    <th>Lead Status</th>
                                    <th>Created Date</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="panel-title"><strong>Revenue </strong> </div>
                            <div class="panel-options">
                                <div class="btn-group custom_btn_group" data-toggle="buttons">
                                    <label class="btn  btn-white active">
                                        <input type="radio" name="RevenueListType" value="Daily" checked="checked">Daily
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="RevenueListType" value="Weekly" >Weekly
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="RevenueListType" value="Monthly" >Monthly
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="RevenueListType" value="Yearly" >Yearly
                                    </label>
                                </div>
                                <div class="btn-group custom_btn_group" data-toggle="buttons">
                                    <label class="btn btn-white active">
                                        <input type="radio" name="RevenueDisplayType" value="Table" checked="checked">Table
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="RevenueDisplayType" value="Chart" >Chart
                                    </label>
                                </div>
                                <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                            </div>
                        </div>
                        <div class="panel-body with-table without-paging">
                            <table class="table table-bordered table-responsive" id="AccountManagerRevenue">
                                <thead>
                                <tr>
                                    <th>Account Manager</th>
                                    <th>Period</th>
                                    <th>Revenue</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                <tfoot>
                                <tr>

                                </tr>
                                </tfoot>
                            </table>
                            <div class="text-center">
                                <div class="bar_chart_revenue hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="panel-title"> <strong>Margin</strong></div>
                            <div class="panel-options">
                                <div class="btn-group custom_btn_group" data-toggle="buttons">
                                    <label class="btn  btn-white active">
                                        <input type="radio" name="MarginListType" value="Daily" checked="checked">Daily
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="MarginListType" value="Weekly" >Weekly
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="MarginListType" value="Monthly" >Monthly
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="MarginListType" value="Yearly" >Yearly
                                    </label>
                                </div>
                                <div class="btn-group custom_btn_group" data-toggle="buttons">
                                    <label class="btn btn-white active">
                                        <input type="radio" name="MarginDisplayType" value="Table" checked="checked">Table
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="MarginDisplayType" value="Chart" >Chart
                                    </label>
                                </div>
                                <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                            </div>
                        </div>
                        <div class="panel-body with-table without-paging">
                            <table class="table table-bordered table-responsive" id="AccountManagerMargin" >
                                <thead>
                                <tr>
                                    <th>Account Manager</th>
                                    <th>Period</th>
                                    <th>Revenue</th>
                                    <th>Margin</th>
                                    <th>Margin(%)</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                <tfoot>
                                <tr>

                                </tr>
                                </tfoot>
                            </table>
                            <div class="text-center">
                                <div class="bar_chart_margin hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="panel-title"> <strong>By Account</strong></div>
                            <div class="panel-options">
                                <div class="btn-group custom_btn_group" data-toggle="buttons">
                                    <label class="btn  btn-white active">
                                        <input type="radio" name="AccountListType" value="Daily" checked="checked">Daily
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="AccountListType" value="Weekly" >Weekly
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="AccountListType" value="Monthly" >Monthly
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="AccountListType" value="Yearly" >Yearly
                                    </label>
                                </div>
                                <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                            </div>
                        </div>
                        <div class="panel-body with-table without-paging">
                            <table class="table table-bordered table-responsive" id="AccountMargin" >
                                <thead>
                                <tr>
                                    <th>Account Manager</th>
                                    <th>Account</th>
                                    <th>Period</th>
                                    <th>Revenue</th>
                                    <th>Margin</th>
                                    <th>Margin(%)</th>
                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                <tfoot>
                                <tr>

                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
    #AccountManagerRevenue_wrapper .row{
        margin-left: 2px;
        margin-right: 0px ;
    }
    #AccountManagerRevenue_wrapper #AccountManagerRevenue_filter{
        margin-right: -15px;
    }

    #AccountManagerMargin_wrapper .row{
        margin-left: 2px;
        margin-right: 0px ;
    }
    #AccountManagerMargin_wrapper #AccountManagerMargin_filter{
        margin-right: -15px;
    }

    #AccountMargin_wrapper .row{
        margin-left: 2px;
        margin-right: 0px ;
    }
    #AccountMargin_wrapper #AccountMargin_filter{
        margin-right: -15px;
    }


</style>
@include('analysis.managerscript')
@stop
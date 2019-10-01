@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate="novalidate" class="form-horizontal form-groups-bordered filter validate" method="post" id="customer_analysis">
                <div class="form-group">
                    <label class="control-label" for="field-1">Start Date</label>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" name="StartDate"  class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d')}}"/>
                        </div>
                        <div class="col-md-6 select_hour">
                            <input type="text" name="StartHour" data-minute-step="30"   data-show-meridian="false" data-default-time="00:00" value="00:00"  data-template="dropdown" class="form-control timepicker">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">End Date</label>
                    <div class="row">
                        <div class="col-sm-6">
                            <input type="text" name="EndDate" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}" data-enddate="{{date('Y-m-d' )}}" />
                        </div>
                        <div class="col-sm-6 select_hour">
                            <input type="text" name="EndHour" data-minute-step="30"   data-show-meridian="false" data-default-time="23:30" value="23:30"   data-template="dropdown" class="form-control timepicker">
                        </div>
                    </div>
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
        @if(User::checkCategoryPermission('DealManagement','Customer') || User::checkCategoryPermission('DealManagement','All'))
            <li class="active"><a href="#">Customer</a></li>
        @endif
        @if(User::checkCategoryPermission('DealManagement','Vendor') || User::checkCategoryPermission('DealManagement','All'))
            <li ><a href="{{ URL::to('dealmanagement/report/vendor') }}">Vendor</a></li>
        @endif
    </ul>
<br>
    <div class="tab-content">
        <div class="tab-pane active" id="customer">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered datatable" id="account_table">
                        <thead>
                        <tr>
                            <th width="20%">Customer</th>
                            <th width="20%">Destination</th>
                            <th width="20%">Destination Break</th>
                            <th width="10%">Prefix</th>
                            <th width="10%">Trunk</th>
                            <th width="10%">No. of Calls</th>
                            <th width="10%">Billed Duration (Min.)</th>
                            <th width="10%">Charged Amount</th>
                            @if((int)Session::get('customer') == 0)
                                <th width="10%">Margin</th>
                                <th width="10%">Margin (%)</th>
                            @endif
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
    <script type="text/javascript">
        jQuery(document).ready(function() {
            $('#filter-button-toggle').show();
        });
    </script>

@include('dealmanagement.report.script')
@stop
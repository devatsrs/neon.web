@extends('layout.main')
@section('content')
<br />
 @if(User::is_admin())
<div class="row">    
    <div class="tab-content">
        <div class="tab-pane active" id="customer" >
            <div class="col-md-12">
                <form novalidate class="form-horizontal form-groups-bordered filter validate" method="post" id="crm_dashboard">
                    <div data-collapsed="0" class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Filter
                            </div>
                            <div class="panel-options">
                                <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">                            
                            <div class="form-group">
                          <label class="col-sm-1 control-label" for="field-1">User</label>
                                    <div class="col-sm-2">
                                       {{Form::select('UsersID',$users,'',array("class"=>"select2"))}}
                                    </div>
                                    <label class="col-sm-1 control-label" for="field-1">Currency</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('CurrencyID',$currency,$DefaultCurrencyID,array("class"=>"select2")) }}
                                    </div>
                            </div>
                            <p style="text-align: right;">
                                <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                                    <i class="entypo-search"></i>
                                    Search
                                </button>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<form novalidate class="form-horizontal form-groups-bordered filter validate" method="post" id="crm_dashboard">
  <input type="hidden" name="CurrencyID" value="{{$CurrencyID}}">
  <input type="hidden" name="UsersID" value="{{User::get_userID()}}">
</form>
@endif


<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h1>My Pipeline Summary</h1>      
                    <div class="PipeLineResult"></div>              
                </div> 
                  <div id="Pipeline" class="panel-options">
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
                    <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                </div>               
            </div>
            
            <div class="panel-body">
            <div class="text-center">
                <div id="crmdpipeline1" class="crmdpipeline"></div>
            </div>
        </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>My Active Tasks ()</h3>
                    
                </div>

                <div id="UsersTasks" class="panel-options">
{{ Form::select('CompanyGatewayID', array("All"=>"All","duetoday"=>"Due Today","duesoon"=>"Due Soon","overdue"=>"Overdue"), 'duetoday', array('id'=>'TaskType','class'=>'select_gray')) }}
                    <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a>
                    <a data-rel="close" href="#"><i class="entypo-cancel"></i></a>
                </div>
            </div>
            <div class="panel-body" style="max-height: 450px; overflow-y: auto; overflow-x: hidden;">
                <table id="UsersTasksTable"  class="table table-responsive">
                    <thead>
                    <tr>
                        <th width="25%">Subject</th>
                        <th width="25%">Status</th>
                        <th width="25%">Due Date</th>
                        <th width="25%">Related To</th>
                    </tr>

                    </thead>

                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="{{ URL::asset('assets/js/reports_crm.js') }}"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script>
        var $searchFilter = {};
        var chart_type = '#destination';
        jQuery(document).ready(function ($) {

            $("#crm_dashboard").submit(function(e) {
                e.preventDefault();
                public_vars.$body = $("body");
                //show_loading_bar(40);
                set_search_parameter($(this));
                reloadCrmCharts('{{Config::get('app.pageSize')}}',$searchFilter);
                return false;
            });
            set_search_parameter($("#crm_dashboard"));
            Highcharts.theme = {
                colors: ['#3366cc', '#ff9900' ,'#dc3912' , '#109618', '#66aa00', '#dd4477','#0099c6', '#990099', '#143DFF']
            };
            // Apply the theme
            Highcharts.setOptions(Highcharts.theme);
            reloadCrmCharts('{{Config::get('app.pageSize')}}',$searchFilter);
        });
    </script>
 @stop
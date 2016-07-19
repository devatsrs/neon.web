@extends('layout.main')
@section('content')
<div class="row">
  <div class="tab-content">
    <div class="tab-pane active" id="customer" >
      <div class="col-md-12">
        <form novalidate class="form-horizontal form-groups-bordered filter validate" method="post" id="crm_dashboard">
          <div data-collapsed="0" class="panel panel-primary">
            <div class="panel-heading">
              <div class="panel-title"> Filter </div>
              <div class="panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> </div>
            </div>
            <div class="panel-body">
              <div class="form-group">
              @if(User::is_admin())
                <label class="col-sm-1 control-label" for="field-1">User</label>
                <div class="col-sm-6"> {{Form::select('UsersID[]', $users, '' ,array("class"=>"select2","multiple"=>"multiple"))}} </div>
                @else
                <input type="hidden" name="UsersID[]" value="{{User::get_userID()}}">
                @endif
                <label class="col-sm-1 control-label" for="field-1">Currency</label>
                <div class="col-sm-2"> {{ Form::select('CurrencyID',$currency,$DefaultCurrencyID,array("class"=>"select2")) }} </div>
              </div>
              <p style="text-align: right;">
                <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit"> <i class="entypo-search"></i> Search </button>
              </p>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="row">
<div class="col-sm-12">
    <div class="panel panel-primary panel-table">
      <div class="panel-heading">
        <div class="panel-title">
          <h1>Pipeline Summary</h1>
          <div class="PipeLineResult"></div>
        </div>
        <div id="Pipeline" class="panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a> </div>
      </div>
      <div class="panel-body">
        <div class="text-center">
          <div id="crmdpipeline1" class="crmdpipeline"></div>
        </div>
      </div>
    </div>
  </div>
<div class="col-md-12">
    <div class="panel panel-primary panel-table">
      <div class="panel-heading">
        <div id="Forecast" class="pull-right panel-box panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a></div>
        <div class="panel-title forecase_title">
          <h1>Forecast</h1>
          <div class="forecastResult"></div>
        </div>
        <div id="Forecast" class="clear clearfix">
          <div class="form_forecast">
            <form novalidate class="form-horizontal form-groups-bordered"  id="crm_dashboard_forecast">
              <div class="form-group form-group-border-none">
              <div class="pull-left small-input first">
                <label class="control-label">Close Date</label>
              </div>
                <div class="col-sm-4">                  
                  <input value="{{$StartDateDefault}} - {{$DateEndDefault}}" type="text" id="Closingdate"  data-format="YYYY-MM-DD"  name="Closingdate" class=" daterange form-control">                  
                  
                </div> 
              </div>
              
              <!-- -->
              <div class="form-group form-group-padding-none">
                <ul class="icheck-list">
                  <li>
                    <div class="pull-left small-input first status">
                      <label class="control-label" >Status</label>
                    </div>
                    <div class="radio radio-replace color-blue pull-left">
                      <input class="icheck-11 statusCheckbox" type="checkbox" id="minimal-radio-4" name="Status_{{Opportunity::$status[Opportunity::Abandoned]}}" value="{{Opportunity::Abandoned}}">
                      <label for="minimal-radio-4">{{Opportunity::$status[Opportunity::Abandoned]}}</label>
                    </div>
                    <div class="radio radio-replace color-red pull-left">
                      <input class="icheck-11 statusCheckbox" type="checkbox" id="minimal-radio-3" name="Status_{{Opportunity::$status[Opportunity::Lost]}}" value="{{Opportunity::Lost}}">
                      <label for="minimal-radio-3">{{Opportunity::$status[Opportunity::Lost]}}</label>
                      &nbsp;&nbsp;</div>
                    <div class="radio radio-replace color-purple pull-left">
                      <input class="icheck-11 statusCheckbox" type="checkbox" id="minimal-radio-2" name="Status_{{Opportunity::$status[Opportunity::Open]}}" value="{{Opportunity::Open}}">
                      <label for="minimal-radio-2">{{Opportunity::$status[Opportunity::Open]}}</label>
                      &nbsp;&nbsp;</div>
                    <div class="radio radio-replace color-green pull-left">
                      <input class="icheck-11 statusCheckbox" type="checkbox" id="minimal-radio-1" name="Status_{{Opportunity::$status[Opportunity::Won]}}" value="{{Opportunity::Won}}" checked>
                      <label for="minimal-radio-1">{{Opportunity::$status[Opportunity::Won]}}</label>
                      &nbsp;&nbsp;</div>
                  </li>
                </ul>
                <div class="pull-left">
                  <button type="submit" id="submit_forecast" class="btn btn-sm btn-primary"><i class="entypo-search"></i></button>
                </div>
              </div>
              <!-- -->
              
              <div class="text-center">
                <div id="crmdforecast1" class="crmdforecast"></div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="panel-body forecast-body"> </div>
  </div>
  
</div>
<div class="clear clearfix margin-bottom"></div>
<div class="row">
  <div class="col-sm-12">
    <div class="panel panel-primary panel-table">
      <div class="panel-heading">
        <div class="panel-title">
          <h3>Active Tasks ()</h3>
        </div>
        <div id="UsersTasks" class="panel-options"> {{ Form::select('CompanyGatewayID', array("All"=>"All","duetoday"=>"Due Today","duesoon"=>"Due Soon","overdue"=>"Overdue"), 'duetoday', array('id'=>'TaskType','class'=>'select_gray')) }} <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a> </div>
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
<script src="{{ URL::asset('assets/js/daterangepicker/moment.min.js') }}"></script> 
<script src="{{ URL::asset('assets/js/daterangepicker/daterangepicker.js') }}"></script> 
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
<style>
.padding-none{
	padding:0px !important;
	margin:0px !important;
}
.small-input{ margin-right: 5px;}
#submit_forecast{margin-left:5px;}
#crm_dashboard_forecast .first{margin-left:5px;}
#crm_dashboard_forecast .status{width:7%;}
#crm_dashboard_forecast .dash{width:2%; margin-left:2px; margin-top:2px;}
.form_forecast{ margin-left:30px;}
.forecase_title{padding:10px 15px !important;}
.form-group-border-none{border-bottom:none !important; padding-bottom:0px !important;}
.form-group-padding-none{padding-top:6px !important;}
.radio-replace{margin-right:3px;}
</style>
@stop
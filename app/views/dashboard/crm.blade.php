@extends('layout.main')
@section('content')
<div class="row">
  <div class="tab-content">
    <div class="tab-pane active" id="customer" >
      <div class="col-md-12">
        <form novalidate class="form-horizontal form-groups-bordered filter validate" method="post" id="crm_dashboard">
          <div data-collapsed="0" class="panel panel-primary">
            <div class="panel-heading">
              <div class="panel-title">
                <h3> Filter</h3>
              </div>
              <div class="panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> </div>
            </div>
            <div class="panel-body">
              <div class="form-group"> @if(User::is_admin())
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
          <h3>Active Tasks</h3>
        </div>
        <div id="UsersTasks" class="panel-options"> {{ Form::select('DueDateFilter', array("All"=>"All","duetoday"=>"Due Today","duesoon"=>"Due Soon","overdue"=>"Overdue"), 'All', array('id'=>'DueDateFilter','class'=>'select_gray')) }} <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a> </div>
      </div>
      <div class="panel-body" style="max-height: 450px; overflow-y: auto; overflow-x: hidden;">
        <table class="table table-bordered datatable" id="taskGrid">
          <thead>
            <tr>
              <th width="30%" >Subject</th>
              <th width="10%" >Due Date</th>
              <th width="10%" >Status</th>
              <th width="20%">Assigned To</th>
              <th width="20%">Related To</th>
              <th width="10%">Action</th>
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
  <div class="col-md-6">
    <div class="panel panel-primary panel-table">
      <div class="panel-heading">
        <div id="Sales" class="pull-right panel-box panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a></div>
        <div class="panel-title forecase_title">
          <h3>Sales</h3>
          <div class="SalesResult"></div>
        </div>
        <div  class="clear clearfix">
          <div class="form_Sales">
            <form novalidate class="form-horizontal form-groups-bordered"  id="crm_dashboard_Sales">
              <div class="form-group form-group-border-none">
                <label for="Closingdate" class="col-sm-2 control-label ClosingdateLabel ">Close Date</label>
                <div class="col-sm-6">
                  <input value="{{$StartDateDefault}} - {{$DateEndDefault}}" type="text" id="Closingdate"  data-format="YYYY-MM-DD"  name="Closingdate" class="small-date-input daterange">
                </div>
              </div>
              <div class="form-group form-group-padding-none">
                <label for="field-1" class="col-sm-2 control-label StatusLabel">Status</label>
                <div class="col-sm-8 Statusdiv"> {{Form::select('Status[]', Opportunity::$status, Opportunity::Won ,array("class"=>"select2","multiple"=>"multiple"))}} </div>
                <button type="submit" id="submit_Sales" class="btn btn-sm btn-primary"><i class="entypo-search"></i></button>
              </div>
              <div class="text-center">
                <div id="crmdSales1" style="min-width: 310px; height: 400px; margin: 0 auto" class="crmdSales"></div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="panel panel-primary panel-table">
      <div class="panel-heading">
        <div class="panel-title">
          <h3>Pipeline Summary</h3>
          <div class="PipeLineResult"></div>
        </div>
        <div id="Pipeline" class="panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a> </div>
      </div>
      <div class="panel-body white-bg">
        <div class="text-center">
          <div id="crmdpipeline1" style="min-width: 310px; height: 400px; margin: 0 auto" class="crmdpipeline"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-primary panel-table">
      <div class="panel-heading">
        <div id="Forecast" class="pull-right panel-box panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a></div>
        <div class="panel-title forecase_title">
          <h3>Forecast</h3>
          <div class="ForecastResult"></div>
        </div>
        <div  class="clear clearfix">
          <div class="form_Forecast">
            <form novalidate class="form-horizontal form-groups-bordered"  id="crm_dashboard_Forecast">
              <div class="form-group form-group-border-none">
                <label for="ClosingdateFortcast" class="col-sm-2 control-label ClosingdateLabel1 ">Close Date</label>
                <div class="col-sm-2" style="width:180px;">
                  <input value="{{$DateEndDefault}} - {{$StartDateDefaultforcast}}" type="text" id="ClosingdateFortcast"  data-format="YYYY-MM-DD"  name="Closingdate" class=" daterange small-date-input">
                </div>
                <button type="submit" id="submit_Forecast" class="btn btn-sm btn-primary"><i class="entypo-search"></i></button>
              </div>
              <div class="text-center">
                <div id="crmdForecast1" style="min-width: 310px; height: 400px; margin: 0 auto" class="crmdForecast"></div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
<div class="col-sm-12">
    <div class="panel panel-primary panel-table">
      <div class="panel-heading">
        <div id="Sales_Manager" class="pull-right panel-box panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a></div>
        <div class="panel-title forecase_title">
          <h3>LeaderShip Chart</h3>
          <div class="SalesResultManager"></div>
        </div>
        <div  class="clear clearfix">
          <div class="form_Sales">
            <form novalidate class="form-horizontal form-groups-bordered"  id="crm_dashboard_Sales_Manager">
              <div class="form-group form-group-border-none">
                <label for="Closingdate" class="col-sm-2 control-label managerLabel ">Date</label>
                <div class="col-sm-6">
                  <input value="{{$StartDateDefault}} - {{$DateEndDefault}}" type="text" id="Closingdate"  data-format="YYYY-MM-DD"  name="Closingdate" class="small-date-input daterange"> 
                  <button type="submit" id="submit_Sales" class="btn btn-sm btn-primary"><i class="entypo-search"></i></button>
                </div>
              </div>
              <div class="text-center">
                <div id="crmdSalesManager1" style="min-width: 310px; height: 400px; margin: 0 auto" class="crmdSalesManager1"></div>
              </div>
            </form>
          </div>
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
          <h3>Open Opportunities</h3>
        </div>
        <div id="UsersOpportunities" class="panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> <a data-rel="reload" href="#"><i class="entypo-arrows-ccw"></i></a> <a data-rel="close" href="#"><i class="entypo-cancel"></i></a> </div>
      </div>
      <div class="panel-body">
        <table class="table table-bordered datatable" id="opportunityGrid">
          <thead>
            <tr>
              <th width="25%" >Name</th>
              <th width="5%" >Status</th>
              <th width="20%">Assigned To</th>
              <th width="20%">Related To</th>
              <th width="10%" >Expected Close Date</th>
              <th width="5%" >Value</th>
              <th width="5%" >Rating</th>
              <th width="10%">Action</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
        <div class="blockUI blockOverlay loaderopportunites" style="z-index: 1000; border: medium none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background-color: rgb(255, 255, 255); cursor: wait; position: absolute; opacity: 0.3;">
      </div>
    </div>
  </div>
</div>
</div>
<div class="salestable_div"> </div>
<script>
var pageSize = '{{Config::get('app.pageSize')}}';
@if(User::checkCategoryPermission('Task','Edit')) 
var task_edit = 1;
@else 
var task_edit = 0;
@endif;

@if(User::checkCategoryPermission('Opportunity','Edit')) 
var Opportunity_edit = 1;
@else 
var Opportunity_edit = 0;
@endif;
var TaskBoardID = '{{$TaskBoard[0]->BoardID}}';

var opportunitystatus = JSON.parse('{{json_encode(Opportunity::$status)}}');
var OpportunityClose =  '{{Opportunity::Close}}';
</script> 
<script src="https://code.highcharts.com/highcharts.js"></script> 
<script src="{{ URL::asset('assets/js/reports_crm.js') }}"></script> 
<style>
#taskGrid > tbody > tr:hover,#opportunityGrid  > tbody > tr:hover{background:#ccc; cursor:pointer;} 
#taskGrid > thead >tr > th:last-child,#opportunityGrid > thead >tr > th:last-child{display:none;}
#taskGrid > tbody >tr > td:last-child,#opportunityGrid > tbody >tr > td:last-child{display:none;}
.padding-none{padding:0px !important;margin:0px !important;}
.small-input{ margin-right: 5px;}
#submit_Sales,#submit_Forecast{margin-left:5px;}
#crm_dashboard_Sales .first,#crm_dashboard_Forecast .first{margin-left:5px;}
#crm_dashboard_Sales .status, #crm_dashboard_Forecast .status{width:7%;}
#crm_dashboard_Sales .dash, #crm_dashboard_Forecast .dash {width:2%; margin-left:2px; margin-top:2px;}
.form_Sales, .form_Forecast{ margin-left:30px;}
/*.forecase_title{padding:10px 15px !important;}*/
.forecase_title{padding-bottom:10px !important;}
.form-group-border-none{border-bottom:none !important; padding-bottom:0px !important;}
.form-group-padding-none{padding-top:6px !important; padding-bottom:6px !important;}
.radio-replace{margin-right:3px;}
    .file-input-wrapper{
        height: 26px;
    }

    .margin-top{
        margin-top:10px;
    }
    .margin-top-group{
        margin-top:15px;
    }
    .paddingleft-0{
        padding-left: 3px;
    }
    .paddingright-0{
        padding-right: 0px;
    }
    #add-modal-opportunity .btn-xs{
        padding:0px;
    }
    .resizevertical{
        resize:vertical;
    }

    .file-input-names span{
        cursor: pointer;
    }

    .WorthBox{display:none;}
    .oppertunityworth{
        border-radius:5px;
        border:2px solid #ccc;
        background:#fff;
        padding:0 0 0 6px;
        margin-bottom:10px;
        width:60%;
        font-weight:bold;
    }
	.ClosingdateLabel{
		padding-left:0;
		padding-right:0;
		width:72px;
	}
	.ClosingdateLabel1{
		padding-left:0;
		padding-right:0;
		width:72px;
	}
	
	.StatusLabel{
		padding-left:0;
		padding-right:0;
		width:9%;
	}
	.Statusdiv{
	margin-left:25px;
	}
	.small-date-input
	{
		width:150px;
	}
	.white-bg{background:#fff none repeat scroll 0 0 !important; }
	.managerLabel{
		padding-left:0;
		padding-right:0;
		width:38px;
	}
</style>
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="edit-modal-opportunity">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="edit-opportunity-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Add New Opportunity</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 text-left">
              <label for="field-5" class="control-label col-sm-2">Tag User</label>
              <div class="col-sm-10">
                <?php unset($account_owners['']); ?>
                {{Form::select('TaggedUsers[]',$account_owners,[],array("class"=>"select2","multiple"=>"multiple"))}} </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Account Owner *</label>
                <div class="col-sm-8"> {{Form::select('UserID',$account_owners,'',array("class"=>"select2"))}} </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Opportunity Name *</label>
                <div class="col-sm-8">
                  <input type="text" name="OpportunityName" class="form-control" id="field-5" placeholder="">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label for="input-1" class="control-label col-sm-4">Rate This</label>
                <div class="col-sm-8">
                  <input type="text" class="knob" data-min="0" data-max="5" data-width="85" data-height="85" name="Rating" value="0" />
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top-group pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">First Name*</label>
                <div class="col-sm-8">
                  <div class="input-group" style="width: 100%;">
                    <div class="input-group-addon" style="padding: 0px; width: 85px;">
                      <?php $NamePrefix_array = array( ""=>"-None-" ,"Mr"=>"Mr", "Miss"=>"Miss" , "Mrs"=>"Mrs" ); ?>
                      {{Form::select('Title', $NamePrefix_array, '' ,array("class"=>"selectboxit"))}} </div>
                    <input type="text" name="FirstName" class="form-control" id="field-5">
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Last Name*</label>
                <div class="col-sm-8">
                  <input type="text" name="LastName" class="form-control" id="field-5">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Company*</label>
                <div class="col-sm-8">
                  <input type="text" name="Company" class="form-control" id="field-5">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Phone Number</label>
                <div class="col-sm-8">
                  <input type="text" name="Phone" class="form-control" id="field-5">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Email Address*</label>
                <div class="col-sm-8">
                  <input type="text" name="Email" class="form-control" id="field-5">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top-group pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Status</label>
                <div class="col-sm-8 input-group"> {{Form::select('Status', Opportunity::$status, '' ,array("class"=>"selectboxit"))}} </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Select Board*</label>
                <div class="col-sm-8"> {{Form::select('BoardID',$boards,'',array("class"=>"selectboxit"))}} </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Tags</label>
                <div class="col-sm-8 input-group">
                  <input class="form-control opportunitytags" name="Tags" type="text" >
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top-group pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Value</label>
                <div class="col-sm-8">
                  <input class="form-control" value="0" name="Worth" type="number" step="any" min=”0″>
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Expected Close Date</label>
                <div class="col-sm-8">
                  <input autocomplete="off" type="text" name="ExpectedClosing" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top-group pull-left">
              <div class="form-group">
                <label class="col-sm-4 control-label">Close</label>
                <div class="col-sm-3 make">
                  <p class="make-switch switch-small">
                  </p>
                    <input name="opportunityClosed" type="checkbox" value="{{Opportunity::Close}}">
                </div>
                <label class="col-sm-2 control-label closedDate hidden">Closed Date</label>
                <div class="col-sm-3"> <span id="closedDate"></span> </div>
              </div>
            </div>
            
            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Actual Close Date</label>
                                    <div class="col-sm-8">
                                        <input autocomplete="off" id="ClosingDate" type="text" name="ClosingDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                                    </div>
                                </div>
                            </div>
            
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="OpportunityID">
          <button type="submit" id="opportunity-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="edit-modal-task">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="edit-task-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Add New Task</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12 text-left">
              <label for="field-5" class="control-label col-sm-2">Tag User</label>
              <div class="col-sm-10" style="padding: 0px 10px;">
                <?php unset($account_owners['']); ?>
                {{Form::select('TaggedUsers[]',$account_owners,[],array("class"=>"select2","multiple"=>"multiple"))}} </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Task Status *</label>
                <div class="col-sm-8"> {{Form::select('TaskStatus',$taskStatus,'',array("class"=>"selectboxit"))}} </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Assign To*</label>
                <div class="col-sm-8"> {{Form::select('UsersIDs',$account_owners,'',array("class"=>"select2"))}} </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Task Subject *</label>
                <div class="col-sm-8">
                  <input type="text" name="Subject" class="form-control" id="field-5" placeholder="">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Due Date</label>
                <div class="col-sm-5">
                  <input autocomplete="off" type="text" name="DueDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                </div>
                <div class="col-sm-3">
                  <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="23:59:59" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-4">Company</label>
                <div class="col-sm-8"> {{Form::select('AccountIDs',$leadOrAccount,'',array("class"=>"select2"))}} </div>
              </div>
            </div>
            <div class="col-md-6 margin-top pull-right">
              <div class="form-group">
                <label class="col-sm-4 control-label">Priority</label>
                <div class="col-sm-3 make"> <span class="make-switch switch-small">
                  <input name="Priority" value="1" type="checkbox">
                  </span> </div>
                <label class="col-sm-2 control-label">Close</label>
                <div class="col-sm-3 taskClosed">
                  <p class="make-switch switch-small">
                    <input name="taskClosed" type="checkbox" value="{{Task::Close}}">
                  </p>
                </div>
              </div>
            </div>
            <div class="col-md-12 margin-top pull-left">
              <div class="form-group">
                <label for="field-5" class="control-label col-sm-2">Description</label>
                <div class="col-sm-10">
                  <textarea name="Description" class="form-control textarea autogrow resizevertical"> </textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="TaskID">
          <button type="submit" id="task-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop
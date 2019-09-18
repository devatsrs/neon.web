@extends('layout.main')

@section('content')

  <ol class="breadcrumb bc-3">
    <li>
      <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>

      <a href="{{URL::to('dealmanagement')}}">Deal Management</a>
    </li>
    <li class="active">
      <strong>New Deal</strong>
    </li>
  </ol>
  <h3>New Deal</h3>
  @include('includes.errors')
  @include('includes.success')

  <p style="text-align: right;">
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..." id="save_deal">
      <i class="entypo-floppy"></i>
      Save
    </button>

    <a href="{{URL::to('/dealmanagement')}}" class="btn btn-danger btn-sm btn-icon icon-left">
      <i class="entypo-cancel"></i>
      Close
    </a>
  </p>
  <br>
  <div class="row">
    <div class="col-md-12">
      <form role="form" id="deal-from" method="post" action="{{URL::to('dealmanagement/store')}}" class="form-horizontal form-groups-bordered">

        <div class="panel panel-primary" data-collapsed="0">
          <div class="panel-body">
            <div class="form-group">
              <label class="col-md-2 control-label">Title*</label>
              <div class="col-md-4">
                <input type="text" name="Title" class="form-control" id="field-1" placeholder="" value="" />
              </div>
              <label class="col-md-2 control-label">Deal Type*</label>
              <div class="col-md-4">
                {{Form::select('DealType',Deal::$TypeDropDown, 'Revenue',array("class"=>"select2"))}}
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-2 control-label">Account*</label>
              <div class="col-md-4">
                {{Form::select('AccountID',$Accounts,'',array("class"=>"select2"))}}
              </div>
              <label class="col-md-2 control-label">Codedeck*</label>
              <div class="col-md-4">
                {{Form::select('CodedeckID',$codedecklist,'',array("class"=>"select2"))}}
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-2 control-label">Status*</label>
              <div class="col-md-4">
                {{Form::select('Status',Deal::$StatusDropDown, 'Active',array("class"=>"select2"))}}
              </div>
              <label class="col-md-2 control-label">Alert Email</label>
              <div class="col-md-4">
                <input type="text" name="AlertEmail" class="form-control" id="field-1" placeholder="" value="" />
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-2 control-label">Start Date*</label>
              <div class="col-md-4">
                {{ Form::text('StartDate', '', array("class"=>"form-control small-date-input datepicker", 'id' => 'StartDate', "data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
              </div>
              <label class="col-md-2 control-label">End Date*</label>
              <div class="col-md-4">
                {{ Form::text('EndDate', '', array("class"=>"form-control small-date-input datepicker", 'id' => 'EndDate',"data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
              </div>
            </div>
          </div>
          <div class="panel panel-primary hide" data-collapsed="0">
            <div class="panel-heading">
              <div class="panel-title">
                Deal Detail
              </div>

              <div class="panel-options">
                <button type="button" onclick="addDeal()" class="btn btn-primary btn-xs add-deal" data-loading-text="Loading...">
                  <i></i>
                  +
                </button>
                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
              </div>
            </div>
            <div class="panel-body">
              <table class="table table-bordered dealTable" id="table-4">
                <thead>
                <tr class="revenueRow">
                  <th style="width: 13%">Type</th>
                  <th style="width: 12%">Destination</th>
                  <th style="width: 12%">Trunk</th>
                  <th style="width: 10%">Revenue</th>
                  <th style="width: 9%">Sale Price</th>
                  <th style="width: 9%">Buy Price</th>
                  <th style="width: 9%">(Profit/Loss) per min</th>
                  <th style="width: 10%">Minutes</th>
                  <th style="width: 10%">Profit/Loss</th>
                  <th style="width: 5%">Action</th>
                </tr>
                <tr class="paymentRow">
                  <th style="width: 13%">Type</th>
                  <th style="width: 12%">Destination</th>
                  <th style="width: 12%">Trunk</th>
                  <th style="width: 10%">Minutes</th>
                  <th style="width: 9%">Sale Price</th>
                  <th style="width: 9%">Buy Price</th>
                  <th style="width: 9%">(Profit/Loss) per min</th>
                  <th style="width: 10%">Revenue</th>
                  <th style="width: 10%">Profit/Loss</th>
                  <th style="width: 5%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                <tr>
                  <th colspan="7"></th>
                  <th>Total</th>
                  <th class="pl-grand">0</th>
                  <input type="hidden" name="TotalPL" value="0">
                </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="panel panel-primary hide" data-collapsed="0">
            <div class="panel-heading">
              <div class="panel-title">
                Notes
              </div>

              <div class="panel-options">
                <button type="button" onclick="addNote()" class="btn btn-primary btn-xs add-note" data-loading-text="Loading...">
                  <i></i>
                  +
                </button>
                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
              </div>
            </div>
            <div class="panel-body">
              <table class="table table-bordered noteTable" id="table-4">
                <thead>
                <tr>
                  <th style="width: 65%">Note</th>
                  <th style="width: 15%">Created By</th>
                  <th style="width: 15%">Created At</th>
                  <th style="width: 5%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  @include('dealmanagement.add_edit_script')
  @include('includes.ajax_submit_script', array('formID'=>'deal-from' , 'url' => 'dealmanagement/store','update_url'=>'dealmanagement/update/{id}' ))
@stop
@section('footer_ext')
  @parent
@stop
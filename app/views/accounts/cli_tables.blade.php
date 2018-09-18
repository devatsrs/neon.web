<style>
    #selectcheckbox{
        padding: 15px 10px;
    }
</style>
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            CLI
        </div>

        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>

    <div class="panel-body">
        <div id="clitable_filter" method="get" action="#" >
            <div class="panel panel-primary panel-collapse" data-collapsed="1">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body" style="display: none;">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-1 control-label">CLI</label>
                        <div class="col-sm-2">
                            <input type="text" name="CLIName" class="form-control" value="" />
                        </div>
                        <div class="col-sm-9">
                            <p style="text-align: right;">
                                <button class="btn btn-primary btn-sm btn-icon icon-left" id="clitable_submit">
                                    <i class="entypo-search"></i>
                                    Search
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="pull-right">
                    @if( User::checkCategoryPermission('AuthenticationRule','Add,Delete'))
                        <button type="button" class="btn btn-primary btn-sm  dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false">Action <span class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu"
                            style="background-color: #000; border-color: #000; margin-top:0px;">
                            @if( User::checkCategoryPermission('AuthenticationRule','Add'))
                                <li>
                                    <a class="create" id="add-clitable" href="javascript:;">
                                        <i class="entypo-plus"></i>
                                        Add
                                    </a>
                                </li>
                            @endif
                            @if( User::checkCategoryPermission('AuthenticationRule','Delete'))
                                <li>
                                    <a class="generate_rate create" id="bulk-delete-cli" href="javascript:;"
                                       style="width:100%">
                                        <i class="entypo-trash"></i>
                                        Delete
                                    </a>
                                </li>
                            @endif
                            @if( User::checkCategoryPermission('AuthenticationRule','Add'))
                                <li>
                                    <a class="generate_rate create" id="changeSelectedCLI" href="javascript:;">
                                        <i class="entypo-pencil"></i>
                                        Change RateTable
                                    </a>
                                </li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        </br>
        <table id="table-clitable" class="table table-bordered datatable table-clitable">
            <thead>
            <tr>
                <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                <th width="30%">CLI</th>
                <th width="35%">Rate Table</th>
                <th width="20%">Service</th>
                <th width="30%">Action</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

    </div>
</div>
<script type="text/javascript">
var AccountID = '{{$account->AccountID}}';
var ServiceID='{{$ServiceID}}';
</script>
<script src="{{ URL::asset('assets/js/clitable.js') }}"></script>
@section('footer_ext')
    @parent

    <div class="modal fade in" id="modal-clitable">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="clitable-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">CLI</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row edit_hide">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">CLI</label>
                                    <textarea name="CLI" class="form-control autogrow"></textarea>
                                    *Adding multiple CLIs ,Add one CLI in each line.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">RateTable
                                        <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Select Rate Table to rate Inboud Calls based on origination no" data-original-title="Rate Table">?</span></label>
                                    </label>
                                    {{ Form::select('RateTableID', $rate_table , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="AccountID" value="{{$account->AccountID}}">
                    <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
                    <input type="hidden" name="CLIRateTableIDs" value="">
                    <input type="hidden" name="CLIRateTableID" value="">
                    <input type="hidden" name="AuthRule" value="{{$AuthRule or ''}}">
                    <input type="hidden" name="criteria" value="">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
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
    <div class="modal fade" id="confirm-modal" >
        <div class="modal-dialog">
            <div class="modal-content">
                <form role="form" id="form-confirm-modal" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Delete CLI</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-sm-3">Date</label>
                                    <div class="col-sm-9">
                                        <input type="text" value="{{date('Y-m-d')}}" name="Closingdate" id="Closingdate" class="form-control datepicker" data-date-format="yyyy-mm-dd" placeholder="">
                                    </div>
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">This is the date when you deleted CLI against this account from the switch</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" data-loading-text = "Loading..."  class="btn btn-primary btn-sm btn-icon icon-left" id="delete-clidate">
                            <i class="entypo-floppy"></i>
                            Delete
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
@stop



@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="opportunityboard_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Board Name</label>
                    {{ Form::text('BoardName', '', array("class"=>"form-control")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Active</label>
                    <?php $active = [""=>"Both","1"=>"Active","0"=>"Inactive"]; ?>
                    {{ Form::select('Active', $active, '1', array("class"=>"form-control select2 small")) }}
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
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Opportunity Board</strong>
        </li>
    </ol>
    <h3>Opportunity Boards</h3>
    <div class="tab-content">
        <div class="tab-pane active">
            <p style="text-align: right;">
                @if(User::checkCategoryPermission('OpportunityBoard','Add'))
                <a href="javascript:void(0)" id="add-new-opportunityboard" class="btn btn-primary ">
                    <i class="fa fa-line-chart"></i>
                    Add New
                </a>
                @endif
            </p>
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th width="30%">Board Name</th>
                    <th width="10%">Active</th>
                    <th width="10%">Created By</th>
                    <th width="20%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var list_fields  = ['BoardName' ,'Status','CreatedBy','BoardID'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                var opportunityBoard = '{{CRMBoard::OpportunityBoard}}';
                var taskBoard = '{{CRMBoard::TaskBoard}}';
                jQuery(document).ready(function ($) {

                    $('#filter-button-toggle').show();

                    public_vars.$body = $("body");
                    $searchFilter.BoardName = $("#opportunityboard_filter [name='BoardName']").val();
                    $searchFilter.Active = $("#opportunityboard_filter select[name='Active']").val();
                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/opportunityboards/ajax_datagrid",
                        "fnServerParams": function (aoData) {
                            aoData.push({ "name": "BoardName", "value": $searchFilter.BoardName },
                                    { "name": "Active", "value": $searchFilter.Active });
                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[0, 'asc']],
                        "aoColumns": [
                            {  "bSortable": true ,
                                mRender:function(val,type,full){
                                    var manage =baseurl + '/opportunityboards/{id}/manage';
                                    manage = manage.replace('{id}',full[3]);
                                    return ' <a href="'+manage+'">'+val+'</a>';
                                }
                            },  // 1 Opportunity Board Name
                            {  "bSortable": true,
                                mRender: function (val){
                                    if(val==1){
                                        return   '<i class="entypo-check" style="font-size:22px;color:green"></i>'
                                    }else {
                                        return '<i class="entypo-cancel" style="font-size:22px;color:red"></i>'
                                    }
                                }

                            },  // 2 Active
                            {  "bSortable": true },  // 3 Created By
                            {                       //  5  Action
                                "bSortable": false,
                                mRender: function (id, type, full) {
                                    var configure = baseurl + '/opportunityboards/{id}/configure';
                                    configure = configure.replace('{id}',id);
                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }
                                    action += '</div>';
                                    @if(User::checkCategoryPermission('OpportunityBoard','Edit'))
                                        action += ' <a data-name = "' + full[0] + '" data-id="' + id + '" title="Edit" class="edit-opportunitybaord btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                                    @endif
                                    @if(User::checkCategoryPermission('OpportunityBoard','Configure'))
                                        action += ' <a class="manage-deal-board btn btn-default btn-sm btn-icon icon-left" href="'+configure+'"><i class="entypo-cog"></i> Configure Board</a>';
                                    @endif
                                    return action;
                                }
                            }
                        ],
                        "oTableTools": {
                            "aButtons": [
                            ]
                        },
                        "fnDrawCallback": function () {
                            $(".dataTables_wrapper select").select2({
                                minimumResultsForSearch: -1
                            });
                        }

                    });
                    $("#opportunityboard_filter").submit(function(e){
                        e.preventDefault();
                        $searchFilter.BoardName = $("#opportunityboard_filter [name='BoardName']").val();
                        $searchFilter.Active = $("#opportunityboard_filter select[name='Active']").val();
                        data_table.fnFilter('', 0);
                        return false;
                    });


                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    $('table tbody').on('click', '.edit-opportunitybaord', function (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        $('#add-edit-opportunitybaord-form').trigger("reset");
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){
                            if(list_fields[i] == 'Status'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                    $('#add-edit-opportunitybaord-form [name="Status"]').prop('checked',true)
                                }else{
                                    $('#add-edit-opportunitybaord-form [name="Status"]').prop('checked',false)
                                }
                            }else{
                                $("#add-edit-opportunitybaord-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }
                        }
                        $('#add-edit-modal-opportunity-board h4').html('Edit Board');
                        $('#add-edit-modal-opportunity-board').modal('show');
                    });

                    $('#add-new-opportunityboard').click(function (ev) {
                        ev.preventDefault();
                        var id = $(this).attr('id');
                        $('#add-edit-opportunitybaord-form').trigger("reset");
                        $("#add-edit-opportunitybaord-form [name='BoardID']").val('');
                        var BoardType = id=='add-new-opportunityboard'?opportunityBoard:taskBoard;
                        $("#add-edit-opportunitybaord-form [name='BoardType']").val(BoardType);
                        $('#add-edit-modal-opportunity-board h4').html('Add New Board');
                        $('#add-edit-modal-opportunity-board').modal('show');
                    });

                    $('#add-edit-opportunitybaord-form').submit(function(e){
                        e.preventDefault();
                        var BoardID = $("#add-edit-opportunitybaord-form [name='BoardID']").val();
                        if( typeof BoardID != 'undefined' && BoardID != ''){
                            update_new_url = baseurl + '/opportunityboards/'+BoardID+'/update';
                        }else{
                            update_new_url = baseurl + '/opportunityboards/create';
                        }
                        var formData = new FormData($('#add-edit-opportunitybaord-form')[0]);
                        $.ajax({
                            url: update_new_url,  //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                if(response.status =='success'){
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $('#add-edit-modal-opportunity-board').modal('hide');
                                    data_table.fnFilter('', 0);
                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $("#opportunityboard-update").button('reset');
                            },
                            // Form data
                            data: formData,
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    });

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    $('body').on('click', '.btn.delete', function (e) {
                        e.preventDefault();

                        response = confirm('Are you sure?');
                        if( typeof $(this).attr("data-redirect")=='undefined'){
                            $(this).attr("data-redirect",'{{ URL::previous() }}')
                        }
                        redirect = $(this).attr("data-redirect");
                        if (response) {

                            $.ajax({
                                url: $(this).attr("href"),
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    $(".btn.delete").button('reset');
                                    if (response.status == 'success') {
                                        toastr.success(response.message, "Success", toastr_opts);
                                        data_table.fnFilter('', 0);
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                },
                                // Form data
                                //data: {},
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }
                        return false;
                    });

                });
            </script>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="add-edit-modal-opportunity-board">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-edit-opportunitybaord-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Board</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Board Name *</label>
                                    <input type="text" name="BoardName" class="form-control" id="field-5" placeholder="">
                                    <input type="hidden" name="BoardID" />
                                    <input type="hidden" name="BoardType" value="{{CRMBoard::OpportunityBoard}}" />
                                </div>
                            </div>
                            </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label">Active</label>
                                    <div class="make-switch switch-small">
                                        <input type="checkbox" name="Status" checked="" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="opportunityboard-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
@stop

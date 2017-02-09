<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Activities
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div class="text-right">
            <a  id="add-new-activity" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
            <div class="clear clearfix"><br></div>
        </div>
                    <form id="activity_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                        <div class="panel panel-primary" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Filter
                                </div>
                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-2 control-label">Activity Title</label>
                                    <div class="col-sm-2">
                                        {{ Form::text('ActivityTitle', '', array("class"=>"form-control")) }}
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">Activity Type</label>
                                    <div class="col-sm-2">
                                          {{ Form::select('activityType', $activity_type, '', array("class"=>"form-control select2 small")) }}
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">Activity Status</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('activityStatus', $activity_status, '', array("class"=>"form-control select2 small")) }}
                                    </div>
                                </div>
                                <p style="text-align: right;">
                                    <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                        <i class="entypo-search"></i>
                                        Search
                                    </button>
                                </p>
                            </div>
                        </div>
                    </form>
            <table class="table table-bordered datatable" id="table-activity">
                <thead>
                <tr>
                    <th width="10%">Title</th>
                    <th width="30%">Description</th>
                    <th width="10%">Date</th>
                    <th width="10%">Activity Type</th>
                    <th width="20%">Created date</th>
                    <th width="20%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var list_fields_activity  = ['Title','Description','Date','ActivityType','created_at','ActivityID'];
                var activity_type = {{json_encode(AccountActivity::$activity_type)}};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {
                        public_vars.$body = $("body");
                    var $search = {};
                    $search.ActivityTitle = $("#activity_filter").find('[name="ActivityTitle"]').val();
                    $search.activityType = $("#activity_filter").find('[name="activityType"]').val();
                    $search.activityStatus = $("#activity_filter").find('[name="activityStatus"]').val();
                        data_table_activity = $("#table-activity").dataTable({
                            "bDestroy": true,
                            "bProcessing": true,
                            "bServerSide": true,
                            "sAjaxSource": baseurl + "/accounts/{{$account->AccountID}}/activities/ajax_datagrid",
                            "fnServerParams": function (aoData) {
                                aoData.push({"name": "Title", "value": $search.ActivityTitle},
                                        {"name": "activityType", "value": $search.activityType},
                                        {"name": "activityStatus", "value": $search.activityStatus});

                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({"name": "Title", "value": $search.ActivityTitle},
                                        {"name": "activityType", "value": $search.activityType},
                                        {"name": "activityStatus", "value": $search.activityStatus},
                                        {"name": "Export", "value": 1});

                            },
                            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'r>",
                            "aaSorting": [[0, 'asc']],
                            "aoColumns": [
                                {"bSortable": true},  // 1 Title
                                {"bSortable": true},  // 2 Description
                                {"bSortable": true,   // 4 ActivityType
                                    mRender: function (id, type, full) {
                                        var d = id.split(' ');
                                        return d[0];
                                    }
                                },  // 3 Date
                                {
                                    "bSortable": true,   // 4 ActivityType
                                    mRender: function (id, type, full) {
                                        return activity_type[id];
                                    }
                                },
                                {"bSortable": true},  // created_at
                                {                       //  5  Action
                                    "bSortable": false,
                                    mRender: function (id, type, full) {

                                        var delete_ = "{{ URL::to('/accounts/'.$account->AccountID.'/activities/{id}/delete/')}}";
                                        delete_ = delete_.replace('{id}', id);

                                        action = '<div class = "hiddenRowData" >';
                                        for (var i = 0; i < list_fields_activity.length; i++) {
                                            action += '<input type = "hidden"  name = "' + list_fields_activity[i] + '"       value = "' + (full[i] != null ? full[i] : '') + '" / >';
                                        }
                                        action += '</div>';
                                        action += ' <a data-id="' + id + '" title="Edit" class="edit-activity btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                                        action += ' <a href="' + delete_ + '" data-redirect="{{ URL::to('products')}}" title="Delete"  class="btn delete btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                                        return action;
                                    }
                                }
                            ],
                            "oTableTools": {
                                "aButtons": [
                                    {
                                        "sExtends": "download",
                                        "sButtonText": "Export Data",
                                        "sUrl": baseurl + "/products/ajax_datagrid", //baseurl + "/generate_xls.php",
                                        sButtonClass: "save-collection"
                                    }
                                ]
                            },
                            "fnDrawCallback": function () {
                                $(".dataTables_wrapper select").select2({
                                    minimumResultsForSearch: -1
                                });
                            }

                        });
                    $("#activity_filter").submit(function(e) {
                        e.preventDefault();
                        $search.ActivityTitle = $("#activity_filter").find('[name="ActivityTitle"]').val();
                        $search.activityType = $("#activity_filter").find('[name="activityType"]').val();
                        $search.activityStatus = $("#activity_filter").find('[name="activityStatus"]').val();
                        data_table_activity.fnFilter('', 0);
                    });

                        // Replace Checboxes
                        $(".pagination a").click(function (ev) {
                            replaceCheckboxes();
                        });

                        $('table tbody').on('click', '.edit-activity', function (ev) {
                            ev.preventDefault();
                            ev.stopPropagation();
                            $('#add-edit-activity-form').trigger("reset");
                            var cur_obj = $(this).prev("div.hiddenRowData");
                            for(var i = 0 ; i< list_fields_activity.length; i++){

                                if(list_fields_activity[i] == 'ActivityType'){
                                    $("#add-edit-activity-form [name='ActivityType']").val(cur_obj.find("input[name='"+list_fields_activity[i]+"']").val()).trigger("change");
                                }else if(list_fields_activity[i]=='Date'){
                                    var str = cur_obj.find("input[name='"+list_fields_activity[i]+"']").val();
                                    var datetime = str.split(' ');
                                    $("#add-edit-activity-form [name='Date']").val(datetime[0]);
                                    $("#add-edit-activity-form [name='Time']").val(datetime[1]);
                                }else{
                                    $("#add-edit-activity-form [name='"+list_fields_activity[i]+"']").val(cur_obj.find("input[name='"+list_fields_activity[i]+"']").val());
                                }
                            }
                            $('#add-edit-modal-activity h4').html('Edit activity');
                            $('#add-edit-modal-activity').modal('show');
                        });


                    $('#add-new-activity').click(function (ev) {
                        ev.preventDefault();
                        $('#add-edit-activity-form').trigger("reset");
                        $("#add-edit-activity-form [name='ActivityID']").val('');
                        $("#add-edit-activity-form [name='ActivityType']").val('').trigger("change");
                        $('#add-edit-modal-activity h4').html('Add New activity');
                        $('#add-edit-modal-activity').modal('show');
                    });


                    $('#add-edit-activity-form').submit(function(e){
                        e.preventDefault();
                        var ActivityID = $("#add-edit-activity-form [name='ActivityID']").val()
                        if( typeof ActivityID != 'undefined' && ActivityID != ''){
                            update_new_url = baseurl + '/accounts/{{$account->AccountID}}/activities/'+ActivityID+'/update';
                        }else{
                            update_new_url = baseurl + '/accounts/{{$account->AccountID}}/activities/store';
                        }
                        $.ajax({
                            url: update_new_url,  //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                if(response.status =='success'){
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $('#add-edit-modal-activity').modal('hide');
                                    data_table_activity.fnFilter('', 0);
                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $("#activity-add").button('reset');
                            },
                            // Form data
                            data: $('#add-edit-activity-form').serialize(),
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false
                        });
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
                                    data_table_activity.fnFilter('', 0);
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
            </script>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>

</div>

@section('footer_ext')
    @parent

    <div class="modal fade custom-width" id="add-edit-modal-activity">
        <div class="modal-dialog" style="width: 60%;">
            <div class="modal-content">
                <form id="add-edit-activity-form" method="post" class="form-horizontal form-groups-bordered">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New activity</h4>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="field-5" class="col-sm-2 control-label">Activity Title *<span id="currency"></span></label>
                            <div class="col-sm-4">
                                <input type="text" name="Title" class="form-control"  value="" />
                                <input type="hidden" name="ActivityID" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-5" class="col-sm-2 control-label">Activity Type<span id="currency"></span></label>
                            <div class="col-sm-4">
                                {{ Form::select('ActivityType', $activity_type, '', array("class"=>"form-control select2 small")) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">Date</label>
                            <div class="col-sm-2">
                                <input type="text" name="Date" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="" data-startdate="{{date('Y-m-d',strtotime(" today"))}}" />
                            </div>
                            <div class="col-sm-2">
                                <input type="text" name="Time" data-minute-step="5" data-show-meridian="false" data-default-time="00:00 AM" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="field-1">Description</label>
                            <div class="col-sm-4">
                                <textarea type="text" name="Description" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="activity-add" class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
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



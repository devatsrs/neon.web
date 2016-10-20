<div class="row">
    <div class="col-md-12">
        <form id="call_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
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
                        <label for="field-1" class="col-sm-1 control-label">Type</label>
                        <div class="col-sm-3">
                            {{Form::select('AlertType',$call_monitor_alert_type,'',array("class"=>"select2"))}}
                        </div>
                    </div>
                    <p style="text-align: right;">
                        <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left" id="call_submit">
                            <i class="entypo-search"></i>
                            Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
@if(User::checkCategoryPermission('Alert','Add'))
    <p style="text-align: right;">
        <a class=" btn btn-primary btn-sm btn-icon icon-left" id="add-call-alert">
            <i class="entypo-plus"></i>
            Add Call Monitor Alert
        </a>
    </p>
@endif
<table class="table table-bordered datatable" id="table-6">
    <thead>
    <tr>
        <th width="20%">Name</th>
        <th width="10%">Type</th>
        <th width="10%">Status</th>
        <th width="10%">Low Value</th>
        <th width="10%">High Value</th>
        <th width="10%">Last Updated</th>
        <th width="10%">Updated By</th>
        <th width="20%">Action</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<script type="text/javascript">
    var list_fields  = ["Name","AlertType","Status","LowValue","HighValue","created_at","CreatedBy","AlertID","Settings"];
    var CallAlertType = JSON.parse('{{json_encode($call_monitor_alert_type)}}');
    var $search = {};
    var update_new_url;
    var postdata;
    var alert_add_url = baseurl + "/alert/store";
    var alert_edit_url = baseurl + "/alert/update/{id}";
    var alert_delete_url = baseurl + "/alert/delete/{id}";
    var alert_datagrid_url = baseurl + "/alert/ajax_datagrid/type";
    jQuery(document).ready(function ($) {
        $search.AlertType = $('#call_filter [name="AlertType"]').val();
        data_table_call = $("#table-6").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": alert_datagrid_url,
            "fnServerParams": function (aoData) {
                aoData.push(
                        {"name": "AlertType", "value": $search.AlertType},
                        {"name": "AlertGroup", "value": '{{Alert::GROUP_CALL}}'}
                );

                data_table_extra_params.length = 0;
                data_table_extra_params.push(
                        {"name": "AlertType", "value": $search.AlertType},
                        {"name": "AlertGroup", "value": '{{Alert::GROUP_CALL}}'},
                        {"name":"Export","value":1}

                );

            },
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "aoColumns": [
                {"bSortable": true},  // 0 Name
                {"bSortable": true,mRender:function(id,type,full){
                    return CallAlertType[id];
                }},  // 1 Type
                {
                    mRender: function (status, type, full) {
                        if (status == 1)
                            return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                        else
                            return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                    }
                }, //2   Status
                {"bSortable": true},  // 3 Created At
                {"bSortable": true},  // 4 Created At
                {"bSortable": true},  // 5 Created At
                {"bSortable": true},  // 6 Created By
                {                        // 7 Action
                    "bSortable": false,
                    mRender: function (id, type, full) {
                        action = '<div class = "hiddenRowData" >';
                        for (var i = 0; i < list_fields.length; i++) {
                            if(list_fields[i] == 'Settings' && IsJsonString(full[i])){
                                var settings_json = JSON.parse(full[i]);
                                $.each(settings_json, function(key, value) {
                                    action += '<input disabled type = "hidden"  name = "' +key + '"       value = "' + value + '" / >';
                                });
                            }else {
                                action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + full[i] + '" / >';
                            }
                        }
                        action += '</div>';
                        @if(User::checkCategoryPermission('Alert','Update'))
                                action += ' <a href="' + alert_edit_url.replace("{id}", id) + '" class="edit-call-alert btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                        @endif
                                @if(User::checkCategoryPermission('Alert','Delete'))
                                action += ' <a href="' + alert_delete_url.replace("{id}", id) + '" class="delete-call-alert btn btn-danger btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Delete </a>'
                        @endif
                                return action;
                    }
                }
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/alert/ajax_datagrid/xlsx",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/alert/ajax_datagrid/csv",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
            "fnDrawCallback": function () {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });
            }

        });
        $("#call_submit").click(function(e) {

            e.preventDefault();
            public_vars.$body = $("body");
            $search.AlertType = $('#call_filter [name="AlertType"]').val();
            data_table_call.fnFilter('', 0);
            return false;
        });


// Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


//inst.myMethod('I am a method');
        $('#add-call-alert').click(function(ev){
            ev.preventDefault();
            $('#call-billing-form').trigger("reset");
            $('#add-call-modal h4').html('Add Call Monitor Alert');

            $(".js-example-disabled").prop("disabled", false);
            $('#call-billing-form select').select2("val", "");

            var selectBox = $("#call-billing-form [name='AlertType']");
            selectBox.val('').trigger("change");
            selectBox.prop("disabled", false);
            $('.tax').removeClass('hidden');

            $('#call-billing-form').attr("action",alert_add_url);
            $('#add-call-modal').modal('show');
        });
        $('table tbody').on('click', '.edit-call-alert', function (ev) {
            ev.preventDefault();
            $('#call-billing-form').trigger("reset");
            var edit_url  = $(this).attr("href");
            $('#call-billing-form').attr("action",edit_url);
            $('#add-call-modal h4').html('Edit Call Monitor Alert');
            $('#call-billing-form select').select2("val", "");
            $(this).prev("div.hiddenRowData").find('input').each(function(i, el){
                var ele_name = $(el).attr('name');
                var ele_val = $(el).val();
                $("#call-billing-form [name='"+ele_name+"']").val(ele_val);
                if(ele_name == 'AlertType'){
                    var selectBox = $("#call-billing-form [name='"+ele_name+"']");
                    selectBox.val(ele_val).trigger("change");
                    selectBox.prop("readonly", true);
                }else if(ele_name == 'Day') {
                    $("#call-billing-form [name='CallAlert[Day][]']").val(ele_val.split(',')).trigger('change');
                }else if(ele_name == 'Interval'){
                    setTimeout(function(){
                        $("#call-billing-form [name='CallAlert[Interval]']").val(ele_val).trigger('change');
                    },5);
                }else if(ele_name == 'Status'){
                    if (ele_val == 1) {
                        $("#call-billing-form [name='"+ele_name+"']").prop('checked', true)
                    } else {
                        $("#call-billing-form [name='"+ele_name+"']").prop('checked', false)
                    }
                }else{
                    $("#call-billing-form [name='CallAlert["+ele_name+"]']").val(ele_val);
                }

            });

            $('#add-call-modal').modal('show');
        });
        $('table tbody').on('click', '.delete-call-alert', function (ev) {
            ev.preventDefault();
            result = confirm("Are you Sure?");
            if(result){
                var delete_url  = $(this).attr("href");
                submit_ajax_datatable( delete_url,"",0,data_table_call);
            }
            return false;
        });

        $("#call-billing-form").submit(function(e){
            e.preventDefault();
            var _url  = $(this).attr("action");
            submit_ajax_datatable(_url,$(this).serialize(),0,data_table_call);

        });

// Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
    });

</script>

@include('notification.call_modal')
<br/>
<p class="col-md-12 text-right">
    <a class=" btn btn-primary btn-sm btn-icon icon-left" id="add-call-alert">
        <i class="entypo-plus"></i>
        @lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_BUTTON_ADD_NEW')
    </a>
</p>
<table class="table table-bordered datatable" id="table-6">
    <thead>
    <tr>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_TBL_NAME')</th>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_TBL_TYPE')</th>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_TBL_STATUS')</th>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_TBL_LAST_UPDATED')</th>
        <th width="10%">@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_TBL_UPDATED_BY')</th>
        <th width="20%">@lang('routes.TABLE_COLUMN_ACTION')</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>
<script type="text/javascript">
    var list_fields  = ["Name","AlertType","Status","created_at","CreatedBy","AlertID","Settings"];
    var CallAlertType = JSON.parse('{{json_encode($call_monitor_alert_type)}}');
    var $search = {};
    var update_new_url;
    var postdata;
    var alert_add_url = baseurl + "/customer/alert/store";
    var alert_edit_url = baseurl + "/customer/alert/update/{id}";
    var alert_delete_url = baseurl + "/customer/alert/delete/{id}";
    var alert_datagrid_url = baseurl + "/customer/alert/ajax_datagrid/type";
    jQuery(document).ready(function ($) {
        $search.AlertType = $('#call_filter [name="AlertType"]').val();
        data_table_call = $("#table-6").dataTable({
            "oLanguage": {
                "sUrl": baseurl + "/translate/datatable_Label"
            },
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
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "aoColumns": [
                {"bSortable": true},  // 5 Created At
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

                                action += ' <a href="' + alert_edit_url.replace("{id}", id) + '" title="@lang('routes.BUTTON_EDIT_CAPTION')" class="edit-call-alert btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'


                                action += ' <a href="' + alert_delete_url.replace("{id}", id) + '" title="@lang('routes.BUTTON_DELETE_CAPTION')" class="delete-call-alert btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'

                                return action;
                    }
                }
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "@lang("routes.BUTTON_EXPORT_EXCEL_CAPTION")",
                        "sUrl": baseurl + "/customer/alert/ajax_datagrid/xlsx",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "@lang("routes.BUTTON_EXPORT_CSV_CAPTION")",
                        "sUrl": baseurl + "/customer/alert/ajax_datagrid/csv",
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
            $('#add-call-modal h4').html('@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_ADD_MONITORING_TITLE')');


            $('#call-billing-form select').select2("val", "");
            var selected_days = "SUN,MON,TUE,WED,THU,FRI,SAT";
            $("#call-billing-form [name='CallAlert[Day][]']").val(selected_days.split(',')).trigger('change');

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
            $('#add-call-modal h4').html('@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MODAL_EDIT_MONITORING_TITLE')');
            $('#call-billing-form select').select2("val", "");
            $(this).prev("div.hiddenRowData").find('input').each(function(i, el){
                var ele_name = $(el).attr('name');
                var ele_val = $(el).val();
                $("#call-billing-form [name='"+ele_name+"']").val(ele_val);
                if(ele_name == 'AlertType'){
                    var selectBox = $("#call-billing-form [name='"+ele_name+"']");
                    selectBox.prop("disabled", true);
                    selectBox.val(ele_val).trigger("change");
                }else if(ele_name == 'BlacklistDestination') {
                    $("#call-billing-form [name='CallAlert[BlacklistDestination][]']").val(ele_val.split(',')).trigger('change');
                }else if(ele_name == 'AccountID'){
                    var selectBox = $("#call-billing-form [name='CallAlert["+ele_name+"]']");
                    selectBox.val(ele_val).trigger("change");
                }else if(ele_name == 'EmailToAccount'){
                    if (ele_val == 1) {
                        $("#call-billing-form [name='CallAlert["+ele_name+"]']").prop('checked', true)
                    } else {
                        $("#call-billing-form [name='CallAlert["+ele_name+"]']").prop('checked', false)
                    }
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
            result = confirm("@lang('routes.MESSAGE_ARE_YOU_SURE')");
            if(result){
                var delete_url  = $(this).attr("href");
                submit_ajax_datatable( delete_url,"",0,data_table_call);
            }
            return false;
        });

        $("#call-billing-form").submit(function(e){
            e.preventDefault();
            $("#call-billing-form [name='AlertType']").prop("disabled", false);
            var _url  = $(this).attr("action");
            submit_ajax_datatable(_url,$(this).serialize(),0,data_table_call);

        });

// Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
    });

</script>

@include('customer.notification.call_modal')
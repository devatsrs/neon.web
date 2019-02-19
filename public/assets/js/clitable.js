var update_new_url;
var postdata;
jQuery(document).ready(function ($) {
    var cli_list_fields = ["CLIRateTableID", "CLI", "CLI Rate Table", 'Package', 'Package Rate Table', 'CityTariff', 'RateTableID', 'PackageID', 'PackageRateTableID', 'Prefix', 'Status'];
    public_vars.$body = $("body");
    var $searchcli = {};
    var clitable_add_url = baseurl + "/clitable/store";
    var clitable_update_url = baseurl + "/clitable/update";
    var clitable_delete_url = baseurl + "/clitable/delete/{id}";
    var clitable_datagrid_url = baseurl + "/clitable/ajax_datagrid/" + AccountID;
    $("#clitable_submit").click(function (e) {
        e.preventDefault();
        $searchcli.CLIName = $("#clitable_filter").find('[name="CLIName"]').val();
        data_table_clitable = $("#table-clitable").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": clitable_datagrid_url,
            "fnServerParams": function (aoData) {
                aoData.push({
                        "name": "AccountID",
                        "value": AccountID
                    },
                    {
                        "name": "ServiceID",
                        "value": ServiceID
                    },
                    {
                        "name": "AccountServiceID",
                        "value": AccountServiceID
                    },
                    {
                        "name": "CLIName",
                        "value": $searchcli.CLIName
                    }
                );

                data_table_extra_params.length = 0;
                data_table_extra_params.push({
                        "name": "AccountID",
                        "value": AccountID
                    },
                    {
                        "name": "ServiceID",
                        "value": ServiceID
                    },
                    {
                        "name": "AccountServiceID",
                        "value": AccountServiceID
                    },
                    {
                        "name": "CLIName",
                        "value": $searchcli.CLIName
                    }
                );

            },
            "iDisplayLength": 10,
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "aoColumns": [
                {
                    "bSortable": false,
                    mRender: function (id, type, full) {
                        return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                    }
                },
                {"bSortable": true},  // 0 Sequence NO
                {"bSortable": true},  // 1  Name
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
                {   //Prefix
                    "bSortable": true,
                    mRender: function ( id, type, full ) {
                        return full[9] == 0 ? '' : full[9];
                    }
                },
                {  "bSortable": false, //Status
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_;
                        //console.log(id);
                        if(full[10] == 1){
                            action='<i class="entypo-check" style="font-size:22px;color:green"></i>';
                        }else{
                            action='<i class="entypo-cancel" style="font-size:22px;color:red"></i>';
                        }
                        return action;
                    }
                },  //0  Status', '', '', '
                {                        // 10 Action
                    "bSortable": false,
                    mRender: function (id, type, full) {
                        action = '<div class = "hiddenRowData" >';
                        for (var i = 0; i < cli_list_fields.length; i++) {
                            action += '<input disabled type = "hidden"  name = "' + cli_list_fields[i] + '"  value = "' + (full[i] != null ? full[i] : '') + '" / >';
                        }
                        action += '</div>';
                        action += ' <a href="javascript:;" title="Edit" class="edit-clitable btn btn-default btn-sm tooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i></a>';
                        action += ' <a href="' + clitable_delete_url.replace("{id}", full[0]) + '" class="delete-clitable btn btn-danger btn-sm tooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="entypo-trash"></i></a>'
                        return action;
                    }
                }
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "Export Data",
                        "sUrl": clitable_datagrid_url,
                        sButtonClass: "save-collection"
                    }
                ]
            },
            "fnDrawCallback": function () {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

                default_row_selected('table-clitable','selectall','selectallbutton');
                select_all_top('selectallbutton','table-clitable','selectall');
            }
        });
        setTimeout(function () {
            $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
        }, 10);
    });



    selected_all('selectall', 'table-clitable');

    table_row_select('table-clitable', 'selectallbutton');


    // Replace Checboxes
    $(".pagination a").click(function (ev) {
        replaceCheckboxes();
    });
    $('#clitable_submit').trigger('click');

    $('#add-clitable').click(function (ev) {
        ev.preventDefault();
        $('#clitable-form').trigger("reset");
        $('#modal-clitable h4').html('Add CLI');
        $("#clitable-form [name=RateTableID]").select2().select2('val', "");
        $("#clitable-form [name=PackageID]").select2().select2('val', "");
        $("#clitable-form [name=PackageRateTableID]").select2().select2('val', "");
        $('#clitable-form').attr("action", clitable_add_url);
        $('#clitable-form .edit_hide').show().find(".cli-field").attr("name", "CLI");
        $('#clitable-form .edit_show').hide().find(".cli-field").attr("name", "");
        $('#modal-clitable').modal('show');
    });

    $('table tbody').on('click', '.edit-clitable', function (ev) {
        ev.preventDefault();

        var fields = $(this).prev(".hiddenRowData");
        $('#clitable-form').trigger("reset");
        $('#modal-clitable h4').html('Edit CLI RateTable');

        var CLI = fields.find("[name='CLI']").val();
        var RateTableID = fields.find("[name='RateTableID']").val();
        var CityTariff = fields.find("[name='CityTariff']").val();
        var Prefix = fields.find("[name='Prefix']").val();
        var Status = fields.find("[name='Status']").val();
        var PackageID = fields.find("[name='PackageID']").val();
        var PackageRateTableID = fields.find("[name='PackageRateTableID']").val();
        var CLIRateTableID = fields.find("[name='CLIRateTableID']").val();

        RateTableID = RateTableID == 0 ? '' : RateTableID;
        PackageID = PackageID == 0 ? '' : PackageID;
        PackageRateTableID = PackageRateTableID == 0 ? '' : PackageRateTableID;
        Prefix = Prefix == 0 ? '' : Prefix;

        $('#clitable-form .edit_hide').hide().find(".cli-field").attr("name", "");
        $('#clitable-form .edit_show').show().find(".cli-field").attr("name", "CLI");
        $("#clitable-form .edit_show [name=CLI]").val(CLI);
        $("#clitable-form [name=RateTableID]").select2().select2('val', RateTableID);
        $("#clitable-form [name=CityTariff]").val(CityTariff);
        $("#clitable-form [name=Prefix]").val(Prefix);
        $("#clitable-form [name=Status]").prop("checked", Status == 1).trigger("change");
        $("#clitable-form [name=PackageID]").select2().select2('val', PackageID);
        $("#clitable-form [name=PackageRateTableID]").select2().select2('val', PackageRateTableID);
        $('#clitable-form input[name=CLIRateTableID]').val(CLIRateTableID);
        $('#clitable-form input[name=CLIRateTableIDs]').val("");
        $('#clitable-form input[name=criteria]').val("");
        $('#clitable-form').attr("action", clitable_update_url);
        $('#modal-clitable').modal('show');
    });

    $('table tbody').on('click', '.delete-clitable', function (ev) {
        ev.preventDefault();
        $(this).button('loading');
        result = confirm("Are you Sure?");
        if (result) {
            var delete_url = $(this).attr("href");
            var AuthRule = $('#clitable-form').find('input[name=AuthRule]').val();
            var AccountID = $('#clitable-form').find('input[name=AccountID]').val();
            delete_cli(delete_url,"AuthRule="+AuthRule+'&AccountID='+AccountID+'&ServiceID='+ServiceID)
        } else
            $(this).button('reset');
        return false;
    });

    $("#clitable-form").submit(function (e) {
        e.preventDefault();
        var _url = $(this).attr("action");
        submit_ajax_datatable(_url, $(this).serialize(), 0, data_table_clitable);
    });

    $("#bulk-delete-cli").click(function (ev) {
        var criteria = '';
        if ($('#selectallbutton').is(':checked')) {
            criteria = JSON.stringify($searchcli);
        }
        var CLIRateTableIDs = [];
        if (!confirm('Are you sure you want to delete selected CLI?')) {
            return;
        }
        $('#table-clitable tr .rowcheckbox:checked').each(function (i, el) {
            CLIRateTableID = $(this).val();
            if (typeof CLIRateTableID != 'undefined' && CLIRateTableID != null && CLIRateTableID != 'null') {
                CLIRateTableIDs[i] = CLIRateTableID;
            }
        });
        var AuthRule = $('#clitable-form').find('input[name=AuthRule]').val();
        var AccountID = $('#clitable-form').find('input[name=AccountID]').val();
        if (CLIRateTableIDs.length) {
            delete_cli(clitable_delete_url.replace("{id}", 0),'CLIRateTableIDs=' + CLIRateTableIDs.join(",") + '&criteria=' + criteria+'&AuthRule='+AuthRule+'&AccountID='+AccountID+'&ServiceID=' + ServiceID+'&AccountServiceID='+AccountServiceID)
        }
    });
    $("#changeSelectedCLI").click(function (ev) {
        var criteria = '';
        if ($('#selectallbutton').is(':checked')) {
            criteria = JSON.stringify($searchcli);
        }
        var CLIRateTableIDs = [];
        $('#table-clitable tr .rowcheckbox:checked').each(function (i, el) {
            CLIRateTableID = $(this).val();
            if (typeof CLIRateTableID != 'undefined' && CLIRateTableID != null && CLIRateTableID != 'null') {
                CLIRateTableIDs[i] = CLIRateTableID;
            }
        });
        $('#clitable-form').trigger("reset");
        $('#modal-clitable h4').html('Update CLI');
        $("#clitable-form [name=RateTableID]").select2().select2('val', "");
        $("#clitable-form [name=PackageID]").select2().select2('val', "");
        $("#clitable-form [name=PackageRateTableID]").select2().select2('val', "");
        $('#clitable-form').find('input[name=CLIRateTableIDs]').val(CLIRateTableIDs);
        $('#clitable-form').find('input[name=criteria]').val(criteria);
        $('#clitable-form').attr("action", clitable_update_url);
        $('#clitable-form').find('.edit_hide').hide();
        $('#modal-clitable').modal('show');
    });

    $('#form-confirm-modal').submit(function (e) {
        e.preventDefault();
        var dates = $('#form-confirm-modal [name="Closingdate"]').val();
        var criteria = '';
        if ($('#selectallbutton').is(':checked')) {
            criteria = JSON.stringify($searchcli);
        }
        var CLIRateTableIDs = [];
        $('#table-clitable tr .rowcheckbox:checked').each(function (i, el) {
            CLIRateTableID = $(this).val();
            if (typeof CLIRateTableID != 'undefined' && CLIRateTableID != null && CLIRateTableID != 'null') {
                CLIRateTableIDs[i] = CLIRateTableID;
            }
        });
        var CLIRateTableID = $('#clitable-form').find('input[name=CLIRateTableID]').val();
        delete_cli(clitable_delete_url.replace("{id}", CLIRateTableID),'CLIRateTableIDs=' + CLIRateTableIDs.join(",") + '&criteria=' + criteria+'&dates=' + dates+'&ServiceID=' + ServiceID+'&AccountServiceID='+AccountServiceID)

    });
});

function delete_cli(action,data) {
    $.ajax({
        url: action,
        type: 'POST',
        data: data,
        datatype: 'json',
        success: function (response) {
            $("#delete-clidate").button('reset');
            $(".btn").button('reset');
            if (response.status == 'success') {
                $('#confirm-modal').modal('hide');
                if (typeof data_table_clitable != 'undefined') {
                    data_table_clitable.fnFilter('', 0);
                }
                $('.selectall').prop("checked", false);
                toastr.success(response.message, 'Success', toastr_opts);
            } else if (response.status == 'check') {
                var CLIRateTableID = 0;
                var p = new RegExp('(\\/)(\\d+)', ["i"]);
                var m = p.exec(action);
                if (m != null) {
                    CLIRateTableID = m[2];
                }
                $('#clitable-form').find('input[name=CLIRateTableID]').val(CLIRateTableID);
                $('#confirm-modal').modal('show');
            } else {
                toastr.error(response.message, "Error", toastr_opts);
            }
        }
    });
}
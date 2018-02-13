@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="reseller_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                <!--<div class="form-group">
                    <label for="field-1" class="control-label">Reseller Name</label>
                    {{ Form::text('ResellerName', '', array("class"=>"form-control")) }}
                </div>-->
                <div class="form-group">
                    <label for="field-1" class="control-label">Account Name</label>
                    {{ Form::select('AccountID', Account::getAccountList(['IsReseller'=>'1']), '', array("class"=>"select2","data-allow-clear"=>"true")) }}
					<input id="Status" name="Status" type="hidden" value="1">
					<input id="ResellerRefresh" type="hidden" value="1">
                </div>
                <!--
				<div class="form-group">
                    <label for="field-1" class="control-label">Status</label><br/>
                    <p class="make-switch switch-small">
                        <input id="Status" name="Status" type="checkbox" checked>
                        <input id="ResellerRefresh" type="hidden" value="1">
                    </p>
                </div>-->
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
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Reseller</strong>
    </li>
</ol>
<h3>Resellers</h3>
<p class="text-right">
@if(User::checkCategoryPermission('Reseller','Add'))
    <a href="#" id="add-reseller" data-action="showAddModal" data-type="reseller" data-modal="add-new-modal-reseller" class="btn btn-primary">
        <i class="entypo-plus"></i>
        Add New
    </a>
    <a href="#" id="copy-resellerdata" class="btn btn-primary">
        Copy Data
    </a>
@endif
</p>

<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
        <th>Reseller Account</th>
        <th>UserName</th>
        <th>Number Of Account</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
 

    </tbody>
</table>

<script type="text/javascript">
    var $searchFilter = {};
    var checked = '';
    jQuery(document).ready(function ($) {

        $('#filter-button-toggle').show();

        $searchFilter.AccountID = $("#reseller_filter [name='AccountID']").val();
        $searchFilter.Status = $("#reseller_filter [name='Status']").val();
        //$searchFilter.Status = $("#reseller_filter [name='Status']").prop("checked");

        data_table = $("#table-4").dataTable({

            "bProcessing":true,
            "bServerSide":true,
            //"sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sAjaxSource": baseurl + "/reseller/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "aaSorting"   : [[0, 'asc']],
            "fnServerParams": function(aoData) {
                aoData.push({"name":"AccountID","value":$searchFilter.AccountID},{"name":"Status","value":$searchFilter.Status});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"AccountID","value":$searchFilter.AccountID},{"name":"Status","value":$searchFilter.Status},{ "name": "Export", "value": 1});
            },
            "aoColumns": 
             [
                 {"bSortable": false,
                     mRender: function(id, type, full) {
                         // checkbox for bulk action
                         return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                     }
                 },// resellerid
                 { "bSortable": true }, //AccountName
                 { "bSortable": true }, //Email
                 { "bSortable": true }, //NumberOfAccount
                 //{ "bVisible": false, "bSortable": true  }, //Status
                 {
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_, delete_ ;
                        action = '<div class = "hiddenRowData" >';
                        action += '<input type = "hidden"  name = "ResellerID" value = "' + (full[0] != null ? full[0] : 0) + '" / >';
                        action += '<input type = "hidden"  name = "AccountName" value = "' + (full[1] != null ? full[1] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Email" value = "' + (full[2] != null ? full[2] : '') + '" / >';
                        action += '<input type = "hidden"  name = "NumberOfAccount" value = "' + (full[3] != null ? full[3] : '') + '" / >';
                        action += '<input type = "hidden"  name = "AccountID" value = "' + (full[4] != null ? full[4] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Status" value = "' + (full[5] != null ? full[5] : 0) + '" / >';
                        action += '<input type = "hidden"  name = "AllowWhiteLabel" value = "' + (full[6] != null ? full[6] : 0) + '" / >';
                        action += '<input type = "hidden"  name = "CompanyID" value = "' + (full[7] != null ? full[7] : '') + '" / >';
                        action += '<input type = "hidden"  name = "ChildCompanyID" value = "' + (full[8] != null ? full[8] : 0) + '" / >';
                        action += '</div>';
                        <?php if(User::checkCategoryPermission('Reseller','Edit')){ ?>
                                action += ' <a data-name = "'+full[1]+'" data-id="'+ full[0] +'" title="Edit" class="edit-reseller btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('Reseller','Delete')){ ?>
                                action += ' <a data-id="'+ full[0] +'" title="Delete" class="delete-reseller btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
                        <?php } ?>
                        return action;
                      }
                  }
            ],
            "oTableTools":
            {
                "aButtons": [
                {
                    "sExtends": "download",
                    "sButtonText": "EXCEL",
                    "sUrl": baseurl + "/reseller/exports/xlsx",
                    sButtonClass: "save-collection btn-sm"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/reseller/exports/csv",
                    sButtonClass: "save-collection btn-sm"
                }
                ]
            },
            "fnDrawCallback": function() {
                $(".delete-reseller.btn").click(function(ev) {
                    response = confirm('Are you sure?');
                    if (response) {
                        var clear_url;
                        var id  = $(this).attr("data-id");
                        clear_url = baseurl + "/reseller/delete/"+id;
                        $(this).button('loading');
                        //get
                        $.get(clear_url, function (response) {
                            if (response.status == 'success') {
                                $(this).button('reset');
                                data_table.fnFilter('', 0);
								/*
                                if ($('#Status').is(":checked")) {
                                    data_table.fnFilter(1,0);  // 1st value 2nd column index
                                } else {
                                    data_table.fnFilter(0,0);
                                }*/
                                toastr.success(response.message, "Success", toastr_opts);
                            } else {
                                data_table.fnFilter('', 0);
								/*
                                if ($('#Status').is(":checked")) {
                                    data_table.fnFilter(1,0);  // 1st value 2nd column index
                                } else {
                                    data_table.fnFilter(0,0);
                                }*/
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        });
                    }
                    return false;


                });

                $('#table-4 tbody tr').each(function (i, el) {
                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        if (checked != '') {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                            $('#selectallbutton').prop("checked", true);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                            $(this).removeClass('selected');
                        }
                    }
                });
                //select all record
                $('#selectallbutton').click(function(){
                    if($('#selectallbutton').is(':checked')){
                        checked = 'checked=checked disabled';
                        $("#selectall").prop("checked", true).prop('disabled', true);
                        //if($('.gridview').is(':visible')){
                        $('.gridview li div.box').each(function(i,el){
                            $(this).addClass('selected');
                        });
                        //}else{
                        $('#table-4 tbody tr').each(function (i, el) {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                        });
                        //}
                    }else{
                        checked = '';
                        $("#selectall").prop("checked", false).prop('disabled', false);
                        //if($('.gridview').is(':visible')){
                        $('.gridview li div.box').each(function(i,el){
                            $(this).removeClass('selected');
                        });
                        //}else{
                        $('#table-4 tbody tr').each(function (i, el) {
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                            $(this).removeClass('selected');
                        });
                        //}
                    }
                });
            }
        });

        // select all records which are showing in list
        $("#selectall").click(function (ev) {
            var is_checked = $(this).is(':checked');
            $('#table-4 tbody tr').each(function (i, el) {
                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                }
            });
        });
        // select single record which row is clicked
        $('#table-4 tbody').on('click', 'tr', function () {
            if (checked == '') {
                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            }
        });

        /*
        $('#Status').change(function() {
             if ($(this).is(":checked")) {
                data_table.fnFilter(1,0);  // 1st value 2nd column index
            } else {
                data_table.fnFilter(0,0);
            } 
        });*/

        $("#reseller_filter").submit(function(e) {
            e.preventDefault();

			$searchFilter.ResellerName = $("#reseller_filter [name='ResellerName']").val();
			$searchFilter.AccountID = $("#reseller_filter [name='AccountID']").val();
			$searchFilter.Status = $("#reseller_filter [name='Status']").val();
            //$searchFilter.Status = $("#reseller_filter [name='Status']").prop("checked");

            data_table.fnFilter('', 0);
            return false;
        });

        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Highlighted rows
        $("#table-2 tbody input[type=checkbox]").each(function (i, el) {
            var $this = $(el),
                $p = $this.closest('tr');

            $(el).on('change', function () {
                var is_checked = $this.is(':checked');

                $p[is_checked ? 'addClass' : 'removeClass']('highlight');
            });
        });

        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

        $('#add-reseller').click(function(e) {
           $("#add-new-reseller-form [name='AccountID']").removeAttr("disabled");
            $('#add-new-reseller-form [name="AllowWhiteLabel"]').prop('checked',false);
            $('#copy_data').show();
        });

        $('table tbody').on('click','.edit-reseller',function(ev){
            ev.preventDefault();
            ev.stopPropagation();
            $('#add-new-reseller-form').trigger("reset");

            ResellerName = $(this).prev("div.hiddenRowData").find("input[name='ResellerName']").val();
            AccountID = $(this).prev("div.hiddenRowData").find("input[name='AccountID']").val();
            FirstName = $(this).prev("div.hiddenRowData").find("input[name='FirstName']").val();
            LastName = $(this).prev("div.hiddenRowData").find("input[name='LastName']").val();
            Email = $(this).prev("div.hiddenRowData").find("input[name='Email']").val();
            Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
            AllowWhiteLabel = $(this).prev("div.hiddenRowData").find("input[name='AllowWhiteLabel']").val();

            getDomainUrl($(this).attr('data-id'));
			/*
            if(Status == 1 ){
                $('#add-new-reseller-form [name="Status"]').prop('checked',true);
            }else{
                $('#add-new-reseller-form [name="Status"]').prop('checked',false);
            }*/

            $("#add-new-reseller-form [name='ResellerName']").val(ResellerName);            
            $("#add-new-reseller-form [name='FirstName']").val(FirstName);
            $("#add-new-reseller-form [name='LastName']").val(LastName);
            $("#add-new-reseller-form [name='Email']").val(Email);
            $("#add-new-reseller-form [name='Status']").val(Status);
            if(AllowWhiteLabel == 1 ){
                $('#add-new-reseller-form [name="AllowWhiteLabel"]').prop('checked',true);
            }else{
                $('#add-new-reseller-form [name="AllowWhiteLabel"]').prop('checked',false);
            }
            $("#add-new-reseller-form [name='AccountID']").select2().select2('val',AccountID);
            $("#add-new-reseller-form [name='UpdateAccountID']").val(AccountID);
            $("#add-new-reseller-form [name='ResellerID']").val($(this).attr('data-id'));

            //account disabled when edit
            $("#add-new-reseller-form [name='AccountID']").attr("disabled","disabled");
            //hide copy data when edit
            $('#copy_data').hide();
            $('#add-new-modal-reseller h4').html('Edit Reseller');
            setTimeout(function(){
                $('#add-new-modal-reseller').modal('show');
            },10);

        })

        $('#copy-resellerdata').on('click', function(e){
            e.preventDefault();
            var criteria = '';
            if ($('#selectallbutton').is(':checked')) {
                criteria = JSON.stringify($searchFilter);
            }
            var ResellerIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                //console.log($(this).val());
                ResellerID = $(this).val();
                if (typeof ResellerID != 'undefined' && ResellerID != null && ResellerID != 'null') {
                    ResellerIDs[i++] = ResellerID;
                }
                if (ResellerIDs.length) {
                    //$('#selected-reseller-copy-form').reset();
                    $(".multi-select").multiSelect('deselect_all').trigger('change');
                    //$(".multi-select").multiSelect('destroy').trigger('change');
                    //$(".multi-select").multiSelect('searchable').trigger('change');

                    $("#selected-reseller-copy-form").find("input[name='ResellerIDs']").val(ResellerIDs.join(","));
                    $("#selected-reseller-copy-form").find("input[name='criteria']").val(criteria);
                    //$("#selected-reseller-copy-form").find("input[name='reseller-item']").val('');
//                    $("#selected-reseller-copy-form").find("input[name='reseller-item']").multiSelect('deselect_all');
                    $('#selected-reseller-copy').modal('show');
                    //$("#selected-reseller-copy-form [name='InvoiceStatus']").select2().select2('val', '');
                    //$("#selected-reseller-copy-form [name='CancelReason']").val('');
                    //$('#statuscancel').hide();
                }
            });
        });

        function getDomainUrl(id){
            var domain_url;
            var result='';
            domain_url = baseurl + "/reseller/getdomainurl/"+id;
            $.get(domain_url, function (response) {
                $("#add-new-reseller-form [name='DomainUrl']").val(response.DomainUrl);
            });
        }

    });

</script>
<style>
    #selectcheckbox{
        padding: 15px 10px;
    }
</style>
@include('reseller.resellermodal')
@stop
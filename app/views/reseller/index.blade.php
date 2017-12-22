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
                <div class="form-group">
                    <label for="field-1" class="control-label">Reseller Name</label>
                    {{ Form::text('ResellerName', '', array("class"=>"form-control")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Account Name</label>
                    {{ Form::select('AccountID', Account::getAccountList(), '', array("class"=>"select2","data-allow-clear"=>"true")) }}
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
@endif
</p>

<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>        
        <th>Reseller Name</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
 

    </tbody>
</table>

<script type="text/javascript">
    var $searchFilter = {};
    jQuery(document).ready(function ($) {

        $('#filter-button-toggle').show();

        $searchFilter.ResellerName = $("#reseller_filter [name='ResellerName']").val();
        $searchFilter.AccountID = $("#reseller_filter [name='AccountID']").val();
        $searchFilter.Status = $("#reseller_filter [name='Status']").val();
        //$searchFilter.Status = $("#reseller_filter [name='Status']").prop("checked");

        data_table = $("#table-4").dataTable({

            "bProcessing":true,
            "bServerSide":true,
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sAjaxSource": baseurl + "/reseller/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "aaSorting"   : [[0, 'asc']],
            "fnServerParams": function(aoData) {
                aoData.push({"name":"ResellerName","value":$searchFilter.ResellerName},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"Status","value":$searchFilter.Status});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"ResellerName","value":$searchFilter.ResellerName},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"Status","value":$searchFilter.Status},{ "name": "Export", "value": 1});
            },
            "aoColumns": 
             [
                { "bSortable": true }, //Name
                 { "bSortable": true }, //FirstName
                 { "bSortable": true }, //lastName
                 { "bSortable": true }, //Email
                 //{ "bVisible": false, "bSortable": true  }, //Status
                 {
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_, delete_ ;
                        action = '<div class = "hiddenRowData" >';
                        action += '<input type = "hidden"  name = "ResellerName" value = "' + (full[0] != null ? full[0] : '') + '" / >';
                        action += '<input type = "hidden"  name = "FirstName" value = "' + (full[1] != null ? full[1] : '') + '" / >';
                        action += '<input type = "hidden"  name = "LastName" value = "' + (full[2] != null ? full[2] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Email" value = "' + (full[3] != null ? full[3] : '') + '" / >';
                        action += '<input type = "hidden"  name = "AccountID" value = "' + (full[4] != null ? full[4] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Status" value = "' + (full[5] != null ? full[5] : 0) + '" / >';
                        action += '<input type = "hidden"  name = "CompanyID" value = "' + (full[6] != null ? full[6] : 0) + '" / >';
                        action += '<input type = "hidden"  name = "ChildCompanyID" value = "' + (full[7] != null ? full[7] : 0) + '" / >';
                        action += '<input type = "hidden"  name = "ResellerID" value = "' + (full[8] != null ? full[8] : 0) + '" / >';
                        action += '<input type = "hidden"  name = "AccountName" value = "' + (full[9] != null ? full[9] : 0) + '" / >';
                        action += '</div>';
                        <?php if(User::checkCategoryPermission('Reseller','Edit')){ ?>
                                action += ' <a data-name = "'+full[0]+'" data-id="'+ full[8] +'" title="Edit" class="edit-reseller btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('Reseller','Delete')){ ?>
                                action += ' <a data-id="'+ full[8] +'" title="Delete" class="delete-reseller btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
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
								data_table.fnFilter(1,0);
								/*
                                if ($('#Status').is(":checked")) {
                                    data_table.fnFilter(1,0);  // 1st value 2nd column index
                                } else {
                                    data_table.fnFilter(0,0);
                                }*/
                                toastr.success(response.message, "Success", toastr_opts);
                            } else {
								data_table.fnFilter(1,0);
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

        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

        $('#add-reseller').click(function(e) {
           $("#add-new-reseller-form [name='AccountID']").removeAttr("disabled");
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
            $("#add-new-reseller-form [name='AccountID']").select2().select2('val',AccountID);
            $("#add-new-reseller-form [name='ResellerID']").val($(this).attr('data-id'));
            $("#add-new-reseller-form [name='AccountID']").attr("disabled","disabled");
            $('#add-new-modal-reseller h4').html('Edit Reseller');
            $('#add-new-modal-reseller').modal('show');
        })

    });

</script>
@include('reseller.resellermodal')
@stop
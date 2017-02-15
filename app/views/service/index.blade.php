@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Service</strong>
    </li>
</ol>
<h3>Services</h3>
<p class="text-right">
@if(User::checkCategoryPermission('Service','Add'))
    <a href="#" data-action="showAddModal" data-type="service" data-modal="add-new-modal-service" class="btn btn-primary">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>
<div class="form-group">
    <label class="control-label">Status</label>
        <p class="make-switch switch-small mar-left-5 mar-top-5" >
            <input id="ServiceStatus" type="checkbox" checked>
            <input id="ServiceRefresh" type="hidden" value="1">
        </p>
</div>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th>Status</th>
        <th>Title</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
 

    </tbody>
</table>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        data_table = $("#table-4").dataTable({

            "bProcessing":true,
            "bServerSide":true,
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sAjaxSource": baseurl + "/services/ajax_datagrid",
            "iDisplayLength": parseInt('{{Config::get('app.pageSize')}}'),
            "sPaginationType": "bootstrap",
            "aaSorting"   : [[5, 'desc']],    
            "aoColumns": 
             [
                { "bVisible": false, "bSortable": true  },
                { "bSortable": true },
                {
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_, delete_ ;
                        action = '<div class = "hiddenRowData" >';
                        action += '<input type = "hidden"  name = "ServiceName" value = "' + (full[1] != null ? full[1] : '') + '" / >';
                        action += '<input type = "hidden"  name = "ServiceType" value = "' + (full[3] != null ? full[3] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Status" value = "' + (full[0] != null ? full[0] : 0) + '" / ></div>';
                        <?php if(User::checkCategoryPermission('Service','Edit')){ ?>
                                action += ' <a data-name = "'+full[1]+'" data-id="'+ full[2] +'" title="Edit" class="edit-service btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('Service','Delete')){ ?>
                                action += ' <a data-id="'+ full[2] +'" title="Delete" class="delete-service btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
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
                    "sUrl": baseurl + "/services/exports/xlsx",
                    sButtonClass: "save-collection btn-sm"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/services/exports/csv",
                    sButtonClass: "save-collection btn-sm"
                }
                ]
            },
            "fnDrawCallback": function() {
                $(".delete-service.btn").click(function(ev) {
                    response = confirm('Are you sure?');
                    if (response) {
                        var clear_url;
                        var id  = $(this).attr("data-id");
                        clear_url = baseurl + "/services/delete/"+id;
                        $(this).button('loading');
                        //get
                        $.get(clear_url, function (response) {
                            if (response.status == 'success') {
                                $(this).button('reset');
                                if ($('#ServiceStatus').is(":checked")) {
                                    data_table.fnFilter(1,0);  // 1st value 2nd column index
                                } else {
                                    data_table.fnFilter(0,0);
                                }
                                toastr.success(response.message, "Success", toastr_opts);
                            } else {
                                if ($('#ServiceStatus').is(":checked")) {
                                    data_table.fnFilter(1,0);  // 1st value 2nd column index
                                } else {
                                    data_table.fnFilter(0,0);
                                }
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        });
                    }
                    return false;


                });
            }
        });
        $('#ServiceStatus').change(function() {
             if ($(this).is(":checked")) {
                data_table.fnFilter(1,0);  // 1st value 2nd column index
            } else {
                data_table.fnFilter(0,0);
            } 
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

        $('table tbody').on('click','.edit-service',function(ev){
            ev.preventDefault();
            ev.stopPropagation();
            $('#add-new-service-form').trigger("reset");

            ServiceName = $(this).prev("div.hiddenRowData").find("input[name='ServiceName']").val();
            ServiceType = $(this).prev("div.hiddenRowData").find("input[name='ServiceType']").val();
            Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
            if(Status == 1 ){
                $('#add-new-service-form [name="Status"]').prop('checked',true);
            }else{
                $('#add-new-service-form [name="Status"]').prop('checked',false);
            }

            $("#add-new-service-form [name='ServiceName']").val(ServiceName);
            $("#add-new-service-form [name='ServiceType']").select2().select2('val',ServiceType);
            $("#add-new-service-form [name='ServiceID']").val($(this).attr('data-id'));
            $('#add-new-modal-service h4').html('Edit Service');
            $('#add-new-modal-service').modal('show');
        })

    });

</script>
@include('service.servicemodal')
@stop
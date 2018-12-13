@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="service_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    {{ Form::text('ServiceName', '', array("class"=>"form-control")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Service</label>
                    {{ Form::select('ServiceId',Service::getDropdownIDList(),'', array("class"=>"select2 small")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Currency</label><br/>
                        {{ Form::select('FilterCurrencyId',Currency::getCurrencyDropdownIDList(),'', array("class"=>"select2 small")) }}
                        <input id="ServiceRefresh" type="hidden" value="1">
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
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Service Template</strong>
    </li>
</ol>
<h3>Service Template</h3>
<p class="text-right">
@if(User::checkCategoryPermission('Service','Add'))
    <a href="#" data-action="showAddModal" data-type="service" data-modal="add-new-modal-service" class="btn btn-primary">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>

<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th>Status</th>
        <th>Name</th>
        <th>Service Name</th>
        <th>Currency</th>
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




        data_table = $("#table-4").dataTable({

            "bProcessing":true,
            "bServerSide":true,
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "sAjaxSource": baseurl + "/servicesTemplate/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "aaSorting"   : [[5, 'desc']],
            "fnServerParams": function(aoData) {
                //alert("Called1");
                $searchFilter.ServiceName = $("#service_filter [name='ServiceName']").val();
                $searchFilter.ServiceId = $("#service_filter [name='ServiceId']").val();
                $searchFilter.FilterCurrencyId = $("#service_filter [name='FilterCurrencyId']").val();
                //alert($searchFilter.ServiceId);//{"name":"sSearch_0","value":""}
                aoData.push({"name":"ServiceName","value":$searchFilter.ServiceName},{"name":"sSearch_0","value":""},{"name":"ServiceId","value":$searchFilter.ServiceId},{"name":"FilterCurrencyId","value":$searchFilter.FilterCurrencyId});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"ServiceName","value":$searchFilter.ServiceName},{"name":"sSearch_0","value":""},{"name":"ServiceId","value":$searchFilter.ServiceId},{"name":"FilterCurrencyId","value":$searchFilter.FilterCurrencyId},{ "name": "Export", "value": 1});
            },
            "aoColumns": 
             [
                { "bVisible": false, "bSortable": true  }, //Status
                { "bSortable": true }, //Name
                { "bSortable": true }, //Type
                { "bSortable": true }, //Gateway
                {
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_, delete_ ;
                        //alert("Called2");
                        //alert(full);
                        action = '<div class = "hiddenRowData"  >';

                        action += '<input type = "hidden"  name = "ServiceName" value = "' + (full[1] != null ? full[1] : '') + '" / >';
                        action += '<input type = "hidden"  name = "CurrencyID" value = "' + (full[6] != null ? full[6] : '') + '" / >';
                        action += '<input type = "hidden"  name = "ServiceId" value = "' + (full[0] != null ? full[0] : '') + '" / >';
                        action += '<input type = "hidden"  name = "OutboundDiscountPlanID" value = "' + (full[8] != null ? full[8] : '') + '" / >';
                        action += '<input type = "hidden"  name = "InboundDiscountPlanID" value = "' + (full[7] != null ? full[7] : '') + '" / >';
                        action += '<input type = "hidden"  name = "OutboundTariffId" value = "' + (full[4] != null ? full[4] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Status" value = "" / ></div>';
                        <?php if(User::checkCategoryPermission('Service','Edit')){ ?>
                                action += ' <a data-name = "'+full[1]+'" data-id="'+ full[5] +'" title="Edit" class="edit-service btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('Service','Delete')){ ?>
                                action += ' <a data-id="'+ full[5] +'" title="Delete" class="delete-service btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
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
                    "sUrl": baseurl + "/servicesTemplate/exports/xlsx",
                    sButtonClass: "save-collection btn-sm"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/servicesTemplate/exports/csv",
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
                        clear_url = baseurl + "/servicesTemplate/delete/"+id;
                        //alert(clear_url);
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
        /*
        $('#ServiceStatus').change(function() {
             if ($(this).is(":checked")) {
                data_table.fnFilter(1,0);  // 1st value 2nd column index
            } else {
                data_table.fnFilter(0,0);
            } 
        });*/

        $("#service_filter").submit(function(e) {
            e.preventDefault();

            $searchFilter.ServiceName = $("#service_filter [name='ServiceName']").val();
            $searchFilter.CompanyGatewayID = $("#service_filter [name='CompanyGatewayID']").val();
            $searchFilter.ServiceStatus = $("#service_filter [name='ServiceStatus']").prop("checked");

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

        $('table tbody').on('click','.edit-service',function(ev){
            ev.preventDefault();
            ev.stopPropagation();
            $('#add-new-service-form').trigger("reset");
            var id  = $(this).attr("data-id");
            ServiceTemplateName = $(this).prev("div.hiddenRowData").find("input[name='ServiceName']").val();
            CurrencyID = $(this).prev("div.hiddenRowData").find("input[name='CurrencyID']").val();

            ServiceId = $(this).prev("div.hiddenRowData").find("input[name='ServiceId']").val();
            CompanyGatewayID = $(this).prev("div.hiddenRowData").find("input[name='CompanyGatewayID']").val();
            Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
            if(Status == 1 ){
                $('#add-new-service-form [name="Status"]').prop('checked',true);
            }else{
                $('#add-new-service-form [name="Status"]').prop('checked',false);
            }

            $("#add-new-service-form [name='Name']").val(ServiceTemplateName);
            $("#add-new-service-form [name='CurrencyId']").select2().select2('val',CurrencyID);
            $("#add-new-service-form [name='CurrencyId']").prop('disabled',true);//disabled="true"
            loadValuesBasedOnCurrency(CurrencyID,true);
            editSelectedTemplateSubscription(CurrencyID,id);
           // alert(ServiceId);
            $("#add-new-service-form [name='ServiceId']").select2().select2('val',ServiceId);
            $("#add-new-service-form [name='CompanyGatewayID']").select2().select2('val',CompanyGatewayID);
            $("#add-new-service-form [name='ServiceID']").val($(this).attr('data-id'));
            $('#add-new-modal-service  Service Template');
            $('#add-new-modal-service h5').html('Edit Service Template');
            $('#add-new-modal-service').modal('show');


        })

    });

</script>
@include('servicetemplate.servicetemplatemodal')
@stop
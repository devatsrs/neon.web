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
                    <label for="field-1" class="control-label">Country</label>
                    {{ Form::select('CountryID', $country,'', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Access Type</label>
                    {{ Form::select('AccessType', $AccessType, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Prefix</label>
                    {{ Form::select('Prefix', $Prefix, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">City</label>
                    {{ Form::select('City', $City, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Tariff</label>
                    {{ Form::select('Tariff', $Tariff, '', array("class"=>"select2")) }}
                </div>

               
                {{--<div class="form-group">--}}
                    {{--<label for="field-1" class="control-label">Currency</label><br/>--}}
                        {{--{{ Form::select('FilterCurrencyId',Currency::getCurrencyDropdownIDList(),'', array("class"=>"select2 small")) }}--}}
                        {{--<input id="ServiceRefresh" type="hidden" value="1">--}}
                {{--</div>--}}
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
        <strong>Product</strong>
    </li>
</ol>
<h3>Product</h3>
<p class="text-right">
@if(User::checkCategoryPermission('SubscriptionTemplate','Add'))
    <a href="#" data-action="showAddServiceTemplateModal" data-type="service" data-modal="add-new-modal-service" class="btn btn-primary">
        <i class="entypo-plus"></i>
        Add New
    </a>

    <a href="#" id="bulkActions" data-action="showAddbulkActionServiceTemplateModal" data-type="bulkAction" data-modal="add-new-BulkAction-modal-service" class="btn btn-primary">
            <i class="entypo-plus"></i>
            Bulk Actions
    </a>
@endif
    @if(User::checkCategoryPermission('SubscriptionTemplate','Add'))
        <a href="{{  URL::to('servicetempaltes/servicetemplatetype') }}" class="btn btn-primary pull-right" style="margin-right:2px;">
            <i class="glyphicon glyphicon-th"></i>
            Dynamic Fields
        </a>
    @endif
</p>

<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
        <th>Status</th>
        <th>Name</th>
        <th>Service Name</th>
        <th>Country</th>
        <th>Prefix</th>
        <th>Access Type</th>
        <th>City</th>
        <th>Tariff</th>
        {{--<th>Currency</th>--}}
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
 

    </tbody>
</table>

<script type="text/javascript">
    var checkBoxArray =[];

    function resetFormFields() {
        document.getElementById("SubscriptionIDListBody").innerHTML="";
        document.getElementById('categoryTariffIDListBody').innerHTML="";
        document.getElementById("selectedSubscription").value="";
        document.getElementById("selectedcategotyTariff").value="";
        document.getElementById("DidCategoryTariffID").innerHTML = "";


        document.getElementById("tab1").setAttribute("class", "active");
        document.getElementById("tab2").setAttribute("class", "");

        // document.getElementById("ContentSubscriptionTab").innerHTML = "";
       // document.getElementById("ContentInboundTariffTab").innerHTML= "";

        saveSelectedCategoryTariff="";
        saveSelectedSubscription="";
        SubscriptionIDListBody = "";
        categoryTariffIDListBody = "";
        saveDidCategoryTariffID = "";
        $("#add-new-service-form [name='CurrencyId']").prop('disabled',false);//disabled="true"
        $("#add-new-service-form [name='ServiceId']").select2().select2('val','');
        $("#add-new-service-form [name='ContractDuration']").val("");
        $("#add-new-service-form [name='AutomaticRenewal']").prop("checked", true).trigger("change");
        $("#add-new-service-form [name='CancellationCharges']").trigger("reset");
        $("#add-new-service-form [name='CancellationCharges']:first").prop("checked", true);
        $("#add-new-service-form [name='CancellationFee']").val("");
        $("#add-new-service-form [name='OutboundDiscountPlanId']").select2().select2('val','');
        $("#add-new-service-form [name='InboundDiscountPlanId']").select2().select2('val','');
        $("#add-new-service-form [name='OutboundRateTableId']").select2().select2('val','');
    }
    $(document).on('click','[data-action="showAddServiceTemplateModal"]' ,function(e) {
        //alert("Called");
        resetFormFields();
        document.getElementById('ajax_dynamicfield_html').innerHTML= "";
        e.preventDefault();
        var self = $(this);
        var modal = $('#'+self.attr('data-modal'));
        var forms = modal.find('form');
        forms.each(function(index,form){
            resetForm($(form),self.attr('data-type'));
        });
        hideCancelCollapse();
       // alert(document.getElementById("SubscriptionIDListBody").innerHTML);
       // alert(document.getElementById('categoryTariffIDListBody').innerHTML);
       // alert(categoryTariffIDListBody);
        modal.modal('show');
       // modal.find('h4').html("Add New"+getTitle(self.attr('data-type')));
        $('#add-new-modal-service h5').html('Add Product');
        $.ajax({
            type: "POST",
            url: "servicetempaltes/servicetemplatetype/dynamicField/fieldAccess",
            cache: false,
            success: function(response){
                console.info(response);
                $('#ajax_dynamicfield_html').html(response);
                //perform operation
            },
            error: function(error) {

                $('#ajax_dynamicfield_html').html('');
                $(".btn").button('reset');
                ShowToastr("error", error);
            }
        });
    });

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
            "aaSorting"   : [[10, 'desc']],
            "fnServerParams": function(aoData) {
                //alert("Called1");
                $searchFilter.ServiceName = $("#service_filter [name='ServiceName']").val();
                $searchFilter.ServiceId = $("#service_filter [name='ServiceId']").val();
                $searchFilter.FilterCurrencyId = $("#service_filter [name='FilterCurrencyId']").val();
                $searchFilter.CountryID = $("#service_filter [name='CountryID']").val();
                $searchFilter.AccessType = $("#service_filter [name='AccessType']").val();
                $searchFilter.Prefix = $("#service_filter [name='Prefix']").val();
                $searchFilter.City = $("#service_filter [name='City']").val();
                $searchFilter.Tariff = $("#service_filter [name='Tariff']").val();

                



                //alert($searchFilter.ServiceId);//{"name":"sSearch_0","value":""}
                aoData.push({"name":"ServiceName","value":$searchFilter.ServiceName},{"name":"sSearch_0","value":""},{"name":"ServiceId","value":$searchFilter.ServiceId},{"name":"FilterCurrencyId","value":$searchFilter.FilterCurrencyId},{"name":"CountryID","value":$searchFilter.CountryID},{"name":"AccessType","value":$searchFilter.AccessType},{"name":"Prefix","value":$searchFilter.Prefix},{"name":"City","value":$searchFilter.City},{"name":"Tariff","value":$searchFilter.Tariff});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"ServiceName","value":$searchFilter.ServiceName},{"name":"CountryID","value":$searchFilter.CountryID},{"name":"AccessType","value":$searchFilter.AccessType},{"name":"Prefix","value":$searchFilter.Prefix},{"name":"City","value":$searchFilter.City},{"name":"Tariff","value":$searchFilter.Tariff},{"name":"sSearch_0","value":""},{"name":"ServiceId","value":$searchFilter.ServiceId},{"name":"FilterCurrencyId","value":$searchFilter.FilterCurrencyId},{ "name": "Export", "value": 1});
            },
            "aoColumns": 
             [
                 {"bSortable": false,
                     mRender: function(id, type, full) {
                         // checkbox for bulk action
                         return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                     }
                 },
                {"bSortable": true, "bVisible": false   }, //Status
                { "bSortable": true }, //Name
                { "bSortable": true }, //Type
                 { "bSortable": true }, //Country
                 { "bSortable": true }, //Prefix
                 { "bSortable": true }, //Type
                 { "bSortable": true }, //City
                 { "bSortable": true }, //Tariff
//                { "bSortable": true }, //Gateway
                {
                   "bSortable": false,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_, delete_ ;
                        //alert("Called2");
                        //alert(full);
                        resetFormFields();
                        action = '<div class = "hiddenRowData"  >';
                        action += '<input type = "hidden"  name = "ServiceTemplateId" value = "' + (full[0] != null ? full[0] : 0) + '" / >';
                        action += '<input type = "hidden"  name = "ServiceId" value = "' + (full[1] != null ? full[1] : '') + '" / >';
                        action += '<input type = "hidden"  name = "OutboundTariffId" value = "' + (full[9] != null ? full[9] : '') + '" / >';
                        action += '<input type = "hidden"  name = "ServiceName" value = "' + (full[2] != null ? full[2] : '') + '" / >';
                        action += '<input type = "hidden"  name = "CurrencyID" value = "' + (full[10] != null ? full[10] : '') + '" / >';
                        action += '<input type = "hidden"  name = "OutboundDiscountPlanID" value = "' + (full[12] != null ? full[12] : '') + '" / >';
                        action += '<input type = "hidden"  name = "InboundDiscountPlanID" value = "' + (full[11] != null ? full[11] : '') + '" / >';
                        action += '<input type = "hidden"  name = "PackageDiscountPlanId" value = "' + (full[17] != null ? full[17] : '') + '" / >';
                        action += '<input type = "hidden"  name = "ContractDuration" value = "' + (full[13] != null ? full[13] : '') + '" / >';
                        action += '<input type = "hidden"  name = "AutomaticRenewal" value = "' + (full[14] != null ? full[14] : '') + '" / >';
                        action += '<input type = "hidden"  name = "CancellationCharges" value = "' + (full[15] != null ? full[15] : '') + '" / >';
                        action += '<input type = "hidden"  name = "CancellationFee" value = "' + (full[16] != null ? full[16] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Status" value = "" / ></div>';
                        <?php if(User::checkCategoryPermission('SubscriptionTemplate','Edit')){ ?>
                                action += ' <a data-name = "'+full[1]+'" data-id="'+ full[0] +'" title="Edit" class="edit-service btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('SubscriptionTemplate','Delete')){ ?>
                                action += ' <a data-id="'+ full[0] +'" title="Delete" class="delete-service btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
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
            $searchFilter.CountryID = $("#service_filter [name='CountryID']").val();
            $searchFilter.AccessType = $("#service_filter [name='AccessType']").val();
            $searchFilter.Prefix = $("#service_filter [name='Prefix']").val();
            $searchFilter.City = $("#service_filter [name='City']").val();
            $searchFilter.Tariff = $("#service_filter [name='Tariff']").val();

            data_table.fnFilter('', 0);
            return false;
        });

        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        $("#selectall").click(function (ev) {
            var is_checked = $(this).is(':checked');

            if(checkBoxArray != null || checkBoxArray == undefined)
               checkBoxArray = [];

            $('#table-4 tbody tr').each(function (i, el) {
                var txtValue = $(this).find('.rowcheckbox').prop("checked", true).val();

                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                        console.log(txtValue);
                        if(txtValue)
                          checkBoxArray.push(txtValue);

                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                        checkBoxArray = [];
                    }
                }
            });
        });
        // select single record which row is clicked
        $('#table-4 tbody').on('click', 'tr', function () {

            var txtValue = $(this).find('.rowcheckbox').prop("checked", true).val();
            var checked = $(this).is(':checked')
            if (checked == '') {
                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        checkBoxArray.push(txtValue);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        checkBoxArray.pop(txtValue);

                    }
                }
            }
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
            resetFormFields();
            ev.preventDefault();
            ev.stopPropagation();
            $('#add-new-service-form').trigger("reset");
            var id  = $(this).attr("data-id");
            ServiceTemplateName = $(this).prev("div.hiddenRowData").find("input[name='ServiceName']").val();
            CurrencyID = $(this).prev("div.hiddenRowData").find("input[name='CurrencyID']").val();

            var ServiceId = $(this).prev("div.hiddenRowData").find("input[name='ServiceId']").val();
             var OutboundDiscountPlanID1= $(this).prev("div.hiddenRowData").find("input[name='OutboundDiscountPlanID']").val();
             var InboundDiscountPlanID1= $(this).prev("div.hiddenRowData").find("input[name='InboundDiscountPlanID']").val();
             var PackageDiscountPlanId1= $(this).prev("div.hiddenRowData").find("input[name='PackageDiscountPlanId']").val();
            var OutboundTariffId= $(this).prev("div.hiddenRowData").find("input[name='OutboundTariffId']").val();
            var ContractDuration= $(this).prev("div.hiddenRowData").find("input[name='ContractDuration']").val();
            var AutomaticRenewal= $(this).prev("div.hiddenRowData").find("input[name='AutomaticRenewal']").val();
            var CancellationCharges= $(this).prev("div.hiddenRowData").find("input[name='CancellationCharges']").val();
            var CancellationFee= $(this).prev("div.hiddenRowData").find("input[name='CancellationFee']").val();
            CompanyGatewayID = $(this).prev("div.hiddenRowData").find("input[name='CompanyGatewayID']").val();
            Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
            if(Status == 1 ){
                $('#add-new-service-form [name="Status"]').prop('checked',true);
            }else{
                $('#add-new-service-form [name="Status"]').prop('checked',false);
            }

            $("#add-new-service-form [name='Name']").val(ServiceTemplateName);
            $("#add-new-service-form [name='ContractDuration']").val(ContractDuration);
            $("#add-new-service-form [name='CancellationFee']").val(CancellationFee);
            $("#add-new-service-form [name='CancellationCharges'][value='" + CancellationCharges+"']").prop("checked", true).trigger("change");
            $("#add-new-service-form [name='AutomaticRenewal']").prop(':checked', AutomaticRenewal == 1).trigger('change');
            loadValuesBasedOnCurrency(CurrencyID,true);
            editSelectedTemplateSubscription(CurrencyID,id);
            $("#add-new-service-form [name='ServiceId']").select2().select2('val',ServiceId);
            $("#add-new-service-form [name='PackageDiscountPlanId']").select2().select2('val',PackageDiscountPlanId1);
            $("#add-new-service-form [name='InboundDiscountPlanID123']").select2().select2('val',InboundDiscountPlanID1);
            $("#add-new-service-form [name='OutboundRateTableId']").select2().select2('val',OutboundTariffId);
            $("#add-new-service-form [name='OutboundDiscountPlanID123']").select2().select2('val',OutboundDiscountPlanID1);
            $("#add-new-service-form [name='CompanyGatewayID']").select2().select2('val',CompanyGatewayID);
            $("#add-new-service-form [name='ServiceID']").val($(this).attr('data-id'));
            //$('#add-new-modal-service  Service Template');
            document.getElementById('ajax_dynamicfield_html').innerHTML= "";
            $('#add-new-modal-service h5').html('Edit Product');
            document.getElementById("ActiveTabContent").innerHTML = document.getElementById("ContentSubscriptionTab").innerHTML;

            hideCancelCollapse();

            $.ajax({
                type: "GET",
                url: "servicetempaltes/servicetemplatetype/dynamicField/typesAccess/"+id,
                cache: false,
                success: function(response){
                    console.info(response);
                    $('#ajax_dynamicfield_html').html(response);
                    //perform operation
                },
                error: function(error) {
                    $('#ajax_dynamicfield_html').html('');
                    $(".btn").button('reset');
                    ShowToastr("error", error);
                }
            });


            $('#add-new-modal-service').modal('show', {backdrop: 'static'});

            return false;

        });


        $("#bulkActions").click(function(){


            _currency = $("#service_filter [name='FilterCurrencyId']").val()
            url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + _currency + "&selectedData=outboundTariff";
            $.post(url, function (data, status) {
                // var res = data.split('/>');
                document.getElementById("OutboundRateTableIdBulkAction").innerHTML = "" + data;
                // var OutboundTariffId = $("div.hiddenRowData").find("input[name='OutboundTariffId']").val();
//                if (OutboundTariffId != '') {
//                    $("#add-action-bulk-form [name='OutboundRateTableIdBulkAction']").select2().select2('val', OutboundTariffId);
//                }else {
//                    $("#add-action-bulk-form [name='OutboundRateTableIdBulkAction']").select2().select2('val', '');
//                }

                // $("#serviceBasedOnCurreny").html(data);
            }, 'html');


            document.getElementById("add-action-bulk-form").reset();
            $("#InboundTariff").val("");

            if($("#service_filter [name='FilterCurrencyId']").val() != "" && checkBoxArray != "")
            {
                $('#BulkServiceTemplateModelTitle').text('Bulk Action');
                var GetCurrencyId = $("#service_filter [name='FilterCurrencyId']").val();
                $("#CurrencyIdBulkAction").val(GetCurrencyId);
                $("#ServiceTemplateIdBulkAction").val(checkBoxArray);
                $("#add-new-BulkAction-modal-service input:checkbox").prop("checked",false);
                $("#OutboundRateTableIdBulkAction").prop("disabled",true);
                $("#OutboundDiscountPlanIdBulkAction").prop("disabled",true);
                $("#InboundDiscountPlanIdBulkAction").prop("disabled",true);
                $("#ServiceIdBulkAction").prop("disabled",true);
                $("#DidCategoryIDBulkAction").prop("disabled","disab");
                $("#DidCategoryTariffIDBulkAction").prop("disabled",true);

                $( "#add-action-bulk-form").children('select').find('option:eq(0)').prop('selected', true);
                document.getElementById("selectedcategotyTariffBulkAction").value = "";
                document.getElementById("categoryTariffIDListBodyBulkAction").innerHTML = "";
                $( "#ServiceIdBulkAction").select2().select2('val',1);



            }else{

                if(checkBoxArray == "")
                    ShowToastr("error", "Please select any rows");
                return false;

            }


            var selected_company, data, url;

            selected_currency = $("#CurrencyIdBulkAction").val();
            selected_didCategory = $("#DidCategoryIDBulkAction").val();
            DidCategoryIndexValue = document.getElementById("DidCategoryIDBulkAction").selectedIndex;

            if(selected_currency) {
                if (selected_currency == '') {
                    selected_currency = "NAN";
                }
                data = {company: selected_company};

                url = baseurl + "/servicesTemplate/selectDataOnCurrency" +
                        "?selectedCurrency=" + selected_currency + "&selectedData=DidCategoryID&selected_didCategory=" + selected_didCategory;

                $.post(url, function (data, status) {
                    //  var res = data.split('/>');
                    document.getElementById("DidCategoryTariffIDBulkAction").innerHTML = "" + data;
                    DidCategoryTariffIDBulkAction = document.getElementById("DidCategoryTariffIDBulkAction").innerHTML;
                    // $("#serviceBasedOnCurreny").html(data);
                }, 'html');

            }

            $('#add-new-BulkAction-modal-service').modal('show', {backdrop: 'static'});

        });


        $('#add-action-bulk-form').submit(function(e){
            //add-action-bulk-form
           update_new_url = baseurl + '/servicesTemplate/addBulkAction';
            var data = new FormData(($('#add-action-bulk-form')[0]));

            showAjaxScript(update_new_url, data, function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {

                    $('#add-new-BulkAction-modal-service').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    var dataTableName = $("#table-4").dataTable();
                    dataTableName.fnDraw();
                    $("#add-new-BulkAction-modal-service [name='CurrencyId']").attr('checked',false);

                    $("#add-new-BulkAction-modal-service input:checkbox").prop("checked",false);

                    $( "#add-action-bulk-form").children('select').find('option:eq(0)').prop('selected', true);
                    document.getElementById("selectedcategotyTariffBulkAction").value = "";
                    checkBoxArray = [];


                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
            return false;
        });

    });

    function hideCancelCollapse(){
        var panelLabels      = $('.cancelRadio label');
        var cancelField      = $(".cancellationDiv");

        panelLabels.removeClass('active');
        cancelField.hide();

        var selected = $('.cancelRadio input[type="radio"]:checked');
        selected.val(selected.data('value'))
                .parent()
                .addClass('active');
        if(selected.val() != 2 && selected.val() != 5){
            var label = selected.val() == 3 ? "Percentage" : "Fee";
            cancelField.find('label').text(label);
            cancelField.find('input[type="text"]').attr("placeholder", label);
            cancelField.show();
        }
    }
</script>

@include('servicetemplate.servicetemplatemodal')
@include('servicetemplate.bulkservicetemplatemodal')

@stop
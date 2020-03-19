@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form role="form" id="vendor-activecall-table-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>

                <div class="form-group">
                    <label class="control-label" for="field-1">Gateway Name</label>
                    <input type="text" name="GatewayName" class="form-control mid_fld "  value=""  />
                </div>

                <div class="form-group">
                    <label class="control-label" for="field-1">Prefix</label>
                    <input type="text" name="CallPrefix" class="form-control mid_fld "  value="{{Input::get('prefix')}}"  />
                </div>

                <div class="form-group">
                    <label class="control-label" for="field-1">Gateway</label>
                    {{ Form::select('CompanyGatewayID',$gateway,'', array("class"=>"select2","id"=>"bluk_CompanyGatewayID")) }}
                </div>

                <div class="form-group">
                    <label class="control-label" for="field-1">Total Current Calls</label>
                    <input type="text" name="TotalCurrentCalls" class="form-control mid_fld "   />
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
<style>
.small_fld{width:80.6667%;}
.small_label{width:5.0%;}
.col-sm-e2{width:15%;}
.small-date-input{width:11%;}
#selectcheckbox{
    padding: 15px 10px;
}
</style>
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <a href="javascript:void(0)">Active Calls</a> </li>
</ol>
<h3>Active Calls</h3>
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    @if(User::checkCategoryPermission('ActiveCall','View') && CompanyConfiguration::getValueConfigurationByKey('SIDEBAR_ACTIVECALL_MENU',User::get_companyID()) == '1')
        <li >
            <a href="{{ URL::to('ActiveCalls') }}" >
                <span class="hidden-xs">Active Calls</span>
            </a>
        </li>
    @endif

    @if(User::checkCategoryPermission('VOSActiveCall','View') &&  CompanyConfiguration::getValueConfigurationByKey('VOS_ACTIVECALL_MENU',User::get_companyID()) == '1')
        <li>
            <a href="{{ URL::to('/VOS_ActiveCalls') }}" >
                <span class="hidden-xs">Current Call</span>
            </a>
        </li>
    @endif

    <li class="active">
        <a href="{{ URL::to('/Vendor_ActiveCalls') }}" >
            <span class="hidden-xs">Online Routing Gateway</span>
        </a>
    </li>

    @if(User::checkCategoryPermission('VOSOnlineGatewayMapping','View') && CompanyConfiguration::getValueConfigurationByKey('VOS_ONLINE_GATEWAY_MAPPING_MENU',User::get_companyID()) == '1')

    <li>
        <a href="{{ URL::to('/GatewayMappingOnline') }}" >
            <span class="hidden-xs">Online Mapping Gateway</span>
        </a>
    </li>
    @endif


</ul>


    <div class="clear"></div>


<div class="row dropdown">
    <div  class="col-md-12">
        @if(CompanyConfiguration::getValueConfigurationByKey('VENDOR_ACTIVECALL_BTN_LOADACTIVECALL',User::get_companyID()) == '1')
            <a href="javascript:;" id="LoadVendorActiveCalls" class="btn upload btn-primary pull-right" data-action="{{URL::to('Vendor_ActiveCalls/API/GetGatewayRoutingOnline')}}"> Load Active Calls </a>
        @endif

    </div>
</div>
<br>
<div style="overflow-y: auto;">
    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            {{--<th width="1%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>--}}
            <th width="10%">Gateway name</th>
            <th width="8%">Prefix</th>
            <th width="10%">Total Current Calls</th>
            <th width="10%">ASR</th>
            <th width="10%">ACD</th>
            <th width="10%">Remote IP</th>

        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    </div>
    <script type="text/javascript">
        var toFixed = '{{get_round_decimal_places()}}';

                var list_fields  = ['GatewayName','CallPrefix','TotalCurrentCalls','Asr','Acd','RemoteIP','CompanyGatewayID'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {

                    $('#filter-button-toggle').show();

                    $searchFilter.GatewayName = $("#vendor-activecall-table-search [name='GatewayName']").val();
                    $searchFilter.CallPrefix = $("#vendor-activecall-table-search [name='CallPrefix']").val();

                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/Vendor_ActiveCalls/ajax_datagrid/type",
                        "fnServerParams": function (aoData) {
                            aoData.push(
                                    {"name": "GatewayName", "value": $searchFilter.GatewayName},
                                    {"name": "CallPrefix","value": $searchFilter.CallPrefix},
                                    {"name": "TotalCurrentCalls","value": $searchFilter.TotalCurrentCalls},
                                    {"name": "CompanyGatewayID","value": $searchFilter.CompanyGatewayID}

                            );
                            data_table_extra_params.length = 0;
                            data_table_extra_params.push(
                                    {"name": "GatewayName", "value": $searchFilter.GatewayName},
                                    {"name": "CallPrefix","value": $searchFilter.CallPrefix},
                                    {"name": "CompanyGatewayID","value": $searchFilter.CompanyGatewayID},
                                    {"name": "TotalCurrentCalls","value": $searchFilter.TotalCurrentCalls},
                                    {"name":"Export","value":1}
                            );

                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox1.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[0, 'asc']],
                        "aoColumns": [
                            /*{
                                "bSortable": false, //checkbox
                                mRender: function (id, type, full) {
                                    var chackbox = '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + full[0] + '" class="rowcheckbox" ></div>';
                                    if($('#Recall_on_off').prop("checked")){
                                        chackbox='';
                                    }
                                    return chackbox;
                                }
                            },*/
                            {
                                "bSortable": true //GatewayName
                            },
                            {
                                "bSortable": true //Prefix
                            },
                            {
                                "bSortable": true // TotalCurrentCall
                            },
                            {
                                "bSortable": true // ASR
                            },
                            {
                                "bSortable": true // ACD
                            },
                            {
                                "bSortable": true // RemoteIP
                            }

                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/Vendor_ActiveCalls/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                                    sButtonClass: "save-collection"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/Vendor_ActiveCalls/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                                    sButtonClass: "save-collection"
                                }
                            ]
                        },
                        "fnDrawCallback": function () {

                            $(".dataTables_wrapper select").select2({
                                minimumResultsForSearch: -1
                            });
                            if($('#Recall_on_off').prop("checked")){
                                $('#selectcheckbox').addClass('hidden');
                            }else{
                                $('#selectcheckbox').removeClass('hidden');
                            }
                            $("#table-4 tbody input[type=checkbox]").each(function (i, el) {
                                var $this = $(el),
                                        $p = $this.closest('tr');

                                $(el).on('change', function () {
                                    var is_checked = $this.is(':checked');

                                    $p[is_checked ? 'addClass' : 'removeClass']('selected');
                                });
                            });

                            $('.tohidden').removeClass('hidden');
                            $('#selectall').removeClass('hidden');
                            if($('#Recall_on_off').prop("checked")){
                                $('.tohidden').addClass('hidden');
                                $('#selectall').addClass('hidden');
                            }
                            //select all record
                            $('#selectallbutton').click(function(){
                                if($('#selectallbutton').is(':checked')){
                                    checked = 'checked=checked disabled';
                                    $("#selectall").prop("checked", true).prop('disabled', true);
                                    $('#table-4 tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    });
                                }else{
                                    checked = '';
                                    $("#selectall").prop("checked", false).prop('disabled', false);
                                    $('#table-4 tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    });
                                }
                            });
                        }

                    });
                    $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    $('#upload-payments').click(function(ev){
                        ev.preventDefault();
                        $('#upload-modal-payments').modal('show');
                    });


                    $('body').on('click', '.btn.recall,.recall', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var self = $(this);
                        var PaymentIDs =[];
                        $('#recall-payment-form').trigger("reset");
                        if(self.hasClass('btn')){
                            setSelection(self);
                            var tr = self.parents('tr');
                            var ID = tr.find('.rowcheckbox:checked').val();
                            PaymentIDs[0] = ID;

                        }else{
                            PaymentIDs = getselectedIDs();
                        }
                        $('#recall-payment-form [name="PaymentIDs"]').val(PaymentIDs);
                        $('#recall-modal-payment').modal('show');

                    });

                    $('body').on('click', '.btn.quickbook_post,.quickbook_post', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var self = $(this);
                        var PaymentIDs =[];
                        if (!confirm('Are you sure you want to post in quickbook selected invoices?')) {
                            return;
                        }

                        if(self.hasClass('btn')){
                            setSelection(self);
                            var tr = self.parents('tr');
                            var ID = tr.find('.rowcheckbox:checked').val();
                            PaymentIDs[0] = ID;

                        }else{
                            PaymentIDs = getselectedIDs();
                        }
                        //alert(PaymentIDs);return false;
                        if (PaymentIDs.length) {
                            submit_ajax(baseurl + '/payments/payments_quickbookpost', 'PaymentIDs=' + PaymentIDs)
                        }

                    });

                    $('#recall-payment-form').submit(function(e){
                        e.preventDefault();
                        var SelectedIDs 		  =  $('#recall-payment-form [name="PaymentIDs"]').val();
                        var criteria_ac			  =  '';

                        if($('#selectallbutton').is(':checked')){
                            criteria_ac = 'criteria';
                            $('#recall-payment-form [name="criteria"]').val(JSON.stringify($searchFilter));
                        }else{
                            criteria_ac = 'selected';
                            $('#recall-payment-form [name="criteria"]').val('');
                        }

                        if(SelectedIDs=='' && criteria_ac=='selected')
                        {
                            alert("Please select atleast one account.");
                            $("#payment-recall").button('reset');
                            return false;
                        }
                        var formData = new FormData($('#recall-payment-form')[0]);
                        $.ajax({
                            url: $(this).attr("action"),
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                $(".btn.save").button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $('#recall-modal-payment').modal('hide');
                                    $('#selectallbutton').prop('checked',false);
                                    data_table.fnFilter('', 0);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },
                            // Form data
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    });


                    $('#confirm-modal-payment').on('hidden.bs.modal', function(event){
                        $('#confirm-payments').removeClass('hidden');
                    });

                    $('.btn.check').click(function(e){
                        e.preventDefault();
                        $('#table-4_processing').removeClass('hidden');
                        var formData = new FormData($('#add-template-form')[0]);
                        $.ajax({
                            url:'{{URL::to('payments/ajaxfilegrid')}}',
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function(){
                                $('.btn.check').button('loading');
                            },
                            success: function(response) {
                                $('.btn.check').button('reset');
                                if (response.status == 'success') {
                                    var data = response.data;
                                    createGrid(data);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $('#table-4_processing').addClass('hidden');
                            },
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    });

                    $('table tbody').on('click', '.approvepayment , .rejectpayment', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var self = $(this);
                        setSelection(self);
                        var text = (self.hasClass("approvepayment")?'Approve':'Reject');
                        if (!confirm('Are you sure you want to '+ text +' the payment?')) {
                            return;
                        }
                        $("#payment-status-form").find("input[name='Notes']").val('');
                        $("#payment-status-form").find("input[name='URL']").val($(this).attr('href'));
                        $("#payment-status").modal('show', {backdrop: 'static'});
                        return false;
                    });

                    $("#add-edit-payment-form [name='AccountID']").change(function(){

                        $("#add-edit-payment-form [name='AccountName']").val( $("#add-edit-payment-form [name='AccountID'] option:selected").text());

                        var AccountID = $("#add-edit-payment-form [name='AccountID'] option:selected").val()

                        if(AccountID > 0 ) {
                            var url = baseurl + '/payments/get_currency_invoice_numbers/'+AccountID;
                            $.get(url, function (response) {

                                console.log(response);
                                if( typeof response.status != 'undefined' && response.status == 'success'){

                                    $("#currency").text('(' + response.Currency_Symbol + ')');

                                    var InvoiceNumbers = response.InvoiceNumbers;
                                    $('input[name=InvoiceNo]').typeahead({
                                        //source: InvoiceNumbers,
                                        local: InvoiceNumbers

                                    });

                                }

                            });

                        }
                    });


                    $(document).on('click', '#table-4 tbody tr', function() {
                        $(this).toggleClass('selected');
                        if($(this).is('tr')) {
                            if ($(this).hasClass('selected')) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false);
                            }
                        }
                    });

                    $('#selectall').click(function(){
                        if($(this).is(':checked')){
                            checked = 'checked=checked';
                            $(this).prop("checked", true);
                            $(this).parents('table').find('tbody tr').each(function (i, el) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                                $(this).addClass('selected');
                            });
                        }else{
                            checked = '';
                            $(this).prop("checked", false);
                            $(this).parents('table').find('tbody tr').each(function (i, el) {
                                $(this).find('.rowcheckbox').prop("checked", false);
                                $(this).removeClass('selected');
                            });
                        }
                    });


                    $('#add-edit-payment-form').submit(function(e){
                        e.preventDefault();
                        var PaymentID = $("#add-edit-payment-form [name='PaymentID']").val();
                        if( typeof PaymentID != 'undefined' && PaymentID != ''){
                            update_new_url = baseurl + '/payments/'+PaymentID+'/update';
                        }else{
                            update_new_url = baseurl + '/payments/create';
                        }
                        ajax_Add_update(update_new_url);
                    });
                    //$("#vendor-activecall-table-search").submit();


                    function createGrid(data){
                        var tr = $('#tablemapping thead tr');
                        var body = $('#tablemapping tbody');
                        tr.empty();
                        body.empty();
                        $.each( data.columns, function( key, value ) {
                            tr.append('<th>'+value+'</th>');
                        });

                        $.each( data.rows, function(key, row) {
                            var tr = '<tr>';
                            $.each( row, function(key, item) {
                                if(typeof item == 'object' && item != null ){
                                    tr+='<td>'+item.date+'</td>';
                                }else{
                                    tr+='<td>'+(!item?'':item)+'</td>';
                                }
                            });
                            tr += '</tr>';
                            body.append(tr);
                        });
                        $("#mapping select").each(function(i, el){
                            if(el.name !='selection[DateFormat]'){
                                var self = $('#add-template-form [name="'+el.name+'"]');
                                rebuildSelect2(self,data.columns,'Skip loading');
                            }
                        });
                        if ( data.PaymentUploadTemplate ) {
                            $.each( data.PaymentUploadTemplate, function( optionskey, option_value ) {
                                if(optionskey == 'Title'){
                                    $('#add-template-form').find('[name="TemplateName"]').val(option_value)
                                }
                                if(optionskey == 'Options'){
                                    $.each( option_value.option, function( key, value ) {

                                        if(typeof $("#add-template-form [name='option["+key+"]']").val() != 'undefined'){
                                            $('#add-template-form').find('[name="option['+key+']"]').val(value)
                                            if(key == 'Firstrow'){
                                                $("#add-template-form [name='option["+key+"]']").val(value).trigger("change");
                                            }
                                        }

                                    });
                                    $.each( option_value.selection, function( key, value ) {
                                        if(typeof $("#add-template-form input[name='selection["+key+"]']").val() != 'undefined'){
                                            $('#add-template-form').find('input[name="selection['+key+']"]').val(value)
                                        }else if(typeof $("#add-template-form select[name='selection["+key+"]']").val() != 'undefined'){
                                            $("#add-template-form [name='selection["+key+"]']").val(value).trigger("change");
                                        }
                                    });
                                }
                            });
                        }

                        $('#add-template-form').find('[name="TemplateFile"]').val(data.filename);
                        $('#add-template-form').find('[name="TempFileName"]').val(data.tempfilename);
                    }
					

                    if (isxs()) {
                        $('#paymentsearch').find('.col-sm-2,.col-sm-1').each(function () {
                            $(this).removeClass('col-sm-e2');
                            $(this).removeClass('small-date-input');
                            $(this).removeAttr('style');

                        });
                    }

                    $('[name="Status"]').on('select2-open', function() {
                        $('.select2-results .select2-add').on('click', function(e) {
                            e.stopPropagation();
                        });
                    });

                    $("#vendor-activecall-table-search").submit(function(e) {
                        e.preventDefault();
                        public_vars.$body = $("body");
                        //show_loading_bar(40);
                        $searchFilter.GatewayName = $("#vendor-activecall-table-search [name='GatewayName']").val();
                        $searchFilter.CallPrefix = $("#vendor-activecall-table-search [name='CallPrefix']").val();
                        $searchFilter.CompanyGatewayID = $("#vendor-activecall-table-search [name='CompanyGatewayID']").val();
                        $searchFilter.TotalCurrentCalls = $("#vendor-activecall-table-search [name='TotalCurrentCalls']").val();

                        data_table.fnFilter('', 0);
                        return false;
                    });

                    //LoadVendorActiveCalls
                    $("#LoadVendorActiveCalls").on("click", function (e) {
                        e.preventDefault();
                        $("#LoadVendorActiveCalls").attr('disabled','disabled');
                        $.ajax({
                            url: $(this).attr("data-action"),
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                $(".btn.save").button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    data_table.fnFilter('', 0);
                                    $("#LoadVendorActiveCalls").removeAttr('disabled');
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                    $("#LoadVendorActiveCalls").removeAttr('disabled');
                                }
                            },
                            // Form data
                            //data: formData,
                            cache: false,
                            contentType: false,
                            processData: false
                        });

                    });

                });

                function ajax_Add_update(fullurl){
                    var data = new FormData($('#add-edit-payment-form')[0]);
                    //show_loading_bar(0);

                    $.ajax({
                        url:fullurl, //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function(){
                            /*$('.btn.upload').button('loading');
                             show_loading_bar({
                             pct: 50,
                             delay: 5
                             });*/

                        },
                        afterSend: function(){
                            console.log("Afer Send");
                        },

                        success: function(response) {
                            $("#payment-update").button('reset');
                            $('#modal-payment').modal('hide');

                            if (response.status == 'success') {
                                $('#add-edit-modal-payment').modal('hide');
                                toastr.success(response.message, "Success", toastr_opts);
                                if( typeof data_table !=  'undefined'){
                                    data_table.fnFilter('', 0);
                                }
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $('.btn.upload').button('reset');
                        },
                        complete:function(){
                            $(".btn").button('reset');
                        },
                        data: data,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                    var $label = $('#add-edit-payment-form [for="PaymentProof"]');
                    $label.parents('.form-group').find('a,span').remove();
                    $label.parents('.form-group').find('div').append('<input id="PaymentProof" name="PaymentProof" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class=\'glyphicon glyphicon-circle-arrow-up\'></i>&nbsp;   Browse" />');
                    var $this = $('#PaymentProof');
                    var label = attrDefault($this, 'label', 'Browse');
                    $this.bootstrapFileInput(label);
                }
                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });

        function getselectedIDs(){
            var SelectedIDs = [];
            $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                var accountIDs = $(this).val().trim();
                SelectedIDs[i++] = accountIDs;
            });
            return SelectedIDs;
        }

            </script>
    <style>
                .dataTables_filter label{
                    display:none !important;
                }
                .dataTables_wrapper .export-data{
                    right: 30px !important;
                }
            </style>
    @include('includes.errors')
    @include('includes.success')

@stop
@section('footer_ext')
    @parent



@stop
@extends('layout.main')

@section('content')

    <style>
        .dataTables_processing{
            top:10%;
        }
    </style>

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}"> </i>Accounts</a>
    </li>
    <li>
        <a href="{{URL::to('accounts/'.$account->AccountID.'/edit')}}"></i>Edit Account({{$account->AccountName}})</a>
    </li>
    @if($ServiceID!='0')
    <li>
        <a href="{{URL::to('accountservices/'.$account->AccountID.'/edit/'.$ServiceID)}}"></i>Edit Service({{Service::getServiceNameByID($ServiceID)}})</a>
    </li>
    @endif
    <li class="active">

        <strong>Authentication Rule</strong>
    </li>
</ol>
<h3>Authentication Rule</h3>
<p style="text-align: right;">
    @if( User::checkCategoryPermission('AuthenticationRule','Add'))
        <button type="button" id="save_account" data-loading-text = "Loading..." class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Save
        </button>
    @endif


    @if($ServiceID=='0')
        <a href="{{URL::to('accounts/'.$account->AccountID.'/edit')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    @else
        <a href="{{URL::to('accountservices/'.$account->AccountID.'/edit/'.$ServiceID)}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    @endif

</p>
<?php $AccountNameFormat = array(''=>'Select Authentication Rule')+GatewayConfig::$AccountNameFormat;?>
@if($account->IsCustomer == 1 )
<div class="row">
    <div class="col-md-12">
        <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="customer_detail">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Customer Details
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Authentication Rule

                        </label>
                        <div class="desc col-sm-4">
                            {{Form::select('CustomerAuthRule',$AccountNameFormat,(isset($AccountAuthenticate->CustomerAuthRule)?$AccountAuthenticate->CustomerAuthRule:''),array( "class"=>"select2 small"))}}
                        </div>
                        <?php
                            $AccountIPList = array();
                            $AccountCLIList = array();
                            $CustomerAuthValue = '';
                            if(!empty($AccountAuthenticate->CustomerAuthRule) && $AccountAuthenticate->CustomerAuthRule == 'IP'){
                                $AccountIPList = array_filter(explode(',',$AccountAuthenticate->CustomerAuthValue));
                            }elseif(!empty($AccountAuthenticate->CustomerAuthRule) && $AccountAuthenticate->CustomerAuthRule == 'CLI'){
                                $AccountCLIList = array_filter(explode(',',$AccountAuthenticate->CustomerAuthValue));
                            }
                            if(!empty($AccountAuthenticate->CustomerAuthValue)){
                                $CustomerAuthValue = $AccountAuthenticate->CustomerAuthValue;
                            }

                        ?>
                            <label for="field-1" class="col-sm-1 customer_accountip control-label hidden">Account IP</label>
                        <div class="desc col-sm-5 customer_accountip hidden table_{{count($AccountIPList)}}" >
                            <div class="row dropdown">
                                <div  class="col-md-12">
                                    @if( User::checkCategoryPermission('AuthenticationRule','Add,Delete'))
                                        <div class="input-group-btn pull-right" style="width:70px;">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                                                @if( User::checkCategoryPermission('AuthenticationRule','Add'))
                                                    <li class="li_active">
                                                        <a class="customer-add-ip" type_ad="active" href="javascript:void(0);" >
                                                            <i class="entypo-plus"></i>
                                                            <span>Add</span>
                                                        </a>
                                                    </li>
                                                @endif
                                                @if( User::checkCategoryPermission('AuthenticationRule','Delete'))
                                                    <li>
                                                        <a href="javascript:void(0);" class="customer-delete-ip" >
                                                            <i class="entypo-trash"></i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div><!-- /btn-group -->
                                    @endif
                                </div>
                                <div class="clear"></div>
                            </div>
                            <br>
                            <div id="customeriptableprocessing" class="dataTables_processing hidden">Processing...</div>
                            <table id="customeriptable" class="table table-bordered datatable dataTable customeriptable ">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" name="checkbox[]" class="selectall" /></th>
									<th>IP</th>
									<th>Service</th>
									<th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($allcustomerip))
                                    @foreach($allcustomerip as $key=>$cip)
                                        <tr>
                                            <td><div class="checkbox "><input type="checkbox" name="checkbox[]" value="{{$key}}" class="rowcheckbox" ></div></td>
                                            <td>
                                                {{$cip['CustomerIP']}}
                                            </td>
                                            <td>
                                                {{$cip['ServiceName']}}
                                            </td>
                                            <td>
                                                @if( User::checkCategoryPermission('AuthenticationRule','Delete'))
                                                    <button type="button" title="delete IP" class="btn btn-danger icon-left btn-sm customer-delete-ip"> <i class="entypo-trash"></i> </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="desc col-sm-6 customer_accountcli table_{{count($AccountCLIList)}}" >
                            @include('accounts.cli_tables')

                        </div>

                        <label for="field-1" class="col-sm-2 control-label hidden customer_value_other">Value</label>
                        <div class="desc col-sm-4 hidden customer_value_other">
                            <input type="text" class="form-control"  name="CustomerAuthValueText" value="{{$CustomerAuthValue}}" id="CustomerAuthValueText">
                        </div>
                        <input type="hidden" class="form-control"  name="CustomerAuthValue" id="field-1" placeholder="" value="{{$CustomerAuthValue}}" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@if($account->IsVendor == 1 )
<div class="row">
    <div class="col-md-12">
        <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="vendor_detail">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Vendor Details
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Authentication Rule

                        </label>
                        <div class="desc col-sm-4">
                            {{Form::select('VendorAuthRule',$AccountNameFormat,(isset($AccountAuthenticate->VendorAuthRule)?$AccountAuthenticate->VendorAuthRule:''),array( "class"=>"select2 small"))}}
                        </div>
                        <?php
                        $AccountIPList = array();
                        $AccountCLIList = array();
                        $VendorAuthValue = '';
                        if(!empty($AccountAuthenticate->VendorAuthRule) && $AccountAuthenticate->VendorAuthRule == 'IP'){
                            $AccountIPList = array_filter(explode(',',$AccountAuthenticate->VendorAuthValue));
                        }elseif(!empty($AccountAuthenticate->VendorAuthRule) && $AccountAuthenticate->VendorAuthRule == 'CLI'){
                            $AccountCLIList = array_filter(explode(',',$AccountAuthenticate->VendorAuthValue));
                        }
                        if(!empty($AccountAuthenticate->VendorAuthValue)){
                            $VendorAuthValue = $AccountAuthenticate->VendorAuthValue;
                        }
                        ?>

                            <label for="field-1" class="col-sm-1 vendor_accountip hidden control-label">Account IP</label>
                        <div class="desc col-sm-5 vendor_accountip hidden table_{{count($AccountIPList)}}" >
                            <div class="row dropdown">
                                <div  class="col-md-12">
                                    @if( User::checkCategoryPermission('AuthenticationRule','Add,Delete'))
                                    <div class="input-group-btn pull-right" style="width:70px;">
                                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                                        <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                                            @if( User::checkCategoryPermission('AuthenticationRule','Add'))
                                                <li class="li_active">
                                                    <a class="vendor-add-ip" type_ad="active" href="javascript:void(0);" >
                                                        <i class="entypo-plus"></i>
                                                        <span>Add</span>
                                                    </a>
                                                </li>
                                            @endif
                                            @if( User::checkCategoryPermission('AuthenticationRule','Delete'))
                                                <li>
                                                    <a href="javascript:void(0);" class="vendor-delete-ip" >
                                                        <i class="entypo-trash"></i>
                                                        <span>Delete</span>
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div><!-- /btn-group -->
                                    @endif
                                </div>
                                <div class="clear"></div>
                            </div>
                            <br>
                            <div id="vendoriptableprocessing" class="dataTables_processing hidden">Processing...</div>
                            <table id="vendoriptable" class="table  table-bordered datatable dataTable vendoriptable ">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" name="checkbox[]" class="selectall" /></th>
                                    <th>IP</th>
                                    <th>Service</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($allvendorip))
                                    @foreach($allvendorip as $key=>$vip)
                                        <tr>
                                            <td><div class="checkbox "><input type="checkbox" name="checkbox[]" value="{{$key}}" class="rowcheckbox" ></div></td>
                                            <td>
                                                {{$vip['VendorIP']}}
                                            </td>
                                            <td>
                                                {{$vip['ServiceName']}}
                                            </td>

                                            <td>
                                                <button type="button" title="delete IP" class="btn btn-danger icon-left btn-sm vendor-delete-ip"> <i class="entypo-trash"></i> </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>


                            <label for="field-1" class="col-sm-1 vendor_accountcli control-label">Account CLI</label>
                        <div class="desc col-sm-5 vendor_accountcli table_{{count($AccountCLIList)}}" >
                            <div class="row dropdown">
                                <div  class="col-md-12">
                                    @if( User::checkCategoryPermission('AuthenticationRule','Add,Delete'))
                                        <div class="input-group-btn pull-right" style="width:70px;">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                                            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                                                @if( User::checkCategoryPermission('AuthenticationRule','Add'))
                                                    <li class="li_active">
                                                        <a class="vendor-add-cli" type_ad="active" href="javascript:void(0);" >
                                                            <i class="entypo-plus"></i>
                                                            <span>Add</span>
                                                        </a>
                                                    </li>
                                                @endif
                                                @if( User::checkCategoryPermission('AuthenticationRule','Delete'))
                                                    <li>
                                                        <a href="javascript:void(0);" class="vendor-delete-cli" >
                                                            <i class="entypo-trash"></i>
                                                            <span>Delete</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div><!-- /btn-group -->
                                    @endif
                                </div>
                                <div class="clear"></div>
                            </div>
                            <br>
                            <div id="vendorclitableprocessing" class="dataTables_processing hidden">Processing...</div>
                            <table id="vendorclitable" class="table  table-bordered datatable dataTable vendorclitable ">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" name="checkbox[]" class="selectall" /></th><th>CLI</th><th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($AccountCLIList))
                                    @foreach($AccountCLIList as $index=>$row2)
                                        <tr>
                                            <td><div class="checkbox "><input type="checkbox" name="checkbox[]" value="{{$index}}" class="rowcheckbox" ></div></td>
                                            <td>
                                                {{$row2}}
                                            </td>
                                            <td>
                                                <button type="button" title="delete CLI" class="btn btn-danger icon-left btn-sm vendor-delete-cli"> <i class="entypo-trash"></i> </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>

                        <label for="field-1" class="col-sm-2 control-label hidden vendor_value_other">Value</label>
                        <div class="desc col-sm-4 hidden vendor_value_other">
                            <input type="text" class="form-control"  name="VendorAuthRuleText" id="VendorAuthRuleText" value="{{$VendorAuthValue}}">
                        </div>
                        <input type="hidden" class="form-control"  name="VendorAuthValue" id="field-1" placeholder="" value="{{$VendorAuthValue}}" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
<script type="text/javascript">
    jQuery(document).ready(function($) {
        var acountipclitable;
        var type = 0;
        var isCustomerOrVendor=0;
        var ipclis = '';
        var accountID = '{{$account->AccountID}}';
        var ServiceID = '{{$ServiceID}}';
        attachchangeevent('vendoriptable');
        attachchangeevent('customeriptable');
        attachchangeevent('vendorclitable');
        attachchangeevent('table-clitable');

        if($('#customer_detail select[name="CustomerAuthRule"]').val()=='IP'){
            $('.customer_accountip').removeClass('hidden');
        }else if($('#customer_detail select[name="CustomerAuthRule"]').val()=='CLI'){
            $('.customer_accountcli').removeClass('hidden');
        }
        if($('#vendor_detail select[name="VendorAuthRule"]').val()=='IP'){
            $('.vendor_accountip').removeClass('hidden');
        }else if($('#vendor_detail select[name="VendorAuthRule"]').val()=='CLI'){
            $('.vendor_accountcli').removeClass('hidden');
        }
        $('.vendoriptable,.customeriptable,.vendorclitable,.table-clitable').DataTable({"aaSorting":[[1, 'asc']],"fnDrawCallback": function() {
            $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });
        }});
        $('#save_account').click(function(){
            $(this).button('loading');
            var post_data = '';
            if($('#customer_detail select[name="CustomerAuthRule"]').length > 0) {
                if ($('#customer_detail select[name="CustomerAuthRule"]').val() != 'IP') {
                    $('.customeriptable').DataTable().fnClearTable();
                } else if ($('#customer_detail select[name="CustomerAuthRule"]').val() != 'CLI') {
                    $('.table-clitable').DataTable().fnClearTable();
                }
                post_data = $('#customer_detail').serialize();
            }
            if($('#vendor_detail select[name="VendorAuthRule"]').length > 0) {
                if ($('#vendor_detail select[name="VendorAuthRule"]').val() != 'IP') {
                    $('.vendoriptable').DataTable().fnClearTable();
                } else if ($('#vendor_detail select[name="VendorAuthRule"]').val() != 'CLI') {
                    $('.vendorclitable').DataTable().fnClearTable();
                }
                post_data +='&'+$('#vendor_detail').serialize();
            }
            post_data += '&AccountID='+'{{$account->AccountID}}'+'&ServiceID='+ServiceID;
            var post_url = '{{URL::to('accounts/authenticate_store')}}';
            submit_ajaxbtn(post_url,post_data,'',$(this),1);
        });
        /*$('body').on('click', '.customer-add-ip,.customer-add-cli', function(e) {
            $('#form-addipcli-modal').find("[name='AccountIPCLI']").val('');
            $('.autogrow').trigger('autosize.resize');
            $("#addipcli-modal").modal('show');
            if($(this).hasClass('customer-add-ip')) {
                acountipclitable = 'customeriptable';
                type = 0; //0 for ip
            }else if($(this).hasClass('customer-add-cli')){
                acountipclitable = 'customerclitable';
                type = 1; //1 for cli
            }
            isCustomerOrVendor = 1; //1 for customer
        });*/
        $('body').on('click', '.vendor-add-ip,.vendor-add-cli,.customer-add-ip,.customer-add-cli', function(e) {
            $('#form-addipcli-modal').find("[name='AccountIPCLI']").val('');
            $('.autogrow').trigger('autosize.resize');
            if($(this).hasClass('vendor-add-ip')) {
                acountipclitable = 'vendoriptable';
                type = 0; //0 for ip
                isCustomerOrVendor = 2; //2 for vendor
                $("#addipcli-modal").find('.modal-title').text('Add IP');
                $("#addipcli-modal").find('.control-label').text('Add IP');
            }else if($(this).hasClass('vendor-add-cli')){
                acountipclitable = 'vendorclitable';
                type = 1; //1 for cli
                isCustomerOrVendor = 2; //2 for vendor
                $("#addipcli-modal").find('.modal-title').text('Add CLI');
                $("#addipcli-modal").find('.control-label').text('Add CLI');
            }else if($(this).hasClass('customer-add-ip')){
                acountipclitable = 'customeriptable';
                type = 0; //0 for ip
                isCustomerOrVendor = 1; //1 for customer
                $("#addipcli-modal").find('.modal-title').text('Add IP');
                $("#addipcli-modal").find('.control-label').text('Add IP');
            }else if($(this).hasClass('customer-add-cli')){
                acountipclitable = 'table-clitable';
                type = 1; //1 for cli
                isCustomerOrVendor = 1; //1 for customer
                $("#addipcli-modal").find('.modal-title').text('Add CLI');
                $("#addipcli-modal").find('.control-label').text('Add CLI');
            }
            $("#addipcli-modal").modal('show');
        });
        $('#VendorAuthRuleText').keyup(function () {
            $('#vendor_detail').find('[name="VendorAuthValue"]').val($(this).val());
        });
        $('#CustomerAuthValueText').keyup(function () {
            $('#customer_detail').find('[name="CustomerAuthValue"]').val($(this).val());
        });

        $('[name="CustomerAuthRule"]').change(function(){
            $('.customer_accountip').addClass('hidden');
            $('.customer_accountcli').addClass('hidden');
            $('.customer_value_other').addClass('hidden');
            $('#save_account').removeClass('hidden')
            if($(this).val() == 'Other'){
                $('.customer_value_other').removeClass('hidden');
            }else if($(this).val() == 'IP'){
                $('.customer_accountip').removeClass('hidden');
                $('#save_account').addClass('hidden')
            }else if($(this).val() == 'CLI'){
                $('.customer_accountcli').removeClass('hidden');
                $('#save_account').addClass('hidden')
            }
        });
        $('[name="VendorAuthRule"]').change(function(){
            $('.vendor_accountip').addClass('hidden');
            $('.vendor_accountcli').addClass('hidden');
            $('.vendor_value_other').addClass('hidden');
            $('#save_account').removeClass('hidden')
            if($(this).val() == 'Other'){
                $('.vendor_value_other').removeClass('hidden');
            }else if($(this).val() == 'IP'){
                $('.vendor_accountip').removeClass('hidden');
                $('#save_account').addClass('hidden')
            }else if($(this).val() == 'CLI'){
                $('.vendor_accountcli').removeClass('hidden');
                $('#save_account').addClass('hidden')
            }
        });
        $("#form-addipcli-modal").submit(function(e){
            e.preventDefault();
            ipclis=$(this).find("[name='AccountIPCLI']").val().trim();
            if(type==0) {
                var url = baseurl + '/accounts/' + accountID + '/addips';
            }else if(type==1){
                var url = baseurl + '/accounts/' + accountID + '/addclis';
            }
            $.ajax({
                url: url,
                type:'POST',
                data:{ipclis:ipclis,isCustomerOrVendor:isCustomerOrVendor,type:type,ServiceID:ServiceID},
                datatype:'json',
                success: function(response) {
                    if (response.status == 'success') {
                        createTable(response);
                        $("#addipcli-modal").modal('hide');
                        toastr.success(response.message,'Success', toastr_opts);
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $('.btn').button('reset');
                }
            });
        });
        $('.customer_accountip').addClass('hidden');
        $('.customer_accountcli').addClass('hidden');
        $('.customer_value_other').addClass('hidden');
        $('.vendor_value_other').addClass('hidden');
        $('.vendor_accountip').addClass('hidden');
        $('.vendor_accountcli').addClass('hidden');
        $('[name="CustomerAuthRule"]').trigger('change');
        $('[name="VendorAuthRule"]').trigger('change');

        $('.selectall').click(function(){
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
        table_row_select('customeriptable','');
        table_row_select('vendoriptable','');
        table_row_select('vendorclitable','');

        $(document).on('click','.vendor-delete-ip,.vendor-delete-cli,.customer-delete-ip,.customer-delete-cli',function(e){
            e.preventDefault();
            if($(this).hasClass('icon-left')){
               var tr = $(this).parents('tr');
                tr.addClass('selected');
                tr.find('.rowcheckbox').prop("checked", true);
            }
            if($(this).hasClass('vendor-delete-ip')){
                acountipclitable = 'vendoriptable';
                isCustomerOrVendor = 0;
                type = 0;
                var processing = 'vendoriptableprocessing';
                var selection = 'IP Address';
            }else if($(this).hasClass('vendor-delete-cli')){
                acountipclitable = 'vendorclitable';
                isCustomerOrVendor = 0;
                type = 1;
                var processing = 'vendorclitableprocessing';
                var selection = 'CLI';
            }else if($(this).hasClass('customer-delete-ip')){
                acountipclitable = 'customeriptable';
                isCustomerOrVendor = 1;
                type = 0;
                var processing = 'customeriptableprocessing';
                var selection = 'IP Address';
            }else if($(this).hasClass('customer-delete-cli')){
                acountipclitable = 'table-clitable';
                isCustomerOrVendor = 1;
                type = 1;
                var processing = 'table-clitableprocessing';
                var selection = 'CLI';
            }
            var SelectedIDs = getselectedIDs(acountipclitable);
            if (SelectedIDs.length == 0) {
                toastr.error('Please select at least one '+selection+'.', "Error", toastr_opts);
                return false;
            }else{
                if(confirm('Are you sure you want to delete selected '+selection+'?')){
                    $('#'+processing).removeClass('hidden');
                    if(type == 0){
                        var url = baseurl + "/accounts/"+accountID+"/deleteips";
                    }else{
                        var url = baseurl + "/accounts/"+accountID+"/deleteclis";
                    }
                    ipclis = SelectedIDs.join(",");
                    $.ajax({
                        url: url,
                        type:'POST',
                        data:{ipclis:ipclis,isCustomerOrVendor:isCustomerOrVendor,ServiceID:ServiceID},
                        datatype:'json',
                        success: function(response) {
                            if (response.status == 'success') {
                                createTable(response);
                                $('.selectall').prop("checked", false);
                                toastr.success(response.message,'Success', toastr_opts);
                            }else if (response.status == 'check') {
                                $('#confirm-ip-modal').modal('show');
                            }
                            else{
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $('#'+processing).addClass('hidden');
                        }

                    });
                }
            }
        });

        $('#form-ip-confirm-modal').submit(function(e){
            e.preventDefault();
            var url = baseurl + "/accounts/"+accountID+"/deleteips";
            var dates = $('#form-ip-confirm-modal [name="Closingdate"]').val();
            $.ajax({
                url: url,
                type:'POST',
                data:{ipclis:ipclis,isCustomerOrVendor:isCustomerOrVendor,dates:dates},
                datatype:'json',
                success: function(response) {
                    if (response.status == 'success') {
                        createTable(response);
                        $('.selectall').prop("checked", false);
                        toastr.success(response.message,'Success', toastr_opts);
                    } else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    $('.btn').button('reset');
                    $('#confirm-ip-modal').modal('hide');
                }

            });
        });

        function createTable(response){
            var class_deletipcli = '';
            if(acountipclitable == 'customeriptable'){
                class_deletipcli = 'customer-delete-ip';
            }else if(acountipclitable == 'table-clitable'){
                class_deletipcli = 'customer-delete-cli';
            }else if(acountipclitable == 'vendoriptable'){
                class_deletipcli = 'vendor-delete-ip';
            }else if(acountipclitable == 'vendorclitable'){
                class_deletipcli = 'vendor-delete-cli';
            }
            $('.' + acountipclitable).dataTable().fnDestroy();
            var accoutipclihtml = '';
            var authValue = [];
            if(response.object) {
                if(acountipclitable == 'customeriptable' || acountipclitable == 'customerclitable'){
                    $('#customer_detail [name="CustomerAuthValue"]').val(response.object.CustomerAuthValue);
                    //authValue = response.object.CustomerAuthValue.split(',');
                }else if(acountipclitable == 'vendoriptable' || acountipclitable == 'vendorclitable'){
                    $('#vendor_detail [name="VendorAuthValue"]').val(response.object.VendorAuthValue);
                    //authValue = response.object.VendorAuthValue.split(',');
                }
				if(acountipclitable == 'customeriptable' || acountipclitable == 'vendoriptable'){
					authValue = response.ipobject;								
					$.each(authValue , function( index, obj ) {
                        accoutipclihtml +='<tr><td><div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + index + '" class="rowcheckbox" ></div></td>';
						$.each(obj, function( key, value ) {
                            if(value==null){
                                accoutipclihtml += '<td></td>';
                            }else{
                               accoutipclihtml += '<td>' + value + '</td>';
                            }

							
						});
                        accoutipclihtml += '<td><button type="button" title="'+class_deletipcli+'" class="btn btn-danger btn-sm icon-left delete-cli '+class_deletipcli +'"> <i class="entypo-trash"></i> </button></td></tr>';
					});

					/*
                    $.each(authValue, function (index, item) {
                        if(item){
                            accoutipclihtml += '<tr><td><div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + index + '" class="rowcheckbox" ></div></td><td>' + item[index].IP + '</td><td>' + item[index].ServiceName + '</td><td><button type="button" title="'+class_deletipcli+'" class="btn btn-danger btn-xs icon-left delete-cli '+class_deletipcli +'"> <i class="entypo-trash"></i> </button></td></tr>';
                        }
                    }); */
				}else{
					$.each(authValue, function (index, item) {
					if(item){
                    accoutipclihtml += '<tr><td><div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + index + '" class="rowcheckbox" ></div></td><td>' + item + '</td><td>' + item + '</td><td><button type="button" title="'+class_deletipcli+'" class="btn btn-danger btn-sm icon-left delete-cli '+class_deletipcli +'"> <i class="entypo-trash"></i> </button></td></tr>';
					}
                });	
				}
                
                $('.' + acountipclitable).children('tbody').html(accoutipclihtml);
                $('.' + acountipclitable).DataTable({"aaSorting":[[1, 'asc']],"fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }});
            }
        }

        function attachchangeevent(table){
            $("."+table+" tbody input[type=checkbox]").each(function (i, el) {
                var $this = $(el),
                        $p = $this.closest('tr');

                $(el).on('change', function () {
                    var is_checked = $this.is(':checked');

                    $p[is_checked ? 'addClass' : 'removeClass']('selected');
                });
            });
        }

        function getselectedIDs(table){
            var SelectedIDs = [];
            $('#'+table+' tr .rowcheckbox:checked').each(function (i, el) {
                var ipAddress = $(this).parents('td').next().text().trim();
                SelectedIDs[i++] = ipAddress;
            });
            return SelectedIDs;
        }
    });

</script>
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="addipcli-modal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" id="form-addipcli-modal" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add IP</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                        <label class="col-sm-3 control-label">Account IP</label>
                        <div class="col-sm-9">
                            <textarea name="AccountIPCLI" class="form-control autogrow"></textarea>
                            *Adding multiple IPS or CLIs ,Add one IP or CLI in each line.
                        </div>
                    </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" data-loading-text = "Loading..."  class="btn btn-primary btn-sm btn-icon icon-left">
                        <i class="entypo-floppy"></i>
                        Add
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
<div class="modal fade" id="confirm-ip-modal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" id="form-ip-confirm-modal" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Delete Ips</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label col-sm-3">Date</label>
                                <div class="col-sm-9">
                                    <input type="text" value="{{date('Y-m-d')}}" name="Closingdate" id="Closingdate" class="form-control datepicker" data-date-format="yyyy-mm-dd" placeholder="">
                                </div>
                                <div class="col-sm-3"></div>
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">This is the date when you deleted IP against this account from the switch</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" data-loading-text = "Loading..."  class="btn btn-primary btn-sm btn-icon icon-left">
                        <i class="entypo-floppy"></i>
                        Delete
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
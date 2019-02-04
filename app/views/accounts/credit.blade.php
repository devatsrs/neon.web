@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}"> </i>Accounts</a>
    </li>
    <li>
        <a><span>{{customer_dropbox($account->AccountID)}}</span></a>
    </li>
    <li>
        <a href="{{URL::to('accounts/'.$account->AccountID.'/edit')}}"></i>Edit Account({{$account->AccountName}})</a>
    </li>
    <li class="active">

        <strong>Account Credits</strong>
    </li>
</ol>
<h3>Account Credits</h3>
<p style="text-align: right;">
    @if(User::checkCategoryPermission('CreditControl','Edit'))
    <button type="button" id="save_account" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>
    @endif


    <a href="{{URL::to('accounts/'.$account->AccountID.'/edit')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</p>

<div class="row">
    <div class="col-md-12">
        <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="customer_detail">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Credit Control
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Account Balance</label>
                        <div class="desc col-sm-2 ">
                            <input type="text" class="form-control" readonly name="AccountBalance" value="{{$SOA_Amount}}">
                        </div>
                        @if($BillingType==AccountApproval::BILLINGTYPE_PREPAID)
                            <div  class="col-sm-1">
                                <button id="prepaid_billed_report" class="btn btn-primary btn-sm btn-icon icon-left prepaid_billed_report" data-id="{{$account->AccountID}}" data-loading-text="Loading...">
                                    <i class="fa fa-eye"></i>View Report
                                </button>
                            </div>
                        @endif
                    </div>
                    @if($BillingType==AccountApproval::BILLINGTYPE_POSTPAID)
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Customer Unbilled Amount</label>
                        <div class="desc col-sm-2">
                            <input type="text" class="form-control " readonly name="UnbilledAmount" value="{{$UnbilledAmount}}" >
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Vendor Unbilled Amount</label>
                        <div class="desc col-sm-2 ">
                            <input type="text" class="form-control" readonly name="VendorUnbilledAmount" value="{{$VendorUnbilledAmount}}" >
                        </div>
                        <div  class="col-sm-1">
                            <button id="unbilled_report" class="btn btn-primary btn-sm btn-icon icon-left unbilled_report" data-id="{{$account->AccountID}}" data-loading-text="Loading...">
                                <i class="fa fa-eye"></i>View Report
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Account Exposure</label>
                        <div class="desc col-sm-4 ">
                            <input type="text" class="form-control" readonly name="AccountExposure" value="{{$BalanceAmount}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Available Credit Limit</label>
                        <div class="desc col-sm-4 ">
                            <input type="text" class="form-control" readonly name="AccountBalance" value="{{($PermanentCredit - $BalanceAmount)<0?0:($PermanentCredit - $BalanceAmount)}}">
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Credit Limit</label>
                        <div class="desc col-sm-4 ">
                            <input type="text" class="form-control"  name="PermanentCredit" value="{{$PermanentCredit}}" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Balance Threshold
                            <span data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="If you want to add percentage value enter i.e. 10p for 10% percentage value" data-original-title="Example" class="label label-info popover-primary">?</span>
                        </label>
                        <div class="desc col-sm-4 ">
                            <input type="text" class="form-control"  name="BalanceThreshold" value="{{$BalanceThreshold}}" id="Threshold Limit">
                        </div>
                    </div>
                    
                    <div class="panel panel-primary" data-collapsed="0" id="Merge-components">
            <div class="panel-heading">
                <div class="panel-title">
                    Balance Threshold
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">

                <div class="col-md-12">
                    <br/>
                    <input type="hidden" id="getIDs" name="getIDs" value=""/>
                    <table id="servicetableSubBox" class="table table-bordered datatable">
                        <thead>
                        <tr>
                            <th width="30%">Balance Threshold</th>
                            <th width="20%">Email</th>
                            <th width="10%">Add</th>
                        </tr>
                        </thead>
                        <tbody id="tbody">
                            @if(count($AccountBalanceThreshold))
            @foreach($AccountBalanceThreshold as $AccountBalanceThresholdRow)
                        <tr id="selectedRow-1">
                            <td id="testValues">
                                <input type="text" class="form-control"  name="BalanceThreshold[]" value="{{$AccountBalanceThresholdRow->BalanceThreshold}}" id="Threshold Limit">
                            </td>
                            <td>
                                <input type="text" class="form-control"  name="email[]" value="{{$AccountBalanceThresholdRow->BalanceThresholdEmail}}" id="email">
                            </td>
                            
                            <td>
                                <button type="button" onclick="createCloneRow()" id="Service-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
                                    <i></i>
                                    +
                                </button>
                                <a onclick="deleteRow(this.id)" id="0" class="btn btn-danger btn-sm " data-loading-text="Loading...">
                                    <i></i>
                                    -

                                </a>
                            </td>
                        </tr>
@endforeach
        @else
            <tr id="selectedRow-1">
                            <td id="testValues">
                                <input type="text" class="form-control"  name="BalanceThreshold[]" value="" id="Threshold Limit">
                            </td>
                            <td>
                                <input type="text" class="form-control"  name="email[]" value="" id="email">
                            </td>
                            
                            <td>
                                <button type="button" onclick="createCloneRow()" id="Service-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
                                    <i></i>
                                    +
                                </button>
                                <a onclick="deleteRow(this.id)" id="0" class="btn btn-danger btn-sm " data-loading-text="Loading...">
                                    <i></i>
                                    -

                                </a>
                            </td>
                        </tr>
        @endif
                        </tbody>
                    </table>

                </div>


            </div>
            
            </div>
                    
                    
                </div>
            </div>
        </form>
    </div>
</div>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="10%" >Credit Limit</th>
        <th width="10%" >Balance Threshold</th>
        <th width="10%" >Created By</th>
        <th width="10%" >Created at</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script type="text/javascript">
    
    function getNumber($item){
        var txt = $item;
        var numb = txt.match(/\d/g);
        numb = numb.join("");
        return numb;
    }
    function createCloneRow()
    {


        var $item = $('#servicetableSubBox tr:last').attr('id');
        var numb = getNumber($item);
        numb++;
        var Component      =  $(this).closest('tr').children('td:eq(0)').children('select').attr('name');
        var action         =  $(this).closest('tr').children('td:eq(1)').children('select').attr('name');
        var merge          =  $(this).closest('tr').children('td:eq(2)').children('select').attr('name');
        var ServiceUpdate  =  $(this).closest('tr').children('td:eq(3)').children('button').attr('id');

        $("#"+$item).clone().appendTo("#tbody");

        $('#servicetableSubBox tr:last').attr('id', 'selectedRow-'+numb);

        $('#servicetableSubBox tr:last').children('td:eq(0)').children('select').attr('name', 'Component-'+numb+'[]').attr('id', 'Component-'+numb).select2().select2('val', '');
        $('#servicetableSubBox tr:last').children('td:eq(1)').children('select').attr('name', 'Action-'+numb).attr('id', 'Action-'+numb).select2().select2('val', '');
        $('#servicetableSubBox tr:last').children('td:eq(2)').children('select').attr('name', 'MergeTo-'+numb).attr('id', 'MergeTo-'+numb).select2().select2('val', '');

        if($('#getIDs').val() == '' ){
            $('#getIDs').val(numb+',');
        }else{
            var getIDString =  $('#getIDs').val();
            getIDString = getIDString + numb + ',';
            $('#getIDs').val(getIDString);
        }
        $('#Component-'+numb+' option').each(function() {
            $(this).remove();
        });


        var selectAllComponents = $("#AllComponent").val();
        selectAllComponents = String(selectAllComponents);
        var ComponentsArray = selectAllComponents.split(',');

        var i;
        for (i = 0; i < ComponentsArray.length; ++i) {
            var data = {
                id: ComponentsArray[i],
                text: ComponentsArray[i]
            };

            if( typeof data.id != 'undefined' && data.id  != 'null'){

                var newOption = new Option(data.text, data.id, false, false);

                $('#Component-'+numb).append(newOption).trigger('change');
            }
        }

        $('#servicetableSubBox tr:last').closest('tr').children('td:eq(3)').children('a').attr('id',numb);
        $('#servicetableSubBox tr:last').children('td:eq(0)').find('div:first').remove();
        $('#servicetableSubBox tr:last').children('td:eq(1)').find('div:first').remove();
        $('#servicetableSubBox tr:last').children('td:eq(2)').find('div:first').remove();
        $('#servicetableSubBox tr:last').closest('tr').children('td:eq(3)').find('a').removeClass('hidden');

    }

    function deleteRow(id)
    {
        if(confirm("Are You Sure?")) {
            var selectedSubscription = $('#getIDs').val();
            var removeValue = id + ",";
            var removalueIndex = selectedSubscription.indexOf(removeValue);
            var firstValue = selectedSubscription.substr(0, removalueIndex);//1,2,3,
            var lastValue = selectedSubscription.substr(removalueIndex + removeValue.length, selectedSubscription.length);

            var selectedSubscription = firstValue + lastValue;
            if (selectedSubscription.charAt(0) == ',') {
                selectedSubscription = selectedSubscription.substr(1, selectedSubscription.length)
            }
            $('#getIDs').val(selectedSubscription);

            var rowCount = $("#servicetableSubBox > tbody").children().length;
            if (rowCount > 1) {
                $("#" + id).closest("tr").remove();

            } else {
                $('#getIDs').val('1,');

                toastr.error("You cannot delete. At least one component is required.", "Error", toastr_opts);
            }
        }
    }
    jQuery(document).ready(function($) {
        var acountiptable;
        $('#save_account').click(function(){
            $("#save_account").button('loading');
            var post_data = $('#vendor_detail').serialize()+'&'+$('#customer_detail').serialize()+'&AccountID='+'{{$account->AccountID}}';
            var post_url = '{{URL::to('account/update_credit')}}';
            submit_ajaxbtn(post_url,post_data,'',$(this),1);
        });
        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/account/ajax_datagrid_credit/type",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "fnServerParams": function(aoData) {
                aoData.push(
                        {"name":"AccountID","value":'{{$account->AccountID}}'}
                );
                data_table_extra_params.length = 0;
                data_table_extra_params.push(
                        {"name":"Export","value":1},
                        {"name":"AccountID","value":'{{$account->AccountID}}'}
                );
            },
            "aaSorting": [[0, 'asc']],
            "aoColumns":
                    [
                        {  "bSortable": true },
                        {  "bSortable": true },
                        {  "bSortable": true },
                        {  "bSortable": true }

                    ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/account/ajax_datagrid_credit/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/account/ajax_datagrid_credit/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
            "fnDrawCallback": function() {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });
            }

        });
    });

</script>
@include('accounts.unbilledreportmodal')
@stop
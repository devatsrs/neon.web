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
                        <div class="desc col-sm-4 ">
                            <input type="text" class="form-control" readonly name="AccountBalance" value="{{$SOA_Amount}}">
                        </div>
                    </div>
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
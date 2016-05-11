@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Statement of Account</a>
        </li>
    </ol>

    <h3>Statement of Account</h3>
    <div class="tab-content">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="row">
                <div class="col-md-12">
                    <form role="form" id="account-statement-search" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>
                        <div class="panel panel-primary" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Search
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-1 control-label">Account Name</label>
                                    <div class="col-sm-2">
                                        {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
                                    </div>
                                    <label class="col-sm-1 control-label">Start Date</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="StartDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" id="field-5" placeholder="" value="{{date("Y-m-d",strtotime("-7 days"))}}" >
                                    </div>

                                    <label class="col-sm-1 control-label">End Date</label>
                                    <div class="col-sm-2">
                                        <input type="text" name="EndDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" id="field-5" placeholder="" value="{{date("Y-m-d")}}">
                                    </div>
                                </div>

                                <p style="text-align: right;">
                                    <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                        <i class="entypo-search"></i>
                                        Search
                                    </button>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="clear"></div>

            <div class="row">
                <div class="col-md-12">
                    <div style="width:80px;" class="input-group-btn pull-right">
                        <div class="export-data">
                            <div class="DTTT btn-group">
                                <a class="btn btn-white save-collection" style="display: none;" id="ToolTables_table-4_0">
                                    <undefined>EXCEL</undefined>
                                </a>
                            </div>
                        </div>
                    </div><!-- /btn-group -->
                </div>
                <div class="clear"></div>
                <div id="table-4_processing" class="dataTables_processing" style="display: none;">Processing...</div>
            </div>
            <table class="table table-bordered datatable" id="table-4">
                <thead>
                <tr>
                    <th colspan="4" style="text-align: center;">{{$CompanyName}} INVOICE</th>
                    <th colspan="5"></th>
                    <th colspan="5" style="text-align: center;">INVOICE</th>
                    <th colspan="3"></th>
                </tr>
                <tr>
                    <th width="6%">INVOICE NO</th>
                    <th width="14%">PERIOD COVERED</th>
                    <th width="6%">AMOUNT</th>
                    <th width="6%">PENDING DISPUTE</th>
                    <th width="2%"></th>
                    <th width="9%">Payment Date</th>
                    <th width="6%">PAYMENT</th>
                    <th width="6%">BALANCE</th>
                    <th width="2%"></th>
                    <th width="6%">INVOICE NO</th>
                    <th width="14%">PERIOD COVERED</th>
                    <th width="6%">AMOUNT</th>
                    <th width="6%">PENDING DISPUTE</th>
                    <th width="2%"></th>
                    <th width="9%">Payment Date</th>
                    <th width="6%">{{$CompanyName}} PAYMENT</th>
                    <th width="6%">BALANCE</th>
                </tr>
                </thead>
                <tbody>
                <tr></tr>
                </tbody>
            </table>
            <iframe id="RemotingIFrame" name="RemotingIFrame" style="border: 0px none; width: 0px; height: 0px;">
                <html>
                <head></head>
                <body>
                <form method="post" action="">

                </form>
                </body>
                </html>
            </iframe>
            <script type="text/javascript">
                $(document).ready(function($){
                    $('#account-statement-search').submit(function(e){
                        e.preventDefault();
                        var AccountID = $('#account-statement-search [name="AccountID"]').val();
                        var AccountName = $('#account-statement-search [name="AccountID"] option:selected').text();
                        var InvoiceInAmount = 0;
                        var InvoiceOutAmount = 0;
                        var PaymentsInAmount = 0;
                        var PaymentsOutAmount = 0;
                        var Ballance = 0;
                        var check1= '';
                        var check2='';
                        if(AccountID==''){
                            toastr.error("Please Select a Account", "Error", toastr_opts);
                            return false;
                        }
                        $('#table-4_processing').show();
                        $('#ToolTables_table-4_0').hide();
                        $.ajax({
                            url: baseurl+'/account_statement/ajax_datagrid',
                            data: {
                                AccountID: AccountID,
                                StartDate: $("#account-statement-search [name='StartDate']").val(),
                                EndDate: $("#account-statement-search [name='EndDate']").val()
                            },
                            error: function() {
                                toastr.error("error", "Error", toastr_opts);
                            },
                            dataType: 'json',
                            success: function(data) {



                                var InvoiceOutAmountTotal  = data.InvoiceOutAmountTotal;
                                var PaymentInAmountTotal  = data.PaymentInAmountTotal;
                                var InvoiceInAmountTotal  = data.InvoiceInAmountTotal;
                                var PaymentOutAmountTotal  = data.PaymentOutAmountTotal;
                                var InvoiceOutDisputeAmountTotal  = data.InvoiceOutDisputeAmountTotal;
                                var InvoiceInDisputeAmountTotal  = data.InvoiceInDisputeAmountTotal;
                                var CompanyBalance = data.CompanyBalance;
                                var AccountBalance = data.AccountBalance;
                                var OffsetBalance = data.OffsetBalance;

                                var CurencySymbol  = data.CurencySymbol;
                                var roundplaces  = data.roundplaces;

                                var result = data.result;


								if(result.length<1){
									$('#table-4 > tbody ').html('<tr class="odd"><td valign="top" colspan="15" class="dataTables_empty">No data available in table</td></tr>');
									 $('#table-4_processing').hide();
									return false;
								}
                                $('#table-4 > thead > tr:nth-child(1) > th:nth-child(3)').html(AccountName + " INVOICE");
                                $('#table-4 > thead > tr:nth-child(2) > th:nth-child(6)').html(AccountName + " PAYMENT");
                                $('#table-4 > tbody > tr').remove();
                                $('#table-4 > tbody').append('<tr></tr>');

                                /*var InvoiceAmount=0;
                                var payment=0;
                                var IN_DisputeAmount = OUT_DisputeAmount = 0;
                                var IN_PendingDispute = OUT_PendingDispute = '';
                                var ballence=0;
                                var InvoiceAmounts=0;
                                var payments=0;
                                var ballences=0;
                                var PaymentDate='';
                                var PaymentDates='';

                                var IN_TotalDispute = OUT_TotalDispute = 0;
                                console.log(result);*/

                                var InvoiceIn_AmountTotal = InvoiceOut_AmountTotal = PaymentIn_AmountTotal = PaymentOut_AmountTotal = InvoiceIn_DisputeAmountTotal = InvoiceOut_DisputeAmountTotal = 0;


                                for (i = 0; i < result.length; i++) {

                                    console.log(result[i]);

                                    //Invoice Out
                                    if( typeof result[i]['InvoiceOut_InvoiceNo'] == 'undefined' ) {
                                        result[i]['InvoiceOut_InvoiceNo'] = '';
                                    }
                                    if( typeof result[i]['InvoiceOut_PeriodCover'] == 'undefined' ) {
                                        result[i]['InvoiceOut_PeriodCover'] = '';
                                    }
                                    if( typeof result[i]['InvoiceOut_Amount'] == 'undefined' ) {
                                        result[i]['InvoiceOut_Amount'] = 0;
                                    }
                                    if( typeof result[i]['InvoiceOut_DisputeAmount'] == 'undefined' ) {
                                        result[i]['InvoiceOut_DisputeAmount'] = 0;
                                    }
                                    //Payment In
                                    if( typeof result[i]['PaymentIn_PeriodCover'] == 'undefined' ) {
                                        result[i]['PaymentIn_PeriodCover'] = '';
                                    }
                                    if( typeof result[i]['PaymentIn_PaymentID'] == 'undefined' ) {
                                        result[i]['PaymentIn_PaymentID'] = '';
                                    }
                                    if( typeof result[i]['PaymentIn_Amount'] == 'undefined' ) {
                                        result[i]['PaymentIn_Amount'] = 0;
                                    }
                                    //Invoice In
                                    if( typeof result[i]['InvoiceIn_InvoiceNo'] == 'undefined' ) {
                                        result[i]['InvoiceIn_InvoiceNo'] = '';
                                    }
                                    if( typeof result[i]['InvoiceIn_PeriodCover'] == 'undefined' ) {
                                        result[i]['InvoiceIn_PeriodCover'] = '';
                                    }
                                    if( typeof result[i]['InvoiceIn_Amount'] == 'undefined' ) {
                                        result[i]['InvoiceIn_Amount'] = 0;
                                    }
                                    if( typeof result[i]['InvoiceIn_DisputeAmount'] == 'undefined' ) {
                                        result[i]['InvoiceIn_DisputeAmount'] = 0;
                                    }
                                    //Payment Out
                                    if( typeof result[i]['PaymentOut_PeriodCover'] == 'undefined' ) {
                                        result[i]['PaymentOut_PeriodCover'] = '';
                                    }
                                    if( typeof result[i]['PaymentOut_PaymentID'] == 'undefined' ) {
                                        result[i]['PaymentOut_PaymentID'] = '';
                                    }
                                    if( typeof result[i]['PaymentOut_Amount'] == 'undefined' ) {
                                        result[i]['PaymentOut_Amount'] = 0;
                                    }

                                    InvoiceOut_Amount = parseFloat(result[i]['InvoiceOut_Amount']).toFixed(roundplaces);
                                    //InvoiceOut_Amount = InvoiceOut_Amount > 0 ? InvoiceOut_Amount : '';

                                    InvoiceIn_Amount = parseFloat(result[i]['InvoiceIn_Amount']).toFixed(roundplaces);
                                    //InvoiceIn_Amount = InvoiceIn_Amount > 0 ? InvoiceIn_Amount : '';

                                    InvoiceIn_DisputeAmount = parseFloat(result[i]['InvoiceIn_DisputeAmount']).toFixed(roundplaces);
                                    InvoiceIn_DisputeAmount = InvoiceIn_DisputeAmount > 0 ? InvoiceIn_DisputeAmount : '';

                                    InvoiceOut_DisputeAmount = parseFloat(result[i]['InvoiceOut_DisputeAmount']).toFixed(roundplaces);
                                    InvoiceOut_DisputeAmount = InvoiceOut_DisputeAmount > 0 ? InvoiceOut_DisputeAmount : '';

                                    PaymentIn_Amount = parseFloat(result[i]['PaymentIn_Amount']).toFixed(roundplaces);
                                    PaymentIn_Amount = PaymentIn_Amount > 0 ? PaymentIn_Amount : '';

                                    PaymentOut_Amount = parseFloat(result[i]['PaymentOut_Amount']).toFixed(roundplaces);
                                    PaymentOut_Amount = PaymentOut_Amount > 0 ? PaymentOut_Amount : '';



                                    if( result[i]['PaymentIn_PaymentID'] !='' ) {
                                        result[i]['PaymentIn_PaymentID'] = '<a class="paymentsModel" id="'+result[i]['PaymentIn_PaymentID']+'" href="javascript:;" onClick="paymentsModel(this);">'+PaymentIn_Amount+'</a>';
                                    } else {
                                        result[i]['PaymentIn_PaymentID'] = '';
                                    }
                                    if( result[i]['PaymentOut_PaymentID'] !='' ) {
                                        result[i]['PaymentOut_PaymentID'] = '<a class="paymentsModel" id="' + result[i]['PaymentOut_PaymentID'] + '" href="javascript:;" onClick="paymentsModel(this);">'+PaymentOut_Amount+'</a>';
                                    } else {
                                        result[i]['PaymentOut_PaymentID'] = '';
                                    }


                                    newRow = "<tr>" +

                                                // Invoice Out
                                    "<td>"+result[i]['InvoiceOut_InvoiceNo']+"</td>" +
                                    "<td>"+result[i]['InvoiceOut_PeriodCover']+"</td>" +
                                    "<td>"+ InvoiceOut_Amount +"</td>" +
                                    "<td>"+ InvoiceOut_DisputeAmount +"</td>" +

                                    "<td> </td>" +

                                                // Payment In
                                    "<td>"+result[i]['PaymentIn_PeriodCover']+"</td>" +
                                    "<td>"+result[i]['PaymentIn_PaymentID']+"</td>" +
                                    "<td>"+ PaymentIn_Amount +"</td>" +

                                    "<td> </td>" +

                                                    // Invoice In
                                    "<td>"+result[i]['InvoiceIn_InvoiceNo']+"</td>" +
                                    "<td>"+result[i]['InvoiceIn_PeriodCover']+"</td>" +
                                    "<td>"+ InvoiceIn_Amount +"</td>" +
                                    "<td>"+ InvoiceIn_DisputeAmount +"</td>" +

                                    "<td> </td>" +

                                                    //Payment Out
                                    "<td>"+ result[i]['PaymentOut_PeriodCover'] +"</td>" +
                                    "<td>"+ result[i]['PaymentOut_PaymentID'] +"</td>" +
                                    "<td>"+ PaymentOut_Amount +"</td>" +

                                    "</tr>";


                                    $('#table-4 > tbody > tr:last').after(newRow);

                                }

                                newRow = '<tr>' +
                                '<td></td>' +
                                '<td></td>' +
                                '<td>'+ CurencySymbol+ InvoiceOutAmountTotal +'</td>' +
                                '<td></td>' +
                                '<td></td>' +
                                '<td></td>' +
                                '<td>'+ CurencySymbol+ PaymentInAmountTotal+'</td>' +
                                '<td>'+CurencySymbol + AccountBalance+'</td>' +
                                '<td></td>' +
                                '<td></td>' +
                                '<td></td>' +
                                '<td>'+ CurencySymbol + InvoiceInAmountTotal +'</td>' +
                                '<td></td>' +
                                '<td></td>' +
                                '<td></td>' +
                                '<td>'+ CurencySymbol + PaymentOutAmountTotal +'</td>' +
                                '<td>'+ CurencySymbol + CompanyBalance +'</td>' +
                                '</tr>'+
                                '<tr><td colspan="17"></td></tr>'+
                                '<tr><td colspan="2">BALANCE AFTER OFFSET:</td><td>' + CurencySymbol + OffsetBalance +'</td><td>' + CurencySymbol + InvoiceOutDisputeAmountTotal +'</td><td colspan="8"></td><td>' + CurencySymbol + InvoiceInDisputeAmountTotal +'</td><td colspan="6"></td></tr>';
                                $('#table-4 > tbody > tr:last').after(newRow);
                                $('#table-4_processing').hide();
                                $('#ToolTables_table-4_0').show();
                            },
                            type: 'GET'
                        });

                    });
                    $('#ToolTables_table-4_0').click(function(){
                        var AccountID = $('#account-statement-search [name="AccountID"]').val();
                        var StartDate = $("#account-statement-search [name='StartDate']").val();
                        var EndDate =  $("#account-statement-search [name='EndDate']").val();
                        var url = baseurl + '/account_statement/exports/xlsx?AccountID='+AccountID+"&StartDate="+StartDate+"&EndDate="+EndDate;
                        $( "#RemotingIFrame" ).contents().find("form").attr('action',url);
                        window.open(url, "RemotingIFrame");
                    });
                });
                function paymentsModel(self){
                    id = $(self).attr('id');
                    if(id=='null' || id==''){
                        return false;
                    }
                    $.ajax({
                        url: baseurl + '/account_statement/payment',
                        data: {
                            id: id
                        },
                        error: function () {
                            toastr.error("error", "Error", toastr_opts);
                        },
                        dataType: 'json',
                        success: function (data) {

                            $("#view-modal-payment [name='AccountID']").select2().select2('val',data['AccountID']);
                            $("#view-modal-payment [name='InvoiceNo']").text(data['InvoiceNo']);
                            $("#view-modal-payment [name='PaymentDate']").text(data['PaymentDate']);
                            $("#view-modal-payment [name='PaymentMethod']").text(data['PaymentMethod']);
                            $("#view-modal-payment [name='PaymentType']").text(data['PaymentType']);
                            $("#view-modal-payment [name='Currency']").text(data['Currency']);
                            $("#view-modal-payment [name='Amount']").text(parseFloat(Math.round(data['Amount'] * 100) / 100).toFixed(2));
                            $("#view-modal-payment [name='Notes']").text(data['Notes']);
                            $('#view-modal-payment').modal('show');
                        },
                        type: 'GET'
                    });

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

        </div>
    </div>
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="view-modal-payment">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">View Payment</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Account Name</label>
                                {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account","disabled","disabled ")) }}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Currency</label>
                                <div class="col-sm-12" name="Currency"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Invoice</label>
                                <div class="col-sm-12" name="InvoiceNo"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Payment Date</label>
                                <div class="col-sm-12" name="PaymentDate"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Payment Method</label>
                                <div class="col-sm-12" name="PaymentMethod"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Action</label>
                                <div class="col-sm-12" name="PaymentType"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Amount</label>
                                <div class="col-sm-12" name="Amount"></div>
                                <input type="hidden" name="PaymentID" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Notes</label>
                                <div class="col-sm-12" name="Notes"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

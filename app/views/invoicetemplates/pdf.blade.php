@extends('layout.print')

@include('invoicetemplates.html')
@section('content')
<style>
*{
    font-family: Arial;
    font-size: 10px;
    line-height: normal;
}
p{ line-height: 20px;}
.text-left{ text-align: left}
.text-right{ text-align: right}
.text-center{ text-align: center}
table.invoice th{ padding:3px; background-color: #f5f5f6}
.bg_graycolor{background-color: #f5f5f6}
table.invoice td , table.invoice_total td{ padding:3px;}
@media print {
    .page_break{page-break-after: always;}
    * {
        background-color: auto !important;
        background: auto !important;
        color: auto !important;
    }
    th,td{ padding: 1px; margin: 1px;}
}
.page_break{page-break-after: always;}
table{
    width: 100%;
    border-spacing: 0;
    margin-bottom: 0;
}
tr {
    page-break-inside: avoid;
}

thead {
    display: table-header-group
}

tfoot {
    display: table-row-group
}
</style>
        @yield('logo')
<br />
        @yield('invoice_from')
        @if(Input::get('Type') == 1 )
        <table width="100%" border="0">
            <tbody>
            <tr>
                <td width="40%">
                    @yield('usage_invoice_duration')
                    </td>
                <td width="25%"></td>
                <td width="35%">
                    @yield('usage_invoice_prevbal')
                    </td>
            </tr>
        </tbody>
        </table>
        <br><br>
        @yield('subscriptiontotal')

        @else
            @yield('items')
        @endif

<br />
    <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="table-responsive">
                                <table border="0" width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td class="col-md-6" valign="top" width="65%">
                                                    @yield('terms')
                                            </td>
                                            <td class="col-md-6"  valign="top" width="35%" >
                                                    @yield('total')
                                            </td>
                                        </tr>
                                    </table>

                            </br>
                            </br>
                            </br>
                        </div>
                    </div>
                </div>
            </div>
        </div><div class="page_break"> </div>
      @if(Input::get('Type') == 1 )
         @yield('sub_usage')
     @endif

 @stop
@extends('layout.blank')
@include('invoicetemplates.html')
@section('content')
<style type="text/css">
.invoice,
.invoice table,.invoice table td,.invoice table th,
.invoice ul li
{ font-size: 12px; }
.page_break{page-break-after: always;}
#pdf_header, #pdf_footer{
    position: fixed;
}
</style>
<div class="invoice" style="max-height: 1050px;overflow-x: hidden;overflow-y: auto;">
    <div class="row">
            <div class="col-md-12">
    		@yield('logo')
    		</div>

    	</div>
    	<div class="row">
            <div class="col-md-12">
            @yield('invoice_from')
            </div>
        </div>
        <br/><br/>
    @if(Input::get('Type') == 1 )
    <div class="row">
        <div class="col-sm-5">
            <div class="invoice-left">
                @yield('usage_invoice_duration')
                </div>
            </div>
        <div class="col-sm-2"></div>
        <div class="col-sm-5">
            <div class="invoice-right">
                @yield('usage_invoice_prevbal')
                </div>
            </div>
    </div>
     <div class="row">
        <div class="col-sm-12">
            @yield('subscriptiontotal')
        </div>
    </div>
    @else
        @yield('items')
    @endif

    <div class="margin"></div>
    <div class="row">

        <div class="col-sm-7">

            <div class="invoice-left">
                @yield('terms')
            </div>

        </div>

        <div class="col-sm-5">

            <div class="invoice-right">

               @yield('total')

            </div>
        </div>
    </div>
     @yield('footerterms')
             @if(Input::get('Type') == 1 )
                 @yield('sub_usage')
             @endif

</div>
 @stop
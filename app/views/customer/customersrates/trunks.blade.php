@extends('layout.customer.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
            <a href="#"><i class="entypo-home"></i>@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SETTINGS_TITLE")</a>
    </li>
</ol>
<h3>Settings</h3>
{{--@include('accounts.errormessage')--}}
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    <li class="active">
        <a href="{{ URL::to('/customer/customers_rates') }}" >
            @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SETTINGS_TITLE")
        </a>
    </li>
    <li>
        <a href="{{ URL::to('/customer/customers_rates/rate') }}" >
            @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_OUTBOUND_RATE_TITLE")
        </a>
    </li>
    @if(isset($displayinbound) && $displayinbound>0)
    <li>
        <a href="{{ URL::to('/customer/customers_rates/inboundrate') }}" >
            @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_INBOUND_RATE_TITLE")
        </a>
    </li>
    @endif
    @if(isset($displayservice) && $displayservice>0)
    <li>
        <a href="{{ URL::to('/customer/customers_rates/servicerate') }}" >
            @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SERVICE_RATE_TITLE")
        </a>
    </li>
    @endif
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="customer_rate_tab_content">
        <div class="row">
            <div class="col-md-12">
                <form  id="CustomerTrunk-form" method="post" action="#" >
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            @lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SETTINGS_TAB_OUTGOING_TITLE")
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered datatable" id="table-4">
                            <thead>
                                <tr>
                                    <!--<th width="1%"><div class="checkbox "><input type="checkbox" id="selectall" name="checkbox[]" class="" ></div></th>-->
                                    <th width="10%">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SETTINGS_TAB_OUTGOING_TBL_TRUNK")</th>
                                    <th width="10%">@lang("routes.CUST_PANEL_PAGE_CUSTOMERS_RATES_TAB_SETTINGS_TAB_OUTGOING_TBL_PREFIX")</th>
                                </tr>
                            </thead>
                            <tbody>

                            @if(isset($activetrunks) && count($activetrunks)>0)
                                @foreach($activetrunks as $activetrunk)

                                <tr class="odd gradeX">
                                    <td>{{$activetrunk['Trunk']}}</td>
                                    <td>@if(isset($activetrunk['Prefix'])){{$activetrunk['Prefix']}}@endif</td>
                                </tr>

                                @endforeach
                            @endif
                            @if(empty($activetrunks) && count($activetrunks)==0)
                                <tr class="odd"><td valign="top" colspan="2" class="dataTables_empty">@lang("routes.MESSAGE_DATA_NOT_AVAILABLE")</td></tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                </form>
            </div>
        </div>



    </div>
</div>
<script type="text/javascript">
var ratabale = '{{json_encode($rate_tables)}}';
    jQuery(document).ready(function ($) {

        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

    });

</script>
    {{--@include('includes.errors')
    @include('includes.success')--}}

<?php //@include('includes.ajax_submit_script', array('formID'=>'CustomerTrunk-form' , 'url' => 'customers_rates/update_trunks/'.$id )) ?>
@stop
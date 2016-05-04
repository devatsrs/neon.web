@extends('layout.customer.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
            <a href="#"><i class="entypo-home"></i>Settings</a>
    </li>
</ol>
<h3>Settings</h3>
<!--@include('accounts.errormessage')-->
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    <li>
        <a href="{{ URL::to('/customer/customers_rates') }}" >
             Customer Rate
        </a>
    </li>
    <li class="active">
        <a href="{{ URL::to('/customer/customers_rates/settings') }}" >
             Settings
        </a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="customer_rate_tab_content">
        <div class="row">
            <div class="col-md-12">
                <form  id="CustomerTrunk-form" method="post" action="{{URL::to('/customers_rates/update_trunks/'.$id)}}" >
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Outgoing
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
                                    <th width="10%">Trunk</th>
                                    <th width="10%">Prefix</th>
                                    <th style="text-align:center" width="5%">Show Prefix in Ratesheet</th>
                                    <th width="15%">Use Prefix In CDR</th>
                                    <th style="text-align:center" width="5%">Enable Routing Plan</th>
                                    <th width="4%">Status</th>
                                </tr>
                            </thead>
                            <tbody>

                            @if(isset($trunks) && count($trunks)>0)
                                @foreach($trunks as $trunk)

                                <tr class="odd gradeX">
                                    <!--<td><input type="checkbox" name="CustomerTrunk[{{{$trunk->TrunkID}}}][Status]" class="rowcheckbox" value="1" @if(isset($customer_trunks[$trunk->TrunkID]->Status) && $customer_trunks[$trunk->TrunkID]->Status == 1) checked @endif ></td>-->
                                    <td>{{$trunk->Trunk}}</td>
                                    <td>@if(isset($customer_trunks[$trunk->TrunkID]->Prefix)){{$customer_trunks[$trunk->TrunkID]->Prefix}}@endif</td>
                                    <td class="center" style="text-align:center">@if(isset($customer_trunks[$trunk->TrunkID]->IncludePrefix) && $customer_trunks[$trunk->TrunkID]->IncludePrefix == 1 ) Yes @endif</td>
                                    <td class="center" style="text-align:center">@if((isset($customer_trunks[$trunk->TrunkID]->UseInBilling) && $customer_trunks[$trunk->TrunkID]->UseInBilling == 1)  || (CompanySetting::getKeyVal('UseInBilling') == 1 && !isset($customer_trunks[$trunk->TrunkID]->UseInBilling))) Yes @endif</td>
                                    <td class="center" style="text-align:center">@if(isset($customer_trunks[$trunk->TrunkID]->RoutinePlanStatus) && $customer_trunks[$trunk->TrunkID]->RoutinePlanStatus == 1 ) Yes @endif</td>
                                    <td>
                                        @if(isset($customer_trunks[$trunk->TrunkID]->Status) && ($customer_trunks[$trunk->TrunkID]->Status == 1)) Active @else Inactive
                                        @endif
                                    </td>
                                </tr>

                                @endforeach
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
        @if(count($customer_trunks) == 0)
        $('.nav-tabs').find('a').each(function () {
            if($.trim($(this).text()) != 'Settings'){
                $(this).prop('disabled', true);
                $(this).attr('disabled', 'disabled');
            }
        });
        $('a').click(function(){
            return ($(this).attr('disabled')) ? false : true;
        });
        @endif

    });

</script>
    {{--@include('includes.errors')
    @include('includes.success')--}}

<?php //@include('includes.ajax_submit_script', array('formID'=>'CustomerTrunk-form' , 'url' => 'customers_rates/update_trunks/'.$id )) ?>
@stop
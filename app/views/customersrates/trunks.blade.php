@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
	<li>
		<a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
	</li>
	<li>
		<a href="{{URL::to('accounts')}}">Accounts</a>
	</li>
    <li>
        {{customer_dropbox($id,["IsCustomer"=>1])}}
    </li>
	<li class="active">
		<strong>Settings</strong>
	</li>
</ol>
<h3>Settings</h3>
@include('accounts.errormessage');
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    <li>
        <a href="{{ URL::to('/customers_rates/'.$id) }}" >
             Customer Rate
        </a>
    </li>
    <li class="active">
        <a href="{{ URL::to('/customers_rates/settings/'.$id) }}" >
             Settings
        </a>
    </li>
    @if(User::checkCategoryPermission('CustomersRates','Download'))
    <li>
        <a href="{{ URL::to('/customers_rates/'.$id.'/download') }}" >
             Download Rate sheet
        </a>
    </li>
    @endif
    @if(User::checkCategoryPermission('CustomersRates','History'))
    <li>
        <a href="{{ URL::to('/customers_rates/'.$id.'/history') }}" >
            History
        </a>
    </li>
    @endif
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="customer_rate_tab_content">
        <div class="row">
            <div class="col-md-12">
                <form  id="CustomerTrunk-form" method="post" action="{{URL::to('/customers_rates/update_trunks/'.$id)}}" >
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Trunks
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered datatable" id="table-4">
                            <thead>
                                <tr>
                                    <th width="1%"><div class="checkbox "><input type="checkbox" id="selectall" name="checkbox[]" class="" ></div></th>
                                    <th width="15%">Trunk</th>
                                    <th width="10%">Prefix</th>
                                    <th style="text-align:center" width="5%">Show Prefix in Ratesheet</th>
                                    <th width="15%">Use Prefix In CDR</th>
                                    <th style="text-align:center" width="5%">Enable Routing Plan</th>
                                    <th width="15%">Gateway</th>
                                    <th width="15%">CodeDeck</th>
                                    <th width="15%">Base Rate Table</th>
                                    <th width="4%">Status</th>
                                </tr>
                            </thead>
                            <tbody>

                            @if(isset($trunks) && count($trunks)>0)
                                @foreach($trunks as $trunk)

                                <tr class="odd gradeX  @if(isset($customer_trunks[$trunk->TrunkID]->Status) && $customer_trunks[$trunk->TrunkID]->Status == 1) selected @endif">
                                    <td><input type="checkbox" name="CustomerTrunk[{{{$trunk->TrunkID}}}][Status]" class="rowcheckbox" value="1" @if(isset($customer_trunks[$trunk->TrunkID]->Status) && $customer_trunks[$trunk->TrunkID]->Status == 1) checked @endif ></td>
                                    <td>{{$trunk->Trunk}}</td>
                                    <td><input type="text" class="form-control" name="CustomerTrunk[{{{$trunk->TrunkID}}}][Prefix]" value="@if(isset($customer_trunks[$trunk->TrunkID]->Prefix)){{$customer_trunks[$trunk->TrunkID]->Prefix}}@endif"  /></td>
                                    <td class="center" style="text-align:center"><input type="checkbox" value="1" name="CustomerTrunk[{{{$trunk->TrunkID}}}][IncludePrefix]" @if(isset($customer_trunks[$trunk->TrunkID]->IncludePrefix) && $customer_trunks[$trunk->TrunkID]->IncludePrefix == 1 ) checked @endif  ></td>
                                    <td class="center" style="text-align:center"><input type="checkbox" value="1" name="CustomerTrunk[{{{$trunk->TrunkID}}}][UseInBilling]" @if((isset($customer_trunks[$trunk->TrunkID]->UseInBilling) && $customer_trunks[$trunk->TrunkID]->UseInBilling == 1)  || CompanySetting::getKeyVal('UseInBilling') == 1) checked @endif  ></td>
                                    <td class="center" style="text-align:center"><input type="checkbox" value="1" name="CustomerTrunk[{{{$trunk->TrunkID}}}][RoutinePlanStatus]" @if(isset($customer_trunks[$trunk->TrunkID]->RoutinePlanStatus) && $customer_trunks[$trunk->TrunkID]->RoutinePlanStatus == 1 ) checked @endif  ></td>
                                    <td>
                                        {{ Form::select( 'CustomerTrunk['.$trunk->TrunkID.'][CompanyGatewayID][]', $companygateway, (isset($customer_trunks[$trunk->TrunkID]->CompanyGatewayIDs)? explode(',',$customer_trunks[$trunk->TrunkID]->CompanyGatewayIDs) : '' ), array("class"=>"select2",'multiple',"data-placeholder"=>"Select a Gateway")) }}
                                    </td>
                                    <td  class="center">
                                    <?php $CodeDeckId =  isset($customer_trunks[$trunk->TrunkID])? $customer_trunks[$trunk->TrunkID]->CodeDeckId:''?>
                                        {{ Form::select('CustomerTrunk['.$trunk->TrunkID.'][CodeDeckId]', $codedecklist, $CodeDeckId , array("class"=>"select2 codedeckid")) }}
                                        <input type="hidden" name="trunkid" value="{{$trunk->TrunkID}}">
                                        <input type="hidden" name="codedeckid" value="{{$CodeDeckId}}">
                                    </td>

                                    <td  class="center">
                                        <?php
                                        $rate_table = RateTable::getRateTableList(["TrunkID"=>$trunk->TrunkID,'CodeDeckId'=>$CodeDeckId,'CurrencyID'=>$Account->CurrencyId]);
                                        $RateTableID = (isset($customer_trunks[$trunk->TrunkID]->RateTableID))?$customer_trunks[$trunk->TrunkID]->RateTableID:'';
                                        ?>
                                        {{ Form::select( 'CustomerTrunk['.$trunk->TrunkID.'][RateTableID]'    , $rate_table, $RateTableID   , array("class"=>"selectboxit ratetableid","data-placeholder"=>"Select a Table")) }}
                                        <input type="hidden" name="ratetableid" value="{{$RateTableID}}">
                                    </td>
                                    <td>
                                        @if(isset($customer_trunks[$trunk->TrunkID]->Status) && ($customer_trunks[$trunk->TrunkID]->Status == 1)) Active @else Inactive
                                        @endif
                                    </td>
                                    <input type="hidden" name="CustomerTrunk[{{{$trunk->TrunkID}}}][CustomerTrunkID]" value="@if(isset($customer_trunks[$trunk->TrunkID]->CustomerTrunkID)){{$customer_trunks[$trunk->TrunkID]->CustomerTrunkID}}@endif"  /></td>
                                </tr>

                                @endforeach
                            @endif
                            </tbody>
                        </table>
                        <p class="float-right " >
                            <a href="#" id="customer-trunks-submit" class="btn save btn-primary btn-sm btn-icon icon-left">
                                <i class="entypo-floppy"></i>
                                Save
                            </a>
                        </p>
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


		 /* Highlight row */
        $('#table-4 tbody .rowcheckbox').click(function () {

            //$(this).parent().parent().toggleClass('selected');

            if( $(this).prop("checked")){
                $(this).parent().parent().addClass('selected');
            }else{
                $(this).parent().parent().removeClass('selected');
            }
             
             /*if ($(this).parent().parent().hasClass("selected")) {
                $(this).prop("checked", true);
                $(this).parent().parent().addClass('selected');
            } else {
                $(this).prop("checked", false);
                $(this).parent().parent().removeClass('selected');
            }*/        
            });

        // Select all
        $("#selectall").click(function (ev) {

            var is_checked = $(this).is(':checked');

            $('#table-4 tbody tr').each(function (i, el) {
                if(is_checked){
                    $(this).find('.rowcheckbox').prop("checked",true);
                    $(this).addClass('selected');
                }else{
                    $(this).find('.rowcheckbox').prop("checked",false);
                    $(this).removeClass('selected');
                }
            });
        });

	    $("#customer-trunks-submit").click(function () {
	    	$("#CustomerTrunk-form").submit();
            return false;
	    });
	    $(".ratetableid").bind('change',function (e) {
	        var prev_val = $(this).parent().find('[name="ratetableid"]').val();
	        if(prev_val > 0){
                changeConfirmation = confirm("Are you sure?Effective dates will be changed");
                if(changeConfirmation){

               }else{
                    $(this).val(prev_val);
                    var select  = $(this).parent().find('.ratetableid');
                     setTimeout(function() {
                         select.find("option[value='"+prev_val+"']").attr('selected','selected');
                         select.selectBoxIt().data("selectBox-selectBoxIt").refresh();
                     },200);
                }
            }
	    });

	    $(".codedeckid").bind('change',function (e) {
	        var prev_val = $(this).parent().find('[name="codedeckid"]').val()
	        var trunkid = $(this).parent().find('[name="trunkid"]').val()
	        var current_obj = $(this);
	        var selectBox = current_obj.parent().next().find('.ratetableid').selectBoxIt().data("selectBox-selectBoxIt");
	        selectBox.remove();
            var json = JSON.parse(ratabale);
            selectBox.add({'text':'Select a Rate Table','value':''})
            if( typeof  json[trunkid] != 'undefined'){
                selectBox.add(json[trunkid][current_obj.val()]);
            }

	        $.ajax({
                    url:baseurl + '/customers_rates/delete_customerrates/{{$id}}', //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if(response > 0){
                            changeConfirmation = confirm("Are you sure? Realated Rates will be deleted");
                            if(changeConfirmation){
                                prev_val = current_obj.val();
                                current_obj.prop('selected', prev_val);
                                current_obj.parent().find('select.select2').select2().select2('val',prev_val);
                                selectBox.selectOption("");
                                current_obj.parent().find('[name="codedeckid"]').val(prev_val)
                                current_obj.select2().select2('val',prev_val);
                                submit_ajax(baseurl + '/customers_rates/delete_customerrates/{{$id}}','Trunkid='+trunkid)
                            }else{
                                current_obj.val(prev_val);
                                current_obj.prop('selected', prev_val);
                                current_obj.parent().find('select.select2').select2().select2('val',prev_val);
                            }
                        }

                    },
                    data: 'action=check_count&Trunkid='+trunkid,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false
                });
            return false;
        });

        $("#table-4 tbody input[type='text']").click(function (e) {
            
            return false;


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
    @include('includes.errors')
    @include('includes.success')

<?php //@include('includes.ajax_submit_script', array('formID'=>'CustomerTrunk-form' , 'url' => 'customers_rates/update_trunks/'.$id )) ?>
@stop
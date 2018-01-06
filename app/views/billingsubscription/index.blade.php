@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="billing_subscription_filter" method="post"    class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    <input type="text" name="FilterName" class="form-control" id="field-5" placeholder="">
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Currency</label>
                    {{Form::select('FilterCurrencyID', $currencies, '' ,array("class"=>"form-control select2 small"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Advance Subscription</label>
                    <!-- <input id="FilterAdvance" name="FilterAdvance" type="checkbox" value="1" >-->
                    {{Form::select('FilterAdvance', BillingSubscription::$Advance, '' ,array("class"=>"form-control select2 small"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Applied To</label>
                    <!-- <input id="FilterAdvance" name="FilterAdvance" type="checkbox" value="1" >-->
                    {{Form::select('FilterAppliedTo', BillingSubscription::$ALLAppliedTo, '' ,array("class"=>"form-control select2 small"))}}
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

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Subscription</strong>
    </li>
</ol>
<h3>Subscription</h3>

@include('includes.errors')
@include('includes.success')



<p style="text-align: right;">
@if(User::checkCategoryPermission('BillingSubscription','Add'))
<a href="#" id="add-new-billing_subscription" class="btn btn-primary ">
    <i class="entypo-plus"></i>
    Add New
</a>
@endif
</p>
<table class="table table-bordered datatable" id="table-4">
<thead>
<tr>
    <th width="25%">Name</th>
    <th width="10%">Yearly Fee</th>
    <th width="10%">Quarterly Fee</th>
    <th width="10%">Monthly Fee</th>
    <th width="10%">Weekly Fee</th>
    <th width="10%">Daily Fee</th>
    <th width="10%">Advance Subscription</th>
    <th width="15%">Action</th>
</tr>
</thead>
<tbody>


</tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var AdvanceSubscription = {{$AdvanceSubscription}};
var update_new_url;
var postdata;
jQuery(document).ready(function ($) {

    $('#filter-button-toggle').show();

    public_vars.$body = $("body");
    //show_loading_bar(40);

    var list_fields  = ["Name","AnnuallyFeeWithSymbol","QuarterlyFeeWithSymbol","MonthlyFeeWithSymbol","WeeklyFeeWithSymbol","DailyFeeWithSymbol","Advance","SubscriptionID" , "ActivationFee","CurrencyID","InvoiceLineDescription","Description","AnnuallyFee", "QuarterlyFee", "MonthlyFee", "WeeklyFee", "DailyFee","AppliedTo"];
    $searchFilter.FilterName = $("#billing_subscription_filter [name='FilterName']").val();
    $searchFilter.FilterCurrencyID = $("#billing_subscription_filter select[name='FilterCurrencyID']").val();
    $searchFilter.FilterAdvance = $("#billing_subscription_filter select[name='FilterAdvance']").val();
    $searchFilter.FilterAppliedTo = $("#billing_subscription_filter select[name='FilterAppliedTo']").val();
    //$searchFilter.FilterAdvance = $("#billing_subscription_filter [name='FilterAdvance']").prop("checked");

    data_table = $("#table-4").dataTable({
        "bDestroy": true,
        "bProcessing":true,
        "bServerSide":true,
        "sAjaxSource": baseurl + "/billing_subscription/ajax_datagrid/type",
        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
        "sPaginationType": "bootstrap",
        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
        "aaSorting": [[0, 'asc']],
        "fnServerParams": function(aoData) {
            aoData.push({"name":"FilterName","value":$searchFilter.FilterName},{"name":"FilterCurrencyID","value":$searchFilter.FilterCurrencyID},{"name":"FilterAdvance","value":$searchFilter.FilterAdvance},{"name":"FilterAppliedTo","value":$searchFilter.FilterAppliedTo});
            data_table_extra_params.length = 0;
            data_table_extra_params.push({"name":"FilterName","value":$searchFilter.FilterName},{"name":"FilterCurrencyID","value":$searchFilter.FilterCurrencyID},{"name":"FilterAdvance","value":$searchFilter.FilterAdvance},{"name":"FilterAppliedTo","value":$searchFilter.FilterAppliedTo},{"name":"Export","value":1});
        },
        "aoColumns":
        [
            {  "bSortable": true },  //0  [Name]', '', '', '
            {  "bSortable": true }, //1   [AnnuallyFee]
            {  "bSortable": true }, //1   [QuarterlyFee]
            {  "bSortable": true }, //1   [MonthlyFee]
            {  "bSortable": true }, //2   [WeeklyFee]
            {  "bSortable": true }, //3   [DailyFee]
			{  
                        "bSortable": true,
                        mRender: function (id, type, full) {
                            return AdvanceSubscription[id];
                        }

                     }, //4   [Advance Subscription]
            {                       //5  [SubscriptionID]
               "bSortable": true,
                mRender: function ( id, type, full ) {
                    var action , edit_ , show_ , delete_;
                     action = '<div class = "hiddenRowData" >';

                     for(var i = 0 ; i< list_fields.length; i++){
                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + full[i] + '" / >';
                     }
                     action += '</div>';
                     <?php if(User::checkCategoryPermission('BillingSubscription','Edit')) { ?>
                        action += ' <a data-name = "'+full[0]+'" data-id="'+ id +'" title="Edit" class="edit-billing_subscription btn btn-default btn-sm tooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i>&nbsp;</a>';
                        action += ' <a data-name = "'+full[0]+'" data-id="'+ id +'" title="Clone" class="clone-billing_subscription btn btn-default btn-sm tooltip-primary" data-original-title="Clone" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-clone"></i>&nbsp;</a>';
                    <?php } ?>
                     <?php if(User::checkCategoryPermission('BillingSubscription','Delete')) { ?>
                        action += ' <a data-id="'+ id +'" title="Delete" class="delete-billing_subscription btn delete btn-danger btn-sm tooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-trash"></i></a>';
                     <?php } ?>
                    return action;
                  }
              }
        ],
        "oTableTools": {
            "aButtons": [
                {
                    "sExtends": "download",
                    "sButtonText": "EXCEL",
                    "sUrl": baseurl + "/billing_subscription/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                    sButtonClass: "save-collection"
                },
                {
                    "sExtends": "download",
                    "sButtonText": "CSV",
                    "sUrl": baseurl + "/billing_subscription/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                    sButtonClass: "save-collection"
                }
            ]
        },
       "fnDrawCallback": function() {
               //After Delete done
               FnDeleteSubscriptionSuccess = function(response){

                   if (response.status == 'success') {
                       $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                       ShowToastr("success",response.message);
                       data_table.fnFilter('', 0);
                   }else{
                       ShowToastr("error",response.message);
                   }
               }
               //onDelete Click
               FnDeleteSubscription = function(e){
                   result = confirm("Are you Sure?");
                   if(result){
                       var id  = $(this).attr("data-id");
                       showAjaxScript( baseurl + "/billing_subscription/"+id+"/delete" ,"",FnDeleteSubscriptionSuccess );
                   }
                   return false;
               }
               $(".delete-billing_subscription").click(FnDeleteSubscription); // Delete Note
               $(".dataTables_wrapper select").select2({
                   minimumResultsForSearch: -1
               });
       }

    });


    // Replace Checboxes
    $(".pagination a").click(function (ev) {
        replaceCheckboxes();
    });

    $("#billing_subscription_filter").submit(function(e){
            e.preventDefault();
            $searchFilter.FilterName = $("#billing_subscription_filter [name='FilterName']").val();
            $searchFilter.FilterCurrencyID = $("#billing_subscription_filter select[name='FilterCurrencyID']").val();
           // $searchFilter.FilterAdvance = $("#billing_subscription_filter [name='FilterAdvance']").prop("checked");
            $searchFilter.FilterAdvance = $("#billing_subscription_filter select[name='FilterAdvance']").val();
            $searchFilter.FilterAppliedTo = $("#billing_subscription_filter select[name='FilterAppliedTo']").val();
            data_table.fnFilter('', 0);
            return false;
    });

    $('#add-new-billing_subscription').click(function(ev){
        ev.preventDefault();
        $('#add-new-billing_subscription-form').trigger("reset");
        $("#add-new-billing_subscription-form [name='SubscriptionID']").val('');
        $("#add-new-billing_subscription-form [name='SubscriptionClone']").val(0);
        $('#add-new-modal-billing_subscription h4').html('Add New Subscription');
        $('#add-new-modal-billing_subscription').modal('show');
        $("#add-new-modal-billing_subscription [name=CurrencyID]").prop("disabled",false);
        $("#add-new-billing_subscription-form select[name=CurrencyID]").val('').trigger("change");

    });
    $('table tbody').on('click','.edit-billing_subscription',function(e){
        e.preventDefault();
        e.stopPropagation();

        $('#add-new-billing_subscription-form').trigger("reset");
        $('#add-new-modal-billing_subscription').modal('show');

        var $this = $(this);
        $.each(list_fields, function( index, field_name ) {
            var val = $this.prev("div.hiddenRowData").find("input[name='"+field_name+"']").val();
            $("#add-new-billing_subscription-form [name='"+field_name+"']").val(val!='null'?val:'');
            if(field_name =='CurrencyID' || field_name =='AppliedTo'){
                $("#add-new-billing_subscription-form [name='"+field_name+"']").val(val).trigger("change");
            }else if(field_name == 'Advance'){
                if(val == 1 ){
                    $('#add-new-billing_subscription-form [name="Advance"]').prop('checked',true)
                }else{
                    $('#add-new-billing_subscription-form [name="Advance"]').prop('checked',false)
                }
            }
        });
        if($("#add-new-modal-billing_subscription select[name=CurrencyID]").val() > 0 ){
            //$("#add-new-modal-billing_subscription select[name=CurrencyID]").prop("disabled",true);
        }
        $("#add-new-billing_subscription-form [name='SubscriptionClone']").val(0);

        $('#add-new-modal-billing_subscription h4').html('Edit Subscription');

    });
    $('table tbody').on('click','.clone-billing_subscription',function(e){
        e.preventDefault();
        e.stopPropagation();

        $('#add-new-billing_subscription-form').trigger("reset");
        $('#add-new-modal-billing_subscription').modal('show');

        var $this = $(this);
        $.each(list_fields, function( index, field_name ) {
            var val = $this.prev().prev("div.hiddenRowData").find("input[name='"+field_name+"']").val();
            $("#add-new-billing_subscription-form [name='"+field_name+"']").val(val!='null'?val:'');
            if(field_name =='CurrencyID' || field_name =='AppliedTo'){
                $("#add-new-billing_subscription-form [name='"+field_name+"']").val(val).trigger("change");
            }else if(field_name == 'Advance'){
                if(val == 1 ){
                    $('#add-new-billing_subscription-form [name="Advance"]').prop('checked',true)
                }else{
                    $('#add-new-billing_subscription-form [name="Advance"]').prop('checked',false)
                }
            }
        });
        if($("#add-new-modal-billing_subscription select[name=CurrencyID]").val() > 0 ){
            //$("#add-new-modal-billing_subscription select[name=CurrencyID]").prop("disabled",true);
        }

        $("#add-new-billing_subscription-form [name='SubscriptionClone']").val(1);
        $('#add-new-modal-billing_subscription h4').html('Clone Subscription');

    });
});

</script>
<style>
.dataTables_filter label{
display:none !important;
}
.dataTables_wrapper .export-data{
right: 30px !important;
}
</style>
@include('currencies.currencymodal')


@stop
@section('footer_ext')
@parent
<div class="modal fade custom-width" id="add-new-modal-billing_subscription">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Add New Subscription</h4>
        </div>
        @include('billingsubscription.subscriptionform')
    </div>
</div>
</div>
@stop
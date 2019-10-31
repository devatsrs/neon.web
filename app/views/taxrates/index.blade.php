@extends('layout.main')
@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <div id="table_filter" method="get" action="#" >
                <div class="form-group">
                    <label for="field-1" class="control-label">Title</label>
                    <input type="text" name="title" class="form-control" value="" />
                </div>
                
                            <div class="form-group hidden-xs hidden-sm hidden-md hidden-lg">
                                <label for="field-5" class="control-label">Tax Type</label>
                                {{ Form::select('TaxType',TaxRate::$tax_array_filter,'', array("class"=>"select2",'id'=>'TaxTypeID')) }}
                            </div>
                            <div class="form-group">
                                <label for="field-5" class="control-label">Country</label>
                                {{ Form::select('ftCountry',Country::getCountryByNameAndCode('filter'),'', array("class"=>"select2",'id'=>'Country')) }}
                            </div>
                            <div class="form-group">
                                <label for="field-5" class="control-label">Dutch Provider</label>
                                <div class="clear">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox"  name="ftDutchProvider" value="0">
                                    </p>
                                    </div>
                        </div>
                        
                            <div class="form-group">
                                <label for="field-5" class="control-label">Dutch Foundation</label>
                                <div class="clear">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox"  name="ftDutchFoundation" value="0">
                                    </p>
                                    </div>
                            </div>
                            <div class="form-group hidden-xs hidden-sm hidden-md hidden-lg">
                                <label for="field-5" class="control-label">Flat</label>
                                
                                    <p class="make-switch switch-small">
                                        <input type="checkbox"  name="ftFlatStatus" value="0">
                                    </p>   
                        </div>
                        
                            
                       
                        

                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="filter_submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop
@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>VAT Rates</strong>
    </li>
</ol>
<h3>VAT Rates</h3>

@include('includes.errors')
@include('includes.success')

<style>
    .checkicon{color:green; font-size: 17px;}
    .timesicon{color:red; font-size: 17px;}
    .aligncenter { text-align: center; }
    
</style>

<p style="text-align: right !important;">

@if( User::checkCategoryPermission('TaxRates','Add') )
    <a href="#" id="add-new-taxrate" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="15%">Title</th>
        
        <th width="10%">VAT %</th>
        
        <th width="10%">Country</th>
        <th width="10%">Dutch Provider</th>
        <th width="10%">Dutch Foundation</th>
        <th width="20%">Action</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);
        $("#filter_submit").click(function(e) {
                e.preventDefault();
        $searchFilter.Title = $("#table_filter").find('[name="title"]').val();
        $searchFilter.TaxType = $("#table_filter").find('[name="TaxType"]').val();
        $searchFilter.ftCountry = $("#table_filter").find('[name="ftCountry"]').val();
        $searchFilter.FlatStatus = $("#table_filter").find('[name="ftFlatStatus"]').val();
        $searchFilter.ftDutchProvider = $("#table_filter").find('[name="ftDutchProvider"]').val();
        $searchFilter.ftDutchFoundation = $("#table_filter").find('[name="ftDutchFoundation"]').val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/taxrate/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Title", "value": $searchFilter.Title},
                                {"name": "TaxType", "value": $searchFilter.TaxType},
                                {"name": "Country", "value": $searchFilter.ftCountry},
                                {"name": "FlatStatus", "value": $searchFilter.FlatStatus},
                                {"name": "ftDutchProvider", "value": $searchFilter.ftDutchProvider},
                                {"name": "ftDutchFoundation", "value": $searchFilter.ftDutchFoundation}
                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                            {"name": "Title", "value": $searchFilter.Title},
                            {"name": "TaxType", "value": $searchFilter.TaxType},
                            {"name": "Country", "value": $searchFilter.ftCountry},
                            {"name": "FlatStatus", "value": $searchFilter.FlatStatus},
                            {"name": "ftDutchProvider", "value": $searchFilter.ftDutchProvider},
                            {"name": "ftDutchFoundation", "value": $searchFilter.ftDutchFoundation},
                            {"name": "Export", "value": 1}
                        );
                    },
             "aoColumns":
            [
                {  "bSortable": true },  //0  TaxRateTitle', '', '', '
                /*{  "bSortable": true, "bVisible":false, mRender: function ( data, type, full ) {
                    if(data == 2) {return "Usage Only";}
                    else if(data == 3){return "Recurring";}
                    else {return "All Charges overall Invoice";}
                }  }, //1   TaxRateAmount*/
                {  "bSortable": true},
                {  "bSortable": true ,mRender: function ( data, type, full ) {
                    return full[6];
                }
                /*, "sClass":"aligncenter",mRender: function ( data, type, full ) {
                     
                    if(data == 1) {var display = "<i class='fa fa-check-circle checkicon'></i>";} else {var display = "<i class='fa fa-times-circle timesicon'></i>";}
                    return display;} },*/
                },    
                // {  "bSortable": true, mRender: function ( data, type, full ) {
                //     if(data == 'NL'){return 'Netherlands';} else if(data == 'EU'){return 'EU Country';} else if(data == 'NEU'){return 'Non EU';} else{return data;}} },
                {  "bSortable": true,"sClass":"aligncenter", mRender: function ( data, type, full ) {
                    if(data == 1) {return "<i class='fa fa-check-circle checkicon'></i>";} else {return "<i class='fa fa-times-circle timesicon'></i>";}
                    }  },
                {  "bSortable": true,"sClass":"aligncenter", mRender: function ( data, type, full ) {
                    if(data == 1) {return "<i class='fa fa-check-circle checkicon'></i>";} else {return "<i class='fa fa-times-circle timesicon'></i>";}
                    }  
                },
                {  "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_;
                         action = '<div class = "hiddenRowData" >';
                         action += '<input type = "hidden"  name = "Title" value = "' + full[0] + '" / >';
                         action += '<input type = "hidden"  name = "Amount" value = "' + full[1] + '" / >';
                         action += '<input type = "hidden"  name = "TaxType" value = "1" / >';
                         action += '<input type = "hidden"  name = "FlatStatus" value = "0" / >';
                         action += '<input type = "hidden"  name = "Country" value = "' + full[2] + '" / >';
                         action += '<input type = "hidden"  name = "DutchProvider" value = "' + full[3] + '" / >';
                         action += '<input type = "hidden"  name = "DutchFoundation" value = "' + full[4] + '" / >';
                         action += '</div>';
                         <?php if(User::checkCategoryPermission('TaxRates','Edit')){ ?>
                            action += ' <a data-name = "'+full[0]+'" data-id="'+ id +'" title="Edit" class="edit-taxrate btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                         <?php } ?>
                         <?php if(User::checkCategoryPermission('TaxRates','Delete')){ ?>
                            action += ' <a data-id="'+ id +'" title="Delete" class="delete-taxrate btn delete btn-danger btn-sm"><i class="entypo-trash"></i></a>';
                          <?php } ?>
                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "Export Data",
                        "sUrl": baseurl + "/taxrate/base_exports", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   //After Delete done
                   FnDeleteTaxRateSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteTaxRate = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/taxrate/"+id+"/delete" ,"",FnDeleteTaxRateSuccess );
                       }
                       return false;
                   }
                   $(".delete-taxrate").click(FnDeleteTaxRate); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }

        });
});
$('#filter_submit').trigger('click');
        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


    $('#add-new-taxrate').click(function(ev){
        ev.preventDefault();
        $('[name="Country"]').prop('disabled', false);
        $('p#custom').removeClass('deactivate');

        $('#add-new-taxrate-form').trigger("reset");
        $("#add-new-taxrate-form [name='TaxRateID']").val('');
        
        $('#add-new-modal-taxrate h4').html('Add New VAT Rate');
        $('#add-new-modal-taxrate').modal('show');
    });
    $('table tbody').on('click','.edit-taxrate',function(ev){
        ev.preventDefault();
        ev.stopPropagation();

        $('[name="Country"]').prop('disabled', true);
        $('p#custom').addClass('deactivate');

        $('#add-new-taxrate-form').trigger("reset");
        var prevrow = $(this).prev("div.hiddenRowData");

        Title = prevrow.find("input[name='Title']").val();
        Amount = prevrow.find("input[name='Amount']").val();

        $("#add-new-taxrate-form [name='Title']").val(Title);
        $("#add-new-taxrate-form [name='Amount']").val(Amount);
        $("#add-new-taxrate-form [name='FlatStatus']").val(prevrow.find("input[name='FlatStatus']").val() );
        $("#add-new-taxrate-form [name='TaxType']").select2().select2('val',prevrow.find("input[name='TaxType']").val());
        if(prevrow.find("input[name='FlatStatus']").val() == 1 ){
            $('[name="Status_name"]').prop('checked',true)
        }else{
            $('[name="Status_name"]').prop('checked',false)
        }
        $("#add-new-taxrate-form [name='Country']").select2().select2('val',prevrow.find("input[name='Country']").val());
        if(prevrow.find("input[name='DutchProvider']").val() == 1 ){
            $('[name="DutchProviderSt"]').prop('checked',true)
        }else{
            $('[name="DutchProviderSt"]').prop('checked',false)
        }
        if(prevrow.find("input[name='DutchFoundation']").val() == 1 ){
            $('[name="DutchFoundationSt"]').prop('checked',true)
        }else{
            $('[name="DutchFoundationSt"]').prop('checked',false)
        }
        
        $("#add-new-taxrate-form [name='TaxRateID']").val($(this).attr('data-id'));
        $('#add-new-modal-taxrate h4').html('Edit VAT Rate');
        $('#add-new-modal-taxrate').modal('show');
    })

    $('#add-new-taxrate-form').submit(function(e){
        e.preventDefault();
        var TaxRateID = $("#add-new-taxrate-form [name='TaxRateID']").val()

        if( typeof TaxRateID != 'undefined' && TaxRateID != ''){
            update_new_url = baseurl + '/taxrate/update/'+TaxRateID;
        }else{
            update_new_url = baseurl + '/taxrate/create';
        }
        ajax_update(update_new_url,$('#add-new-taxrate-form').serialize());
    });

    $('[name="Status_name"]').change(function(e){
        if($(this).prop('checked')){
            $("#add-new-taxrate-form [name='FlatStatus']").val(1);
        }else{
            $("#add-new-taxrate-form [name='FlatStatus']").val(0);
        }

    });

    $('[name="DutchFoundationSt"]').change(function(e){
        if($(this).prop('checked')){
            $("#add-new-taxrate-form [name='DutchFoundation']").val(1);
        }else{
            $("#add-new-taxrate-form [name='DutchFoundation']").val(0);
        }

    });
    $('[name="DutchProviderSt"]').change(function(e){
        if($(this).prop('checked')){
            $("#add-new-taxrate-form [name='DutchProvider']").val(1);
        }else{
            $("#add-new-taxrate-form [name='DutchProvider']").val(0);
        }

    });

    $('[name="ftDutchFoundation"]').change(function(e){
        if($(this).prop('checked')){
            $("[name='ftDutchFoundation']").val(1);
        }else{
            $("[name='ftDutchFoundation']").val(0);
        }

    });
    $('[name="ftDutchProvider"]').change(function(e){
        if($(this).prop('checked')){
            $("[name='ftDutchProvider']").val(1);
        }else{
            $(" [name='ftDutchProvider']").val(0);
        }

    });
    $('[name="ftFlatStatus"]').change(function(e){
        if($(this).prop('checked')){
            $(" [name='ftFlatStatus']").val(1);
        }else{
            $(" [name='ftFlatStatus']").val(0);
        }

    });


    });

function ajax_update(fullurl,data){
//alert(data)
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#taxrate-update").button('reset');
            $(".btn").button('reset');
            $('#modal-TaxRate').modal('hide');

            if (response.status == 'success') {
                $('#add-new-modal-taxrate').modal('hide');
                toastr.success(response.message, "Success", toastr_opts);
                if( typeof data_table !=  'undefined'){
                    data_table.fnFilter('', 0);
                }
            } else {
                toastr.error(response.message, "Error", toastr_opts);
            }
        },
        data: data,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false
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
#filter-button-toggle {display:block !important;float:left;}
</style>
@stop


@section('footer_ext')
@parent
<div class="modal fade" id="add-new-modal-taxrate">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-taxrate-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New VAT Rate</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Title</label>
                                <input type="text" name="Title" class="form-control" id="field-5" placeholder="">
                             </div>
                        </div>
                        <div class="col-md-6 hidden-xs hidden-sm hidden-md hidden-lg">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Tax Type</label>
                                {{ Form::select('TaxType',TaxRate::$tax_array,TaxRate::TAX_ALL, array("class"=>"select2",'id'=>'TaxTypeID')) }}
                            </div>
                        </div>
                        <div class="col-md-12 ">
                            <div class="form-group">
                                <label for="field-5" class="control-label">VAT %</label>
                                <input type="number" step="0.01" min="0.01" max="1000" name="Amount" class="form-control" id="field-5" placeholder="">
                                <input type="hidden" name="TaxRateID" >
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Country</label>
                                {{ Form::select('Country',Country::getCountryByNameAndCode(),'', array("class"=>"select2",'id'=>'Country')) }}
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-4 hidden-xs hidden-sm hidden-md hidden-lg">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Flat</label>
                                <div class="clear">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox"  name="Status_name" value="0">
                                    </p>
                                    <input type="hidden"  name="FlatStatus" value="0">
                                    </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Dutch Provider</label>
                                <div class="clear">
                                    <p class="make-switch switch-small" id="custom">
                                        <input type="checkbox"  name="DutchProviderSt" value="0">
                                        <input type="hidden"  name="DutchProvider" value="0">
                                    </p>
                                    
                                    </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Dutch Foundation</label>
                                <div class="clear">
                                    <p class="make-switch switch-small" id="custom">
                                        <input type="checkbox"  name="DutchFoundationSt" value="0">
                                        <input type="hidden"  name="DutchFoundation" value="0">
                                    </p>
                                    
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="taxrate-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
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

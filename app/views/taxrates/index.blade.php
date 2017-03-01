@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Tax Rates</strong>
    </li>
</ol>
<h3>Tax Rates</h3>

@include('includes.errors')
@include('includes.success')



<p style="text-align: right;">

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
        <th width="30%">Title</th>
        <th width="25%">Amount (%)</th>
        <th width="25%">Action</th>
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

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/taxrate/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
             "aoColumns":
            [
                {  "bSortable": true },  //0  TaxRateTitle', '', '', '
                {  "bSortable": true }, //1   TaxRateAmount
                {                       //3  TaxRateID
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_;
                         action = '<div class = "hiddenRowData" >';
                         action += '<input type = "hidden"  name = "Title" value = "' + full[0] + '" / >';
                         action += '<input type = "hidden"  name = "Amount" value = "' + full[1] + '" / >';
                         action += '<input type = "hidden"  name = "TaxType" value = "' + full[3] + '" / >';
                         action += '<input type = "hidden"  name = "FlatStatus" value = "' + full[4] + '" / >';
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


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


    $('#add-new-taxrate').click(function(ev){
        ev.preventDefault();
        $('#add-new-taxrate-form').trigger("reset");
        $("#add-new-taxrate-form [name='TaxRateID']").val('')
        $('#add-new-modal-taxrate h4').html('Add New TaxRate');
        $('#add-new-modal-taxrate').modal('show');
    });
    $('table tbody').on('click','.edit-taxrate',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
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
        $("#add-new-taxrate-form [name='TaxRateID']").val($(this).attr('data-id'));
        $('#add-new-modal-taxrate h4').html('Edit TaxRate');
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
                    <h4 class="modal-title">Add New TaxRate</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Title</label>
                                <input type="text" name="Title" class="form-control" id="field-5" placeholder="">
                             </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Amount</label>
                                <input type="text" name="Amount" class="form-control" id="field-5" placeholder="">
                                <input type="hidden" name="TaxRateID" >
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Tax Type</label>
                                {{ Form::select('TaxType',TaxRate::$tax_array,'', array("class"=>"select2",'id'=>'TaxTypeID')) }}
                            </div>
                        </div>
                        <div class="col-md-12">
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

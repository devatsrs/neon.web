@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Currency</strong>
    </li>
</ol>
<h3>Currency</h3>

@include('includes.errors')
@include('includes.success')



<p style="text-align: right;">
@if( User::can('CurrenciesController.create') )
    <a href="#" id="add-new-currency" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New Currency
    </a>
@endif
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="30%">Currency Code</th>
        <th width="30%">Currency Symbol</th>
        <th width="25%">Currency Description</th>
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
            "sAjaxSource": baseurl + "/currency/ajax_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
             "aoColumns":
            [
                {  "bSortable": true },  //0  CurrencyCode', '', '', '
                {  "bSortable": true },  //0  Currency Symbol', '', '', '
                {  "bSortable": true }, //1   CurrencyDescription
                {                       //3  CurrencyID
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_;
                         action = '<div class = "hiddenRowData" >';
                         action += '<input type = "hidden"  name = "Code" value = "' + (full[0] != null ? full[0] : '') + '" / >';
                        action += '<input type = "hidden"  name = "Symbol" value = "' + (full[1] != null ? full[1] : '') + '" / >';
                         action += '<input type = "hidden"  name = "Description" value = "' + (full[2] != null ? full[2] : '') + '" / ></div>';
                         if('{{User::can('CurrenciesController.update')}}'){
                            action += ' <a data-name = "'+full[0]+'" data-id="'+ id +'" class="edit-currency btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                         }
                         if('{{User::can('CurrenciesController.delete')}}'){
                            action += ' <a data-id="'+ id +'" class="delete-currency btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
                         }
                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "Export Data",
                        "sUrl": baseurl + "/currency/base_exports", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   //After Delete done
                   FnDeleteCurrencySuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteCurrency = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/currency/"+id+"/delete" ,"",FnDeleteCurrencySuccess );
                       }
                       return false;
                   }
                   $(".delete-currency").click(FnDeleteCurrency); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }

        });


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


    $('#add-new-currency').click(function(ev){
        ev.preventDefault();
        $('#add-new-currency-form').trigger("reset");
        $("#add-new-currency-form [name='CurrencyID']").val('')
        $('#add-new-modal-currency h4').html('Add New Currency');
        $('#add-new-modal-currency').modal('show');
    });
    $('table tbody').on('click','.edit-currency',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-currency-form').trigger("reset");

        Code = $(this).prev("div.hiddenRowData").find("input[name='Code']").val();
        Description = $(this).prev("div.hiddenRowData").find("input[name='Description']").val();
        Symbol = $(this).prev("div.hiddenRowData").find("input[name='Symbol']").val();

        $("#add-new-currency-form [name='Code']").val(Code);
        $("#add-new-currency-form [name='Symbol']").val(Symbol);
        $("#add-new-currency-form [name='Description']").val(Description);
        $("#add-new-currency-form [name='CurrencyID']").val($(this).attr('data-id'));
        $('#add-new-modal-currency h4').html('Edit Currency');
        $('#add-new-modal-currency').modal('show');
    })

    $('#add-new-currency-form').submit(function(e){
        e.preventDefault();
        var CurrencyID = $("#add-new-currency-form [name='CurrencyID']").val()
        if( typeof CurrencyID != 'undefined' && CurrencyID != ''){
            update_new_url = baseurl + '/currency/update/'+CurrencyID;
        }else{
            update_new_url = baseurl + '/currency/create';
        }
        ajax_update(update_new_url,$('#add-new-currency-form').serialize());
    })


    });

function ajax_update(fullurl,data){
//alert(data)
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#currency-update").button('reset');
            $(".btn").button('reset');
            $('#modal-Currency').modal('hide');

            if (response.status == 'success') {
                $('#add-new-modal-currency').modal('hide');
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
<div class="modal fade" id="add-new-modal-currency">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-currency-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Currency</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Currency Code</label>
                                <input type="text" name="Code" class="form-control" id="field-5" placeholder="">
                             </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Currency Symbol</label>
                                <input type="text" name="Symbol" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Description</label>
                                <input type="text" name="Description" class="form-control" id="field-5" placeholder="">
                                <input type="hidden" name="CurrencyID" >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="currency-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

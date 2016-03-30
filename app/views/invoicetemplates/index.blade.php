@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Invoice Template</strong>
    </li>
</ol>
<h3>Invoice Template</h3>

@include('includes.errors')
@include('includes.success')



<p style="text-align: right;">

@if( User::checkCategoryPermission('InvoiceTemplates','Add'))
    <a href="#" id="add-new-invoice_template" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New Invoice Template
    </a>
@endif    
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="25%">Name</th>
        <th width="25%">Modified Date</th>
        <th width="25%">Modified By</th>
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

        var list_fields  = ['Name','updated_at','ModifiedBy','InvoiceTemplateID','InvoiceStartNumber','CompanyLogoUrl','InvoiceNumberPrefix','InvoicePages','LastInvoiceNumber','ShowZeroCall','ShowPrevBal','DateFormat','Type','ShowBillingPeriod','EstimateStartNumber','LastEstimateNumber','EstimateNumberPrefix'];

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/invoice_template/ajax_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "fnServerParams": function(aoData) {
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"Export","value":1});
            },
             "aoColumns":
            [
                {  "bSortable": true },  //1  [CompanyName]', '', '', '
                {  "bSortable": true },  //2  [ModifledDate]', '', '', '
                {  "bSortable": true },  //3  [ModifledBy]', '', '', '
                {                       //4  [InvoiceTemplateID]
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_,view_url;
                         action = '<div class = "hiddenRowData" >';

                        view_url = baseurl + "/invoice_template/{id}/view";
                         view_url = view_url.replace("{id}",id);

                         for(var i = 0 ; i< list_fields.length; i++){
                            action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'') + '" / >';
                         }
                         action += '</div>';
                         <?php if(User::checkCategoryPermission('InvoiceTemplates','Edit') ){ ?>
                            action += ' <a data-name = "'+full[0]+'" data-id="'+ id +'" class="edit-invoice_template btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                         <?php } ?>
                         action += ' <a  href="'+ view_url +'?Type=2" data-name = "'+full[0]+'" data-id="'+ id +'" class="view-invoice_template btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Item  View </a>';
                         action += ' <a  href="'+ view_url +'?Type=1" data-name = "'+full[0]+'" data-id="'+ id +'" class="view-invoice_template btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Periodic View </a>';
                         <?php if(User::checkCategoryPermission('InvoiceTemplates','Delete') ){ ?>
                            action += ' <a data-id="'+ id +'" class="delete-invoice_template btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
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
                        "sUrl": baseurl + "/invoice_template/ajax_datagrid", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   //After Delete done
                   FnDeleteInvoiceTemplateSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteInvoiceTemplate = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/invoice_template/"+id+"/delete" ,"",FnDeleteInvoiceTemplateSuccess );
                       }
                       return false;
                   }
                   $(".delete-invoice_template").click(FnDeleteInvoiceTemplate); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }

        });


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


    $('#add-new-invoice_template').click(function(ev){
        ev.preventDefault();
        $('#add-new-invoice_template-form').trigger("reset");
        $("#add-new-invoice_template-form [name='InvoiceTemplateID']").val('')
        $('#add-new-modal-invoice_template h4').html('Add New InvoiceTemplate');
        $("#add-new-invoice_template-form .LastInvoiceNumber").hide();
		$("#add-new-invoice_template-form .LastEstimateNumber").hide();
		

        $("#add-new-invoice_template-form [name='CompanyLogoUrl']").prop("src",'http://placehold.it/250x100');
        $('#add-new-modal-invoice_template #InvoiceStartNumberToggle ').show();
		
		$('#add-new-modal-invoice_template #EstimateStartNumberToggle').show();
		
		
        $('#add-new-modal-invoice_template').modal('show');
    });
    $('table tbody').on('click','.edit-invoice_template',function(e){
        e.preventDefault();
        e.stopPropagation();

        $('#add-new-invoice_template-form').trigger("reset");
        $('#add-new-invoice_template-form').trigger("reset");
        $('#add-new-modal-invoice_template #InvoiceStartNumberToggle ').hide();
		$('#add-new-modal-invoice_template #EstimateStartNumberToggle').hide();
        $('#add-new-modal-invoice_template').modal('show');

        $("#add-new-invoice_template-form .LastInvoiceNumber").show();
		$("#add-new-invoice_template-form .LastEstimateNumber").show();
        $("#add-new-invoice_template-form [name='CompanyID']").val($(this).prev("div.hiddenRowData").find("input[name='CompanyID']").val());
        $("#add-new-invoice_template-form [name='Name']").val($(this).prev("div.hiddenRowData").find("input[name='Name']").val());
        $("#add-new-invoice_template-form [name='InvoiceTemplateID']").val($(this).prev("div.hiddenRowData").find("input[name='InvoiceTemplateID']").val());
        $("#add-new-invoice_template-form [name='InvoiceStartNumber']").val($(this).prev("div.hiddenRowData").find("input[name='InvoiceStartNumber']").val());
        $("#add-new-invoice_template-form [name='InvoiceNumberPrefix']").val($(this).prev("div.hiddenRowData").find("input[name='InvoiceNumberPrefix']").val());
		$("#add-new-invoice_template-form [name='EstimateNumberPrefix']").val($(this).prev("div.hiddenRowData").find("input[name='EstimateNumberPrefix']").val());
        $("#add-new-invoice_template-form [name='InvoicePages']").selectBoxIt().data("selectBox-selectBoxIt").selectOption($(this).prev("div.hiddenRowData").find("input[name='InvoicePages']").val());
        $("#add-new-invoice_template-form [name='DateFormat']").selectBoxIt().data("selectBox-selectBoxIt").selectOption($(this).prev("div.hiddenRowData").find("input[name='DateFormat']").val());
        $("#add-new-invoice_template-form [name='LastInvoiceNumber']").val($(this).prev("div.hiddenRowData").find("input[name='LastInvoiceNumber']").val());
		
		$("#add-new-invoice_template-form [name='LastEstimateNumber']").val($(this).prev("div.hiddenRowData").find("input[name='LastEstimateNumber']").val());
		
        if($(this).prev("div.hiddenRowData").find("input[name='ShowZeroCall']").val() == 1 ){
            $('[name="ShowZeroCall"]').prop('checked',true)
        }else{
            $('[name="ShowZeroCall"]').prop('checked',false)
        }
        if($(this).prev("div.hiddenRowData").find("input[name='ShowPrevBal']").val() == 1 ){
            $('[name="ShowPrevBal"]').prop('checked',true)
        }else{
            $('[name="ShowPrevBal"]').prop('checked',false)
        }
        if($(this).prev("div.hiddenRowData").find("input[name='ShowBillingPeriod']").val() == 1 ){
            $('[name="ShowBillingPeriod"]').prop('checked',true)
        }else{
            $('[name="ShowBillingPeriod"]').prop('checked',false)
        }

        var InvoiceTemplateID = $(this).prev("div.hiddenRowData").find("input[name='InvoiceTemplateID']").val();

        $("#add-new-invoice_template-form [name='CompanyLogoUrl']").prop("src",'http://placehold.it/250x100');
        CompanyLogoUrl = baseurl + "/invoice_template/"+InvoiceTemplateID +"/get_logo";
        $.get( baseurl + "/invoice_template/"+InvoiceTemplateID+"/get_logo", function( data ) {
            CompanyLogoUrl = data;
            if(CompanyLogoUrl){
                $("#add-new-invoice_template-form [name='CompanyLogoUrl']").prop("src",CompanyLogoUrl);
            }
         });

        //console.log(Country);

        $('#add-new-modal-invoice_template h4').html('Edit Invoice Template');

    });

    $('#add-new-invoice_template-form').submit(function(e){
        e.preventDefault();
        var InvoiceTemplateID = $("#add-new-invoice_template-form [name='InvoiceTemplateID']").val()
        if( typeof InvoiceTemplateID != 'undefined' && InvoiceTemplateID != ''){
            update_new_url = baseurl + '/invoice_template/'+InvoiceTemplateID+'/update';
        }else{
            update_new_url = baseurl + '/invoice_template/create';
        }
        var formData = new FormData($('#add-new-invoice_template-form')[0]);
        ajax_update(update_new_url,formData);
        return false;
    });
});

function ajax_update(fullurl,data){
//alert(data)
     $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#invoice_template-update").button('reset');
            if (response.status == 'success') {
                $('#add-new-modal-invoice_template').modal('hide');
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
        cache: false,
        contentType: false,
        processData: false
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
<div class="modal fade custom-width" id="add-new-modal-invoice_template">
    <div class="modal-dialog" style="width: 60%;">
        <div class="modal-content">
            <form id="add-new-invoice_template-form" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Invoice Template</h4>
                </div>
                <div class="modal-body">

                         <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Template Name</label>
                            <div class="col-sm-4">
                                    <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                            </div>
                            <div id="InvoiceStartNumberToggle">
                            <label for="field-1" class="col-sm-2 control-label">Invoice Start Number</label>
                            <div class="col-sm-4">
                                <input type="text" name="InvoiceStartNumber" class="form-control" id="field-1" placeholder="" value="" />
                            </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Invoice Prefix</label>
                            <div class="col-sm-4">
                                    <input type="text" name="InvoiceNumberPrefix" class="form-control" id="field-5" placeholder="">
                            </div>
                            <div class="LastInvoiceNumber">
                            <label for="field-1" class="col-sm-2 control-label">Last Invoice Number</label>
                            <div class="col-sm-4">
                                    <input type="text" name="LastInvoiceNumber" class="form-control" id="field-5" placeholder="">
                            </div>
                            </div>
                        </div>
                        <div class="form-group">
                           <label for="field-1" class="col-sm-2 control-label">Estimate Prefix</label>
                            <div class="col-sm-4">
                                    <input type="text" name="EstimateNumberPrefix" class="form-control" id="field-5" placeholder="">
                            </div>
                            <div id="EstimateStartNumberToggle">
                            <label for="field-1" class="col-sm-2 control-label">Estimate Start Number</label>
                            <div class="col-sm-4">
                                <input type="text" name="EstimateStartNumber" class="form-control" id="field-1" placeholder="" value="" />
                            </div>
                            </div>
                            <div class="LastEstimateNumber">
                            <label for="field-1" class="col-sm-2 control-label">Last Estimate Number</label>
                            <div class="col-sm-4">
                                    <input type="text" name="LastEstimateNumber" class="form-control" id="field-5" placeholder="">
                            </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Pages</label>
                            <div class="col-sm-7">
                            <?php  $invoice_page_array =  array(''=>'Select Invoice Pages','single'=>'A single page with totals only','single_with_detail'=>'First page with totals + usage details attached on additional pages')?>
                              {{Form::select('InvoicePages',$invoice_page_array,'',array("class"=>"selectboxit"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Logo</label>
                            <div class="col-sm-10">
                                <div class="col-sm-6">
                                    <input id="picture" type="file" name="CompanyLogo" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                                </div>
                                <div class="col-sm-6">
                                    <img name="CompanyLogoUrl" src="http://placehold.it/250x100" width="100"> (Only Upload .jpg file)
                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Show Zero Call</label>
                                <div class="col-sm-4">
                                     <p class="make-switch switch-small">
                                        <input type="checkbox" checked=""  name="ShowZeroCall" value="0">
                                    </p>
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Show Previous Balance</label>
                                <div class="col-sm-4">
                                     <p class="make-switch switch-small">
                                        <input type="checkbox"    name="ShowPrevBal" value="0">
                                    </p>
                                </div>
                        </div>
                         <div class="form-group">
                            <label class="col-sm-2 control-label">Date Format</label>
                            <div class="col-sm-4">
                              {{Form::select('DateFormat',InvoiceTemplate::$invoice_date_format,'',array("class"=>"selectboxit"))}}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Show Billing Period</label>
                            <div class="col-sm-4">
                                 <p class="make-switch switch-small">
                                    <input type="checkbox"    name="ShowBillingPeriod" value="0">
                                </p>
                            </div>
                        </div>
                  </div>
                <div class="modal-footer">
                    <input type="hidden" name="InvoiceTemplateID" value="" />
                    <button type="submit" id="invoice_template-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
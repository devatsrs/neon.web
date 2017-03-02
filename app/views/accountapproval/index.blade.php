@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Account Checklist</strong>
    </li>
</ol>
<h3>Account Checklist</h3>

@include('includes.errors')
@include('includes.success')



<p style="text-align: right;">
@if(User::checkCategoryPermission('AccountChecklist','Add'))
    <a href="#" id="add-new-config" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="20%">Document Name</th>
        <th width="15%">Required For</th>
        <th width="15%">Attachment</th>
        <th width="10%">Country</th>
        <th width="10%">Billing Type</th>
        <th width="7%">Required</th>
        <th width="7%">Status</th>
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

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/accountapproval/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
             "aoColumns":
            [
                {  "bSortable": true },  //0   name', '', '', '

                {  mRender: function(required, type, full) {

                                                                   if (required == '{{AccountApproval::VENDOR}}')
                                                                        return 'Vendor';
                                                                   else if (required == '{{AccountApproval::CUSTOMER}}')
                                                                        return 'Customer';
                                                                   else
                                                                        return 'Customer & Vendor';
                                                               }
                                }, //1   Account Type
                 {  mRender: function(status, type, full) {
                        if(status !== null){
                            var b=status.match(/[\/|\\]([^\\\/]+)$/);
                            if(b !== null && b[1] ){
                                return '<a class="external"  href="'+baseurl + "/accounts/download_doc_file/"+full[7]+'" title="" > '+b[1]+'</a><br/>';

                            }
                            }
                 } },  //2   file name', '', '', '
                {  "bSortable": true },  //3   country', '', '', '
                {  mRender: function(required, type, full) {

                           if (required == '{{AccountApproval::BILLINGTYPE_PREPAID}}')
                                return 'Prepaid';
                           else if (required == '{{AccountApproval::BILLINGTYPE_POSTPAID}}')
                                return 'Postpaid';
                           else
                                return 'Prepaid & Postpaid';
                       }
                }, //4   Account Type
                {  mRender: function(required, type, full) {
                                                   if (required == 1)
                                                       return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                                                   else
                                                       return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                                               }
                }, //5   Required
                {  mRender: function(status, type, full) {
                                                   if (status == 1)
                                                       return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                                                   else
                                                       return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                                               }
                }, //6   Status

                {                       //7  AccountApprovalID
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_;
                         action = '<div class = "hiddenRowData" >';
                         action += '<input type = "hidden"  name = "Key" value = "' + full[0] + '" / >';
                         action += '<input type = "hidden"  name = "AccountType" value = "' + full[1] + '" / >';
                         action += '<input type = "hidden"  name = "BillingType" value = "' + full[4]+ '" / >';
                         action += '<input type = "hidden"  name = "Required" value = "' + full[5] + '" / >';
                         action += '<input type = "hidden"  name = "Status" value = "' + full[6] + '" / >';
                         action += '<input type = "hidden"  name = "Infomsg" value = "' + full[8] + '" / >';
                         action += '<input type = "hidden"  name = "CountryId" value = "' + full[9] + '" / >';
                         action += '</div>';
                         <?php if(User::checkCategoryPermission('AccountChecklist','Edit')){ ?>
                            action += ' <a data-name = "'+full[0]+'" data-id="'+ id +'" title="Edit" class="edit-config btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                         <?php } ?>
                         <?php if(User::checkCategoryPermission('AccountChecklist','Delete')){ ?>
                            action += ' <a data-id="'+ id +'" title="Delete" class="delete-config btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
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
                        "sUrl": baseurl + "/accountapproval/base_exports", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   //After Delete done
                   FnDeleteCongfigSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteConfig = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/accountapproval/delete/"+id ,"",FnDeleteCongfigSuccess );
                       }
                       return false;
                   }
                   $(".delete-config").click(FnDeleteConfig); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }

        });


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


    $('#add-new-config').click(function(ev){
        ev.preventDefault();
        $('#add-new-config-form').trigger("reset");
        $("#add-new-config-form [name='AccountApprovalID']").val('')
        $("#CountryId").select2().select2('val','');
        $('#add-new-modal-config h4').html('Add New Document');
        $('#add-new-modal-config').modal('show');
    });
    $('table tbody').on('click','.edit-config',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-config-form').trigger("reset");

        Key = $(this).prev("div.hiddenRowData").find("input[name='Key']").val();
        Required = $(this).prev("div.hiddenRowData").find("input[name='Required']").val();
        Status = $(this).prev("div.hiddenRowData").find("input[name='Status']").val();
        CountryId = $(this).prev("div.hiddenRowData").find("input[name='CountryId']").val();
        Infomsg = $(this).prev("div.hiddenRowData").find("input[name='Infomsg']").val();
        AccountType = $(this).prev("div.hiddenRowData").find("input[name='AccountType']").val();
        BillingType = $(this).prev("div.hiddenRowData").find("input[name='BillingType']").val();


        $("#add-new-config-form [name='Key']").val(Key);
        $("#add-new-config-form [name='Required']").val(Required);
        $("#add-new-config-form [name='Status']").val(Status);
        $("#add-new-config-form [name='Infomsg']").val(Infomsg);
        //$("#add-new-config-form [name='AccountType']").val(AccountType);
        $("#account_type").val(AccountType).trigger("change");
        $("#billing_type").val(BillingType).trigger("change");

        if(CountryId !=  'null' && CountryId !=  null){
            $("#CountryId").val(CountryId).trigger("change");
        }else{
            $("#CountryId").val('').trigger("change");
        }
        if(Required == 1 ){
            $('[name="Required_name"]').prop('checked',true)
        }else{
            $('[name="Required_name"]').prop('checked',false)
        }
        if(Status == 1 ){
            $('[name="Status_name"]').prop('checked',true)
        }else{
            $('[name="Status_name"]').prop('checked',false)
        }
        $("#add-new-config-form [name='AccountApprovalID']").val($(this).attr('data-id'));
        $('#add-new-modal-config h4').html('Edit Document');
        $('#add-new-modal-config').modal('show');
    })
    $('[name="Required_name"]').change(function(e){
        if($(this).prop('checked')){
            $("#add-new-config-form [name='Required']").val(1);
        }else{
            $("#add-new-config-form [name='Required']").val(0);
        }

    });

    $('[name="Status_name"]').change(function(e){
        if($(this).prop('checked')){
            $("#add-new-config-form [name='Status']").val(1);
        }else{
            $("#add-new-config-form [name='Status']").val(0);
        }

    });
    $('#add-new-config-form').submit(function(e){
        e.preventDefault();
        var AccountApprovalID = $("#add-new-config-form [name='AccountApprovalID']").val()
        if( typeof AccountApprovalID != 'undefined' && AccountApprovalID != ''){
            update_new_url = baseurl + '/accountapproval/update/'+AccountApprovalID;
        }else{
            update_new_url = baseurl + '/accountapproval/create';
        }
        var formData = new FormData($('#add-new-config-form')[0]);
        $.ajax({
            url: update_new_url,  //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                if(response.status =='success'){
                    toastr.success(response.message, "Success", toastr_opts);
                    $('#add-new-modal-config').modal('hide');
                     data_table.fnFilter('', 0);
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
                $("#config-update").button('reset');
            },
            // Form data
            data: formData,
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false,
            contentType: false,
            processData: false
        });
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
.export-data{
    display: none;
}
</style>
@stop


@section('footer_ext')
@parent
<div class="modal fade" id="add-new-modal-config">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-config-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Config</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Document Name</label>
                                <input type="text" name="Key" class="form-control" id="field-5" placeholder="">
                             </div>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Message</label>
                                <input type="text" name="Infomsg" class="form-control" id="field-5" placeholder="">
                             </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Country</label>
                                {{Form::select('CountryId', $countries, '',array("id"=>"CountryId","class"=>"form-control select2"))}}
                             </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Required For</label>
                                {{Form::select('AccountType', AccountApproval::$account_type, '',array('id'=>'account_type',"class"=>"select2 small"))}}
                             </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Billing Type</label>
                                {{Form::select('BillingType', AccountApproval::$billing_type_1, '',array('id'=>'billing_type',"class"=>"select2 small"))}}
                             </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                            <div class="row">
                                <label for="field-5" class="control-label col-md-3">Required</label>
                                <div class="col-md-3">
                                <p class="make-switch switch-small">
                                    <input type="checkbox" checked=""  name="Required_name" value="0">
                                    <input type="hidden"  name="Required" value="0">
                                </p>
                                </div>
                                <label for="field-5" class="control-label col-md-3">Active</label>
                                <div class="col-md-3">
                                    <p class="make-switch switch-small">
                                        <input type="checkbox" checked=""  name="Status_name" value="0">
                                    </p>
                                    <input type="hidden"  name="Status" value="0">
                                    <input type="hidden" name="AccountApprovalID" >
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Attachment</label>
                                <br/>
                                <input type="file" id="DocumentFiles"  name="DocumentFiles" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                             </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="config-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

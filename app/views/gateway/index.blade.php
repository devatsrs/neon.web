@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Gateway</strong>
    </li>
</ol>
<h3>Gateway</h3>

@include('includes.errors')
@include('includes.success')



<p style="text-align: right;">
@if( User::checkCategoryPermission('Gateway','Add') )
    <a href="#" id="add-new-config" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add Gateway
    </a>
@endif
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="20%">Gateway Name</th>
        <th width="20%">IP</th>
        <th width="10%">Status</th>
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
            "sAjaxSource": baseurl + "/gateway/ajax_datagrid/type",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "fnServerParams": function(aoData) {
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"Export","value":1});
            },
            "aaSorting": [[0, 'asc']],
             "aoColumns":
            [
                {  "bSortable": true },  //0   name', '', '', '
                {  "bSortable": true },  //1   name', '', '', '
                {  mRender: function(status, type, full) {
                                                   if (status == 1)
                                                       return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                                                   else
                                                       return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                                               }
                }, //2   Status
                {                       //3
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                    var GatewayID = full[3]>0?full[3]:'';
                        var action ='';
                         action = '<div class = "hiddenRowData" >';
                         action += '<input type = "hidden"  name = "GatewayID" value = "' + GatewayID + '" / >';
                         action += '<input type = "hidden"  name = "CompanyGatewayID" value = "' + full[4] + '" / >';
                         action += '<input type = "hidden"  name = "Title" value = "' + full[0] + '" / >';
                         action += '<input type = "hidden"  name = "Status" value = "' + full[2] + '" / >';
                         action += '<input type = "hidden"  name = "IP" value = "' +( full[1]!==null?full[1]:'') + '" / >';
                        action += '<input type = "hidden"  name = "TimeZone" value = "' +( full[5]!==null?full[5]:'') + '" / >';
                        action += '<input type = "hidden"  name = "BillingTimeZone" value = "' +( full[6]!==null?full[6]:'') + '" / >';
                         action += '</div>';

                         <?php if(User::checkCategoryPermission('Gateway','Edit') ){ ?>
                            action += ' <a data-name = "'+full[0]+'" data-id="'+ full[3]+'" class="edit-config btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                         <?php } ?>
                         <?php if(User::checkCategoryPermission('Gateway','Delete') ){ ?>
                            action += ' <a data-id="'+ full[4] +'" class="delete-config btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
                         <?php } ?>
                         if( full[4]>0){
                            action += ' <a data-id="'+ full[4]+'" class="test-connection btn btn-success btn-sm btn-icon icon-left"><i class="entypo-rocket"></i>Test Connection </a>';
                         }
                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/gateway/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/gateway/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
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
                           showAjaxScript( baseurl + "/gateway/delete/"+id ,"",FnDeleteCongfigSuccess );
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
        $("#add-new-config-form [name='CompanyGatewayID']").val('');
        $("#GatewayID").select2().select2('val','');
        $("#GatewayID").trigger('change');
        $('#add-new-modal-config h4').html('Add New Gateway');
        $('#add-new-modal-config').modal('show');
    });
    $('table tbody').on('click','.test-connection',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $(this).button('loading');
        submit_ajax(baseurl+'/gateway/test_connetion/'+$(this).attr('data-id'),'');
    });

    $('table tbody').on('click','.edit-config',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-config-form').trigger("reset");
        var prevrow = $(this).prev("div.hiddenRowData");
        $("#add-new-config-form [name='CompanyGatewayID']").val(prevrow.find("input[name='CompanyGatewayID']").val())
        $("#add-new-config-form [name='Title']").val(prevrow.find("input[name='Title']").val())
        $("#add-new-config-form [name='IP']").val(prevrow.find("input[name='IP']").val())
        $("#add-new-config-form [name='TimeZone']").select2().select2('val',prevrow.find("input[name='TimeZone']").val());
        $("#add-new-config-form [name='BillingTimeZone']").select2().select2('val',prevrow.find("input[name='BillingTimeZone']").val());
        if(prevrow.find("input[name='Status']").val() == 1 ){
            $('[name="Status_name"]').prop('checked',true)
        }else{
            $('[name="Status_name"]').prop('checked',false)
        }
        GatewayID = prevrow.find("input[name='GatewayID']").val()>0?prevrow.find("input[name='GatewayID']").val():'other';
        $("#GatewayID").select2().select2('val',GatewayID);
        $("#GatewayID").trigger('change');

        $('#add-new-modal-config h4').html('Edit Gateway');
        $('#add-new-modal-config').modal('show');
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
        var CompanyGatewayID = $("#add-new-config-form [name='CompanyGatewayID']").val()
        if( typeof CompanyGatewayID != 'undefined' && CompanyGatewayID != ''){
            update_new_url = baseurl + '/gateway/update/'+CompanyGatewayID;
        }else{
            update_new_url = baseurl + '/gateway/create';
        }
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
            data: $('#add-new-config-form').serialize(),
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false
        });
    });
            $('#GatewayID').change(function(e){
                $('#ajax_config_html').html('Loading...<br>');
                if($(this).val() != ''){
                $.ajax({
                    url: baseurl + "/gateway/ajax_load_gateway_dropdown",
                    type: 'POST',
                    success: function(response) {
                        $('#ajax_config_html').html(response);
                    },
                    // Form data
                    data: "GatewayID="+$(this).val()+'&CompanyGatewayID='+$("#add-new-config-form [name='CompanyGatewayID']").val(),
                    cache: false
                    });
                }else{
                    $('#ajax_config_html').html('');
                }
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
                                <label for="field-5" class="control-label">Gateway Type</label>
                                {{ Form::select('GatewayID',$gateway,'', array("class"=>"select2",'id'=>'GatewayID')) }}
                             </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Gateway Title</label>
                                <input name="Title" class="form-control" value="" placeholder="">
                             </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">IP address</label>
                                <input name="IP" class="form-control" value="" placeholder="">
                             </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Timezone</label>
                                {{Form::select('TimeZone', $timezones, '' ,array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">BillingTimeZone</label>
                                {{Form::select('BillingTimeZone', $timezones, '' ,array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                    </div>
                <div id="ajax_config_html"></div>
                <div class="row">
                    <label for="field-5" class="control-label col-md-3">Active</label>
                    <div class="clear col-md-3">
                        <p class="make-switch switch-small">
                            <input type="checkbox" checked=""  name="Status_name" value="0">
                        </p>
                        <input type="hidden"  name="Status" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="config-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                        <input type="hidden" name="CompanyGatewayID" value="">
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

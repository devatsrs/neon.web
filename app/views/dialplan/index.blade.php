@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Dial Plans</strong>
    </li>
</ol>
<h3>Dial Plans</h3>

@include('includes.errors')
@include('includes.success')


<!--<script src="{{URL::to('/')}}/assets/js/neon-fileupload.js" type="text/javascript"></script>-->

<p style="text-align: right;">
    @if( User::checkCategoryPermission('DialPlans','Add'))
    <a href="#" id="add-new-dialplan" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New DialPlan
    </a>
    @endif
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="30%">Name</th>
        <th width="25%">Create Date</th>
        <th width="25%">Created By</th>
        <th width="20%">Actions</th>
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
            "sAjaxSource": baseurl + "/dialplans/dialplan_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
             "aoColumns":
            [
                {  "bSortable": true },
                {  "bSortable": true },
                {  "bSortable": true },
                {
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                        var action , edit_ , show_ , delete_;
                        show_ = "{{ URL::to('dialplans/dialplancode/{id}')}}";
                        delete_ = "{{ URL::to('dialplans/{id}/delete_dialplan')}}";
                        show_ = show_.replace( '{id}', id);
                        delete_ = delete_.replace( '{id}', id);
                        action = '<a href="'+show_+'" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>View</a>';
                        <?php if(User::checkCategoryPermission('DialPlans','Edit') ){ ?>
                            action += ' <a data-name = "'+full[0]+'" data-type = "'+full[4]+'" data-id="'+ id +'" class="edit-dialplan btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('DialPlans','Delete') ){ ?>
                            action += ' <a href="'+ delete_ +'" class="delete-dialplan btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
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
                        "sUrl": baseurl + "/dialplans/exports/xlsx", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/dialplans/exports/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });

               $(".delete-dialplan").click(function(e) {
                   e.preventDefault();
                   response = confirm('Are you sure?');
                   if (response) {
                       $(this).text('Loading..');
                       $('#table-4_processing').css('visibility','visible');
                       $.ajax({
                           url: $(this).attr("href"),
                           type: 'POST',
                           dataType: 'json',
                           beforeSend: function(){
                               //    $(this).text('Loading..');
                           },
                           success: function(response) {
                               if (response.status == 'success') {
                                   toastr.success(response.message, "Success", toastr_opts);
                                   data_table.fnFilter('', 0);
                               } else {
                                   toastr.error(response.message, "Error", toastr_opts);
                                   data_table.fnFilter('', 0);
                               }
                               $('#table-4_processing').css('visibility','hidden');
                           },
                           // Form data
                           //data: {},
                           cache: false,
                           contentType: false,
                           processData: false
                       });
                   }
                   return false;

               });
           }

        });



        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


    $('#add-new-dialplan').click(function(ev){
        ev.preventDefault();
        $('#add-new-dialplan-form').trigger("reset");
        $("#add-new-dialplan-form [name='DialPlanID']").val('')
        $('#add-new-modal-dialplan h4').html('Add New Dial Plan');
        $('#add-new-modal-dialplan').modal('show');
    });
    $('table tbody').on('click','.edit-dialplan',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-dialplan-form').trigger("reset");
        $("#add-new-dialplan-form [name='Name']").val($(this).attr('data-name'));
        $("#add-new-dialplan-form [name='Type']").select2().select2('val',$(this).attr('data-type'));
        $("#add-new-dialplan-form [name='DialPlanID']").val($(this).attr('data-id'));
        $('#add-new-modal-dialplan h4').html('Edit Dial Plan');
        $('#add-new-modal-dialplan').modal('show');
    });

    $('#add-new-dialplan-form').submit(function(e){
        e.preventDefault();
        var DialPlanID = $("#add-new-dialplan-form [name='DialPlanID']").val();
        if( typeof DialPlanID != 'undefined' && DialPlanID != ''){
            update_new_url = baseurl + '/dialplans/update_dialplan/'+DialPlanID;
        }else{
            update_new_url = baseurl + '/dialplans/create_dialplan';
        }
        ajax_update(update_new_url,$('#add-new-dialplan-form').serialize());
    })


    });

function ajax_update(fullurl,data){
//alert(data)
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#dialplan-update").button('reset');
            $(".btn").button('reset');
            $('#modal-dialplan').modal('hide');

            if (response.status == 'success') {
                $('#add-new-modal-dialplan').modal('hide');
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
<div class="modal fade" id="add-new-modal-dialplan">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-dialplan-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Dial plan</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Dialplan Name</label>
                                <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                                <input type="hidden" name="DialPlanID" >
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" id="dialplan-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

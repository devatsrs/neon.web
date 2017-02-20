@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Dial Strings</strong>
    </li>
</ol>
<h3>Dial Strings</h3>

@include('includes.errors')
@include('includes.success')


<!--<script src="{{URL::to('/')}}/assets/js/neon-fileupload.js" type="text/javascript"></script>-->

<p style="text-align: right;">
    @if( User::checkCategoryPermission('DialStrings','Add'))
    <a href="#" id="add-new-dialstring" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New
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
            "sAjaxSource": baseurl + "/dialstrings/dialstring_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
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
                        show_ = "{{ URL::to('dialstrings/dialstringcode/{id}')}}";
                        delete_ = "{{ URL::to('dialstrings/{id}/delete_dialstring')}}";
                        show_ = show_.replace( '{id}', id);
                        delete_ = delete_.replace( '{id}', id);
                        action = '<a href="'+show_+'" title="View" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';
                        <?php if(User::checkCategoryPermission('DialStrings','Edit') ){ ?>
                            action += ' <a data-name = "'+full[0]+'" data-type = "'+full[4]+'" data-id="'+ id +'" title="Edit" class="edit-dialstring btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                        <?php } ?>
                        <?php if(User::checkCategoryPermission('DialStrings','Delete') ){ ?>
                            action += ' <a href="'+ delete_ +'" title="Delete" class="delete-dialstring btn delete btn-danger btn-sm"><i class="entypo-trash"></i></a>';
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
                        "sUrl": baseurl + "/dialstrings/exports/xlsx", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/dialstrings/exports/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });

               $(".delete-dialstring").click(function(e) {
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


    $('#add-new-dialstring').click(function(ev){
        ev.preventDefault();
        $('#add-new-dialstring-form').trigger("reset");
        $("#add-new-dialstring-form [name='DialStringID']").val('')
        $('#add-new-modal-dialstring h4').html('Add New Dial String');
        $('#add-new-modal-dialstring').modal('show');
    });
    $('table tbody').on('click','.edit-dialstring',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-dialstring-form').trigger("reset");
        $("#add-new-dialstring-form [name='Name']").val($(this).attr('data-name'));
        $("#add-new-dialstring-form [name='Type']").select2().select2('val',$(this).attr('data-type'));
        $("#add-new-dialstring-form [name='DialStringID']").val($(this).attr('data-id'));
        $('#add-new-modal-dialstring h4').html('Edit Dial String');
        $('#add-new-modal-dialstring').modal('show');
    });

    $('#add-new-dialstring-form').submit(function(e){
        e.preventDefault();
        var DialStringID = $("#add-new-dialstring-form [name='DialStringID']").val();
        if( typeof DialStringID != 'undefined' && DialStringID != ''){
            update_new_url = baseurl + '/dialstrings/update_dialstring/'+DialStringID;
        }else{
            update_new_url = baseurl + '/dialstrings/create_dialstring';
        }
        ajax_update(update_new_url,$('#add-new-dialstring-form').serialize());
    })


    });

function ajax_update(fullurl,data){
//alert(data)
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#dialstring-update").button('reset');
            $(".btn").button('reset');
            $('#modal-dialstring').modal('hide');

            if (response.status == 'success') {
                $('#add-new-modal-dialstring').modal('hide');
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
<div class="modal fade" id="add-new-modal-dialstring">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-dialstring-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Dial String</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Dial String Name</label>
                                <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                                <input type="hidden" name="DialStringID" >
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" id="dialstring-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

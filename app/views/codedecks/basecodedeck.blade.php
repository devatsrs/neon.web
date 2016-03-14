@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Code Decks</strong>
    </li>
</ol>
<h3>Code Decks</h3>

@include('includes.errors')
@include('includes.success')


<!--<script src="{{URL::to('/')}}/assets/js/neon-fileupload.js" type="text/javascript"></script>-->

<p style="text-align: right;">
    @if( User::can('CodeDecksController.cretecodedeck'))
    <a href="#" id="add-new-codedeck" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New CodeDeck
    </a>
    @endif
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="30%">CodedeckName</th>
        <th width="25%">Modified Date</th>
        <th width="25%">ModifiedBy</th>
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
            "sAjaxSource": baseurl + "/codedecks/base_datagrid",
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
                        show_ = "{{ URL::to('codedecks/basecodedeck/{id}')}}";
                        show_ = show_.replace( '{id}', id);
                        action = '<a href="'+show_+'" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>View</a>';                        
                        if('{{User::can('CodeDecksController.updatecodedeck')}}'){
                            action += ' <a data-name = "'+full[0]+'" data-type = "'+full[4]+'" data-id="'+ id +'" class="edit-codedeck btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                        }
                        if('{{User::can('CodeDecksController.base_delete')}}'){
                            action += ' <a data-id="'+ id +'" class="delete-codedecks btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
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
                        "sUrl": baseurl + "/codedecks/base_exports", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   //After Delete done
                   FnDeleteCodeDecksSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteCodeDecks = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/codedecks/"+id+"/base_delete" ,"",FnDeleteCodeDecksSuccess );
                       }
                       return false;
                   }
                   $(".delete-codedecks").click(FnDeleteCodeDecks); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }

        });


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });


    $('#add-new-codedeck').click(function(ev){
        ev.preventDefault();
        $('#add-new-codedeck-form').trigger("reset");
        $("#add-new-codedeck-form [name='CodeDeckId']").val('')
        $('#add-new-modal-codedeck h4').html('Add New Codedeck');
        $('#add-new-modal-codedeck').modal('show');
    });
    $('table tbody').on('click','.edit-codedeck',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-codedeck-form').trigger("reset");
        $("#add-new-codedeck-form [name='CodedeckName']").val($(this).attr('data-name'));
        $("#add-new-codedeck-form [name='Type']").select2().select2('val',$(this).attr('data-type'));
        $("#add-new-codedeck-form [name='CodeDeckId']").val($(this).attr('data-id'));
        $('#add-new-modal-codedeck h4').html('Edit Codedeck');
        $('#add-new-modal-codedeck').modal('show');
    })

    $('#add-new-codedeck-form').submit(function(e){
        e.preventDefault();
        var CodeDeckId = $("#add-new-codedeck-form [name='CodeDeckId']").val()
        if( typeof CodeDeckId != 'undefined' && CodeDeckId != ''){
            update_new_url = baseurl + '/codedecks/updatecodedeck/'+CodeDeckId;
        }else{
            update_new_url = baseurl + '/codedecks/cretecodedeck';
        }
        ajax_update(update_new_url,$('#add-new-codedeck-form').serialize());
    })


    });

function ajax_update(fullurl,data){
//alert(data)
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#codedeck-update").button('reset');
            $(".btn").button('reset');
            $('#modal-Codedeck').modal('hide');

            if (response.status == 'success') {
                $('#add-new-modal-codedeck').modal('hide');
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
<div class="modal fade" id="modal-fileformat">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Code Decks File Format</h4>
            </div>



            <div class="modal-body">
            <p>All columns are mandatory and the first line should have the column headings.</p>
                        <table class="table responsive">
                            <thead>
                                <tr>
                                    <th>Country</th>
                                    <th>Code</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Afghanistan</td>
                                    <td>9379</td>
                                    <td>Afghanistan Cellular-Others</td>
                                    <td>I <span data-original-title="Insert" data-content="When action is set to 'I', It will insert new CodeDeck" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                                </tr>
                                <tr>
                                    <td>Afghanistan</td>
                                    <td>9377</td>
                                    <td>Afghanistan Cellular-Areeba</td>
                                    <td>U <span data-original-title="Insert" data-content="When action is set to 'U',It will replace existing CodeDeck" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                                </tr>
                                <tr>
                                    <td>Afghanistan</td>
                                    <td>9378</td>
                                    <td>Afghanistan Cellular-Etisalat</td>
                                    <td>D <span data-original-title="Insert" data-content="When action is set to 'D',It will delete existing CodeDeck" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                                </tr>
                            </tbody>
                        </table>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="add-new-modal-codedeck">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-codedeck-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Codedeck</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Codedeck Name</label>
                                <input type="text" name="CodedeckName" class="form-control" id="field-5" placeholder="">
                                <input type="hidden" name="CodeDeckId" >
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" id="codedeck-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

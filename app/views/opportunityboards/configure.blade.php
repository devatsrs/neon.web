@extends('layout.main')

@section('content')

<div id="content">
    <div class="row">
        <ol class="breadcrumb bc-3">
            <li>
                <a href="index.html"><i class="entypo-home"></i>Home</a>
            </li>
            <li class="active">
                <a href="{{URL::to('opportunityboards')}}"> Board</a>
            </li>
            <li class="active">
                <strong>{{$Board->BoardName}}</strong>
            </li>
        </ol>
    </div>

    <h3>Configure Board</h3>
    <div id="manage-board">
        <p style="text-align: right;">
            <a href="{{URL::to('/'.$urlto)}}" class="btn btn-danger btn-sm btn-icon icon-left">
                <i class="entypo-cancel"></i>
                Close
            </a>
            <a href="javascript:void(0)" id="add-colomn" class="btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-plus"></i>Add Column</a>
        </p>
        <div class="row">
            <div class="col-md-12">
                <div class="deals-board">
                    <div id="board-start" class="board manage-board-main">
                        <ul id="deals-dashboard" class="no-select manage-board-inner ui-sortable">
                        </ul>
                        <div class="clearer">&nbsp;</div>
                    </div>
                </div>
                <form id="columnorder" method="POST" />
                    <input type="hidden" name="columnorder" />
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var BoardID = '{{$id}}';
            fillColumns();
            $('#add-colomn').click(function(){
                $('#add-edit-columnname-form').trigger("reset");
                $('#add-edit-columnname-form [name="BoardID"]').val(BoardID);
                $('#add-edit-columnname-form [name="BoardColumnID"]').val('');
                $('#add-edit-columnname-form [name="SetCompleted"]').prop('checked', false);
                $('#add-edit-columnname-modal h4').text('Add New Column');
                $('#add-edit-columnname-modal').modal('show');
            });
            $(document).on('click','.edit-column',function(){
                var BoardColumnID = $(this).attr('data-id');
                var BoardColumnName = $(this).attr('data-name');
                var SetCompleted = $(this).attr('data-setcompleted')==1?true:false;
                $('#add-edit-columnname-form [name="BoardID"]').val(BoardID);
                $('#add-edit-columnname-form [name="BoardColumnID"]').val(BoardColumnID);
                $('#add-edit-columnname-form [name="BoardColumnName"]').val(BoardColumnName);

                if($(this).attr('data-setcompleted')==1){
                    $('#add-edit-columnname-form').find('[name="SetCompleted"]').prop('checked', true);
                }else{
                    $('#add-edit-columnname-form').find('[name="SetCompleted"]').prop('checked', false);
                }
                $('#add-edit-columnname-modal h4').text('Edit Column');
                $('#add-edit-columnname-modal').modal('show');
            });

            $('#add-edit-columnname-form').submit(function(e){
                e.preventDefault();
                var update_new_url = '';
                var BoardColumnID = $('#add-edit-columnname-form').find('[name="BoardColumnID"]').val();
                var update_new_url = baseurl + '/opportunityboardcolumn/create';
                if( typeof BoardColumnID != 'undefined' && BoardColumnID != ''){
                    update_new_url = baseurl + '/opportunityboardcolumn/'+BoardColumnID+'/update';
                }else{
                    update_new_url = baseurl + '/opportunityboardcolumn/create';
                }
                var formData = new FormData($('#add-edit-columnname-form')[0]);
                $.ajax({
                    url: update_new_url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-edit-columnname-modal').modal('hide');
                            fillColumns();
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $("#opportunityboardcolumn-update").button('reset');
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            function initdrageable(){
                var dealboard = $('#deals-dashboard');
                dealboard.sortable({
                    placeholder: 'placeholder-2',
                    stop: function() {
                        postorder();
                    }
                });
            }

            function fillColumns(){
                var url = baseurl + '/opportunityboardcolumn/{{$id}}/ajax_datacolumn';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(!response.status) {
                            $('#deals-dashboard').empty();
                            $(response).each(function (i, item) {
                                $('#deals-dashboard').append(builditem(item));
                                initdrageable();
                            });
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    // Form data
                    //data: {},
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function builditem(item){
                var items = '<li id="'+item.BoardColumnID+'" class="board-column count-li" style="position: relative;">';
                items += '   <header>';
                items += '    <h5>'+item.BoardColumnName;
                items += '        <a class="edit-column" data-name="'+item.BoardColumnName+'" data-id="'+item.BoardColumnID+'" data-height="'+item.Height+'" data-setcompleted="'+item.SetCompleted+'" data-width="'+item.Width+'"><i class="edit-button-color entypo-pencil pull-right"></i></a>';
                items += '    </h5>';
                items += '   </header>';
                items += '</li>';
                return items;
            }

            function postorder(){
                saveOrder();
                url = baseurl + '/opportunityboardcolumn/'+BoardID+'/updateColumnOrder';
                var formData = new FormData($('#columnorder')[0]);
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){

                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                            fillColumns();
                        }
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function saveOrder() {
                var selectedColumns = new Array();
                $('#deals-dashboard li.count-li').each(function() {
                    selectedColumns.push($(this).attr("id"));
                });
                $('#columnorder [name="columnorder"]').val(selectedColumns);
            }

        });
    </script>

    @include('includes.errors')
    @include('includes.success')
</div>

@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="add-edit-columnname-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-edit-columnname-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add Column Name</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 margin-top">
                                <div class="form-group">
                                    <label class="control-label col-sm-3">Column Name</label>
                                    <div class="col-sm-9">
                                        <input name="BoardColumnName" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="opportunityboardcolumn-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <input type="hidden" name="BoardID">
                        <input type="hidden" name="BoardColumnID">
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

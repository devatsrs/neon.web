@extends('layout.main')

@section('content')

<div id="content">
    <div class="row">
        <ol class="breadcrumb bc-3">
            <li>
                <a href="index.html"><i class="entypo-home"></i>Home</a>
            </li>
            <li>
                <a href="#">Opportunity</a>
            </li>
            <li class="active">
                <a href="{{URL::to('opportunityboards')}}">Board</a>
            </li>
            <li class="active">
                <strong>{{$OpportunityBoard->OpportunityBoardName}}</strong>
            </li>
        </ol>
    </div>

    <h3>Manage Opportunity Board</h3>
    <div id="manage-board">
        <div class="row">
            <div class="col-md-6 pull-left">

            </div>
            <div class="col-md-3 pull-right">
                <a href="{{URL::to('/opportunityboards')}}" class="btn btn-primary"><i class="entypo-plus"></i>Close</a>
                <a href="javascript:void(0)" id="add-colomn" class="btn btn-primary"><i class="entypo-plus"></i>Add Column</a>
            </div>
        </div>
        <div class="row">
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

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var OpportunityBoardID = {{$id}};
            fillColumns();
            $('#add-colomn').click(function(){
                $('#add-edit-columnname-form').trigger("reset");
                $('#add-edit-columnname-form [name="OpportunityBoardID"]').val(OpportunityBoardID);
                $('#add-edit-columnname-form [name="OpportunityBoardColumnID"]').val('');
                $('#add-edit-columnname-modal h4').text('Add New Column');
                $('#add-edit-columnname-modal').modal('show');
            });
            $(document).on('click','.edit-column',function(){
                var OpportunityBoardColumnID = $(this).attr('data-id');
                var OpportunityBoardColumnName = $(this).attr('data-name');
                $('#add-edit-columnname-form [name="OpportunityBoardID"]').val(OpportunityBoardID);
                $('#add-edit-columnname-form [name="OpportunityBoardColumnID"]').val(OpportunityBoardColumnID);
                $('#add-edit-columnname-form [name="OpportunityBoardColumnName"]').val(OpportunityBoardColumnName);
                $('#add-edit-columnname-modal h4').text('Edit Column');
                $('#add-edit-columnname-modal').modal('show');
            });

            $('#add-edit-columnname-form').submit(function(e){
                e.preventDefault();
                var update_new_url = '';
                var OpportunityBoardColumnID = $('#add-edit-columnname-form').find('[name="OpportunityBoardColumnID"]').val();
                var update_new_url = baseurl + '/opportunityboardcolumn/create';
                if( typeof OpportunityBoardColumnID != 'undefined' && OpportunityBoardColumnID != ''){
                    update_new_url = baseurl + '/opportunityboardcolumn/'+OpportunityBoardColumnID+'/update';
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
                        $('#deals-dashboard').empty();
                        $(response).each(function(i,item){
                            $('#deals-dashboard').append(builditem(item));
                            initdrageable();
                        });
                    },
                    // Form data
                    //data: {},
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function builditem(item){
                var items = '<li id="'+item.OpportunityBoardColumnID+'" class="board-column count-li" style="position: relative;">';
                items += '   <header>';
                items += '    <h5>'+item.OpportunityBoardColumnName;
                items += '        <a class="edit-column" data-name="'+item.OpportunityBoardColumnName+'" data-id="'+item.OpportunityBoardColumnID+'"><i class="edit-button-color entypo-pencil pull-right"></i></a>';
                items += '    </h5>';
                items += '   </header>';
                items += '</li>';
                return items;
            }

            function postorder(){
                saveOrder();
                url = baseurl + '/opportunityboardcolumn/'+OpportunityBoardID+'/updateColumnOrder';
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
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Column Name</label>
                            <div class="col-sm-5">
                                <input name="OpportunityBoardColumnName" type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="opportunityboardcolumn-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <input type="hidden" name="OpportunityBoardID">
                        <input type="hidden" name="OpportunityBoardColumnID">
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

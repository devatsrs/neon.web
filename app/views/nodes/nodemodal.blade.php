
<script>
    $(document).ready(function ($) {

        $('#add-new-node-form').submit(function(e){
            e.preventDefault();
            var ServerID = $("#add-new-node-form [name='ServerID']").val();
            if( typeof ServerID != 'undefined' && ServerID != ''){
                update_new_url = baseurl + '/node/update/'+ServerID;
            }else{
                update_new_url = baseurl + '/node/store';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-new-node-form')[0])), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    checkBoxArray = [];
                    $('#add-new-modal-node').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    data_table.fnFilter('', 0);
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        })
    });
</script>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-node">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-node-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Node</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Name*</label>
                                    <input type="text" name="ServerName" class="form-control" id="field-5" placeholder="">
                                    <input type="hidden" name="ServerID" >
                                </div>
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Server IP*</label>
                                    <input type="text" name="ServerIP" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <br>
                            <div class="col-md-12">
                                <label class="control-label">Status</label>
                                <div class="panel-options">
                                    <div class="make-switch switch-small" >
                                        <input type="checkbox" name="status" checked>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="Package-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
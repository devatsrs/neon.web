<?php
 if(empty($PageRefresh)){
     $PageRefresh='';
 }
?>
<script>
    $(document).ready(function ($) {
        $('#add-new-routingcategory-form').submit(function(e){
            e.preventDefault();
            var PageRefresh = '{{$PageRefresh}}';
            var RoutingCategoryID = $("#add-new-routingcategory-form [name='RoutingCategoryID']").val();
            console.log(RoutingCategoryID);
            if( RoutingCategoryID != ''){
                update_new_url = baseurl + '/routingcategory/update/'+RoutingCategoryID;
            }else{
                update_new_url = baseurl + '/routingcategory/create';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-new-routingcategory-form')[0])), function(response){
                console.log(response);
                $(".btn").button('reset');
                if (response.status == 'success') {
                    $('#add-new-modal-routingcategory').modal('hide');
                    data_table.fnFilter('', 0);
                    
                    toastr.success(response.message, "Success", toastr_opts);
                    $('select[data-type="routingcategory"]').each(function(key,el){
                        if($(el).attr('data-active') == 1) {
                            var newState = new Option(response.newcreated.Code, response.newcreated.RoutingCategoryID, true, true);
                        }else{
                            var newState = new Option(response.newcreated.Code, response.newcreated.RoutingCategoryID, false, false);
                        }
                        $(el).append(newState).trigger('change');
                        $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                            return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                        }));
                    });
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        })
    });
</script>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-routingcategory">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-routingcategory-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title">Add New Routing Category</h3>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Name</label>
                                    <input type="text"  name="Name" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Description</label>
                                    <textarea name="Description" class="form-control" id="field-6" placeholder=""></textarea>
                                    <input type="hidden" name="RoutingCategoryID" >
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
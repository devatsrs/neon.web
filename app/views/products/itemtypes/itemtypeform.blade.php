<script>
    $(document).ready(function ($) {
        var txtItem = '{{Product::$TypetoProducts[Product::ITEM]}}';
        var productsubscription = $("#add-edit-product-subscription [name='productsubscription']").val();
        $('#add-edit-itemtype-form').submit(function(e){
            e.preventDefault();
            var modal = $(this).parents('.modal');
            var composit = modal.hasClass('composite')?1:0;
            var datatype = 'select[data-type="item"]';
            var ItemTypeID = $("#add-edit-itemtype-form [name='ItemTypeID']").val();
            var ProductClone = $("#add-edit-itemtype-form [name='ProductClone']").val();

            if( typeof ItemTypeID != 'undefined' && ItemTypeID != ''){
                update_new_url = baseurl + '/products/itemtypes/'+ItemTypeID+'/update';
            }else{
                update_new_url = baseurl + '/products/itemtypes/create';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-edit-itemtype-form')[0])), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    modal.modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    if( typeof data_table !=  'undefined'){
                        data_table.fnFilter('', 0);
                    }else {
                        if (composit==1) {
                            datatype = '.optgroup_'+txtItem;
                        }
                        $(datatype).each(function (key, el) {
                            if (composit==1) {
                                var self = $(el).parents('select');
                            }else{
                                var self = $(el).clone();
                            }
                            if(productsubscription==1){
                                NewProductID = '1-' + response.newcreated.ItemTypeID;
                                var newState = $('<option>').val(NewProductID).text(response.newcreated.Name).attr({'item_subscription_txt':'Item','item_subscription_type':'1','selected':'selected'});
                            }else{
                                if (self.attr('data-active') == 1) {
                                    var newState = new Option(response.newcreated.Name, NewProductID, true, true);
                                } else {
                                    var newState = new Option(response.newcreated.Name, NewProductID, false, false);
                                }
                            }

                            $(el).append(newState);
                            self.trigger('change');
                            $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                                return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                            }));
                        });
                        $('select[data-active="1"]').change();
                    }
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        })
    });
</script>
<form id="add-edit-itemtype-form" method="post">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Title *</label>
                    <input type="text" name="Title" class="form-control" id="field-5" placeholder="">
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Active</label>
                    <p class="make-switch switch-small">
                        <input id="Active" name="Active" type="checkbox" value="1" checked >
                    </p>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="ItemTypeID" />
    <input type="hidden" name="ProductClone" value="" />
    <div class="modal-footer">
        <button type="submit" id="itemtype-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Save
        </button>
        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
            <i class="entypo-cancel"></i>
            Close
        </button>
    </div>
</form>
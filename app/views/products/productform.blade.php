<script>
    $(document).ready(function ($) {
        var txtItem = '{{Product::$TypetoProducts[Product::ITEM]}}';
        $('#add-edit-product-form').submit(function(e){
            e.preventDefault();
            var modal = $(this).parents('.modal');
            var composit = modal.hasClass('composite')?1:0;
            var datatype = 'select[data-type="item"]';
            var ProductID = $("#add-edit-product-form [name='ProductID']").val()
            if( typeof ProductID != 'undefined' && ProductID != ''){
                update_new_url = baseurl + '/products/'+ProductID+'/update';
            }else{
                update_new_url = baseurl + '/products/create';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-edit-product-form')[0])), function(response){
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
                            if (self.attr('data-active') == 1) {
                                var newState = new Option(response.newcreated.Name, response.newcreated.ProductID, true, true);
                            } else {
                                var newState = new Option(response.newcreated.Name, response.newcreated.ProductID, false, false);
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
<form id="add-edit-product-form" method="post">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Item Name *</label>
                    <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Item Code *</label>
                    <input type="text" name="Code" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Description *</label>
                    <input type="text" name="Description" class="form-control" id="field-5" placeholder="">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Unit Cost *</label>
                    <input type="text" name="Amount" class="form-control" id="field-5" placeholder="" maxlength="10">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Note</label>
                    <textarea name="Note" class="form-control"></textarea>
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
    <input type="hidden" name="ProductID" />
    <div class="modal-footer">
        <button type="submit" id="product-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Save
        </button>
        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
            <i class="entypo-cancel"></i>
            Close
        </button>
    </div>
</form>
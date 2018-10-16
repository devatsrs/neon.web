<script>
    $(document).ready(function ($) {
        $('#tooltip_lowstock').tooltip();
        var txtItem = '{{Product::$TypetoProducts[Product::ITEM]}}';
        var productsubscription = $("#add-edit-product-subscription [name='productsubscription']").val();
        $('#add-edit-product-form').submit(function(e){
            e.preventDefault();
            var modal = $(this).parents('.modal');
            var composit = modal.hasClass('composite')?1:0;
            var datatype = 'select[data-type="item"]';
            var ProductID = $("#add-edit-product-form [name='ProductID']").val();
            var ProductClone = $("#add-edit-product-form [name='ProductClone']").val();
            if( typeof ProductID != 'undefined' && ProductID != '' && typeof ProductClone != 'undefined' && ProductClone == '1'){
                update_new_url = baseurl + '/products/create';
            }else if( typeof ProductID != 'undefined' && ProductID != ''){
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
                            if(productsubscription==1){
                                NewProductID = '1-' + response.newcreated.ProductID;
                                var newState = $('<option>').val(NewProductID).text(response.newcreated.Name).attr({'item_subscription_txt':'Item','item_subscription_type':'1','selected':'selected'});
                            }else{
                                if (self.attr('data-active') == 1) {
                                    var newState = new Option(response.newcreated.Name, NewProductID, true, true);
                                } else {
                                    var newState = new Option(response.newcreated.Name, NewProductID, false, false);
                                }
                            }

                            $(el).append(newState);
                            if(composit!=1){
                                self.trigger('change');
                            }
                            $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                                return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                            }));
                        });
                        $('select[data-active="1"]').change();
                        if(composit==1){
                            var itemdropdown=$('#rowContainer .itemrow').find(".product_dropdown");
                            $('option:selected', itemdropdown).remove();
                        }
                    }
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        })

        $("#add-edit-product-form #ItemType").on('change',function(){
            var itemTypeID=$(this).val();
            var ProductID = $("#add-edit-product-form [name='ProductID']").val();
            if(typeof(itemTypeID)!='undefined' && itemTypeID!=null && itemTypeID!='0'){
                $('#ajax_dynamicfield_html').html('Loading...<br>');
                var url=baseurl + '/products/'+itemTypeID+'/change_type';
                $.ajax({
                    type: "POST",
                    url: url ,
                    data:'ProductID='+ProductID,
                    cache: false,
                    success: function(response){
                        $('#ajax_dynamicfield_html').html(response);
                        //perform operation
                    },
                    error: function(error) {
                        $('#ajax_dynamicfield_html').html('');
                        $(".btn").button('reset');
                        ShowToastr("error", error);
                    }
                });

            }else{
                $('#ajax_dynamicfield_html').html('');
            }

        });

        $(".modal-dialog").css("width","70%");

    });
</script>

<form id="add-edit-product-form" method="post">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label col-sm-2">Item Type *</label>
                    <div class="col-sm-4">
                        {{Form::select('ItemTypeID',$itemtypes,'',array("id"=>"ItemType","class"=>"form-control select2 small"))}}
                    </div>
                    <label for="field-5" class="control-label col-sm-2">Item Name *</label>
                    <div class="col-sm-4">
                        <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                    </div>
                </div>
            </div>
        </div>

        <div class="row margin-top">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label col-sm-2">Item Code *</label>
                    <div class="col-sm-4">
                        <input type="text" name="Code" class="form-control" id="field-5" placeholder="">
                    </div>
                    <label for="field-5" class="control-label col-sm-2">Description *</label>
                    <div class="col-sm-4">
                        <input type="text" name="Description" class="form-control" id="field-5" placeholder="">
                    </div>
                </div>
            </div>
        </div>

        <div class="row margin-top">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label col-sm-2">Buying Price </label>
                    <div class="col-sm-4">
                        <input type="text" name="BuyingPrice" class="form-control" id="field-5" placeholder="" maxlength="10">
                    </div>
                    <label for="field-5" class="control-label col-sm-2">Unit Cost(Selling Price) *</label>
                    <div class="col-sm-4">
                        <input type="text" name="Amount" class="form-control" id="field-5" placeholder="" maxlength="10">
                    </div>
                </div>
            </div>
        </div>

        <div class="row margin-top">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label col-sm-2">Quantity </label>
                    <div class="col-sm-4">
                        <input type="text" name="Quantity" class="form-control" id="field-5" placeholder="" maxlength="10">
                    </div>
                    <label for="field-5" class="control-label col-sm-2">Low Stock Level  <span id="tooltip_lowstock" data-content="Low Stock Reminder will be sent if stock will go below this level" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></label>

                    <div class="col-sm-4">
                        <input type="text" name="LowStockLevel" class="form-control" id="field-5" placeholder="" maxlength="10">
                    </div>
                </div>
            </div>
        </div>

        <div class="row margin-top">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label col-sm-2">Note </label>
                    <div class="col-sm-4">
                        <textarea name="Note" class="form-control"></textarea>
                    </div>
                    <label for="field-5" class="control-label col-sm-2">Active</label>
                    <div class="col-sm-4">
                        <p class="make-switch switch-small">
                            <input id="Active" name="Active" type="checkbox" value="1" checked >
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row margin-top">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label col-sm-2">Applied To </label>
                    <div class="col-sm-4">
                        {{Form::select('AppliedTo',Product::$AppliedTo,'',array("class"=>"form-control select2 small"))}}
                    </div>

                    <label for="field-5" class="control-label col-sm-2">Image <br> (.jpeg, .png, .jpg, .gif)</label>
                    <div class="col-sm-4">
                        <div class="clear clearfix"></div>
                        <input id="Image" name="Image" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                        <div id="download_attach" class="pull-right" style="margin-right: 150px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="ajax_dynamicfield_html" class="margin-top"></div>
        <?php
        /*
        @if (isset($DynamicFields) && $DynamicFields['totalfields'] > 0)
            @foreach($DynamicFields['fields'] as $field)
                @if($field->Status == 1)

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="field-5" class="control-label">{{ $field->FieldName }}</label>
                        {{Form::text('DynamicFields['.$field->DynamicFieldsID.']', '',array("class"=>"form-control"))}}
                    </div>
                </div>

                @endif
            @endforeach
        @endif
        */ ?>

    </div>
    <input type="hidden" name="ProductID" />
    <input type="hidden" name="ProductClone" value="" />
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


<script>
    $(document).ready(function ($) {
        var txtItem = '{{Product::$TypetoProducts[Product::ITEM]}}';
        var productsubscription = $("#add-edit-product-subscription [name='productsubscription']").val();
        $('#add-edit-dynamicfield-form').submit(function(e){
            e.preventDefault();
            var modal = $(this).parents('.modal');
            var composit = modal.hasClass('composite')?1:0;
            var datatype = 'select[data-type="item"]';
            var DynamicFieldsID = $("#add-edit-dynamicfield-form [name='DynamicFieldsID']").val();
            var ProductClone = $("#add-edit-dynamicfield-form [name='ProductClone']").val();

            if( typeof DynamicFieldsID != 'undefined' && DynamicFieldsID != ''){
                update_new_url = baseurl + '/products/dynamicfields/'+DynamicFieldsID+'/update';
            }else{
                update_new_url = baseurl + '/products/dynamicfields/create';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-edit-dynamicfield-form')[0])), function(response){
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
                                NewDynamicFieldsID = '1-' + response.newcreated.DynamicFieldsID;
                                var newState = $('<option>').val(NewDynamicFieldsID).text(response.newcreated.Name).attr({'item_subscription_txt':'Item','item_subscription_type':'1','selected':'selected'});
                            }else{
                                if (self.attr('data-active') == 1) {
                                    var newState = new Option(response.newcreated.Name, NewDynamicFieldsID, true, true);
                                } else {
                                    var newState = new Option(response.newcreated.Name, NewDynamicFieldsID, false, false);
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

        $("#add-edit-dynamicfield-form [name='FieldDomType']").on('change',function(){
            var domtype=$(this).val();
            var DynamicFieldsID = $("#add-edit-dynamicfield-form [name='DynamicFieldsID']").val();
            if(typeof(domtype)!='undefined' && domtype!='' && DynamicFieldsID==''){
                if(domtype=='numeric' || domtype=='string'){
                    var minmax='<div class="form-group"><label for="field-5" class="control-label">Default Value </label>{{ Form::text("DefaultValue", "", array("class"=>"form-control"))  }}</div><div class="form-group"><label for="field-5" class="control-label">Min </label>{{ Form::text("Minimum", "", array("class"=>"form-control"))  }}</div><div class="form-group"><label for="field-5" class="control-label">Max </label>{{ Form::text("Maximum", "", array("class"=>"form-control"))  }}</div>';
                    $("#minmaxdiv").html(minmax);
                }else if(domtype=='select'){
                    var selectVal='<div class="form-group"><label for="field-5" class="control-label">Select Value (separated by comma) </label>{{ Form::text("SelectVal", "", array("class"=>"form-control"))  }}</div>';
                    $("#minmaxdiv").html(selectVal);
                }else{
                    $("#minmaxdiv").html('');
                }
            }else{
                $("#minmaxdiv").html('');
            }

        });

    });
</script>
<form id="add-edit-dynamicfield-form" method="post">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Item Type *</label>
                    {{Form::select('ItemTypeID',$itemtypes,'',array("class"=>"form-control select2 small"))}}
                </div>
                <div class="form-group">
                    <label for="field-5" class="control-label">DOM Type *</label>
                    <?php
                    $FieldDomTypes=[''=>'Select DOM Type','string'=>'String','numeric'=>'Numeric','textarea'=>'Text Area','select'=>'Select','file'=>'File','datetime'=>'DateTime','boolean'=>'Boolean'];
                    ?>
                    {{Form::select('FieldDomType',$FieldDomTypes,'',array("class"=>"form-control select2 small"))}}
                </div>
                <div id="minmaxdiv"></div>
                <div class="form-group">
                    <label for="field-5" class="control-label">Field Name *</label>
                    <input type="text" name="FieldName" class="form-control" id="field-5" placeholder="">
                </div>
                <div class="form-group">
                    <label for="field-5" class="control-label">Field Description</label>
                    <textarea name="FieldDescription" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label for="field-5" class="control-label">Field Order</label>
                    <input type="text" name="FieldOrder" class="form-control" id="field-5" placeholder="">
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
    <input type="hidden" name="DynamicFieldsID" />
    <input type="hidden" name="ProductClone" value="" />
    <div class="modal-footer">
        <button type="submit" id="dynamicfield-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Save
        </button>
        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
            <i class="entypo-cancel"></i>
            Close
        </button>
    </div>
</form>
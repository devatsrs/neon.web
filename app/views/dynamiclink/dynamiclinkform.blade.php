<script>
    $(document).ready(function ($) {

        $('#add-edit-dynamicfield-form').submit(function(e){
            e.preventDefault();
            var modal = $(this).parents('.modal');
            var composit = modal.hasClass('composite')?1:0;
            var datatype = 'select[data-type="item"]';
            var DynamicLinkID = $("#add-edit-dynamicfield-form [name='DynamicLinkID']").val();
            console.log(DynamicLinkID);

            if( typeof DynamicLinkID != 'undefined' && DynamicLinkID != ''){
                update_new_url = baseurl + '/dynamiclink/'+DynamicLinkID+'/update';
            }else{
                update_new_url = baseurl + '/dynamiclink/create';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-edit-dynamicfield-form')[0])), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    modal.modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    if( typeof data_table !=  'undefined'){
                        data_table.fnFilter('', 0);
                    }else {
                        $(datatype).each(function (key, el) {
                            if (composit==1) {
                                var self = $(el).parents('select');
                            }else{
                                var self = $(el).clone();
                            }

                            if (self.attr('data-active') == 1) {
                                var newState = new Option(response.newcreated.Name, NewDynamicFieldsID, true, true);
                            } else {
                                var newState = new Option(response.newcreated.Name, NewDynamicFieldsID, false, false);
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
<form id="add-edit-dynamicfield-form" method="post">
    <input type="hidden" name="DynamicLinkID" />
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="field-5" class="control-label">Title*</label>
                    <input type="text" name="Title" class="form-control" id="field-5" placeholder="">
                </div>
                <div class="form-group">
                    <label for="field-5" class="control-label">Link <span id="tooltip_Link" data-content="lang={LANGUAGE},  AccountID={ACCOUNTID}, AccountNo={ACCOUNTNUMBER}, CompanyID={COMPANYID}, username={billingemail1}, Password={CustomerPanelPassword}" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span> *</label>
                    <input type="text" name="Link" class="form-control" id="field-5" placeholder="">
                </div>
                <div class="form-group">
                    <label for="field-5" class="control-label">Currency</label>
                    {{Form::select('CurrencyID',$Currency,'',array("class"=>"form-control select2 small"))}}
                </div>

            </div>


        </div>
    </div>

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
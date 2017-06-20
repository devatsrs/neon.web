<?php
 if(empty($PageRefresh)){
     $PageRefresh='';
 }
?>
<script>
    $(document).ready(function ($) {
        $('#add-new-currency-form').submit(function(e){
            e.preventDefault();
            var PageRefresh = '{{$PageRefresh}}';
            var CurrencyID = $("#add-new-currency-form [name='CurrencyID']").val();
            if( typeof CurrencyID != 'undefined' && CurrencyID != ''){
                update_new_url = baseurl + '/currency/update/'+CurrencyID;
            }else{
                update_new_url = baseurl + '/currency/create';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-new-currency-form')[0])), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    $('#add-new-modal-currency').modal('hide');
                    if( typeof PageRefresh != 'undefined' && PageRefresh != ''){
                        data_table.fnFilter('', 0);
                    }
                    toastr.success(response.message, "Success", toastr_opts);
                    $('select[data-type="currency"]').each(function(key,el){
                        if($(el).attr('data-active') == 1) {
                            var newState = new Option(response.newcreated.Code, response.newcreated.CurrencyId, true, true);
                        }else{
                            var newState = new Option(response.newcreated.Code, response.newcreated.CurrencyId, false, false);
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
    <div class="modal fade" id="add-new-modal-currency">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-currency-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Currency</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Currency Code</label>
                                    <input type="text"  maxlength="3"  name="Code" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Currency Symbol</label>
                                    <input type="text" name="Symbol" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Description</label>
                                    <input type="text" name="Description" class="form-control" id="field-5" placeholder="">
                                    <input type="hidden" name="CurrencyID" >
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
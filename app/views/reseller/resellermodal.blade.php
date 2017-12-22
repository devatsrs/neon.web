<script>
    $(document).ready(function ($) {

        $('#add-new-reseller-form').submit(function(e){
            e.preventDefault();
            var ResellerUpdate = 0;
            var ResellerID = $("#add-new-reseller-form [name='ResellerID']").val()
            if( typeof ResellerID != 'undefined' && ResellerID != ''){
                update_new_url = baseurl + '/reseller/update/'+ResellerID;
                ResellerUpdate = 1;
            }else{
                update_new_url = baseurl + '/reseller/store';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-new-reseller-form')[0])), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    $('#add-new-modal-reseller').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);										
					var ResellerRefresh = $("#ResellerRefresh").val()
					if( typeof ResellerRefresh != 'undefined' && ResellerRefresh == '1'){
                        data_table.fnFilter(1,0);
                        /*
						if ($('#Status').is(":checked")) {
                            data_table.fnFilter(1,0);  // 1st value 2nd column index
                        }else{
                            data_table.fnFilter(0,0);
                        }*/
					}else{
						 $('select[data-type="reseller"]').each(function(key,el){
                        if($(el).attr('data-active') == 1) {
                            var newState = new Option(response.newcreated.ResellerName, response.newcreated.ResellerID, true, true);
                        }else{
                            var newState = new Option(response.newcreated.ResellerName, response.newcreated.ResellerID, false, false);
                        }
                        $(el).append(newState).trigger('change');
                        $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                            return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                        }));
                    });	
					}
                    
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        })
    });
</script>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-reseller">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-reseller-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Reseller</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Name</label>
                                    <input type="text" name="ResellerName" class="form-control" id="field-5" placeholder="">
									<input type="hidden" name="ResellerID" >
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Account Name</label>
                                    {{ Form::select('AccountID', Account::getAccountList(), '', array("class"=>"select2","data-allow-clear"=>"true")) }}
                                    <input id="Status" type="hidden" value="1">
                                </div>
                            </div>
                            <!--
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Active</label>
                                    <div class="make-switch switch-small">
										<input type="checkbox" name="Status" checked="" value="1">
									</div>
                                </div>
                            </div>-->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">First Name</label>
                                    <input type="text" name="FirstName" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Last Name</label>
                                    <input type="text" name="LastName" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Email</label>
                                    <input type="text" name="Email" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Password</label>
                                    <input type="password" name="Password"  class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Confirm Password</label>
                                    <input type="password" name="Password_confirmation" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="Reseller-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
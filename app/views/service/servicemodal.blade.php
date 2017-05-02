
<script>
    $(document).ready(function ($) {
        $('#add-new-service-form').submit(function(e){
            e.preventDefault();
            var ServiceID = $("#add-new-service-form [name='ServiceID']").val()
            if( typeof ServiceID != 'undefined' && ServiceID != ''){
                update_new_url = baseurl + '/services/update/'+ServiceID;
            }else{
                update_new_url = baseurl + '/services/store';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-new-service-form')[0])), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    $('#add-new-modal-service').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);										
					var ServiceRefresh = $("#ServiceRefresh").val()
					if( typeof ServiceRefresh != 'undefined' && ServiceRefresh == '1'){
						if ($('#ServiceStatus').is(":checked")) {
                            data_table.fnFilter(1,0);  // 1st value 2nd column index
                        }else{
                            data_table.fnFilter(0,0);
                        }
					}else{
						 $('select[data-type="service"]').each(function(key,el){
                        if($(el).attr('data-active') == 1) {
                            var newState = new Option(response.newcreated.ServiceName, response.newcreated.ServiceID, true, true);
                        }else{
                            var newState = new Option(response.newcreated.ServiceName, response.newcreated.ServiceID, false, false);
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
    <div class="modal fade" id="add-new-modal-service">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-service-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Service</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Name</label>
                                    <input type="text" name="ServiceName" class="form-control" id="field-5" placeholder="">
									<input type="hidden" name="ServiceID" >
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Type</label>
									{{ Form::select('ServiceType',Service::$ServiceType,'', array("class"=>"select2")) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Gateway</label>
									{{ Form::select('CompanyGatewayID',CompanyGateway::getCompanyGatewayIdList(),'', array("class"=>"select2")) }}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Active</label>
                                    <div class="make-switch switch-small">
										<input type="checkbox" name="Status" checked="" value="1">
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="Service-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
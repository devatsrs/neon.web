<script src="<?php echo URL::to('/'); ?>/assets/js/jquery.multi-select.js"></script>
<script>
    $(document).ready(function ($) {

        $('#add-new-reseller-form').submit(function(e){
            e.preventDefault();
            var ResellerUpdate = 0;
            var ResellerID = $("#add-new-reseller-form [name='ResellerID']").val();
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
					var ResellerRefresh = $("#ResellerRefresh").val();
					if( typeof ResellerRefresh != 'undefined' && ResellerRefresh == '1'){
                        data_table.fnFilter('', 0);
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
        });
        $('#selected-reseller-copy-form').submit(function(e) {
            e.preventDefault();
            update_new_url = baseurl + '/reseller/bulkcopydata';
            showAjaxScript(update_new_url, new FormData(($('#selected-reseller-copy-form')[0])), function(response){
                $('#selected-reseller-copy-update').button('reset');
                if (response.status == 'success') {
                    $('#selected-reseller-copy').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        });
    });
</script>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-reseller">
        <div class="modal-dialog" style="width: 65%;">
            <div class="modal-content">
                <form id="add-new-reseller-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Reseller</h4>
                    </div>
                    <div class="modal-body">
                        <div class="panel panel-primary" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Details
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6  margin-top">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-4 control-label">Reseller Account:</label>
                                            <div class="col-sm-8">
                                                {{ Form::select('AccountID', Account::getAccountList(['IsReseller'=>'1']), '', array("class"=>"select2","data-allow-clear"=>"true")) }}
                                                <input type="hidden" name="ResellerID" >
                                                <input type="hidden" name="UpdateAccountID" >
                                                <input id="Status" type="hidden" value="1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 margin-top">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-4 control-label">User Name:</label>
                                            <div class="col-sm-8">
                                                <input type="text" name="Email" class="form-control" id="field-5" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="col-md-6 margin-top">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-4 control-label">Password:</label>
                                            <div class="col-sm-8">
                                                <input type="password" name="Password"  class="form-control" id="field-5" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6  margin-top">
                                        <div class="form-group">
                                            <label for="field-1"  class="col-sm-4 control-label">Allow White Label:
                                                <span data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="If you allow your re seller to white label the panel then please make sure you setup different domain for your reseller." data-original-title="Allow white label" class="label label-info popover-primary">?</span>
                                            </label>
                                            <div class="col-md-8">
                                                <div class="make-switch switch-small">
                                                    <input type="checkbox" name="AllowWhiteLabel"  @if(Input::old('AllowWhiteLabel') == 1 )checked=""@endif value="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="col-md-6 margin-top">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-4 control-label">Panel Url:
                                                <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Panel Url will be url + /reseller/login" data-original-title="Panel Url" class="label label-info popover-primary">?</span>
                                            </label>
                                            <div class="col-sm-8">
                                                <input type="text" name="DomainUrl"  class="form-control" id="field-5" placeholder="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>

                            </div>
                        </div>

                        <div class="panel panel-primary" data-collapsed="0" id="copy_data">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Copy Data
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6  margin-top">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-4 control-label">Items</label>
                                            <div class="col-sm-12">
                                                {{ Form::select('reseller-item[]', $Products, '', array("class"=>"multi-select","multiple"=>"multiple")) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 margin-top">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-4 control-label">Subscriptions</label>
                                            <div class="col-sm-12">
                                                {{ Form::select('reseller-subscription[]', $BillingSubscription, '', array("class"=>"multi-select","multiple"=>"multiple")) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
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
    <!-- reseller copy data form -->
    <div class="modal fade" id="selected-reseller-copy">
        <div class="modal-dialog" style="width: 65%;">
            <div class="modal-content">
                <form id="selected-reseller-copy-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Reseller Data</h4>
                    </div>
                    <div class="modal-body">
                        <div class="panel panel-primary" data-collapsed="0" id="copy_data">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Copy Data
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6  margin-top">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-4 control-label">Items</label>
                                            <div class="col-sm-12">
                                                {{ Form::select('reseller-item[]', $Products, '', array("class"=>"multi-select","multiple"=>"multiple")) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 margin-top">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-4 control-label">Subscriptions</label>
                                            <div class="col-sm-12">
                                                {{ Form::select('reseller-subscription[]', $BillingSubscription, '', array("class"=>"multi-select","multiple"=>"multiple")) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="selected-reseller-copy-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            <input type="hidden" name="ResellerIDs" >
                            <input type="hidden" name="criteria" >
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
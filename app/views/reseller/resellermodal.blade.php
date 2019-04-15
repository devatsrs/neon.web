<script src="<?php echo URL::to('/'); ?>/assets/js/jquery.multi-select.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo URL::to('/'); ?>/assets/css/invoicetemplate/invoicestyle.css" />
<script>
    $(document).ready(function ($) {
        var modal1 = $('#txt-footer');
        var modal2 = $('#txt-terms');
        show_summerinvoicetemplate(modal1.find(".invoiceFooterTerm"));
        show_summerinvoicetemplate(modal2.find(".TermsAndCondition"));

        
        
        $("select[name=InvoiceToInfo]").change( function (e) {
            var str = $('.invoice-to').val();
            str += $(this).val();
            $('.invoice-to').val(str);
        });

        $.fn.editable.defaults.mode = 'inline';
        $.fn.editable.defaults.ajaxOptions = {type: "PUT"};
        $.fn.editable.defaults.showbuttons = false;
        $.fn.editableform.template = '<form class="form-inline editableform" enctype="multipart/form-data">'+
        '<div class="control-group">' +
        '<div><div class="editable-input"></div><div class="editable-buttons"></div></div>'+
        '<div class="editable-error-block"></div>' +
        '</div>' +
        '</form>';

        $('#InvoiceTemplateName').editable();
        $('#InvoiceStartNumber').editable();
        $('#InvoiceTemplateFooter').editable();
        $('#InvoiceTemplateTerms').editable();

        

        

       
            
            
        $('#add-new-reseller-form').submit(function(e){
            e.preventDefault();
            var val = $('#AccountIDs').val();
            $('#Account').val(val);
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
<style>
	    .invoice-editable:focus {
	        background: #FFFEBD;
	    }
	    #invoice_template-save:focus{
            background: #0058FA;
	    }
	    .editable-container.editable-inline{
	        width: 100%;
	    }


	    .invoice-right .editable-inline .control-group.form-group{width: 100%;}
	    .invoice-left .editable-inline .control-group.form-group{width: 90%;}

        .invoice-right  .editable-container .form-control ,.invoice-right .editable-input{width: 90%;}
	    .invoice-left  .editable-container .form-control ,.invoice-left .editable-input{width: 100%;}

        .invoice-footer .editable-inline .control-group.form-group{width: 90%;}
        .invoice-footer .editable-container .form-control ,.invoice-footer .editable-input{width: 100%;}

	</style>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-reseller">
        <div class="modal-dialog" style="width: 65%;">
            <div class="modal-content">
                <form id="add-new-reseller-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Partner</h4>
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
                                            <label for="field-1" class="col-sm-4 control-label">Partner Account:</label>
                                            <div class="col-sm-8">
                                                {{ Form::select('AccountIDs', Account::getAccountList(['IsReseller'=>'1']), '', array("class"=>"select2","id" => "AccountIDs","disabled","data-allow-clear"=>"true")) }}
                                                <input type="hidden" name="ResellerID" >
                                                <input type="hidden" name="UpdateAccountID">
                                                <input type="hidden" name="AccountID" id="Account">
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
                                    <div class="panel-body">
                                            <div class="form-group">
                                                <label for="field-1" class="col-sm-2 control-label">Logo</label>
                                                <div class="col-sm-10">
                                                    <div class="col-sm-6">
                                                        <input id="picture" type="file" name="CompanyLogo" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <br> 
                                            <div class="form-group">
                                                <div>
                                                    <label for="" class="col-sm-2">Invoice From</label>
                                                    <div class="col-sm-10">
                                                        <textarea id="InvoiceTemplateHeader" name="InvoiceFrom" class="form-control" style="min-width: 100%;" rows="7"></textarea>     
                                                    </div>                              
                                                </div>
                                            </div>
                                            <br>
                                            <div class="form-group" style="margin-top:120px;">
                                                <div>
                                                    <label for="" class="col-sm-2">Invoice To</label>
                                                    <div class="col-sm-10">
                                                        <div style="padding-bottom:8px; width:50%;">{{ Form::select('InvoiceToInfo', Invoice::$invoice_account_info, (!empty(Input::get('InvoiceToInfo'))?explode(',',Input::get('InvoiceFromInfo')):[]), array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account Info")) }}</div>
                                                        <textarea name="invoiceTo" class="form-control invoice-to" style="min-width: 100%;" rows="7"></textarea>
                                                    </div>
                                                </div>
                                            </div>  
                                            <br>
                                            <br>
                                            <br>
                                            <br>
                                            <div class="form-Group" id="txt-footer" style="margin-top: 106px;" >
                                                <br>
                                                <label for="" class="col-sm-2">Footer</label>
                                                <div class="col-sm-10">
                                                    <textarea class="form-control invoiceFooterTerm" rows="8" id="field-3" name="FooterTerm"></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group" id="txt-terms">
                                                <div>
                                                    <label for="" class="col-sm-2">Terms And Conditions</label>
                                                    <div class="col-sm-10">
                                                        <textarea name="TermsAndCondition" class="form-control TermsAndCondition" style="min-width: 100%;" rows="7"></textarea> 
                                                    </div>                                                                                    
                                                </div>
                                            </div>
                                    <div class="clear"></div>
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
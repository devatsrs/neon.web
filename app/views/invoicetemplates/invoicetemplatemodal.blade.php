<script>
    $(document).ready(function(){
        $('#add-new-invoice_template-form').submit(function(e){
            e.preventDefault();
            var InvoiceTemplateID = $("#add-new-invoice_template-form [name='InvoiceTemplateID']").val()
            if( typeof InvoiceTemplateID != 'undefined' && InvoiceTemplateID != ''){
                update_new_url = baseurl + '/invoice_template/'+InvoiceTemplateID+'/update';
            }else{
                update_new_url = baseurl + '/invoice_template/create';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-new-invoice_template-form')[0])), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    $('#add-new-modal-invoice_template').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    $('select[data-type="invoice_template"]').each(function(key,el){
                        if($(el).attr('data-active') == 1) {
                            var newState = new Option(response.newcreated.Name, response.newcreated.InvoiceTemplateID, true, true);
                        }else{
                            var newState = new Option(response.newcreated.Name, response.newcreated.InvoiceTemplateID, false, false);
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
            return false;
        });
    });

    function ajax_update(fullurl,data){
//alert(data)
        $.ajax({
            url:fullurl, //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#invoice_template-update").button('reset');
                if (response.status == 'success') {
                    $('#add-new-modal-invoice_template').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    if( typeof data_table !=  'undefined'){
                        data_table.fnFilter('', 0);
                    }
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
            },
            data: data,
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false,
            contentType: false,
            processData: false
        });
    }

</script>

@section('footer_ext')
    @parent
    <div class="modal fade custom-width" id="add-new-modal-invoice_template">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="add-new-invoice_template-form" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Invoice Template</h4>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Template Name</label>
                            <div class="col-sm-4">
                                <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                            </div>
                            <div id="InvoiceStartNumberToggle">
                                <label for="field-1" class="col-sm-2 control-label">Invoice Start Number</label>
                                <div class="col-sm-4">
                                    <input type="text" name="InvoiceStartNumber" class="form-control" id="field-1" placeholder="" value="" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Invoice Prefix</label>
                            <div class="col-sm-4">
                                <input type="text" name="InvoiceNumberPrefix" class="form-control" id="field-5" placeholder="">
                            </div>
                            <div class="LastInvoiceNumber">
                                <label for="field-1" class="col-sm-2 control-label">Last Invoice Number</label>
                                <div class="col-sm-4">
                                    <input type="text" name="LastInvoiceNumber" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Estimate Prefix</label>
                            <div class="col-sm-4">
                                <input type="text" name="EstimateNumberPrefix" class="form-control" id="field-5" placeholder="">
                            </div>
                            <div id="EstimateStartNumberToggle">
                                <label for="field-1" class="col-sm-2 control-label">Estimate Start Number</label>
                                <div class="col-sm-4">
                                    <input type="text" name="EstimateStartNumber" class="form-control" id="field-1" placeholder="" value="" />
                                </div>
                            </div>
                            <div class="LastEstimateNumber">
                                <label for="field-1" class="col-sm-2 control-label">Last Estimate Number</label>
                                <div class="col-sm-4">
                                    <input type="text" name="LastEstimateNumber" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">CreditNotes Prefix</label>
                            <div class="col-sm-4">
                                <input type="text" name="CreditNotesNumberPrefix" class="form-control" id="field-5" placeholder="">
                            </div>
                            <div id="CreditNotesStartNumberToggle">
                                <label for="field-1" class="col-sm-2 control-label">CreditNotes Start Number</label>
                                <div class="col-sm-4">
                                    <input type="text" name="CreditNotesStartNumber" class="form-control" id="field-1" placeholder="" value="" />
                                </div>
                            </div>
                            <div class="LastCreditNotesNumber">
                                <label for="field-1" class="col-sm-2 control-label">Last CreditNotes Number</label>
                                <div class="col-sm-4">
                                    <input type="text" name="LastCreditNotesNumber" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Pages</label>
                            <div class="col-sm-7">
                                <?php  $invoice_page_array =  array(''=>'Select Invoice Pages','single'=>'A single page with totals only','single_with_detail'=>'First page with totals + usage details attached on additional pages')?>
                                {{Form::select('InvoicePages',$invoice_page_array,'',array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Logo</label>
                            <div class="col-sm-10">
                                <div class="col-sm-6">
                                    <input id="picture" type="file" name="CompanyLogo" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                                </div>
                                <div class="col-sm-6">
                                    <img name="CompanyLogoUrl" src="http://placehold.it/250x100" width="100"> (Only Upload .jpg file)
                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Show Zero Call</label>
                            <div class="col-sm-4">
                                <p class="make-switch switch-small">
                                    <input type="checkbox" checked=""  name="ShowZeroCall" value="0">
                                </p>
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Show Previous Balance</label>
                            <div class="col-sm-4">
                                <p class="make-switch switch-small">
                                    <input type="checkbox"    name="ShowPrevBal" value="0">
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Date Format</label>
                            <div class="col-sm-4">
                                {{Form::select('DateFormat',InvoiceTemplate::$invoice_date_format,'',array("class"=>"select2 small"))}}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Show Billing Period</label>
                            <div class="col-sm-4">
                                <p class="make-switch switch-small">
                                    <input type="checkbox"    name="ShowBillingPeriod" value="0">
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="InvoiceTemplateID" value="" />
                        <button type="submit" id="invoice_template-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
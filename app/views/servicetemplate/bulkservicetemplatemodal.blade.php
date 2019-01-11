
<script>

  $(document).ready(function ($) {
      countSelectedItems = 1;

      var selected_company, data, url;
      selected_currency = $("#serviceTemplateCurreny").val();

      data = {company: selected_company};
      resetFormFields();

      // This function exist in service template moal class
      loadValuesBasedOnCurrency(selected_currency,false,'','','','');

      var table = document.getElementById ("table-4");
      $("#add-new-BulkAction-modal-service input:checkbox").prop("checked",false);
      $("#add-new-BulkAction-modal-service select").prop("disabled",true);


      $("#add-new-BulkAction-modal-service input:checkbox[name='Service']").change(function() {

          if ($(this).prop('checked') == false)
          {
              $("#ServiceId").prop('disabled', 'disabled');
              countSelectedItems++;
          }else{
              $("#ServiceId").prop('disabled', false);
              countSelectedItems--;
          }


      });


      $("#add-new-BulkAction-modal-service input:checkbox[name='OutboundTraiff']").change(function() {

          if ($(this).prop('checked') == false)
          {
              $("#OutboundRateTableId").prop('disabled', 'disabled');
              countSelectedItems++;
          }else {
              $("#OutboundRateTableId").prop('disabled', false);
              countSelectedItems--;
          }
      });


      $("#add-new-BulkAction-modal-service input:checkbox[name='OutboundDiscountPlan']").change(function() {

          if ($(this).prop('checked') == false)
          {
              $("#OutboundDiscountPlanId").prop('disabled', 'disabled');
              countSelectedItems++;
          }else {
              $("#OutboundDiscountPlanId").prop('disabled', false);
              countSelectedItems--;
          }
      });

      $("#add-new-BulkAction-modal-service input:checkbox[name='InboundDiscountPlan']").change(function() {
          if ($(this).prop('checked') == false)
          {
              $("#InboundDiscountPlanId").prop('disabled', 'disabled');
              countSelectedItems++;
          }else {
              $("#InboundDiscountPlanId").prop('disabled', false);
              countSelectedItems--;
          }
      });

      $('#add-action-bulk-form').submit(function(e){

          console.log(countSelectedItems);

          if(countSelectedItems == 1)
          {
              alert("Please select service tempalte");
              $('#add-new-BulkAction-modal-service').modal('hide');
              return false;
          }

          update_new_url = baseurl + '/servicesTemplate/addBulkAction';
          var data = new FormData(($('#add-action-bulk-form')[0]));

          showAjaxScript(update_new_url, data, function(response){
              $(".btn").button('reset');
              if (response.status == 'success') {

                  $('#add-new-BulkAction-modal-service').modal('hide');
                  toastr.success(response.message, "Success", toastr_opts);
                  var dataTableName = $("#table-4").dataTable();
                  dataTableName.fnDraw();
                  $("#add-new-BulkAction-modal-service [name='CurrencyId']").attr('checked',false);


              }else{
                  toastr.error(response.message, "Error", toastr_opts);
              }
          });
            return false;
      });


  });


</script>



    <div class="modal fade" id="add-new-BulkAction-modal-service">

    <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-action-bulk-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h5 class="modal-title" id="BulkServiceTemplateModelTitle">Add New Bulk Action</h5>
                    </div>
                    <div class="modal-body">

                        <div id="TemplateDataTab">

                            <div id="ContentTemplateDataTab" class="modal-body">
                                <div class="row">
                                    <input type="hidden" name="CurrencyId" id="CurrencyId" val="" />
                                    <input type="hidden" name="ServiceTemplateId" id="ServiceTemplateId" val="" />

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <table width="100%">
                                                <tr>
                                                    <td width="15%"><label for="field-5" class="control-label"><input type="checkbox" name="Service" value=""> Service</label></td>
                                                    <td width="30%"><select  id="ServiceId" name="ServiceId" class="form-control"></select></td>
                                                    <td width="5%">&nbsp;</td>
                                                    <td width="15%"><label for="field-5" class="control-label"><input type="checkbox" name="OutboundTraiff" value=""> Outbound Traiff</label></td>
                                                    <td width="35%">
                                                        <select id="OutboundRateTableId" name="OutboundRateTableId" class="form-control">
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <table width="100%">

                                                <tr>
                                                    <td width="15%"><label for="field-5" class="control-label"><input type="checkbox" name="OutboundDiscountPlan" value=""> Outbound Discount Plan</label></td>
                                                    <td width="30"><select id="OutboundDiscountPlanId" name="OutboundDiscountPlanId" class="form-control"></select></td>
                                                    <td width="5%">&nbsp;</td>
                                                    <td width="15%"><label for="field-5" class="control-label"><input type="checkbox" name="InboundDiscountPlan" value=""> Inbound Discount Plan</label></td>
                                                    <td width="35%">
                                                        <select id="InboundDiscountPlanId" name="InboundDiscountPlanId" class="form-control">
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>


                                        </div>
                                    </div>

                                    {{--<div id="ajax_dynamicfield_html" class="margin-top"></div>--}}
                                </div>
                            </div>
                        </div>

                        <div>


                            <br/>
                        </div>





                        <div class="modal-footer" style="vertical-align: top">
                            <button type="submit" id="add-bulkAction"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                <i class="entypo-floppy"></i>
                                Save
                            </button>
                            <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                                <i class="entypo-cancel"></i>
                                Close
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

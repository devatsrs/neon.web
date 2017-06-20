<script>
    jQuery(document).ready(function ($) {

        $(document).on('click','.manual_billing',function(e){
            e.preventDefault();
			var update_new_url 	= 	baseurl + '/accounts_manual_bill';
            $('#manual-billing-modal').modal('show');
        });
        $('#add-manualbilling-form .billing_account').change(function(e){
            e.preventDefault();
            if($(this).val() > 0) {
                var update_new_url = baseurl + '/get_unbill_report/' + $(this).val();
                $('#unbilling_html').html('<div class="col-md-12">Loading...</div>');
                $.ajax({
                    url: update_new_url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#unbilling_html').html(response);
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }else{
                $('#unbilling_html').html('');
            }
        });

        $('#add-manualbilling-form [name="BillingCheck"]').change(function(e){
            var checked = $(this).is(':checked');
            if(checked){
                $('.all_accounts').hide();
                $('.manual_accounts').show();
            }else{
                $('.all_accounts').show();
                $('.manual_accounts').hide();
            }

        });
        $('#add-manualbilling-form [name="AccountID"]').val('').trigger('change');
        $('#add-manualbilling-form [name="BillingCheck"]').val('').trigger('change');

        $('#add-manualbilling-form').submit(function(e){
            e.preventDefault();
            submit_ajax(baseurl + '/generate_manual_invoice',$(this).serialize())
        });
    });
</script>

@section('footer_ext')@parent
<div class="modal fade" id="manual-billing-modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="add-manualbilling-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title"><strong>Manual Billing</strong></h4>
        </div>
        <div class="modal-body">
            <div class="row" id="error_html">

            </div>
          <div class="row" id="manual_billing_html">
              <div class="col-md-4">
                  <div class="form-group">
                      <?php
                      $manual_data['Status'] =1;
                      $manual_data['AccountType'] =1;
                      $manual_data['VerificationStatus'] =Account::VERIFIED;
                      $manual_data['CompanyID'] =User::get_companyID();
                      $manual_data['BillingCycleType'] = 'manual';
                      $manual_accounts = Account::join('tblAccountBilling','tblAccountBilling.AccountID','=','tblAccount.AccountID')
                              ->where($manual_data)->select(array('AccountName', 'tblAccount.AccountID'))->orderBy('AccountName')->lists('AccountName', 'AccountID');
                      if(!empty($manual_accounts)){
                          $manual_accounts = array(""=> "Select")+$manual_accounts;
                      }
                      ?>
                          <div class="checkbox" style="padding-left: 0px;">
                              <label class="control-label "> <input name="BillingCheck" type="checkbox">Manual Billing
                                  <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Display only account which have manual billing." data-original-title="Manual Billing">?</span>
                                   Accounts
                              </label>
                          </div>

                      {{Form::select('AccountID',$accounts,'',array( "class"=>"select2 all_accounts billing_account", "data-allow-clear"=>"true","data-placeholder"=>"Select Account"))}}
                      {{Form::select('AccountID',$manual_accounts,'',array( "class"=>"select2 manual_accounts billing_account", "data-allow-clear"=>"true","data-placeholder"=>"Select Account"))}}
                  </div>
              </div>
              <div id="unbilling_html">

              </div>
          </div>

        </div>
        <div class="modal-footer">
            <button  type="submit" class="btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Generate New Invoice </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop
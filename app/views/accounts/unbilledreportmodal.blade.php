<style>
#report_filter{
    width: 100%;
    margin: auto;
}
</style>
<?Php
if(isset($account))
    $data_id = $account->AccountID;
else{
    $data_id = '';
}    
?>
<script>
    jQuery(document).ready(function ($) {
        $(document).on('click','.unbilled_report',function(e){
            e.preventDefault();
            $('#unbilled_report').button('loading');
			var update_new_url 	= 	baseurl + '/accounts/unbilledreport/'+$(this).attr('data-id');
            if($('#unbilled_report').length == 0){
                $('#unbilledreport-modal').modal('show');
                $('#unbilled_report_day').html('<div class="col-md-12">Loading...</div>');
            }
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'html',
                success: function (response) {
                    $('#unbilled_report').button('reset');
                    $('#unbilledreport-modal').modal('show');
                    $('#unbilled_report_day').html(response);
                },
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });
        $("#reports-search").click(function (e) {
            e.preventDefault();

            startDate = $('#DateFrom').val();
            endDate = $('#DateTo').val();
            if(startDate == '' && endDate == ''){
                toastr.error("Date from and date to field required", "Error", toastr_opts);
                return false;
            }
            if(startDate != '' || endDate != ''){
                if(startDate == ''){
                    toastr.error("Date from field required", "Error", toastr_opts);
                    return false;
                }
                if(endDate == ''){
                    toastr.error("Date to field required", "Error", toastr_opts);
                    return false;
                }
                if(startDate >= endDate){
                    toastr.error("Date to field always greater than date from field", "Error", toastr_opts);
                    return false;
                }
                

            }
            var searchreport = {};

            searchreport.DateFrom = $("#report_filter").find('[name="DateFrom"]').val();
            searchreport.DateTo = $("#report_filter").find('[name="DateTo"]').val();
            searchreport.Type = $("#report_filter").find('[name="Type"]').val();
            searchreport.Description = $("#report_filter").find('[name="Description"]').val();
            var update_new_url 	= 	baseurl + '/accounts/prepaidunbilledreport/'+$(this).attr('data-id');
            $('#prepaidunbilled_report_day').html('<div class="col-md-12">Loading...</div>');
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'html',
                data: {searchreport},
                success: function (response) {
                    $('#prepaid_billed_report').button('reset');
                    $('#prepaidunbilledreport-modal').modal('show');
                    $('#prepaidunbilled_report_day').html(response);
                },
                //Options to tell jQuery not to process data or worry about content-type.
               
            });
           
        });
        $(document).on('click','.prepaid_billed_report',function(e){
            e.preventDefault();
            var d = new Date();
            var firstDay = new Date(d.getFullYear(), d.getMonth(),d.getDate() - 31);
            var lastDay = new Date(d.getFullYear(), d.getMonth() , d.getDate())
            
            $("#DateFrom").datepicker("setDate", firstDay);
            $("#DateTo").datepicker("setDate", lastDay);
            var searchreport = {};

            searchreport.DateFrom = $("#report_filter").find('[name="DateFrom"]').val();
            searchreport.DateTo = $("#report_filter").find('[name="DateTo"]').val();
            searchreport.Type = $("#report_filter").find('[name="Type"]').val();
            searchreport.Description = $("#report_filter").find('[name="Description"]').val();
             $("#report_filter").find('[name="Description"]').val('');
            $('#prepaid_billed_report').button('loading');
            var update_new_url 	= 	baseurl + '/accounts/prepaidunbilledreport/'+$(this).attr('data-id');
            if($('#prepaid_billed_report').length == 0){
                $('#prepaidunbilledreport-modal').modal('show');
                $('#prepaidunbilled_report_day').html('<div class="col-md-12">Loading...</div>');
            }
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'html',
                data: {searchreport},
                success: function (response) {
                    $('#prepaid_billed_report').button('reset');
                    $('#prepaidunbilledreport-modal').modal('show');
                    $('#prepaidunbilled_report_day').html(response);
                },
            });
        });
    });
</script>

@section('footer_ext')@parent
<div class="modal fade" id="unbilledreport-modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="add-unbilledreport-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title"><strong>Unbilled Report By Day</strong></h4>
        </div>
        <div class="modal-body">
          <div class="row" id="unbilled_report_day">

          </div>
        </div>
        <div class="modal-footer">
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="prepaidunbilledreport-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="add-prepaidunbilledreport-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><strong>Account Balance Report</strong></h4>
                </div>
                <div class="modal-body">
                    <div id="report_filter" method="get" action="#">
                        <table width="100%">
                            <tr>
                                <td width="11%"><label for="field-1" class="col-sm-12 control-label">Date From</label></td>
                                <td width="14%"><input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="DateFrom" name="DateFrom"></td>
                                <td width="10%"><label for="field-1" class="col-sm-12 control-label">Date To</label></td>
                                <td width="15%"><input type="text" data-date-format="yyyy-mm-dd"  class="form-control datepicker" id="DateTo" name="DateTo"></td>
                                <td width="5%"><label for="field-1" class="col-sm-12 control-label">Type</label></td>
                                <td width="20%">{{ Form::select('Type', [""=>"All","Oneofcharge" => "One Of Charge","PRS Earnings"=>"PRS Earnings", "Out Payment"=>"Out Payment","Subscription" => "Subscription","TopUp"=>"Top Up","Usage"=>"Usage"], '', array("class"=>"form-control select2 small")) }}</td>
                                <td width="5%"><label for="field-1" class="col-sm-12 control-label">Description</label></td>
                                <td width="20%"><input type="text" class="form-control" name="Description"></td>
                            </tr>
                            <tr>
                                <td colspan="10" align="right" style="padding-top: 10px">
                                    <button class="btn btn-primary btn-sm btn-icon icon-left" data-id="{{ $data_id}}" id="reports-search">
                                        <i class="entypo-search"></i>
                                        Search
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <div class="row" id="prepaidunbilled_report_day">

                    </div>
                </div>
                <div class="modal-footer">
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
<script>
    jQuery(document).ready(function ($) {
        var alert_inbound_first = false;
        var alert_outbound_first = false;
        $('#minutes_report').click(function(e){
            e.preventDefault();
            $('#minutes_report').button('loading');
            getreport("{{AccountDiscountPlan::OUTBOUND}}")
        });
        $('#inbound_minutes_report').click(function(e){
            e.preventDefault();
            $('#inbound_minutes_report').button('loading');
            getreport("{{AccountDiscountPlan::INBOUND}}")
        });
        $('select[name="DiscountPlanID"]').on( "change",function(e){
            if(alert_inbound_first == true) {
                alert('Are you sure? Current used minutes will be refreshed.');
            }else if($(this).val()){
                alert_inbound_first = true;
            }
            if($(this).val()){
                $('#minutes_report').removeClass('hidden')
            }else{
                $('#minutes_report').addClass('hidden')
            }
        });
        $('select[name="DiscountPlanID"]').trigger( "change" );
        $('select[name="InboundDiscountPlanID"]').on( "change",function(e){
            if(alert_outbound_first == true) {
                alert('Are you sure? Current used minutes will be refreshed.');
            }else if($(this).val()){
                alert_outbound_first = true;
            }
            if($(this).val()){
                $('#inbound_minutes_report').removeClass('hidden')
            }else{
                $('#inbound_minutes_report').addClass('hidden')
            }
        });
        $('select[name="InboundDiscountPlanID"]').trigger( "change" );
    });
    function getreport(Type){
        var update_new_url 	= 	baseurl + '/account/used_discount_plan/'+'{{$account->AccountID}}';
        var ServiceID = '{{$ServiceID}}';
        $.ajax({
            url: update_new_url,  //Server script to process data
            type: 'POST',
            data:'Type='+Type+'&ServiceID='+ServiceID,
            dataType: 'html',
            success: function (response) {
                $('#minutes_report').button('reset');
                $('#inbound_minutes_report').button('reset');
                $('#minutes_report-modal').modal('show');
                $('#used_minutes_report').html(response);
            }
        });
    }
</script>

@section('footer_ext')@parent
<div class="modal fade" id="minutes_report-modal">
  <div class="modal-dialog" style="width: 70%;">
    <div class="modal-content">
      <form id="add-minutes_report-form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title"><strong> Discount Plan Detail</strong></h4>
        </div>
        <div class="modal-body">
          <div class="row" id="used_minutes_report">

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
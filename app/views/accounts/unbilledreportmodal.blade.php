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

        $(document).on('click','.prepaid_billed_report',function(e){
            e.preventDefault();
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
                success: function (response) {
                    $('#prepaid_billed_report').button('reset');
                    $('#prepaidunbilledreport-modal').modal('show');
                    $('#prepaidunbilled_report_day').html(response);
                },
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
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
                    <h4 class="modal-title"><strong>Account Balance Report By Day</strong></h4>
                </div>
                <div class="modal-body">
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
@extends('layout.main')
@section('content')   
    @if(User::checkCategoryPermission('TicketDashboardTimeLineWidgets','View'))
<div class="row">
  <div class="col-md-12">
    <div data-collapsed="0" class="panel panel-primary">
      <div class="panel-heading">
        <div class="panel-title"> Recent Activities </div>
        <div class="panel-options"> <a data-rel="collapse" href="#"> <i class="entypo-down-open"></i> </a> </div>
      </div>
      <div id="activity-timeline" class="panel-body">
        <ul>
        </ul>
      </div>
    </div>
  </div>
</div>
@endif 
<script type="text/javascript">
        var scroll_more 	  =  		1;
        jQuery(document).ready(function ($) {
            last_msg_funtion();
            $(window).scroll(function(){
                if ($(window).scrollTop() >= ($('#activity-timeline li:last-child').offset().top - 400 )){

                    setTimeout(function() {
                        last_msg_funtion(0);
                    }, 1000);
                }
            });

            function last_msg_funtion(first)
            {
                if(scroll_more==0){
                    return false;
                }
                var count = 0;
                if(first==0) {
                    count = $("#activity-timeline ul li").length;
                }
                console.log(count);
                var url = baseurl + '/ticket_dashboard/timelinewidgets';

                $('div#last_msg_loader').html('<img src="'+baseurl+'/assets/images/bigLoader.gif">');

                /////////////

                $.ajax({
                    url: url+'/'+count,
                    type: 'GET',
                    dataType: 'html',
                    async :false,
                    data:{"TicketID":{{$id}}},
                    success: function(response) {
                        if (isJson(response)) {
                            var response_json  =  JSON.parse(response);
                            if(response_json.scroll=='end') {
                                scroll_more= 0;
                                if($(".timeline-end").length > 0) {
                                    return false;
                                }
                                var html_end = '<li class="timeline-end" style="text-align: center;"><i class="entypo-infinity" style="font-size: 25px;color: cadetblue;"></i> </li>';
                                $("#activity-timeline ul").append(html_end);
                                $('div#last_msg_loader').empty();
                                return true;
                            }
                            ShowToastr("error",response_json.message);
                        } else {
                            $("#activity-timeline ul").append(response);
                        }
                        $('div#last_msg_loader').empty();
                    }
                });

                //////////////

            }

        });
    </script> 
@stop
@extends('layout.main')
@section('content')
    <?php if(User::checkCategoryPermission('TicketDashboardSummaryWidgets','View')){ ?>
        <div class="row ticket">
        <div class="col-md-12">
            <div data-collapsed="0" class="panel panel-primary">
                <div id="ticket-widgets" class="panel-body">

                    <div class="col-sm-2 col-xs-4">
                        <div class="tile-stats tile-aqua">
                            <div class="icon"><i class="entypo-users"></i></div>
                            <a target="_blank" class="undefined"
                                                                  data-startdate="" data-enddate=""
                                                                  data-currency="" href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p> Unresolved</p></a></div>
                    </div>

                    <!--if((count($TicketDashboardWidgets)==0) ||  in_array('BillingDashboardTotalInvoiceSent',$TicketDashboardWidgets))
                    <div class="col-sm-2 col-xs-4">
                        <div class="tile-stats tile-white">
                            <div class="icon"><i class="entypo-calendar"></i></div>
                            <a target="_blank" class="undefined" data-startdate=""
                                                              data-enddate="" data-currency=""
                                                              href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Overdue</p></a></div>
                    </div>
                    endif
                    if((count($TicketDashboardWidgets)==0) ||  in_array('BillingDashboardTotalInvoiceReceived',$TicketDashboardWidgets))
                    <div class="col-sm-2 col-xs-4">
                        <div class="tile-stats tile-white">
                            <div class="icon"><i class="entypo-hourglass"></i></div>
                            <a target="_blank" class="undefined" data-startdate=""
                                                              data-enddate="" data-currency=""
                                                              href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Due Today</p></a></div>
                    </div>
                    endif-->

                    <div class="col-sm-2 col-xs-4">
                        <div class="tile-stats tile-cyan">
                            <div class="icon"><i class="entypo-ticket"></i></div>
                            <a target="_blank" class="undefined" data-startdate=""
                                                            data-enddate="" data-currency=""
                                                            href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Open</p></a></div>
                    </div>

                    <div class="col-sm-2 col-xs-4">
                        <div class="tile-stats tile-orange">
                            <div class="icon"><i class="entypo-clock"></i></div>
                            <a target="_blank" class="undefined" data-startdate=""
                                                             data-enddate="" data-currency=""
                                                             href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>On Hold</p></a></div>
                    </div>

                    <div class="col-sm-2 col-xs-4">
                        <div class="tile-stats tile-red">
                            <div class="icon"><i class="entypo-help"></i></div>
                            <a target="_blank" class="undefined" data-startdate=""
                                                               data-enddate="" data-currency=""
                                                               href="javascript:void(0)">
                                <div class="num" data-start="0" data-end="0" data-prefix="" data-postfix=""
                                     data-duration="1500" data-delay="1200">0
                                </div>
                                <p>Unassigned</p></a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    @if(User::checkCategoryPermission('TicketDashboardTimeLineWidgets','View'))
    <div class="row">
        <div class="col-md-12">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Recent Activities
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#">
                            <i class="entypo-down-open"></i>
                        </a>
                    </div>
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
            SummaryWidgets(1);
            last_msg_funtion();
            $(window).scroll(function(){
                if ($(window).scrollTop() >= ($('#activity-timeline li:last-child').offset().top - 500 )){

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
                    data:{},
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

            function SummaryWidgets() {
                var get_url = baseurl + "/ticket_dashboard/summarywidgets";
                $.get(get_url, [], function (response) {
                    if(response.status == 'success') {
                        var option = [];
                        var widgets = '';
                        response = response.data.pop();
                        option["amount"] = response.UnResolved;
                        option["end"] = response.UnResolved;
                        option["type"] = 'Unresolved';
                        option['sign'] = 'users';
                        option['class']     = 'tile-aqua';
                        option['url']   = '{{URL::to('tickets?status='.array_search('All UnResolved', $status))}}';
                        widgets += buildbox(option);

                        option["amount"] = response.Open;
                        option["end"] = response.Open;
                        option["type"] = 'Open';
                        option['sign'] = 'ticket';
                        option['class']     = 'tile-cyan';
                        option['url']   = '{{URL::to('tickets?status='.array_search('Open', $status))}}';
                        widgets += buildbox(option);

                        option["amount"] = response.OnHold;
                        option["end"] = response.OnHold;
                        option["type"] = 'On Hold';
                        option['sign'] = 'clock';
                        option['class']     = 'tile-orange';
                        option['url']   = '{{URL::to('tickets?status='.array_search('Pending', $status).(!empty($statusOnHold)?','.implode(',',array_keys($statusOnHold)):''))}}';
                        widgets += buildbox(option);

                        option["amount"] = response.UnAssigned;
                        option["end"] = response.UnAssigned;
                        option["type"] = 'Unassigned';
                        option['sign'] = 'help';
                        option['class']     = 'tile-red';
                        option['url']   = '{{URL::to('tickets?agent=1')}}';
                        widgets += buildbox(option);

                        $('#ticket-widgets').html(widgets);
                        $("#ticket-widgets").find('.tile-stats').each(function (i, el) {
                            titleState(el);
                        });
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                }, "json");
            }

            function buildbox(option) {
                html = '<div class="col-sm-2 col-xs-4">';
                html += ' <div class="tile-stats '+option['class']+'">';
                html += '<div class="icon"><i class="entypo-' + option['sign'] + '"></i></div>';
                html += '  <a href="' + option['url'] + '" target="_blank">';
                html += '   <div class="num" data-start="0" data-end="' + option['end'] + '" data-duration="1500" data-delay="1200">' + option['amount'] + '</div>';
                html += '    <p>' + option['type'] + '</p>';
                html += '  </a>';
                html += ' </div>';
                html += '</div>';
                return html;
            }

            function titleState(el) {

                var $this = $(el),
                        $num = $this.find('.num'),
                        start = attrDefault($num, 'start', 0),
                        end = attrDefault($num, 'end', 0),
                        prefix = attrDefault($num, 'prefix', ''),
                        postfix = attrDefault($num, 'postfix', ''),
                        duration = attrDefault($num, 'duration', 1000),
                        delay = attrDefault($num, 'delay', 1000);
                round = attrDefault($num, 'round', 0);

                var i = setInterval(function () {
                    if (start === end) {
                        clearInterval(i);
                        $num.animate({MarginRight: 0});
                    } else {
                        start++;
                        var margin = start%2==0?0:1;
                        $num.text(prefix+start).animate({MarginRight: margin}, 20);
                    }
                }, 20);
            }

        });
    </script>
@stop
<script src="{{ URL::asset('assets/js/reports.js') }}"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script>
    var $searchFilter = {};
    var toFixed = '{{get_round_decimal_places()}}';
    var table_name = '#destination_table';
    var chart_type = '#destination';
    var cdr_url = "";
    var customer_login ;
    @if(Session::get('customer') == 1)
        cdr_url = "{{URL::to('customer/cdr')}}";
        customer_login = 1;
    @else
        cdr_url = "{{URL::to('cdr_show')}}";
        customer_login = 0;
    @endif
    $searchFilter.map_url = "{{URL::to('getWorldMap')}}";
    $searchFilter.pageSize = '{{CompanyConfiguration::get('PAGE_SIZE')}}';
    jQuery(document).ready(function ($) {

        $(".refresh_tab li a").click(function(){
            table_name = $(this).attr('href')+'_table';
            chart_type = $(this).attr('href');
            $("#customer_analysis").find("input[name='chart_type']").val(chart_type.slice(1));
            setTimeout(function(){
                set_search_parameter($("#customer_analysis"));
                reloadCharts(table_name,'{{CompanyConfiguration::get('PAGE_SIZE')}}',$searchFilter);
            }, 10);
        });
        chart_type = $(".refresh_tab li.active a").attr('href');
        table_name = $(".refresh_tab li.active a").attr('href')+'_table';
        $("#customer_analysis").find("input[name='chart_type']").val(chart_type.slice(1));
        $("[name='UserID']").change(function(e) {
            if($(this).val() > 0) {
                $("#customer_analysis").find("input[name='Admin']").val(0);
            }else{
                $("#customer_analysis").find("input[name='Admin']").val($("#customer_analysis").find("input[name='Admin1']").val());
            }
        });
        $(".datepicker").change(function(e) {
            var start = new Date($("[name='StartDate']").val()),
                    end   = new Date($("[name='EndDate']").val()),
                    diff  = new Date(end - start),
                    days  = diff/1000/60/60/24;
            if(days > 31){
                $("[name='StartHour']").attr('disabled','disabled');
                $("[name='EndHour']").attr('disabled','disabled');
                $("[name='TimeZone']").val('').trigger('change');
                $(".select_hour").hide();
            }else{
                $("[name='EndHour']").removeAttr('disabled');
                $("[name='StartHour']").removeAttr('disabled');
                $(".select_hour").show();
            }
        });
        $("#customer_analysis").submit(function(e) {
            e.preventDefault();
            public_vars.$body = $("body");
            //show_loading_bar(40);
            set_search_parameter($(this));
            reloadCharts(table_name,'{{CompanyConfiguration::get('PAGE_SIZE')}}',$searchFilter);
            return false;
        });
        set_search_parameter($("#customer_analysis"));
        Highcharts.theme = {
            colors: ['#3366cc', '#ff9900' ,'#dc3912' , '#109618', '#66aa00', '#dd4477','#0099c6', '#990099', '#143DFF']
        };
        // Apply the theme
        Highcharts.setOptions(Highcharts.theme);
        Highcharts.setOptions({
            lang: {
                drillUpText: '◁ Back'
            }
        });
        reloadCharts(table_name,'{{CompanyConfiguration::get('PAGE_SIZE')}}',$searchFilter);
    });
</script>
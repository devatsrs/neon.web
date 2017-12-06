<script src="{{ URL::asset('assets/js/manager_reports.js') }}"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script>
    var $searchFilter = {};
    var toFixed = '{{get_round_decimal_places()}}';
    var table_name = '#destination_table';
    var chart_type = '#destination';
    $searchFilter.pageSize = '{{CompanyConfiguration::get('PAGE_SIZE')}}';
    jQuery(document).ready(function ($) {
        $('#filter-button-toggle').show();

        $("[name='UsersID[]']").change(function(e) {
            if($(this).val() != null) {
                $("#analysis_manager").find("input[name='Admin']").val(0);
            }else{
                $("#analysis_manager").find("input[name='Admin']").val($("#analysis_manager").find("input[name='Admin1']").val());
            }
        });
        $("[name='UsersID[]']").trigger('change');
        set_search_parameter($("#analysis_manager"));
        $("[name='RevenueDisplayType']").change(function(e) {
            var RevenueDisplayType = $("[name='RevenueDisplayType']:checked").val();
            console.log(RevenueDisplayType)
            if(RevenueDisplayType == 'Table'){
                $(".bar_chart_revenue").addClass('hidden');
                $("#AccountManagerRevenue_wrapper").removeClass('hidden');
            }else{
                $(".bar_chart_revenue").removeClass('hidden');
                $("#AccountManagerRevenue_wrapper").addClass('hidden');
            }
        });
        $("[name='MarginDisplayType']").change(function(e) {
            var MarginDisplayType = $("[name='MarginDisplayType']:checked").val();
            console.log(MarginDisplayType)
            if(MarginDisplayType == 'Table'){
                $(".bar_chart_margin").addClass('hidden');
                $("#AccountManagerMargin_wrapper").removeClass('hidden');
            }else{
                $(".bar_chart_margin").removeClass('hidden');
                $("#AccountManagerMargin_wrapper").addClass('hidden');
            }
        });
        $("[name='ActiveLead']").change(function(e) {
            $searchFilter.ActiveLead = $(this).val();
            loadLeads('#leads',10,$searchFilter);
        });
        $("[name='ActiveAccount']").change(function(e) {
            $searchFilter.ActiveAccount = $(this).val();
            loadAccounts('#accounts',10,$searchFilter);
        });
        $("[name='RevenueListType']").change(function(e) {
            $searchFilter.RevenueListType = $(this).val();
            loadAccountManagerRevenue('#AccountManagerRevenue',10,$searchFilter);
            loadRevenueChart($searchFilter);
            $("[name='RevenueDisplayType']").trigger('change');
        });
        $("[name='MarginListType']").change(function(e) {
            $searchFilter.MarginListType = $(this).val();
            loadAccountManagerMargin('#AccountManagerMargin',10,$searchFilter);
            loadMarginChart($searchFilter);
            $("[name='MarginDisplayType']").trigger('change');
        });
        $("[name='AccountListType']").change(function(e) {
            $searchFilter.AccountListType = $(this).val();
            loadAccountRevenueMargin('#AccountMargin',10,$searchFilter);
        });
        $("#analysis_manager").submit(function(e) {
            e.preventDefault();
            public_vars.$body = $("body");
            //show_loading_bar(40);
            set_search_parameter($(this));
            reloadCharts(table_name,'{{CompanyConfiguration::get('PAGE_SIZE')}}',$searchFilter);
            $("[name='RevenueDisplayType']").trigger('change');
            $("[name='MarginDisplayType']").trigger('change');
            return false;
        });
        reloadCharts(table_name,'{{CompanyConfiguration::get('PAGE_SIZE')}}',$searchFilter);
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

    });
</script>

<script src="{{ URL::asset('assets/js/reports.js') }}"></script>
<script>
    var $searchFilter = {};
    var toFixed = '{{get_round_decimal_places()}}';
    var table_name = '#report_table';
    var cdr_url = "";
    var customer_login ;
    $searchFilter.pageSize = '{{CompanyConfiguration::get('PAGE_SIZE')}}';
    jQuery(document).ready(function ($) {
        data_table  = $(table_name).dataTable({
            "bDestroy": true,
            "bProcessing": true,
                "bServerSide": true,
            "bAutoWidth": false,
            "sAjaxSource": baseurl + "/dealmanagement/get_customer_report",
            "fnServerParams": function (aoData) {
                aoData.push(
                        {"name": "StartDate", "value": $searchFilter.StartDate},
                        {"name": "EndDate","value": $searchFilter.EndDate}
                );
                data_table_extra_params.length = 0;
                data_table_extra_params.push(
                        {"name": "StartDate", "value": $searchFilter.StartDate},
                        {"name": "EndDate","value": $searchFilter.EndDate},
                        {"name":"Export","value":1}
                );
            },
            "iDisplayLength": '{{CompanyConfiguration::get('PAGE_SIZE')}}',
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
            "aoColumns": [
                {  "bSortable": true },
                {  "bSortable": true },
                {  "bSortable": true },
                {  "bSortable": true },
                {  "bSortable": true },
                {  "bSortable": true },
                {  "bSortable": true },
                {  "bSortable": true },
                {  "bSortable": true },
                {  "bSortable": true }  
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/analysis/get_account/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/analysis/get_account/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
            "fnDrawCallback": function () {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

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
            set_search_parameter($(this));
            return false;
        });

        set_search_parameter($("#customer_analysis"));
    });
</script>
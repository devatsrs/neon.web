<style>
    .modal-ku {
        width: 750px;
        margin: auto;
    }
    .display{
        display:none;
    }
</style>
@section('footer_ext')
    @parent

    <div class="modal fade" id="history-modal">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h3 class="modal-title">Contract History</h3>
                    </div>

                    <div class="modal-body">
                        <div class="dataTables_wrapper">
                            <table id="table-history" class="table table-bordered datatable">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Action By</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

<div class="modal-footer">
    <button  type="button" class="btn  btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
        <i class="entypo-cancel"></i>
        Close
    </button>
</div>
</div>
</div>
</div>


    <script type="text/javascript">
        var data_table_history = null;
        function load_history(){
            var $searchFilter = {};



            var accountServiceID = {{$AccountServiceID}}
            //console.log(accountServiceID);
                    {{--parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}')--}}

            $('#filter-button-toggle').show();

            if (data_table_history == null) {
            data_table_history = $("#table-history").dataTable({

                "bProcessing": true,
                "bServerSide": true,
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": baseurl + "/accountservices/history/" + accountServiceID,
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "aaSorting": [[2, 'desc']],
                "fnServerParams": function (aoData) {
                    //alert("Called1");

//                    aoData.push({"name":"ServiceName","value":$searchFilter.ServiceName},{"name":"sSearch_0","value":""},{"name":"ServiceId","value":$searchFilter.ServiceId},{"name":"FilterCurrencyId","value":$searchFilter.FilterCurrencyId});
//                    data_table_extra_params.length = 0;
//                    data_table_extra_params.push({"name":"ServiceName","value":$searchFilter.ServiceName},{"name":"sSearch_0","value":""},{"name":"ServiceId","value":$searchFilter.ServiceId},{"name":"FilterCurrencyId","value":$searchFilter.FilterCurrencyId},{ "name": "Export", "value": 1});
                },
                "aoColumns": [

                    {"bSortable": true}, //Name
                    {"bSortable": true}, //Type
                    {"bSortable": true}, //Gateway
                ],
                "oTableTools": {
                    "aButtons": [
//                        {
//                            "sExtends": "download",
//                            "sButtonText": "EXCEL",
//                            "sUrl": baseurl + "/servicesTemplate/exports/xlsx",
//                            sButtonClass: "save-collection btn-sm"
//                        },
//                        {
//                            "sExtends": "download",
//                            "sButtonText": "CSV",
//                            "sUrl": baseurl + "/servicesTemplate/exports/csv",
//                            sButtonClass: "save-collection btn-sm"
//                        }
                    ]
                },
                "fnDrawCallback": function () {

                }
            })
            }else{
                data_table_history.fnFilter(1,0);
            }
            /*
             $('#ServiceStatus').change(function() {
             if ($(this).is(":checked")) {
             data_table.fnFilter(1,0);  // 1st value 2nd column index
             } else {
             data_table.fnFilter(0,0);
             }
             });*/

            $("#service_filter").submit(function (e) {
                e.preventDefault();

                $searchFilter.ServiceName = $("#service_filter [name='ServiceName']").val();
                $searchFilter.CompanyGatewayID = $("#service_filter [name='CompanyGatewayID']").val();
                $searchFilter.ServiceStatus = $("#service_filter [name='ServiceStatus']").prop("checked");

                data_table_history.fnFilter('', 0);
                return false;
            });

            $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });

            $("#selectall").click(function (ev) {
                var is_checked = $(this).is(':checked');

                if (checkBoxArray != null || checkBoxArray == undefined)
                    checkBoxArray = [];

                $('#table-history tbody tr').each(function (i, el) {
                    var txtValue = $(this).find('.rowcheckbox').prop("checked", true).val();

                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        if (is_checked) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            $(this).addClass('selected');
                            console.log(txtValue);
                            if (txtValue)
                                checkBoxArray.push(txtValue);

                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            $(this).removeClass('selected');
                            checkBoxArray = [];
                        }
                    }
                });
            });
            // select single record which row is clicked
            $('#table-history tbody').on('click', 'tr', function () {

                var txtValue = $(this).find('.rowcheckbox').prop("checked", true).val();
                var checked = $(this).is(':checked')
                if (checked == '') {
                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        $(this).toggleClass('selected');
                        if ($(this).hasClass('selected')) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            checkBoxArray.push(txtValue);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            checkBoxArray.pop(txtValue);

                        }
                    }
                }
            });
        }


    </script>
@stop


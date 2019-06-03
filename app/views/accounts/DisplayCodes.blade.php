


<script>


    jQuery(document).ready(function ($) {
        var AccountID = '{{$account->AccountID}}';
        var ServiceID='{{$ServiceID}}';
        var AccountServiceID='{{$AccountServiceID}}';

        var displaycode_list_fields = ['Code'];
        public_vars.$body = $("body");
        var $searchcli = {};
        var data_table_terminationtable;
        var displaycodes_datagrid_url = baseurl + "/discount_plan/TerminationDiscountPlanCodes";


        $("#DisplayCode_submit").click(function (e) {
            e.preventDefault();
            var TerminationDiscountPlanName =$("#DisplayCode-form [name=TerminationDiscountPlanName]").val();
            var RateCodeID =$("#DisplayCode-form [name=RateCodeID]").val();

            data_table_displayCode = $("#table-displayCode").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,

                "sAjaxSource": displaycodes_datagrid_url,
                "fnServerParams": function (aoData) {
                    aoData.push({
                                "name": "AccountID",
                                "value": AccountID
                            },
                            {
                                "name": "ServiceID",
                                "value": ServiceID
                            },
                            {
                                "name": "AccountServiceID",
                                "value": AccountServiceID
                            },
                            {
                                "name": "TerminationDiscountPlanName",
                                "value": TerminationDiscountPlanName
                            },
                            {
                                "name": "RateCodeID",
                                "value": RateCodeID
                            }
                    );

                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({
                                "name": "AccountID",
                                "value": AccountID
                            },
                            {
                                "name": "ServiceID",
                                "value": ServiceID
                            },
                            {
                                "name": "AccountServiceID",
                                "value": AccountServiceID
                            },
                            {
                                "name": "TerminationDiscountPlanName",
                                "value": TerminationDiscountPlanName
                            },
                            {
                                "name": "RateCodeID",
                                "value": RateCodeID
                            }
                    );

                },
                "iDisplayLength": 10,
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#packageselectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {"bSortable": false},    // 1 Package


                ],
                "oTableTools": {
                    "aButtons": [
                        {}
                    ]
                },
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });


                }
            });


        });
    });


</script>
<div class="modal fade in" id="modal-DisplayCode">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="DisplayCode-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Display Codes</h4>
                </div>
                <div class="modal-body">
                    <table width="100%">
                        <tr>
                            <td width="20%"><label for="field-1" class="col-sm-1 control-label">Code</label></td>
                            <td width="20%"><input type="text"  class="form-control" id="RateCodeID" name="RateCodeID"></td>
                            <td>&nbsp;</td>
                            <td><button class="btn btn-primary btn-sm btn-icon icon-left" style="align:right;" id="DisplayCode_submit">
                            <i class="entypo-search"></i>
                            Search
                        </button></td>
                    </tr>
                    </table>
                    <input type="hidden" name="TerminationDiscountPlanName" value="">
              </div>

            </form>

            <table id="table-displayCode" class="table table-bordered datatable table-displayCode">
                <thead>
                <tr>
                    <th>Code</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        </div>
    </div>

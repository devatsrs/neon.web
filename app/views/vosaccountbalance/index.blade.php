@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form role="form" id="vos-account-balance" method="post"  action="{{Request::url()}}" class="form-horizontal form-groups-bordered validate" novalidate>

                <div class="form-group">
                    <label class="control-label" for="field-1">Account Name</label>
                    <input type="text" name="AccountName" class="form-control mid_fld "  value=""  />
                </div>

                <div class="form-group">
                    <label class="control-label" for="field-1">Account Number</label>
                    <input type="text" name="AccountNumber" class="form-control mid_fld "  value="{{Input::get('prefix')}}"  />
                </div>

                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')
<style>
.small_fld{width:80.6667%;}
.small_label{width:5.0%;}
.col-sm-e2{width:15%;}
.small-date-input{width:11%;}
#selectcheckbox{
    padding: 15px 10px;
}
</style>
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <a href="javascript:void(0)">Account Balance</a> </li>
</ol>
<h3>Account Balance</h3>

    <div class="clear"></div>

<br>
    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            {{--<th width="1%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>--}}
            <th>Account Name</th>
            <th>Account Number</th>
            <th>Account Balance</th>

        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        var toFixed = '{{get_round_decimal_places()}}';

                var list_fields  = ['AccountName','AccountNumber','AccountBalance'];
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {

                    $('#filter-button-toggle').show();

                    $searchFilter.AccountName = $("#vos-account-balance [name='AccountName']").val();
                    $searchFilter.AccountNumber = $("#vos-account-balance [name='AccountNumber']").val();

                    data_table = $("#table-4").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/VOS/AccountBalance/ajax_datagrid/type",
                        "fnServerParams": function (aoData) {
                            aoData.push(
                                    {"name": "AccountName", "value": $searchFilter.AccountName},
                                    {"name": "AccountNumber","value": $searchFilter.AccountNumber}
                            );
                            data_table_extra_params.length = 0;
                            data_table_extra_params.push(
                                    {"name": "AccountName", "value": $searchFilter.AccountName},
                                    {"name": "AccountNumber","value": $searchFilter.AccountNumber},
                                    {"name":"Export","value":1}
                            );

                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox1.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "aaSorting": [[0, 'asc']],
                        "aoColumns": [
                            /*{
                                "bSortable": false, //checkbox
                                mRender: function (id, type, full) {
                                    var chackbox = '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + full[0] + '" class="rowcheckbox" ></div>';
                                    if($('#Recall_on_off').prop("checked")){
                                        chackbox='';
                                    }
                                    return chackbox;
                                }
                            },*/
                            {
                                "bSortable": true //AccountName
                            },
                            {
                                "bSortable": true //AccountNumber
                            },
                            {
                                "bSortable": true //AccountBalance
                            }

                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": baseurl + "/VOS/AccountBalance/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                                    sButtonClass: "save-collection"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/VOS/AccountBalance/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                                    sButtonClass: "save-collection"
                                }
                            ]
                        },
                        "fnDrawCallback": function () {

                            $(".dataTables_wrapper select").select2({
                                minimumResultsForSearch: -1
                            });
                            if($('#Recall_on_off').prop("checked")){
                                $('#selectcheckbox').addClass('hidden');
                            }else{
                                $('#selectcheckbox').removeClass('hidden');
                            }
                            $("#table-4 tbody input[type=checkbox]").each(function (i, el) {
                                var $this = $(el),
                                        $p = $this.closest('tr');

                                $(el).on('change', function () {
                                    var is_checked = $this.is(':checked');

                                    $p[is_checked ? 'addClass' : 'removeClass']('selected');
                                });
                            });

                            $('.tohidden').removeClass('hidden');
                            $('#selectall').removeClass('hidden');
                            if($('#Recall_on_off').prop("checked")){
                                $('.tohidden').addClass('hidden');
                                $('#selectall').addClass('hidden');
                            }
                            //select all record
                            $('#selectallbutton').click(function(){
                                if($('#selectallbutton').is(':checked')){
                                    checked = 'checked=checked disabled';
                                    $("#selectall").prop("checked", true).prop('disabled', true);
                                    $('#table-4 tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    });
                                }else{
                                    checked = '';
                                    $("#selectall").prop("checked", false).prop('disabled', false);
                                    $('#table-4 tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    });
                                }
                            });
                        }

                    });
                    $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });


                    /*$(document).on('click', '#table-4 tbody tr', function() {
                        $(this).toggleClass('selected');
                        if($(this).is('tr')) {
                            if ($(this).hasClass('selected')) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false);
                            }
                        }
                    });*/

                    $('#selectall').click(function(){
                        if($(this).is(':checked')){
                            checked = 'checked=checked';
                            $(this).prop("checked", true);
                            $(this).parents('table').find('tbody tr').each(function (i, el) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                                $(this).addClass('selected');
                            });
                        }else{
                            checked = '';
                            $(this).prop("checked", false);
                            $(this).parents('table').find('tbody tr').each(function (i, el) {
                                $(this).find('.rowcheckbox').prop("checked", false);
                                $(this).removeClass('selected');
                            });
                        }
                    });



                    $('[name="Status"]').on('select2-open', function() {
                        $('.select2-results .select2-add').on('click', function(e) {
                            e.stopPropagation();
                        });
                    });

                    $("#vos-account-balance").submit(function(e) {
                        e.preventDefault();
                        public_vars.$body = $("body");
                        //show_loading_bar(40);
                        $searchFilter.AccountName = $("#vos-account-balance [name='AccountName']").val();
                        $searchFilter.AccountNumber = $("#vos-account-balance [name='AccountNumber']").val();

                        data_table.fnFilter('', 0);
                        return false;
                    });

                });

                // Replace Checboxes
                $(".pagination a").click(function (ev) {
                    replaceCheckboxes();
                });

        function getselectedIDs(){
            var SelectedIDs = [];
            $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                var accountIDs = $(this).val().trim();
                SelectedIDs[i++] = accountIDs;
            });
            return SelectedIDs;
        }

            </script>
    <style>
                .dataTables_filter label{
                    display:none !important;
                }
                .dataTables_wrapper .export-data{
                    right: 30px !important;
                }
            </style>
    @include('includes.errors')
    @include('includes.success')

@stop
@section('footer_ext')
    @parent



@stop
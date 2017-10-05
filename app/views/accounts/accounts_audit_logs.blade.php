@extends('layout.main')
@section('content')

    <ol class="breadcrumb bc-3">
        <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
        <li> <a href="{{URL::to('accounts')}}">Accounts</a> </li>
        <li>
            {{--<a><span>{{customer_dropbox($account->AccountID)}}</span></a>--}}
            <a><span>{{customer_leads_dropbox($account->AccountID)}}</span></a>
        </li>
        <li class="active"> <strong>Account Logs</strong> </li>
    </ol>
    <h3>Accounts</h3>

    @include('includes.errors')
    @include('includes.success')

    {{--<p style="text-align: right;">
        @if(User::checkCategoryPermission('Account','Add'))
            <a href="{{URL::to('accounts/create')}}" class="btn btn-primary ">
                <i class="entypo-plus"></i>
                Add New
            </a>
        @endif
    </p>--}}
    <table class="table table-bordered datatable" id="table-4">
        <thead>
            <tr>
                {{--<th>Account Name</th>--}}
                <th>Field Name</th>
                <th>Old Value</th>
                <th>New Value</th>
                <th>Modified At</th>
                <th>Modified By</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {

            jQuery(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });

            var AccountID = jQuery('#drp_toandfro_jump').val();
            data_table = $("#table-4").dataTable({

                "bProcessing":true,
                "bDestroy": true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/accounts/"+AccountID+"/ajax_datagrid_account_logs",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting"   : [[3, 'desc']],
                "fnServerParams": function(aoData) {
                    aoData.push();
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push();
                },
                "aoColumns":
                        [
                            /*{ "bSortable": true}, //AccountName*/
                            { "bSortable": true}, //Field Name
                            { "bSortable": false}, //Old Value
                            { "bSortable": false}, //New Value
                            { "bSortable": true}, //Modified At
                            { "bSortable": true}, //Modified By
                        ],
                "oTableTools": {
                    "aButtons": []
                },
                "fnDrawCallback": function() {
                    jQuery(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }
            });
        }); // main script over
    </script>
    <style>
        .dataTables_filter label{
            display:none !important;
        }
        .dataTables_wrapper .export-data{
            right: 30px !important;
        }
    </style>
@stop
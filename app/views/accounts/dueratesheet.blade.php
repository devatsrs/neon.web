@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">

        <strong>Recent Due Sheet</strong>
    </li>
</ol>
<h3>Recent Due Sheet</h3>
<div class="row">
    <div class="col-md-12">
        <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="duesheet_filter">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="field-1">Account Type</label>
                        <div class="col-sm-3">
                            {{ Form::select('AccountType',array(AccountApproval::CUSTOMER=>'Customer',AccountApproval::VENDOR=>'Vendor'), Input::get('accounttype'), array("class"=>"select2")) }}
                        </div>
                        <label class="col-sm-2 control-label" for="field-1">Due Date</label>
                        <div class="col-sm-3">
                            {{ Form::select('DueDate',array('Today'=>'Today' , 'Tomorrow'=>'Tomorrow','Yesterday'=>'Yesterday'),'', array("class"=>"select2")) }}
                        </div>
                    </div>
                    <p style="text-align: right;">
                        <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                            <i class="entypo-search"></i>
                            Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="cler row">
    <div class="col-md-12">
        <form role="form" id="form1" method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Recent Due Sheet
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <table class="table table-bordered datatable" id="table-4">
                                <thead>
                                    <tr>
                                        <th>Account Name</th>
                                        <th>Trunk</th>
                                        <th>Due Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($) {
    var $searchFilter = {};

        $searchFilter.AccountType = $("#duesheet_filter [name='AccountType']").val();
        $searchFilter.DueDate = $("#duesheet_filter [name='DueDate']").val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/accounts/ajax_datagrid_sheet/type",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[2, "desc"]],
            "fnServerParams": function(aoData) {
                aoData.push({"name":"AccountType","value":$searchFilter.AccountType},{"name":"DueDate","value":$searchFilter.DueDate});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"AccountType","value":$searchFilter.AccountType},{"name":"Export","value":1},{"name":"DueDate","value":$searchFilter.DueDate});
            },
            "fnRowCallback": function(nRow, aData) {
                $(nRow).attr("id", "host_row_" + aData[2]);
            },
            "aoColumns":
                    [
                        {},
                        {},
                        {},
                        {
                        mRender: function ( id, type, full ) {
                            if($searchFilter.AccountType == '{{AccountApproval::CUSTOMER}}'){
                                action = '<a href="'+baseurl+'/customers_rates/'+id+'" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>'
                            }
                            if($searchFilter.AccountType == '{{AccountApproval::VENDOR}}'){
                                action = '<a href="'+baseurl+'/vendor_rates/'+id+'" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>'
                            }

                            return action;
                            }
                        }

                    ],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl+'/accounts/ajax_datagrid_sheet/xlsx',
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl+'/accounts/ajax_datagrid_sheet/csv',
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    },
            "fnDrawCallback": function() {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });
            }
        });

        $("#duesheet_filter").submit(function(e) {
            e.preventDefault();
            $searchFilter.AccountType = $("#duesheet_filter [name='AccountType']").val();
            $searchFilter.DueDate = $("#duesheet_filter [name='DueDate']").val();
            data_table.fnFilter('', 0);
            return false;
         });
    });

</script>
@stop
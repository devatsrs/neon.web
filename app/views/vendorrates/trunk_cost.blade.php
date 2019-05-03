@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('accounts')}}">Accounts</a>
        </li>
        <li>
            {{customer_dropbox($id,["IsVendor"=>1])}}
        </li>
        <li>
            <a href="{{URL::to('accounts/'.$Account->AccountID.'/edit')}}"></i>Edit Account({{$Account->AccountName}})</a>
        </li>
        <li class="active">
            <strong>Vendor Trunk Cost</strong>
        </li>
    </ol>
    <h3>Vendor Trunk Cost</h3>

    @include('includes.errors')
    @include('includes.success')

    <ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
        @if(User::checkCategoryPermission('VendorRates','Connection'))
            <li>
                <a href="{{ URL::to('/vendor_rates/connection/'.$id) }}" >
                    <span class="hidden-xs">Connection</span>
                </a>
            </li>
        @endif

        @if(User::checkCategoryPermission('VendorRates','TrunkCost'))
            <li class="active">
                <a href="{{ URL::to('/vendor_rates/'.$id.'/trunk_cost') }}" >
                    <span class="hidden-xs">Trunk Cost</span>
                </a>
            </li>
        @endif
        @if(User::checkCategoryPermission('VendorRates','Download'))
            <li>
                <a href="{{ URL::to('/vendor_rates/'.$id.'/download') }}" >
                    <span class="hidden-xs">Vendor Rate Download</span>
                </a>
            </li>
        @endif
        @if(User::checkCategoryPermission('VendorRates','History'))
            <li>
                <a href="{{ URL::to('/vendor_rates/'.$id.'/history') }}" >
                    <span class="hidden-xs">Vendor Rate History</span>
                </a>
            </li>
        @endif
        @if(User::checkCategoryPermission('Timezones','Add'))
            <li>
                <a href="{{ URL::to('/timezones/vendor_rates/'.$id) }}" >
                    <span class="hidden-xs">Time Of Day</span>
                </a>
            </li>
        @endif
    </ul>


    <div class="row">
        <div class="col-md-12">
            <form role="form" id="vendor-trunk-cost" method="post" action="{{URL::to('vendor_rates/'.$id.'/trunk_cost_update')}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Trunk Cost
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Trunk Cost</label>
                            <div class="col-sm-3">
                                <input type="text" name="Cost" class="form-control" placeholder="" value="{{ !empty($VendorTrunkCost) ? $VendorTrunkCost->Cost : '' }}" />
                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Currency</label>
                            <div class="col-sm-3">
                                {{ Form::select('CurrencyID', Currency::getCurrencyDropdownIDList(), !empty($VendorTrunkCost) ? $VendorTrunkCost->CurrencyID : $Account->CurrencyId, array("class"=>"select2")) }}
                            </div>

                            <div class="col-sm-2 text-center">
                                <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                    <i class="entypo-floppy"></i>
                                    Save
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th>Trunk Cost</th>
            <th>Currency</th>
            <th>Created At</th>
            <th>Created By</th>
        </tr>
        </thead>
        <tbody>


        </tbody>
    </table>

    <script>
        var data_table;
        $(function(){
            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/vendor_rates/{{$id}}/trunk_cost_ajax_datagrid",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [2, "desc"],
                "aoColumns":
                        [
                            {"bSortable": true}, //1 Trunk Cost
                            {"bSortable": true}, //2 Currency
                            {"bSortable": true}, //3 Created At
                            {"bSortable": true} //4 Created By
                        ],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/vendor_rates/{{$id}}/trunk_cost_ajax_datagrid/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/vendor_rates/{{$id}}/trunk_cost_ajax_datagrid/csv",
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

        });

        $("#vendor-trunk-cost").submit(function(e){
            e.preventDefault();
            var that = $(this);
            that.find(".btn").button("loading");
            $.ajax({
                url: that.attr("action"), //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    that.find(".btn").button("reset");
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        if( typeof data_table !=  'undefined'){
                            data_table.fnFilter('', 0);
                        }
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: that.serialize(),
                error: function(){
                    that.find(".btn").button("reset");
                    toastr.error("Something wrong.", "Error", toastr_opts);
                }
            });
        });
    </script>

    <style>
        .dataTables_filter label{
            display:none !important;
        }
        .dataTables_wrapper .export-data{
            right: 30px !important;
        }
        #table-4 tbody tr td.details-control{
            width: 8%;
        }
    </style>
@stop


@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="ratetable_filter">
                {{--<div class="form-group">
                    <label for="Search" class="control-label">Search</label>
                    <input class="form-control" name="Search" id="Search"  type="text" >
                </div>--}}
                <div class="form-group">
                    <label class="control-label" for="field-1">Level</label>
                    {{ Form::select('level', ["T"=>"Trunk Level","S"=>"Service Level"], 'T', array("class"=>"select2 level","data-type"=>"level")) }}
                </div>

                <div class="form-group hidden S">
                    <label class="control-label" for="field-1">Services</label>
                    {{ Form::select('services', $allservice, '', array("class"=>"select2","data-type"=>"service")) }}
                </div>
                <div class="form-group T">
                    <label class="control-label" for="field-1">Trunk</label>
                    {{ Form::select('TrunkID', $trunks, '', array("class"=>"select2","data-type"=>"trunk")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Customers</label>
                    {{Form::select('SourceCustomers[]', $all_customers, array() ,array("class"=>"form-control select2",'multiple'))}}
                </div>

                <div class="form-group">
                    <label for="field-1" class="control-label">Rate Table</label>
                    {{Form::select('RateTableId', $rate_tables, array() ,array("class"=>"form-control select2"))}}
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
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Rate Table For Multi Acoount</strong>
        </li>
    </ol>
    <h3>Rate Table For Multi Acoount</h3>
    <p style="text-align: right;">
        @if(User::checkCategoryPermission('RateTables','Add'))
            <a href="#" id="add-new-rate-table" class="btn btn-primary ">
                <i class="entypo-plus"></i>
                Bulk Changes Selected
            </a>
        @endif

    </p>

    <div class="cler row">
        <div class="col-md-12">
            <form role="form" id="form1" method="post" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Rate Table For Multi Account
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <div class="col-md-12">
                                {{-- Service Level --}}
                                <table class="table table-bordered datatable" id="table-service">
                                    <thead>
                                    <tr>
                                        <th><input type="checkbox" class="selectallcust" name="customer[]" /></th>
                                        <th>Customer Name</th>
                                        <th>Inbound Rate Table</th>
                                        <th>Outbound Rate Table</th>
                                        <th id="servicenametd">Service Name</th>
                                        <th >Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                {{-- Trunk Level--}}
                                <table class="table table-bordered datatable" id="table-trunk">
                                    <thead>
                                    <tr>
                                        <th><input type="checkbox" class="selectallcust" name="customer[]" /></th>
                                        <th>Customer Name</th>
                                        <th>Rate Table</th>
                                        <th>Trunk</th>
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

            var levellbl = $("#ratetable_filter select[name='level']").val();
            var levelhidden =  levellbl=='T'?'S':'T';
            $('.'+levellbl).removeClass('hidden');
            $('.'+levelhidden).addClass('hidden');
            $("#table-service").addClass('hidden');

            $('#filter-button-toggle').show();

            $("#ratetable_filter").submit(function(e) {

                var $searchFilter = {};
                $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();
                $searchFilter.RateTableId = $("#ratetable_filter [name='RateTableId']").val();
                $searchFilter.SourceCustomers = $("#ratetable_filter select[name='SourceCustomers[]']").val();
                $searchFilter.services = $('#ratetable_filter [name="services"]').val();
                $searchFilter.level = $('#ratetable_filter [name="level"]').val();
                if(jQuery.isEmptyObject($searchFilter.SourceCustomers) == false){
                    $searchFilter.SourceCustomers = $searchFilter.SourceCustomers.join(",");}

                if($searchFilter.level =='T'){

                    var tableid = 'table-trunk';
                    $("#table-service").addClass('hidden');
                    $("#table-trunk").removeClass('hidden');
                    $("#table-trunk_wrapper").removeClass('hidden');
                    $("#table-service_wrapper").addClass('hidden');
                    aoColumns = [
                        {"bSortable": false, //RateID
                            mRender: function(id, type, full) {
                                return '<div class="checkbox "><input type="checkbox" name="customer[]" value="' + full[0] + '" class="rowcheckbox" ></div>';
                            }
                        },
                        {"bSortable": true},
                        {"bSortable": true},
                        {"bSortable": true},
                        {
                            mRender: function(id, type, full) {
                                var action;
                                action = '<a title="Edit" data-id="'+  full[0] +'" data-rateTableName="'+full[4]+'" data-TrunkID="'+full[5]+'"  class="edit-ratetable btn btn-default btn-sm"><i class="entypo-pencil"></i></a>&nbsp;';
                                return action;
                            }
                        },
                    ]

                }else{

                    var tableid = 'table-service';
                    $("#table-service").removeClass('hidden');
                    $("#table-trunk").addClass('hidden');
                    $("#table-trunk_wrapper").addClass('hidden');
                    $("#table-service_wrapper").removeClass('hidden');

                    aoColumns = [
                        {"bSortable": false,
                            mRender: function(id, type, full) {
                                return '<div class="checkbox "><input type="checkbox" name="customer[]" value="' + full[0] + '" class="rowcheckbox" ></div>';
                            }
                        },
                        {"bSortable": true},
                        {"bSortable": true},
                        {"bSortable": true},
                        {"bSortable": true},
                        {
                            mRender: function(id, type, full) {
                                var action;
                                action = '<a title="Edit" data-id="'+ full[0] +'" data-OutboundRatetable="'+full[7]+'" data-inboundRatetable="'+full[6]+'" data-serviceId="'+full[5]+'" class="edit-ratetable btn btn-default btn-sm"><i class="entypo-pencil"></i></a>&nbsp;';

                                return action;
                            }
                        },
                    ]
                }

                data_table = $("#"+tableid).dataTable({
                    "bDestroy": true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": baseurl + "/rate_tables/multiaccounts/ajax_datagrid/type",
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "oTableTools": {},
                    "aaSorting": [[1, "desc"]],
                    "fnServerParams": function(aoData) {
                        aoData.push(
                                {"name":"TrunkID","value":$searchFilter.TrunkID},
                                {"name":"RateTableId","value":$searchFilter.RateTableId},
                                {"name":"SourceCustomers","value":$searchFilter.SourceCustomers},
                                {"name":"level","value":$searchFilter.level},
                                {"name":"services","value":$searchFilter.services},
                                {"name":"Search","value":$searchFilter.Search});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name":"TrunkID","value":$searchFilter.TrunkID},
                                {"name":"RateTableId","value":$searchFilter.RateTableId},
                                {"name":"SourceCustomers","value":$searchFilter.SourceCustomers},
                                {"name":"level","value":$searchFilter.level},
                                {"name":"services","value":$searchFilter.services},
                                {"name":"Search","value":$searchFilter.Search},
                                {"name":"Export","value":1});
                    },

                    "aoColumns":aoColumns ,
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/rate_tables/multiaccounts/ajax_datagrid/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/rate_tables/multiaccounts/ajax_datagrid/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    },
                    "fnDrawCallback": function() {
                        $(".selectallcust").click(function(ev) {
                            var is_checked = $(this).is(':checked');
                            $("#"+tableid +' tbody tr').each(function(i, el) {
                                if (is_checked) {
                                    $(this).find('.rowcheckbox').prop("checked", true);
                                } else {
                                    $(this).find('.rowcheckbox').prop("checked", false);
                                }
                            });
                        });

                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });


                    }
                });
                return false;

            });

            $('table tbody').on('click','.edit-ratetable',function(ev){

                $("#add-new-form").trigger("reset");
                ev.preventDefault();
                ev.stopPropagation();
                $('#modal-add-new-rate-table').trigger("reset");

                /* For Service Level */
                $("#modal-add-new-rate-table [name='selected_customer']").val($(this).attr('data-id'));
                var dataInBound = $(this).attr('data-inboundRatetable')!= 'null' ?  $(this).attr('data-inboundRatetable') : '';
                $("#modal-add-new-rate-table [name='InboundRateTable']").select2('val', dataInBound);
                var dataOutBound = $(this).attr('data-OutboundRatetable')!= 'null' ?  $(this).attr('data-OutboundRatetable') : '';
                $("#modal-add-new-rate-table [name='OutboundRateTable']").select2('val', dataOutBound);
                $("#modal-add-new-rate-table [name='ServiceID']").select2('val', $(this).attr('data-serviceId'));

                /* For Trunk Level */
                var RateTblId = $(this).attr('data-rateTableName')!= 'null' ?  $(this).attr('data-rateTableName') : '';
                var trunkid = $(this).attr('data-trunkid')!= 'null' ?  $(this).attr('data-trunkid') : '';
                $("#modal-add-new-rate-table [name='TrunkID']").select2('val', trunkid);
                $("#modal-add-new-rate-table [name='RateTable_Id']").select2('val', RateTblId);

                var level = $("#ratetable_filter select[name='level']").val();
                $("input[name='selected_level']").val(level);
                $('#modal-add-new-rate-table').modal('show');

            });

            $("#add-new-rate-table").click(function(ev) {

                ev.preventDefault();
                $('#modal-add-new-rate-table').modal('show', {backdrop: 'static'});
                /* Get selected Customer */
                var favorite = [];
                $.each($("input[name='customer[]']:checked"), function(){
                    favorite.push($(this).val());
                });
                favorite.shift()
                $.unique(favorite);
                $("input[name='selected_customer']").val(favorite.join(","));
                var level = $("#ratetable_filter select[name='level']").val();
                $("input[name='selected_level']").val(level);
                /* Get selected Customer */
            });

            $("#add-new-form").submit(function(ev){
                ev.preventDefault();
                update_new_url = baseurl + '/rate_tables/multiaccounts/store';
                submit_ajax(update_new_url,$("#add-new-form").serialize());
            });

            $(".level").click(function(){

                var levellbl = $("#ratetable_filter select[name='level']").val();
                var levelhidden =  levellbl=='T'?'S':'T';
                $('.'+levellbl).removeClass('hidden');
                $('.'+levelhidden).addClass('hidden');

            });

        });

    </script>

    @include('includes.errors')
    @include('includes.success')
    @include('trunk.trunkmodal')
    @include('currencies.currencymodal')

@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-add-new-rate-table">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Apply RateTable to Customer</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="selected_customer">
                        <input type="hidden" name="selected_level">
                        <div class="row T">
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="field-5" class="control-label">Trunk</label>
                                    {{Form::SelectControl('trunk')}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label for="field-5" class="control-label">Rate Table</label>
                                    {{Form::select('RateTable_Id', $rate_tables, '',array("class"=>"form-control select2"))}}
                                </div>
                            </div>

                        </div>
                        <div class="row S">
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label for="field-5" class="control-label">Select Service</label>
                                    {{ Form::select('ServiceID', $allservice, '', array("class"=>"select2","data-type"=>"service")) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    {{-- <input type="checkbox" class="form-group" name="inboundcheck">--}}
                                    <label for="field-5" class="control-label">Inbound Rate Table</label>
                                    {{Form::select('InboundRateTable', $rate_tables, '',array("class"=>"form-control select2"))}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{-- <input type="checkbox" class="form-group" name="outboundcheck">--}}
                                    <label for="field-5" class="control-label">Outbound Rate Table</label>
                                    {{Form::select('OutboundRateTable', $rate_tables, '',array("class"=>"form-control select2"))}}
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="codedeck-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Apply
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form role="form" id="did-search-form" method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                <div class="form-group">
                    <div class="SelectedEffectiveDate_Class">

                        <div class="input-group-btn">
                            <button type="button" class="btn btn-primary dropdown-toggle pull-right" data-toggle="dropdown" aria-expanded="false" style="width:100%">{{RateType::getRateTypeTitleBySlug(RateType::SLUG_DID)}}<span class="caret"></span></button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px; width:100% ">
                                <li> <a  href="{{URL::to('lcr')}}"  style="width:100%;background-color:#398439;color:#fff">{{RateType::getRateTypeTitleBySlug(RateType::SLUG_VOICECALL)}}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Date</label>
                    {{Form::text('EffectiveDate', date('Y-m-d') ,array("class"=>"form-control datepicker","Placeholder"=>"Effective Date" , "data-startdate"=>date('Y-m-d'), "data-start-date"=>date('Y-m-d',strtotime(" today")) ,"data-date-format"=>"yyyy-mm-dd" ,  "data-start-view"=>"2"))}}
                </div>
                <div class="form-group">
                    <label class="control-label">Product</label>
                    {{ Form::select('Product', $products, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Currency</label>
                    {{Form::select('Currency', $currencies, $CurrencyID ,array("class"=>"select2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Show Positions</label>
                    {{ Form::select('LCRPosition', LCR::$position, $LCRPosition , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Category</label>
                    {{Form::select('DIDCategoryID', $Categories, '',array("class"=>"select2"))}}
                </div>
                <div class="form-group usage-inp">
                    <h4>Usage Input</h4>
                </div>
                <div class="form-group" id="Calls">
                    <label class="control-label">Calls</label>
                    <input type="number" min="0" name="Calls" class="form-control" id="field-15" placeholder="" />
                </div>
                <div class="form-group" id="Minutes">
                    <label class="control-label">Minutes</label>
                    <input type="number" min="0" name="Minutes" class="form-control" id="field-15" placeholder="" />
                </div>
                <div class="form-group" id="Timezone">
                    <label class="control-label">Time of day</label>
                    {{ Form::select('Timezone', $Timezones, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group" id="TimezonePercentage">
                    <label class="control-label">Time of day %</label>
                    <input type="number" min="0" name="TimezonePercentage" class="form-control" id="field-15" placeholder="" />
                </div>
                <div class="form-group" id="Origination">
                    <label class="control-label">Origination</label>
                    <input type="text" name="Origination" class="form-control" id="field-15" placeholder="" />
                </div>
                <div class="form-group" id="OriginationPercentage">
                    <label class="control-label">Origination %</label>
                    <input type="number" min="0" name="OriginationPercentage" class="form-control" id="field-15" placeholder="" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Date From</label>
                    {{Form::text('DateFrom', date('Y-m-d') ,array("class"=>"form-control datepicker","Placeholder"=>"Effective Date" , "data-startdate"=>date('Y-m-d'), "data-start-date"=>date('Y-m-d',strtotime(" today")) ,"data-date-format"=>"yyyy-mm-dd" ,  "data-start-view"=>"2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Date To</label>
                    {{Form::text('DateTo', date('Y-m-d') ,array("class"=>"form-control datepicker","Placeholder"=>"Effective Date" , "data-startdate"=>date('Y-m-d'), "data-start-date"=>date('Y-m-d',strtotime(" today")) ,"data-date-format"=>"yyyy-mm-dd" ,  "data-start-view"=>"2"))}}
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
        .usage-inp h4 {
            color: #fff;
            margin-bottom: 0;
        }
    </style>
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>

            <a href="{{URL::to('lcr')}}">LCR</a>
        </li>
        <li class="active">
            <strong>Access</strong>
        </li>
    </ol>
    <h3 id="headingLCR">Access</h3>
    <div class="clear"></div>
    <br>
    <table class="table table-bordered datatable" id="table">
        <thead>
        {{--<tr>
            <th><h4><strong>PRS IT 0900 caller rate:</strong></h4></th>
            <th>$</th>
            <th></th>
            <th></th>
        </tr>--}}
        <tr>
            <th>Cost Components</th>
            <th id="dt_p1">Position 1</th>
            <th id="dt_p2">Position 2</th>
            <th id="dt_p3">Position 3</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        var $searchFilter = {};
        var data_table;
        jQuery(document).ready(function($) {
            $('#filter-button-toggle').show();
            $("#did-search-form").submit(function(e) {
                e.preventDefault();
                $searchFilter.EffectiveDate = $("#did-search-form input[name='EffectiveDate']").val();
                $searchFilter.Product       = $("#did-search-form input[name='Product']").val();
                $searchFilter.Currency      = $("#did-search-form select[name='Currency']").val();
                $searchFilter.LCRPosition   = $("#did-search-form select[name='LCRPosition']").val();
                $searchFilter.DIDCategoryID = $("#lcr-search-form select[name='DIDCategoryID']").val();
                $searchFilter.Calls         = $("#did-search-form input[name='Calls']").val();
                $searchFilter.Minutes       = $("#did-search-form input[name='Minutes']").val();
                $searchFilter.Origination   = $("#did-search-form input[name='Origination']").val();
                $searchFilter.OriginationPercentage   = $("#did-search-form input[name='OriginationPercentage']").val();
                $searchFilter.Timezone      = $("#lcr-search-form select[name='Timezone']").val();
                $searchFilter.TimezonePercentage = $("#lcr-search-form select[name='TimezonePercentage']").val();
                $searchFilter.Origination   = $("#lcr-search-form select[name='Origination']").val();
                $searchFilter.DateTo        = $("#did-search-form input[name='DateTo']").val();
                $searchFilter.DateFrom      = $("#did-search-form input[name='DateFrom']").val();

                data_table = $("#table").dataTable({
                    "bDestroy":    true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": baseurl + "/lcr/search_ajax_datagrid/type",
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "EffectiveDate", "value": $searchFilter.EffectiveDate},
                                {"name": "Product","value": $searchFilter.Product},
                                {"name": "Currency","value": $searchFilter.Currency},
                                {"name": "LCRPosition","value": $searchFilter.LCRPosition},
                                {"name": "DIDCategoryID","value": $searchFilter.DIDCategoryID},
                                {"name": "Calls","value": $searchFilter.Calls},
                                {"name": "Minutes","value": $searchFilter.Minutes},
                                {"name": "Origination","value": $searchFilter.Origination},
                                {"name": "OriginationPercentage","value": $searchFilter.OriginationPercentage},
                                {"name": "Timezone","value": $searchFilter.Timezone},
                                {"name": "TimezonePercentage","value": $searchFilter.TimezonePercentage},
                                {"name": "DateTo", "value": $searchFilter.DateTo},
                                {"name": "DateFrom", "value": $searchFilter.DateFrom}
                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "EffectiveDate", "value": $searchFilter.EffectiveDate},
                                {"name": "Product","value": $searchFilter.Product},
                                {"name": "Currency","value": $searchFilter.Currency},
                                {"name": "LCRPosition","value": $searchFilter.LCRPosition},
                                {"name": "DIDCategoryID","value": $searchFilter.DIDCategoryID},
                                {"name": "Calls","value": $searchFilter.Calls},
                                {"name": "Minutes","value": $searchFilter.Minutes},
                                {"name": "Origination","value": $searchFilter.Origination},
                                {"name": "OriginationPercentage","value": $searchFilter.OriginationPercentage},
                                {"name": "Timezone","value": $searchFilter.Timezone},
                                {"name": "TimezonePercentage","value": $searchFilter.TimezonePercentage},
                                {"name": "DateTo", "value": $searchFilter.DateTo},
                                {"name": "DateFrom", "value": $searchFilter.DateFrom}
                        );

                    },
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left '<'.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[5, 'desc']],
                    "aoColumns": [
                        {
                            "bSortable": true,
                            mRender: function (id, type, full) {
                                return full[0]
                            }
                        },
                        {
                            "bSortable": true,
                            mRender: function (id, type, full) {
                                return full[1]
                            }
                        },
                        {
                            "bSortable": true,
                            mRender: function (id, type, full) {
                                return full[2]
                            }
                        },
                        {
                            "bSortable": true,
                            mRender: function (id, type, full) {
                                return full[3]
                            }
                        }
                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/payments/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                                sButtonClass: "save-collection"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/payments/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
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
                return false;
            });

            // Replace Checboxes
            $(".pagination a").click(function(ev) {
                replaceCheckboxes();
            });
        });
    </script>
    <style>
        .dataTables_filter label{
            display:none !important;
        }
        .table_wrapper .export-data{
            right: 30px !important;
        }
        #margineDataTable_filter label {
            display: block !important;
            padding-right: 118px;
        }
        #table thead tr:first-child th {
            border: none;
        }
    </style>
@stop
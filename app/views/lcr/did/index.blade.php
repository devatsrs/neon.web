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
                            <button type="button" class="btn btn-primary dropdown-toggle pull-right didbutton" data-toggle="dropdown" aria-expanded="false" style="width:100%">{{RateType::getRateTypeTitleBySlug(RateType::SLUG_DID)}} <span class="caret"></span></button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px; width:100% ">
                                <li> <a  href="{{URL::to('lcr')}}"  style="width:100%;background-color:#398439;color:#fff">{{RateType::getRateTypeTitleBySlug(RateType::SLUG_VOICECALL)}}</a></li>
                               <li> <a  href="javascript:;" class="packageoption"  style="width:100%;background-color:#398439;color:#fff">{{RateType::getRateTypeTitleBySlug(RateType::SLUG_PACKAGE)}}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Date</label>
                    {{Form::text('EffectiveDate', date('Y-m-d') ,array("class"=>"form-control datepicker","Placeholder"=>"Effective Date" , "data-startdate"=>date('Y-m-d'), "data-start-date"=>date('Y-m-d',strtotime(" today")) ,"data-date-format"=>"yyyy-mm-dd" ,  "data-start-view"=>"2"))}}
                </div>
                <div class="form-group productdiv">
                    <label class="control-label">Country</label>
                    {{ Form::select('CountryID', $country, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group productdiv">
                    <label class="control-label">Access Type</label>
                    {{ Form::select('AccessType', $AccessType, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group productdiv">
                    <label class="control-label">Prefix</label>
                    {{ Form::select('Prefix', $Prefix, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group productdiv">
                    <label class="control-label">City/Tariff</label>
                    {{ Form::select('CityTariff', $CityTariff, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group packagediv" style="display:none;">
                    <label class="control-label">Package</label>
                    {{ Form::select('PackageID', $Package, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Currency</label>
                    {{Form::select('Currency', $currencies, $CurrencyID ,array("class"=>"select2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Show Positions</label>
                    {{ Form::select('LCRPosition', LCR::$position, $LCRPosition , array("class"=>"select2")) }}
                </div>
                <div class="form-group productcategory">
                    <label for="field-1" class="control-label">Category</label>
                    {{Form::select('DIDCategoryID', $Categories, '',array("class"=>"select2"))}}
                </div>
                <div class="form-group">
                    <h4 style="color: #fff;margin-bottom: 0;">Usage Input</h4>
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
                    <label class="control-label">Time Of Day</label>
                    {{ Form::select('Timezone', $Timezones, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group" id="TimezonePercentage">
                    <label class="control-label">Time Of Day %</label>
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
                    {{Form::text('DateFrom', date('Y-m-d',strtotime("-1 month")) ,array("class"=>"form-control datepicker","Placeholder"=>"Date From" , "data-date-format"=>"yyyy-mm-dd" ,  "data-start-view"=>"2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Date To</label>
                    {{Form::text('DateTo', date('Y-m-d') ,array("class"=>"form-control datepicker","Placeholder"=>"Date To" , "data-date-format"=>"yyyy-mm-dd" ,  "data-start-view"=>"2"))}}
                </div>
                <div class="form-group">
                    <br/>
                    <input type="hidden" name="lcr_type" id="lcr_type" value="" >
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
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>

            <a href="{{URL::to('lcr')}}">Compare Vendor Rate</a>
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
            <th id="dt_company1">Position 1</th>
            <th id="dt_company2">Position 2</th>
            <th id="dt_company3">Position 3</th>
            <th id="dt_company4">Position 4</th>
            <th id="dt_company5">Position 5</th>
            <th id="dt_company6">Position 6</th>
            <th id="dt_company7">Position 7</th>
            <th id="dt_company8">Position 8</th>
            <th id="dt_company9">Position 9</th>
            <th id="dt_company10">Position 10</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <script type="text/javascript">
        
        
        var $searchFilter = {};
        var data_table;


        jQuery(document).ready(function($) {
            var accbtnval=$('.didbutton').text();
            var packbtnval=$('.packageoption').text();

            @if($lcrType == "Package")
            $('#lcr_type').val('Y');
            $('.didbutton').html(packbtnval+' <span class="caret"></span>');
            $('.packageoption').html(accbtnval);
            $('.packagediv').show();
            $('.productdiv').hide();
            $('.productcategory').hide();
            $('#Origination').hide();
            $('#OriginationPercentage').hide();
            @endif

            @if($lcrType == "Access")
             $('#lcr_type').val('N');
            $('.didbutton').html(accbtnval+' <span class="caret"></span>');
            $('.packageoption').html(packbtnval);
            $('.packagediv').hide();
            $('.productdiv').show();
            $('.productcategory').show();
            $('#Origination').show();
            $('#OriginationPercentage').show();
            @endif

           // alert(accbtnval);
            $('.packageoption').click(function(){
                if($('.packageoption').text()=='Package'){

                   $('#lcr_type').val('Y');
                   $('.didbutton').html(packbtnval+' <span class="caret"></span>');
                   $('.packageoption').html(accbtnval);
                   $('.packagediv').show();
                   $('.productdiv').hide();
                   $('.productcategory').hide();
                    $('#Origination').hide();
                    $('#OriginationPercentage').hide();
                }else{

                    $('#lcr_type').val('N');
                   $('.didbutton').html(accbtnval+' <span class="caret"></span>');
                    $('.packageoption').html(packbtnval); 
                    $('.packagediv').hide();
                   $('.productdiv').show();
                   $('.productcategory').show();
                    $('#Origination').show();
                    $('#OriginationPercentage').show();
                }
                
            });
            
                
            $('#filter-button-toggle').show();

            //var data_table;
            if('{{$LCRPosition}}'=='5'){
                setTimeout(function(){
                    $('#dt_company6').addClass("hidden");
                    $('#dt_company7').addClass("hidden");
                    $('#dt_company8').addClass("hidden");
                    $('#dt_company9').addClass("hidden");
                    $('#dt_company10').addClass("hidden");
                },10);
            }else{
                setTimeout(function(){
                    $('#dt_company6').removeClass("hidden");
                    $('#dt_company7').removeClass("hidden");
                    $('#dt_company8').removeClass("hidden");
                    $('#dt_company9').removeClass("hidden");
                    $('#dt_company10').removeClass("hidden");
                },10);
            }



            $("#did-search-form").submit(function(e) {
                e.preventDefault();
                $searchFilter.EffectiveDate             = $("#did-search-form input[name='EffectiveDate']").val();
                $searchFilter.Country                   = $("#did-search-form select[name='CountryID']").val();
                $searchFilter.AccessType                = $("#did-search-form select[name='AccessType']").val();
                $searchFilter.Prefix                    = $("#did-search-form select[name='Prefix']").val();
                $searchFilter.CityTariff                    = $("#did-search-form select[name='CityTariff']").val();
                $searchFilter.Currency                   = $("#did-search-form select[name='Currency']").val();
                $searchFilter.LCRPosition                 = $("#did-search-form select[name='LCRPosition']").val();
                $searchFilter.DIDCategoryID              = $("#did-search-form select[name='DIDCategoryID']").val();
                $searchFilter.Calls                       = $("#did-search-form input[name='Calls']").val();
                $searchFilter.Minutes                    = $("#did-search-form input[name='Minutes']").val();
                $searchFilter.Origination                = $("#did-search-form input[name='Origination']").val();
                $searchFilter.Timezone                  = $("#did-search-form select[name='Timezone']").val();
                $searchFilter.TimezonePercentage        = $("#did-search-form input[name='TimezonePercentage']").val();
                $searchFilter.Origination               = $("#did-search-form input[name='Origination']").val();
                $searchFilter.OriginationPercentage       = $("#did-search-form input[name='OriginationPercentage']").val();
                $searchFilter.DateTo                     = $("#did-search-form input[name='DateTo']").val();
                $searchFilter.DateFrom                   = $("#did-search-form input[name='DateFrom']").val();
                
                $searchFilter.lcr_type                   = $("#did-search-form input[name='lcr_type']").val();
                $searchFilter.PackageID                  = $("#did-search-form select[name='PackageID']").val();

                @if($lcrType == "Package")
           $('#lcr_type').val('Y');
                $searchFilter.lcrType = "Package";
                @endif

                @if($lcrType == "Access")
                        $searchFilter.lcrType = "Access";
                @endif
                
                var aoColumnDefs, aoColumnDefs;
                if($searchFilter.LCRPosition=='5'){

                    setTimeout(function(){
                        $('#dt_company6').addClass("hidden");
                        $('#dt_company7').addClass("hidden");
                        $('#dt_company8').addClass("hidden");
                        $('#dt_company9').addClass("hidden");
                        $('#dt_company10').addClass("hidden");
                    },10);
                    aoColumns = [
                        {
                            mRender: function (id, type, full) {
                                if(full[0] == 'zCost'){
                                    return "<strong>Cost</strong>"
                                }
                                return full[0]
                            }

                        }, //1 Components
                        { "bSortable": false}, //2 Company 1
                        { "bSortable": false}, //3 Company 2
                        { "bSortable": false}, //4 Company 3
                        { "bSortable": false}, //5 Company 4
                        { "bSortable": false}, //6 Company 5
                        { "bVisible": false}, //7 Company 6
                        { "bVisible": false}, //8 Company 7
                        { "bVisible": false}, //9 Company 8
                        { "bVisible": false}, //10 Company 9
                        { "bVisible": false} //11 Company 10

                    ];

                    aoColumnDefs = [
                        {    "sClass": "destination", "aTargets": [ 0 ] },
                        {    "sClass": "rate1_class", "aTargets": [ 1 ] },
                        {    "sClass": "rate2_class", "aTargets": [ 2 ] },
                        {    "sClass": "rate3_class", "aTargets": [ 3 ] },
                        {    "sClass": "rate4_class", "aTargets": [ 4 ] },
                        {    "sClass": "rate5_class", "aTargets": [ 5 ] }
                    ];
                }else{
                    setTimeout(function(){
                        $('#dt_company6').removeClass("hidden");
                        $('#dt_company7').removeClass("hidden");
                        $('#dt_company8').removeClass("hidden");
                        $('#dt_company9').removeClass("hidden");
                        $('#dt_company10').removeClass("hidden");
                    },10);
                    aoColumns = [
                        {
                            mRender: function (id, type, full) {
                                if(full[0] == 'Total'){
                                    return "<strong>Total</strong>"
                                }
                                return full[0]
                            }

                        }, //1 Components
                        { "bSortable": false}, //2 Company 1
                        { "bSortable": false}, //3 Company 2
                        { "bSortable": false}, //4 Company 3
                        { "bSortable": false}, //5 Company 4
                        { "bSortable": false}, //6 Company 5
                        { "bSortable": false}, //7 Company 6
                        { "bSortable": false}, //8 Company 7
                        { "bSortable": false}, //9 Company 8
                        { "bSortable": false}, //10 Company 9
                        { "bSortable": false} //11 Company 10

                    ];

                    aoColumnDefs = [
                        {    "sClass": "destination", "aTargets": [ 0 ] },
                        {    "sClass": "rate1_class", "aTargets": [ 1 ] },
                        {    "sClass": "rate2_class", "aTargets": [ 2 ] },
                        {    "sClass": "rate3_class", "aTargets": [ 3 ] },
                        {    "sClass": "rate4_class", "aTargets": [ 4 ] },
                        {    "sClass": "rate5_class", "aTargets": [ 5 ] },
                        {    "sClass": "rate6_class", "aTargets": [ 6 ] },
                        {    "sClass": "rate7_class", "aTargets": [ 7 ] },
                        {    "sClass": "rate8_class", "aTargets": [ 8 ] },
                        {    "sClass": "rate9_class", "aTargets": [ 9 ] },
                        {    "sClass": "rate10_class", "aTargets": [ 10 ] }
                    ];
                }

                if(typeof $searchFilter.EffectiveDate  == 'undefined'){
                    toastr.error("Please Select a Effective Date", "Error", toastr_opts);
                    return false;
                }
                if((typeof $searchFilter.Currency  == 'undefined' ) ){
                    toastr.error("Please Select Currency", "Error", toastr_opts);
                    return false;
                }
                if($('#lcr_type').val()=='Access'){
                    if(typeof $searchFilter.Country  == 'undefined'){
                        toastr.error("Please Select a Country", "Error", toastr_opts);
                        return false;
                    }
                    if(typeof $searchFilter.AccessType  == 'undefined' || $searchFilter.AccessType == '' ){
                        toastr.error("Please Select a Access Type", "Error", toastr_opts);
                        return false;
                    }
                    if(typeof $searchFilter.Prefix  == 'undefined' || $searchFilter.Prefix == '' ){
                        toastr.error("Please Select a Prefix", "Error", toastr_opts);
                        return false;
                    }
                }
                if($('#lcr_type').val()=='Y'){
                }else{
                if(typeof $searchFilter.DIDCategoryID  == 'undefined' || $searchFilter.DIDCategoryID == '' ){
                    toastr.error("Please Select a Category", "Error", toastr_opts);
                    return false;
                }
                }

                data_table = $("#table").dataTable({
                    "bDestroy":    true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": baseurl + "/did/lcr/search_ajax_datagrid/type",
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "EffectiveDate", "value": $searchFilter.EffectiveDate},
                                {"name": "Currency","value": $searchFilter.Currency},
                                {"name": "CountryID","value": $searchFilter.Country},
                                {"name": "AccessType","value": $searchFilter.AccessType},
                                {"name": "Prefix","value": $searchFilter.Prefix},
                                {"name": "CityTariff","value": $searchFilter.CityTariff},
                                {"name": "LCRPosition","value": $searchFilter.LCRPosition},
                                {"name": "DIDCategoryID","value": $searchFilter.DIDCategoryID},
                                {"name": "Calls","value": $searchFilter.Calls},
                                {"name": "Minutes","value": $searchFilter.Minutes},
                                {"name": "Origination","value": $searchFilter.Origination},
                                {"name": "OriginationPercentage","value": $searchFilter.OriginationPercentage},
                                {"name": "Timezone","value": $searchFilter.Timezone},
                                {"name": "TimezonePercentage","value": $searchFilter.TimezonePercentage},
                                {"name": "DateTo", "value": $searchFilter.DateTo},
                                {"name": "DateFrom", "value": $searchFilter.DateFrom},
                                {"name": "lcr_type", "value": $searchFilter.lcr_type},
                                {"name": "PackageID", "value": $searchFilter.PackageID},
                                {"name": "lcrType", "value": $searchFilter.lcrType}

                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "EffectiveDate", "value": $searchFilter.EffectiveDate},
                                {"name": "CountryID","value": $searchFilter.Country},
                                {"name": "AccessType","value": $searchFilter.AccessType},
                                {"name": "Prefix","value": $searchFilter.Prefix},
                                {"name": "CityTariff","value": $searchFilter.CityTariff},
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
                                {"name": "DateFrom", "value": $searchFilter.DateFrom},
                                {"name": "lcr_type", "value": $searchFilter.lcr_type},
                                {"name": "PackageID", "value": $searchFilter.PackageID},
                                {"name": "lcrType", "value": $searchFilter.lcrType},
                                {"name":"Export","value":1}
                        );

                    },
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left '<'.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[5, 'desc']],
                    "aoColumnDefs": aoColumnDefs,
                    "aoColumns":aoColumns,
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/did/lcr/search_ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                                sButtonClass: "save-collection"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/did/lcr/search_ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                                sButtonClass: "save-collection"
                            }
                        ]
                    },
                    "fnDrawCallback": function (results) {

                        $('.btn').button('reset');

                        if (typeof results != 'undefined') {

                            try {

                                //Clear All Fields on Refresh
                                $('#dt_company1').html("");
                                $('#dt_company2').html("");
                                $('#dt_company3').html("");
                                $('#dt_company4').html("");
                                $('#dt_company5').html("");
                                $('#dt_company6').html("");
                                $('#dt_company7').html("");
                                $('#dt_company8').html("");
                                $('#dt_company9').html("");
                                $('#dt_company10').html("");


                                // console.log(data_table.oApi.aoColumns);
                                //data_table.Columns[0].ColumnName = "newColumnName";
                                if (typeof results.jqXHR.responseJSON.sColumns[1] != 'undefined') {
                                    $('#dt_company1').html(results.jqXHR.responseJSON.sColumns[1]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[2] != 'undefined') {
                                    $('#dt_company2').html(results.jqXHR.responseJSON.sColumns[2]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[3] != 'undefined') {
                                    $('#dt_company3').html(results.jqXHR.responseJSON.sColumns[3]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[4] != 'undefined') {
                                    $('#dt_company4').html(results.jqXHR.responseJSON.sColumns[4]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[5] != 'undefined') {
                                    $('#dt_company5').html(results.jqXHR.responseJSON.sColumns[5]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[6] != 'undefined') {
                                    $('#dt_company6').html(results.jqXHR.responseJSON.sColumns[6]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[7] != 'undefined') {
                                    $('#dt_company7').html(results.jqXHR.responseJSON.sColumns[7]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[8] != 'undefined') {
                                    $('#dt_company8').html(results.jqXHR.responseJSON.sColumns[8]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[9] != 'undefined') {
                                    $('#dt_company9').html(results.jqXHR.responseJSON.sColumns[9]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[10] != 'undefined') {
                                    $('#dt_company10').html(results.jqXHR.responseJSON.sColumns[10]);
                                }
                            }
                            catch(err) {

                            }
                        }

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
    </style>
@stop
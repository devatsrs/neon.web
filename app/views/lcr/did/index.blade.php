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
                    <label class="control-label">City</label>
                    {{ Form::select('City', $City, '', array("class"=>"select2")) }}
                </div>
                <div class="form-group productdiv">
                    <label class="control-label">Tariff</label>
                    {{ Form::select('Tariff', $Tariff, '', array("class"=>"select2")) }}
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
                <div class="form-group NoOfServicesContracted">
                    <label for="field-1" class="control-label">No Of Services</label>
                    {{Form::number('NoOfServicesContracted','' ,array("class"=>"form-control","min" => "0"))}}
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
            <strong id="treeLCR">{{!isset($_REQUEST['lcrType']) ? "Access":$_REQUEST['lcrType']}}</strong>
        </li>
    </ol>
    <h3 id="headingLCR">{{!isset($_REQUEST['lcrType']) ? "Access":$_REQUEST['lcrType']}}</h3>
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
            <th id="dt_AccessType">Access Type</th>
            <th id="dt_Country">Country</th>
            <th id="dt_Prefix">Prefix</th>
            <th id="dt_City">City</th>
            <th id="dt_Tariff">Tariff</th>
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
            $('#filter-button-toggle').show();
            var accbtnval=$('.didbutton').text();
            var packbtnval=$('.packageoption').text();

            @if($lcrType == "Package")
            $('#lcr_type').val('Y');
            var packbtnval = "Package";
            // $('.didbutton').html(packbtnval+' <span class="caret"></span>');
            $('.packageoption').html(accbtnval);
            $('.packagediv').show();
            $('.productdiv').hide();
            $('.productcategory').hide();
            $('.NoOfServicesContracted').hide();

            $('#Origination').hide();
            $('#OriginationPercentage').hide();
            if (packbtnval == "Package") {
                $('.didbutton').html("Package" + ' <span class="caret"></span>');
                $('.packageoption').html("Access");
            }else {
                $('.didbutton').html("Access" + ' <span class="caret"></span>');
                $('.packageoption').html("Package");
            }
                    @endif

                    @if($lcrType == "Access")
            var packbtnval = "Access";
            // alert(packbtnval);
            //var packbtnval = $('.didbutton').html();
            $('#lcr_type').val('N');
            // $('.didbutton').html(accbtnval+' <span class="caret"></span>');
            $('.packageoption').html(packbtnval);
            $('.packagediv').hide();
            $('.productdiv').show();
            $('.productcategory').show();
            $('.NoOfServicesContracted').show();
            $('#Origination').show();
            $('#OriginationPercentage').show();
            if (packbtnval == "Package") {
                $('.didbutton').html("Package" + ' <span class="caret"></span>');
                $('.packageoption').html("Access");
            }else {
                $('.didbutton').html("Access" + ' <span class="caret"></span>');
                $('.packageoption').html("Package");
            }
            @endif

            $('.didbutton').click(function(){
                
                var packbtnval = $('.didbutton').html();
                if(packbtnval=='Package'){
                    var accbtnval=$('.didbutton').text();
                    var packbtnval=$('.packageoption').text();
                    $('#lcr_type').val('Y');

                    // $('.packageoption').html(accbtnval);
                    $('.packagediv').show();
                    $('.productdiv').hide();
                    $('.productcategory').hide();
                    $('.NoOfServicesContracted').hide();
                    $('#Origination').hide();
                    $('#OriginationPercentage').hide();
                    $('.didbutton').html(packbtnval+' <span class="caret"></span>');
                    //  $('.packageoption').html(accbtnval);
                }else if(packbtnval == 'Access') {
                    var accbtnval = $('.didbutton').text();
                    var packbtnval = $('.packageoption').text();
                    $('#lcr_type').val('N');
                    //$('.packageoption').html(packbtnval);
                    $('.packagediv').hide();
                    $('.productdiv').show();
                    $('.productcategory').show();
                    $('.NoOfServicesContracted').show();
                    $('#Origination').show();
                    $('#OriginationPercentage').show();
                }
                //  $('.didbutton').html(accbtnval+' <span class="caret"></span>');
                //  $('.packageoption').html(packbtnval);
            });

            // alert(accbtnval);
            $('.packageoption').click(function(){
                // alert("Package Called");
                // var packbtnval = $('.didbutton').html();
                var packbtnval =$('.packageoption').html();
                if(packbtnval=='Package'){
                    var accbtnval=$('.didbutton').text();
                    var packbtnval=$('.packageoption').text();
                    $('#lcr_type').val('Y');

                    // $('.packageoption').html(accbtnval);
                    $('.packagediv').show();
                    $('.productdiv').hide();
                    $('.productcategory').hide();
                    $('.NoOfServicesContracted').hide();
                    $('#Origination').hide();
                    $('#OriginationPercentage').hide();
                    if (packbtnval == "Package") {
                        $('.didbutton').html("Package" + ' <span class="caret"></span>');
                        $('.packageoption').html("Access");
                    }else {
                        $('.didbutton').html("Access" + ' <span class="caret"></span>');
                        $('.packageoption').html("Package");
                    }
                    //  $('.packageoption').html(accbtnval);
                } else {
                    var accbtnval = $('.didbutton').text();
                    var packbtnval = $('.packageoption').text();
                    $('#lcr_type').val('N');
                    //$('.packageoption').html(packbtnval);
                    $('.packagediv').hide();
                    $('.productdiv').show();
                    $('.productcategory').show();
                    $('.NoOfServicesContracted').show();
                    $('#Origination').show();
                    $('#OriginationPercentage').show();
                    if (packbtnval == "Package") {
                        $('.didbutton').html("Package" + ' <span class="caret"></span>');
                        $('.packageoption').html("Access");
                    }else {
                        $('.didbutton').html("Access" + ' <span class="caret"></span>');
                        $('.packageoption').html("Package");
                    }
                }
            });

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
                $searchFilter.City                = $("#did-search-form select[name='City']").val();
                $searchFilter.Tariff                = $("#did-search-form select[name='Tariff']").val();
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
                $searchFilter.ServicesContracted         = $("#did-search-form input[name='NoOfServicesContracted']").val();
                $searchFilter.lcr_type                   = $("#did-search-form input[name='lcr_type']").val();
                $searchFilter.PackageID                  = $("#did-search-form select[name='PackageID']").val();
                $searchFilter.lcrType = '';
                if($('#lcr_type').val()=='Y'){
                    $searchFilter.lcrType = "Package";
                }else {
                    $searchFilter.lcrType = "Access";
                }

                var packbtnval = $searchFilter.lcrType;
                if (packbtnval == "Package") {
                    $('.didbutton').html("Package" + ' <span class="caret"></span>');
                    $('.packageoption').html("Access");
                }else {
                    $('.didbutton').html("Access" + ' <span class="caret"></span>');
                    $('.packageoption').html("Package");
                }
                // alert($searchFilter.lcrType);

                var aoColumnDefs, aoColumnDefs;
                if($searchFilter.lcrType == "Package" && $searchFilter.LCRPosition=='5'){
                    $('#dt_AccessType').html("Package Name");
                    $('#dt_Country').html("Position 1");
                    $('#dt_Prefix').html("Position 2");
                    $('#dt_City').html("Position 3");
                    $('#dt_Tariff').html("Position 4");
                    $('#dt_company1').html("Position 5");
                    setTimeout(function(){
                        $('#dt_company6').addClass("hidden");
                        $('#dt_company7').addClass("hidden");
                        $('#dt_company8').addClass("hidden");
                        $('#dt_company9').addClass("hidden");
                        $('#dt_company10').addClass("hidden");
                    },10);
                    aoColumns = [
                        { "bSortable": false}, //1 Access Type
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[1] != null) {
                                    var array = full[1].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[1];
                            }
                        }, //2 Country
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[2] != null) {
                                    var array = full[2].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[2];
                            }
                        }, //3 Prefix
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[3] != null) {
                                    var array = full[3].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[3];
                            }
                        }, //4 City
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[4] != null) {
                                    var array = full[4].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[4];
                            }
                        }, //5 Tariff
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[5] != null) {
                                    var array = full[5].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[5];
                            }
                        }, //6 Position 1
                        { "bSortable": false,"bVisible" : false}, //7 Position 2
                        { "bSortable": false,"bVisible" : false}, //8 Position 3
                        { "bSortable": false,"bVisible" : false}, //9 Position 4
                        { "bSortable": false,"bVisible" : false}, //10 Position 5
                        /* { "bSortable": false}, //11 Position 6
                         { "bVisible": false},  //12 Position 7
                         { "bVisible": false},  //13 Position 8
                         { "bVisible": false},  //14 Position 9
                         { "bVisible": false},  //15 Company 10*/


                    ];

                    aoColumnDefs = [
                        {    "sClass": "destination", "aTargets": [ 0 ] },
                        {    "sClass": "destination", "aTargets": [ 1 ] },
                        {    "sClass": "destination", "aTargets": [ 2 ] },
                        {    "sClass": "destination", "aTargets": [ 3 ] },
                        {    "sClass": "destination", "aTargets": [ 4 ] },
                        {    "sClass": "rate1_class", "aTargets": [ 5 ] },
                        {    "sClass": "rate2_class", "aTargets": [ 6 ] },
                        {    "sClass": "rate3_class", "aTargets": [ 7 ] },
                        {    "sClass": "rate4_class", "aTargets": [ 8 ] },
                        {    "sClass": "rate5_class", "aTargets": [ 9 ] }
                    ];
                }else if ($searchFilter.lcrType == "Package" && $searchFilter.LCRPosition=='10') {
                    $('#dt_AccessType').html("Package Name");
                    $('#dt_Country').html("Position 1");
                    $('#dt_Prefix').html("Position 2");
                    $('#dt_City').html("Position 3");
                    $('#dt_Tariff').html("Position 4");
                    $('#dt_company1').html("Position 5");
                    setTimeout(function(){
                        $('#dt_company6').removeClass("hidden");
                        $('#dt_company7').addClass("hidden");
                        $('#dt_company8').addClass("hidden");
                        $('#dt_company9').addClass("hidden");
                        $('#dt_company10').addClass("hidden");
                    },10);
                    aoColumns = [
                        { "bSortable": false}, //1 Access Type
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[1] != null) {
                                    var array = full[1].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[1];
                            }
                        }, //2 Country
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[2] != null) {
                                    var array = full[2].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[2];
                            }
                        }, //3 Prefix
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[3] != null) {
                                    var array = full[3].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[3];
                            }
                        }, //4 City
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[4] != null) {
                                    var array = full[4].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[4];
                            }
                        }, //5 Tariff
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[5] != null) {
                                    var array = full[5].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[5];
                            }
                        }, //6 Position 1
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[6] != null) {
                                    var array = full[6].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[6];
                            }
                        }, //7 Position 2
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[7] != null) {
                                    var array = full[7].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[7];
                            }
                        }, //8 Position 3
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[8] != null) {
                                    var array = full[8].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[8];
                            }
                        }, //9 Position 4
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[9] != null) {
                                    var array = full[9].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[9];
                            }
                        }, //10 Position 5
                        { "bSortable": false,
                            mRender: function (id, type, full) {
                                if (full[10] != null) {
                                    var array = full[10].split('<br>');
                                    var html = "<table>";
                                    html += "<tr><td>" + array[0] + "</td></tr>";
                                    html += "<tr><td>" + array[1] + "</td></tr>";
                                    html += "<tr><td>" + array[2] + "</td></tr>";
                                    html += "</table>";

                                    return html;
                                }
                                return full[10];
                            }
                        }, //11 Position 6
                        /* { "bVisible": false},  //12 Position 7
                         { "bVisible": false},  //13 Position 8
                         { "bVisible": false},  //14 Position 9
                         { "bVisible": false},  //15 Company 10*/


                    ];

                    aoColumnDefs = [
                        {    "sClass": "destination", "aTargets": [ 0 ] },
                        {    "sClass": "destination", "aTargets": [ 1 ] },
                        {    "sClass": "destination", "aTargets": [ 2 ] },
                        {    "sClass": "destination", "aTargets": [ 3 ] },
                        {    "sClass": "destination", "aTargets": [ 4 ] },
                        {    "sClass": "rate1_class", "aTargets": [ 5 ] },
                        {    "sClass": "rate2_class", "aTargets": [ 6 ] },
                        {    "sClass": "rate3_class", "aTargets": [ 7 ] },
                        {    "sClass": "rate4_class", "aTargets": [ 8 ] },
                        {    "sClass": "rate5_class", "aTargets": [ 9 ] }
                    ];
                }  else  if($searchFilter.LCRPosition=='5'){

                    $('#dt_AccessType').html("Access Type");
                    $('#dt_Country').html("Country");
                    $('#dt_Prefix').html("Prefix");
                    $('#dt_City').html("City");
                    $('#dt_Tariff').html("Tariff");
                    $('#dt_company1').html("Position 1");
                    $('#dt_company2').html("Position 2");
                    $('#dt_company3').html("Position 3");
                    $('#dt_company4').html("Position 4");
                    $('#dt_company5').html("Position 5");

                    setTimeout(function(){
                        $('#dt_company6').addClass("hidden");
                        $('#dt_company7').addClass("hidden");
                        $('#dt_company8').addClass("hidden");
                        $('#dt_company9').addClass("hidden");
                        $('#dt_company10').addClass("hidden");
                    },10);
                    aoColumns = [
                        { "bSortable": false}, //1 Access Type
                        { "bSortable": false}, //2 Country
                        { "bSortable": false}, //3 Prefix
                        { "bSortable": false}, //4 City
                        { "bSortable": false}, //5 Tariff
                        { "bSortable": false}, //6 Position 1
                        { "bSortable": false}, //7 Position 2
                        { "bSortable": false}, //8 Position 3
                        { "bSortable": false}, //9 Position 4
                        { "bSortable": false}, //10 Position 5
                        /* { "bSortable": false}, //11 Position 6
                         { "bVisible": false},  //12 Position 7
                         { "bVisible": false},  //13 Position 8
                         { "bVisible": false},  //14 Position 9
                         { "bVisible": false},  //15 Company 10*/


                    ];

                    aoColumnDefs = [
                        {    "sClass": "destination", "aTargets": [ 0 ] },
                        {    "sClass": "destination", "aTargets": [ 1 ] },
                        {    "sClass": "destination", "aTargets": [ 2 ] },
                        {    "sClass": "destination", "aTargets": [ 3 ] },
                        {    "sClass": "destination", "aTargets": [ 4 ] },
                        {    "sClass": "rate1_class", "aTargets": [ 5 ] },
                        {    "sClass": "rate2_class", "aTargets": [ 6 ] },
                        {    "sClass": "rate3_class", "aTargets": [ 7 ] },
                        {    "sClass": "rate4_class", "aTargets": [ 8 ] },
                        {    "sClass": "rate5_class", "aTargets": [ 9 ] }
                    ];
                } else {

                    $('#dt_company6').html("Position 6");
                    $('#dt_company7').html("Position 7");
                    $('#dt_company8').html("Position 8");
                    $('#dt_company9').html("Position 9");
                    $('#dt_company10').html("Position 10");

                    setTimeout(function(){
                        $('#dt_company6').removeClass("hidden");
                        $('#dt_company7').removeClass("hidden");
                        $('#dt_company8').removeClass("hidden");
                        $('#dt_company9').removeClass("hidden");
                        $('#dt_company10').removeClass("hidden");
                    },10);
                    aoColumns = [
                        { "bSortable": false}, //1 Access Type
                        { "bSortable": false}, //2 Country
                        { "bSortable": false}, //3 Prefix
                        { "bSortable": false}, //4 City
                        { "bSortable": false}, //5 Tariff
                        { "bSortable": false}, //6 Position 1
                        { "bSortable": false}, //7 Position 2
                        { "bSortable": false}, //8 Position 3
                        { "bSortable": false}, //9 Position 4
                        { "bSortable": false}, //10 Position 5
                        { "bSortable": false}, //11 Position 6
                        { "bSortable": false},  //12 Position 7
                        { "bSortable": false},  //13 Position 8
                        { "bSortable": false},  //14 Position 9
                        { "bSortable": false},  //15 Company 10



                    ];

                    aoColumnDefs = [
                        {    "sClass": "destination", "aTargets": [ 0 ] },
                        {    "sClass": "destination", "aTargets": [ 1 ] },
                        {    "sClass": "destination", "aTargets": [ 2 ] },
                        {    "sClass": "destination", "aTargets": [ 3 ] },
                        {    "sClass": "destination", "aTargets": [ 4 ] },
                        {    "sClass": "rate1_class", "aTargets": [ 5 ] },
                        {    "sClass": "rate2_class", "aTargets": [ 6 ] },
                        {    "sClass": "rate3_class", "aTargets": [ 7 ] },
                        {    "sClass": "rate4_class", "aTargets": [ 8 ] },
                        {    "sClass": "rate5_class", "aTargets": [ 9 ] },
                        {    "sClass": "rate6_class", "aTargets": [ 10 ] },
                        {    "sClass": "rate7_class", "aTargets": [ 11 ] },
                        {    "sClass": "rate8_class", "aTargets": [ 12 ] },
                        {    "sClass": "rate9_class", "aTargets": [ 13 ] },
                        {    "sClass": "rate10_class", "aTargets": [ 14 ] }
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
                    "sAjaxSource": baseurl + "/did/lcr/search_ajax_datagrid/type" ,
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "EffectiveDate", "value": $searchFilter.EffectiveDate},
                                {"name": "Currency","value": $searchFilter.Currency},
                                {"name": "CountryID","value": $searchFilter.Country},
                                {"name": "AccessType","value": $searchFilter.AccessType},
                                {"name": "Prefix","value": $searchFilter.Prefix},
                                {"name": "City","value": $searchFilter.City},
                                {"name": "Tariff","value": $searchFilter.Tariff},
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
                                {"name": "NoOfServicesContracted", "value": $searchFilter.ServicesContracted}
                                

                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "EffectiveDate", "value": $searchFilter.EffectiveDate},
                                {"name": "CountryID","value": $searchFilter.Country},
                                {"name": "AccessType","value": $searchFilter.AccessType},
                                {"name": "Prefix","value": $searchFilter.Prefix},
                                {"name": "City","value": $searchFilter.City},
                                {"name": "Tariff","value": $searchFilter.Tariff},
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
                                {"name": "NoOfServicesContracted", "value": $searchFilter.ServicesContracted},

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

                       // $('.btn').button('reset');

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
                                if (typeof results.jqXHR.responseJSON.sColumns[5] != 'undefined') {
                                    $('#dt_company1').html(results.jqXHR.responseJSON.sColumns[5]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[6] != 'undefined') {
                                    $('#dt_company2').html(results.jqXHR.responseJSON.sColumns[6]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[7] != 'undefined') {
                                    $('#dt_company3').html(results.jqXHR.responseJSON.sColumns[7]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[8] != 'undefined') {
                                    $('#dt_company4').html(results.jqXHR.responseJSON.sColumns[8]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[9] != 'undefined') {
                                    $('#dt_company5').html(results.jqXHR.responseJSON.sColumns[9]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[10] != 'undefined') {
                                    $('#dt_company6').html(results.jqXHR.responseJSON.sColumns[10]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[11] != 'undefined') {
                                    $('#dt_company7').html(results.jqXHR.responseJSON.sColumns[11]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[12] != 'undefined') {
                                    $('#dt_company8').html(results.jqXHR.responseJSON.sColumns[12]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[13] != 'undefined') {
                                    $('#dt_company9').html(results.jqXHR.responseJSON.sColumns[13]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[14] != 'undefined') {
                                    $('#dt_company10').html(results.jqXHR.responseJSON.sColumns[14]);
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

                $('#treeLCR').text($searchFilter.lcrType);
                $('#headingLCR').text($searchFilter.lcrType);
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
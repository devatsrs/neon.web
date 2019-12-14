@extends('layout.main')

@section('filter')

    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form role="form" id="lcr-search-form" method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                <div class="form-group">
                    <div class="SelectedEffectiveDate_Class">
                        {{--<label for="field-1" class="control-label">Type</label>--}}

                        <div class="input-group-btn">
                            <button type="button" class="btn btn-primary dropdown-toggle pull-right" data-toggle="dropdown" aria-expanded="false" style="width:100%">{{RateType::getRateTypeTitleBySlug(RateType::SLUG_VOICECALL)}}<span class="caret"></span></button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px; width:100% ">
                                <li> <a  href="{{URL::to('did/lcr?lcrType=Access')}}"  style="width:100%;background-color:#398439;color:#fff">{{RateType::getRateTypeTitleBySlug(RateType::SLUG_DID)}}</a></li>
                                <li> <a  href="{{URL::to('did/lcr?lcrType=Package')}}"  style="width:100%;background-color:#398439;color:#fff">{{RateType::getRateTypeTitleBySlug(RateType::SLUG_PACKAGE)}}</a></li>
                            </ul>
                        </div>




                    </div>
                </div>
                <div class="form-group">
                    <div class="SelectedEffectiveDate_Class">
                        <label for="field-1" class="control-label">Date</label>
                        {{Form::text('SelectedEffectiveDate', date('Y-m-d') ,array("class"=>"form-control datepicker","Placeholder"=>"Effective Date" , "data-startdate"=>date('Y-m-d'), "data-start-date"=>date('Y-m-d',strtotime(" today")) ,"data-date-format"=>"yyyy-mm-dd" ,  "data-start-view"=>"2"))}}
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">Origination Code</label>
                    <!--<input type="text" name="OriginationCode" class="form-control" placeholder="" />-->
                    <input list="OriginationCode" name="OriginationCode" class="form-control" />
                    <datalist id="OriginationCode">
                        <option value="blank">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="control-label">Origination Description</label>
                    <input type="text" name="OriginationDescription" class="form-control" placeholder="" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Destination Code</label>
                    <input type="text" name="Code" class="form-control" id="field-1" placeholder="" />
                </div>
                <div class="form-group">
                    <label class="control-label">Destination Description</label>
                    <input type="text" name="Description" class="form-control" id="field-1" placeholder="" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Trunk</label>
                    {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2")) }}
                </div>
                {{-- <div class="form-group">
                    <label class="control-label">Merge Time Of Day</label>
                    <p class="make-switch switch-small">
                        <input id="merge_timezones" name="merge_timezones" type="checkbox" value="1">
                    </p>
                </div> --}}
                <div class="form-group TimezonesMergedBox" style="display: none;">
                    <label class="control-label">Time Of Day</label>
                    {{ Form::select('TimezonesMerged[]', $Timezones, '', array("class"=>"select2","multiple"=>"multiple")) }}
                </div>
                <div class="form-group TimezonesMergedBox" style="display: none;">
                    <label class="control-label">Take Price</label>
                    {{ Form::select('TakePrice', array(RateGenerator::HIGHEST_PRICE=>'Highest Price',RateGenerator::LOWEST_PRICE=>'Lowest Price'), 0 , array("class"=>"select2")) }}
                </div>
                {{-- <div class="form-group" id="TimezonesBox">
                    <label class="control-label">Time Of Day</label>
                    {{ Form::select('Timezones', $Timezones, '', array("class"=>"select2")) }}
                </div> --}}
                <div class="form-group">
                    <label for="field-1" class="control-label">CodeDeck</label>
                    {{ Form::select('CodeDeckId', $codedecklist, $DefaultCodedeck , array("class"=>"select2")) }}
                </div>

                <div class="form-group">
                    <label for="field-1" class="control-label">Currency</label>
                    {{Form::select('Currency', $currencies, $CurrencyID ,array("class"=>"form-control select2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">LCR Policy</label>
                    {{ Form::select('Policy', LCR::$policy, LCR::LCR , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Show Positions</label>
                    {{ Form::select('LCRPosition', LCR::$position, $LCRPosition , array("class"=>"select2")) }}
                </div>
                <div class="form-group" style="display: none">
                    <label for="field-1" class="control-label">Group By</label>
                    {{Form::select('GroupBy', ["code"=>"Code"], 'Code' ,array("class"=>"form-control select2"))}}
                    {{--{{Form::select('GroupBy', ["code"=>"Code", "description" => "Description"], $GroupBy ,array("class"=>"form-control select2"))}}--}}
                </div>

                <div class="form-group">
                    <label for="field-1" class="control-label">Vendors</label>
                    {{Form::select('Accounts[]', $all_accounts, array() ,array("class"=>"form-control select2",'multiple'))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Use Preference</label>
                    <p class="make-switch switch-small">
                        <input id="Use_Preference" name="Use_Preference" type="checkbox" value="1">
                    </p>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Show Blocked Vendors</label>
                    <p class="make-switch switch-small">
                        <input id="vendor_block" name="vendor_block" type="checkbox" value="1">
                    </p>
                </div>
                <div class="form-group" style="display:none;"> <!--Hide this as per skype requirement by mam - 07-11-2019-->
                    <label for="field-1" class="control-label">Show All Vendor Codes</label>
                    <p class="make-switch switch-small">
                        <input id="show_customer_rate" name="show_all_vendor_codes" type="checkbox" value="1">
                    </p>
                </div>
                {{--   <div class="form-group">
                       <label for="field-1" class="control-label">Show Customer Sell Rate</label>
                       <p class="make-switch switch-small">
                           <input id="show_customer_rate" name="show_customer_rate" type="checkbox" value="1">
                       </p>
                   </div>--}}
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                    <div class="btn-group">
                        <button type="button" id="btn-xls" class="btn-export btn btn-default btn-xs">Excel</button>
                        <button type="button" id="btn-csv" class="btn-export btn btn-default btn-xs">CSV</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')
    <style>
        .lowest_rate{
            background-color: #ff6600;
        }
        .hrpadding{
            margin-top: 4px;
            margin-bottom: 2px;
        }
        .destination{
            cursor: pointer;
        }
        .toolbartitle{

            text-align: center;
        }
        .centercaption{
            background: #fff;
            border-top: 1px solid #ebebeb;
            border-bottom: 0;
            padding: 15px 12px;
            height: 58px;
        }
        .table-responsive {
            overflow-x: unset;
        }
        .exportbtn{
            margin-left: -15px;
            padding-right: 0;
        }

        .not-active {
  pointer-events: none;
  cursor: default;
  text-decoration: none;
  color: black;
}

    </style>

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>

            <a href="{{URL::to('accounts')}}">Compare Vendor Rate</a>
        </li>
        <li class="active">
            <strong>LCR</strong>
        </li>
    </ol>
    <h3>LCR</h3>

    <br>
    <div class="table-responsive">
        <table class="table table-bordered datatable" id="table-4">
            <thead>
            <tr>
                <th>Destination</th>
                <th>Time Of Day</th>
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
    </div>

    <div class="vendorRateInfo hide" style="display:none">
        <ul class="nav nav-tabs">
            <?php $i = 0; $active = ''; ?>
            @foreach($Timezones as $ID => $Title)
                <?php $active = $i==0 ? 'active' : ''; ?>
                <li class="{{$active}}"><a href="#customer-tabs-{{$ID}}" data-toggle="tab">{{$Title}}</a></li>
                <?php $i++; ?>
            @endforeach
        </ul>
        <div class="tab-content" style="overflow: hidden;margin-top: 15px;">
            <?php $i = 0; $active = ''; ?>
            @foreach($Timezones as $ID => $Title)
                <?php $active = $i==0 ? 'active' : ''; ?>
                <div class="tab-pane customer-tabs {{$active}}" id="customer-tabs-{{$ID}}"></div>
                <?php $i++; ?>
            @endforeach
        </div>
    </div>


    {{-- edit preference --}}
            <!-- Modal -->


    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Preference</h4>
                </div>
                <form id="edit-preference-form" method="post">

                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="submit" id="preference-update" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..." style="visibility: visible;"> <i class="entypo-floppy"></i> Save </button>
                        <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                    </div>
                </form>

            </div>

        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {

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

            $("#lcr-search-form").submit(function(e) {
                $(".vendorRateInfo").addClass('hide');
                var OriginationCode, OriginationDescription,Code, Description, Currency,CodeDeck,Use_Preference,vendor_block,show_all_vendor_codes,Policy,LCRPosition,GroupBy,SelectedEffectiveDate,aoColumns,aoColumnDefs,accounts,Timezones,merge_timezones,TimezonesMerged,TakePrice;
                OriginationCode = $("#lcr-search-form input[name='OriginationCode']").val();
                OriginationDescription = $("#lcr-search-form input[name='OriginationDescription']").val();
                Code = $("#lcr-search-form input[name='Code']").val();
                Description = $("#lcr-search-form input[name='Description']").val();
                Currency = $("#lcr-search-form select[name='Currency']").val();
                Trunk = $("#lcr-search-form select[name='Trunk']").val();
                CodeDeck = $("#lcr-search-form select[name='CodeDeckId']").val();
                Policy = $("#lcr-search-form select[name='Policy']").val();
                Use_Preference = $("#lcr-search-form [name='Use_Preference']").prop("checked");
                vendor_block = $("#lcr-search-form [name='vendor_block']").prop("checked");
                show_all_vendor_codes = $("#lcr-search-form [name='show_all_vendor_codes']").prop("checked");
                LCRPosition = $("#lcr-search-form select[name='LCRPosition']").val();
                GroupBy = $("#lcr-search-form select[name='GroupBy']").val();
                SelectedEffectiveDate = $("#lcr-search-form input[name='SelectedEffectiveDate']").val();
                Accounts = $("#lcr-search-form select[name='Accounts[]']").val();
                // Timezones = $("#lcr-search-form select[name='Timezones']").val();
                // merge_timezones = $("#lcr-search-form [name='merge_timezones']").prop("checked");
                TimezonesMerged = $("#lcr-search-form select[name='TimezonesMerged[]']").val();
                TakePrice       = $("#lcr-search-form select[name='TakePrice']").val();

                if(LCRPosition=='5'){
                    setTimeout(function(){
                        $('#dt_company6').addClass("hidden");
                        $('#dt_company7').addClass("hidden");
                        $('#dt_company8').addClass("hidden");
                        $('#dt_company9').addClass("hidden");
                        $('#dt_company10').addClass("hidden");
                    },10);
                    var GroupBy = $("#lcr-search-form select[name='GroupBy']").val();
                    aoColumns = [
                        {}, //1 Destination
                        {}, //1 Timezone
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                    '<i class="fa '+blockfa+'"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'" id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>'+hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //2 Company 1
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"   data-ratetablerateid="'+RateTableRateID+'"   id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //3 Company 2
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"    id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //4 Company 3
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //5 Company 4
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        } //6 Company 5

                    ];

                    aoColumnDefs = [
                        {    "sClass": "destination not-active", "aTargets": [ 0 ] },
                        {    "sClass": "timezone not-active", "aTargets": [ 1 ] },
                        {    "sClass": "rate1_class", "aTargets": [ 2 ] },
                        {    "sClass": "rate2_class", "aTargets": [ 3 ] },
                        {    "sClass": "rate3_class", "aTargets": [ 4 ] },
                        {    "sClass": "rate4_class", "aTargets": [ 5 ] },
                        {    "sClass": "rate5_class", "aTargets": [ 6 ] }
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
                        {}, //1 Destination
                        {}, //1 Timezone
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //2 Company 1
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //3 Company 2
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //4 Company 3
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //5 Company 4
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //6 Company 5
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //7 Company 6
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //8 Company 7
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //9 Company 8
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        }, //10 Company 9
                        { "bSortable": false,
                            mRender: function ( id, type, full ) {
                                if (typeof id != 'undefined' && id != null && id != 'null') {

                                    var array = id.split(",");
                                    var action = "" ;
                                    for (i=0;i<array.length;i++){
                                        //alert(array[i]);
                                        var data3 = array[i].split("=");
                                        action += data3[0];
                                        var blockdata = data3[1].split("-");
                                        var RateTableRateID = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var Preference = blockdata[4];
                                        var Blocked = blockdata[3];

                                        var blocktitle = Blocked  == 0 ? '"Block Code "' : '"UnBlock Code "';
                                        var blockfa = Blocked  == 0 ? 'fa-lock' : 'fa fa-unlock';
                                        var blockclass = Blocked  == 0 ? 'danger' : 'success';

                                        var countryblocktitle = Preference == 0 ? '"Country Block"' : '"Country UnBlock"';
                                        var Countryblockclass = Preference == 0 ? 'danger' : 'success';

                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';

                                        if(!merge_timezones) {
                                            action += '<a style="margin-left:3px" href="javascript:;" title=' + blocktitle + ' data-id="' + accountId + '" data-rowcode="' + RowCode + '" data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"  id="'+Blocked+'" class="blockingbycode btn btn-' + blockclass + ' btn-xs pull-right">' +
                                                    '<i class="fa ' + blockfa + '"></i></a>' +
                                                    '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="' + accountId + '" data-rowcode="'+RowCode+'"  data-preference="'+Preference+'"  data-ratetablerateid="'+RateTableRateID+'"' + '"id="'+Blocked+'">' +
                                                    '<i class="fa fa-pencil"></i></a>' + hr;
                                        }
                                    }
                                    return action;
                                }
                            }
                        } //11 Company 10

                    ];

                    aoColumnDefs = [
                        {    "sClass": "destination", "aTargets": [ 0 ] },
                        {    "sClass": "timezone not-active", "aTargets": [ 1 ] },
                        {    "sClass": "rate1_class", "aTargets": [ 2 ] },
                        {    "sClass": "rate2_class", "aTargets": [ 3 ] },
                        {    "sClass": "rate3_class", "aTargets": [ 4 ] },
                        {    "sClass": "rate4_class", "aTargets": [ 5 ] },
                        {    "sClass": "rate5_class", "aTargets": [ 6 ] },
                        {    "sClass": "rate6_class", "aTargets": [ 7 ] },
                        {    "sClass": "rate7_class", "aTargets": [ 8 ] },
                        {    "sClass": "rate8_class", "aTargets": [ 9 ] },
                        {    "sClass": "rate9_class", "aTargets": [ 10 ] },
                        {    "sClass": "rate10_class", "aTargets": [ 11 ] }
                    ];
                }
                if(typeof Trunk  == 'undefined' || Trunk == '' ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Select a Trunk", "Error", toastr_opts);
                    return false;
                }
                if(typeof CodeDeck  == 'undefined' || CodeDeck == '' ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Select a CodeDeck", "Error", toastr_opts);
                    return false;
                }
                if((typeof Code  == 'undefined' || Code == '' ) && (typeof Description  == 'undefined' || Description == '' )){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Enter a Code Or Description", "Error", toastr_opts);
                    return false;
                }
                if(typeof Currency  == 'undefined' || Currency == '' ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Select a Currency", "Error", toastr_opts);
                    return false;
                }


                data_table = $("#table-4").dataTable({
                    "bDestroy": true, // Destroy when resubmit form
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": baseurl + "/lcr/search_ajax_datagrid/type",
                    "fnServerParams": function(aoData) {
                        aoData.push({"name": "OriginationCode", "value": OriginationCode},{"name": "OriginationDescription", "value": OriginationDescription},{"name": "Code", "value": Code},{"name": "Description", "value": Description},{"name": "LCRPosition", "value": LCRPosition},{"name": "Accounts", "value": Accounts},  {"name": "Currency", "value": Currency}, {"name": "Trunk", "value": Trunk},{"name": "CodeDeck", "value": CodeDeck},{"name": "Use_Preference", "value": Use_Preference},{"name": "vendor_block", "value": vendor_block},{"name": "show_all_vendor_codes", "value": show_all_vendor_codes},{"name": "GroupBy", "value": GroupBy},{ "name" : "SelectedEffectiveDate"  , "value" : SelectedEffectiveDate },{"name":"Policy","value":Policy},{"name":"TimezonesMerged","value":TimezonesMerged},{"name":"TakePrice","value":TakePrice});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({"name": "OriginationCode", "value": OriginationCode},{"name": "OriginationDescription", "value": OriginationDescription},{"name": "Code", "value": Code},{"name": "Description", "value": Description},{"name": "LCRPosition", "value": LCRPosition},{"name": "Accounts", "value": Accounts},  {"name": "Currency", "value": Currency}, {"name": "Trunk", "value": Trunk},{"name": "CodeDeck", "value": CodeDeck},{"name": "Use_Preference", "value": Use_Preference},{"name": "vendor_block", "value": vendor_block},{"name": "show_all_vendor_codes", "value": show_all_vendor_codes},{"name": "GroupBy", "value": GroupBy},{ "name" : "SelectedEffectiveDate"  , "value" : SelectedEffectiveDate },{"name":"Policy","value":Policy},{"name":"TimezonesMerged","value":TimezonesMerged},{"name":"TakePrice","value":TakePrice},{"name":"Export","value":1});
                    },
                    "iDisplayLength": 10,
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-md-6 col-xs-12 col-left'i><'col-md-6 col-xs-12 col-right'p>>",
                    "aaSorting": [[0, "asc"]],
                    "aoColumnDefs": aoColumnDefs,
                    "aoColumns":aoColumns,
                    "oTableTools":
                    {
                        "aButtons": [

                        ]
                    },
                    "fnDrawCallback": function(results) {
                        /*console.log(results);*/
                        //results.aoData[0]._aData[0]="---------";


                        $('.btn.btn').button('reset');

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

                                if (typeof results.jqXHR.responseJSON.sColumns[2] != 'undefined') {
                                    $('#dt_company1').html(results.jqXHR.responseJSON.sColumns[2]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[3] != 'undefined') {
                                    $('#dt_company2').html(results.jqXHR.responseJSON.sColumns[3]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[4] != 'undefined') {
                                    $('#dt_company3').html(results.jqXHR.responseJSON.sColumns[4]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[5] != 'undefined') {
                                    $('#dt_company4').html(results.jqXHR.responseJSON.sColumns[5]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[6] != 'undefined') {
                                    $('#dt_company5').html(results.jqXHR.responseJSON.sColumns[6]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[7] != 'undefined') {
                                    $('#dt_company6').html(results.jqXHR.responseJSON.sColumns[7]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[8] != 'undefined') {
                                    $('#dt_company7').html(results.jqXHR.responseJSON.sColumns[8]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[9] != 'undefined') {
                                    $('#dt_company8').html(results.jqXHR.responseJSON.sColumns[9]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[10] != 'undefined') {
                                    $('#dt_company9').html(results.jqXHR.responseJSON.sColumns[10]);
                                }
                                if (typeof results.jqXHR.responseJSON.sColumns[11] != 'undefined') {
                                    $('#dt_company10').html(results.jqXHR.responseJSON.sColumns[11]);
                                }
                            }
                            catch(err) {

                            }
                        }

                        //mark_lowest_rate_selected(results);

                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    }
                });
                return false;
            });

            // Replace Checboxes
            $(".pagination a").click(function(ev) {
                replaceCheckboxes();123
            });

            function mark_lowest_rate_selected(result){
                var rates = new Array();
                var selected_index , min_rate;
                $('#table-4 > tbody > tr').each(function(index, row){

                    var i = 0;
                    min_rate = 0;
                    selected_index = -1;

                    $(row).find("td").each(function(){

                        if(i++ > 0){

                            td_rate_val  = $(this).html();
                            var br_index = td_rate_val.indexOf("<br>");
                            if( br_index > 0){
                                if(min_rate==0){
                                    min_rate = td_rate_val.substr(0,br_index);
                                    selected_index = i;
                                }else{
                                    rate  = td_rate_val.substr(0,br_index);
                                    if(rate < min_rate){
                                        min_rate = rate;
                                        selected_index = i;
                                    }
                                }
                            }
                        }
                    });
                    console.log(i +" min_rate  " +min_rate);
                    console.log(i +" selected_index"+selected_index);
                    if(selected_index > -1 ){
                        $(row).find("td:nth-child("+selected_index+")" ).addClass( "lowest_rate" );
                    }
                });
            }

            $('#table-4 tbody').on('click','.blockingbycode',function(){

                var thisclass = $(this);

                var descriptioname = $(this).parent().siblings(":first").text();
                var Timezones = $("#lcr-search-form select[name='Timezones']").val();
                var GroupBy = $("#lcr-search-form select[name='GroupBy']").val();
                var Blocked = thisclass.attr("id");
                var RateTableRateID = thisclass.attr("data-ratetablerateid");

                descriptioname = descriptioname.split("=>");
                var OriginationDescription = $.trim(descriptioname[0]).replace("*", "");
                var Description = $.trim(descriptioname[1]).replace("*", "");

                $.ajax({
                    type: "POST",
                    url: baseurl + '/vendor_blocking_lrc/blockunblockcode',
                    dataType: 'json',
                    data: {
                        Blocked: Blocked,
                        RateTableRateID: RateTableRateID,
                        Description: Description,
                        OriginationDescription: OriginationDescription,
                        GroupBy: GroupBy,
                        Timezones: Timezones,
                    },
                    success: function(data){
                        ShowToastr("success",data.message);
                        data_table.fnFilter('', 0);
                    }

                });
            });

            /* show margine datatable */
            $('#table-4 tbody').on('click', 'td.destination', function () {
                return false; // disabled
                var SelectedEffectiveDate = $("#lcr-search-form input[name='SelectedEffectiveDate']").val();
                var LCRPosition = $("#lcr-search-form select[name='LCRPosition']").val();
                $("#margineDataTable_processing").css('visibility','visible');
                var GroupBy = $("#lcr-search-form select[name='GroupBy']").val();
                var caption = $(this).html();
                var desinationdata = $(this).html().split(":");
                var code = desinationdata[0];
                var code_des = $(this).html();
                var allVendordata = $(this).next('td').html().split('<br>');
                var v_rate = allVendordata[0];
                var vendor = allVendordata[1];
                arr = [];
                arr['rate'] = [];
                arr['vendor'] = [];
                var PolicyID = $("#lcr-search-form select[name='Policy']").val();
                var ratekey = PolicyID == 1 ? 0 : 1;
                var vendorkey = PolicyID == 1 ? 1 : 2;
                for (i = 1; i <= LCRPosition; i++) {
                    var value = $(this).closest("tr").find("td:eq(" + i + ")").html().split('<br>');
                    var valuetd = $.trim(value);
                    if (valuetd.length != 0) {
                        var td2_vrate = value[ratekey];
                        var td2_vendor = value[vendorkey];
                        arr['rate'].push(td2_vrate);
                        arr['vendor'].push(td2_vendor);
                    }

                }

                $('.customer-tabs').each(function() {
                    var tab_id          = $(this).attr('id');
                    var tab_id_split    = tab_id.split('-');
                    var TimezonesID     = tab_id[tab_id.length-1];

                    $.ajax({
                        type: "POST",
                        url: baseurl + '/lcr/ajax_customer_rate_grid',
                        data: {
                            code: code,
                            TimezonesID: TimezonesID,
                            rate: v_rate,
                            GroupBy: GroupBy,
                            effactdate:SelectedEffectiveDate
                        },
                        success: function (response) {
                            var decimalpoint = response.decimalpoint;
                            var margindata = response.result;
                            var result = '<div class="table-responsive"><table id="margineDataTable'+TimezonesID+'" class="table table-bordered datatable">' +
                                    '<thead><tr><th id="dt_col1">Customer</th><th id="dt_col1">CRate</th><th id="dt_col2">&nbsp;</th></tr>' +
                                    '</thead><tbody>';
                            margindata.forEach(function (data) {
                                var verate = '<table class="table table-bordered" style="background-color:#f8f8ff"><tr><th>Vendor</th><th>Rate</th><th>Margin (Percentage)</th></tr>';
                                margin = "";
                                margin_percentage = "";
                                for (i = 0; i <= arr['rate'].length - 1; i++) {
                                    var margin = parseFloat(data.Rate) - parseFloat(arr['rate'][i]) ;
                                    var margincolor = parseFloat(data.Rate) < parseFloat(arr['rate'][i]) ? 'color:red' : '' ;
                                    var margin_percentage =  (parseFloat(data.Rate) * 100 / parseFloat(arr['rate'][i])) - 100;
                                    verate += '<tr><td>' + arr['vendor'][i] + '</td><td>' + arr['rate'][i] + '</td>' +
                                            '<td style="'+ margincolor +'">' + margin.toFixed(decimalpoint) + ' (' + margin_percentage.toFixed(2) + '%)</td></tr>';
                                }
                                verate += '</table></div>';
                                var linkurl = baseurl + "/customers_rates/" + data.AccountID;
                                var accountNameLink = '<a target="_blank" href="'+linkurl+'">'+data.AccountName+'</a>';

                                result += '<tr><td>'+accountNameLink+'</td><td>'+data.Symbol+''+data.Rate+'</td><td colspan="3">' + verate + '</td></tr>';
                            });
                            result += '</tbody></table>';
                            $(".vendorRateInfo").removeClass('hide');
                            $("#"+tab_id).html(result);

                            var margineDataTable = $('#margineDataTable'+TimezonesID).DataTable({
                                "bDestroy": true,
                                "bProcessing": true,
                                "sDom": "<'row'<'col-md-push-4 col-md-4 col-xs-12 centercaption'<'toolbartitle'> ><'col-md-pull-4 col-md-4 col-xs-12 col-left'l ><'col-md-4 col-xs-12 exportbtn'<'export-data exbtn'T>f>r>t<'row'<'col-md-6 col-xs-12 col-left'i><'col-md-6 col-xs-12 col-right'p>>",
                                "aaSorting": [[0, "asc"]],
                                "oTableTools": {
                                    "aButtons": [
                                        {
                                            "sExtends": "download",
                                            "sButtonText": "EXCEL",
                                            sButtonClass: "save-collection btn-sm",
                                            "fnClick": function (e, dt, node, config) {
                                                $.ajax({
                                                    type: "POST",
                                                    dataType: 'json',
                                                    url: baseurl + '/lcr/ajax_customer_rate_export/xlsx',
                                                    data: {type:'xlsx',vendor:arr['vendor'],rate:arr['rate'],customer:response},
                                                    success: function (data) {
                                                        document.location = baseurl + "/download_file?file="+data.fileurl;
                                                    }
                                                });
                                            }
                                        },
                                        {
                                            "sExtends": "download",
                                            "sButtonText": "CSV",
                                            sButtonClass: "save-collection btn-sm",
                                            "fnClick": function () {
                                                $.ajax({
                                                    type: "POST",
                                                    dataType: 'json',
                                                    url: baseurl + '/lcr/ajax_customer_rate_export/csv',
                                                    data: {type:'csv',vendor:arr['vendor'],rate:arr['rate'],customer:response},
                                                    success: function (data) {
                                                        document.location = baseurl + "/download_file?file="+data.fileurl;
                                                    }
                                                });
                                            }
                                        }
                                    ]
                                }
                            });
                            $(".dataTables_wrapper select").select2({
                                minimumResultsForSearch: -1
                            });
                            $("div.toolbartitle").html('<b>'+caption+'</b>');
                            $("#margineDataTable_processing").css('visibility','hidden');

                        }

                    });

                });

            });

            /* show margine datatable end */

            /* Edit preference */
            $(document).on('click','.openPopup',function(){

                var thisclass = $(this);
                var preference = thisclass.attr("data-preference");
                var ratetablerateid = thisclass.attr("data-ratetablerateid");
                var GroupBy = $("#lcr-search-form select[name='GroupBy']").val();
                Timezones = $("#lcr-search-form select[name='Timezones']").val();

                var descriptioname = thisclass.parent().siblings(":first").text();

                 descriptioname = descriptioname.split("=>");
                 var OriginationDescription = $.trim(descriptioname[0]).replace("*", "");
                var Description = $.trim(descriptioname[1]).replace("*", "");

                var data = '<div class="row">' +
                        '<div class="col-md-12">' +
                        '<div class="form-group">' +
                        '<label for="field-5" class="control-label">Enter Preference</label>' +
                        '<input type="number" value="'+preference+'" id="txtpreference" name="preference" class="form-control" placeholder="Enter Preference">' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<input type="hidden" name="OriginationDescription" value="'+OriginationDescription+'">' +
                        '<input type="hidden" name="Description" value="'+Description+'">' +
                        '<input type="hidden" name="GroupBy" value='+GroupBy+'>' +
                        '<input type="hidden" name="Timezones" value='+Timezones+'>' +
                        '<input type="hidden" name="RateTableRateID" value='+ratetablerateid+'>' +
                        '<input type="hidden" class="form-control">';
                $('.modal-body').html(data);
                $('#myModal').modal({show:true});


            });
            /* Edit Preference*/

            $('#edit-preference-form').submit(function(e){

                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: baseurl + '/lcr/edit_preference',
                    data: $("#edit-preference-form").serialize(),
                    dataType: 'json',
                    success: function(data)
                    {
                        ShowToastr("success",data.message);
                        $('#myModal').modal('hide');
                        data_table.fnFilter('', 0);
                    }
                });
                // return false;
            });

            $('#merge_timezones').on('change', function() {
                if($(this).is(":checked")) {
                    $('.TimezonesMergedBox').show();
                    $('#TimezonesBox').hide();
                } else {
                    $('#TimezonesBox').show();
                    $('.TimezonesMergedBox').hide();
                }
            });

            $('.btn-export').on('click', function() {
                var this_btn = $(this);
                var id = this_btn.attr('id');
                var type = 'csv';
                if(id=='btn-xls') {
                    type = 'xlsx';
                } else if(id=='btn-csv') {
                    type = 'csv';
                }
                var formData        = new FormData($("#lcr-search-form")[0]);
                var CodeDeck        = $("#lcr-search-form select[name='CodeDeckId']").val();
                var Use_Preference  = $("#lcr-search-form [name='Use_Preference']").prop("checked");
                var vendor_block    = $("#lcr-search-form [name='vendor_block']").prop("checked");
                var Code            = $("#lcr-search-form input[name='Code']").val();
                var Description     = $("#lcr-search-form input[name='Description']").val();
                var Currency        = $("#lcr-search-form select[name='Currency']").val();
                var Trunk           = $("#lcr-search-form select[name='Trunk']").val();
                formData.append("CodeDeck", CodeDeck);
                formData.append("Use_Preference", Use_Preference);
                formData.append("vendor_block", vendor_block);
                formData.append("Export", 1);
                formData.append("iDisplayStart", 0);
                formData.append("iDisplayLength", 10);
                formData.append("sSortDir_0", 'asc');

                if(typeof Trunk  == 'undefined' || Trunk == '' ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Select a Trunk", "Error", toastr_opts);
                    return false;
                }
                if(typeof CodeDeck  == 'undefined' || CodeDeck == '' ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Select a CodeDeck", "Error", toastr_opts);
                    return false;
                }
                if((typeof Code  == 'undefined' || Code == '' ) && (typeof Description  == 'undefined' || Description == '' )){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Enter a Code Or Description", "Error", toastr_opts);
                    return false;
                }
                if(typeof Currency  == 'undefined' || Currency == '' ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please Select a Currency", "Error", toastr_opts);
                    return false;
                }

                this_btn.attr('disabled','disabled');
                $.ajax({
                    url: baseurl + "/lcr/search_ajax_datagrid/"+type,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        this_btn.removeAttr('disabled');
                    },
                    // Form data
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

        });



    </script>
    <style>
        .dataTables_filter label{
            display:none !important;
        }
        .table-4_wrapper .export-data{
            right: 30px !important;
        }
        .rate1_class{
            background-color: #f5fea8;
        }
        .rate2_class{
            background-color: #f3fe9a;
        }
        .rate3_class{
            background-color: #f2fe8b;
        }
        .rate4_class{
            background-color: #f0fe7d;
        }
        .rate5_class{
            background-color: #EFFE6F;
        }
        .rate6_class{
            background-color: #E8F764;
        }
        .rate7_class{
            background-color: #E4EF7B;
        }
        .rate8_class{
            background-color: #DAE477;
        }
        .rate9_class{
            background-color: #CED774;
        }
        .rate10_class{
            background-color: #c1c96f;
        }
        #margineDataTable_filter label {
            display: block !important;
            padding-right: 118px;
        }
        .exbtn {
            right: 242px !important;
        }


    </style>
@stop
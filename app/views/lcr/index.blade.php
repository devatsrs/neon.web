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
                        <label for="field-1" class="control-label">Date</label>
                        {{Form::text('SelectedEffectiveDate', date('Y-m-d') ,array("class"=>"form-control datepicker","Placeholder"=>"Effective Date" , "data-start-date"=>date('Y-m-d',strtotime(" today")) ,"data-date-format"=>"yyyy-mm-dd" ,  "data-start-view"=>"2"))}}
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Code</label>
                    <input type="text" name="Code" class="form-control" id="field-1" placeholder="" value="" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Description</label>
                    <input type="text" name="Description" class="form-control" id="field-1" placeholder="" value="" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Trunk</label>
                    {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2")) }}
                </div>
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
                    {{ Form::select('Policy', LCR::$policy, LCR::LCR_PREFIX , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Show Positions</label>
                    {{ Form::select('LCRPosition', LCR::$position, $LCRPosition , array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Group By</label>
                    {{Form::select('GroupBy', ["code"=>"Code", "description" => "Description"], $GroupBy ,array("class"=>"form-control select2"))}}
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
                    <label for="field-1" class="control-label">Show Block Vendor</label>
                    <p class="make-switch switch-small">
                        <input id="vendor_block" name="vendor_block" type="checkbox" value="1">
                    </p>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Show Customer Sell Rate</label>
                    <p class="make-switch switch-small">
                        <input id="show_customer_rate" name="show_customer_rate" type="checkbox" value="1">
                    </p>
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
    </style>

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>

            <a href="{{URL::to('accounts')}}">Accounts</a>
        </li>
        <li class="active">
            <strong>LCR</strong>
        </li>
    </ol>
    <h3>LCR</h3>

    <br>

    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th>Destination</th>
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

    <div class="vendorRateInfo hide"></div>


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
                        <button type="button" id="preference-update" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..." style="visibility: visible;"> <i class="entypo-floppy"></i> Save </button>
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
                var show_customer_rate = $("#lcr-search-form [name='show_customer_rate']").prop("checked");
                if(show_customer_rate == false) {
                    $(".vendorRateInfo").addClass('hide');
                }
                var Code, Description, Currency,CodeDeck,Use_Preference,vendor_block, Policy,LCRPosition,GroupBy,SelectedEffectiveDate,aoColumns,aoColumnDefs,accounts;
                Code = $("#lcr-search-form input[name='Code']").val();
                Description = $("#lcr-search-form input[name='Description']").val();
                Currency = $("#lcr-search-form select[name='Currency']").val();
                Trunk = $("#lcr-search-form select[name='Trunk']").val();
                CodeDeck = $("#lcr-search-form select[name='CodeDeckId']").val();
                Policy = $("#lcr-search-form select[name='Policy']").val();
                Use_Preference = $("#lcr-search-form [name='Use_Preference']").prop("checked");
                vendor_block = $("#lcr-search-form [name='vendor_block']").prop("checked");
                LCRPosition = $("#lcr-search-form select[name='LCRPosition']").val();
                GroupBy = $("#lcr-search-form select[name='GroupBy']").val();
                SelectedEffectiveDate = $("#lcr-search-form input[name='SelectedEffectiveDate']").val();
                Accounts = $("#lcr-search-form select[name='Accounts[]']").val();
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
                                    }
                                    return action;
                                }
                            }
                        } //6 Company 5

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
                        {}, //1 Destination
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
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
                                        var blockid = blockdata[0];
                                        var accountId = blockdata[1];
                                        var RowCode = blockdata[2];
                                        var blocktitle = blockid == 0 ? 'Block' : 'UnBlock';
                                        var blockfa = blockid == 0 ? 'fa-lock' : 'fa-unlock-alt';
                                        var blockclass = blockid == 0 ? 'danger' : 'success';
                                        var len = array.length-1;
                                        var hr = len != i ? '<hr class="hrpadding">' : '';
                                        action += '<a style="margin-left:3px" href="javascript:;" title='+blocktitle+' data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'" class="blockingbycode btn btn-'+blockclass+' btn-xs pull-right">' +
                                                '<i class="fa '+blockfa+'"></i></a>' +
                                                '<a class="openPopup btn btn-grey btn-xs pull-right" title="Edit Preference" data-toggle="modal" data-id="'+accountId+'" data-rowcode="'+RowCode+'" id="'+blockid+'">' +
                                                '<i class="fa fa-pencil"></i></a>'+hr;
                                    }
                                    return action;
                                }
                            }
                        } //11 Company 10

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
                        aoData.push({"name": "Code", "value": Code},{"name": "Description", "value": Description},{"name": "LCRPosition", "value": LCRPosition},{"name": "Accounts", "value": Accounts},  {"name": "Currency", "value": Currency}, {"name": "Trunk", "value": Trunk},{"name": "CodeDeck", "value": CodeDeck},{"name": "Use_Preference", "value": Use_Preference},{"name": "vendor_block", "value": vendor_block},{"name": "GroupBy", "value": GroupBy},{ "name" : "SelectedEffectiveDate"  , "value" : SelectedEffectiveDate },{"name":"Policy","value":Policy});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({"name": "Code", "value": Code},{"name": "Description", "value": Description},{"name": "LCRPosition", "value": LCRPosition},{"name": "Accounts", "value": Accounts},  {"name": "Currency", "value": Currency}, {"name": "Trunk", "value": Trunk},{"name": "CodeDeck", "value": CodeDeck},{"name": "Use_Preference", "value": Use_Preference},{"name": "vendor_block", "value": vendor_block},{"name": "GroupBy", "value": GroupBy},{ "name" : "SelectedEffectiveDate"  , "value" : SelectedEffectiveDate },{"name":"Policy","value":Policy},{"name":"Export","value":1});
                    },
                    "iDisplayLength": 10,
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, "asc"]],
                    "aoColumnDefs": aoColumnDefs,
                    "aoColumns":aoColumns,
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/lcr/search_ajax_datagrid/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/lcr/search_ajax_datagrid/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    },
                    "fnDrawCallback": function(results) {
                        /*console.log(results);*/
                        //results.aoData[0]._aData[0]="---------";


                        $('.btn.btn').button('reset');

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
                            $('#dt_company1').html( results.jqXHR.responseJSON.sColumns[1] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[2] != 'undefined') {
                            $('#dt_company2').html( results.jqXHR.responseJSON.sColumns[2] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[3] != 'undefined') {
                            $('#dt_company3').html( results.jqXHR.responseJSON.sColumns[3] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[4] != 'undefined') {
                            $('#dt_company4').html( results.jqXHR.responseJSON.sColumns[4] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[5] != 'undefined') {
                            $('#dt_company5').html( results.jqXHR.responseJSON.sColumns[5] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[6] != 'undefined') {
                            $('#dt_company6').html( results.jqXHR.responseJSON.sColumns[6] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[7] != 'undefined') {
                            $('#dt_company7').html( results.jqXHR.responseJSON.sColumns[7] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[8] != 'undefined') {
                            $('#dt_company8').html( results.jqXHR.responseJSON.sColumns[8] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[9] != 'undefined') {
                            $('#dt_company9').html( results.jqXHR.responseJSON.sColumns[9] );
                        }
                        if (typeof results.jqXHR.responseJSON.sColumns[10] != 'undefined') {
                            $('#dt_company10').html( results.jqXHR.responseJSON.sColumns[10] );
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

                Trunk = $("#lcr-search-form select[name='Trunk']").val();
                CodeDeck = $("#lcr-search-form select[name='CodeDeckId']").val();
                GroupBy = $("#lcr-search-form select[name='GroupBy']").val();
                var thisclass = $(this);
                var thisid = thisclass.attr("id");
                var thisaccid = thisclass.attr("data-id");
                var rowcode = thisclass.attr("data-rowcode");
                $.ajax({
                    type: "POST",
                    url: baseurl + '/vendor_blocking_lrc/blockunblockcode',
                    data: {
                        id: thisid,
                        acc_id: thisaccid,
                        trunk: Trunk,
                        CodeDeckId: CodeDeck,
                        rowcode: rowcode,
                        GroupBy: GroupBy
                    },
                    success: function(response){
                        ShowToastr("success",response.message);
                        data_table.fnFilter('', 0);
                    }

                });
            });

            /* show margine datatable */
            $('#table-4 tbody').on('click', 'td.destination', function () {
                var show_customer_rate = $("#lcr-search-form [name='show_customer_rate']").prop("checked");
                if(show_customer_rate == true) {

                    GroupBy = $("#lcr-search-form select[name='GroupBy']").val();
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
                    for (i = 1; i <= 5; i++) {
                        var value = $(this).closest("tr").find("td:eq(" + i + ")").html().split('<br>');
                        var valuetd = $.trim(value);
                        if (valuetd.length != 0) {
                            var td2_vrate = value[0];
                            var td2_vendor = value[1];
                            arr['rate'].push(td2_vrate);
                            arr['vendor'].push(td2_vendor);
                        }

                    }
                    $.ajax({
                        type: "POST",
                        url: baseurl + '/lcr/margin-rate',
                        data: {
                            code: code,
                            rate: v_rate,
                            GroupBy: GroupBy,
                        },
                        success: function (response) {
                            var decimalpoint = response.decimalpoint;
                            var margindata = response.result;
                            var verate = '';
                            var result = '<h5 class="text-center bold">' + caption + '</h5>' +
                                    '<table id="margineDataTable" class="table table-bordered datatable">' +
                                    '<thead><tr><th id="dt_col1">Customer</th><th id="dt_col1">CRate</th><th id="dt_col2">&nbsp;</th></tr>' +
                                    '</thead><tbody>';
                            margindata.forEach(function (data) {
                                var verate = '<table class="table table-bordered"><tr><th>Vendor</th><th>Rate</th><th>Margin (Percentage)</th></tr>';
                                margin = "";
                                margin_percentage = "";
                                for (i = 0; i <= arr['rate'].length - 1; i++) {
                                    var margin = parseFloat(data.Rate) - parseFloat(arr['rate'][i]) ;
                                    var margincolor = parseFloat(data.Rate) < parseFloat(arr['rate'][i]) ? 'color:red' : '' ;
                                    var margin_percentage =  (parseFloat(data.Rate) * 100 / parseFloat(arr['rate'][i])) - 100;
                                    var margin_percentage = Math.abs(margin_percentage);
                                    verate += '<tr><td>' + arr['vendor'][i] + '</td><td>' + arr['rate'][i] + '</td>' +
                                            '<td style="'+ margincolor +'">' + Math.abs(margin.toFixed(decimalpoint)) + ' (' + margin_percentage.toFixed(2) + '%)</td></tr>';
                                }
                                verate += '</table>';
                                result += '<tr><td>' + data.AccountName + '</td><td>'+data.Rate+'</td><td colspan="3">' + verate + '</td></tr>';
                            });
                            result += '</tbody></table>';
                            $(".vendorRateInfo").removeClass('hide');
                            $(".vendorRateInfo").html(result);
                            var margineDataTable = $('#margineDataTable').DataTable({
                                "bDestroy": true,
                                "bProcessing": true,
                                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data exbtn'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                                "aaSorting": [[0, "asc"]],
                                "oTableTools": {
                                    "aButtons": [
                                        {
                                            "sExtends": "download",
                                            "sButtonText": "EXCEL",
                                            "sUrl": baseurl + "/lcr/margin-rate-export/xlsx/" + code,
                                            sButtonClass: "save-collection btn-sm",
                                        },
                                        {
                                            "sExtends": "download",
                                            "sButtonText": "CSV",
                                            "sUrl": baseurl + "/lcr/margin-rate-export/csv/" + code,
                                            sButtonClass: "save-collection btn-sm"
                                        }
                                    ]
                                }
                            });
                        }

                    });

                }
            });
            /* show margine datatable end */

            /* Edit preference */
            $(document).on('click','.openPopup',function(){

                Trunk = $("#lcr-search-form select[name='Trunk']").val();
                CodeDeck = $("#lcr-search-form select[name='CodeDeckId']").val();
                GroupBy = $("#lcr-search-form select[name='GroupBy']").val();
                var thisclass = $(this);
                var thisid = thisclass.attr("id");
                var thisaccid = thisclass.attr("data-id");
                var rowcode = thisclass.attr("data-rowcode");
                var data = '<div class="row">' +
                                '<div class="col-md-12">' +
                                    '<div class="form-group">' +
                                        '<label for="field-5" class="control-label">Enter Preference</label>' +
                                            '<input type="number" name="preference" class="form-control" placeholder="Enter Preference">' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '<input type="hidden" name="id" value='+thisid+'>' +
                        '<input type="hidden" name="acc_id" value='+thisaccid+'>' +
                        '<input type="hidden" name="trunk" value='+Trunk+'>' +
                        '<input type="hidden" name="CodeDeckId" value='+CodeDeck+'>' +
                        '<input type="hidden" name="GroupBy" value='+GroupBy+'>' +
                        '<input type="hidden" name="rowcode" value='+rowcode+'>' +
                        '<input type="hidden" class="form-control">';
                $('.modal-body').html(data);
                $('#myModal').modal({show:true});
                /*$('.modal-body').load(dataURL,function(){
                    $('#myModal').modal({show:true});
                });*/
            });
            /* Edit Preference*/

            $('#preference-update').click(function(){
                $.ajax({
                    type: "POST",
                    url: baseurl + '/lcr/edit_preference',
                    data: $("#edit-preference-form").serialize(),
                    success: function(data)
                    {
                        ShowToastr("success",data);
                        $('#myModal').modal('hide');
                        data_table.fnFilter('', 0);
                    }
                });
               // return false;
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
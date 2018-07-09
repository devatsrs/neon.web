@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form role="form" id="rate-compare-search-form" method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                <div class="form-group">
                    <label for="field-1" class="control-label">Code</label>
                    <input type="text" class="form-control popover-primary" name="Code"  id="field-1" placeholder="" value="" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Code Or Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Code/Description" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Description</label>
                    <input type="text" class="form-control popover-primary" name="Description"  id="field-1" placeholder="" value="" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Enter either Code Or Description. Use * for all codes or description. For wildcard search use  e.g. 92* or india*." data-original-title="Code/Description" />
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Trunk</label>
                    {{ Form::select('Trunk', $trunks, $default_trunk, array("class"=>"select2")) }}
                </div>
                <div class="form-group">
                    <label class="control-label">Timezone</label>
                    {{ Form::select('Timezones', $Timezones, '', array("class"=>"select2")) }}
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
                    <label for="field-1" class="control-label">Group By</label>
                    {{Form::select('GroupBy', ["code"=>"Code", "description" => "Description"], $GroupBy ,array("class"=>"form-control select2"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Effective</label>
                    {{Form::select('Effective', ["Now"=>"Current", "Future" => "Future", "Selected" => "Selected"], 'Now' ,array("class"=>"form-control select2"))}}
                    <span data-loading-text="..." data-html="true" data-trigger="hover" data-toggle="popover" data-placement="right" data-content="<b>Current:</b> System will use Current Rates for comparison<br><b>Future:</b> System will use maximum future rates for comparison<br><b>Selected:</b> System will use rate where effective date is equal to selected effective date<br>" data-original-title="Effective" class="hidden label label-info popover-primary">?</span>
                </div>
                <div class="form-group">
                    <div class="SelectedEffectiveDate_Class hidden">
                        <label for="field-1" class="control-label">Date</label>
                        {{Form::text('SelectedEffectiveDate', date('Y-m-d') ,array("class"=>"form-control datepicker","Placeholder"=>"Effective Date" , "data-start-date"=>date('Y-m-d',strtotime(" today")) ,"data-date-format"=>"yyyy-mm-dd" ,  "data-start-view"=>"2"))}}
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Vendors</label>
                    {{Form::select('SourceVendors[]', $all_vendors, array() ,array("class"=>"form-control select2",'multiple'))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Customers</label>
                    {{Form::select('SourceCustomers[]', $all_customers, array() ,array("class"=>"form-control select2",'multiple'))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Rate Tables</label>
                    {{Form::select('SourceRateTables[]', $rate_table, array() ,array("class"=>"form-control select2",'multiple'))}}
                </div>

                <div class="form-group hidden">
                    <label for="field-1" class="control-label"></label>
                    <label for="field-1" class="control-label">Vendors</label>
                    {{Form::select('DestinationVendors[]', $all_vendors, array() ,array("class"=>"form-control select2",'multiple'))}}
                </div>
                <div class="form-group hidden">
                    <label for="field-1" class="control-label">Customers</label>
                    {{Form::select('DestinationCustomers[]', $all_customers, array() ,array("class"=>"form-control select2",'multiple'))}}
                </div>
                <div class="form-group hidden">
                    <label for="field-1" class="control-label">Rate Tables</label>
                    {{Form::select('DestinationRateTables[]', $rate_table, array() ,array("class"=>"form-control select2",'multiple'))}}
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
    </style>

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Rate Analysis</strong>
        </li>
    </ol>
    <h3>Rate Analysis</h3>

    <br>

    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
        </tr>

        </thead>

        <tbody>
        <tr class="main">

        </tr>

        </tbody>
    </table>


    <script type="text/javascript">
        jQuery(document).ready(function($) {

            $('#filter-button-toggle').show();

            //var data_table;
            var Code, Description, Currency,CodeDeck,Trunk,GroupBy,Effective,SelectedEffectiveDate, SourceVendors,SourceCustomers,SourceRateTables,DestinationVendors,DestinationCustomers,DestinationRateTables;
            var _customers_json, _vendors_json;
            var _margins_array = new Array();


            $('select[name="Trunk"]').on( "change",function(e) {

                TrunkID = $(this).val();

                $.post( baseurl + '/rate_compare/load_account_dropdown', {"TrunkID":TrunkID,"IsCustomer":1 }, function(response) {
                    //var data = [{ id: 0, text: 'enhancement' }, { id: 1, text: 'bug' }, { id: 2, text: 'duplicate' }, { id: 3, text: 'invalid' }, { id: 4, text: 'wontfix' }];
                    _customers_json = response.data;
                    rebuildSelect2($("#rate-compare-search-form select[name='SourceCustomers[]']"),_customers_json,'');

                 }, "json" );

                $.post( baseurl + '/rate_compare/load_account_dropdown', {"TrunkID":TrunkID,"IsVendor":1 }, function(response) {
                    //var data = [{ id: 0, text: 'enhancement' }, { id: 1, text: 'bug' }, { id: 2, text: 'duplicate' }, { id: 3, text: 'invalid' }, { id: 4, text: 'wontfix' }];

                    _vendors_json = response.data;
                    rebuildSelect2($("#rate-compare-search-form select[name='SourceVendors[]']"),_vendors_json,'');

                 }, "json" );

            });

            $('select[name="Effective"]').on( "change",function(e) {
                var selection = $(this).val();
                var hidden = false;
                if ($(this).hasClass('hidden')) {
                    hidden = true;
                }
                $(".SelectedEffectiveDate_Class").addClass("hidden");
                console.log(selection);

                if(selection == 'Selected') {
                    $(".SelectedEffectiveDate_Class").removeClass("hidden");
                }

            });



            $("#rate-compare-search-form").submit(function(e) {

                _margins_array = new Array(); // reset

                Trunk = $("#rate-compare-search-form select[name='Trunk']").val();
                Timezones = $("#rate-compare-search-form select[name='Timezones']").val();
                CodeDeck = $("#rate-compare-search-form select[name='CodeDeckId']").val();
                Currency = $("#rate-compare-search-form select[name='Currency']").val();
                Code = $("#rate-compare-search-form input[name='Code']").val();
                Description = $("#rate-compare-search-form input[name='Description']").val();
                GroupBy = $("#rate-compare-search-form select[name='GroupBy']").val();
                Effective = $("#rate-compare-search-form select[name='Effective']").val();
                SelectedEffectiveDate = $("#rate-compare-search-form input[name='SelectedEffectiveDate']").val();
                SourceVendors = $("#rate-compare-search-form select[name='SourceVendors[]']").val();
                SourceCustomers = $("#rate-compare-search-form select[name='SourceCustomers[]']").val();
                SourceRateTables = $("#rate-compare-search-form select[name='SourceRateTables[]']").val();
                DestinationVendors = $("#rate-compare-search-form select[name='DestinationVendors[]']").val();
                DestinationCustomers = $("#rate-compare-search-form select[name='DestinationCustomers[]']").val();
                DestinationRateTables = $("#rate-compare-search-form select[name='DestinationRateTables[]']").val();

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

                if(SourceVendors == null && SourceCustomers == null && SourceRateTables == null && DestinationVendors == null && DestinationCustomers == null && DestinationRateTables == null  ){
                    setTimeout(function(){
                        $('.btn').button('reset');
                    },10);
                    toastr.error("Please select a Vendor or a Customer or a Rate Table", "Error", toastr_opts);
                    return false;
                }


                var aoColumns = [
                    { "bSortable": false },

                ];
                var aoColumnDefs = [
                    { "sClass": "", "aTargets": [ 0 ] } ,

                ];


                data_table = $("#table-4").dataTable({
                    "bDestroy": true, // Destroy when resubmit form
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": baseurl + "/rate_compare/search_ajax_datagrid/json",
                    "fnServerParams": function(aoData) {
                        aoData.push({ "name" : "Code"  , "value" : Code },{ "name" : "Description"  , "value" : Description },{ "name" : "Currency"  , "value" : Currency },{ "name" : "CodeDeck"  , "value" : CodeDeck },{ "name" : "Trunk"  , "value" : Trunk },{ "name" : "GroupBy"  , "value" : GroupBy },{ "name" : "Effective"  , "value" : Effective },{ "name" : "SelectedEffectiveDate"  , "value" : SelectedEffectiveDate },{ "name" : "SourceVendors"  , "value" : SourceVendors },{ "name" : "SourceCustomers"  , "value" : SourceCustomers },{ "name" : "SourceRateTables"  , "value" : SourceRateTables },{ "name" : "DestinationVendors"  , "value" : DestinationVendors },{ "name" : "DestinationCustomers"  , "value" : DestinationCustomers },{ "name" : "DestinationRateTables"  , "value" : DestinationRateTables }, {"name": "Timezones", "value": Timezones});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({ "name" : "Code"  , "value" : Code },{ "name" : "Description"  , "value" : Description },{ "name" : "Currency"  , "value" : Currency },{ "name" : "CodeDeck"  , "value" : CodeDeck },{ "name" : "Trunk"  , "value" : Trunk },{ "name" : "GroupBy"  , "value" : GroupBy },{ "name" : "Effective"  , "value" : Effective },{ "name" : "SelectedEffectiveDate"  , "value" : SelectedEffectiveDate },{ "name" : "SourceVendors"  , "value" : SourceVendors },{ "name" : "SourceCustomers"  , "value" : SourceCustomers },{ "name" : "SourceRateTables"  , "value" : SourceRateTables },{ "name" : "DestinationVendors"  , "value" : DestinationVendors },{ "name" : "DestinationCustomers"  , "value" : DestinationCustomers },{ "name" : "DestinationRateTables"  , "value" : DestinationRateTables },{"name":"Export","value":1}, {"name": "Timezones", "value": Timezones});
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
                                "sUrl": baseurl + "/rate_compare/search_ajax_datagrid/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/rate_compare/search_ajax_datagrid/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    },
                    "fnDrawCallback": function(results) {

                        $('.btn.btn').button('reset');

                        var source_column_index = [];
                        var destination_column_index = [];
                        var customerrate_column_index = [];
                        var vendorrate_column_index = [];
                        var ratetable_column_index = [];
                        var columnIDs_column_index = [];
                        var column_name = [];

                        if( typeof results.jqXHR.responseJSON.sColumns != 'undefined') {

                            $("#table-4"+'>thead').html('<tr></tr>');
                            $.each(results.jqXHR.responseJSON.sColumns, function (k, col) {
                                console.log(k + col);
                                var _class = "";

                                if (col.indexOf("Source") >= 0) { // this is not in use
                                    _class = "source";
                                    source_column_index.push(k);

                                } else if (col.indexOf("Destination:") >= 0) {
                                    _class = "destination";
                                    destination_column_index.push(k);
                                }
                                if (col.indexOf("(CR)") >= 0) {
                                    customerrate_column_index.push(k);
                                }else if (col.indexOf("(VR)") >= 0) {
                                    vendorrate_column_index.push(k);
                                }else if (col.indexOf("(RT)") >= 0) {
                                    ratetable_column_index.push(k);
                                }else if (col.indexOf("ColumnIDS") >= 0) {
                                    columnIDs_column_index.push(k);
                                }

                                if(col == 'Destination') {
                                    col_text = '';
                                } else if(col != 'ColumnIDS') {
                                    var _margin = '';
                                    if(typeof _margins_array[k] != 'undefined' && _margins_array[k]  != '' ) {
                                        _margin = _margins_array[k];
                                    }
                                    col_text = col +  ' <span class="float-right"><input type="text" name="margin" value="' + _margin + '"  placeholder="Margin" data-col-index="' + k + '" class="margin form-control popover-primary"  data-min="1" data-trigger="hover" data-toggle="popover" data-placement="right" data-content="Margin: Add \'p\' for percentage ie. 10p." data-original-title="Margin" ></span>';
                                }

                                column_name.push(col);
                                if (col.indexOf("ColumnIDS") == -1 ) {  // if not columnid no need to add column id in display

                                    str = '<th class="'+ _class +'">' + col_text + '</th>';
                                    $(str).appendTo("#table-4"+'>thead>tr');
                                }




                            });


                        }


                        if( typeof results.jqXHR.responseJSON.aaData != 'undefined') {

                            $("#table-4"+'>tbody').html('<tr></tr>');
                            if(results.jqXHR.responseJSON.aaData.length == 0) {
                                html = "<td ><center>No Data found</center></td>";
                                $(html).appendTo("#table-4"+'>tbody>tr:last');

                            }
                            $.each(results.jqXHR.responseJSON.aaData, function (k, row) {

                                console.log(k + row);
                                var _class = html = _code_description = "";

                                for(var i = 0 ; i < row.length ; i++ ){
                                    var str = _class = "";
                                    var _edit;
                                    var _type = '';
                                    var _column_name = '';

                                    str = row[i] ;
                                    if(typeof  column_name[i] != 'undefind' ){
                                        _column_name = column_name[i];
                                    }
                                    if($.inArray( i, source_column_index ) != -1 ){
                                        _class = "source";
                                    }else if($.inArray( i, destination_column_index ) != -1 ){
                                        _class = "destination";
                                    }

                                    if(i == 0) {
                                        _code_description = str;
                                    }

                                    /////////
                                    var action = '<span class = "hiddenRowData" >';

                                    if ($.inArray(i, customerrate_column_index) != -1) {
                                        _type = "customer_rate";
                                    } else if ($.inArray(i, vendorrate_column_index) != -1) {
                                        _type = "vendor_rate";
                                    } else if ($.inArray(i, ratetable_column_index) != -1) {
                                        _type = "rate_table";
                                    }

                                    var _ColumnIDS_index = i - 1;
                                    var ColumnIDS = row[row.length-1].split(',');
                                    var _typeID = ColumnIDS[_ColumnIDS_index];

                                    action += '<input type = "hidden"  name = "Type" value = "' + _type + '" / >';
                                    action += '<input type = "hidden"  name = "TypeID" value = "' + _typeID + '" / >';
                                    action += '<input type = "hidden"  name = "GroupBy" value = "' + GroupBy + '" / >';
                                    action += '<input type = "hidden"  name = "ColumnName" value = "' + _column_name + '" / >';

                                    if (GroupBy == 'description'){
                                        action += '<input type = "hidden"  name = "Code" value = "" / >';
                                        action += '<input type = "hidden"  name = "Description" value = "' + _code_description.trim() + '" / >';
                                    } else {

                                        var code_array = _code_description.split(':');

                                        $.each(code_array, function(index, value) {
                                            if(index == 0){
                                                action += '<input type = "hidden"  name = "Code" value = "' + value.trim() + '" / >';
                                            } else if(index == 1){
                                                action += '<input type = "hidden"  name = "Description" value = "' + value.trim() + '" / >';
                                            }
                                        });
                                    }
                                    ///////////
                                    if (str.trim() != '') {


                                        if (i > 0 ) {
                                            var rate_array = str.split('<br>');
                                            var _rate = '', _rate_orig = '', _effective_date = '';
                                            $.each(rate_array, function(index, value) {
                                                if(index == 0){
                                                    _rate_orig = _rate = value;

                                                    if(typeof _margins_array[i] != 'undefined' && _margins_array[i]  != '' ) {
                                                        _rate = add_margin(_margins_array[i],_rate);
                                                    }

                                                    action += '<input type = "hidden"  name = "Rate" value = "' + _rate + '" / >';

                                                } else if(index == 1){
                                                    _effective_date = value;
                                                    action += '<input type = "hidden"  name = "EffectiveDate" value = "' + value + '" / >';
                                                }
                                            });
                                            action += '</span>';


                                            _edit = ' <span class="float-right"><a href="#" class="edit-ratecompare btn btn-default btn-xs"><i class="entypo-pencil"></i>&nbsp;</a>'+action+'</span>';
                                            str = '<span class="_column_rate">'+_rate +'</span><br>';
                                            str += '<span class="_column_effectiveDate">'+_effective_date+'</span>';
                                            str += '<span class="_column_rate_orig hidden">'+_rate_orig +'</span><br>';
                                            str += _edit;

                                        }

                                    } else {

                                        /*if (i > 0 ) {

                                            action += '</span>';
                                            var _add = ' <span class="float-right"><a href="#" class="add-ratecompare btn btn-default btn-xs"><i class="entypo-plus"></i>&nbsp;</a>' + action + '</span>';
                                            str += _add;
                                        }*/
                                    }
                                    if (i < (row.length -1) ){ // skip ColumnIDS
                                        html += '<td class="'+ _class +'">' + str + '</td>';
                                    }

                                }

                                $(html).appendTo("#table-4"+'>tbody>tr:last');
                                $('<tr class="dynamic"></tr>').appendTo("#table-4"+'>tbody');

                            });


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

            function add_margin(_margin , _rate ) {

                _rate = parseFloat(_rate);

                if (_margin.indexOf("p") > 0) {

                    var _numeric_margin_ = parseFloat(_margin.replace("p", ''));

                    _new_rate = parseFloat(_rate + ( _rate * _numeric_margin_ / 100 ));

                } else {

                    _new_rate = parseFloat(_rate + parseFloat(_margin));

                }

                return _new_rate.toFixed(6);

            }

            $('table thead').on('change', '.margin', function (ev) {

                var _margin = $(this).val();
                var _index = $(this).attr("data-col-index")*1 + 1 ;

                _margins_array[_index-1] = _margin;


                // Add/update data_table_extra_params on margin change
                var param_name = "margin_" + (_index-1);
                var _param_exists = false;
                for (var i = 0; i < data_table_extra_params.length; i++)
                {

                    if(data_table_extra_params[i].name == param_name ) {
                        data_table_extra_params[i].value =_margin;
                        _param_exists = true;
                    }
                }
                if(!_param_exists) {
                    data_table_extra_params.push( { "name" : param_name , "value" : _margin } );
                }
                //------------------

                $('table tbody tr').each( function ( index ) {

                    var _selected_column  = $(this).find("td:nth-child(" +_index+ ")");
                    var _rate = _selected_column.find("span._column_rate_orig").text();
                    var _column_rate_el = _selected_column.find("span._column_rate");

                    if (_rate == '' || _column_rate_el.text() == '') {
                        return;
                    }else if (_margin == '') {
                        _column_rate_el.text(_rate);
                        _selected_column.find(".hiddenRowData").find("input[name=Rate]").val(_rate);
                    }else if ( _column_rate_el.text() != '') {

                        _new_rate =  parseFloat(add_margin(_margin, _rate));
                        _new_rate = _new_rate.toFixed(6);
                        _column_rate_el.text(_new_rate);
                        _selected_column.find(".hiddenRowData").find("input[name=Rate]").val(_new_rate);
                    }

                });



            });

            $('table tbody').on('click', '.add-ratecompare', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();

                $('#add-edit-ratecompare-form').find("input, textarea, select").val("");

                var cur_obj = $(this).parent().find(".hiddenRowData");

                var hidden_list_fields = ["GroupBy","Code","Description","Type","TypeID"];

                for(var i = 0 ; i< hidden_list_fields.length; i++){

                    var field_value = cur_obj.find("input[name='"+hidden_list_fields[i]+"']").val();
                    $("#add-edit-ratecompare-form [name='"+hidden_list_fields[i]+"']").val(field_value);

                }
                $("#add-edit-ratecompare-form [name='TrunkID']").val(Trunk);
                $("#add-edit-ratecompare-form [name='Effective']").val(Effective);
                $("#add-edit-ratecompare-form [name='SelectedEffectiveDate']").val(SelectedEffectiveDate);
                $("#add-edit-ratecompare-form [name='Action']").val("add");


                var edit_title = 'Add ' + cur_obj.find("input[name='ColumnName']").val().replace("<br>",' - ') ;

                $('#add-edit-modal-ratecompare h4').html(edit_title);
                $('#add-edit-modal-ratecompare').modal('show');

                if($("#add-edit-ratecompare-form [name='GroupBy']").val() == 'description' ){
                    $('#add-edit-modal-ratecompare .hide_if_groupby_description').addClass("hidden");
                }else {
                    $('#add-edit-modal-ratecompare .hide_if_groupby_description').removeClass("hidden");
                }

            });

            $('table tbody').on('click', '.edit-ratecompare', function (ev) {

                ev.preventDefault();
                ev.stopPropagation();

                //reset form
                $('#add-edit-ratecompare-form').find("input, textarea, select").val("");

                var cur_obj = $(this).parent().find(".hiddenRowData");

               var hidden_list_fields = ["GroupBy","Code","Description","Rate", "EffectiveDate","Type","TypeID"];


                for(var i = 0 ; i< hidden_list_fields.length; i++){

                    var field_value = cur_obj.find("input[name='"+hidden_list_fields[i]+"']").val();
                    $("#add-edit-ratecompare-form [name='"+hidden_list_fields[i]+"']").val(field_value);

                }


                $("#add-edit-ratecompare-form [name='NewDescription']").val($("#add-edit-ratecompare-form [name='Description']").val());
                $("#add-edit-ratecompare-form [name='TrunkID']").val(Trunk);
                $("#add-edit-ratecompare-form [name='Effective']").val(Effective);
                $("#add-edit-ratecompare-form [name='SelectedEffectiveDate']").val(SelectedEffectiveDate);
                $("#add-edit-ratecompare-form [name='Action']").val("edit");

                var edit_title = 'Edit ' + cur_obj.find("input[name='ColumnName']").val().replace("<br>",' - ') ;

                $('#add-edit-modal-ratecompare h4').html(edit_title);
                $('#add-edit-modal-ratecompare').modal('show');

                if($("#add-edit-ratecompare-form [name='GroupBy']").val() == 'description' ){
                    $('#add-edit-modal-ratecompare .hide_if_groupby_description').addClass("hidden");
                }else {
                    $('#add-edit-modal-ratecompare .hide_if_groupby_description').removeClass("hidden");
                }

            });

            $('#add-edit-ratecompare-form').submit(function(e){
                e.preventDefault();

                var submit_url = baseurl + '/rate_compare/rate_update';

                var formData = new FormData($('#add-edit-ratecompare-form')[0]);
                submit_ajax_withfile(submit_url,formData,false,false);

            });
        });
    </script>
    <style>
        .dataTables_filter label{
            display:none !important;
        }
        .dataTables_wrapper .export-data {
            right: 30px !important;

        }
        .dataTable thead th , .dataTable  tbody td:first-child {
            background: #d7ef87 !important;
            font-weight: bold;
            color: #000 !important;
        }
        .dataTable input.margin.form-control {
            width: 70px;
        }
    </style>

    <div class="modal fade" id="add-edit-modal-ratecompare">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-edit-ratecompare-form" method="post">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Rate</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group hide_if_groupby_description">
                                    <label for="field-5" class="control-label">Code</label>
                                    <input type="text" readonly="readonly" id="Code" name="Code" class="form-control" id="field-5" placeholder="">
                                </div>
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Description</label>
                                    <input type="text" id="NewDescription" name="NewDescription" class="form-control" id="field-5" placeholder="">
                                </div>
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Rate</label>
                                    <input type="text" id="Rate" name="Rate" class="form-control"  data-mask="fdecimal" data-min="1" maxlength ="8" id="field-5" placeholder="">
                                </div>
                                <div class="form-group hide_if_groupby_description">
                                    <label for="field-5" class="control-label">Effective Date</label>
                                    <input type="text" readonly="readonly" id="EffectiveDate" name="EffectiveDate" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="GroupBy" value="" >
                        <input type="hidden" name="Type"  value="">
                        <input type="hidden" name="TypeID"  value="">
                        <input type="hidden" name="TrunkID"  value="">
                        <input type="hidden" name="Effective"  value="">
                        <input type="hidden" name="Description"  value="">
                        <input type="hidden" name="SelectedEffectiveDate"  value="">
                        <input type="hidden" name="Action"  value="">
                        <button type="submit" id="ratecompare-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop
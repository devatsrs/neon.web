@extends('layout.main')
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
    <li>

        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li class="active">
        <strong>LCR</strong>
    </li>
</ol>
<h3>LCR</h3>

<br>
<div class="row">
    <div class="col-md-12">
        <form role="form" id="lcr-search-form" method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Search LCR
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">

                        <label for="field-1" class="col-sm-1 control-label">Code</label>
                        <div class="col-sm-2">
                            <input type="text" name="Code" class="form-control" id="field-1" placeholder="" value="" />
                        </div>

                        <label for="field-1" class="col-sm-1 control-label">Description</label>
                        <div class="col-sm-2">
                            <input type="text" name="Description" class="form-control" id="field-1" placeholder="" value="" />
                        </div>

                        <label for="field-1" class="col-sm-1 control-label">Trunk</label>
                        <div class="col-sm-2">
                           {{ Form::select('Trunk', $trunks, $trunk_keys, array("class"=>"select2")) }}

                        </div>
                        <label for="field-1" class="col-sm-1 control-label">CodeDeck</label>
                        <div class="col-sm-2">
                           {{ Form::select('CodeDeckId', $codedecklist, '' , array("class"=>"select2")) }}

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-1 control-label">Use Preference</label>
                        <div class="col-sm-2">
                           <div class="col-sm-1">
                               <p class="make-switch switch-small">
                                   <input id="Use_Preference" name="Use_Preference" type="checkbox" value="1">
                               </p>
                           </div>
                        </div>
                        <label for="field-1" class="col-sm-1 control-label">Currency</label>
                        <div class="col-sm-2">
                            {{Form::select('Currency', $currencies, $CurrencyID ,array("class"=>"form-control select2"))}}
                        </div>
                        <label for="field-1" class="col-sm-1 control-label">LCR Policy</label>
                        <div class="col-sm-2">
                            {{ Form::select('Policy', LCR::$policy, LCR::LCR_PREFIX , array("class"=>"select2")) }}
                        </div>
                        <label for="field-1" class="col-sm-1 control-label">LCR Position</label>
                        <div class="col-sm-2">
                            {{ Form::select('LCRPosition', LCR::$position, $LCRPosition , array("class"=>"select2")) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-1 control-label">Accounts</label>
                        <div class="col-sm-2">
                            {{Form::select('Accounts[]', $all_accounts, array() ,array("class"=>"form-control select2",'multiple'))}}
                        </div>
                    </div>
                    <p style="text-align: right;">
                        <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="glyphicon glyphicon-circle-arrow-up"></i>
                            Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>

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


<script type="text/javascript">
    jQuery(document).ready(function($) {
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


            var Code, Description, Currency,CodeDeck,Use_Preference, Policy,LCRPosition,aoColumns,aoColumnDefs,accounts;
            Code = $("#lcr-search-form input[name='Code']").val();
            Description = $("#lcr-search-form input[name='Description']").val();
            Currency = $("#lcr-search-form select[name='Currency']").val();
            Trunk = $("#lcr-search-form select[name='Trunk']").val();
            CodeDeck = $("#lcr-search-form select[name='CodeDeckId']").val();
            Policy = $("#lcr-search-form select[name='Policy']").val();
            Use_Preference = $("#lcr-search-form [name='Use_Preference']").prop("checked");
            LCRPosition = $("#lcr-search-form select[name='LCRPosition']").val();
            Accounts = $("#lcr-search-form select[name='Accounts[]']").val();
            if(LCRPosition=='5'){
                setTimeout(function(){
                    $('#dt_company6').addClass("hidden");
                    $('#dt_company7').addClass("hidden");
                    $('#dt_company8').addClass("hidden");
                    $('#dt_company9').addClass("hidden");
                    $('#dt_company10').addClass("hidden");
                },10);
                 aoColumns = [
                     {}, //1 Destination
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
                    {}, //1 Destination
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
                    aoData.push({"name": "Code", "value": Code},{"name": "Description", "value": Description},{"name": "LCRPosition", "value": LCRPosition},{"name": "Accounts", "value": Accounts},  {"name": "Currency", "value": Currency}, {"name": "Trunk", "value": Trunk},{"name": "CodeDeck", "value": CodeDeck},{"name": "Use_Preference", "value": Use_Preference},{"name":"Policy","value":Policy});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name": "Code", "value": Code},{"name": "Description", "value": Description},{"name": "LCRPosition", "value": LCRPosition},{"name": "Accounts", "value": Accounts},  {"name": "Currency", "value": Currency}, {"name": "Trunk", "value": Trunk},{"name": "CodeDeck", "value": CodeDeck},{"name": "Use_Preference", "value": Use_Preference},{"name":"Policy","value":Policy},{"name":"Export","value":1});
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
    });
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
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

</style>
@stop
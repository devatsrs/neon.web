@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('/rategenerators')}}">Rate Generator</a>
        </li>

        <li class="active">
            <strong>Create Rate Generator</strong>
        </li>
    </ol>
    <h3>Create Rate Generator</h3>
    <form role="form" id="rategenerator-from" method="post" action="{{URL::to('/rategenerators/store')}}" class="form-horizontal form-groups-bordered">
        <div class="float-right">
            <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..." id="submitBtn">
                <i class="entypo-floppy"></i>
                Save
            </button>

            <a href="{{URL::to('/rategenerators')}}" class="btn btn-danger btn-sm btn-icon icon-left">
                <i class="entypo-cancel"></i>
                Close
            </a>
        </div>
        <br/>

        <div class="row">
            <div class="panel-body">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Rate Generator Rule Information
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Type</label>
                            <div class="col-sm-4">
                                {{Form::select('SelectType',$AllTypes,'',array("class"=>"form-control select2 small"))}}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Name</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="RateGeneratorName" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{Input::old('RateGeneratorName')}}" />
                            </div>
                        </div>

                        <div class="form-group" id="rate-ostion-trunk-div">
                            <label for="field-1" class="col-sm-2 control-label">Policy</label>
                            <div class="col-sm-4">
                                {{ Form::select('Policy', LCR::$policy, null , array("class"=>"select2")) }}
                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Trunk</label>
                            <div class="col-sm-4">
                                {{ Form::select('TrunkID', $trunks, null , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="form-group" id="group-preference-div">

                            <label for="field-1" class="col-sm-2 control-label" id="group-by-lbl">Group By</label>
                            <div class="col-sm-4" id="group-by-select-div">
                                {{ Form::select('GroupBy', array('Code'=>'Code','Desc'=>'Description'), 'Code' , array("class"=>"select2")) }}
                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Use Preference</label>
                            <div class="col-sm-1">
                                <div class="make-switch switch-small">
                                    {{Form::checkbox('UsePreference', 1, '');}}
                                </div>
                            </div>

                            <div id="rate-aveg-div">
                                <label for="field-1" class="col-sm-1 control-label">Use Average</label>
                                <div class="col-sm-2">
                                    <div class="make-switch switch-small">
                                        {{Form::checkbox('UseAverage', 1,  '' );}}
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="form-group NonDID-Div">
                            <label for="field-1" class="col-sm-2 control-label">If calculated rate is less then</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="LessThenRate" value="" />

                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Change rate to</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="ChargeRate" value="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Currency</label>
                            <div class="col-sm-4">
                                {{Form::SelectControl('currency')}}
                            </div>

                            <label class="col-sm-2 control-label">Rate Position</label>
                            <div class="col-sm-1">
                                <input type="text" class="form-control" name="RatePosition" data-validate="required" data-message-required="." id="field-1" placeholder="" value="" />
                            </div>
                            <div id="percentageRate">
                                <label class="col-sm-1 control-label">Percentage </label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control popover-primary" rows="1" id="percentageRate" name="percentageRate" value="" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Use vendor position mention in Rate Position unless vendor selected position is more then N% more costly than the previous vendor" data-original-title="Percentage" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group NonDID-Div">
                            <label for="field-1" class="col-sm-2 control-label">CodeDeck</label>
                            <div class="col-sm-4">
                                {{ Form::select('codedeckid', $codedecklist,  null , array("class"=>"select2")) }}

                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Timezones</label>
                            <div class="col-sm-4">
                                {{ Form::select('Timezones[]', $Timezones, array_keys($Timezones) , array("class"=>"select2 multiselect", "multiple"=>"multiple")) }}
                            </div>
                        </div>
                        <div class="form-group NonDID-Div">
                            <label class="col-sm-2 control-label">Merge Rate By Timezones</label>
                            <div class="col-sm-4">
                                <div class="make-switch switch-small">
                                    {{Form::checkbox('IsMerge', 1,  null, array('id' => 'IsMerge') );}}
                                </div>
                            </div>
                            <label class="col-sm-2 control-label IsMerge">Take Price</label>
                            <div class="col-sm-4 IsMerge">
                                {{ Form::select('TakePrice', array(RateGenerator::HIGHEST_PRICE=>'Highest Price',RateGenerator::LOWEST_PRICE=>'Lowest Price'), null , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="form-group NonDID-Div">
                            <label class="col-sm-2 control-label IsMerge">Merge Into</label>
                            <div class="col-sm-4 IsMerge">
                                {{ Form::select('MergeInto', $Timezones, null , array("class"=>"select2")) }}
                            </div>

                            <div id="hide-components">
                                <label for="field-1" class="col-sm-2 control-label">Components</label>
                                <div class="col-sm-4">
                                    {{ Form::select('AllComponent[]', RateGenerator::$Component, null, array("class"=>"select2 multiselect" , "multiple"=>"multiple", "id"=>"AllComponent" )) }}
                                </div>

                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="field-1" class="col-sm-2 control-label">Country</label>
                            <div class="col-sm-4">
                                {{ Form::select('CountryID', $country, '', array("class"=>"select2")) }}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Access Type</label>
                            <div class="col-sm-4">
                                {{ Form::select('AccessType', $AccessType, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="field-1" class="col-sm-2 control-label">Prefix</label>
                            <div class="col-sm-4">
                                {{ Form::select('Prefix', $Prefix, '', array("class"=>"select2")) }}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">City/Tariff</label>
                            <div class="col-sm-4">
                                {{ Form::select('CityTariff', $CityTariff, null, array("class"=>"select2")) }}
                            </div>
                        </div>
                        
                        <div class="form-group Package-Div" style="display:none;">
                            <label for="field-1" class="col-sm-2 control-label">Package</label>
                            <div class="col-sm-10">
                                {{ Form::select('PackageID', $Package, null, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="DateFrom" class="col-sm-2 control-label">Date From</label>
                            <div class="col-sm-4">
                                <input id="DateFrom" type="text" name="DateFrom" class="form-control datepicker" data-date-format="yyyy-mm-dd" placeholder="YYYY-MM-DD"/>
                            </div>
                            <label for="DateTo" class="col-sm-2 control-label">Date To</label>
                            <div class="col-sm-4">
                                <input id="DateTo" type="text" name="DateTo" class="form-control datepicker" data-date-format="yyyy-mm-dd" placeholder="YYYY-MM-DD"/>
                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="Calls" class="col-sm-2 control-label">Calls</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" class="form-control" id="Calls" name="Calls"/>
                            </div>
                            <label for="Minutes" class="col-sm-2 control-label">Minutes</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" class="form-control" id="Minutes" name="Minutes"/>
                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="TimeOfDay" class="col-sm-2 control-label">Time of Day</label>
                            <div class="col-sm-4">
                                {{ Form::select('TimezonesID', $Timezones, null, array("class"=>"select2")) }}
                            </div>
                            <label for="TimeOfDayPercentage" class="col-sm-2 control-label">Time of Day %</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" class="form-control" id="TimeOfDayPercentage" name="TimezonesPercentage"/>
                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="Origination" class="col-sm-2 control-label">Origination</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="Origination" name="Origination"/>
                            </div>
                            <label for="OriginationPercentage" class="col-sm-2 control-label">Origination %</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" class="form-control" id="OriginationPercentage" name="OriginationPercentage"/>
                            </div>
                        </div>
                        <div class="form-group" id="DIDCategoryDiv">
                            <label for="field-1" class="col-sm-2 control-label">Category</label>
                            <div class="col-sm-4">
                                {{ Form::select('Category', $Categories,null, array("class"=>"select2")) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-primary" data-collapsed="0" id="Merge-components">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Merge Components
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="" style="overflow: auto;">
                            <br/>
                            <input type="hidden" id="getIDs" name="getIDs" value=""/>
                            <table id="servicetableSubBox" class="table table-bordered datatable">
                                <thead>
                                <tr>
                                    <th style="width:200px !important;">Component</th>
                                    <th style="width:200px !important;">Origination</th>
                                    <th style="width:200px !important;">Time of Day</th>
                                    <th style="width:200px !important;">Action</th>
                                    <th style="width:200px !important;">Merge To</th>
                                    <th style="width:200px !important;">To Origination</th>
                                    <th style="width:200px !important;">To Time of Day</th>
                                    <th style="width:250px !important;">From Country</th>
                                    <th style="width:250px !important;">From Type</th>
                                    <th style="width:250px !important;">From Prefix</th>
                                    <th style="width:250px !important;">From City/Tariff</th>
                                    <th style="width:250px !important;">To Country</th>
                                    <th style="width:250px !important;">To Type</th>
                                    <th style="width:250px !important;">To Prefix</th>
                                    <th style="width:250px !important;">To City/Tariff</th>
                                    <th style="width:100px !important;">Add</th>
                                </tr>
                                </thead>
                                <tbody id="tbody">
                                <tr id="selectedRow-1">
                                    <td id="testValues">
                                        {{ Form::select('Component-1[]', RateGenerator::$Component, null, array("class"=>"select2 selected-Components" ,'multiple', "id"=>"Component-1")) }}
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="Origination-1"/>
                                    </td>
                                    <td>
                                        {{ Form::select('TimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('Action-1',  RateGenerator::$Action, RateGenerator::$Action, array("class"=>"select2")) }}

                                    </td>
                                    <td>
                                        {{ Form::select('MergeTo-1', RateGenerator::$Component,  null , array("class"=>"select2" , "id"=>"MergeTo-1")) }}

                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="ToOrigination-1"/>
                                    </td>
                                    <td>
                                        {{ Form::select('ToTimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('FCountry-1', $country, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('FAccessType-1', $AccessType, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('FPrefix-1', $Prefix, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('FCity_Tariff-1', $CityTariff, null, array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('TCountry-1', $country, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('TAccessType-1', $AccessType, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('TPrefix-1', $Prefix, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('TCity_Tariff-1', $CityTariff, null, array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        <button type="button" onclick="createCloneRow('servicetableSubBox','getIDs')" id="Service-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
                                            <i></i>
                                            +
                                        </button>
                                        <a onclick="deleteRow(this.id, 'servicetableSubBox','getIDs')" id="merge-0" class="btn btn-danger btn-sm hidden" data-loading-text="Loading..." >
                                            <i></i>
                                            -
                                        </a>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="panel panel-primary DID-Div" data-collapsed="0" id="Calculated-Rate">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Calculated Rate
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="" style=" overflow: auto;">
                            <br/>
                            <input type="hidden" id="getRateIDs" name="getRateIDs" value=""/>
                            <table id="ratetableSubBox" class="table table-bordered datatable">
                                <thead>
                                <tr>
                                    <th style="width:200px !important;">Component</th>
                                    <th style="width:200px !important;">Origination</th>
                                    <th style="width:200px !important;">Time of Day</th>
                                    <th style="width:250px !important;">Country</th>
                                    <th style="width:250px !important;">Type</th>
                                    <th style="width:250px !important;">Prefix</th>
                                    <th style="width:250px !important;">City/Tariff</th>
                                    <th style="width:200px !important;">Calculated Rate</th>
                                    <th style="width:200px !important;">Change Rate To</th>
                                    <th style="width:100px !important;">Add</th>
                                </tr>
                                </thead>
                                <tbody id="ratetbody">
                                <tr id="selectedRateRow-1">
                                    <td>
                                        {{ Form::select('RateComponent-1[]', RateGenerator::$Component, '', array("class"=>"select2 selected-Components" ,'multiple', "id"=>"RateComponent-1")) }}
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="RateOrigination-1"/>
                                    </td>
                                    <td>
                                        {{ Form::select('RateTimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('Country1-1', $country, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('AccessType1-1', $AccessType, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('Prefix1-1', $Prefix, '', array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        {{ Form::select('City_Tariff1-1', $CityTariff, null, array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        <input type="number" min="0" class="form-control" name="RateLessThen-1"/>
                                    </td>
                                    <td>
                                        <input type="number" min="0" class="form-control" name="ChangeRateTo-1"/>
                                    </td>
                                    <td>
                                        <button type="button" onclick="createCloneRow('ratetableSubBox','getRateIDs')" id="rate-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
                                            <i></i>
                                            +
                                        </button>
                                        <a onclick="deleteRow(this.id,'ratetableSubBox','getRateIDs')" id="rateCal-0" class="btn btn-danger btn-sm hidden" data-loading-text="Loading..." >
                                            <i></i>
                                            -
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <style>
        .IsMerge {
            display: none;
        }
        #servicetableSubBox {
            width:3000px;
            overflow-x: auto;
        }
        #ratetableSubBox{
            width:1800px;
            overflow-x: auto;
        }
    </style>
    <script type="text/javascript">

        function ajax_form_success(response){
            if(typeof response.redirect != 'undefined' && response.redirect != ''){
                window.location = response.redirect;
            }
        }
        /*jQuery(document).ready(function($) {
         $(".save.btn").click(function(ev) {
         $("#rategenerator-from").submit();
         });
         });*/

        function getIds(tblID, idInp){
            $('#'+idInp).val("");
            $('#' + tblID + ' tbody tr').each(function() {

                var row = tblID == "servicetableSubBox" ? "selectedRow-0" : "selectedRateRow-0";
                var id = 0;
                if(this.id != row)
                    id = getNumber(this.id);

                var getIDString =  $('#'+idInp).val();
                getIDString = getIDString + id + ',';
                $('#'+idInp).val(getIDString);

            });
        }


        $(document).ready(function() {

            if(performance.navigation.type == 2)
            {
                $('#getIDs').val('');
                $('#getRateIDs').val('');
                $("#hide-components").hide();
            }

            $("#DateFrom").datepicker({
                todayBtn:  1,
                autoclose: true
            }).on('changeDate', function (selected) {
                var minDate = new Date(selected.date.valueOf());
                var endDate = $('#DateTo');
                endDate.datepicker('setStartDate', minDate);
                if(endDate.val() && new Date(endDate.val()) != undefined) {
                    if(minDate > new Date(endDate.val()))
                        endDate.datepicker("setDate", minDate)
                }
            });

            $("#DateTo").datepicker({autoclose: true})
                    .on('changeDate', function (selected) {
                        var maxDate = new Date(selected.date.valueOf());
                        //$('#StartDate').datepicker('setEndDate', maxDate);
                    });

            if(new Date($('#DateFrom').val()) != undefined){
                $("#DateTo").datepicker('setStartDate', new Date($('#DateFrom').val()))
            }


            $(window).load(function() {
                getIds("servicetableSubBox", "getIDs");
                getIds("ratetableSubBox", "getRateIDs");
            });

            $('#servicetableSubBox tbody tr').each(function() {

                var id = getNumber(this.id);
                var getIDString =  $('#getIDs').val();
                getIDString = getIDString + id + ',';
                $('#getIDs').val(getIDString);

                selectAllComponents = $("#AllComponent").val();
                selectAllComponents = String(selectAllComponents);
                var ComponentsArray = selectAllComponents.split(',');

                var component = $('#Component-' + id).val();
                component = String(component);
                var getCompArray = component.split(',');

                for( var i = 0; i < ComponentsArray.length; i++){
                    if ( ComponentsArray[i] == getCompArray[i]) {
                        ComponentsArray.splice(i, 1);
                    }
                }

                var i;
                for (i = 0; i < ComponentsArray.length; ++i) {
                    var data = {
                        id: ComponentsArray[i],
                        text: ComponentsArray[i]
                    };

                    if (typeof data.id != 'undefined' && data.id != 'null') {

                        var newOption = new Option(data.text, data.id, false, false);
                        var newOption2 = new Option(data.text, data.id, false, false);
                        $('#Component-' + id).append(newOption).trigger('change');
                        $('#MergeTo-' + id).append(newOption2).trigger('change');
                    }
                }

            });

        });

        var TypeValue = $("#rategenerator-from [name='SelectType']").val();
        if(TypeValue == 2){
            $("#rate-ostion-trunk-div").hide();
            $("#rate-aveg-div").hide();
            $("#group-preference-div").hide();
            $("#DIDCategoryDiv").show();
            $("#Merge-components").show();
            $(".DID-Div").show();
            $(".NonDID-Div").hide();$(".Package-Div").hide();

        }else if(TypeValue == 1){
            $("#rate-ostion-trunk-div").show();
            $("#rate-aveg-div").show();
            $("#group-preference-div").show();
            $("#Merge-components").hide();
            $("#DIDCategoryDiv").hide();
            $("#hide-components").hide();
            $(".DID-Div").hide();
            $(".NonDID-Div").show();$(".Package-Div").hide();
        }else if(TypeValue == 3){
            
            $(".DID-Div").hide();
            $(".NonDID-Div").hide();
            $(".Package-Div").show();
            $("#rate-ostion-trunk-div").hide();
            $("#rate-aveg-div").hide();
            $("#group-preference-div").hide();
            $("#DIDCategoryDiv").hide();
            $("#Merge-components").show();
            
        } else {
            $(".DID-Div").hide();
            $(".NonDID-Div").show();$(".Package-Div").hide();
        }

        $("#rategenerator-from [name='SelectType']").on('change', function() {

            var TypeValue = $(this).val();

            if(TypeValue == 2){
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();
                $("#Merge-components").show();
                $("#DIDCategoryDiv").show();
                $("#hide-components").show();
                $(".DID-Div").show();
                $(".NonDID-Div").hide();$(".Package-Div").hide();


            }else if(TypeValue == 1){
                $("#rate-ostion-trunk-div").show();
                $("#rate-aveg-div").show();
                $("#group-preference-div").show();
                $("#Merge-components").hide();
                $("#hide-components").hide();
                $("#DIDCategoryDiv").hide();
                $(".DID-Div").hide();
                $(".NonDID-Div").show();$(".Package-Div").hide();
            }else if(TypeValue == 3){
            
                $(".DID-Div").hide();
                $(".NonDID-Div").hide();
                $(".Package-Div").show();
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();
                $("#DIDCategoryDiv").hide();
                $("#Merge-components").show();
            
            } else {
                $(".DID-Div").hide();$(".Package-Div").hide();
                $(".NonDID-Div").show();
            }

        });
        $('#IsMerge').on('change', function(e, s) {
            if($('#IsMerge').is(':checked')) {
                $('.IsMerge').show();
            } else {
                $('.IsMerge').hide();
            }
        });


        $( "#AllComponent" ).on('change', function() {


            var selectAllComponents = $("#AllComponent").val();
            selectAllComponents = String(selectAllComponents);
            var ComponentsArray = selectAllComponents.split(',');
            var addCostComponents = new Array();
            var j= 0;


            $('#servicetableSubBox tbody tr').each(function() {

                if(this.id == 'selectedRow-0')
                    var id = 0;
                else
                    var id = getNumber(this.id);

                var component = $('#Component-' + id).val();
                component = String(component);
                var getCompArray = component.split(',');

//                for( var i = 0; i < ComponentsArray.length; i++){
//                    alert(ComponentsArray[i]);
//                    if ( ComponentsArray[i] == getCompArray[i]) {
//                          ComponentsArray.splice(ComponentsArray.indexOf(i), 1);
//                    }
//                }
//                $('#MergeTo-'+id+' option').each(function() {
//                    $(this).remove();
//                });
                $('#Component-'+id+' option').each(function() {
                    $(this).remove();
                });
                $('#MergeTo-'+id+' option').each(function() {
                    $(this).remove();
                });
                var i;
                for (i = 0; i < ComponentsArray.length; ++i) {
                    var data = {
                        id: ComponentsArray[i],
                        text: ComponentsArray[i]
                    };

                    if( typeof data.id != 'undefined' && data.id  != 'null'){

                        var newOption = new Option(data.text, data.id, false, false);
                        var newOption2 = new Option(data.text, data.id, false, false);

                        $('#Component-'+id).append(newOption).trigger('change');
                        $('#MergeTo-'+id).append(newOption2).trigger('change');
                    }
                }

                $('#Component-'+id).select2().select2('val', getCompArray);

            });

        });


        $("#rategenerator-from [name='SelectType']").on('change', function() {

            var TypeValue = $(this).val();
            if(TypeValue == 2){
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();

            }else if(TypeValue == 1) {
                $("#rate-ostion-trunk-div").show();
                $("#rate-aveg-div").show();
                $("#group-preference-div").show();

            }

        });

        function getNumber($item){
            var txt = $item;
            var numb = txt.match(/\d/g);
            numb = numb.join("");
            return numb;
        }

        function createCloneRow(tblID, idInp) {
            var $item = $('#' + tblID + ' tr:last').attr('id');
            var numb = getNumber($item);
            numb++;

            $("#" + $item).clone().appendTo('#' + tblID + ' tbody');

            var row = tblID == "servicetableSubBox" ? "selectedRow" : "selectedRateRow";

            $('#' + tblID + ' tr:last').attr('id', row + '-' + numb);
            if (tblID == "servicetableSubBox") {
                $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'Component-' + numb + '[]').attr('id', 'Component-' + numb).select2().select2('val', '');
                $('#' + tblID + ' tr:last').children('td:eq(1)').children('input').attr('name', 'Origination-' + numb).attr('id', 'Origination-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(2)').children('select').attr('name', 'TimeOfDay-' + numb).attr('id', 'TimeOfDay-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(3)').children('select').attr('name', 'Action-' + numb).attr('id', 'Action-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(4)').children('select').attr('name', 'MergeTo-' + numb).attr('id', 'MergeTo-' + numb).select2().select2('val', '');
                $('#' + tblID + ' tr:last').children('td:eq(5)').children('input').attr('name', 'ToOrigination-' + numb).attr('id', 'ToOrigination-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(6)').children('select').attr('name', 'ToTimeOfDay-' + numb).attr('id', 'ToTimeOfDay-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(7)').children('select').attr('name', 'FCountry-' + numb).attr('id', 'FCountry-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(8)').children('select').attr('name', 'FAccessType-' + numb).attr('id', 'FAccessType-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(9)').children('select').attr('name', 'FPrefix-' + numb).attr('id', 'FPrefix-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(10)').children('select').attr('name', 'FCity_Tariff-' + numb).attr('id', 'FCity_Tariff-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(11)').children('select').attr('name', 'TCountry-' + numb).attr('id', 'TCountry-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(12)').children('select').attr('name', 'TAccessType-' + numb).attr('id', 'TccessType-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(13)').children('select').attr('name', 'TPrefix-' + numb).attr('id', 'TPrefix-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(14)').children('select').attr('name', 'TCity_Tariff-' + numb).attr('id', 'TCity_Tariff-' + numb).select2();
            } else {
                $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'RateComponent-' + numb + '[]').attr('id', 'RateComponent-' + numb).select2().select2('val', '');
                $('#' + tblID + ' tr:last').children('td:eq(1)').children('input').attr('name', 'RateOrigination-' + numb).attr('id', 'RateOrigination-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(2)').children('select').attr('name', 'RateTimeOfDay-' + numb).attr('id', 'RateTimeOfDay-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(3)').children('select').attr('name', 'Country1-' + numb).attr('id', 'Country1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(4)').children('select').attr('name', 'AccessType1-' + numb).attr('id', 'AccessType1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(5)').children('select').attr('name', 'Prefix1-' + numb).attr('id', 'Prefix1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(6)').children('select').attr('name', 'City_Tariff1-' + numb).attr('id', 'City_Tariff1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(7)').children('input').attr('name', 'RateLessThen-' + numb).attr('id', 'RateLessThen-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(8)').children('input').attr('name', 'ChangeRateTo-' + numb).attr('id', 'ChangeRateTo-' + numb).val('');
            }
            if ($('#' + idInp).val() == '') {
                $('#' + idInp).val(numb + ',');
            } else {
                var getIDString = $('#' + idInp).val();
                getIDString = getIDString + numb + ',';
                $('#' + idInp).val(getIDString);
            }

            /* if(tblID == "servicetableSubBox") {
             $('#Component-' + numb + ' option').each(function () {
             $(this).remove();
             });
             } else {
             $('#RateComponent-' + numb + ' option').each(function () {
             $(this).remove();
             });
             }*/
            if (tblID == "servicetableSubBox") {
                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(4)').children('a').attr('id', "merge-" + numb);
            } else {
                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(5)').children('a').attr('id', "rateCal-" + numb);
            }
            alert(tblID);


            $('#' + tblID + ' tr:last').children('td:eq(0)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(1)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(2)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(3)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(4)').find('div:first').remove();

            if (tblID == "servicetableSubBox") {
                $('#' + tblID + ' tr:last').children('td:eq(5)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(6)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(7)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(8)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(9)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(10)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(11)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(12)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(13)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(14)').find('div:first').remove();

                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(15)').find('a').removeClass('hidden');
            } else {
                $('#' + tblID + ' tr:last').children('td:eq(5)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(6)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(7)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(8)').find('div:first').remove();

                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(9)').find('a').removeClass('hidden');
            }
        }
        function deleteRow(id, tblID, idInp)
        {
            if(confirm("Are You Sure?")) {
                var selectedSubscription = $('#'+idInp).val();
                var removeValue = id + ",";
                var removalueIndex = selectedSubscription.indexOf(removeValue);
                var firstValue = selectedSubscription.substr(0, removalueIndex);
                var lastValue = selectedSubscription.substr(removalueIndex + removeValue.length, selectedSubscription.length);
                var selectedSubscription = firstValue + lastValue;
                if (selectedSubscription.charAt(0) == ',') {
                    selectedSubscription = selectedSubscription.substr(1, selectedSubscription.length)
                }
                $('#'+idInp).val(selectedSubscription);


                var rowCount = $("#" + tblID + " > tbody").children().length;
                if (rowCount > 1) {
                    $("#" + id).closest("tr").remove();
                    getIds(tblID, idInp);

                } else {

                    toastr.error("You cannot delete. At least one component is required.", "Error", toastr_opts);
                }
                return false;
            }
        }


    </script>
    @include('currencies.currencymodal')
    @include('trunk.trunkmodal')
    @include('includes.ajax_submit_script', array('formID'=>'rategenerator-from' , 'url' => ('/rategenerators/store'),'update_url'=>'rategenerators/{id}/update'))
@stop         

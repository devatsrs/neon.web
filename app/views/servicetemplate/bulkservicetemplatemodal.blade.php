
<script>

    $(document).ready(function ($) {
        countSelectedItems = 1;

        var selected_company, data, url;
        selected_currency = $("#serviceTemplateCurreny").val();

        data = {company: selected_company};
        resetFormFields();
        ShowTariffOnSelectedCategoryBulkAction();
        // This function exist in service template moal class
        // Getting all the values from the ajax for Select boxes

        // alert(selected_currency);
        if (selected_currency == '') {
            selected_currency = "NAN";
        }
//        url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + selected_currency + "&selectedData=service";
//        // alert("url :" + url);
//        $.post(url, function (data, status) {
//            //  var res = data.split('/>');
//            //alert(data);
//            document.getElementById("ServiceIdBulkAction").innerHTML = "" + data;
//            var ServiceId = $("div.hiddenRowData").find("input[name='ServiceIdBulkAction']").val();
//            // alert("ServiceId" + ServiceId);
//            if (ServiceId != '') {
//                $("#add-action-bulk-form [name='ServiceIdBulkAction']").select2().select2('val', ServiceId);
//
//            }else {
//                $("#add-action-bulk-form [name='ServiceIdBulkAction']").select2().select2('val', '');
//            }
//            // console.log(document.getElementById("TemplateDataTabServiceId").innerHTML);
//            // alert(document.getElementById("TemplateDataTabServiceId").innerHTML);
//
//            // $("#serviceBasedOnCurreny").html(data);
//        }, 'html');
        //if (selectData) {
        // editServiceId = $("div.hiddenRowData").find("input[name='ServiceId']").val();
        //  alert('editServiceId' + editServiceId);
        //  alert(document.getElementById('ServiceId'));
        //  document.getElementById('ServiceId').value = editServiceId;
        // $("#add-new-service-form [name='ServiceId']").select2().select2('val', editServiceId);
        // }
        //
        //
//        url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + selected_currency + "&selectedData=outboundPlan";
//        // alert("url :" + url);
//        $.post(url, function (data, status) {
//            // var res = data.split('/>');
//            // alert(data);
//            document.getElementById("OutboundDiscountPlanIdBulkAction").innerHTML = "" + data;
//            //var OutboundDiscountPlanID = $("div.hiddenRowData").find("input[name='OutboundDiscountPlanID']").val();
//            // alert(OutboundDiscountPlanID);
//            $("#add-action-bulk-form [name='OutboundDiscountPlanIdBulkAction']").select2().select2('val', '');
//
//            // $("#serviceBasedOnCurreny").html(data);
//        }, 'html');
//        url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + selected_currency + "&selectedData=inboundPlan";
//        // alert("url :" + url);
//        $.post(url, function (data, status) {
//            // var res = data.split('/>');
//            document.getElementById("InboundDiscountPlanIdBulkAction").innerHTML = "" + data;
//            //var InboundDiscountPlanID = $("div.hiddenRowData").find("input[name='InboundDiscountPlanID']").val();
//            $("#add-action-bulk-form [name='InboundDiscountPlanIdBulkAction']").select2().select2('val', '');
//
//            // $("#serviceBasedOnCurreny").html(data);
//        }, 'html');
//
        url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + selected_currency + "&selectedData=outboundTariff";
        // alert("url :" + url);
        $.post(url, function (data, status) {
            // var res = data.split('/>');
            document.getElementById("OutboundRateTableId").innerHTML = "" + data;
            // var OutboundTariffId = $("div.hiddenRowData").find("input[name='OutboundTariffId']").val();
            $("#add-action-bulk-form [name='OutboundRateTableIdBulkAction']").select2().select2('val', '');

            // $("#serviceBasedOnCurreny").html(data);
        }, 'html');



        url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + selected_currency + "&selectedData=templateSubscriptionList";
        // alert("url :" + url);
        $.post(url, function (data, status) {
            // var res = data.split('/>');
            //  alert(data);
            document.getElementById("templateSubscriptionList").innerHTML = "" + data;
            // $("#serviceBasedOnCurreny").html(data);
        }, 'html');

        var table = document.getElementById ("table-4");
        $("#add-new-BulkAction-modal-service input:checkbox").prop("checked",false);
        $("#OutboundRateTableIdBulkAction").prop("disabled",true);
        $("#OutboundDiscountPlanIdBulkAction").prop("disabled",true);
        $("#InboundDiscountPlanIdBulkAction").prop("disabled",true);
        $("#PackageDiscountPlanIdBulkAction").prop("disabled",true);
        $("#ServiceIdBulkAction").prop("disabled",true);

        $("#add-new-BulkAction-modal-service input:checkbox[name='Service']").change(function() {

            if ($(this).prop('checked') == false)
            {
                $("#ServiceIdBulkAction").prop('disabled', 'disabled');
                countSelectedItems++;
            }else{
                $("#ServiceIdBulkAction").prop('disabled', false);
                $(this).val(1);
                countSelectedItems--;
            }


        });


        $("#add-new-BulkAction-modal-service input:checkbox[name='OutboundTraiff']").change(function() {

            if ($(this).prop('checked') == false)
            {
                $("#OutboundRateTableIdBulkAction").prop('disabled', 'disabled');
                countSelectedItems++;
            }else {
                $("#OutboundRateTableIdBulkAction").prop('disabled', false);
                $(this).val(1);
                countSelectedItems--;
            }
        });


        $("#add-new-BulkAction-modal-service input:checkbox[name='OutboundDiscountPlan']").change(function() {

            if ($(this).prop('checked') == false)
            {
                $("#OutboundDiscountPlanIdBulkAction").prop('disabled', 'disabled');
                countSelectedItems++;
            }else {
                $("#OutboundDiscountPlanIdBulkAction").prop('disabled', false);
                $(this).val(1);
                countSelectedItems--;
            }
        });

        $("#add-new-BulkAction-modal-service input:checkbox[name='InboundDiscountPlan']").change(function() {
            if ($(this).prop('checked') == false)
            {
                $("#InboundDiscountPlanIdBulkAction").prop('disabled', 'disabled');
                countSelectedItems++;
            }else {
                $("#InboundDiscountPlanIdBulkAction").prop('disabled', false);
                $(this).val(1);
                countSelectedItems--;
            }
        });
        $("#add-new-BulkAction-modal-service input:checkbox[name='PackageDiscountPlan']").change(function() {
            if ($(this).prop('checked') == false)
            {
                $("#PackageDiscountPlanIdBulkAction").prop('disabled', 'disabled');
                countSelectedItems++;
            }else {
                $("#PackageDiscountPlanIdBulkAction").prop('disabled', false);
                $(this).val(1);
                countSelectedItems--;
            }
        });


        $("#add-new-BulkAction-modal-service input:checkbox[name='InboundTariff']").change(function() {


            if($("#InboundTariff").val() == "")
            {
                $("#InboundTariff").val(1);

                $("#DidCategoryIDBulkAction").prop("disabled",false);
                $("#DidCategoryTariffIDBulkAction").prop("disabled",false);

            }else{
                $("#InboundTariff").val("");

                $("#DidCategoryIDBulkAction").prop("disabled","disabled");
                $("#DidCategoryTariffIDBulkAction").prop("disabled","disabled");

            }


        });
    });

    function ShowTariffOnSelectedCategoryBulkAction()
    {
        var selected_company, data, url;

        selected_currency = $("#CurrencyIdBulkAction").val();
        selected_didCategory = $("#DidCategoryIDBulkAction").val();
        DidCategoryIndexValue = document.getElementById("DidCategoryIDBulkAction").selectedIndex;


        if (selected_currency == '') {
            selected_currency = "NAN";
        }
        data = {company: selected_company};

        url = baseurl + "/servicesTemplate/selectDataOnCurrency" +
                "?selectedCurrency=" + selected_currency + "&selectedData=DidCategoryID&selected_didCategory="+selected_didCategory;

        $.post(url, function (data, status) {
            //  var res = data.split('/>');
            document.getElementById("DidCategoryTariffIDBulkAction").innerHTML = "" + data;
            DidCategoryTariffIDBulkAction = document.getElementById("DidCategoryTariffIDBulkAction").innerHTML;
            // $("#serviceBasedOnCurreny").html(data);
        }, 'html');
    }
    function RemoveCategoryTariffRowInTableBulkAction(rowID) {
        var removeValue = rowID.substr("CategoryTariffRowIDBulkAction".length, rowID.length) + ",";
        // alert(removeValue);
        var selectedselectedcategotyTariff = document.getElementById("selectedcategotyTariffBulkAction").value;
        var removalueIndex = selectedselectedcategotyTariff.indexOf(removeValue);
        var firstValue = selectedselectedcategotyTariff.substr(0, removalueIndex);
        var lastValue = selectedselectedcategotyTariff.substr(removalueIndex + removeValue.length, selectedSubscription.length);
        selectedselectedcategotyTariff = firstValue + lastValue;
        // alert("selectedselectedcategotyTariff in remove:" + selectedselectedcategotyTariff);
        document.getElementById("selectedcategotyTariffBulkAction").value = selectedselectedcategotyTariff;
        document.getElementById(rowID).innerHTML = "";
        document.getElementById(rowID).setAttribute("id", "");
    }

    function AddCategoryTariffInTableBulkAction() {
        //DidCategoryID DidCategoryTariffID
        var SelectedDidCategoryID = $("#DidCategoryIDBulkAction option:selected");
        var DidCategoryIDText = SelectedDidCategoryID.text();
        var DidCategoryID = SelectedDidCategoryID.val();
        var SelectedDidCategoryTariffID = $("#DidCategoryTariffIDBulkAction option:selected");
        var DidCategoryTariffIDText = SelectedDidCategoryTariffID.text();
        var DidCategoryTariffID = SelectedDidCategoryTariffID.val();
        //alert(SelectedDidCategoryID + ":" + SelectedDidCategoryTariffID.val());
        if (typeof DidCategoryID == 'undefined' || DidCategoryID == '') {
            DidCategoryID = "0";
            DidCategoryIDText= "";
        }

        if (typeof DidCategoryID != 'undefined' && DidCategoryID != '' && typeof DidCategoryTariffID != 'undefined' && DidCategoryTariffID != '') {
            // alert(document.getElementById("SubscriptionIDListBody"));
            // alert(document.getElementById("SubscriptionIDListBody").innerHTML);
            var CategoryTariffValue = DidCategoryID + '-' + DidCategoryTariffID;
            var CategoryTariffSearchValue = DidCategoryID + '-';

            var setValue = "setValue('CategoryTariffIDBulkAction[" + rowCategoryTariffHtmlIndex + "]','" + CategoryTariffValue + "');";

            var idName = "CategoryTariffIDBulkAction[" + rowCategoryTariffHtmlIndex + "]";

            var idNameRow = "CategoryTariffRowIDBulkAction" + (CategoryTariffValue) + "";

            var colValue = DidCategoryID;
            var selectedselectedcategotyTariff = document.getElementById("selectedcategotyTariffBulkAction").value;
            // alert("selectedselectedcategotyTariff in add:" + selectedselectedcategotyTariff);

            if (selectedselectedcategotyTariff.indexOf(CategoryTariffSearchValue) == -1) {

                var rowCategoryTariffHtml =
                        '<tr class="draggable" + ' +
                        'id="' + idNameRow + '\" ' +
                        'name="' + idNameRow + '\" ' +
                        '>' +
                        '<td>' + DidCategoryIDText + '</td>' +
                        '<td>' + DidCategoryTariffIDText + '</td>' +
                        '<td>' +
                        '<a title="Delete" onClick="RemoveCategoryTariffRowInTableBulkAction(' + "'" + idNameRow + "'" + ');" class="delete-service2 btn btn-danger btn-sm"><i class="entypo-trash"></i></a>' +
                        '</td>' +
                        '</tr>';
                selectedselectedcategotyTariff = selectedselectedcategotyTariff + CategoryTariffValue + ",";
                document.getElementById("selectedcategotyTariffBulkAction").value = selectedselectedcategotyTariff;

                //  alert(rowCategoryTariffHtml);
                rowCategoryTariffHtmlIndex = rowCategoryTariffHtmlIndex + 1;
                document.getElementById("categoryTariffIDListBodyBulkAction").innerHTML = document.getElementById("categoryTariffIDListBodyBulkAction").innerHTML + rowCategoryTariffHtml;
                //categoryTariffIDListBody = document.getElementById("categoryTariffIDListBody").innerHTML;
            } else {
                alert("Tariff against category already added");
            }
        }else {
            alert("Please select Category and Tariff");
        }
    }
</script>



<div class="modal fade" id="add-new-BulkAction-modal-service">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="add-action-bulk-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="BulkServiceTemplateModelTitle">Bulk Action</h5>
                </div>
                <div class="modal-body">

                    <div id="TemplateDataTab">

                        <div id="ContentTemplateDataTab" class="modal-body">
                            <div class="row">
                                <input type="hidden" name="CurrencyIdBulkAction" id="CurrencyIdBulkAction" val="" />
                                <input type="hidden" name="ServiceTemplateIdBulkAction" id="ServiceTemplateIdBulkAction" val="" />

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <table width="100%">
                                            <tr>
                                                <td width="15%"><label for="field-5" class="control-label"><input type="checkbox" name="Service" value=""> Service</label></td>
                                                <td width="30%">
                                                    {{ Form::select('ServiceIdBulkAction',$servicesTemplate,array(), array("id" => "ServiceIdBulkAction", "class"=>"form-control")) }}
                                                    {{--<select  id="ServiceIdBulkAction" name="ServiceIdBulkAction" class="form-control"></select>--}}
                                                </td>
                                                <td width="5%">&nbsp;</td>
                                                <td width="15%"><label for="field-5" class="control-label"><input type="checkbox" name="OutboundTraiff" value=""> Termination Ratetable</label></td>
                                                <td width="35%">
                                                    {{ Form::select('OutboundRateTableIdBulkAction',$rateTable,array(), array("id" => "OutboundRateTableIdBulkAction", "class"=>"form-control")) }}


                                                    {{--<select id="OutboundRateTableIdBulkAction" name="OutboundRateTableIdBulkAction" class="form-control">--}}
                                                    {{--</select>--}}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <table width="100%">

                                            <tr>
                                                <td width="15%"><label for="field-5" class="control-label"><input type="checkbox" name="OutboundDiscountPlan" value=""> Termination Discount Plan</label></td>
                                                <td width="30">
                                                    {{ Form::select('OutboundDiscountPlanIdBulkAction',$DiscountPlanVOICECALL,array(), array("id" => "OutboundDiscountPlanIdBulkAction", "class"=>"form-control")) }}

                                                    {{--<select id="OutboundDiscountPlanIdBulkAction" name="OutboundDiscountPlanIdBulkAction" class="form-control"></select>--}}
                                                </td>
                                                <td width="5%">&nbsp;</td>
                                                <td width="15%"><label for="field-5" class="control-label"><input type="checkbox" name="InboundDiscountPlan" value=""> Access Discount Plan</label></td>
                                                <td width="35%">
                                                    {{ Form::select('InboundDiscountPlanIdBulkAction',$DiscountPlanDID,array(), array("id" => "InboundDiscountPlanIdBulkAction", "class"=>"form-control")) }}

                                                    {{--<select id="InboundDiscountPlanIdBulkAction" name="InboundDiscountPlanIdBulkAction" class="form-control">--}}
                                                    {{--</select>--}}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <table width="100%">
                                            <tr>
                                                <td width="15%"><label for="field-5" class="control-label"><input type="checkbox" name="PackageDiscountPlan" value=""> Package Discount Plan</label></td>
                                                <td width="20">
                                                    {{ Form::select('PackageDiscountPlanIdBulkAction',$DiscountPlanPACKAGE,array(), array("id" => "PackageDiscountPlanIdBulkAction", "class"=>"form-control")) }}
                                                    {{--<select id="PackageDiscountPlanIdBulkAction" name="PackageDiscountPlanIdBulkAction" class="form-control"></select>--}}
                                                </td>
                                                <td width="5%">&nbsp;</td>
                                                <td width="15%"></td>
                                                <td width="35%">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div>
                    </div>
                    <div id="InboundTariffTabBulkAction">
                        <div id="ContentInboundTariffTabBulkAction" class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <br/>
                                    <label for="field-5" class="control-label"><input type="checkbox" id="InboundTariff" name="InboundTariff" value=""> Inbound Ratetable</label>
                                    <br/>

                                    <table id="servicetableSubBoxBulkAction" class="table table-bordered datatable">
                                        <tr>
                                            <td width="10%"><label for="field-5" class="control-label">Access Category</label></td>
                                            <td width="30%">
                                                <select onchange="ShowTariffOnSelectedCategoryBulkAction();" id="DidCategoryIDBulkAction" name="DidCategoryIDBulkAction" class="form-control">
                                                    <?php
                                                    $index1 = 0;?>
                                                    @foreach(DIDCategory::getCategoryDropdownIDList() as $DIDCategoryID  => $CategoryName)
                                                        <option id="didCategotyBulkAction{{$index1++}}" value="{{$DIDCategoryID}}">{{$CategoryName}}</option>
                                                    @endforeach

                                                </select>
                                            </td>
                                            <td width="10%"><label for="field-5" class="control-label">Ratetable</label></td>
                                            <td width="30%">
                                                <select id="DidCategoryTariffIDBulkAction" name="DidCategoryTariffIDBulkAction" class="form-control">
                                                </select>
                                            </td>
                                            <td width="20%">
                                                <button onclick="AddCategoryTariffInTableBulkAction();" type="button" id="Service-update"  class="btn btn-primary btn-sm" data-loading-text="Loading...">
                                                    <i></i>
                                                    +
                                                </button>
                                            </td>
                                        </tr>
                                    </table>

                                    <div>
                                        <table id="categotyTarifftable" class="table table-bordered datatable">
                                            <thead>
                                            <tr>
                                                <td width="35%">Category</td>
                                                <td width="35%">Ratetable</td>
                                                <td width="20%">Actions</td>
                                                <input type="hidden" id="selectedcategotyTariffBulkAction" name="selectedcategotyTariffBulkAction" value=""/>
                                            </tr>
                                            </thead>
                                            <tbody id="categoryTariffIDListBodyBulkAction">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer" style="vertical-align: top">
                        <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

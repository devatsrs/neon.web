
<script>


    $(document).ready(function ($) {
        $('#add-new-service-form').submit(function(e){
            var ServiceID = $("#add-new-service-form [name='ServiceID']").val();
            if( typeof ServiceID != 'undefined' && ServiceID != '') {
               // alert(loadSelectedTemplateSubscription);
                if (!loadSelectedTemplateSubscription) {
                    toastr.error("Please wait.. subscription data is loading", "Error", toastr_opts);
                    return false;
                }
                if (!loadSelectedCategoryTariff) {
                    toastr.error("Please wait.. categoryTariff data is loading", "Error", toastr_opts);
                    return false;
                }
            }
            e.preventDefault();




           /* var OutboundDiscountPlanId = $("#add-new-service-form [name='OutboundDiscountPlanId']").val();
            alert(OutboundDiscountPlanId);
            var InboundDiscountPlanId = $("#add-new-service-form [name='InboundDiscountPlanId']").val();
            alert(InboundDiscountPlanId);
            var OutboundRateTableId = $("#add-new-service-form [name='OutboundRateTableId']").val();
            alert(OutboundRateTableId);*/

            //var subsription = $("#add-new-service-form [name='south']").val();
            //alert(subsription);



            if( typeof ServiceID != 'undefined' && ServiceID != ''){
                update_new_url = baseurl + '/servicesTemplate/update/'+ServiceID;
               // alert(update_new_url);
            }else{
                update_new_url = baseurl + '/servicesTemplate/store';
                //alert(document.getElementById("selectedSubscription").value);
            }
            document.getElementById("Service-update").disabled = true;
            var data = new FormData(($('#add-new-service-form')[0]));
            data.append ('selectedSubscription', document.getElementById("selectedSubscription").value);
            data.append ('selectedcategotyTariff', document.getElementById("selectedcategotyTariff").value);
            //alert("selectedSubscription" + document.getElementById("selectedSubscription").value);
           // alert("selectedcategotyTariff" + document.getElementById("selectedcategotyTariff").value);
            showAjaxScript(update_new_url, data, function(response){
                document.getElementById("Service-update").disabled = false;
                $(".btn").button('reset');
                if (response.status == 'success') {
                    $('#add-new-modal-service').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);										
					var ServiceRefresh = $("#ServiceRefresh").val();
                    //alert("ServiceRefresh" + ServiceRefresh);
					if( typeof ServiceRefresh != 'undefined' && ServiceRefresh == '1'){
						if (true) { //$('#ServiceStatus').is(":checked")
                           // alert("Called Data Table");
                            data_table.fnFilter(1,0);  // 1st value 2nd column index
                        }else{
                            data_table.fnFilter(0,0);
                        }
					}else{
						 $('select[data-type="service"]').each(function(key,el){
                        if($(el).attr('data-active') == 1) {
                            var newState = new Option(response.newcreated.ServiceName, response.newcreated.ServiceID, true, true);
                        }else{
                            var newState = new Option(response.newcreated.ServiceName, response.newcreated.ServiceID, false, false);
                        }
                        $(el).append(newState).trigger('change');
                        $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                            return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                        }));
                    });	
					}
                    
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        })
    });

    function sellectRowCheckBox() {
        //alert("sellectAllCheckBox no param");
        var self = $('.selectallservices');
        var is_checked = self.is(':checked');
        //alert("sellectAllCheckBox no param" + is_checked);
        $('#servicetable').find('tbody tr').each(function (i, el) {
            if (is_checked) {
                if ($(this).is(':visible')) {
                    $(this).find('input[type="checkbox"]').prop("checked", true);
                    $(this).addClass('selected');
                }
            } else {
                $(this).find('input[type="checkbox"]').prop("checked", false);
                $(this).removeClass('selected');
            }
        });

    }


    function sellectAllCheckBox(subsriptionId,count,SubscriptionIDValues) {
        //alert("sellectAllCheckBox three param");

        sellectRowCheckBox();

      //  alert(SubscriptionIDValues);
        var inputTypeCheckBoxName;
        var SubscriptionIDValuesList = SubscriptionIDValues.split(":");
        for (var i =0; i <count;i++ ) {
            inputTypeCheckBoxName= subsriptionId + "[" + i + "]";
            setValue(inputTypeCheckBoxName,SubscriptionIDValues[i]);
        }

    }

    function sellectAllCategoryService(categoryTariffID,count,categoryTariffIDValues) {
        //alert("sellectAllCategoryService three param");

        sellectRowCategoryService();

        //  alert(SubscriptionIDValues);
        var inputTypeCheckBoxName;
        var categoryTariffIDValuesList = categoryTariffIDValues.split(",");
        for (var i =0; i <count;i++ ) {
            inputTypeCheckBoxName= categoryTariffID + "[" + i + "]";
            setValue(inputTypeCheckBoxName,categoryTariffIDValuesList[i]);
        }

    }
    function sellectRowCategoryService() {
        //alert(document.getElementById("selectallCategoryService"));
        //alert(document.getElementById("selectallCategoryService").checked);
        var self = $('.selectallCategoryService');
        var is_checked = document.getElementById("selectallCategoryService").checked;
       // alert(self);
       // alert(is_checked);
        $('#categotyTarifftable').find('tbody tr').each(function (i, el) {
            if (is_checked) {
                if ($(this).is(':visible')) {
                    $(this).find('input[type="checkbox"]').prop("checked", true);
                    $(this).addClass('selected');
                }
            } else {
                $(this).find('input[type="checkbox"]').prop("checked", false);
                $(this).removeClass('selected');
            }
        });

    }

    function setValue(name,value1) {
        //var self = $(name);
      //  alert("Called");inputTypeCheckBoxName= subsriptionId + "[" + i + "]";
        var testBox = document.getElementById(name);
       // alert(testBox.checked);
        //self = document.getElementById(name);
        var is_checked = self.is(':checked');
        //alert(is_checked);
        if (testBox.checked) {
            testBox.value = value1;
        } else {
            testBox.value = '';
        }

       // alert(self.val());
        //alert(self.value);
      //  alert(testBox.value);
    }
    /*$('.selectallservices').on('click', function () {
        alert("Select All");
        var self = $(this);
        var is_checked = $(self).is(':checked');
        self.parents('table').find('tbody tr').each(function (i, el) {
            if (is_checked) {
                if ($(this).is(':visible')) {
                    $(this).find('input[type="checkbox"]').prop("checked", true);
                    $(this).addClass('selected');
                }
            } else {
                $(this).find('input[type="checkbox"]').prop("checked", false);
                $(this).removeClass('selected');
            }
        });
    });*/


    $(document).ready(function(){
        $("#searchFilter").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#servicetable tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });

    $(document).ready(function(){
        $("#filterCategotyTarifftable").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#categotyTarifftable tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });

    function editSelectedTemplateSubscription(selected_currency,editServiceTemplateID) {
       // alert("Called");
        var selected_company, data, url;
        url = baseurl + "/servicesTemplate/selectDataOnCurrency" +
                "?selectedCurrency=" + selected_currency + "&selectedData=editSelectedTemplateSubscription&editServiceTemplateID="+editServiceTemplateID;
         // alert("url :" + url);
        $.post(url, function (data, status) {
            //  var res = data.split('/>');
            //   alert(data);
            document.getElementById("SubscriptionIDListBody").innerHTML = "" + data;
            document.getElementById("selectedSubscription").value = document.getElementById("ajaxEditSelectedTemplateSubscription").value;
            loadSelectedTemplateSubscription = true;
            saveSelectedSubscription = document.getElementById("selectedSubscription").value;
            //alert(editSelectedTemplateSubscription);
            // $("#serviceBasedOnCurreny").html(data);
        }, 'html');

        var tabel = document.getElementById('servicetable');
        var rijen = tabel.rows.length;
        rowSubscriptionHtmlIndex = rijen;
        url = baseurl + "/servicesTemplate/selectDataOnCurrency" +
                "?selectedCurrency=" + selected_currency + "&selectedData=editSelectedTemplateDIDTariff&editServiceTemplateID="+editServiceTemplateID;
        //alert("url :" + url);
        $.post(url, function (data, status) {
            //  var res = data.split('/>');
             //  alert(data);

            document.getElementById("categoryTariffIDListBody").innerHTML = "" + data;
            categoryTariffIDListBody = document.getElementById("categoryTariffIDListBody").innerHTML;
            document.getElementById("selectedcategotyTariff").value = document.getElementById("ajaxEditSelectedTemplateDIDTariff").value;
            saveSelectedCategoryTariff = document.getElementById("selectedcategotyTariff").value;
            //alert(document.getElementById("selectedcategotyTariff").value);
            // $("#serviceBasedOnCurreny").html(data); saveSelectedSubscription
            loadSelectedCategoryTariff = true;
        }, 'html');

         tabel = document.getElementById('categotyTarifftable');
         rijen = tabel.rows.length;
        rowCategoryTariffHtmlIndex = rijen;


    }

    function loadValuesBasedOnCurrency(selected_currency,selectData,ServiceId,OutboundDiscountPlanID,InboundDiscountPlanID,OutboundTariffId) {
        // alert(selected_currency);
        if (selected_currency == '') {
            selected_currency = "NAN";
        }
        url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + selected_currency + "&selectedData=service";
        // alert("url :" + url);
        $.post(url, function (data, status) {
            //  var res = data.split('/>');
            //alert(data);
            document.getElementById("ServiceId").innerHTML = "" + data;
           // var ServiceId = $("div.hiddenRowData").find("input[name='ServiceId']").val();
           // alert("ServiceId" + ServiceId);
            if (ServiceId != '') {
                $("#add-new-service-form [name='ServiceId']").select2().select2('val', ServiceId);
            }else {
                $("#add-new-service-form [name='ServiceId']").select2().select2('val', '');
            }
           // console.log(document.getElementById("TemplateDataTabServiceId").innerHTML);
           // alert(document.getElementById("TemplateDataTabServiceId").innerHTML);

            // $("#serviceBasedOnCurreny").html(data);
        }, 'html');
        //if (selectData) {
           // editServiceId = $("div.hiddenRowData").find("input[name='ServiceId']").val();
          //  alert('editServiceId' + editServiceId);
          //  alert(document.getElementById('ServiceId'));
          //  document.getElementById('ServiceId').value = editServiceId;
           // $("#add-new-service-form [name='ServiceId']").select2().select2('val', editServiceId);
       // }
        //
        //
        url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + selected_currency + "&selectedData=outboundPlan";
        // alert("url :" + url);
        $.post(url, function (data, status) {
            // var res = data.split('/>');
           // alert(data);
            document.getElementById("OutboundDiscountPlanId").innerHTML = "" + data;
            //var OutboundDiscountPlanID = $("div.hiddenRowData").find("input[name='OutboundDiscountPlanID']").val();
           // alert(OutboundDiscountPlanID);
            if (OutboundDiscountPlanID) {
                $("#add-new-service-form [name='OutboundDiscountPlanId']").select2().select2('val', OutboundDiscountPlanID);
            }else {
                $("#add-new-service-form [name='OutboundDiscountPlanId']").select2().select2('val', '');
            }

            // $("#serviceBasedOnCurreny").html(data);
        }, 'html');
        url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + selected_currency + "&selectedData=inboundPlan";
        // alert("url :" + url);
        $.post(url, function (data, status) {
            // var res = data.split('/>');
            document.getElementById("InboundDiscountPlanId").innerHTML = "" + data;
            //var InboundDiscountPlanID = $("div.hiddenRowData").find("input[name='InboundDiscountPlanID']").val();
            if (InboundDiscountPlanID != null) {
                $("#add-new-service-form [name='InboundDiscountPlanId']").select2().select2('val', InboundDiscountPlanID);
            }else {
                $("#add-new-service-form [name='InboundDiscountPlanId']").select2().select2('val', '');
            }

            // $("#serviceBasedOnCurreny").html(data);
        }, 'html');

        url = baseurl + "/servicesTemplate/selectDataOnCurrency?selectedCurrency=" + selected_currency + "&selectedData=outboundTariff";
        // alert("url :" + url);
        $.post(url, function (data, status) {
            // var res = data.split('/>');
            document.getElementById("OutboundRateTableId").innerHTML = "" + data;
           // var OutboundTariffId = $("div.hiddenRowData").find("input[name='OutboundTariffId']").val();
            if (OutboundTariffId != null) {
                $("#add-new-service-form [name='OutboundRateTableId']").select2().select2('val', OutboundTariffId);
            }else {
                $("#add-new-service-form [name='OutboundRateTableId']").select2().select2('val', '');
            }

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

        ShowTariffOnSelectedCategory();

    }
    $(document).ready(function(){
        $("#serviceTemplateCurreny").change(function(){
            var selected_company, data, url;
            selected_currency = $("#serviceTemplateCurreny").val();

            data = {company: selected_company};
            resetFormFields();

            loadValuesBasedOnCurrency(selected_currency,false,'','','','');
        });

    });

    function ShowTariffOnSelectedCategory() {
            var selected_company, data, url;
            selected_currency = $("#serviceTemplateCurreny").val();
            selected_didCategory = $("#DidCategoryID").val();
            DidCategoryIndexValue = document.getElementById("DidCategoryID").selectedIndex;
            if (selected_currency == '') {
                selected_currency = "NAN";
            }
            data = {company: selected_company};

            url = baseurl + "/servicesTemplate/selectDataOnCurrency" +
                    "?selectedCurrency=" + selected_currency + "&selectedData=DidCategoryID&selected_didCategory="+selected_didCategory;
            // alert("url :" + url);
            $.post(url, function (data, status) {
                //  var res = data.split('/>');
             //   alert(data);
                document.getElementById("DidCategoryTariffID").innerHTML = "" + data;
                 saveDidCategoryTariffID = document.getElementById("DidCategoryTariffID").innerHTML;
                // $("#serviceBasedOnCurreny").html(data);
            }, 'html');

        }




    function ShowSubscriptionTemplate(showTabId) {
        //style="visibility: visible" style="visibility: hidden"
      //  alert("Called");
        var ServiceID = $("#add-new-service-form [name='ServiceID']").val();
        if( typeof ServiceID != 'undefined' && ServiceID != '') {
            // alert(loadSelectedTemplateSubscription);
            if (!loadSelectedTemplateSubscription) {
                toastr.error("Please wait.. subscription data is loading", "Error", toastr_opts);
                return false;
            }
            if (!loadSelectedCategoryTariff) {
                toastr.error("Please wait.. categoryTariff data is loading", "Error", toastr_opts);
                return false;
            }
        }
         if (showTabId == "SubscriptionTab") {
            document.getElementById("tab1").setAttribute("class", "active");
            document.getElementById("tab2").setAttribute("class", "");

            DidCategoryTariffID = document.getElementById('DidCategoryTariffID').innerHTML;
            DidCategoryIndexValue = document.getElementById("DidCategoryID").selectedIndex;
            saveTemplateDataCurrenyID = $("#serviceTemplateCurreny").val();
            saveTemplateDataServiceIds = $("#ServiceId").val();
            saveTemplateDataOutboundDiscountPlanId = $("#OutboundDiscountPlanId").val();
            saveTemplateDataInboundDiscountPlanId = $("#InboundDiscountPlanId").val();
            saveTemplateDataOutboundRateTableId = $("#OutboundRateTableId").val();
           // alert("DidCategoryIndexValue :" + DidCategoryIndexValue);
//            if ($("#DidCategoryID option:selected") != null) {
//                DidCategoryIndexValue = $("#DidCategoryID option:selected");
//                alert(DidCategoryIndexValue.selectedIndex);
//            }
            categoryTariffIDListBody = document.getElementById('categoryTariffIDListBody').innerHTML;
            saveSelectedCategoryTariff = document.getElementById("selectedcategotyTariff").value;
            //saveTemplateDataServiceIds = document.getElementById("TemplateDataTabServiceId").innerHTML;
            saveAjaxDynamicFieldHtml = document.getElementById('ajax_dynamicfield_html').innerHTML;
            //alert(saveAjaxDynamicFieldHtml);
            document.getElementById('templateSubscriptionList').innerHTML = templateSubscriptionList;
            document.getElementById("SubscriptionIDListBody").innerHTML = SubscriptionIDListBody;
            document.getElementById("selectedSubscription").value = saveSelectedSubscription;
            saveDidCategoryTariffID = document.getElementById("DidCategoryTariffID").innerHTML;


            var tabel = document.getElementById('servicetable');
            var rijen = tabel.rows.length;
            for (i = 1; i < rijen; i++){
                var inputs = tabel.rows.item(i).getElementsByTagName("input");
                var inputslengte = inputs.length;
                for(var j = 0; j < inputslengte; j++){
                    var inputval = inputs[j].value;
                    if (inputval != null) {
                        inputs[j].setAttribute("checked", "true");
                    }
                }
            }
            document.getElementById("ActiveTabContent").innerHTML = document.getElementById("ContentSubscriptionTab").innerHTML;
            document.getElementById("selectedcategotyTariff").value = saveSelectedCategoryTariff;
        } else if (showTabId == "InboundTariffTab") {
            //alert(saveDidCategoryTariffID);
            document.getElementById("tab2").setAttribute("class", "active");
            document.getElementById("tab1").setAttribute("class", "");


            saveTemplateDataCurrenyID = $("#serviceTemplateCurreny").val();
            saveTemplateDataServiceIds = $("#ServiceId").val();
            saveTemplateDataOutboundDiscountPlanId = $("#OutboundDiscountPlanId").val();
            saveTemplateDataInboundDiscountPlanId = $("#InboundDiscountPlanId").val();
            saveTemplateDataOutboundRateTableId = $("#OutboundRateTableId").val();


            //alert(DidCategoryIndexValue);DidCategoryIndexValue
          //  alert("InboundTariffTab :" + DidCategoryIndexValue);
            if (DidCategoryIndexValue != -1) {
               // document.getElementById("DidCategoryID").selectedIndex = "1";
               // document.getElementById("DidCategoryID").options[2].selected=true;
               // document.getElementById("DidCategoryID").options.namedItem("AAA").selected=true;
                var id = "didCategoty" + DidCategoryIndexValue;
                document.getElementById(id).setAttribute("selected",true);

            }
            saveSelectedSubscription = document.getElementById("selectedSubscription").value;
            saveAjaxDynamicFieldHtml = document.getElementById('ajax_dynamicfield_html').innerHTML;
            document.getElementById('DidCategoryTariffID').innerHTML = DidCategoryTariffID;
            document.getElementById('categoryTariffIDListBody').innerHTML = categoryTariffIDListBody;
            document.getElementById("selectedcategotyTariff").value = saveSelectedCategoryTariff;
            document.getElementById("DidCategoryTariffID").innerHTML = saveDidCategoryTariffID;
            var tabel = document.getElementById('categotyTarifftable');
            var rijen = tabel.rows.length;
            for (i = 1; i < rijen; i++){
                var inputs = tabel.rows.item(i).getElementsByTagName("input");
                var inputslengte = inputs.length;
                for(var j = 0; j < inputslengte; j++){
                    var inputval = inputs[j].value;
                    if (inputval != null) {
                        inputs[j].setAttribute("checked", "true");
                    }
                }
            }
            templateSubscriptionList = document.getElementById('templateSubscriptionList').innerHTML;
            SubscriptionIDListBody = document.getElementById("SubscriptionIDListBody").innerHTML;
            document.getElementById("ActiveTabContent").innerHTML = document.getElementById("ContentInboundTariffTab").innerHTML;
            document.getElementById("selectedSubscription").value = saveSelectedSubscription;
        }
    }
    $(document).ready(function(){
        document.getElementById("ActiveTabContent").innerHTML = document.getElementById("ContentSubscriptionTab").innerHTML;
    });


    function RemoveCategoryTariffRowInTable(rowID) {
        var removeValue = rowID.substr("CategoryTariffRowID".length, rowID.length) + ",";
        // alert(removeValue);
        var selectedselectedcategotyTariff = document.getElementById("selectedcategotyTariff").value;
        var removalueIndex = selectedselectedcategotyTariff.indexOf(removeValue);
        var firstValue = selectedselectedcategotyTariff.substr(0, removalueIndex);
        var lastValue = selectedselectedcategotyTariff.substr(removalueIndex + removeValue.length, selectedSubscription.length);
        selectedselectedcategotyTariff = firstValue + lastValue;
        // alert("selectedselectedcategotyTariff in remove:" + selectedselectedcategotyTariff);
        document.getElementById("selectedcategotyTariff").value = selectedselectedcategotyTariff;
        document.getElementById(rowID).innerHTML = "";
        document.getElementById(rowID).setAttribute("id", "");
    }
    function RemoveSubscriptionRowInTable(rowID) {
        var removeValue = rowID.substr("SubscriptionRowID".length, rowID.length) + ",";
       // alert(removeValue);
        var selectedSubscription = document.getElementById("selectedSubscription").value;
        var removalueIndex = selectedSubscription.indexOf(removeValue);
        var firstValue = selectedSubscription.substr(0, removalueIndex);
        var lastValue = selectedSubscription.substr(removalueIndex + removeValue.length, selectedSubscription.length);
        selectedSubscription = firstValue + lastValue;
        //alert("selectedSubscription row id:" + rowID);
        //alert("selectedSubscription in remove:" + selectedSubscription);
        document.getElementById("selectedSubscription").value = selectedSubscription;
        //alert(firstValue + lastValue);
      //  alert('Called' + rowID);
//        rowID = '#' + rowID;
//        alert($(rowID));
//        $(rowID).closest("tr").remove();
        //$(rowID).remove();
        //alert($(rowID));//servicetable
       // alert(document.getElementById("SubscriptionIDListBody"));
       // document.getElementById("SubscriptionIDListBody").deleteRow(1);
        document.getElementById(rowID).innerHTML = "";
        document.getElementById(rowID).setAttribute("id", "");
        //alert(rowIndex);
    }

    function AddCategoryTariffInTable() {
        //alert("Called");
        //DidCategoryID DidCategoryTariffID
        var SelectedDidCategoryID = $("#DidCategoryID option:selected");
        var DidCategoryIDText = SelectedDidCategoryID.text();
        var DidCategoryID = SelectedDidCategoryID.val();
        var SelectedDidCategoryTariffID = $("#DidCategoryTariffID option:selected");
        var DidCategoryTariffIDText = SelectedDidCategoryTariffID.text();
        var DidCategoryTariffID = SelectedDidCategoryTariffID.val();
        //alert(SelectedDidCategoryID + ":" + SelectedDidCategoryTariffID.val());
        if (typeof DidCategoryID == 'undefined' || DidCategoryID == '') {
            DidCategoryID = "0";
            DidCategoryIDText= "";
        }
        if (typeof DidCategoryID != 'undefined' && DidCategoryID != '' && typeof DidCategoryTariffID != 'undefined' && DidCategoryTariffID != '') {
            // alert("Selected Option Text: "+optionText + " " + optionID);
            // alert(document.getElementById("SubscriptionIDListBody"));
            // alert(document.getElementById("SubscriptionIDListBody").innerHTML);
            var CategoryTariffValue = DidCategoryID + '-' + DidCategoryTariffID;
            var setValue = "setValue('CategoryTariffID[" + rowCategoryTariffHtmlIndex + "]','" + CategoryTariffValue + "');";
            var idName = "CategoryTariffID[" + rowCategoryTariffHtmlIndex + "]";
            var idNameRow = "CategoryTariffRowID" + (CategoryTariffValue) + "";
            var colValue = DidCategoryID;
            //
            var selectedselectedcategotyTariff = document.getElementById("selectedcategotyTariff").value;
           // alert("selectedselectedcategotyTariff in add:" + selectedselectedcategotyTariff);
            if (selectedselectedcategotyTariff.indexOf(CategoryTariffValue) == -1) {
                var rowCategoryTariffHtml =
                        '<tr class="draggable" + ' +
                        'id="' + idNameRow + '\" ' +
                        'name="' + idNameRow + '\" ' +
                        '>' +
                        '<td>' + DidCategoryIDText + '</td>' +
                        '<td>' + DidCategoryTariffIDText + '</td>' +
                        '<td>' +
                        '<a title="Delete" onClick="RemoveCategoryTariffRowInTable(' + "'" + idNameRow + "'" + ');" class="delete-service2 btn btn-danger btn-sm"><i class="entypo-trash"></i></a>' +
                        '</td>' +
                        '</tr>';
                ;

                selectedselectedcategotyTariff = selectedselectedcategotyTariff + CategoryTariffValue + ",";
                document.getElementById("selectedcategotyTariff").value = selectedselectedcategotyTariff;

                //  alert(rowCategoryTariffHtml);
                rowCategoryTariffHtmlIndex = rowCategoryTariffHtmlIndex + 1;
                document.getElementById("categoryTariffIDListBody").innerHTML = document.getElementById("categoryTariffIDListBody").innerHTML + rowCategoryTariffHtml;
                //categoryTariffIDListBody = document.getElementById("categoryTariffIDListBody").innerHTML;
            } else {
                alert("Tariff against category already added");
            }
        }else {
            alert("Please select Category and Tariff");
        }
    }
    function AddSubscriptionInTableWithHtml(optionText,optionID) {
        var setValue = "setValue('SubscriptionID[" + rowSubscriptionHtmlIndex + "]','" + optionID + "');";
        var idName = "SubscriptionID[" + rowSubscriptionHtmlIndex + "]";
        var stateiDName = "SubscriptionCheckState" + rowSubscriptionHtmlIndex + "";
        var idNameRow = "SubscriptionRowID" + (optionID) + "";
        var addOptionID = optionID + ",";
        var colValue = optionText;
        var selectedSubscription = document.getElementById("selectedSubscription").value;
        if (typeof optionID != 'undefined' && optionID != '') {
            // alert("selectedSubscription in add:" + selectedSubscription);
            if (selectedSubscription.indexOf(addOptionID) == -1) {
                var rowSubscriptionHtml =
                        '<tr class="draggable" + ' +
                        'id="' + idNameRow + '\" ' +
                        'name="' + idNameRow + '\" ' +
                        '>' +
                        '<td>' + optionText + '</td>' +
                        '<td>' +
                        '<a title="Delete" onClick="RemoveSubscriptionRowInTable(' + "'" + idNameRow + "'" + ');" class="delete-service1 btn btn-danger btn-sm"><i class="entypo-trash"></i></a>' +
                        '</td>' +
                        '</tr>';
                ;

                selectedSubscription = selectedSubscription + optionID + ",";
                document.getElementById("selectedSubscription").value = selectedSubscription;

                //  alert("selectedSubscription :" + selectedSubscription);
                //  alert(rowSubscriptionHtml);
                rowSubscriptionHtmlIndex = rowSubscriptionHtmlIndex + 1;
                document.getElementById("SubscriptionIDListBody").innerHTML = document.getElementById("SubscriptionIDListBody").innerHTML + rowSubscriptionHtml;
                // SubscriptionIDListBody = document.getElementById("SubscriptionIDListBody").innerHTML;
            } else {
                alert("Selected Subscription is already added");
            }
        }else {
            alert("Please select the subscription");
        }
    }
    function AddSubscriptionInTable() {
        //alert("Called");
        var selectedSubscription = $("#templateSubscriptionList option:selected");
        var optionText = selectedSubscription.text();
        var optionID = selectedSubscription.val();
        AddSubscriptionInTableWithHtml(optionText,optionID);
    }


    var rowCategoryTariffHtmlIndex = 0;
    var rowSubscriptionHtmlIndex = 0;
    var SubscriptionIDListBody = '';
    var categoryTariffIDListBody = '';
    var saveAjaxDynamicFieldHtml = '';
    var saveSelectedSubscription = '';
    var saveSelectedCategoryTariff = '';
    var saveDidCategoryTariffID = '';
    var templateSubscriptionList = '';
    var DidCategoryTariffID = '';
    var DidCategoryIndexValue = -1;
    var loadSelectedTemplateSubscription = false;
    var loadSelectedCategoryTariff = false;
    var saveTemplateDataServiceIds = '';
    var saveTemplateDataCurrenyID = '';
    var saveTemplateDataOutboundDiscountPlanId = '';
    var saveTemplateDataInboundDiscountPlanId = '';
    var saveTemplateDataOutboundRateTableId = '';

</script>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-service">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-service-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h5 class="modal-title" id="ServiceTemplateModelTitle">Add New Service Template</h5>
                    </div>
                    <div class="modal-body">

                        <div id="TemplateDataTab">

                            <div id="ContentTemplateDataTab" class="modal-body">
                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <table width="100%">
                                                <tr>
                                                    <td width="15%"><label for="field-5" class="control-label">Name</label></td>
                                                    <td width="30%"><input type="text" name="Name" class="form-control" id="field-5" placeholder=""></td>
                                                    <td width="5%">&nbsp;</td>
                                                    <td width="15%"><label for="field-5" class="control-label">Currency</label></td>
                                                    <td width="35%">
                                                        {{ Form::select('CurrencyId',Currency::getCurrencyDropdownIDList(),'', array("id" => "serviceTemplateCurreny", "class"=>"form-control")) }}
                                                    </td>
                                                </tr>
                                            </table>
                                            <input type="hidden" name="ServiceID" >
                                        </div>
                                    </div>
                                    <div class="col-md-12" >
                                        <div class="form-group">
                                            <table width="100%">
                                                <tr>
                                                    <td width="15%"><label for="field-5" class="control-label">Service</label></td>
                                                    <td width="30%"><select  id="ServiceId" name="ServiceId" class="form-control"></select></td>
                                                    <td width="5%">&nbsp;</td>
                                                    <td width="15%"><label for="field-5" class="control-label">Outbound Traiff</label></td>
                                                    <td width="35%">
                                                        <select id="OutboundRateTableId" name="OutboundRateTableId" class="form-control">
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <table width="100%">
                                                <tr>
                                                    <td width="15%"><label for="field-5" class="control-label">Outbound Discount Plan</label></td>
                                                    <td width="30"><select id="OutboundDiscountPlanId" name="OutboundDiscountPlanId" ></select></td>
                                                    <td width="5%">&nbsp;</td>
                                                    <td width="15%"><label for="field-5" class="control-label">Inbound Discount Plan</label></td>
                                                    <td width="35%">
                                                        <select id="InboundDiscountPlanId" name="InboundDiscountPlanId" >
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>


                                        </div>
                                    </div>

                                    <div id="ajax_dynamicfield_html" class="margin-top"></div>
                                </div>
                            </div>
                        </div>

                        <div>

                            <ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->

                                <li id="tab1">
                                    <a  href="javascript:void(0);" onclick="ShowSubscriptionTemplate('SubscriptionTab');" >
                                        Subscription
                                    </a>
                                </li>
                                <li id="tab2">
                                    <a href="javascript:void(0);" onclick="ShowSubscriptionTemplate('InboundTariffTab');" >
                                        Inbound Tariff
                                    </a>
                                </li>

                            </ul>
                            <br/>
                        </div>



                            <div id="ActiveTabContent">
                            </div>




                    <div id="SubscriptionTab" style="visibility: hidden">
                        <div id="ContentSubscriptionTab" class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <br/>
                                    <table id="servicetableSubBox" class="table table-bordered datatable">
                                        <tr>
                                            <td width="80%">
                                                <select id="templateSubscriptionList" name="templateSubscriptionList" class="form-control">
                                                </select>
                                            </td>
                                            <td width="20%">
                                                <button onclick="AddSubscriptionInTable();" type="button" id="Service-update"  class="btn btn-primary btn-sm" data-loading-text="Loading...">
                                                    <i></i>
                                                    +
                                                </button>
                                            </td>
                                        </tr>
                                     </table>

                                </div>
                                <div class="col-md-12">
                                    <div class="form-group"><input type="text" id="searchFilter" name="searchFilter" class="form-control" id="field-5" placeholder="Search">
                                        <table id="servicetable" class="table table-bordered datatable">

                                            <thead>
                                            <tr>
                                                <td width="70%">Subscription</td>
                                                <td width="20%">Actions</td>
                                                <input type="hidden" id="selectedSubscription" name="selectedSubscription" value=""/>
                                            </tr>
                                            </thead>
                                            <tbody id="SubscriptionIDListBody">

                                                     <!-- //subscription id list -->

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div id="InboundTariffTab" style="visibility: hidden;display: none">
                        <div id="ContentInboundTariffTab" class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <br/>
                                    <table id="servicetableSubBox" class="table table-bordered datatable">
                                        <tr>
                                            <td width="10%"><label for="field-5" class="control-label">DIDCategory</label></td>
                                            <td width="30%">
                                                <select onchange="ShowTariffOnSelectedCategory();" id="DidCategoryID" name="DidCategoryID" class="form-control">
                                                    <?php
                                                    $index1 = 0;?>
                                                    @foreach(DIDCategory::getCategoryDropdownIDList() as $DIDCategoryID  => $CategoryName)
                                                        <option id="didCategoty{{$index1++}}" value="{{$DIDCategoryID}}">{{$CategoryName}}</option>
                                                    @endforeach

                                                </select>
                                            </td>
                                            <td width="10%"><label for="field-5" class="control-label">Tariff</label></td>
                                            <td width="30%">
                                                <select id="DidCategoryTariffID" name="DidCategoryTariffID" class="form-control">
                                                </select>
                                            </td>
                                            <td width="20%">
                                                <button onclick="AddCategoryTariffInTable();" type="button" id="Service-update"  class="btn btn-primary btn-sm" data-loading-text="Loading...">
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
                                                <td width="35%">Tariff</td>
                                                <td width="20%">Actions</td>
                                                <input type="hidden" id="selectedcategotyTariff" name="selectedcategotyTariff" value=""/>
                                            </tr>
                                            </thead>
                                            <tbody id="categoryTariffIDListBody">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer" style="vertical-align: top">
                        <button type="submit" id="Service-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
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
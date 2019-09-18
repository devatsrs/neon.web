<table id="addRevenueRow" class="hide hidden">
    <tr>
        <td>
            <select class="selectOpt dealer" name="Type[]" onchange="changePrice(this)">
                <option value="Customer">Customer</option>
                <option value="Vendor">Vendor</option>
            </select>
        </td>
        <td>
            {{Form::select('Destination[]', $Countries, '',array("class"=>"selectOpt"))}}
        </td>
        <td>
            {{ Form::select('Trunk[]', $Trunks,'', array("class"=>"selectOpt")) }}
        </td>
        <td>
            <input type="number" name="Revenue[]" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control revenue">
        </td>
        <td>
            <input type="number" name="SalePrice[]" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control salePrice">
        </td>
        <td>
            <input type="number" name="BuyPrice[]" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control buyPrice">
        </td>
        <td>
            <input readonly type="number" name="PLPerMinute[]" value="0.0000" class="form-control pl-minute">
        </td>
        <td>
            <input readonly type="number" name="Minutes[]" value="0" class="form-control minutes">
        </td>
        <td>
            <input readonly type="number" name="PL[]" value="0.0000" class="form-control pl-total">
        </td>
        <td>
            <button type="button" title="Delete" onclick="deleteDeal(this)" class="btn btn-danger btn-xs del-deal" data-loading-text="Loading...">
                <i></i>
                -
            </button>
        </td>
    </tr>
</table>
<table id="addPaymentRow" class="hide hidden">
    <tr>
        <td>
            <select class="selectOpt dealer" name="Type[]" onchange="changePrice(this)">
                <option value="Customer">Customer</option>
                <option value="Vendor">Vendor</option>
            </select>
        </td>
        <td>
            {{Form::select('Destination[]', $Countries, '',array("class"=>"selectOpt"))}}
        </td>
        <td>
            {{ Form::select('Trunk[]', $Trunks,'', array("class"=>"selectOpt")) }}
        </td>
        <td>
            <input type="number" name="Minutes[]" value="0" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control minutes">
        </td>
        <td>
            <input type="number" name="SalePrice[]" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control salePrice">
        </td>
        <td>
            <input type="number" name="BuyPrice[]" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control buyPrice">
        </td>
        <td>
            <input readonly type="number" name="PLPerMinute[]" value="0.0000" class="form-control pl-minute">
        </td>
        <td>
            <input readonly type="number" name="Revenue[]" value="0.00" class="form-control revenue">
        </td>
        <td>
            <input readonly type="number" name="PL[]" value="0.0000" class="form-control pl-total">
        </td>
        <td>
            <button type="button" title="Delete" onclick="deleteDeal(this)" class="btn btn-danger btn-xs del-deal" data-loading-text="Loading...">
                <i></i>
                -
            </button>
        </td>
    </tr>
</table>
<table id="addNote" class="hide hidden">
    <tr>
        <td>
            <textarea name="Note[]" placeholder="Write note here..." class="form-control"></textarea>
        </td>
        <td>
            {{ User::get_user_full_name() }}
        </td>
        <td class="dateTime">
            {{ date("Y-m-d") }}
        </td>
        <td>
            <button type="button" title="Delete" onclick="deleteNote(this)" class="btn btn-danger btn-xs del-deal" data-loading-text="Loading...">
                <i></i>
                -
            </button>
        </td>
    </tr>
</table>

<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $("#save_deal").click(function (ev) {
            $('#save_deal').button('loading');
            $("#deal-from").submit();
        });


        $("#StartDate").datepicker({
            todayBtn:  1,
            autoclose: true
        }).on('changeDate', function (selected) {
            var minDate = new Date(selected.date.valueOf());
            var endDate = $('#EndDate');
            endDate.datepicker('setStartDate', minDate);
            if(endDate.val() && new Date(endDate.val()) != undefined) {
                if(minDate > new Date(endDate.val()))
                    endDate.datepicker("setDate", minDate)
            }
        });

        $("#EndDate").datepicker({autoclose: true})
                .on('changeDate', function (selected) {
                    var maxDate = new Date(selected.date.valueOf());
                    //$('#StartDate').datepicker('setEndDate', maxDate);
                });

        if(new Date($('#StartDate').val()) != undefined){
            $("#EndDate").datepicker('setStartDate', new Date($('#StartDate').val()))
        }
        checkDealType();
        $("[name='DealType']").change(function () {
            checkDealType();
        });
        disableFieldsOnAddDetails();
        countTotalPL();
    });

    function ajax_form_success(response){
        if(typeof response.redirect != 'undefined' && response.redirect != ''){
            window.location = response.redirect;
        }
    }

    function disableFieldsOnAddDetails(){
        var rowLength = $(".dealTable tbody tr");
        var fields = $("select[name='DealType'], select[name='CodedeckID']");
        if(rowLength.length > 0){
            $.each(fields, function(x,y) {
                var ele = $(y);
                ele.after("<input type='hidden' name='" + ele.attr('name') + "' value='" + ele.val() + "'>");
            });
            fields.attr("disabled","disabled").trigger("change");
        } else {
            $.each(fields, function(x,y) {
                $("[type='hidden'][name='" + $(y).attr('name') + "']").remove();
            });
            fields.removeAttr("disabled").trigger("change")
        }
    }

    function checkDealType(){
        var DealType = $("[name='DealType']").val();
        if(DealType == "Revenue"){
            $(".revenueRow").show();
            $(".paymentRow").hide();
        } else {
            $(".revenueRow").hide();
            $(".paymentRow").show();
        }
    }

    function addDeal(){
        var DealType = $("[name='DealType']").val();
        var row = $("#add" + DealType + "Row tr:first").parent().html();
        var tbody = $(".dealTable tbody");
        tbody.append(row);
        var lastRow = $(".dealTable tbody tr:last");
        lastRow.find(".selectOpt").select2();
        disableFieldsOnAddDetails();
    }

    function countTotalPL(){

        var totalPL = 0;
        $(".dealTable tbody tr").each(function () {
            var plVal = $(this).attr('data-pl');
            totalPL +=  (plVal != "" && plVal != undefined && plVal != "NaN") ? parseFloat(plVal) : 0;
        });
        $(".pl-grand").text(totalPL.toFixed(4));
        $("[name='TotalPL']").val(totalPL.toFixed(4));

    }

    function changePrice(ele){
        var that = $(ele);
        var row = that.parent().parent();
        var dealer = row.find("select.dealer").val();
        //console.log(dealer)

        var DealType = $("[name='DealType']").val();
        //Getting Values
        var salePrice = row.find(".salePrice").val() == "" ? 0 : row.find(".salePrice").val();
        salePrice = (salePrice != undefined && salePrice != "NaN") ? parseFloat(salePrice) : 0;
        var buyPrice = row.find(".buyPrice").val() == "" ? 0 : row.find(".buyPrice").val();
        buyPrice = (buyPrice != undefined && buyPrice != "NaN") ? parseFloat(buyPrice) : 0;


        //Calculating values
        var plminute = dealer == "Customer" ? salePrice - buyPrice : buyPrice - salePrice;
        var minutes = revenue = 0;

        if(DealType == "Revenue") {
            revenue = row.find(".revenue").val() == "" ? 0 : row.find(".revenue").val();
            revenue = (revenue != undefined && revenue != "NaN") ? parseFloat(revenue) : 0;

            minutes = salePrice != 0 ? revenue / salePrice : 0;
            minutes = (minutes != undefined && minutes != "NaN") ? minutes : 0;
            row.find(".minutes").val(minutes);
        } else {
            minutes = row.find(".minutes").val() == "" ? 0 : row.find(".minutes").val();
            minutes = (minutes != undefined && minutes != "NaN") ? parseFloat(minutes) : 0;

            if(dealer == "Customer")
                revenue = salePrice != 0 ? minutes * salePrice : 0;
            else
                revenue = buyPrice != 0 ? minutes * buyPrice : 0;

            revenue = (revenue != undefined && revenue != "NaN") ? parseFloat(revenue) : 0;
            row.find(".revenue").val(revenue);
        }

        var profileLoss = plminute * minutes;

        row.find(".pl-minute").val(plminute);
        row.find(".pl-total").val(profileLoss);
        row.attr("data-pl", profileLoss);
        countTotalPL();
    }


    function deleteDeal(ele){
        var that = $(ele);
        var row = that.parent().parent();
        row.remove();
        countTotalPL();
        disableFieldsOnAddDetails();
    }


    function addNote(){
        var row = $("#addNote tr:first").parent().html();
        var tbody = $(".noteTable tbody");
        tbody.append(row);
        var time = new Date();
        $(".noteTable tbody tr:last td.dateTime").append(" " + time.toLocaleTimeString().toLowerCase());
    }

    function deleteNote(ele){
        var that = $(ele);
        var row = that.parent().parent();
        row.remove();
        countTotalPL();
    }
</script>
<table id="addRow" class="hide hidden">
    <tr>
        <td>
            <select class="selectOpt dealer" onchange="changePrice(this)">
                <option value="customer">Customer</option>
                <option value="vendor">Vendor</option>
            </select>
        </td>
        <td>
            <select class="selectOpt">
                <option value="">Select</option>
            </select>
        </td>
        <td>
            <select class="selectOpt">
                <option value="">Select</option>
            </select>
        </td>
        <td>
            <input type="number" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control revenue">
        </td>
        <td>
            <input type="number" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control salePrice">
        </td>
        <td>
            <input type="number" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control buyPrice">
        </td>
        <td>
            <input readonly type="number" value="0.0000" class="form-control pl-minute">
        </td>
        <td>
            <input readonly type="number" value="0" class="form-control minutes">
        </td>
        <td>
            <input readonly type="number" value="0.0000" class="form-control pl-total">
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
            <textarea placeholder="Write note here..." class="form-control"></textarea>
        </td>
        <td>
            {{ User::get_user_full_name() }}
        </td>
        <td class="dateTime">
            {{ date("d-m-Y") }}
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
            fields.attr("disabled","disabled").trigger("change")
        } else {
            fields.removeAttr("disabled").trigger("change")
        }
    }

    function addDeal(){
        var row = $("#addRow tr:first").parent().html();
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
        $(".pl-grand").text(totalPL.toFixed(2));

    }

    function changePrice(ele){
        var that = $(ele);
        var row = that.parent().parent();
        var dealer = row.find("select.dealer").val();
        //console.log(dealer)

        //Getting Values
        var revenue = row.find(".revenue").val() == "" ? 0 : row.find(".revenue").val();
        revenue = (revenue != undefined && revenue != "NaN") ? parseFloat(revenue) : 0;
        var salePrice = row.find(".salePrice").val() == "" ? 0 : row.find(".salePrice").val();
        salePrice = (salePrice != undefined && salePrice != "NaN") ? parseFloat(salePrice) : 0;
        var buyPrice = row.find(".buyPrice").val() == "" ? 0 : row.find(".buyPrice").val();
        buyPrice = (buyPrice != undefined && buyPrice != "NaN") ? parseFloat(buyPrice) : 0;


        //Calculating values
        var plminute = dealer == "customer" ? salePrice - buyPrice : buyPrice - salePrice;
        var minutes = salePrice != 0 ? revenue / salePrice : 0;
        minutes = (minutes != undefined && minutes != "NaN") ? minutes : 0;
        var profileLoss = plminute * minutes;

        row.find(".pl-minute").val(plminute);
        row.find(".minutes").val(minutes);
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
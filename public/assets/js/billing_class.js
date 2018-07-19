jQuery(document).ready(function ($) {
$('#save_billing').on("click",function(e){
    e.preventDefault();
    $('#save_billing').button('loading');
    //submit_ajax($('#save_billing').attr('href'),$("#billing-form").serialize());
    showAjaxScript($('#save_billing').attr('href'), new FormData($('#billing-form')[0]), function(response){
        $(".btn").button('reset');
        if (response.status == 'success') {
            $('#add-new-modal-billingclass').modal('hide');
            toastr.success(response.message, "Success", toastr_opts);
            $('select[data-type="billing_class"]').each(function(key,el){
                if($(el).attr('data-active') == 1) {
                    var newState = new Option(response.data.Name, response.data.BillingClassID, true, true);
                }else{
                    var newState = new Option(response.data.Name, response.data.BillingClassID, false, false);
                }
                $(el).append(newState).trigger('change');
                $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                    return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                }));
            });
        }else{
            toastr.error(response.message, "Error", toastr_opts);
        }
    });

    return false;
});

$("body").popover({
    selector: '[data-toggle="popover_html"]',
    trigger:'hover',
    html:true,
    template:'<div class="popover popover-primary" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
    //template:'<div class="popover3" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
});
$('#payment-add-row').on('click', function(e){
    e.preventDefault();
    var itemrow = $('#rowContainer .itemrow').clone();
    itemrow.removeAttr('class');
    itemrow.find('select.select22').each(function(i,item){
        buildselect2(item);
    });
    $('#PaymentReminderTable > tbody').append(itemrow);
    rebind();
    nicescroll();
});

$('#PaymentReminderTable > tbody').on('click','.remove-row', function(e){
    e.preventDefault();
    var row = $(this).parent().parent();
    row.remove();
});

$("#billing-form [name='PaymentReminder[Time]']").change(function(){
    populateInterval($(this).val(),'PaymentReminder','billing-form');
});

$("#billing-form [name='LowBalanceReminder[Time]']").change(function(){
    populateInterval($(this).val(),'LowBalanceReminder','billing-form');
});
$("#billing-form [name='BalanceWarning[Time]']").change(function(){
    populateInterval($(this).val(),'BalanceWarning','billing-form');
});
$("#billing-form [name='QosAlert[Time]']").change(function(){
    populateInterval($(this).val(),'QosAlert','billing-form');
});
    $("#billing-form [name='Report[Time]']").change(function(){
        populateInterval($(this).val(),'Report','billing-form');
    });
    $("#call-billing-form [name='CallAlert[Time]']").change(function(){
        populateInterval($(this).val(),'CallAlert','call-billing-form');
    });
$("#call-billing-form [name='AlertType']").change(function(){
    $("#call-billing-form .custom_field").addClass('hidden');
    if($(this).val() == 'block_destination'){
        $("#call-billing-form [name='CallAlert[BlacklistDestination][]']").parents('.row').removeClass('hidden');
        $("#call-billing-form .ReminderEmail").html('Send Email To');
    }else if($(this).val() == 'call_duration' || $(this).val() == 'call_cost' || $(this).val() == 'call_after_office'){

        $("#call-billing-form [name='CallAlert[EmailToAccount]']").parents('.col-md-6').removeClass('hidden');
        $("#call-billing-form .ReminderEmail").html('Send Copy To');
        if($(this).val() == 'call_duration'){
            $("#call-billing-form [name='CallAlert[AccountIDs]']").parents('.row').removeClass('hidden');
            $("#call-billing-form [name='CallAlert[Duration]']").parents('.row').removeClass('hidden');
        }else if($(this).val() == 'call_cost'){
            $("#call-billing-form [name='CallAlert[AccountIDs]']").parents('.row').removeClass('hidden');
            $("#call-billing-form [name='CallAlert[Cost]']").parents('.row').removeClass('hidden');
        }else if($(this).val() == 'call_after_office'){
            $("#call-billing-form [name='CallAlert[AccountID]']").parents('.row').removeClass('hidden');
            $("#call-billing-form [name='CallAlert[OpenTime]']").parents('.row').removeClass('hidden');
        }
    }else if($(this).val() == 'vendor_balance_report'){
        $("#call-billing-form .ReminderEmail").html('Send Email To');
        $("#call-billing-form [name='CallAlert[VAccountID][]']").parents('.row').removeClass('hidden');
        $("#call-billing-form [name='CallAlert[Time]']").parents('.row').removeClass('hidden');
        $("#call-billing-form [name='CallAlert[Interval]']").parents('.row').removeClass('hidden');
        $("#call-billing-form [name='CallAlert[Day][]']").parents('.row').removeClass('hidden');
    }
    else if($(this).val() == 'account_balance'){
        $("#call-billing-form .ReminderEmail").html('Send Copy To');
        $("#call-billing-form [name='CallAlert[EmailToAccount]']").parents('.col-md-6').removeClass('hidden');
        $("#call-billing-form [name='CallAlert[AccountIDs]']").parents('.row').removeClass('hidden');
        $("#call-billing-form [name='CallAlert[ReminderDays]']").parents('.row').removeClass('hidden');
    }else if($(this).val() == 'Low_stock_reminder'){
        $("#call-billing-form .ReminderEmail").html('Send Email To');
        //$("#call-billing-form [name='CallAlert[VAccountID][]']").parents('.row').removeClass('hidden');
        $("#call-billing-form [name='CallAlert[Time]']").parents('.row').removeClass('hidden');
        $("#call-billing-form [name='CallAlert[Interval]']").parents('.row').removeClass('hidden');
        $("#call-billing-form [name='CallAlert[Day][]']").parents('.row').removeClass('hidden');
    }
});
});
function rebind() {

    $('#PaymentReminderTable > tbody').find(".input-spinner").each(function (i, el) {
        var $this = $(el),
            $minus = $this.find('button:first'),
            $plus = $this.find('button:last'),
            $input = $this.find('input'),
            minus_step = attrDefault($minus, 'step', -1),
            plus_step = attrDefault($minus, 'step', 1),
            min = attrDefault($input, 'min', null),
            max = attrDefault($input, 'max', null);
        $this.find('button').unbind('click');
        $this.find('button').on('click', function (ev) {
            ev.preventDefault();

            var $this = $(this),
                val = $input.val(),
                step = attrDefault($this, 'step', $this[0] == $minus[0] ? -1 : 1);

            if (!step.toString().match(/^[0-9-\.]+$/)) {
                step = $this[0] == $minus[0] ? -1 : 1;
            }

            if (!val.toString().match(/^[0-9-\.]+$/)) {
                val = 0;
            }

            $input.val(parseFloat(val) + step).trigger('keyup');
        });
        $input.keyup(function () {
            if (min != null && parseFloat($input.val()) < min) {
                $input.val(min);
            }
            else if (max != null && parseFloat($input.val()) > max) {
                $input.val(max);
            }
        });

    });
}

function populateInterval(jobtype,form,formID){


    $("#"+formID+" [name='"+form+"[Interval]']").addClass('visible');
    var selectBox = $("#"+formID+" [name='"+form+"[Interval]']");
    var selectBoxStartDay = $("#"+formID+" [name='"+form+"[StartDay]']");
    $("#"+formID+" ."+form+"Day").hide();
    var starttime = $("#"+formID+" .starttime");
    if(selectBox){
        selectBox.empty();
        selectBoxStartDay.empty();
        options = [];
        option = [];

        if(jobtype == 'HOUR'){
            for(var i=1;i<'24';i++){
                options.push(new Option(i+" Hour", i, false, false));
            }
            starttime.show();
        }else if(jobtype == 'MINUTE'){
            if(form == 'QosAlert' || form == 'CallAlert'){
                options.push(new Option("30 Minute", 30, false, false));
            }else{
                for(var i=1;i<60;i++){
                    options.push(new Option(i+" Minute", i, false, false));
                }
            }

            starttime.hide();
            starttime.val('');
        }else if(jobtype == 'DAILY'){
            for(var i=1;i<'32';i++){
                options.push(new Option(i+" Day", i, false, false));
            }

            starttime.show();
        }else if(jobtype == 'MONTHLY'){
            for(var i=1;i<13;i++){
                options.push(new Option(i+" Month", i, false, false));
            }
            for(var i=1;i<'32';i++){
                option.push(new Option(i+" Day", i, false, false));
            }
            //option.sort();
            selectBoxStartDay.append(option);
            selectBoxStartDay.val(1).trigger('change');

            $("#"+formID+" ."+form+"Day").show();
            starttime.show();
        } else if(jobtype == 'YEARLY'){
            for(var i=1;i<2;i++){
                options.push(new Option(i+" Year", i, false, false));
            }
            //option.sort();
            selectBoxStartDay.append(option);
            selectBoxStartDay.val(1).trigger('change');

            $("#"+formID+" ."+form+"Day").show();
            starttime.show();
        } else if(jobtype == 'WEEKLY'){
            for(var i=1;i<53;i++){
                options.push(new Option(i+" Week", i, false, false));
            }
            //option.sort();
            selectBoxStartDay.append(option);
            selectBoxStartDay.val(1).trigger('change');

            $("#"+formID+" ."+form+"Day").show();
            starttime.show();
        }
        //options.sort();
        selectBox.append(options);
        var firstval = selectBox.find('option').first().val();
        selectBox.val(firstval).trigger('change');
    }
}
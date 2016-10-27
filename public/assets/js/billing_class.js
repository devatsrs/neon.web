$('#save_billing').on("click",function(e){
    e.preventDefault();
    $('#save_billing').button('loading');
    submit_ajax($('#save_billing').attr('href'),$("#billing-form").serialize());
    return false;
});

$('#payment-add-row').on('click', function(e){
    e.preventDefault();
    $('#PaymentReminderTable > tbody').append(add_row_html_payment);

    $('select.select2').addClass('visible');
    $('select.select2').select2();
    rebind();
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
$("#billing-form [name='QosAlert[Time]']").change(function(){
    populateInterval($(this).val(),'QosAlert','billing-form');
});
$("#call-billing-form [name='AlertType']").change(function(){
    $("#call-billing-form .custom_field").addClass('hidden');
    if($(this).val() == 'block_destination'){
        $("#call-billing-form [name='CallAlert[BlacklistDestination][]']").parents('.row').removeClass('hidden');
        $("#call-billing-form [name='CallAlert[ReminderEmail]']").parents('.row').removeClass('hidden');
    }else if($(this).val() == 'call_duration' || $(this).val() == 'call_cost' || $(this).val() == 'call_after_office'){
        $("#call-billing-form [name='CallAlert[AccountID]']").parents('.row').removeClass('hidden');
        if($(this).val() == 'call_duration'){
            $("#call-billing-form [name='CallAlert[Duration]']").parents('.row').removeClass('hidden');
        }else if($(this).val() == 'call_cost'){
            $("#call-billing-form [name='CallAlert[Cost]']").parents('.row').removeClass('hidden');
        }else if($(this).val() == 'call_after_office'){
            $("#call-billing-form [name='CallAlert[OpenTime]']").parents('.row').removeClass('hidden');
        }
    }
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
                options.push(new Option(i+" Hour", i, true, true));
            }
            starttime.show();
        }else if(jobtype == 'MINUTE'){
            for(var i=1;i<60;i++){
                options.push(new Option(i+" Minute", i, true, true));
            }
            starttime.hide();
            starttime.val('');
        }else if(jobtype == 'DAILY'){
            for(var i=1;i<'32';i++){
                options.push(new Option(i+" Day", i, true, true));
            }

            starttime.show();
        }else if(jobtype == 'MONTHLY'){
            for(var i=1;i<13;i++){
                options.push(new Option(i+" Month", i, true, true));
            }
            for(var i=1;i<'32';i++){
                option.push(new Option(i+" Day", i, true, true));
            }
            //option.sort();
            selectBoxStartDay.append(option);
            selectBoxStartDay.val(1).trigger('change');

            $("#"+formID+" ."+form+"Day").show();
            starttime.show();
        }
        //options.sort();
        selectBox.append(options);
        selectBox.val(1).trigger('change');
    }
}
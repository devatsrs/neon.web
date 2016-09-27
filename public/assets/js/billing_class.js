$('#save_billing').on("click",function(e){
    e.preventDefault();
    $('#save_billing').button('loading');
    submit_ajax($('#save_billing').attr('href'),$("#billing-form").serialize());
    return false;
});

$('#payment-add-row').on('click', function(e){
    e.preventDefault();
    $('#PaymentReminderTable > tbody').append(add_row_html_payment);

    $('select.selectboxit').addClass('visible');
    $('select.selectboxit').selectBoxIt();

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
    console.log("jobtype" + $(this).val());
    populateInterval($(this).val());
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

function populateInterval(jobtype){

    //console.log("in populateJonInterval ");
    $("#billing-form [name='PaymentReminder[Interval]']").addClass('visible');
    var selectBox = $("#billing-form [name='PaymentReminder[Interval]']").selectBoxIt().data("selectBox-selectBoxIt");
    var selectBoxStartDay = $("#billing-form [name='PaymentReminder[StartDay]']").selectBoxIt().data("selectBox-selectBoxIt");
    $("#billing-form .JobStartDay").hide();
    var starttime = $("#billing-form .starttime");
    if(selectBox){
        selectBox.remove();
        // console.log("jobtype" + jobtype);
        if(jobtype == 'HOUR'){
            for(var i=1;i<'24';i++){
                selectBox.add({ value: i, text: i+" Hour"})
            }
            starttime.show();
        }else if(jobtype == 'MINUTE'){
            for(var i=1;i<60;i++){
                selectBox.add({ value: i, text: i+" Minute"})
            }
            starttime.hide();
            starttime.val('');
        }else if(jobtype == 'DAILY'){
            for(var i=1;i<'32';i++){
                selectBox.add({ value: i, text: i+" Day"})
            }
            //console.log("jobtype" + jobtype);
            starttime.show();
        }else if(jobtype == 'MONTHLY'){
            for(var i=1;i<13;i++){
                selectBox.add({ value: i, text: i+" Month"})
            }
            for(var i=1;i<'32';i++){
                selectBoxStartDay.add({ value: i, text: i+" Day"})
            }
            $("#billing-form .JobStartDay").show();
            starttime.show();
        }
    }
}
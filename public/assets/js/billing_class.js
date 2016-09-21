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
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        ajax_submit_script = true;
        var toastr_opts = {
            "closeButton": true,
            "debug": false,
            "positionClass": "toast-top-right",
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        //$(".btn.delete").unbind();
        $(".btn.delete").click(function (e) {

            response = confirm('Are you sure?');
            if( typeof $(this).attr("data-redirect")=='undefined'){
                $(this).attr("data-redirect",'{{ URL::previous() }}')
            }
            redirect = $(this).attr("data-redirect");
            if (response) {

                $.ajax({
                    url: $(this).attr("href"),
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $(".btn.delete").button('reset');
                        if (response.status == 'success') {
                            window.location = redirect
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    // Form data
                    //data: {},
                    cache: false,
                    contentType: false,
                    processData: false
                });


            }
            return false;

        });
        var form_action = '/{{$url}}';
        $("#{{$formID}}").submit(function () {

            var formData = new FormData($('#{{$formID}}')[0]);
           // redirect = ($(this).attr("data-redirect")=='undefined')?'':$(this).attr("data-redirect");


            $.ajax({
                url: baseurl+form_action,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    $(".save.btn").button('reset');

                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        $("#{{$formID}}").parents(".modal.fade").modal("hide");
                        @if(isset($update_url))
                            if( typeof  response.LastID != 'undefined' ){
                                form_action = '/{{$update_url}}';
                                form_action = form_action.replace( '{id}', response.LastID );
                            }
                        @endif
                        if( typeof data_table != 'undefined' ){
                            data_table.fnFilter('', 0);
                        }
                     } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },

                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            }).success(function(response){
                if(typeof response.warning != 'undefined' && response.warning != '') {
                    toastr.warning(response.warning, "Error", toastr_opts);
                }
               if (typeof ajax_form_success !== 'undefined' && $.isFunction(ajax_form_success)) {
                     ajax_form_success(response);
               }

            });
            return false;
        });
    });
</script>
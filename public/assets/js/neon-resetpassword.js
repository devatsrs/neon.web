/**
 *	Neon Login Script
 *
 *	Developed by Arlind Nushi - www.laborator.co
 */

var neonResetPassword = neonResetPassword || {};

;(function($, window, undefined)
{
    "use strict";

    $(document).ready(function()
    {
        neonResetPassword.$container = $("#form_reset_password");
        neonResetPassword.$steps = neonResetPassword.$container.find(".form-steps");

        // Login Form & Validation
        neonResetPassword.$container.validate({
            rules: {
                password: {
                    required: true
                },

                confirmpassword: {
                    required: true
                }

            },

            highlight: function(element){
                $(element).closest('.input-group').addClass('validate-has-error');
            },


            unhighlight: function(element)
            {
                $(element).closest('.input-group').removeClass('validate-has-error');
            },

            submitHandler: function(ev)
            {
                $(".login-page").addClass('logging-in');
                $(".form-login-error").slideUp('fast');

                // We consider its 30% completed form inputs are filled
                var postData = $("#form_reset_password").serializeArray();

                        // Send data to the server
                        $.ajax({
                            url: baseurl + '/doreset_password',
                            method: 'POST',
                            dataType: 'json',
                            data: postData,
                            error: function()
                            {
                                alert("An error occoured!");
                            },
                            success: function(response)
                            {

                                // From response you can fetch the data object retured
                                //var email = response.submitted_data.email;


                                // Form is fully completed, we update the percentage
                                //neonResetPassword.setPercentage(100);
                                console.log('success');

                                // We will give some time for the animation to finish, then execute the following procedures
                                setTimeout(function()
                                {
                                    console.log('success1');
                                    // Hide the description title
                                    $(".form-steps").slideUp();
                                    $(".description").slideUp();

                                    // Hide the register form (steps)
                                    //neonForgotPassword.$steps.slideUp('normal', function() {
                                        // Remove loging-in state
                                        console.log('success2');
                                        $(".login-page").removeClass('logging-in');
                                        console.log(response);
                                        if (response.status == 'failed') {
                                            console.log('error1');
                                            var error_message = response.message;
                                            $(".form-login-error").slideDown('normal');
                                            neonResetPassword.$steps.slideDown();
                                            $(".form-login-error").find("p").text(error_message)
                                        } else {
                                            console.log('successfinal');
                                            $(".form-resetpassword-success").slideDown('normal');
                                        }
                                 //   });

                                        // You can use the data returned from response variable


                                }, 1000);
                            }
                        });

            }
        });

    });

})(jQuery, window);
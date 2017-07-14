<script>

    jQuery(document).ready(function(){
        jQuery("#BarCode").focus(function() { jQuery(this).select(); } );
        jQuery("#BarCode").bind('paste', function(e) {
            getProductByBarCode(e.originalEvent.clipboardData.getData('text'));
            setTimeout(function () {
                jQuery("#BarCode").select();
            }, 50);
        });
    });

    function validateBarCodeInput(evt) {
        var theEvent = evt || window.event;
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
        var regex = /[]|\./;
        if(!regex.test(key)) {
            theEvent.returnValue = false;
            if(theEvent.preventDefault) theEvent.preventDefault();
        }
    }

    var status = "ready";

    function getProductByBarCode(BarCode) {
        if(status == "running")
            return false;
        jQuery('#BarCode').attr("maxlength","1000");
        jQuery('#BarCode').select();
        if(BarCode == "")
            BarCode = jQuery('#BarCode').val();

        var url = baseurl + '/products/get_product_by_barcode/'+BarCode;

        if(BarCode != '' && BarCode != null){
            status = "running";
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    status = "ready";
                    if (response.status == 'success') {
                        var isExist = 0;
                        jQuery('#InvoiceTable > tbody').find('tr').each(function(){
                            var crow = this;
                            jQuery(this).find('select[name^=InvoiceDetail]').each(function(){
                                if(jQuery(this).attr('name') == 'InvoiceDetail[ProductID][]') {
                                    if(jQuery(this).val() == response.data.ProductID) {
                                        isExist = 1;
                                        jQuery(crow).find('input[name^=InvoiceDetail]').each(function(){
                                            if(jQuery(this).attr('name') == 'InvoiceDetail[Qty][]'){
                                                jQuery(this).val(parseInt(jQuery(this).val()) + 1);

                                                var $this = jQuery(this);
                                                var $row = $this.parents("tr");
                                                cal_line_total($row);
                                            }
                                        });
                                    }
                                }
                            });
                        });

                        if(isExist == 0) {
                            jQuery('#add-row').click();

                            var row = jQuery('#InvoiceTable > tbody tr:last');

                            row.find('select[name^=InvoiceDetail]').each(function(){
                                if(jQuery(this).attr('name') == 'InvoiceDetail[ProductID][]') {
                                    jQuery(this).select2("val", response.data.ProductID);
                                }
                            });
                            row.find('textarea[name^=InvoiceDetail]').each(function(){
                                if(jQuery(this).attr('name') == 'InvoiceDetail[Description][]') {
                                    jQuery(this).val(response.data.Description);
                                }
                            });
                            row.find('input[name^=InvoiceDetail]').each(function(){
                                if(jQuery(this).attr('name') == 'InvoiceDetail[Price][]') {
                                    jQuery(this).val(response.data.Amount);
                                }
                            });

                            cal_line_total(row);
                        }
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }
    }
</script>
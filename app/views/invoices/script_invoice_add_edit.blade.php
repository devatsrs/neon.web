<script type="text/javascript">
/**
 * Created by deven on 07/07/2015.
 */
$(document).ready(function(){

    var USAGE = '{{Product::USAGE}}';
    var SUBSCRIPTION = '{{Product::SUBSCRIPTION}}';
    var ITEM = '{{Product::ITEM}}';
    var product_types = [];
     product_types['usage']= USAGE;
     product_types['subscription']= SUBSCRIPTION;
     product_types['item']= ITEM;

    function getTableFieldValue(controller_url, id,field ,callback){
        var get_url = baseurl +'/' + controller_url +'/'+id+'/get/'+field;
        $.get( get_url, callback, "json" );
    }

    /** Invoice Usage Functions
    * */

    function getCalculateInvoiceByProduct(product_type,productID,AccountID,qty,callback){
        post_data = {"product_type":product_type,"product_id":productID,"account_id":AccountID,"qty":qty};
        var _url = baseurl + '/invoice/calculate_total';
        $.post( _url, post_data, callback, "json" );
    }

    function getCalculateInvoiceBySubscription(product_type,productID,AccountID,qty,callback){
        post_data = {"product_type":product_type,"product_id":productID,"account_id":AccountID,"qty":qty};
        var _url = baseurl + '/invoice/calculate_total';
        $.post( _url, post_data, callback, "json" );
    }

    function getCalculateInvoiceByDuration(product_type,productID,AccountID,qty,start_date,end_date,InvoiceDetailID,callback){
        post_data = {"product_type":product_type, "product_id":productID,"account_id":AccountID,"qty":qty,"start_date":start_date,"end_date":end_date,"InvoiceDetailID":InvoiceDetailID};
        var _url = baseurl + '/invoice/calculate_total';
        $.post( _url, post_data, callback, "json" );
    }
    /** -----------------------------------*/


    function getInvoiceUsage(invoice_id,callback){
        post_data = { "invoice_id":invoice_id };
        var _url = baseurl + '/invoice/'+invoice_id+'/print_preview';
        $.get( _url, post_data, callback, "html" );
    }
    function sendInvoice(invoice_id,post_data,callback){
        //post_data = { "invoice_id":invoice_id };
        var _url = baseurl + '/invoice/'+invoice_id+'/send';
        $.post( _url, post_data, callback, "json");
    }

    $("#InvoiceTable").delegate( '.product_dropdown' ,'change',function (e) {
        var $this = $(this);
        var $row = $this.parents("tr");
        //console.log($this.val());
        var productID = $this.val();
        var AccountID = $('select[name=AccountID]').val();
        var InvoiceDetailID = $row.find('.InvoiceDetailID').val();
        var  selected_product_type = '';
        //selected_product_type = ($(this.options[this.selectedIndex]).closest('optgroup').prop('label')).toLowerCase();
        //$row.find('.ProductType').val(product_types[selected_product_type]);
        if( productID != ''  && parseInt(AccountID) > 0 ) {
            try{
                $row.find(".Qty").val(1);
                //console.log(productID);
                //console.log(gateway_product_ids);
                if(product_types[selected_product_type] == USAGE ) {

                    $('#add-new-invoice-duration-form').trigger('reset');
                    $('#add-new-invoice-duration-form .save.btn').button('reset');

                    $('#add-new-modal-invoice-duration').modal('show');
                    $('#add-new-invoice-duration-form').submit(function(e){
                        e.preventDefault();
                        setTimeout(function(e){
                            start_date = $('#add-new-invoice-duration-form input[name=start_date]').val();
                            end_date = $('#add-new-invoice-duration-form input[name=end_date]').val();
                            start_time = $('#add-new-invoice-duration-form input[name=start_time]').val();
                            end_time = $('#add-new-invoice-duration-form input[name=end_time]').val();
                            InvoiceDetailID = parseInt(InvoiceDetailID);

                            if(start_time != ''){
                                start_date += ' '+ start_time;
                            }
                            if(end_time != ''){
                                end_date += ' '+ end_time;
                            }
                            getCalculateInvoiceByDuration(selected_product_type,productID,AccountID,1,start_date,end_date,InvoiceDetailID,function(response){
                                $('#add-new-invoice-duration-form').trigger('reset');
                                $('#add-new-invoice-duration-form .save.btn').button('reset');
                                if(response.status =='success'){
                                    $('#add-new-modal-invoice-duration').modal('hide');
                                    $row.find("select.TaxRateID").selectBoxIt().data("selectBox-selectBoxIt").selectOption(response.product_tax_rate_id);
                                    $row.find(".descriptions").val(response.product_description);
                                    $row.find(".Price").val(response.product_amount);
                                    $row.find(".TaxAmount").val(response.product_total_tax_rate);
                                    $row.find(".LineTotal").val(response.sub_total);
                                    $row.find(".product_tax_title").text(response.sub_total);

                                    $row.find(".StartDate").attr("disabled",false);
                                    $row.find(".EndDate").attr("disabled",false);
                                    $row.find(".StartDate").val(start_date);
                                    $row.find(".EndDate").val(end_date);
                                    decimal_places = response.decimal_places;
                                    calculate_total();
                                }else{
                                    if(response.message !== undefined){
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                }
                            });
                        },1000);
                    });

                    return false;
                } else if(product_types[selected_product_type] == SUBSCRIPTION ) {

                    getCalculateInvoiceBySubscription(selected_product_type,productID,AccountID,1,function(response){
                        //console.log(response);
                        if(response.status =='success'){
                            $row.find("select.TaxRateID").selectBoxIt().data("selectBox-selectBoxIt").selectOption(response.product_tax_rate_id);
                            $row.find(".descriptions").val(response.product_description);
                            $row.find(".Price").val(response.product_amount);
                            $row.find(".TaxAmount").val(response.product_total_tax_rate);
                            $row.find(".LineTotal").val(response.sub_total);
                            $row.find(".product_tax_title").text(response.sub_total);
                            decimal_places = response.decimal_places;
                            $row.find(".StartDate").attr("disabled",true);
                            $row.find(".EndDate").attr("disabled",true);
                            calculate_total();
                        }else{
                            if(response.message !== undefined){
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        }
                    });
                    return false;


                }else{

                    getCalculateInvoiceByProduct('item',productID,AccountID,1,function(response){
                        //console.log(response);
                        if(response.status =='success'){
                            $row.find("select.TaxRateID").selectBoxIt().data("selectBox-selectBoxIt").selectOption(response.product_tax_rate_id);
                            $row.find(".descriptions").val(response.product_description);
                            $row.find(".Price").val(response.product_amount);
                            $row.find(".TaxAmount").val(response.product_total_tax_rate);
                            $row.find(".LineTotal").val(response.sub_total);
                            $row.find(".product_tax_title").text(response.sub_total);
                            decimal_places = response.decimal_places;
                            $row.find(".StartDate").attr("disabled",true);
                            $row.find(".EndDate").attr("disabled",true);
                            calculate_total();
                        }else{
                            if(response.message !== undefined){
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        }
                    });
                    return false;
                }


            }catch (e){
                console.log(e);
            }
        }
    });
    $("#InvoiceTable").delegate( '.Price , .Qty , .Discount, .TaxRateID' ,'change',function (e) {
        var $this = $(this);
        var $row = $this.parents("tr");
        cal_line_total($row);
        calculate_total();
    });
    $("input[name=discount]").change(function (e) {
        calculate_total();
    });


    $('#add-row').on('click', function(e){
        e.preventDefault();
        $('#InvoiceTable > tbody').append(add_row_html);

        $('select.selectboxit').addClass('visible');
        $('select.selectboxit').selectBoxIt();

        $('select.select2').addClass('visible');
        $('select.select2').select2();
    });

    $('#InvoiceTable > tbody').on('click','.remove-row', function(e){
        e.preventDefault();
        var row = $(this).parent().parent();
        row.remove();
        calculate_total();
    });

    function calculate_total(){

        var grand_total = 0;
        var total_tax = 0;
        var total_discount = 0.0;
        var taxTitle = 'VAT';

        $('#InvoiceTable tbody tr td .TaxAmount').each(function(i, el){
            var $this = $(el);
            if($this.val() != ''){
                total_tax  = eval(parseFloat(total_tax) + parseFloat($this.val().replace(',/g','')));
            }
        });
        $('#InvoiceTable tbody tr td .LineTotal').each(function(i, el){
            var $this = $(el);
            if($this.val() != ''){
                //decimal_places = get_decimal_places($this.val())
                grand_total = eval(parseFloat(grand_total) + parseFloat($this.val().replace(/,/g,'')));
            }
        });

      /*  $('#InvoiceTable tbody tr td .Discount').each(function(i, el){
            var $this = $(el);
            if($this.val() != ''){
                total_discount = eval(parseFloat(total_discount) + parseFloat($this.val().replace(/,/g,'')));
            }
        });*/

       /* $('#InvoiceTable tbody tr td .TaxRateID').each(function(i, el){
            var $this = $(el);
            if($this.val() != ''){
                taxTitle = $(".TaxRateID option:selected").text();
                if(taxTitle =='Select a Tax Rate'){
                    taxTitle='VAT';
                }
            }
        });*/


        var CurrencySymbol = $("input[name=CurrencyCode]").val();


        $('input[name=SubTotal]').val(grand_total.toFixed(decimal_places));
        $('input[name=TotalTax]').val(total_tax.toFixed(decimal_places));
        total = eval(grand_total + total_tax).toFixed(decimal_places);

        //$('input[name=TotalDiscount]').val(total_discount.toFixed(decimal_places));
        $('input[name=GrandTotal]').val(total);

      //  $(".product_tax_title").text(taxTitle);

    }

    function cal_line_total(obj){


        var price = parseFloat(obj.find(".Price").val().replace(/,/g,''));
        //decimal_places = get_decimal_places(price);

        var qty = parseInt(obj.find(".Qty").val());
        //var discount = parseFloat(obj.find(".Discount").val().replace(/,/g,''));
		var discount = 0;
        var taxAmount = parseFloat(obj.find(".TaxRateID option:selected").attr("data-amount").replace(/,/g,''));
        var tax = parseFloat( (price * qty * taxAmount)/100 );
        obj.find('.TaxAmount').val(tax.toFixed(decimal_places));
        var line_total = parseFloat( parseFloat( parseFloat(price * qty) - discount )) ;

        obj.find('.LineTotal').val(line_total.toFixed(decimal_places));

        calculate_total();
    }


    $(".send-invoice.btn").click( function (e) {
        $('#send-modal-invoice').find(".modal-body").html("Loading Content...");
        var ajaxurl = "/invoice/"+invoice_id+"/invoice_email";
        showAjaxModal(ajaxurl,'send-modal-invoice');
        $("#send-invoice-form")[0].reset();
        $('#send-modal-invoice').modal('show');
    });
    $('select.TaxRateID').on( "change",function(e){

        var taxTitle =  $(this).find(":selected").text() ;
        //var taxTitle = $(".TaxRateID option:selected").text();

        if(taxTitle =='Select a Tax Rate'){
            taxTitle='VAT';
        }
        $(".product_tax_title").text(taxTitle);
    });

    $("select[name=AccountID]").change( function (e) {
        url = baseurl + "/invoice/get_account_info";
        $this = $(this);
        data = {account_id:$this.val()}
        if($this.val() > 0){
            ajax_json(url,data,function(response){
                if ( typeof response.status != undefined &&  response.status == 'failed') {
                    toastr.error(response.message, "Error", toastr_opts);
                } else {
                    $("#Account_Address").html(response.Address);
                    $("input[name=CurrencyCode]").val(response.Currency);
                    $("input[name=CurrencyID]").val(response.CurrencyId);
                    $("input[name=InvoiceTemplateID]").val(response.InvoiceTemplateID);
                    $("[name=Terms]").val(response.Terms);
                    $("[name=FooterTerm]").val(response.FooterTerm);
                    InvoiceTemplateID = response.InvoiceTemplateID;
                }

            });
        }

    });
    //Calculate Total
    calculate_total();

    $("#send-invoice-form").submit(function(e){
        e.preventDefault();
        var post_data  = $(this).serialize();
        var InvoiceID = $(this).find("[name=InvoiceID]").val();
        var _url = baseurl + '/invoice/'+InvoiceID+'/send';
        $.post( _url, post_data, function(response){
            $(".btn.send").button('reset');
            if (response.status == 'success') {
                toastr.success(response.message, "Success", toastr_opts);
            } else {
                toastr.error(response.message, "Error", toastr_opts);
            }
        }, "json");
    });
});
</script>
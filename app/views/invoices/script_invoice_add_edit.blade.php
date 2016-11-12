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
                                    $row.find("select.TaxRateID").val(response.product_tax_rate_id).trigger("change");
									$row.find("select.TaxRateID2").val(response.product_tax_rate_id).trigger("change");
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
                            $row.find("select.TaxRateID").val(response.product_tax_rate_id).trigger("change");
							$row.find("select.TaxRateID2").val(response.product_tax_rate_id).trigger("change");
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
                            $row.find("select.TaxRateID").val(response.product_tax_rate_id).trigger("change");
							$row.find("select.TaxRateID2").val(response.product_tax_rate_id).trigger("change");
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
    $("#InvoiceTable").delegate( '.Price , .Qty , .Discount, .TaxRateID, .TaxRateID2' ,'change',function (e) {
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

        /*$('select.selectboxit').addClass('visible');
        $('select.selectboxit').selectBoxIt();*/

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

        var grand_total 	= 	0;
        var total_tax 		= 	0;
        var total_discount 	= 	0.0;
        var taxTitle 		= 	'VAT';
		var Tax_type		=	new Array();
		var Tax_type_title	=	new Array();

        $('#InvoiceTable tbody tr td .TaxAmount').each(function(i, el){
            var $this = $(el);
            if($this.val() != ''){
                total_tax  = eval(parseFloat(total_tax) + parseFloat($this.val().replace(',/g','')));
            }
        });
		
		$('#InvoiceTable tbody tr td select.Taxentity').each(function(i, el){
            var $this 	=	 $(el);
			var tt		=	 $('option:selected', this);
            if($this.val() != '' && $this.val() != 0)
			{ 
			//Tax_type[$this.val()] = 
                //total_tax  = eval(parseFloat(total_tax) + parseFloat($this.val().replace(',/g','')));
				
				  var obj 		 =   $(el).parent().parent();
				  var price 	 = 	 parseFloat(obj.find(".Price").val().replace(/,/g,''));	
 			      var qty 		 =	 parseInt(obj.find(".Qty").val());
				  
				  var taxAmount  =   parseFloat(tt.attr("data-amount").replace(/,/g,''));				
				  var flatstatus = 	 parseFloat(tt.attr("data-flatstatus").replace(/,/g,''));
				  var titleTax	 =   tt.text();
				  
				  if(flatstatus == 1){
						var tax = parseFloat( ( taxAmount) );
				   }else{
						var tax = parseFloat( (price * qty * taxAmount)/100 );
				   }				
             }			
			 if(Tax_type[$this.val()]!= null){
				 Tax_type[$this.val()]		 = Tax_type[$this.val()]+tax;
			 }else{
				 Tax_type[$this.val()]		 = 		tax;
			 }
			 Tax_type_title[$this.val()] = titleTax;
			 //alert(Tax_type[$this.val()]);
        });
		
		//alert(Tax_type);
	
		$('.tax_rows_invoice').remove();
		
		
		Tax_type.forEach(AddTaxRows);
		function AddTaxRows(value, index) {
			if(value != null){
				$('.grand_total_invoice').before('<tr class="tax_rows_invoice"><td>'+Tax_type_title[index]+'</td><td><input class="form-control text-right" readonly="readonly" name="Tax['+index+']" value="'+value.toFixed(decimal_places)+'" type="text">  </td> </tr>');	
			}
		}

		/*for(loop=1;loop<=Tax_type.length;loop++)
		{	
			//alert(Tax_type[loop]);
			if(Tax_type[loop] != null){
			$('.grand_total_invoice').before('<tr class="tax_row_'+loop+' tax_rows_invoice"><td>'+Tax_type_title[loop]+'</td><td><input class="form-control text-right" readonly="readonly" name="Tax['+Tax_type_title[loop]+']" value="'+Tax_type[loop].toFixed(decimal_places)+'" type="text">  </td> </tr>');	
			}
		}*/
		
		
        $('#InvoiceTable tbody tr td .LineTotal').each(function(i, el){
            var $this = $(el);
            if($this.val() != ''){
                //decimal_places = get_decimal_places($this.val())
                grand_total = eval(parseFloat(grand_total) + parseFloat($this.val().replace(/,/g,'')));
            }
        });
		
		//tax_rows_invoice

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
        var taxAmount  =  parseFloat(obj.find(".TaxRateID option:selected").attr("data-amount").replace(/,/g,''));
		
        var flatstatus = parseFloat(obj.find(".TaxRateID option:selected").attr("data-flatstatus").replace(/,/g,''));
        if(flatstatus == 1){
            var tax = parseFloat( ( taxAmount) );
        }else{
            var tax = parseFloat( (price * qty * taxAmount)/100 );
        }
		
		var taxAmount2 =  parseFloat(obj.find(".TaxRateID2 option:selected").attr("data-amount").replace(/,/g,''));
		
		 var flatstatus2 = parseFloat(obj.find(".TaxRateID2 option:selected").attr("data-flatstatus").replace(/,/g,''));
        if(flatstatus2 == 1){
            var tax2 = parseFloat( ( taxAmount2) );
        }else{
            var tax2 = parseFloat( (price * qty * taxAmount2)/100 );
        }
		
		var tax1val = obj.find("select.TaxRateID").val();
		var tax2val = obj.find("select.TaxRateID2").val(); 
		if(tax1val > 0 &&  (tax1val == tax2val)){
			toastr.error(obj.find(".TaxRateID2 option:selected").text()+" already applied", "Error", toastr_opts);
		}
		
		var tax_final  = 	parseFloat(tax+tax2);
		tax_final  	   = 	tax_final.toFixed(decimal_places);
        obj.find('.TaxAmount').val(tax_final);
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

        var rowCount = $('#InvoiceTable tbody tr').length;
        if(taxTitle =='Select a Tax Rate'){
            taxTitle='VAT';
        }else if(rowCount >1) {
            taxTitle='Total Tax';
        }
        $(".product_tax_title").text(taxTitle);
    });
	
	  $('select.TaxRateID2').on( "change",function(e){

        var taxTitle =  $(this).find(":selected").text() ;
        //var taxTitle = $(".TaxRateID option:selected").text();

        var rowCount = $('#InvoiceTable tbody tr').length;
        if(taxTitle =='Select a Tax Rate'){
            taxTitle='VAT';
        }else if(rowCount >1) {
            taxTitle='Total Tax';
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
                    $("#Account_Address").html('');
                    $("input[name=CurrencyCode]").val('');
                    $("input[name=CurrencyID]").val('');
                    $("input[name=InvoiceTemplateID]").val('');
                    $("[name=Terms]").val('');
                    $("[name=FooterTerm]").val('');
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
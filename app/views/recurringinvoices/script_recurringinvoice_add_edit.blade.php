<script type="text/javascript">

$(document).ready(function(){

    function getTableFieldValue(controller_url, id,field ,callback)
	{
        var get_url = baseurl +'/' + controller_url +'/'+id+'/get/'+field;
        $.get( get_url, callback, "json" );
    }

    function sendRecurringInvoice(recurringinvoice_id,post_data,callback){
        var _url = baseurl + '/recurringprofiles/'+recurringinvoice_id+'/send';
        $.post( _url, post_data, callback, "json");
    }

    $("#RecurringInvoiceTable").delegate( '.product_dropdown' ,'change',function (e) {
        var $this = $(this);
        var $row = $this.parents("tr");
        var productID = $this.val();
        var AccountID = $('select[name=AccountID]').val();
        var BillingClassID = $('select[name="BillingClassID"]').val();
        if( productID != '' && BillingClassID!=''  && parseInt(AccountID) > 0 ) {
            try {
                $row.find(".Qty").val(1);
                post_data = {"product_id": productID, "account_id": AccountID, "BillingClassID":BillingClassID, "qty": 1};
                var _url = baseurl + '/recurringprofiles/calculate_total';
                $.post(_url, post_data, function (response) {
                    if (response.status == 'success') {
                        $row.find(".descriptions").val(response.product_description);
                        $row.find(".Price").val(response.product_amount);
                        $row.find(".TaxAmount").val(response.product_total_tax_rate);
                        $row.find(".LineTotal").val(response.sub_total);
                        decimal_places = response.decimal_places;
                        $('.Taxentity').trigger('change');
                        $("textarea.autogrow").autosize();
                        calculate_total();
                    } else {
                        if (response.message !== undefined) {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }
                }, "json");

                return false;

            } catch (e) {
                console.log(e);
            }
        }
    });
    $("#RecurringInvoiceTable").delegate( '.Price , .Qty , .Discount, .TaxRateID , .TaxRateID2','change',function (e) {
		
        var $this = $(this);
        var $row = $this.parents("tr");
        cal_line_total($row);
        calculate_total();
    });
    $("input[name=discount]").change(function (e) {
        calculate_total();
    });
	
	
	 $(".recurringinvoice_tax_add").click(function (e) {
	   e.preventDefault();
	   	var index_count = $('.all_tax_row').length+1;
        var	recurringinvoice_tax_html_final  = '<tr class="all_tax_row RecurringInvoiceTaxestr'+index_count+' ">'+recurringinvoice_tax_html+"</tr>";
		$('.gross_total_recurringinvoice').before(recurringinvoice_tax_html_final);
		$('select.select2').addClass('visible');
        $('select.select2').select2();
		calculate_total();
    });
	
	 $(document).on('click','.recurringinvoice_tax_remove', function(e){
	    e.preventDefault();
        var row = $(this).parent().parent();
        row.remove();  
		calculate_total();
    });

    $('#add-row').on('click', function(e){
        e.preventDefault();
        $('#RecurringInvoiceTable > tbody').append(add_row_html);

        /*$('select.selectboxit').addClass('visible');
        $('select.selectboxit').selectBoxIt();*/

        $('select.select2').addClass('visible');
        $('select.select2').select2();
		$("textarea.autogrow").autosize();
    });

    $('#RecurringInvoiceTable > tbody').on('click','.remove-row', function(e){
        e.preventDefault();
        var row = $(this).parent().parent();
        row.remove();
        calculate_total();
    });
	
	$(document).on('change','.RecurringInvoiceTaxesFld', function(e){
        e.preventDefault();
        var row = $(this).parent().parent();
        calculate_total();
    });
	

    function calculate_total(){

        var grand_total = 0;
        var total_tax = 0;
        var total_discount = 0.0;		
		var Tax_type		=	new Array();
		var Tax_type_title	=	new Array();

        $('#RecurringInvoiceTable tbody tr td .TaxAmount').each(function(i, el){
            var $this = $(el);
            if($this.val() != ''){
                total_tax  = eval(parseFloat(total_tax) + parseFloat($this.val().replace(/,/g,'')));
            }
        });
		
		$('#RecurringInvoiceTable tbody tr td select.Taxentity').each(function(i, el){
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
        });
		
	
		$('.tax_rows_recurringinvoice').remove();
		
		
		Tax_type.forEach(AddTaxRows);
		function AddTaxRows(value, index) {
			if(value != null){
				$('.grand_total_recurringinvoice').before('<tr class="tax_rows_recurringinvoice"><td>'+Tax_type_title[index]+'</td><td><input class="form-control text-right" readonly="readonly" name="Tax['+index+']" value="'+value.toFixed(decimal_places)+'" type="text">  </td> </tr>');
			}
		}
		
        $('#RecurringInvoiceTable tbody tr td .LineTotal').each(function(i, el){
            var $this = $(el);
            if($this.val() != ''){
                //decimal_places = get_decimal_places($this.val())
                grand_total = eval(parseFloat(grand_total) + parseFloat($this.val().replace(/,/g,'')));
            }
        });

        $('input[name=SubTotal]').val(grand_total.toFixed(decimal_places));
        $('input[name=TotalTax]').val(total_tax.toFixed(decimal_places));
        total = eval(grand_total + total_tax).toFixed(decimal_places);

        //$('input[name=TotalDiscount]').val(total_discount.toFixed(decimal_places));
        $('input[name=GrandTotal]').val(total);
		
		recurringinvoice_main_total_tax = 0; var taxes_array = new Array();
	   $('.RecurringInvoiceTaxesFld').each(function(index, element) {
		   
            var $this 	=	 $(element);
			var tt		=	 $('option:selected', this);
           
		   
		    if($this.val() != '' && $this.val() != 0)
			{ 
				
				  var tax_current_id    =   $this.val();	
				  var tax_already_found =   taxes_array.indexOf(tax_current_id);			
				  
				  if(tax_already_found!=-1){
					toastr.error(tt.text()+" already applied", "Error", toastr_opts);	 
				  }
				  
				  taxes_array.push(tax_current_id);					  
				  
				  
				  var obj 			  =   $(element).parent().parent();
				  var taxAmount  	  =   parseFloat(tt.attr("data-amount").replace(/,/g,''));				
				  var flatstatus 	  =   parseFloat(tt.attr("data-flatstatus").replace(/,/g,''));
				  
				  if(flatstatus == 1){
						var tax = parseFloat( ( taxAmount) );
				   }else{
						var tax = parseFloat( (total * taxAmount)/100 );
				   }
				   
				   obj.find('.RecurringInvoiceTaxesValue').val(tax.toFixed(decimal_places));
				   recurringinvoice_main_total_tax = parseFloat(recurringinvoice_main_total_tax)+parseFloat(tax);
            }
			else
			{
				  var obj 		 =   $(element).parent().parent();
				   obj.find('.RecurringInvoiceTaxesValue').val(0);
			}
    	});
		var gross_total = parseFloat(total)+recurringinvoice_main_total_tax;
		 $('input[name=GrandTotalRecurringInvoice]').val(gross_total.toFixed(decimal_places));

    }
    function cal_line_total(obj){


        var price = parseFloat(obj.find(".Price").val().replace(/,/g,''));
        //decimal_places = get_decimal_places(price);

        var qty = parseInt(obj.find(".Qty").val());
       // var discount = parseFloat(obj.find(".Discount").val().replace(/,/g,''));
	 var  discount = 0;
        var taxAmount = parseFloat(obj.find(".TaxRateID option:selected").attr("data-amount").replace(/,/g,''));
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
			toastr.error(obj.find(".TaxRateID2 option:selected").text()+" already applied on product", "Error", toastr_opts);
		}
		
		var tax_final  = 	parseFloat(tax+tax2);
		tax_final  	   = 	tax_final.toFixed(decimal_places);
		
		
        obj.find('.TaxAmount').val(tax_final);
        var line_total = parseFloat( parseFloat( parseFloat(price * qty) - discount )) ;

        obj.find('.LineTotal').val(line_total.toFixed(decimal_places));
        calculate_total();
    }
    $('select.TaxRateID').on( "change",function(e){

        var taxTitle =  $(this).find(":selected").text() ;

        var rowCount = $('#RecurringInvoiceTable tbody tr').length;
        if(taxTitle =='Select a Tax Rate'){
            taxTitle='VAT';
        }else if(rowCount >1) {
            taxTitle='Total Tax';
        }
        $(".product_tax_title").text(taxTitle);
    });

    $(".send-recurringinvoice.btn").click( function (e) {
        $('#send-modal-recurringinvoice').find(".modal-body").html("Loading Content...");
        var ajaxurl = baseurl + "/recurringprofiles/sendinvoice";
        var formData = new FormData($('#send-recurringinvoice-form')[0]);
        showAjaxScript(ajaxurl,formData,function(response){
            if (response.status == 'success') {
                if(response.invoiceID>0) {
                    var send_url = (baseurl + "/invoice/{id}/invoice_email").replace("{id}", response.invoiceID);
                    showAjaxScript(send_url, formData, function (response) {
                        $('#send-modal-recurringinvoice .modal-body').html(response);
                        $('#send-modal-recurringinvoice').modal('show');
                    }, 'html');
                }
            }else{
                toastr.error(response.message, "Error", toastr_opts);
            }
        },'json');
    });

    $("select[name=AccountID]").change( function (e) {
        url = baseurl + "/recurringprofiles/get_account_info";
        $this = $(this);
        data = {account_id:$this.val()}
        if($this.val() > 0){
            ajax_json(url,data,function(response){
                if ( typeof response.status != undefined &&  response.status == 'failed') {
                    toastr.error(response.message, "Error", toastr_opts);
                    $("#Account_Address").html('');
                    $("input[name=CurrencyCode]").val('');
                    $("input[name=CurrencyID]").val('');
                } else {
                    $("#Account_Address").html(response.Address);
                    $("input[name=CurrencyCode]").val(response.Currency);
                    $("input[name=CurrencyID]").val(response.CurrencyId);
                    $('select[name="BillingClassID"]').val(response.BillingClassID).trigger('change');
                }

            });
        }

    });

    $("select[name=BillingClassID]").change( function (e) {
        url = baseurl + "/recurringprofiles/get_billingclassinfo_info";
        $this = $(this);
        var AccountID = $('select[name=AccountID]').val();
        var BillingClassID = $this.val();
        data = 'BillingClassID='+BillingClassID+'&AccountID='+AccountID;
        if($this.val() > 0){
            ajax_json(url,data,function(response){
                if ( typeof response.status != undefined &&  response.status == 'failed') {
                    toastr.error(response.message, "Error", toastr_opts);
                    $("[name=Terms]").val('');
                    $("[name=FooterTerm]").val('');
                } else {
                    if(response.InvoiceToAddress!=''){
                        $("#Account_Address").html(response.InvoiceToAddress);
                    }
                    $("[name=Terms]").val(response.Terms);
                    $("[name=FooterTerm]").val(response.FooterTerm);
                    add_recurringinvoice_tax(response.TaxRate);
                }

            });
        }else{
            $('.all_tax_row').remove();
        }
    });
	
	function add_recurringinvoice_tax(AccountTaxRate){
		$('.all_tax_row').remove();
		if(AccountTaxRate.length>0){			
			AccountTaxRate.forEach(function(entry,index) {				
				if(index==0){
					$('.RecurringInvoiceTaxesFldFirst').val(entry);
					var change = $('.RecurringInvoiceTaxesFldFirst');
					change.trigger('change');
				}
				else
				{			
				  var	recurringinvoice_tax_html_final  = '<tr class="all_tax_row RecurringInvoiceTaxestr'+index+' ">'+recurringinvoice_tax_html+"</tr>";
				  $('.gross_total_recurringinvoice').before(recurringinvoice_tax_html_final);
				  var current_obj = $('.RecurringInvoiceTaxestr'+index).find('.RecurringInvoiceTaxesFld');
				  current_obj.addClass('RecurringInvoiceTaxesFld'+index);
				  current_obj.val(entry);
				  current_obj.addClass('visible');
				  current_obj.select2();
				  current_obj.trigger('change');
				}
			});		
			 calculate_total();
		}	
	}
	
    //Calculate Total
    calculate_total();

    $("#send-recurringinvoice-form").submit(function(e){
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
	$("textarea.autogrow").autosize();
    $("#recurringinvoice-from [name='RecurringInvoice[Time]']").change(function(){
        populateIntervalInvoice($(this).val(),'RecurringInvoice','recurringinvoice-from');
    });

    $('select[name="BillingCycleType"]').on( "change",function(e){
        var selection = $(this).val();
        $(".billing_options input, .billing_options select").attr("disabled", "disabled");// This is to avoid not posting same name hidden elements
        $(".billing_options").hide();
        console.log(selection);
        switch (selection){
            case "weekly":
                $("#billing_cycle_weekly").show();
                $("#billing_cycle_weekly select").removeAttr("disabled");
                break;
            case "monthly_anniversary":
                $("#billing_cycle_monthly_anniversary").show();
                $("#billing_cycle_monthly_anniversary input").removeAttr("disabled");
                break;
            case "in_specific_days":
                $("#billing_cycle_in_specific_days").show();
                $("#billing_cycle_in_specific_days input").removeAttr("disabled");
                break;
            case "subscription":
                $("#billing_cycle_subscription").show();
                $("#billing_cycle_subscription input").removeAttr("disabled");
                break;
        }
    });


    $('select[name="BillingCycleType"]').trigger( "change" );
});
</script>
<style>
#RecurringInvoiceTable.table > tbody > tr > td > div > a > span.select2-chosen { width:110px;}
</style>
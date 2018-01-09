<script type="text/javascript">
/**
* Created by umer on 14/03/2016.
*/

$(document).ready(function(){
	/*show_summernote($("[name=Terms]"),{});
	show_summernote($("[name=FooterTerm]"),{});*/
	show_summerinvoicetemplate($("[name=Terms]"));
	show_summerinvoicetemplate($("[name=FooterTerm]"));	
	
    var USAGE 						= 	'{{Product::USAGE}}';
    var SUBSCRIPTION 				= 	'{{Product::SUBSCRIPTION}}';
    var ITEM 						= 	'{{Product::ITEM}}';
    var txtUSAGE                    =   '{{ucFirst(Product::$TypetoProducts[Product::USAGE])}}';
    var txtSUBSCRIPTION             =   '{{ucFirst(Product::$TypetoProducts[Product::SUBSCRIPTION])}}';
    var txtITEM                     =   '{{ucFirst(Product::$TypetoProducts[Product::ITEM])}}';
    var product_types 				= 	[];
     product_types['usage']			= 	USAGE;
     product_types['subscription']	= 	SUBSCRIPTION;
     product_types['item']			= 	ITEM;


    function getTableFieldValue(controller_url, id,field ,callback)
	{
        var get_url = baseurl +'/' + controller_url +'/'+id+'/get/'+field;
        $.get( get_url, callback, "json" );
    }

    /** Estimate Usage Functions
    * */

    function getCalculateEstimateByProduct(product_type,productID,AccountID,qty,callback){
		var AccountBillingClassID = $('#AccountBillingClassID').val();
        post_data = {"product_type":product_type,"product_id":productID,"account_id":AccountID,"qty":qty,"BillingClassID":AccountBillingClassID};
        var _url = baseurl + '/estimate/calculate_total';
        $.post( _url, post_data, callback, "json" );
    }

    function getCalculateEstimateBySubscription(product_type,productID,AccountID,qty,callback){
		var AccountBillingClassID = $('#AccountBillingClassID').val();
        post_data = {"product_type":product_type,"product_id":productID,"account_id":AccountID,"qty":qty,"BillingClassID":AccountBillingClassID};
        var _url = baseurl + '/estimate/calculate_total';
        $.post( _url, post_data, callback, "json" );
    }

    function getCalculateEstimateByDuration(product_type,productID,AccountID,qty,start_date,end_date,EstimateDetailID,callback){
		var AccountBillingClassID = $('#AccountBillingClassID').val();
        post_data = {"product_type":product_type, "product_id":productID,"account_id":AccountID,"qty":qty,"start_date":start_date,"end_date":end_date,"EstimateDetailID":EstimateDetailID,"BillingClassID":AccountBillingClassID};
        var _url = baseurl + '/estimate/calculate_total';
        $.post( _url, post_data, callback, "json" );
    }
    /** -----------------------------------*/


    function getEstimateUsage(estimate_id,callback){
        post_data = { "estimate_id":estimate_id };
        var _url = baseurl + '/estimate/'+estimate_id+'/print_preview';
        $.get( _url, post_data, callback, "html" );
    }
    function sendEstimate(estimate_id,post_data,callback){
        //post_data = { "estimate_id":estimate_id };
        var _url = baseurl + '/estimate/'+estimate_id+'/send';
        $.post( _url, post_data, callback, "json");
    }

    $("#EstimateTable").delegate( '.product_dropdown' ,'change',function (e) {		
        var $this = $(this);
        var optgroup = $(this).find(":selected").parents('optgroup');
        var $row = $this.parents("tr");
        var productID = $this.val().split('-')[1];
        var AccountID = $('select[name=AccountID]').val();		
        var EstimateDetailID = $row.find('.EstimateDetailID').val();
        //var  selected_product_type = optgroup.prop('label')==txtSUBSCRIPTION?SUBSCRIPTION:'';
		var  selected_product_type =  $(this).find(":selected").attr('item_subscription_type'); 		
        //selected_product_type = ($(this.options[this.selectedIndex]).closest('optgroup').prop('label')).toLowerCase();
        //$row.find('.ProductType').val(product_types[selected_product_type]);
        if( productID != ''  && parseInt(AccountID) > 0 ) {
            try{
                $row.find(".Qty").val(1);
                //console.log(productID);
                //console.log(gateway_product_ids);
                if(product_types[selected_product_type] == USAGE ) {

                    $('#add-new-estimate-duration-form').trigger('reset');
                    $('#add-new-estimate-duration-form .save.btn').button('reset');

                    $('#add-new-modal-estimate-duration').modal('show');
                    $('#add-new-estimate-duration-form').submit(function(e){
                        e.preventDefault();
                        setTimeout(function(e){
                            start_date = $('#add-new-estimate-duration-form input[name=start_date]').val();
                            end_date = $('#add-new-estimate-duration-form input[name=end_date]').val();
                            start_time = $('#add-new-estimate-duration-form input[name=start_time]').val();
                            end_time = $('#add-new-estimate-duration-form input[name=end_time]').val();
                            EstimateDetailID = parseInt(EstimateDetailID);

                            if(start_time != ''){
                                start_date += ' '+ start_time;
                            }
                            if(end_time != ''){
                                end_date += ' '+ end_time;
                            }
                            getCalculateEstimateByDuration(selected_product_type,productID,AccountID,1,start_date,end_date,EstimateDetailID,function(response){
                                $('#add-new-estimate-duration-form').trigger('reset');
                                $('#add-new-estimate-duration-form .save.btn').button('reset');
                                if(response.status =='success'){
                                    $('#add-new-modal-estimate-duration').modal('hide');
                                    //$row.find("select.TaxRateID").val(response.product_tax_rate_id).trigger("change");
									//$row.find("select.TaxRateID2").val(response.product_tax_rate_id).trigger("change");
                                    $row.find(".descriptions").val(response.product_description);
                                    $row.find(".Price").val(response.product_amount);
                                    $row.find(".TaxAmount").val(response.product_total_tax_rate);
                                    $row.find(".LineTotal").val(response.sub_total);

                                    $row.find(".StartDate").attr("disabled",false);
                                    $row.find(".EndDate").attr("disabled",false);
                                    $row.find(".StartDate").val(start_date);
                                    $row.find(".EndDate").val(end_date);
                                    decimal_places = response.decimal_places;
									$('.Taxentity').trigger('change');
									$("textarea.autogrow").autosize();
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
                } else if(selected_product_type == SUBSCRIPTION ) {
                    getCalculateEstimateBySubscription('subscription',productID,AccountID,1,function(response){
                        //console.log(response);
                        if(response.status =='success'){
                          //  $row.find("select.TaxRateID").val(response.product_tax_rate_id).trigger("change");
							//$row.find("select.TaxRateID2").val(response.product_tax_rate_id).trigger("change");
                            $row.find(".descriptions").val(response.product_description);
                            $row.find(".Price").val(response.product_amount);
                            $row.find(".TaxAmount").val(response.product_total_tax_rate);
                            $row.find(".LineTotal").val(response.sub_total);
                            decimal_places = response.decimal_places;
                            //$row.find(".StartDate").attr("disabled",true);
                            //$row.find(".EndDate").attr("disabled",true);
                            $row.find(".ProductType").val(selected_product_type);

							$('.Taxentity').trigger('change');
							$("textarea.autogrow").autosize();
                            calculate_total();
                        }else{
                            if(response.message !== undefined){
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        }
                    });
		            return false;


                }else{

                    getCalculateEstimateByProduct('item',productID,AccountID,1,function(response){
                        //console.log(response);
                        if(response.status =='success'){
                          //  $row.find("select.TaxRateID").val(response.product_tax_rate_id).trigger("change");
							//$row.find("select.TaxRateID2").val(response.product_tax_rate_id).trigger("change");
                            $row.find(".descriptions").val(response.product_description);
                            $row.find(".Price").val(response.product_amount);
                            $row.find(".TaxAmount").val(response.product_total_tax_rate);
                            $row.find(".LineTotal").val(response.sub_total);
                            decimal_places = response.decimal_places;
                            $row.find(".StartDate").attr("disabled",true);
                            $row.find(".EndDate").attr("disabled",true);                            
                            $row.find(".ProductType").val(selected_product_type);
							$('.Taxentity').trigger('change');
							$("textarea.autogrow").autosize();
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
    $("#EstimateTable").delegate( '.Price , .Qty , .Discount, .TaxRateID , .TaxRateID2','change',function (e) {
		
        var $this = $(this);
        var $row = $this.parents("tr");
        cal_line_total($row);
        calculate_total();
    });
    $("input[name=discount]").change(function (e) {
        calculate_total();
    });
	
	
	 $(".estimate_tax_add").click(function (e) {
	   e.preventDefault();
	   	var index_count = $('.all_tax_row').length+1;
        var	estimate_tax_html_final  = '<tr class="all_tax_row EstimateTaxestr'+index_count+' ">'+estimate_tax_html+"</tr>";
		$('.gross_total_estimate').before(estimate_tax_html_final);	
		$('select.select2').addClass('visible');
        $('select.select2').select2();
		calculate_total();
    });
	
	 $(document).on('click','.estimate_tax_remove', function(e){
	    e.preventDefault();
        var row = $(this).parent().parent();
        row.remove();  
		calculate_total();
    });

    $('#add-row').on('click', function(e){
        e.preventDefault();
        var itemrow = $('#rowContainer .itemrow').clone();
        itemrow.removeAttr('class');
        itemrow.find('select.select22').each(function(i,item){
            buildselect2(item);
        });
        $('#EstimateTable > tbody').append(itemrow);

        /*$('select.selectboxit').addClass('visible');
        $('select.selectboxit').selectBoxIt();*/

        //$('select.select2').addClass('visible');
        //$('select.select2').select2();
        nicescroll();
		$("textarea.autogrow").autosize();
    });

    $('#EstimateTable > tbody').on('click','.remove-row', function(e){
        e.preventDefault();
        var row = $(this).parent().parent();
        row.remove();
        calculate_total();
    });
	
	$(document).on('change','.EstimateTaxesFld', function(e){
        e.preventDefault();
        var row = $(this).parent().parent();
        calculate_total();
    });
	

    function calculate_total()
	{
        var grand_total_item 				= 	0; 
        var grand_total_subscription 		= 	0;
        var total_tax_item 					= 	0;
        var total_tax_subscription 			= 	0;
        var total_discount 					= 	0.0;		
		var Tax_type_item					=	new Array();
		var Tax_type_title_item				=	new Array();
        var Tax_type_subscription			=	new Array();
        var Tax_type_title_subscription		=	new Array();
		

        $('#EstimateTable tbody tr').each(function(i, el){
            var $self = $(el);
            var productType = $self.find('.product_dropdown').find(':selected').attr('Item_Subscription_txt'); 
            $self.find('td .TaxAmount').each(function(i, el){
                var $this = $(el);
                if($this.val() != ''){
                    if(productType==txtITEM){
                        total_tax_item  = eval(parseFloat(total_tax_item) + parseFloat($this.val().replace(/,/g,'')));
                    }else if(productType==txtSUBSCRIPTION){
                        total_tax_subscription  = eval(parseFloat(total_tax_subscription) + parseFloat($this.val().replace(/,/g,'')));
                    }
                }
            });

            $self.find('td select.Taxentity').each(function(i, el){
                var $this 	=	 $(el);
                var tt		=	 $('option:selected', this);
                if($this.val() != '' && $this.val() != 0)
                {
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
                //if(productType==txtITEM) {
					if(productType==txtITEM)
					{
						if (Tax_type_item[$this.val()] != null) {
							Tax_type_item[$this.val()] = Tax_type_item[$this.val()] + tax;
						} else {
							Tax_type_item[$this.val()] = tax;
						}
						Tax_type_title_item[$this.val()] = titleTax;                
					}
					else if(productType==txtSUBSCRIPTION)
					{
						if (Tax_type_subscription[$this.val()] != null) {
							Tax_type_subscription[$this.val()] = Tax_type_subscription[$this.val()] + tax;
						} else {
							Tax_type_subscription[$this.val()] = tax;
						}
						Tax_type_title_subscription[$this.val()] = titleTax;    
						
					}
            });
			
			$self.find('td .LineTotal').each(function(i, el){
					var $this = $(el);
						if($this.val() != ''){
							//decimal_places = get_decimal_places($this.val())
							if(productType==txtITEM) {
								grand_total_item = eval(parseFloat(grand_total_item) + parseFloat($this.val().replace(/,/g, '')));
							}else if(productType==txtSUBSCRIPTION){
									grand_total_subscription = eval(parseFloat(grand_total_subscription) + parseFloat($this.val().replace(/,/g, '')));
							}
						}
					});
			});
		
	
		$('.tax_rows_estimate').remove();
		
		if(grand_total_item > 0){
            var txt = 'One off Sub Total';
			var txt1 = 'One off Total';
            if(grand_total_subscription == 0){
                txt = 'Sub Total';
				var txt1 = '';
            }
            $('#summary tfoot .grand_total_estimate').before('<tr class="tax_rows_estimate"> <td>'+txt+'</td> <td><input class="form-control SubTotal text-right" readonly="readonly" name="SubTotalOnOffCharge" value="'+grand_total_item.toFixed(decimal_places)+'" type="text"></td> </tr>');          
        }
		item_total_sum = parseFloat(grand_total_item.toFixed(decimal_places));
		 if(Tax_type_item.length > 0) { 
            Tax_type_item.forEach(function (value, index) {
                if (value != null) {
                    $('#summary tfoot .grand_total_estimate').before('<tr class="tax_rows_estimate"><td>' + Tax_type_title_item[index] + '</td><td><input class="form-control text-right" readonly="readonly" name="ProductTax[item][' + index + ']" value="' + value.toFixed(decimal_places) + '" type="text">  </td> </tr>');
					item_total_sum = parseFloat(item_total_sum)+parseFloat(value.toFixed(decimal_places));
                }
            });
				if(txt1){ console.log("item_total_sum:"+item_total_sum);
			 $('#summary tfoot .grand_total_estimate').before('<tr class="tax_rows_estimate"><td><strong>' + txt1 + '</strong></td><td><input class="form-control text-right" readonly="readonly" name="dummytax" value="' + item_total_sum.toFixed(decimal_places) + '" type="text">  </td> </tr>');
				}
			
        }
		
        if(grand_total_subscription > 0){
            var txt = 'Recurring Sub Total';
			var txt1 = 'Recurring Total';
            if(grand_total_item == 0){
                txt = 'Sub Total';
				var txt1 = '';
            }

            $('#summary tfoot .grand_total_estimate').before('<tr class="tax_rows_estimate"> <td>'+txt+'</td> <td><input class="form-control SubTotal text-right" readonly="readonly" name="SubTotalSubscription" value="'+grand_total_subscription.toFixed(decimal_places)+'" type="text"></td> </tr>');
          }
		  subscription_total_sum = parseFloat(grand_total_subscription.toFixed(decimal_places));
		   if(Tax_type_subscription.length > 0) {
            Tax_type_subscription.forEach(function (value, index) {
                if (value != null) {
                    $('#summary tfoot .grand_total_estimate').before('<tr class="tax_rows_estimate"><td>' + Tax_type_title_subscription[index] + '</td><td><input class="form-control text-right" readonly="readonly" name="ProductTax[subscription][' + index + ']" value="' + value.toFixed(decimal_places) + '" type="text">  </td> </tr>');				subscription_total_sum = parseFloat(subscription_total_sum)+parseFloat(value.toFixed(decimal_places));
                }
            });
			if(txt1){ console.log("Tax_type_subscription:"+subscription_total_sum);
			 $('#summary tfoot .grand_total_estimate').before('<tr class="tax_rows_estimate"><td><strong>' + txt1 + '</strong></td><td><input class="form-control text-right" readonly="readonly" name="dummytax" value="' + subscription_total_sum + '" type="text">  </td> </tr>');
				}
        }
       

        total = eval(grand_total_item + total_tax_item + grand_total_subscription + total_tax_subscription).toFixed(decimal_places);

        $('#summary tfoot .grand_total_estimate .GrandTotal').val(total);

		estimate_main_total_tax = 0; var taxes_array = new Array();
	   $('.EstimateTaxesFld').each(function(index, element) {
		   
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
				   
				   obj.find('.EstimateTaxesValue').val(tax.toFixed(decimal_places));		
				   estimate_main_total_tax = parseFloat(estimate_main_total_tax)+parseFloat(tax); 		
            }
			else
			{
				  var obj 		 =   $(element).parent().parent();
				   obj.find('.EstimateTaxesValue').val(0);		
			}
    	});
		var gross_total = parseFloat(total)+estimate_main_total_tax; 
		 $('input[name=GrandTotalEstimate]').val(gross_total.toFixed(decimal_places));

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
        //var taxTitle = $(".TaxRateID option:selected").text();

        var rowCount = $('#EstimateTable tbody tr').length;
        if(taxTitle =='Select a Tax Rate'){
            taxTitle='VAT';
        }else if(rowCount >1) {
            taxTitle='Total Tax';
        }
        $(".product_tax_title").text(taxTitle);
    });

    $(".send-estimate.btn").click( function (e) {
        $('#send-modal-estimate').find(".modal-body").html("Loading Content...");
        var ajaxurl = "/estimate/"+estimate_id+"/estimate_email";
        showAjaxModal(ajaxurl,'send-modal-estimate');
        $("#send-estimate-form")[0].reset();
        $('#send-modal-estimate').modal('show');
    });

    $("select[name=AccountID]").change( function (e) {
        url = baseurl + "/estimate/get_account_info";
        $this = $(this);
        data = {account_id:$this.val()}
        if($this.val() > 0){
            ajax_json(url,data,function(response){
                if ( typeof response.status != undefined &&  response.status == 'failed') {
                    toastr.error(response.message, "Error", toastr_opts);
                    $("#Account_Address").html('');
                    $("input[name=CurrencyCode]").val('');
                    $("input[name=CurrencyID]").val('');
                    $('#subscription').find('[data-type="currency"]').val('').trigger('change');
                    if($('#add-new-billing_subscription-form input[name=CurrencyID]').length > 0) {
                        $('#add-new-billing_subscription-form input[name=CurrencyID]').val('');
                    }
                    $("input[name=EstimateTemplateID]").val('');
                    $("[name=Terms]").val('');
                    $("[name=FooterTerm]").val('');
                } else {
                    $("#Account_Address").html(response.EstimateToAddress);
                    $("input[name=CurrencyCode]").val(response.Currency);
                    $("input[name=CurrencyID]").val(response.CurrencyId);
					$("#AccountBillingClassID").val(response.BillingClassID).trigger('change'); 
                    $('#add-new-billing_subscription-form [data-type="currency"]').val(response.CurrencyId).trigger('change');
                    if($('#add-new-billing_subscription-form input[name=CurrencyID]').length > 0) {
                        $('#add-new-billing_subscription-form input[name=CurrencyID]').val(response.CurrencyId);
                    }else{
                        $('#add-new-billing_subscription-form select[data-type="currency"]').after($('<input type="hidden" name="CurrencyID" value="' + response.CurrencyId + '" />'));
                    }
                    $("input[name=EstimateTemplateID]").val(response.EstimateTemplateID);
                    $("[name=Terms]").val(response.Terms);
                    $("[name=FooterTerm]").val(response.FooterTerm);
					add_estimate_tax(response.AccountTaxRate);
                    EstimateTemplateID = response.EstimateTemplateID;
                }
			 	show_summerinvoicetemplate($("[name=Terms]"));
				show_summerinvoicetemplate($("[name=FooterTerm]"));	

            });
        }

    });
	
	$("#AccountBillingClassID").change( function (e) {
        if($("select[name=AccountID]").val() == '') {
            toastr.error("Please Select Client first.", "Error", toastr_opts);
            return false;
        }
        url   = baseurl + "/estimate/get_billingclass_info";
        $this = $(this);
        data  = {BillingClassID:$this.val(),account_id:$("select[name=AccountID]").val()}
        if($this.val() > 0){
            ajax_json(url,data,function(response){
                if ( typeof response.status != undefined &&  response.status == 'failed') {
                    toastr.error(response.message, "Error", toastr_opts);
                    $("#Account_Address").html('');
                   
                    $("input[name=EstimateTemplateID]").val('');
                    $("[name=Terms]").val('');
                    $("[name=FooterTerm]").val('');
                } else {
                    $("#Account_Address").html(response.InvoiceToAddress);
                    $("input[name=EstimateTemplateID]").val(response.EstimateTemplateID);
                    $("[name=Terms]").val(response.Terms);
                    $("[name=FooterTerm]").val(response.FooterTerm);
                    add_estimate_tax(response.AccountTaxRate);
                    EstimateTemplateID = response.EstimateTemplateID;
                }
				show_summerinvoicetemplate($("[name=Terms]"));
				show_summerinvoicetemplate($("[name=FooterTerm]"));	

            });
        }   
	
	});
	
	function add_estimate_tax(AccountTaxRate){
		$('.all_tax_row').remove();
		if(AccountTaxRate.length>0){			
			AccountTaxRate.forEach(function(entry,index) {				
				if(index==0){
					$('.EstimateTaxesFldFirst').val(entry);
					var change = $('.EstimateTaxesFldFirst');
					change.trigger('change');
				}
				else
				{			
				  var	estimate_tax_html_final  = '<tr class="all_tax_row EstimateTaxestr'+index+' ">'+estimate_tax_html+"</tr>";
				  $('.gross_total_estimate').before(estimate_tax_html_final);	
				  var current_obj = $('.EstimateTaxestr'+index).find('.EstimateTaxesFld');
				  current_obj.addClass('EstimateTaxesFld'+index);
				  current_obj.val(entry);
				  current_obj.addClass('visible');
				  current_obj.select2();
				  current_obj.trigger('change');
 				  // var change = $('.InvoiceTaxesFld').eq(index+1);			
				}
			});		
			 calculate_total();
		}	
	}
	
    //Calculate Total
    calculate_total();

    $("#send-estimate-form").submit(function(e){
        e.preventDefault();
        var post_data  = $(this).serialize();
        var EstimateID = $(this).find("[name=EstimateID]").val();
        var _url = baseurl + '/estimate/'+EstimateID+'/send';
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
});
</script>
<style>
#EstimateTable.table > tbody > tr > td > div > a > span.select2-chosen { width:110px;}
</style>
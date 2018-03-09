<script src="{{URL::to('/')}}/assets/js/bootstrap.min.js"></script>
<div class="modal-body">
<div class="row">
	<div class="col-md-3"> </div>
	<div class="col-md-6">
		<table class="table table-bordered datatable" id="ajxtable-stripeach">
			<thead>
			<tr>
				<th width="25%">{{cus_lang('CUST_PANEL_PAGE_INVOICE_BANK_AC_TBL_TITLE')}}</th>
				<th width="10%">{{cus_lang('CUST_PANEL_PAGE_INVOICE_BANK_AC_TBL_DEFAULT')}}</th>
				<th width="20%">{{cus_lang('CUST_PANEL_PAGE_INVOICE_BANK_AC_TBL_PAYMENT_METHOD')}}</th>
				<th width="25%">{{cus_lang('CUST_PANEL_PAGE_INVOICE_BANK_AC_TBL_CREATED_DATE')}}</th>
				<th width="20%">{{cus_lang('TABLE_COLUMN_ACTION')}}</th>
			</tr>
			</thead>
			
			<tbody>
				@foreach($stripeachprofiles as $stripeachprofile)
					<tr>
						<td>{{$stripeachprofile['Title']}}</td>
						<td>
							@if($stripeachprofile['isDefault']==1)
								Default
							@endif
						</td>
						<td>{{$stripeachprofile['PaymentMethod']}}</td>
						<td>{{$stripeachprofile['created_at']}}</td>
						<td><button class="btn paynow btn-success btn-sm " data-cutomerid="{{$stripeachprofile['CustomerProfileID']}}" data-bankid="{{$stripeachprofile['BankAccountID']}}" data-id="{{$stripeachprofile['AccountPaymentProfileID']}}" data-loading-text="{{cus_lang('BUTTON_LOADING_CAPTION')}}">{{cus_lang('BUTTON_PAY_NOW_CAPTION')}}</button>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	<div class="col-md-3"> </div>
</div>
</div>
            <script type="text/javascript">
				$(document).ready(function() {
					@if(isset($Invoice))
						var InvoiceID = '{{$Invoice->InvoiceID}}';
						var AccountID = '{{$Invoice->AccountID}}';
					@elseif(isset($request["Amount"]))
						var InvoiceID = '{{$request["InvoiceID"]}}';
						var AccountID = '{{$Account->AccountID}}';
					@endif

                    $('.paynow').click(function(e) {
						e.preventDefault();
						$(this).button('loading');

						var self= $(this);
						var AccountPaymentProfileID = self.attr('data-id');
						var CustomerProfileID = self.attr('data-cutomerid');
						var BankAccountID = self.attr('data-bankid');

						var update_new_url;
						var type = '{{$type}}';

						if(type == 'StripeACH'){
							update_new_url = '{{URL::to('/')}}/payinvoice_withprofile/'+type;
						}

						var postData='AccountPaymentProfileID='+AccountPaymentProfileID+'&CustomerProfileID='+CustomerProfileID+'&BankAccountID='+BankAccountID+'&InvoiceID='+InvoiceID+'&AccountID='+AccountID;

						@if(isset($request["Amount"]))
							postData+='&GrandTotal={{$request["Amount"]}}&isInvoicePay=0&custome_notes={{$request["custome_notes"]}}';
						@else
							postData+='&isInvoicePay=1';
						@endif

                            $.ajax({
							url: update_new_url,  //Server script to process data
							type: 'POST',
							dataType: 'json',
							success: function (response) {
								$(".paynow").button('reset');
								if(response.status =='success'){
									toastr.success(response.message, "Success", toastr_opts);
									@if(isset($Invoice))
										window.location = '{{URL::to('/')}}/invoice_thanks/{{$Invoice->AccountID}}-{{$Invoice->InvoiceID}}';
									@elseif(isset($request["Amount"]))
										window.location = '{{URL::to('/customer/payments')}}';
									@endif
								}else{
									toastr.error(response.message, "Error", toastr_opts);
								}
							},
							error: function(error) {
								$(".paynow").button('reset');
								toastr.error(response.message, "Error", toastr_opts);
							},
							// Form data
							data: postData,
							//Options to tell jQuery not to process data or worry about content-type.
							cache: false
						});
					});
				});


            </script>
            <style>
				#ajxtable-stripeach{
					width:100%;
				}
                .dataTables_filter label{
                    display:none !important;
                }
                .dataTables_wrapper .export-data{
                    right: 30px !important;
                }
            </style>






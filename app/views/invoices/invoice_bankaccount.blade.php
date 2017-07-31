<script src="{{URL::to('/')}}/assets/js/bootstrap.min.js"></script>
<div class="modal-body">
<div class="row">
	<div class="col-md-3"> </div>
	<div class="col-md-6">
		<table class="table table-bordered datatable" id="ajxtable-stripeach">
			<thead>
			<tr>
				<th width="25%">Title</th>
				<th width="10%">Default</th>
				<th width="20%">Payment Method</th>
				<th width="25%">Created Date</th>
				<th width="20%">Action</th>
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
						<td><button class="btn paynow btn-success btn-sm " data-cutomerid="{{$stripeachprofile['CustomerProfileID']}}" data-bankid="{{$stripeachprofile['BankAccountID']}}" data-id="{{$stripeachprofile['AccountPaymentProfileID']}}" data-loading-text="Loading...">Pay Now </button>
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
					var InvoiceID = '{{$Invoice->InvoiceID}}';
					var AccountID = '{{$Invoice->AccountID}}';

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
							update_new_url = '{{URL::to('/')}}/stripeach_payment';
						}
						$.ajax({
							url: update_new_url,  //Server script to process data
							type: 'POST',
							dataType: 'json',
							success: function (response) {
								$(".paynow").button('reset');
								if(response.status =='success'){
									toastr.success(response.message, "Success", toastr_opts);
									window.location = '{{URL::to('/')}}/invoice_thanks/{{$Invoice->AccountID}}-{{$Invoice->InvoiceID}}';
								}else{
									toastr.error(response.message, "Error", toastr_opts);
								}
							},
							error: function(error) {
								$(".paynow").button('reset');
								toastr.error(response.message, "Error", toastr_opts);
							},
							// Form data
							data: 'AccountPaymentProfileID='+AccountPaymentProfileID+'&CustomerProfileID='+CustomerProfileID+'&BankAccountID='+BankAccountID+'&InvoiceID='+InvoiceID+'&AccountID='+AccountID,
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






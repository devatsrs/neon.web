<script>
	var ajax_url = baseurl + "/accounts/{{$account->AccountID}}/ajax_datagrid_PaymentProfiles";	
</script>
@if( $account->PaymentMethod == 'Stripe'  || $account->PaymentMethod == 'AuthorizeNet' || $account->PaymentMethod == 'FideliPay')
	@if (is_authorize() || is_Stripe() || is_FideliPay())
		@include('customer.paymentprofile.paymentGrid')
	@endif
@endif
@if( $account->PaymentMethod == 'StripeACH')
	@if(is_StripeACH())
		@include('customer.paymentprofile.bankpaymentGrid')
	@endif
@endif
@if( $account->PaymentMethod == 'SagePayDirectDebit')
	@if(is_SagePayDirectDebit())
		@include('customer.paymentprofile.sagepaydirectdebitGrid')
	@endif
@endif
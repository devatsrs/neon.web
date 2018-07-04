<script>
	var ajax_url = baseurl + "/accounts/{{$account->AccountID}}/ajax_datagrid_PaymentProfiles";	
</script>
@if( $account->PaymentMethod == 'Stripe'  || $account->PaymentMethod == 'AuthorizeNet' || $account->PaymentMethod == 'FideliPay' || $account->PaymentMethod == 'PeleCard' || $account->PaymentMethod == 'MerchantWarrior')
	@if (is_authorize($account->CompanyId) || is_Stripe($account->CompanyId) || is_FideliPay($account->CompanyId) || is_PeleCard($account->CompanyId) || is_merchantwarrior($account->CompanyId))
		@include('customer.paymentprofile.paymentGrid')
	@endif
@endif
@if( $account->PaymentMethod == 'StripeACH')
	@if(is_StripeACH($account->CompanyId))
		@include('customer.paymentprofile.bankpaymentGrid')
	@endif
@endif
@if( $account->PaymentMethod == 'SagePayDirectDebit')
	@if(is_SagePayDirectDebit($account->CompanyId))
		@include('customer.paymentprofile.sagepaydirectdebitGrid')
	@endif
@endif
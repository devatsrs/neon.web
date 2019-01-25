<script>
	var ajax_payout_url = baseurl + "/accounts/{{$account->AccountID}}/ajax_datagrid_PayoutAccounts";
</script>
@if(@$account->PayoutMethod == 'Stripe'  || @$account->PayoutMethod == '')
	@include('customer.payoutprofile.paymentGrid')
@endif
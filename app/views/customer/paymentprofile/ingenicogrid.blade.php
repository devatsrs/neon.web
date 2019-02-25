<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading"><div class="panel-title">Ingenico Payment Profile</div>
    <div class="panel-options"><a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a></div></div><div class="panel-body">
        <div class="form-group row">
            <div class="col-sm-2"><label class="control-label">Card Token</label></div>
            <div class="col-sm-6"><input type="text" name="Ingenico" id="ingenico_card" value="{{ AccountsPaymentProfileController::getCardValue($account->AccountID, 'Ingenico')}}" class="form-control"></div>
            
            </div></div></div>

            <script>
$("button#ingenicoadd").unbind("click").click(function(){

 var value = $("input#ingenico_card").val();
 var accountId = '{{$account->AccountID}}';
 var companyId = '{{$account->CompanyId}}';
 var method = 'Ingenico';
 if(value.length == 0){
    alert('please enter card detail');
    return false;
 }
 $.post("{{url('/paymentprofile/ingenicoadd')}}", {method:method,value:value, accountId:accountId,companyId:companyId}, function(data){
    $("#ingenicostatus").html(data);


 });

 
});
            </script>
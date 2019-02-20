<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading"><div class="panel-title">Ingenico Payment Profile</div>
    <div class="panel-options"><a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a></div></div><div class="panel-body">
        <div class="form-group row">
            <div class="col-sm-2"><label class="control-label">Card Token</label></div>
            <div class="col-sm-6"><input type="text" id="ingenico_card" value="{{ AccountsPaymentProfileController::getCardValue($account->AccountID)}}" class="form-control"></div>
            <div class="col-sm-2"><button type="button" class="btn btn-primary" id="ingenicoadd">Update Card</button></div>
            <div class="col-sm-2 control-label"><div id="ingenicostatus"></div></div></div></div></div>

            <script>
                $("#ingenicoadd").unbind("click").click(function(e){
    e.preventDefault();
    
 var value = $("input#ingenico_card").val();
 var accountId = '{{$account->AccountID}}';
 var companyId = '{{$account->CompanyId}}';
 var method = 'Ingenico';
 if(value.length == 0){
    alert('please enter card detail');
    return false;
 }$(this).text('loading..');
 $.post("{{url('/paymentprofile/ingenicoadd')}}", {method:method,value:value, accountId:accountId,companyId:companyId}, function(data){
    $("#ingenicostatus").html(data);


 });
 $(this).text('Update Card');
});
            </script>
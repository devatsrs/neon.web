<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>Payment {{ $data['data']['Status'] }}</h2>

<div>
    <h4>payment Detail</h4><br><br>
    Account Name: {{ $data['data']['AccountName'] }}<br>
    Amount: {{ number_format($data['data']['Amount'],2) }}<br>
    Type:  {{ $data['data']['PaymentType'] }}<br />
    Currency:  {{ $data['data']['Currency'] }}<br />
    Payment Date:  {{ $data['data']['PaymentDate'] }}<br />
    Notes:  {{ $data['data']['Notes'] }}<br />

    <br>
    <br>
    Regards
</div>

</body>
</html>
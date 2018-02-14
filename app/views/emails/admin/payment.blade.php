<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>Payment Verification</h2>
<div>
    <b>Account Name:</b> {{ $data['data']['AccountName'] }}<br>
    <b>Amount:</b> {{ number_format($data['data']['Amount'],2) }}<br>
    <b>Type:</b>  {{ $data['data']['PaymentType'] }}<br />
    <b>Currency:</b>  {{ $data['data']['Currency'] }}<br />
    <b>Payment Date:</b>  {{ $data['data']['PaymentDate'] }}<br />
    <b>Notes:</b>  {{ $data['data']['Notes'] }}<br />
    <b>Created By:</b>  {{ $data['data']['CreatedBy'] }}<br />

</div>

</body>
</html>
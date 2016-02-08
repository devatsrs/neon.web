<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>Customer Panel: Authentication Detail</h2>

<div>Dear {{ $data['AccountName'] }},<br><br>

    Following is your authentication detail for customer panel.

    <h4>Authentication Detail</h4><br><br>
    User Email: {{ $data['BillingEmail'] }}<br>
    Password:  {{$data['password']}}<br />
    Login Link:  {{URL::to('/customer/login')}}<br />
    <br>
    <br>
    Regards
</div>

</body>
</html>
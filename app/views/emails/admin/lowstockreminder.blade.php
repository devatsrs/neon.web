<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<h2>Low Stock Reminder</h2>
<div>
    <b>Item Type:</b> {{ $data['data']['ItemType'] }}<br>
    <b>Item Name:</b> {{ $data['data']['ProductName'] }}<br>
    <b>Item Code:</b> {{ $data['data']['ProductCode'] }}<br>
    <b>Stock:</b> {{ $data['data']['Stock'] }}<br>
    <b>Low Stock Level:</b>  {{ $data['data']['low_stock_level'] }}<br />

</div>

</body>
</html>
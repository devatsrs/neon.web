@extends('layout.main')

@section('content')
    <?php //echo "<pre>";print_r(Crypt::Decrypt('eyJpdiI6InNhcWw0eGl1KzBYWGUzYndmdmthZ0E9PSIsInZhbHVlIjoiTWozc2pjR3JMNjZ2ZXkzWFlFaXNzemFMMlVSc3B5QzZKZ1wvS0lGMGoyNG89IiwibWFjIjoiZmQzMTBmMzQ1N2FiNDA5NTUwMTU3OTA3MDU4NGExY2RiYmFjNzY5OWJlYjA3MTNlYzQyNmNkYmMwZTA3M2JjMCJ9'));exit(); ?>

    <?php
        $userName = "testpelecard3";
        $password = "Q3EJB8Ah";
        $termNo = "0962210";
        $shopNumber = "001";
        $creditCard = "4111111111111111";
        $creditCardDateMmYy = "1219";
        $token = "";//"1310703791";
        $total = "100";
        $currency = "1";
        $cvv2 = "123";
        $id = "123456789";
        $authorizationNumber = "";
        $paramX = "test";

        $data = array(
                    'terminalNumber' => $termNo,
                    'user' => $userName,
                    'password' => $password,
                    'shopNumber' => $shopNumber,
                    'creditCard' => $creditCard,
                    'creditCardDateMmYy' => $creditCardDateMmYy,
                    'token' => $token,
                    'total' => $total,
                    'currency' => $currency,
                    'cvv2' => $cvv2,
                    'id' => $id,
                    'authorizationNumber' => $authorizationNumber,
                    'paramX' => $paramX
                );
        $jsonData = json_encode($data);

        $url = 'https://gateway20.pelecard.biz/services/DebitRegularType';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8','Content-Length: ' . strlen($jsonData))  );
        $result = curl_exec($ch);
        $serverData = json_decode($result,true);
        //echo $serverData['URL'];
        echo "<pre>";print_r($serverData);exit();
    ?>

    <?php
        /*$userName = "testpelecard3";
        $password = "Q3EJB8Ah";
        $termNo = "0962210";
        $shopNumber = "001";
        $creditCard = "4111111111111111";
        $creditCardDateMmYy = "1219";

        $data = array(
                    'terminalNumber' => $termNo,
                    'user' => $userName,
                    'password' => $password,
                    'shopNumber' => $shopNumber,
                    'creditCard' => $creditCard,
                    'creditCardDateMmYy' => $creditCardDateMmYy,
                    'addFourDigits' => "false"
                );
        $jsonData = json_encode($data);

        $url = 'https://gateway20.pelecard.biz/services/ConvertToToken';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8','Content-Length: ' . strlen($jsonData))  );
        $result = curl_exec($ch);
        $serverData = json_decode($result,true);
        //echo $serverData['URL'];
        echo "<pre>";print_r($serverData);exit();*/
    ?>

@stop
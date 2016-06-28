<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>

        <h2>Active Cron Job</h2>
        <b>Cron Job Title : </b>{{$data['JobTitle']}}<br>
        <b>Process was Running Since: </b>{{$data['Minute']}} minutes<br>
        <b>Proccess Id : </b>{{$data['PID']}} <br>
        <b>Company Name : </b>{{$data['CompanyName']}}<br><br>

		<p>We have run following command to terminate the command, and we have received following response.</p>
		
		<p><b>Kill Command : </b>{{$data['KillCommand']}} <br><br>
		<b>Kill Command Response : </b>{{$data['ReturnStatus']}} <br><br>
		<b>Kill Command Detail Response : </b>{{implode('<Br>',$data['DetailOutput'])}} <br><br>
		</p>
		
        <p>Checkout for other <b><a href="{{$data['Url']}}">Active Cron Jobs</a></b> <br><br>
		</p>

        <p>Regards<br>
        {{$data['CompanyName']}}
		</p>

</body>
</html>
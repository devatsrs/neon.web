<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
                <p>
                    Hi {{$data['Firstname'] }} {{ $data['Lastname'] }}<br /><br />
                     <a href="{{$data['user_reset_link']}}">Click Here</a> to Reset Your Password.<br /><br />
                </p>
           <p>
                    Best Regards<br /><br />
                    {{$data['CompanyName']}}
           </p>
	</body>
</html>

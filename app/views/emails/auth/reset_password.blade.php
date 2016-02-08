<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		       <p>
                    Hi {{ $data['Firstname'] }} {{ $data['Lastname'] }}<br /><br />
                    Your Password is reset.<br /><br />
                </p>
                <p>
                    Best Regards<br /><br />
                    {{$data['CompanyName']}}<br /><br />
                </p>
	</body>
</html>

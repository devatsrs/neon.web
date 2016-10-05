@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">QuickBook</a>
        </li>
    </ol>

    <h3>QuickBook</h3>
    <div class="tab-content">
		QuickBooks connection status: 

		@if(!empty($quickbooks_CompanyInfo) && count($quickbooks_CompanyInfo)>0)
			
				CONNECTED!<br>
				<br>
				<i>
					Company: <?php print($quickbooks_CompanyInfo->getCompanyName()); ?><br>
					Email: <?php print($quickbooks_CompanyInfo->getEmail()->getAddress()); ?><br>
					Country: <?php print($quickbooks_CompanyInfo->getCountry()); ?>
				</i>
            <br><br><a  href="{{URL::to('/quickbook/disconnect')}}"> DisConnect </a>
		@endif	
		@if(empty($quickbooks_CompanyInfo))
        <b>NOT</b> CONNECTED!<br>
        <br>
        <ipp:connectToIntuit></ipp:connectToIntuit>
        <br>
        <br>
        You must authenticate to QuickBooks <b>once</b> before you can exchange data with it. <br>
        <br>
        <strong>You only have to do this once!</strong> <br><br>

        After you've authenticated once, you never have to go
        through this connection process again. <br>
        Click the button above to
        authenticate and connect.
		@endif
        <script type="text/javascript" src="https://appcenter.intuit.com/Content/IA/intuit.ipp.anywhere.js"></script>
        <script type="text/javascript">
            intuit.ipp.anywhere.setup({
                menuProxy: '{{ URL::to('/quickbook')}}',
                grantUrl: '{{ URL::to('/quickbook/oauth')}}'
            });
        </script>

        <style>

            table
            {
                margin-left: 20px;
                margin-right: 20px;
            }

            tr:nth-child(even) {background: #CCC}
            tr:nth-child(odd) {background: #EEE}

            td
            {
                padding: 4px;
            }

        </style>
    </div>
@stop

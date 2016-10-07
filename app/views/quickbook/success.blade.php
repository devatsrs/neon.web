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
		<div style="text-align: center;">
			You're connected! Please wait...
		</div>
	
		<script type="text/javascript">
			//window.opener.location.reload(false);
			window.setTimeout('window.close()', 2000);
		</script>
    </div>
@stop

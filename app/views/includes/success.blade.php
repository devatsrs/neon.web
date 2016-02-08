<?php 
$success_message = Session::get('success_message');
?>
 @if(!empty($success_message))
<script type="text/javascript">

    jQuery(document).ready(function ($) {


        toastr.success("{{$success_message}}", "Success", toastr_opts);
});    
</script>
@endif
 
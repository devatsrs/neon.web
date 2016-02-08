<?php $txterrors = ""; ?>
@if($errors->has())

        @foreach ($errors->all() as $error)
            <?php $txterrors .=  $error."<br />";?>
        @endforeach
        <script type="text/javascript">
              jQuery(document).ready(function ($) {
                toastr.error("{{$txterrors}}", "Error", toastr_opts);
            });    
        </script>

@endif
<?php 
$error_message = Session::get('error_message');
?>
 @if(!empty($error_message))
<script type="text/javascript">

    jQuery(document).ready(function ($) {


        toastr.error("{{$error_message}}", "Error", toastr_opts);
});    
</script>
@endif
<?php
$info_message = Session::get('info_message');
?>
 @if(!empty($info_message))
<script type="text/javascript">

    jQuery(document).ready(function ($) {


        toastr.info("{{$info_message}}", "Note:", toastr_opts);
});
</script>
@endif
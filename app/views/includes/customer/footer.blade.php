<!-- Footer -->
<footer class="main">
    <?php
    $domainUrl_key = preg_replace('/[^A-Za-z0-9\-]/', '', $_SERVER['HTTP_HOST']);
    $domainUrl_key = strtoupper(preg_replace('/-+/', '_',$domainUrl_key));
    ?>
    <a href="#" target="_blank"><strong>{{cus_lang("THEMES_".$domainUrl_key."_FOOTERTEXT")}}</strong></a>
</footer>
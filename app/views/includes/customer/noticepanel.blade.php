<?php
$CompanyID = User::get_companyID();
$NoticeBoardPostLast = NoticeBoardPost::where("CompanyID", $CompanyID)->limit(1)->orderBy('NoticeBoardPostID','Desc')->first();
?>
@if(count($NoticeBoardPostLast))
<div class="cc_banner-wrapper ">
    <div class="cc_banner cc_container cc_container--open">
        <a href="{{Url::to('customer/noticeboard')}}" data-cc-event="click:dismiss" target="_blank" class="cc_btn cc_btn_accept_all">View All!</a>
        <p class="cc_message">{{$NoticeBoardPostLast->Title}} </p>
        {{ strlen($NoticeBoardPostLast->Detail)>100 ? substr($NoticeBoardPostLast->Detail,0,100).'...':$NoticeBoardPostLast->Detail}}
    </div>
</div>
@endif
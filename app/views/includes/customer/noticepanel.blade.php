<?php
$CompanyID = User::get_companyID();
if(empty($offset)){
    $offset = 0;
}
$NoticeBoardPostLast = NoticeBoardPost::where("CompanyID", $CompanyID)->limit(1)->offset($offset)
        ->orderBy('NoticeBoardPostID','Desc')->first();
?>
@if(count($NoticeBoardPostLast))
<div class="cc_banner-wrapper  ">
    <div class="cc_banner cc_container  cc_container--open">
        <div class="pull-right">
            <a href="#" data-cc-event="click:dismiss" class="tooltip-primary prev_post"><i class="entypo-left-open-big"></i></a>
            <a href="#" data-cc-event="click:dismiss" class="tooltip-primary next_post"><i class="entypo-right-open-big"></i></a>
            <a href="{{Url::to('customer/noticeboard')}}" data-cc-event="click:dismiss" target="_blank" class="tooltip-primary post_detail"><i class="entypo-list"></i></a>
            <a href="#" data-cc-event="click:dismiss" class="tooltip-primary cc_btn_close"><i class="entypo-cancel-circled"></i></a>
        </div>
        <input type="hidden" id="NoticeBoardPostID_last" name="NoticeBoardPostID" value="{{$NoticeBoardPostLast->NoticeBoardPostID}}">
        <p class="cc_message"><span class="badge {{$NoticeBoardPostLast->Type}}">&nbsp;</span> <strong>{{$NoticeBoardPostLast->Title}}</strong> (<span class="cc_last_updated"> Updated {{\Carbon\Carbon::createFromTimeStamp(strtotime($NoticeBoardPostLast->updated_at))->diffForHumans() }}</span>)
        </p>

        <div class="cc_detail" style="padding-left: 20px;">{{ strlen($NoticeBoardPostLast->Detail)>350 ? substr($NoticeBoardPostLast->Detail,0,350).'...':$NoticeBoardPostLast->Detail}}</div>
    </div>
</div>
@endif

<script>
    $(document).ready(function() {
        var popState = getCookie('popState');
        if(popState == 'close'){
            $('.cc_banner-wrapper').hide();
        }
        $(document).on('click', '.next_post,.prev_post', function(e) {
            e.preventDefault();
            var NoticeBoardPostID = $('#NoticeBoardPostID_last').val();
            var next = 0;
            if($(this).hasClass('next_post')){
                next =1;
            }
            $(this).button('loading');
            ajax_json(baseurl + '/customer/get_next_update/' + NoticeBoardPostID+'?next='+next, $(this).serialize(), function (response) {
                $(".prev_post,.next_post").button('reset');
                if (response.NoticeBoardPostID) {
                    $('#NoticeBoardPostID_last').val(response.NoticeBoardPostID);
                    var post_title = '<span class="badge '+response.Type+'">&nbsp;</span> <strong>'+response.Title +'</strong> (<span class="cc_last_updated"> Updated '+response.LastUpdated+'</span>)';
                    $('.cc_message').html(post_title);
                    $('.cc_detail').html(response.Detail);
                    $('.post_detail').attr('href',baseurl+'/customer/noticeboard#'+response.NoticeBoardPostID);

                }

            });


        });
        $('.cc_btn_close').click(function(e) { // You are clicking the close button
            e.preventDefault();
            $('.cc_banner-wrapper').fadeOut(); // Now the pop up is hiden.
            setCookie('popState','close','30');
        });
    });
    function setCookie(cname,cvalue,exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires=" + d.toGMTString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
</script>
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
    <div class="cc_banner cc_container {{$NoticeBoardPostLast->Type}} cc_container--open">
        <div class="pull-right">
            <a href="{{Url::to('customer/prev_noticeboard')}}" data-cc-event="click:dismiss" target="_blank" class="btn btn-default btn-sm tooltip-primary prev_post"><i class="entypo-left-open-big"></i></a>
            <a href="{{Url::to('customer/next_noticeboard')}}" data-cc-event="click:dismiss" target="_blank" class="btn btn-default btn-sm tooltip-primary next_post"><i class="entypo-right-open-big"></i></a>
            <a href="{{Url::to('customer/noticeboard')}}" data-cc-event="click:dismiss" target="_blank" class="btn btn-default btn-sm tooltip-primary"><i class="entypo-list"></i></a>
            <a href="#" data-cc-event="click:dismiss" class="btn btn-default btn-sm tooltip-primary cc_btn_close"><i class="entypo-cancel-circled"></i></a>
        </div>
        <input type="hidden" id="NoticeBoardPostID_last" name="NoticeBoardPostID" value="{{$NoticeBoardPostLast->NoticeBoardPostID}}">
        <p class="cc_message">{{$NoticeBoardPostLast->Title}} </p>
        <p class="cc_last_updated">Updated {{\Carbon\Carbon::createFromTimeStamp(strtotime($NoticeBoardPostLast->updated_at))->diffForHumans() }} </p>
        <div class="cc_detail">{{ strlen($NoticeBoardPostLast->Detail)>100 ? substr($NoticeBoardPostLast->Detail,0,100).'...':$NoticeBoardPostLast->Detail}}</div>
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
                $(".btn").button('reset');

                if (response.NoticeBoardPostID) {
                    $('#NoticeBoardPostID_last').val(response.NoticeBoardPostID);
                    $('.cc_message').html(response.Title);
                    $('.cc_detail').html(response.Detail);
                    $('.cc_last_updated').html(response.LastUpdated);

                    $('.cc_container').removeClass('post-success');
                    $('.cc_container').removeClass('post-error');
                    $('.cc_container').removeClass('post-info');
                    $('.cc_container').removeClass('post-warning');
                    $('.cc_container').addClass(response.Type);

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
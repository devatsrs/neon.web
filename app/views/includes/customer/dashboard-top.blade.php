<div class="row">

<!-- Profile Info and Notifications -->
<div class="col-md-6 col-sm-8 clearfix">
<ul class="user-info pull-left pull-none-xsm">

    <!-- Profile Info -->
    <li class="profile-info dropdown">
        <!-- add class "pull-right" if you want to place this from right -->

        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            @if(Customer::get_customer_picture_url(Customer::get_accountID()) != '')<img src="{{ Customer::get_customer_picture_url(Customer::get_accountID()) }}" alt="" class="img-circle" width="44" />@endif
            {{Customer::get_accountName()}}
        </a>

        <ul class="dropdown-menu">


            <li class="caret"></li>

            <li>
                <a href="{{URL::to('/customer/profile' )}}">
                    <i class="entypo-user"></i>
                    @lang("routes.CUST_PANEL_HEAER_EDIT_PROFILE")
                </a>
            </li>
        </ul>
    </li>

</ul>

</div>
<!-- Raw Links -->
<div class="col-md-6 col-sm-4 clearfix hidden-xs text-right">

    <ul class="list-inline links-list pull-right">
        <li class="dropdown language-selector" id="user_language">
            <?php
            $cus_language=NeonCookie::getCookie('customer_language',"en");
            ?>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true" aria-expanded="false">
                <img src="" width="30" />
                <span></span>
                <i class="entypo-down-open-mini"></i>
            </a>
            <ul class="dropdown-menu pull-right">
                @foreach( Translation::getLanguageDropdownWithFlagList() as $key=>$value )
                    <?php
                    $selected="";
                        if($cus_language==$key){
                            $selected="active";
                        }
                    ?>
                    <li class="{{$selected}}" lang-key="{{$key}}">
                        <a href="javascript:void(0);">
                            <img src="{{URL::to('/assets/images/flag/'.$value["languageFlag"])}}" width="30" />
                            <span>{{$value["languageName"]}}</span>
                        </a>
                    </li>
                @endforeach
                <?php
                   $cus_alignment = NeonCookie::getCookie('customer_alignment',"left");
                    if($cus_alignment=="left"){
                        $cus_alignment="right";
                    }else{
                        $cus_alignment="left";
                    }
                ?>
                    {{--<li class="" lang-key="toggle"> <a href="javascripe:void(0);"> <i class="glyphicon glyphicon-align-{{$cus_alignment}}"></i> <span>Toggle Alignment</span> </a> </li>--}}
            </ul>
        </li>
        <li>
            <a href="{{ URL::to('customer/logout') }}">@lang('routes.CUST_PANEL_HEAER_LOGOUT') <i class="entypo-logout right"></i></a>
        </li>
    </ul>

</div>

</div>




<!-- chat Section -->
<div id="chat" class="fixed" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">

    <div class="chat-inner">


        <h2 class="chat-header">
            <a href="#" class="chat-close" data-animate="1"><i class="entypo-cancel"></i></a>

            <i class="entypo-users"></i>
            Chat

            <span class="badge badge-success is-hidden">0</span>
        </h2>


        <div class="chat-group" id="group-1">
            <strong>Favorites</strong>

            <a href="#" id="sample-user-123" data-conversation-history="#sample_history"><span class="user-status is-online"></span><em>Catherine J. Watkins</em></a>
            <a href="#"><span class="user-status is-online"></span><em>Nicholas R. Walker</em></a>
            <a href="#"><span class="user-status is-busy"></span><em>Susan J. Best</em></a>
            <a href="#"><span class="user-status is-offline"></span><em>Brandon S. Young</em></a>
            <a href="#"><span class="user-status is-idle"></span><em>Fernando G. Olson</em></a>
        </div>


        <div class="chat-group" id="group-2">
            <strong>Work</strong>

            <a href="#"><span class="user-status is-offline"></span><em>Robert J. Garcia</em></a>
            <a href="#" data-conversation-history="#sample_history_2"><span class="user-status is-offline"></span><em>Daniel A. Pena</em></a>
            <a href="#"><span class="user-status is-busy"></span><em>Rodrigo E. Lozano</em></a>
        </div>


        <div class="chat-group" id="group-3">
            <strong>Social</strong>

            <a href="#"><span class="user-status is-busy"></span><em>Velma G. Pearson</em></a>
            <a href="#"><span class="user-status is-offline"></span><em>Margaret R. Dedmon</em></a>
            <a href="#"><span class="user-status is-online"></span><em>Kathleen M. Canales</em></a>
            <a href="#"><span class="user-status is-offline"></span><em>Tracy J. Rodriguez</em></a>
        </div>

    </div>

    <!-- conversation template -->
    <div class="chat-conversation">

        <div class="conversation-header">
            <a href="#" class="conversation-close"><i class="entypo-cancel"></i></a>

            <span class="user-status"></span>
            <span class="display-name"></span>
            <small></small>
        </div>

        <ul class="conversation-body"></ul>

        <div class="chat-textarea">
            <textarea class="form-control autogrow" placeholder="Type your message"></textarea>
        </div>

    </div>

</div>
<!-- // Popup Model -->
 @section('footer_ext')
 <!-- Job Modal  (Ajax Modal)-->
 @parent
 <div class="modal fade" id="modal-job">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                 <h4 class="modal-title">Job Content</h4>
             </div>
             <div class="modal-body">
                 Content is loading...
             </div>
         </div>
     </div>
 </div>
 <div class="modal fade" id="modal-mailmsg">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                 <h4 class="modal-title">Message Content</h4>
             </div>
             <div class="modal-body">
                 Content is loading...
             </div>
         </div>
     </div>
 </div>

<style>
    .select2-choice {
        text-align: left;
    }
</style>
 @stop

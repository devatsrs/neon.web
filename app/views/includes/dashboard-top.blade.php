<div class="row">

<!-- Profile Info and Notifications -->
<div class="col-md-6 col-sm-8 clearfix">

<ul class="user-info pull-left pull-none-xsm">

    <!-- Profile Info -->
    <li class="profile-info dropdown">
        <!-- add class "pull-right" if you want to place this from right -->

        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="{{ UserProfile::get_user_picture_url(User::get_userID()) }}" alt="" class="img-circle" width="44" />
            {{Auth::user()->FirstName}} {{Auth::user()->LastName}} ({{ User::get_user_roles() }})
        </a>

        <ul class="dropdown-menu">


            <li class="caret"></li>

		 @if( User::checkCategoryPermission('Users','View'))
        <li> <a href="{{Url::to('users')}}">&nbsp;<i class="fa fa-user-secret"></i><span>Users</span> </a></li>
        @endif
        @if(User::is_admin())
        <li> <a href="{{Url::to('roles')}}">&nbsp;<i class="fa fa-key"></i><span>User Roles</span></a></li>
        @endif
        <li><a href="{{URL::to('users/edit_profile/'. User::get_userID() )}}"><i class="entypo-user"></i>Edit Profile</a></li>

            <!-- <li>
                 <a href="mailbox.html">
                     <i class="entypo-mail"></i>
                     Inbox
                 </a>
             </li>

             <li>
                 <a href="extra-calendar.html">
                     <i class="entypo-calendar"></i>
                     Calendar
                 </a>
             </li>
-->
		   <li><a href="{{URL::to('/jobs')}}"><i class="entypo-clipboard"></i>Jobs</a></li>
        </ul>
    </li>

</ul>
<ul class="user-info pull-left pull-right-xs pull-none-xsm">
<!-- Task Notifications -->
<li class="notifications jobs dropdown">

    <!-- Ajax Content here : Latest Jobs -->
     <a data-close-others="true" data-hover="dropdown" data-toggle="dropdown" class="dropdown-toggle jobs" href="#"><i class="entypo-list"></i></a>
     <ul class="dropdown-menu">
         <li class="top">
             <p>Loading...</p>
         </li>
     </ul>
</li>
    <!-- Cron job Notifications -->
    @if( User::checkCategoryPermission('CronJob','View'))
        <li class="notifications cron_jobs dropdown">
            <a title="Cron Jobs" href="{{Url::to('cronjob_monitor')}}"><i class="glyphicon glyphicon-time"></i>&nbsp;&nbsp;
                <span id="failing_placeholder"  title="" data-placement="right" class="hidden badge badge-danger" data-toggle="tooltip" data-original-title="Cron Job is failing...">!</span>
            </a>
        </li>
    @endif
</ul>
</div>
<!-- Raw Links -->
<div class="col-md-6 col-sm-4 clearfix col-xs-12">

    <ul class="list-inline links-list pull-right">

        <!--    Language Selector
         <li class="dropdown language-selector">Language: &nbsp;

             <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true">
                 <img src="{{ URL::to('/') }}/assets/images/flag-uk.png" />
             </a>

             <ul class="dropdown-menu pull-right">
                 <li>
                     <a href="#">
                         <img src="{{ URL::to('/') }}/assets/images/flag-de.png" />
                         <span>Deutsch</span>
                     </a>
                 </li>
                 <li class="active">
                     <a href="#">
                         <img src="{{ URL::to('/') }}/assets/images/flag-uk.png" />
                         <span>English</span>
                     </a>
                 </li>
                 <li>
                     <a href="#">
                         <img src="{{ URL::to('/') }}/assets/images/flag-fr.png" />
                         <span>François</span>
                     </a>
                 </li>
                 <li>
                     <a href="#">
                         <img src="{{ URL::to('/') }}/assets/images/flag-al.png" />
                         <span>Shqip</span>
                     </a>
                 </li>
                 <li>
                     <a href="#">
                         <img src="{{ URL::to('/') }}/assets/images/flag-es.png" />
                         <span>Español</span>
                     </a>
                 </li>
             </ul>

         </li>
         <li class="sep"></li>


         <li>
             <a href="#" data-toggle="chat" data-animate="1" data-collapse-sidebar="1">
                 <i class="entypo-chat"></i>
                 Chat



                 <span class="badge badge-success chat-notifications-badge is-hidden">0</span>
             </a>
         </li>

         <li class="sep"></li>-->

        <li id="filter-button-toggle" style="display: none;">
            <button id="filter-toggle-button" type="button" data-toggle="tooltip" class="btn btn-default btn-xs popover-primary" data-title="Filter" data-placement="left"><i class="fa fa-filter"></i></button>
        </li>

        <li>
            <a href="{{ URL::to('/logout') }}">Log Out <i class="entypo-logout right"></i>
            </a>
        </li>
    </ul>

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
 @stop

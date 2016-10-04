<div class="mail-sidebar">   
  <!-- compose new email button -->
  <div style="visibility:hidden;">
    <div  class="mail-sidebar-row hidden-xs"> <a href="#" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i> </a> </div>
  </div>
  <!-- menu -->
  <ul class="mail-menu">
    <li class="active"> <a href="{{URL::to('/emailmessages')}}"> <span class="badge badge-danger pull-right">{{$TotalUnreads}}</span> Inbox </a> </li>
    <li> <a href="{{URL::to('/emailmessages/sent')}}">  Sent </a> </li>
    <li class="hidden"> <a href="#"> Drafts </a> </li>
    <li class="hidden"> <a href="#"> <span class="badge badge-gray pull-right">1</span> Spam </a> </li>
    <li class="hidden"> <a href="#"> Trash </a> </li>
  </ul>
  <!-- menu --> 
</div>
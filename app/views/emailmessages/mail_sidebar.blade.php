<div class="mail-sidebar"> 
  <!-- compose new email button -->
  <div>
    <div  class="mail-sidebar-row hidden-xs"> <a href="{{URL::to('/emailmessages/compose')}}" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i>&nbsp;</a> </div>
  </div>
  <!-- menu -->
  <ul class="mail-menu">
    <li class="@if($data['BoxType']==Messages::inbox) active @endif "> <a href="{{URL::to('/emailmessages')}}"> <span class="badge badge-danger mailinboxcountersidebar pull-right">{{$TotalUnreads}}</span> Inbox </a> </li>
    <li class="@if($data['BoxType']==Messages::sentbox) active @endif"> <a href="{{URL::to('/emailmessages/sent')}}"> Sent </a> </li>
    <li class="@if($data['BoxType']==Messages::draftbox) active @endif"> <a href="{{URL::to('/emailmessages/draft')}}"><span class="badge badge-danger maildraftcountersidebar pull-right">{{$TotalDraft}}</span> Drafts </a> </li>
    <li class="hidden"> <a href="#"> <span class="badge badge-gray pull-right">1</span> Spam </a> </li>
    <li class="hidden"> <a href="#"> Trash </a> </li>
  </ul>
  <!-- menu --> 
</div>

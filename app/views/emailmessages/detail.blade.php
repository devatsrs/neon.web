@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  @if($Emaildata->EmailCall==Messages::Sent)
  <li><a href="{{URL::to('emailmessages/sent')}}">Sentbox</a></li>
  @elseif ($Emaildata->EmailCall==Messages::Received)
  <li><a href="{{URL::to('emailmessages')}}">Inbox</a></li>
  @endif
  <li class="active"> <strong>Emails Detail</strong> </li>
</ol>
<h3>Emails</h3>
@include('includes.errors')
@include('includes.success')
<div class="mail-env"> 
  
  <!-- compose new email button -->
  <div class="mail-sidebar-row visible-xs"> <a href="#" class="btn btn-success btn-icon btn-block"> Compose Mail <i class="entypo-pencil"></i> </a> </div>
  
  <!-- Mail Body -->
  <div class="mail-body">
    <div class="mail-header"> 
      <!-- title -->
      <div class="mail-title"> {{$Emaildata->Subject}} <span class="label label-warning hidden">Friends</span> <span class="label label-info hidden">Sport</span> </div>
      
      <!-- links -->
      <div class="mail-links hidden"> <a href="#" class="btn btn-default"> <i class="entypo-print"></i> </a> <a href="#" class="btn btn-default"> <i class="entypo-trash"></i> </a> <a class="btn btn-primary btn-icon"> Reply <i class="entypo-reply"></i> </a> </div>
    </div>
    <div class="mail-info">
      <div class="mail-sender"> <a class="href"> <span>{{$from}}</span> ({{$Emaildata->Emailfrom}}) to <span>{{$to}}</span> </a>        
      </div>
      <div class="mail-date"> <?php echo \Carbon\Carbon::createFromTimeStamp(strtotime($Emaildata->created_at))->diffForHumans();  ?> </div>
    </div>
    <div class="mail-text">{{$Emaildata->Message}}</div>
    @if(count($attachments)>0 && is_array($attachments))
    <div class="mail-attachments">
      <h4> <i class="entypo-attach"></i> Attachments <span>({{count($attachments)}})</span> </h4>
      <ul>
        @foreach($attachments as $attachments_data)
        <?php 
   		$FilePath 		= 	AmazonS3::preSignedUrl($attachments_data['filepath']);
		$Filename		=	$attachments_data['filepath'];
   	    ?>
        <li>
          
          <a href="{{$FilePath}}" class="thumb download"> <img width="175"   src="{{getimageicons($Filename)}}" class="img-rounded" /> </a>          
          <a href="{{$FilePath}}" class="shortnamewrap name"> {{$attachments_data['filename']}} </a>
          <div class="links"><a href="{{$FilePath}}">Download</a> </div>
        </li>
        @endforeach
      </ul>
    </div>
    @endif
    <div class="hidden mail-reply">
      <div class="fake-form">
        <div> <a href="#">Reply</a> or <a href="#">Forward</a> this message... </div>
      </div>
    </div>
  </div>
  
  <!-- Sidebar -->
  @include("emailmessages.mail_sidebar")
</div>
<style>
.mail-sender{width:60% !important;}
p{   
	-webkit-margin-before: 1em;
    -webkit-margin-after: 1em;
	-webkit-margin-start: 0px;
    -webkit-margin-end: 0px;
    
}
</style>
@stop 
@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
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
      <div class="mail-sender dropdown"> <a class="href dropdown-toggle" data-toggle="dropdown"> <span>{{$Emaildata->EmailfromName}}</span> ({{$Emaildata->Emailfrom}}) to <span>me</span> </a>
        <ul class="dropdown-menu dropdown-red">
          <li> <a href="#"> <i class="entypo-user"></i> Add to Contacts </a> </li>
          <li> <a href="#"> <i class="entypo-menu"></i> Show other messages </a> </li>
          <li class="divider"></li>
          <li> <a href="#"> <i class="entypo-star"></i> Star this message </a> </li>
          <li> <a href="#"> <i class="entypo-reply"></i> Reply </a> </li>
          <li> <a href="#"> <i class="entypo-right"></i> Forward </a> </li>
        </ul>
      </div>
      <div class="mail-date"> {{date('H:i A',strtotime($Emaildata->created_at))}} - {{date('d M',strtotime($Emaildata->created_at))}} </div>
    </div>
    <div class="mail-text">{{$Emaildata->Message}}</div>
    @if(count($attachments)>0)
    <div class="mail-attachments">
      <h4> <i class="entypo-attach"></i> Attachments <span>({{count($attachments)}})</span> </h4>
      <ul>
        @foreach($attachments as $attachments_data)
        <?php 
			   		$FilePath 		= 	AmazonS3::preSignedUrl($attachments_data['filepath']);
			   		$ext			= 	pathinfo($attachments_data['filename'], PATHINFO_EXTENSION);
					$extimage		=	array("jpg","png","bmp","gif");
				    ?>
        <li>
          <?php if(in_array($ext,$extimage)){ ?>
          <a href="{{$FilePath}}" class="thumb download"> <img width="175"  src="{{$FilePath}}" class="img-rounded" /> </a>
          <?php }else{ ?>
          <a href="{{$FilePath}}" class="thumb download"> <img width="175"  src="{{URL::to('/')}}/assets/images/attach-1.png" class="img-rounded" /> </a>
          <?php } ?>
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
@stop 
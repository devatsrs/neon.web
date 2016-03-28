@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
      <li class="active">
        <a href="{{URL::to('themes')}}">Themes</a>
    </li>
  <li class="active"> <strong>Edit Theme</strong> </li>
</ol>
<h3>Edit Theme</h3>
<div class="panel-title"> @include('includes.errors')
  @include('includes.success') </div>
<div class="float-right">
  <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
  <a href="{{URL::to('/themes/')}}" class="btn btn-danger btn-sm btn-icon icon-left"> <i class="entypo-cancel"></i> Close </a> </div>
<br>
<br>
<div class="row">
  <div class="col-md-12">
    <form role="form" id="form-themes-edit"  method="post"  class="form-horizontal form-groups-bordered">
      <div class="panel panel-primary" data-collapsed="0">
        <div class="panel-body">
          <div class="form-group">
            <label for="DomainUrl" class="col-sm-2 control-label">Domain Url</label>
            <div class="col-sm-4">
              <input type="text" name='DomainUrl' class="form-control" id="DomainUrl" placeholder="https://www.site.com" value="{{$Theme->DomainUrl}}">
            </div>
            <label for="Title" class="col-sm-2 control-label">Title</label>
            <div class="col-sm-4">
              <input type="text" name='Title' class="form-control" id="Title" placeholder="Title" value="{{$Theme->Title}}">
            </div>
          </div>
              <div class="form-group">
            <label for="FooterText" class="col-sm-2 control-label">Footer Text</label>
            <div class="col-sm-4">
              <input type="text" name='FooterText' class="form-control" id="FooterText" placeholder="Footer Text" value="{{$Theme->FooterText}}">
            </div>
            <label for="FooterUrl" class="col-sm-2 control-label">Footer Url</label>
            <div class="col-sm-4">
              <input type="text" name='FooterUrl' class="form-control" id="FooterUrl" placeholder="Footer Url" value="{{$Theme->FooterUrl}}">
            </div>
          </div>
          <div class="form-group">
          <label for="LoginMessage" class="col-sm-2 control-label">Login Message</label>
            <div class="col-sm-8">
              <input type="text" name='LoginMessage' class="form-control" id="LoginMessage" placeholder="Login Message" value="{{$Theme->LoginMessage}}">
            </div>
          </div>
                    <div class="form-group">
            <label for="Logo" class="col-sm-2 control-label">Logo</label>
            <div class="col-sm-10">
              <div class="col-sm-4">
                <input id="Logo" type="file" name="Logo" class="form-control file2 inline btn btn-primary Logo-input-file" accept="image/jpeg" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
              </div>
              <div class="col-sm-6"> 
              <?php 
			  $empty_logo = 1;
			  if($Theme->Logo!='')
			  {
				   $FilePath =  AmazonS3::preSignedUrl($Theme->Logo); 
				  if(file_exists($FilePath))
				  { 
				  	 $empty_logo = 0;
					 if (!is_numeric(strpos($FilePath, "https://")))
					 {
						$FilePath = str_replace('/', '\\', $FilePath);
						if (copy($FilePath, './uploads/' . basename($FilePath)))
						{
							$FilePath = URL::to('/') . '/uploads/' . basename($FilePath);
						}
                	 }  
				  
			  ?>
              <img name="LogoUrl" src="<?php echo $FilePath; ?>" width="200">
              <?php } } 
			  if($empty_logo){ ?>
              <img name="LogoUrl" src="http://placehold.it/200x58" width="200">
              <?php } ?>
               (Only Upload .jpg file)
               </div>
            </div>
          </div>
              <div class="form-group">
            <label for="Favicon" class="col-sm-2 control-label">Favicon</label>
            <div class="col-sm-10">
              <div class="col-sm-4">
                <input id="Favicon" type="file" name="Favicon" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
              </div>
              <div class="col-sm-6">
               <?php 
			  $empty_fav = 1;
			  if($Theme->Favicon!='')
			  {
				   $FilePath =  AmazonS3::preSignedUrl($Theme->Favicon); 
				  if(file_exists($FilePath))
				  { 
				  	 $empty_fav = 0;
					 if (!is_numeric(strpos($FilePath, "https://")))
					 {
						$FilePath = str_replace('/', '\\', $FilePath);
						if (copy($FilePath, './uploads/' . basename($FilePath)))
						{
							$FilePath = URL::to('/') . '/uploads/' . basename($FilePath);
						}
                	 }  
				  
			  ?>
              <img name="FaviconUrl" src="<?php echo $FilePath; ?>" width="32">
              <?php } } 
			  if($empty_fav){ ?>
              <img name="FaviconUrl" src="http://placehold.it/32x32" width="32">
              <?php } ?>
               (Only Upload .jpg file)
              
              </div>
            </div>
          </div>
              <div class="form-group">
          <label for="CustomCss" class="col-sm-2 control-label">Custom Css</label>
            <div class="col-sm-8">
              <textarea name='CustomCss' class="form-control" rows="12"  id="CustomCss" placeholder="Custom Css">{{$Theme->CustomCss}}</textarea>
            </div>
          </div>
                  <div class="form-group">
           <label for="ThemeStatus" class="col-sm-2 control-label">Status</label>
           <div class="col-sm-4">
           		{{Form::select('ThemeStatus',$theme_status_json,$Theme->ThemeStatus,array("class"=>"select2"))}}
          	 </div>            
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $(".save.btn").click(function (ev) {
            $("#form-themes-edit").submit();
            $(this).button('Loading');
        });
		

    });
	

function ajax_form_success(response)
{
    if(typeof response.redirect != 'undefined' && response.redirect != '')
	{
      location.reload();
    }
}	

</script>
@include('includes.ajax_submit_script', array('formID'=>'form-themes-edit' , 'url' => 'themes/'.$Theme->ThemeID.'/update' ))
@stop
@section('footer_ext')
@parent
@stop
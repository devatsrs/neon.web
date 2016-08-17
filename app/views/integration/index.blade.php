@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Integration</strong> </li>
</ol>
<h3>Integration</h3>
@include('includes.errors')
@include('includes.success')
<div class="panel">
<form id="rootwizard-2" method="post" action="" class="form-wizard validate form-horizontal form-groups-bordered" enctype="multipart/form-data">
  <div class="steps-progress">
    <div class="progress-indicator"></div>
  </div>
  <ul id="wizardul" >
    <li class="active" id="st1"> <a href="#tab2-1" data-toggle="tab"><span>1</span>
      <h5 class="test">Select Category</h5>
      </a> </li>
    <li id="st2"> <a href="#tab2-2" data-toggle="tab"><span>2</span>
      <h5 class="test">Select Sub Category</h5>
      </a> </li>
  </ul>
  <div class="tab-content"> <!--<span class="itype">
      <h3>Select Category</h3>
      </span>-->
    <div class="tab-pane active" id="tab2-1">
      <div class="row"> </br>
        </br>
        <div class="col-md-1"></div>
        <div class="col-md-11">
          <div class=""> @foreach($categories as $key => $CategoriesData)
            <?php
				$active = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"ParentIntegrationID"=>$CategoriesData['IntegrationID']))->first();
			  ?>
            <input type="radio" name="category" class="category" data-id="{{$CategoriesData['Slug']}}" catid="{{$CategoriesData['IntegrationID']}}" value="{{$CategoriesData['Slug']}}" id="{{$CategoriesData['Slug']}}" @if($key==0) checked @endif />
            <label for="{{$CategoriesData['Slug']}}" class="newredio @if($key==0) active @endif"> @if(isset($active['Status']) && $active['Status']==1) <i class="entypo-check"></i> @endif
              {{$CategoriesData['Title']}} </label>
            @endforeach </div>
        </div>
      </div>
    </div>
    <div class="tab-pane" id="tab2-2">
      <div class="row"> </br>
        </br>
        <div class="col-md-1"></div>
        <div class="col-md-11">
          <div class="">
            <?php
		  $array_subcategories =	array();
		  	foreach($categories as $key => $CategoriesData) {
		  	 $subcategories = Integration::where(["CompanyID" => $companyID,"ParentID"=>$CategoriesData['IntegrationID']])->orderBy('Title', 'asc')->get();
			 	foreach($subcategories as $key => $subcategoriesData){
					$active = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$subcategoriesData['IntegrationID']))->first();
					$array_subcategories[$subcategoriesData['IntegrationID']] = $subcategoriesData;
			  ?>
            <div class="subcategoryblock sub{{$CategoriesData['Slug']}}">
              <input parent_id="{{$subcategoriesData['ParentID']}}" ForeignID="{{$subcategoriesData['ForeignID']}}" class="subcategory" type="radio" name="subcategoryfld" data-id="key-{{$key}}" subcatid="{{$subcategoriesData['IntegrationID']}}" value="{{$subcategoriesData['Slug']}}" id="{{$subcategoriesData['Slug']}}" @if($key==0) checked @endif />
              <label for="{{$subcategoriesData['Slug']}}" class="newredio secondstep @if($key==0) active @endif">
                <?php 
			  if(File::exists(public_path().'/assets/images/'.$subcategoriesData['Slug'].'.png')){	?>
                <img width="77" height="30" src="<?php  URL::to('/'); ?>assets/images/{{$subcategoriesData['Slug']}}.png" />
                <?php } ?>
                <a data-subcatid="{{$subcategoriesData['IntegrationID']}}"  data-title="{{$subcategoriesData['Title']}}" data-id="subcategorycontent{{$subcategoriesData['Slug']}}" class="manageSubcat">{{$subcategoriesData['Title']}}</a>
                @if(isset($active['Status']) && $active['Status']==1) <i class="entypo-check"></i> @endif
              </label>
            </div>
            <?php } } ?>
          </div>
        </div>
      </div>
    </div>
    <div class="tab-pane" id="tab2-3">
      <div class="subcategorycontent" id="subcategorycontent{{$array_subcategories[6]['Slug']}}">
        <?php 
		$FreshDeskDbData = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$array_subcategories[6]['IntegrationID']))->first();
		$FreshdeskData   = isset($FreshDeskDbData->Settings)?json_decode($FreshDeskDbData->Settings):"";
		$array = json_decode(json_encode($FreshdeskData), True);		
		 ?>
        <div class="row">
          <div class="col-md-6  margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Domain:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="FreshdeskDomain" value="{{isset($FreshdeskData->FreshdeskDomain)?$FreshdeskData->FreshdeskDomain:''}}" />
              </div>
            </div>
          </div>
          <div class="col-md-6 margin-top pull-right">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Email:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="FreshdeskEmail" value="{{isset($FreshdeskData->FreshdeskEmail)?$FreshdeskData->FreshdeskEmail:""}}" />
              </div>
            </div>
          </div>
          <div class="col-md-6  margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Password:</label>
              <div class="col-sm-8">
                <input type="password"  class="form-control" name="FreshdeskPassword" value="{{isset($FreshdeskData->FreshdeskPassword)?$FreshdeskData->FreshdeskPassword:''}}" />
              </div>
            </div>
          </div>
          <div class="col-md-6 margin-top pull-right">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Key:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="Freshdeskkey" value="{{isset($FreshdeskData->Freshdeskkey)?$FreshdeskData->Freshdeskkey:''}}" />
              </div>
            </div>
          </div>
          <div class="col-md-6  margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">Group:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="FreshdeskGroup" value="{{isset($FreshdeskData->FreshdeskGroup)?$FreshdeskData->FreshdeskGroup:''}}" />
              </div>
            </div>
          </div>
          <div class="col-md-6  margin-top pull-right">
            <div class="form-group">
              <label class="col-sm-4 control-label">Active:</label>
              <div class="col-sm-8 make">
                   <input id="FreshDeskStatus" class="subcatstatus" name="Status" type="checkbox" value="1" <?php if(isset($FreshDeskDbData->Status) && $FreshDeskDbData->Status==1){ ?>   checked="checked"<?php } ?> >
              </div>
            </div>
          </div>          
        </div>
      </div>
    </div>
  <ul class="pager wizard">
    <li class="previous"> <a href="#"><i class="entypo-left-open"></i> Previous</a> </li>
    <li class="next"> <a href="#">Next <i class="entypo-right-open"></i></a> </li>
  </ul>
  </div>

</form>
<!-- Footer -->
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var checked='';
        public_vars.$body = $("body");
        $('input[type="radio"], label').addClass('js');

        $('.newredio').on('click', function() {
            $('.newredio').removeClass('active');
            $(this).addClass('active');
        });

        $('#csvimport').hide();
        $('#csvactive').hide();
        $('#gatewayimport').hide();
        $('#uploadaccount').hide();
        var activetab = '';
        var element= $("#rootwizard-2");
        var progress = element.find(".steps-progress div");
        $('#rootwizard-2').bootstrapWizard({
            tabClass:         '',
            nextSelector:     '.wizard li.next',
            previousSelector: '.wizard li.previous',
            firstSelector:    '.wizard li.first',
            lastSelector:     '.wizard li.last',
            onTabShow: function(tab, navigation, index)
            {
                setCurrentProgressTab(element, navigation, tab, progress, index);
            },
            onTabClick: function(){
                return false;
            },
            onNext: function(tab, navigation, index) {
	            activetab = tab.attr('id');
                if(activetab=='st1'){
                    $('.itype').hide();
                    var importfrom  = $("#rootwizard-2 input[name='category']:checked").val();
					var catid   	= $("#rootwizard-2 input[name='category']:checked").attr('catid');
					$('.subcategoryblock').hide();
					$('.sub'+importfrom).show();
					$('.sub'+importfrom+' .newredio').eq(0).addClass('active');
					$('.sub'+importfrom+' .subcategory').eq(0).click();
				    $("#firstcategory").val(importfrom);
					$("#firstcategoryid").val(catid);
					console.log(importfrom+' '+catid);
                }

                if(activetab=='st2'){
					 var importcat   = 	$("#rootwizard-2 input[name='subcategoryfld']:checked").val();
 					 var subcatid    = 	$("#rootwizard-2 input[name='subcategoryfld']:checked").attr('subcatid');
					 var parent_id   = 	$("#rootwizard-2 input[name='subcategoryfld']:checked").attr('parent_id');
					 var ForeignID   = 	$("#rootwizard-2 input[name='subcategoryfld']:checked").attr('ForeignID');

					 console.log(importcat+' '+subcatid+' '+parent_id);
					 if(parent_id==5 && ForeignID!=0){ ///gateway 
					 	//window.location = baseurl+'/gateway?id='+ForeignID;	
					    window.open(baseurl+'/gateway?id='+ForeignID, '_blank');
						return false;
					 }					
                }
            },
            onPrevious: function(tab, navigation, index) {
                activetab = tab.attr('id');
                if(activetab=='st2'){
                   // location.reload();
                }
            }
        });



        $("#SubcategoryForm").submit(function(e){
            e.preventDefault();
	       var formData = new FormData($(this)[0]);
            console.log(formData);
            $.ajax({
                url:"{{URL::to('/integration/update')}}", //Server script to process data
                type: 'POST',
                dataType: 'json',
                beforeSend: function(){
                    $('.btn.save').button('loading');
                },
                success: function(response) {
                    $(".save_template").button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        reloadJobsDrodown(0);
                        location.reload();
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        });
		
		
		$('.manageSubcat').click(function(e) {
			$('#SubcategoryModalContent').html('');
            var SubCatID		 = 	$(this).attr('data-id');
			var DataTitle		 = 	$(this).attr('data-title');	
			var SubCatid	 	=	$(this).attr("data-subcatid");		 
			var SubcatContent 	 = 	$('#'+SubCatID).html(); 			
			$('#SubcategoryModalContent').html(SubcatContent);
			$('#SubcategoryModal .modal-title').html(DataTitle);
			var StatusValue  = $('#'+SubCatID).find('.subcatstatus:checked').val();
			if(StatusValue==1) {
				biuldSwicth('.make','Status','#SubcategoryModal','checked');
			}else{
				biuldSwicth('.make','Status','#SubcategoryModal','');
			}
			$("#secondcategory").val(DataTitle);
			$("#secondcategoryid").val(SubCatid);			
			$('#SubcategoryModal').modal('show');	
        });
		
		 function biuldSwicth(container,name,formID,checked){
                var make = '<span class="make-switch switch-small">';
                make += '<input name="'+name+'" value="1" '+checked+' type="checkbox">';
                make +='</span>';

                var container = $(formID).find(container);
                container.empty();
                container.html(make);
                container.find('.make-switch').bootstrapSwitch();
            }
    });
    </script> 
<script type="text/javascript" src="<?php echo URL::to('/'); ?>/assets/js/jquery.bootstrap.wizard.min.js" ></script>
<style>
    .dataTables_filter label{
        display:none !important;
    }
    .dataTables_wrapper .export-data{
        right: 30px !important;
    }
    #selectcheckbox{
        padding: 15px 10px;
    }
    input[type="radio"].js {
        display: none;
    }

    .newredio.js {
        display: block;
        float: left;
        margin-right: 10px;
        border: 1px solid #ababab;        
        color: #ababab;
        text-align: center;
        padding: 25px;
        height:25%;
        width: 25%;
        cursor: pointer;
    }

    .newredio.js.active {
        border: 1px solid #21a9e1;
        color: #ababab;
        font-weight: bold;
    }
	
	.newredio i {
		color:green;
	}
	.subselected{
		color:green !important;
		font-weight:bold;
	}
    .form-horizontal .control-label{
        text-align: left !important;
    }

    /*#tab2-2{
        margin: 0 0 0 50px;
    }*/
    .pager li.disabled{
        display: none;
    }
    .export-data{
        display: none;
    }
    .pager li > a, .pager li > span{
        background-color: #000000 !important;
        border-radius:3px;
        border:none;
    }
    .pager li > a{

        color : #ffffff !important;
    }
    .gatewayloading{
        display:none;
        color: #ffffff;
        background: #303641;
        display: table;
        position: fixed;
        visibility: visible;
        padding: 10px;
        text-align: center;
        left: 50%; top: auto;
        margin: 71px auto;
        z-index: 999;
        border: 1px solid #303641;
    }
    #st1 a,#st2 a,#st3 a{
        cursor: default;
        text-decoration: none;
    }

    #csvimport{
        padding: 0 75px;
    }
    h5{
        font-size: 14px !important;
    }
	.subcategoryblock, .subcategorycontent{display:none;}
	.secondstep{padding-left:0px !important; padding-bottom:19px !important; padding-top:19px !important; }
</style>
@stop

@section('footer_ext')
    @parent
<div class="modal fade" id="SubcategoryModal" data-backdrop="static">
  <div  class="modal-dialog" style="width:70%;">
  <form id="SubcategoryForm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Subcategory</h4>
      </div>
      <div class="modal-body">
        <div id="SubcategoryModalContent" class=""></div>
      </div>
      <div class="modal-footer">
          <input type="hidden" name="TaskID">
          <button type="submit" id="task-update"  class="save_template save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-floppy"></i> Save </button>
          <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
        </div>
    </div>
      <input name="firstcategory"  id="firstcategory" value="" type="hidden" />
  <input name="secondcategory" id="secondcategory" value="" type="hidden" />
  <input name="firstcategoryid"  id="firstcategoryid" value="" type="hidden" />
  <input name="secondcategoryid" id="secondcategoryid" value="" type="hidden" />
    </form>
  </div>
</div>
@stop
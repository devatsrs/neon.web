@extends('layout.main')

@section('content')
<ol class="breadcrumb bc-3">
  <li> <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a> </li>
  <li class="active"> <strong>Integration</strong> </li>
</ol>
<h3>Integration</h3>
@include('includes.errors')
@include('includes.success')
<style>
    .col-md-4{
        padding-left:5px;
        padding-right:5px;
    }
</style>
<div class="panel">
<form id="rootwizard-2" method="post" action="" class="form-wizard validate form-horizontal form-groups-bordered" enctype="multipart/form-data">
  <div style="display:none;" class="steps-progress">
    <div class="progress-indicator"></div>
  </div>
  <ul style="display:none;" id="wizardul" >
    <li class="active" id="st1"> <a href="#tab2-1" data-toggle="tab"><span>1</span>
      <h5 class="test">Select Category</h5>
      </a> </li>
    <li id="st2"> <a href="#tab2-2" data-toggle="tab"><span>2</span>
      <h5 class="test">Select Sub Category</h5>
      </a> </li>
  </ul>
  <div class="tab-content"> <span class="itype">
      <h3 class="firstStep">Select Category</h3>
      <h3 style="display:none;" class="SecondStep">Select Subcategory</h3>
      </span>
    <div class="tab-pane active" id="tab2-1">
      <div class="row"> </br>
        </br>
          <div class="col-md-1"></div>
          <div class="col-md-9"> @foreach($categories as $key => $CategoriesData)
            <?php
				$active = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"ParentIntegrationID"=>$CategoriesData['IntegrationID']))->first();
				if($CategoriesData['Slug']=='billinggateway' && $GatewayConfiguration>0){$active['Status'] =1;} 
			  ?>
              <div class="col-md-4">
            <input type="radio" name="category" class="category" data-id="{{$CategoriesData['Slug']}}" catid="{{$CategoriesData['IntegrationID']}}" value="{{$CategoriesData['Slug']}}" id="{{$CategoriesData['Slug']}}" @if($key==0) checked @endif />
            <label  for="{{$CategoriesData['Slug']}}" class="newredio @if($key==0) active @endif @if(isset($active['Status']) && $active['Status']==1) wizard-active @endif   "> 
              {{$CategoriesData['Title']}} </label>
              </div>
            @endforeach </div>
          <div class="col-md-1"></div>
      </div>
    </div>
    <div class="tab-pane" id="tab2-2">
      <div class="row"> </br>
        </br>
          <div class="col-md-1"></div>
          <div class="col-md-9">
            <?php
		  	foreach($categories as $key => $CategoriesData) {
				if($CategoriesData['Slug']!==SiteIntegration::$GatewaySlug){
				
		  	 $subcategories = Integration::where(["CompanyID" => $companyID,"ParentID"=>$CategoriesData['IntegrationID']])->orderBy('Title', 'asc')->get();
			 	foreach($subcategories as $key => $subcategoriesData){
					$active = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$subcategoriesData['IntegrationID']))->first();
					if($CategoriesData['Slug']=='billinggateway' && $GatewayConfiguration>0)
					{
						$SubGatewayConfiguration 	= 	IntegrationConfiguration::GetGatewayConfiguration($subcategoriesData['ForeignID']);	
						if($SubGatewayConfiguration>0)
						{
							$active['Status'] = 1;
						}
					} 
					 
			  ?>
            <div class="col-md-4 subcategoryblock sub{{$CategoriesData['Slug']}}">
              <input parent_id="{{$subcategoriesData['ParentID']}}"  class="subcategory" type="radio" name="subcategoryfld" data-id="key-{{$key}}" subcatid="{{$subcategoriesData['IntegrationID']}}" value="{{$subcategoriesData['Slug']}}" id="{{$subcategoriesData['Slug']}}" @if($key==0) checked @endif />
              <label data-subcatid="{{$subcategoriesData['IntegrationID']}}" data-title="{{$subcategoriesData['Title']}}" data-id="subcategorycontent{{$subcategoriesData['Slug']}}" parent_Slug="{{$CategoriesData['Slug']}}" ForeignID="{{$subcategoriesData['ForeignID']}}" for="{{$subcategoriesData['Slug']}}" class="newredio manageSubcat secondstep @if($key==0) active @endif @if(isset($active['Status']) && $active['Status']==1) wizard-active @endif">
                <?php 
			  if(File::exists(public_path().'/assets/images/'.$subcategoriesData['Slug'].'.png')){	?>
                <img class="integrationimage" src="<?php  URL::to('/'); ?>assets/images/{{$subcategoriesData['Slug']}}.png" />
                <?php } ?>
                <a>{{$subcategoriesData['Title']}}</a>
              </label>
            </div>
            <?php 
			}
		}
			else{ //billing gateway
			foreach($Gateway as $key => $Gateway_data){
				?>
             <div class="col-md-4 subcategoryblock sub{{$CategoriesData['Slug']}}">
              <input parent_id="{{$CategoriesData['ParentID']}}"  class="subcategory" type="radio" name="subcategoryfld" data-id="key-{{$key}}" subcatid="{{$Gateway_data['GatewayID']}}" value="{{$Gateway_data['Name']}}" id="{{$Gateway_data['Name']}}" @if($key==0) checked @endif />
              <label data-subcatid="{{$Gateway_data['GatewayID']}}" data-title="{{$Gateway_data['Title']}}" data-id="subcategorycontent{{$Gateway_data['Name']}}" parent_Slug="{{$CategoriesData['Slug']}}" ForeignID="{{$Gateway_data['GatewayID']}}"  for="{{$Gateway_data['Name']}}" class="newredio manageSubcat secondstep @if($key==0) active @endif @if(isset($active['Status']) && $active['Status']==1) wizard-active @endif">
                <?php 
			  if(File::exists(public_path().'/assets/images/'.$Gateway_data['Name'].'.png')){	?>
                <img class="integrationimage" src="<?php  URL::to('/'); ?>assets/images/{{$Gateway_data['Name']}}.png" />
                <?php }else{ ?>
                <img class="integrationimage" src="<?php  URL::to('/'); ?>assets/images/defaultGateway.png" />
                <?php } ?>
                <a>{{$Gateway_data['Title']}}</a>
              </label>
            </div>
                <?php
				
			}
			
			} } ?>
          </div>
          <div class="col-md-1"></div>
      </div>
    </div>
    <div class="tab-pane" id="tab2-3">
    <!-- fresh desk start -->
    <?php 
		$FreshDeskDbData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$freshdeskSlug);
		$FreshdeskData   = isset($FreshDeskDbData->Settings)?json_decode($FreshDeskDbData->Settings):"";
		 ?>
      <div class="subcategorycontent" id="subcategorycontent{{$FreshDeskDbData->Slug}}">
        
        <div class="row">
          <div class="col-md-6  margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Domain:
                  <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Domain Name example cdpk" data-original-title="FreshDesk Domain" class="label label-info popover-primary">?</span>
              </label>
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
              <label for="field-1" class="col-sm-4 control-label">Group:                              
                <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If not specified then system will get tickets against all groups.Multiple Allowed with comma seperated" data-original-title="FreshDesk Group" class="label label-info popover-primary">?</span>
                         
               </label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="FreshdeskGroup" value="{{isset($FreshdeskData->FreshdeskGroup)?$FreshdeskData->FreshdeskGroup:''}}" />
              </div>
            </div>
          </div>
          <div class="col-md-6  margin-top pull-right">
            <div class="form-group">
              <label class="col-sm-4 control-label">Active:               
               <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Enabling this will deactivate all other Support categories" data-original-title="Status" class="label label-info popover-primary">?</span>
               </label>
              <div class="col-sm-8" id="FreshdeskStatusDiv">
                   <input id="FreshDeskStatus" class="subcatstatus" Divid="FreshdeskStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($FreshDeskDbData->Status) && $FreshDeskDbData->Status==1){ ?>   checked="checked"<?php } ?> >
              </div>
            </div>
          </div>          
        </div>
      </div>
      <!-- fresh desk end -->
      <!-- authorize.net start -->
      <?php 
		$AuthorizeDbData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$AuthorizeSlug);
		$AuthorizeData   = isset($AuthorizeDbData->Settings)?json_decode($AuthorizeDbData->Settings):"";
		 ?>
      <div class="subcategorycontent" id="subcategorycontent{{$AuthorizeDbData->Slug}}">        
        <div class="row">
          <div class="col-md-6  margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Api Login ID:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="AuthorizeLoginID" value="{{isset($AuthorizeData->AuthorizeLoginID)?$AuthorizeData->AuthorizeLoginID:''}}" />
              </div>
            </div>
          </div>
          <div class="col-md-6 margin-top pull-right">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Transaction key:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="AuthorizeTransactionKey" value="{{isset($AuthorizeData->AuthorizeTransactionKey)?$AuthorizeData->AuthorizeTransactionKey:""}}" />
              </div>
            </div>
          </div>
          
          <div class="col-md-6 margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Test Account:</label>
              <div class="col-sm-8" id="AuthorizeTestAccountDiv">
                   <input id="AuthorizeTestAccount" class="subcatstatus" Divid="AuthorizeTestAccountDiv" name="AuthorizeTestAccount" type="checkbox" value="1" <?php if(isset($AuthorizeData->AuthorizeTestAccount) && $AuthorizeData->AuthorizeTestAccount==1){ ?>   checked="checked"<?php } ?> >
              </div>
              
            </div>
          </div>          
          <div class="col-md-6  margin-top pull-right">
            <div class="form-group">
              <label class="col-sm-4 control-label">Active:</label>
              <div class="col-sm-8" id="AuthorizeStatusDiv">
                   <input id="AuthorizeStatus" class="subcatstatus" Divid="AuthorizeStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($AuthorizeDbData->Status) && $AuthorizeDbData->Status==1){ ?>   checked="checked"<?php } ?> >
              </div>
            </div>
          </div>          
        </div>
      </div>
      <!-- authorize.net end -->
      <!-- Mandril start -->
       <?php 
	   		$ManrdilDbData   = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$mandrillSlug);
			$ManrdilData     = isset($ManrdilDbData->Settings)?json_decode($ManrdilDbData->Settings):"";
		 ?>
      <div class="subcategorycontent" id="subcategorycontent{{$ManrdilDbData->Slug}}">       
        <div class="row">
          <div class="col-md-6  margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Smtp Server:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="MandrilSmtpServer" value="{{isset($ManrdilData->MandrilSmtpServer)?$ManrdilData->MandrilSmtpServer:''}}" />
              </div>
            </div>
          </div>
          <div class="col-md-6 margin-top pull-right">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Port:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="MandrilPort" value="{{isset($ManrdilData->MandrilPort)?$ManrdilData->MandrilPort:""}}" />
              </div>
            </div>
          </div>                        
          <div class="col-md-6 margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Username:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="MandrilUserName" value="{{isset($ManrdilData->MandrilUserName)?$ManrdilData->MandrilUserName:""}}" />
              </div>
            </div>
          </div>          
          <div class="col-md-6 margin-top pull-right">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Password:</label>
              <div class="col-sm-8">
                <input type="password"  class="form-control" name="MandrilPassword" value="{{isset($ManrdilData->MandrilPassword)?$ManrdilData->MandrilPassword:""}}" />
              </div>
            </div>
          </div>  
          <div class="col-md-6 margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* SSL:</label>
              <div class="col-sm-8" id="AuthorizeSSLDiv">
                   <input id="MandrilSSL" class="subcatstatus" Divid="AuthorizeSSLDiv" name="MandrilSSL" type="checkbox" value="1" <?php if(isset($ManrdilData->MandrilSSL) && $ManrdilData->MandrilSSL==1){ ?>   checked="checked"<?php } ?> >
              </div>              
            </div>
          </div>              
          <div class="col-md-6  margin-top pull-right">
            <div class="form-group">
              <label class="col-sm-4 control-label">Active:</label>
              <div class="col-sm-8" id="MandrilStatusDiv">
                   <input id="MandrilStatus" class="subcatstatus" Divid="MandrilStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($ManrdilDbData->Status) && $ManrdilDbData->Status==1){ ?>   checked="checked"<?php } ?> >
              </div>
            </div>
          </div>          
        </div>
      </div>
      <!-- Mandril end -->    
      <!-- Amazon start -->
       <?php 
		$AmazonDbData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$AmazoneSlug);
		$AmazonData   = isset($AmazonDbData->Settings)?json_decode($AmazonDbData->Settings):"";
		 ?>
      <div class="subcategorycontent" id="subcategorycontent{{isset($AmazonDbData->Slug)?$AmazonDbData->Slug:''}}">        
        <div class="row">
          <div class="col-md-6  margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Key:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="AmazonKey" value="{{isset($AmazonData->AmazonKey)?$AmazonData->AmazonKey:''}}" />
              </div>
            </div>
          </div>
          <div class="col-md-6 margin-top pull-right">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Secret:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="AmazonSecret" value="{{isset($AmazonData->AmazonSecret)?$AmazonData->AmazonSecret:""}}" />
              </div>
            </div>
          </div>
          
          <div class="col-md-6  margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Aws Bucket:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="AmazonAwsBucket" value="{{isset($AmazonData->AmazonAwsBucket)?$AmazonData->AmazonAwsBucket:''}}" />
              </div>
            </div>
          </div>
          
          <div class="col-md-6 margin-top pull-right">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Aws Url:</label>
              <div class="col-sm-8">
                <input type="text"  class="form-control" name="AmazonAwsUrl" value="{{isset($AmazonData->AmazonAwsUrl)?$AmazonData->AmazonAwsUrl:""}}" />
              </div>
            </div>
          </div>
          
          <div class="col-md-6 margin-top pull-left">
            <div class="form-group">
              <label for="field-1" class="col-sm-4 control-label">* Aws Region:</label>
              <div class="col-sm-8" >
                  <input type="text"  class="form-control" name="AmazonAwsRegion" value="{{isset($AmazonData->AmazonAwsRegion)?$AmazonData->AmazonAwsRegion:""}}" />
              </div>
              
            </div>
          </div>          
          <div class="col-md-6  margin-top pull-right">
            <div class="form-group">
              <label class="col-sm-4 control-label">Active:
                             <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Old transactions will not be accessible" data-original-title="Caution" class="label label-info popover-primary">?</span>
              </label>
              <div class="col-sm-8" id="AmazonStatusDiv">
                   <input id="AmazonStatus" class="subcatstatus" Divid="AmazonStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($AmazonDbData->Status) && $AmazonDbData->Status==1){ ?>   checked="checked"<?php } ?> >
              </div>
            </div>
          </div>          
        </div>
      </div>   
      
      <!-- Amazon end -->    
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
                    //$('.itype').hide();
					$('.itype .firstStep').hide();
					$('.itype .SecondStep').show();
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
					$('.itype .firstStep').hide();
					$('.itype .SecondStep').show();
					 var importcat   = 	$("#rootwizard-2 input[name='subcategoryfld']:checked").val();
 					 var subcatid    = 	$("#rootwizard-2 input[name='subcategoryfld']:checked").attr('subcatid');
					 var parent_id   = 	$("#rootwizard-2 input[name='subcategoryfld']:checked").attr('parent_id');
					 var ForeignID   = 	$("#rootwizard-2 input[name='subcategoryfld']:checked").attr('ForeignID');
					 
					 console.log(importcat+' '+subcatid+' '+parent_id);
					 if(parent_id==5 && ForeignID!=0){ ///gateway 
					 	//window.location = baseurl+'/gateway?id='+ForeignID;	
					    window.open(baseurl+'/gateway/'+ForeignID, '_blank');
						return false;
					 }					
                }
            },
            onPrevious: function(tab, navigation, index) {
                activetab = tab.attr('id');
                if(activetab=='st2'){
                   // location.reload();
				   $('.itype .firstStep').show();
				   $('.itype .SecondStep').hide();
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
			var SubCatid	 	 =	$(this).attr("data-subcatid");		 
			var SubcatContent 	 = 	$('#'+SubCatID).html(); 				
			var parent_slug   	 = 	$(this).attr('parent_Slug');
			var ForeignID   	 = 	$(this).attr('ForeignID');
					
			$('#SubcategoryModalContent').html(SubcatContent);
			$('#SubcategoryModal .modal-title').html(DataTitle);
			
			 if(parent_slug=='billinggateway' && ForeignID!=0){ ///gateway 
				window.open(baseurl+'/gateway/'+ForeignID, '_blank');
				return false;
			 }		
			
			
			$('#'+SubCatID).find('.subcatstatus').each(function(index, element) {
                if($(this).prop('checked') == true)
			    {
					biuldSwicth('#'+$(this).attr('Divid'),$(this).attr('name'),'#SubcategoryModal','checked');
				}
				else
				{
					biuldSwicth('#'+$(this).attr('Divid'),$(this).attr('name'),'#SubcategoryModal','');
				}
            });
			
			//var StatusValue  = $('#'+SubCatID).find('.subcatstatus:checked').val();
			
			//alert(StatusValue);
			/*return false;
			if(StatusValue==1) {
				biuldSwicth('.make','Status','#SubcategoryModal','checked');
			}else{
				biuldSwicth('.make','Status','#SubcategoryModal','');
			}*/
			
			
			$("#secondcategory").val(DataTitle);
			$("#secondcategoryid").val(SubCatid);			
			$('#SubcategoryModal').modal('show');	
			
			 $('[data-toggle="popover"]').each(function(i, el)
                {
                    var $this = $(el),
                        placement = attrDefault($this, 'placement', 'right'),
                        trigger = attrDefault($this, 'trigger', 'click'),
                        popover_class = $this.hasClass('popover-secondary') ? 'popover-secondary' : ($this.hasClass('popover-primary') ? 'popover-primary' : ($this.hasClass('popover-default') ? 'popover-default' : ''));

                    $this.popover({
                        placement: placement,
                        trigger: trigger
                    });

                    $this.on('shown.bs.popover', function(ev)
                    {
                        var $popover = $this.next();

                        $popover.addClass(popover_class);
                    });
                });
			
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
	.integrationimage{height:40px !important;}
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
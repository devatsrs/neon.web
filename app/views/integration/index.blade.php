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
    #quickbook-connect{display: none;}
    .selectbatchupload{
        border-color: #ebebeb !important;
        background: #fff;
        background-clip: border-box;
        -moz-box-shadow: none;
        -webkit-box-shadow: none;
        box-shadow: none;
        -webkit-background-clip: padding-box;
        -moz-background-clip: padding;
        background-clip: padding-box;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        height: 21px;
        line-height: 20px;
        outline: none;
        outline: none;
    }
    .styled-select {
        /*background: url(http://i62.tinypic.com/15xvbd5.png) no-repeat 96% 0;*/
        border-color: #ebebeb !important;
        color:#555555;
        background: #fff;
        height: 25px;
        overflow: hidden;
        width: 240px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
    }

    .styled-select select {
        background: transparent;
        border: none;
        font-size: 11px;
        height: 29px;
        padding: 5px; /* If you add too much padding here, the options won't show in IE */
        width: 268px;
    }

    .styled-select.slate {
        background: url(http://i62.tinypic.com/2e3ybe1.jpg) no-repeat right center;
        height: 30px;
        width: 240px;
    }

    .styled-select.slate select {
        border: 1px solid #ebebeb;
        font-size: 14px;
        height: 34px;
        width: 268px;
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
        $active = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"ParentIntegrationID"=>$CategoriesData['IntegrationID'],"status"=>1))->first();
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
        
         //$subcategories = Integration::where(["CompanyID" => $companyID,"ParentID"=>$CategoriesData['IntegrationID']])->orderBy('Title', 'asc')->get();
         $subcategories = Integration::where(["ParentID"=>$CategoriesData['IntegrationID']])->orderBy('Title', 'asc')->get();
        foreach($subcategories as $key => $subcategoriesData){
          $active = IntegrationConfiguration::where(array('CompanyId'=>$companyID,"IntegrationID"=>$subcategoriesData['IntegrationID']))->first();        
           
        ?>
            <div class="col-md-4 subcategoryblock sub{{$CategoriesData['Slug']}}">
              <input parent_id="{{$subcategoriesData['ParentID']}}"  class="subcategory" type="radio" name="subcategoryfld" data-id="key-{{$key}}" subcatid="{{$subcategoriesData['IntegrationID']}}" value="{{$subcategoriesData['Slug']}}" id="{{$subcategoriesData['Slug']}}" @if($key==0) checked @endif />
              <label data-subcatid="{{$subcategoriesData['IntegrationID']}}" data-title="{{$subcategoriesData['Title']}}" data-id="subcategorycontent{{$subcategoriesData['Slug']}}" parent_Slug="{{$CategoriesData['Slug']}}" ForeignID="{{$subcategoriesData['ForeignID']}}" for="{{$subcategoriesData['Slug']}}" class="newredio manageSubcat secondstep @if($key==0) active @endif @if(isset($active['Status']) && $active['Status']==1) wizard-active @endif">
                <?php 
        if(File::exists(public_path().'/assets/images/'.$subcategoriesData['Slug'].'.png')){  ?>
                <img class="integrationimage" src="<?php  URL::to('/'); ?>assets/images/{{$subcategoriesData['Slug']}}.png" />
                <?php } ?>
                <a><b>
                    @if($subcategoriesData['Title']=='SagePay Direct Debit')
                        Direct Debit
                    @else
                        {{$subcategoriesData['Title']}}
                    @endif
                </b></a>
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
        if(File::exists(public_path().'/assets/images/'.$Gateway_data['Name'].'.png')){ ?>
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
        <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Domain:
                  <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Only Domain Name. e.g. abc.freshdesk.com then type just abc" data-original-title="FreshDesk Domain" class="label label-info popover-primary">?</span>
              </label>
              <div >
                <input type="text"  class="form-control" name="FreshdeskDomain" value="{{isset($FreshdeskData->FreshdeskDomain)?$FreshdeskData->FreshdeskDomain:''}}" /> 
                <span >.freshdesk.com</span>
                </div>
            </div>
          </div>
                     
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Email:</label>
                <input type="text"  class="form-control" name="FreshdeskEmail" value="{{isset($FreshdeskData->FreshdeskEmail)?$FreshdeskData->FreshdeskEmail:""}}" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Password:</label>
                <input type="password"  class="form-control" name="FreshdeskPassword" value="{{isset($FreshdeskData->FreshdeskPassword)?$FreshdeskData->FreshdeskPassword:""}}" /> <!--isset($FreshdeskData->FreshdeskPassword)?$FreshdeskData->FreshdeskPassword:''-->
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Key:</label>
              <input type="text"  class="form-control" name="Freshdeskkey" value="{{isset($FreshdeskData->Freshdeskkey)?$FreshdeskData->Freshdeskkey:''}}" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">Group:
                <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If not specified then system will get tickets against all groups.Multiple Allowed with comma seperated" data-original-title="FreshDesk Group" class="label label-info popover-primary">?</span>
              </label>
              <input type="text"  class="form-control" name="FreshdeskGroup" value="{{isset($FreshdeskData->FreshdeskGroup)?$FreshdeskData->FreshdeskGroup:''}}" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">Active:
               <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Enabling this will deactivate all other Support categories" data-original-title="Status" class="label label-info popover-primary">?</span>
               </label>
              <div id="FreshdeskStatusDiv">
                   <input id="FreshDeskStatus" class="subcatstatus" Divid="FreshdeskStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($FreshDeskDbData->Status) && $FreshDeskDbData->Status==1){ ?>   checked="checked"<?php } ?> >
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- fresh desk end -->
     
      
     

        <!-- Ingenico Start -->
        <?php
        $IngenicoDbData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$IngenicoSlug);
        $IngenicoData   = isset($IngenicoDbData->Settings)?json_decode($IngenicoDbData->Settings):"";
        ?>
        <div class="subcategorycontent" id="subcategorycontent{{$IngenicoDbData->Slug}}">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">* PSPID:</label>
                        <input type="text"  class="form-control" name="PSPID" value="{{isset($IngenicoData->PSPID)?$IngenicoData->PSPID:''}}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">* User ID:</label>
                        <input type="text" class="form-control" name="UserID" value="{{isset($IngenicoData->UserID)?$IngenicoData->UserID:''}}" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">* Password:</label>
                        <input type="text" class="form-control" name="Password" value="{{isset($IngenicoData->Password)?$IngenicoData->Password:''}}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">* SHA-IN:</label>
                        <input type="text" class="form-control" name="SHASIGN" value="{{isset($IngenicoData->SHASIGN)?$IngenicoData->SHASIGN:''}}" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Live:</label>
                        <div id="IngenicoLiveDiv">
                            <input id="IngenicoLive" class="subcatstatus" Divid="IngenicoLiveDiv" name="IngenicoLive" type="checkbox" value="1" <?php if(isset($IngenicoDbData->IngenicoLive) && $IngenicoDbData->IngenicoLive==1){ ?>   checked="checked"<?php } ?> >
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Active:</label>
                        <div id="IngenicoStatusDiv">
                            <input id="IngenicoStatus" class="subcatstatus" Divid="IngenicoStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($IngenicoDbData->Status) && $IngenicoDbData->Status==1){ ?>   checked="checked"<?php } ?> >
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- Ingenico End -->


        <!-- Mandril start -->
       <?php 
        $ManrdilDbData   = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$mandrillSlug);
      $ManrdilData     = isset($ManrdilDbData->Settings)?json_decode($ManrdilDbData->Settings):"";
     ?>
      <div class="subcategorycontent" id="subcategorycontent{{$ManrdilDbData->Slug}}">       
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Smtp Server:</label>
              <input type="text"  class="form-control" name="MandrilSmtpServer" value="{{isset($ManrdilData->MandrilSmtpServer)?$ManrdilData->MandrilSmtpServer:''}}" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Port:</label>
              <input type="text"  class="form-control" name="MandrilPort" value="{{isset($ManrdilData->MandrilPort)?$ManrdilData->MandrilPort:""}}" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Username:</label>
              <input type="text"  class="form-control" name="MandrilUserName" value="{{isset($ManrdilData->MandrilUserName)?$ManrdilData->MandrilUserName:""}}" />
            </div>
          </div>          
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Password:</label>
              <input type="password"  class="form-control" name="MandrilPassword" value="{{isset($ManrdilData->MandrilPassword)?$ManrdilData->MandrilPassword:""}}" /> <!-- isset($ManrdilData->MandrilPassword)?$ManrdilData->MandrilPassword:"" -->
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* SSL:</label>
              <div id="AuthorizeSSLDiv">
                   <input id="MandrilSSL" class="subcatstatus" Divid="AuthorizeSSLDiv" name="MandrilSSL" type="checkbox" value="1" <?php if(isset($ManrdilData->MandrilSSL) && $ManrdilData->MandrilSSL==1){ ?>   checked="checked"<?php } ?> >
              </div>              
            </div>
          </div>              
          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">Active:</label>
              <div id="MandrilStatusDiv">
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
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Key:</label>
              <input type="text"  class="form-control" name="AmazonKey" value="{{isset($AmazonData->AmazonKey)?$AmazonData->AmazonKey:''}}" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Secret:</label>
              <input type="text"  class="form-control" name="AmazonSecret" value="{{isset($AmazonData->AmazonSecret)?$AmazonData->AmazonSecret:""}}" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Aws Bucket:</label>
              <input type="text"  class="form-control" name="AmazonAwsBucket" value="{{isset($AmazonData->AmazonAwsBucket)?$AmazonData->AmazonAwsBucket:''}}" />
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Aws Url:</label>
              <input type="text"  class="form-control" name="AmazonAwsUrl" value="{{isset($AmazonData->AmazonAwsUrl)?$AmazonData->AmazonAwsUrl:""}}" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Aws Region:</label>
              <input type="text"  class="form-control" name="AmazonAwsRegion" value="{{isset($AmazonData->AmazonAwsRegion)?$AmazonData->AmazonAwsRegion:""}}" />
            </div>
          </div>          
          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">Active:
                  <span data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Old transactions will not be accessible" data-original-title="Caution" class="label label-info popover-primary">?</span>
              </label>
              <div id="AmazonStatusDiv">
                   <input id="AmazonStatus" class="subcatstatus" Divid="AmazonStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($AmazonDbData->Status) && $AmazonDbData->Status==1){ ?>   checked="checked"<?php } ?> >
              </div>
            </div>
          </div>          
        </div>
          <div class="row">
              <div class="col-md-6">
                  <div class="form-group">
                      <label for="field-1" class="control-label">Signature Version:</label>
                      <div class="styled-select slate">
                          {{Form::select('SignatureVersion',[''=>'Default','v4'=>'V4'],isset($AmazonData->SignatureVersion)?$AmazonData->SignatureVersion:'',array("class"=>""))}}
                      </div>
                  </div>
              </div>
              <div class="col-md-6">

              </div>
          </div>
      </div>   
   

      <!-- Amazon end -->   
      <!-- EmailTracking start -->
       <?php 
    $EmailTrackingDBData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$imapSlug);
    $EmailTrackingData   = isset($EmailTrackingDBData->Settings)?json_decode($EmailTrackingDBData->Settings):"";
     ?>
      <div class="subcategorycontent" id="subcategorycontent{{isset($EmailTrackingDBData->Slug)?$EmailTrackingDBData->Slug:''}}">        
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Email:</label>
              <input type="email"  class="form-control" name="EmailTrackingEmail" value="{{isset($EmailTrackingData->EmailTrackingEmail)?$EmailTrackingData->EmailTrackingEmail:''}}" />
            </div>
          </div> 
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Imap Server:</label>
              <input type="text"  class="form-control" id="EmailTrackingServer" name="EmailTrackingServer" value="{{isset($EmailTrackingData->EmailTrackingServer)?$EmailTrackingData->EmailTrackingServer:""}}" />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Password:</label>
              <input type="password"  class="form-control" id="EmailTrackingPassword" name="EmailTrackingPassword" value="{{isset($EmailTrackingData->EmailTrackingPassword)?$EmailTrackingData->EmailTrackingPassword:""}}" /> <!--isset($EmailTrackingData->EmailTrackingPassword)?$EmailTrackingData->EmailTrackingPassword:'' -->
            </div>
          </div>    
          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">Active: </label>
              <div id="EmailTrackingDiv">
                   <input id="EmailTrackingstatus" class="subcatstatus" Divid="EmailTrackingDiv" name="Status" type="checkbox" value="1" <?php if(isset($EmailTrackingDBData->Status) && $EmailTrackingDBData->Status==1){ ?>   checked="checked"<?php } ?> >
              </div>
         <!--<a id="TestImapConnection"  class="test-connection btn btn-success btn-sm btn-icon icon-left"><i class="entypo-rocket"></i>Test Connection </a>-->        

            </div>
          </div> 
        </div>
      </div>   
      <!-- EmailTracking end -->    
       <!-- Outlook calendar start -->
       <?php 
    $outlookcalendarDBData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$outlookcalenarSlug);
    $outlookcalendarData   = isset($outlookcalendarDBData->Settings)?json_decode($outlookcalendarDBData->Settings):"";
     ?>
      <div class="subcategorycontent" id="subcategorycontent{{isset($outlookcalendarDBData->Slug)?$outlookcalendarDBData->Slug:''}}">        
        <div class="row">
            <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">*Server:</label>
              <input type="text"  class="form-control" name="OutlookCalendarServer" value="{{isset($outlookcalendarData->OutlookCalendarServer)?$outlookcalendarData->OutlookCalendarServer:"pod51036.outlook.com/ews/services.wsdl"}}" />
            </div>
          </div>
            <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Email:</label>
              <input type="email"  class="form-control" name="OutlookCalendarEmail" value="{{isset($outlookcalendarData->OutlookCalendarEmail)?$outlookcalendarData->OutlookCalendarEmail:''}}" />
            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">* Password:</label>
              <input type="password"  class="form-control" name="OutlookCalendarPassword" value="{{isset($outlookcalendarData->OutlookCalendarPassword)?$outlookcalendarData->OutlookCalendarPassword:''}}" /> <!-- isset($outlookcalendarData->OutlookCalendarPassword)?$outlookcalendarData->OutlookCalendarPassword:'' -->
            </div>
          </div>
            <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">Active: </label>
              <div id="OutlookCalendarDiv">
                   <input id="OutlookCalendarstatus" class="subcatstatus" Divid="OutlookCalendarDiv" name="Status" type="checkbox" value="1" <?php if(isset($outlookcalendarDBData->Status) && $outlookcalendarDBData->Status==1){ ?>   checked="checked"<?php } ?> >
              </div>
            </div>
          </div>          
        </div>
      </div>   
      <!-- Outlook calendar end -->    
      
      <!-- Amazon end -->
      <!-- Quick Book -->
        <?php
        $QuickBookDbData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$QuickBookSlug);
        $QuickBookData   = isset($QuickBookDbData->Settings)?json_decode($QuickBookDbData->Settings,true):"";
        ?>
        <div class="subcategorycontent" id="subcategorycontent{{isset($QuickBookDbData->Slug) ? $QuickBookDbData->Slug : ''}}">
            <!-- quickbook form start-->

            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Details
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6  margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* Login ID/Email:</label>
                                <div class="col-sm-8">
                                    <input type="text"  class="form-control" name="QuickBookLoginID" value="{{isset($QuickBookData['QuickBookLoginID'])?$QuickBookData['QuickBookLoginID']:''}}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* Password:</label>
                                <div class="col-sm-8">
                                    <input type="password"  class="form-control" name="QuickBookPassqord" value="{{isset($QuickBookData['QuickBookPassqord'])?$QuickBookData['QuickBookPassqord']:''}}" /> <!-- isset($QuickBookData['QuickBookPassqord'])?$QuickBookData['QuickBookPassqord']:"" -->
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <!--<div class="col-md-6  margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* OAuth Consumer Key:</label>
                                <div class="col-sm-8">
                                    <input type="text"  class="form-control" name="OauthConsumerKey" value="{{isset($QuickBookData['OauthConsumerKey'])?$QuickBookData['OauthConsumerKey']:''}}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* OAuth Consumer Secret:</label>
                                <div class="col-sm-8">
                                    <input type="text"  class="form-control" name="OauthConsumerSecret" value="{{isset($QuickBookData['OauthConsumerSecret'])?$QuickBookData['OauthConsumerSecret']:""}}" />
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>

                        <div class="col-md-6 margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* App Token:</label>
                                <div class="col-sm-8">
                                    <input type="text"  class="form-control" name="AppToken" value="{{isset($QuickBookData['AppToken'])?$QuickBookData['AppToken']:""}}" />
                                </div>

                            </div>
                        </div>

                        <div class="col-md-6 margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">SandBox:</label>
                                <div class="col-sm-8" id="QuickBookSandboxDiv">
                                    <input id="QuickBookSandbox" class="subcatstatus" Divid="QuickBookSandboxDiv" name="QuickBookSandbox" type="checkbox" value="1" <?php if(isset($QuickBookData['QuickBookSandbox']) && $QuickBookData['QuickBookSandbox']==1){ ?>   checked="checked"<?php } ?> >
                                </div>

                            </div>
                        </div>
                        <div class="clear"></div>-->
                        <div class="col-md-6  margin-top">
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Active:</label>
                                <div class="col-sm-8" id="QuickBookStatusDiv">
                                    <input id="QuickBookStatus" class="subcatstatus" Divid="QuickBookStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($QuickBookDbData->Status) && $QuickBookDbData->Status==1){ ?>   checked="checked"<?php } ?> >
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Chart of Accounts Mapping
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="col-md-6  margin-top">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-4 control-label">Invoice:</label>
                            <div class="col-sm-8">
                                <input type="text"  class="form-control" name="InvoiceAccount" value="{{isset($QuickBookData['InvoiceAccount'])?$QuickBookData['InvoiceAccount']:""}}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 margin-top">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-4 control-label">Payment:</label>
                            <div class="col-sm-8">
                                <input type="text"  class="form-control" name="PaymentAccount" value="{{isset($QuickBookData['PaymentAccount'])?$QuickBookData['PaymentAccount']:""}}" />
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <?php $count=0; ?>
                    @if(!empty($TaxLists)&& count($TaxLists)>0)
                        @foreach($TaxLists as $TaxList)
                            <div class="col-md-6  margin-top">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-4 control-label">{{$TaxList->Title}}:</label>
                                    <div class="col-sm-8">
                                        <input type="text"  class="form-control" name="Tax[{{$TaxList->TaxRateId}}]" value="{{isset($QuickBookData['Tax'][$TaxList->TaxRateId])?$QuickBookData['Tax'][$TaxList->TaxRateId]:""}}" />
                                    </div>
                                </div>
                            </div>
                            <?php $count++; ?>
                            @if($count%2 == 0)
                                <div class="clear"></div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- quickbook form end-->
        </div>
      <!-- Quick Book End-->

        <!-- Quick Book Desktop-->
        <?php
        $QuickBookDesktopDbData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$QuickBookDesktopSlug);
        $QBDesktopData   = isset($QuickBookDesktopDbData->Settings)?json_decode($QuickBookDesktopDbData->Settings,true):"";
        ?>
        <div class="subcategorycontent" id="subcategorycontent{{isset($QuickBookDesktopDbData->Slug) ? $QuickBookDesktopDbData->Slug : ''}}">
            <!-- quickbook form start-->

            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Chart of Accounts Mapping
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="col-md-6  margin-top">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-4 control-label">Invoice:</label>
                            <div class="col-sm-8">
                                <input type="text"  class="form-control" name="InvoiceAccount" value="{{isset($QBDesktopData['InvoiceAccount'])?$QBDesktopData['InvoiceAccount']:""}}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 margin-top">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-4 control-label">Payment:</label>
                            <div class="col-sm-8">
                                <input type="text"  class="form-control" name="PaymentAccount" value="{{isset($QBDesktopData['PaymentAccount'])?$QBDesktopData['PaymentAccount']:""}}" />
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <?php $count=0; ?>
                    @if(!empty($TaxLists)&& count($TaxLists)>0)
                        @foreach($TaxLists as $TaxList)
                            <div class="col-md-6  margin-top">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-4 control-label">{{$TaxList->Title}}:</label>
                                    <div class="col-sm-8">
                                        <input type="text"  class="form-control" name="Tax[{{$TaxList->TaxRateId}}]" value="{{isset($QBDesktopData['Tax'][$TaxList->TaxRateId])?$QBDesktopData['Tax'][$TaxList->TaxRateId]:""}}" />
                                    </div>
                                </div>
                            </div>
                            <?php $count++; ?>
                            @if($count%2 == 0)
                                <div class="clear"></div>
                            @endif
                        @endforeach
                    @endif
                    <div class="col-md-6  margin-top">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Active:</label>
                            <div class="col-sm-8" id="QuickBookDesktopStatusDiv">
                                <input id="QuickBookDesktopStatus" class="subcatstatus" Divid="QuickBookDesktopStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($QuickBookDesktopDbData->Status) && $QuickBookDesktopDbData->Status==1){ ?>   checked="checked"<?php } ?> >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- quickbook form end-->
        </div>
        <!-- Quick Book Desktop End-->

        <!-- Xero -->
        <?php
        $XeroDbData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$XeroSlug);
        $XeroData   = isset($XeroDbData->Settings)?json_decode($XeroDbData->Settings,true):"";
        ?>
        <div class="subcategorycontent" id="subcategorycontent{{isset($XeroDbData->Slug) ? $XeroDbData->Slug : ''}}">
            <!-- Xero form start-->

            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Details
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6  margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* Consumer Key:</label>
                                <div class="col-sm-8">
                                    <input type="text"  class="form-control" name="ConsumerKey" value="{{isset($XeroData['ConsumerKey'])?$XeroData['ConsumerKey']:''}}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* Secret:</label>
                                <div class="col-sm-8">
                                    <input type="text"  class="form-control" name="ConsumerSecret" value="{{isset($XeroData['ConsumerSecret'])?$XeroData['ConsumerSecret']:''}}" />
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="col-md-12 margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* Upload certificate file (.pem):</label>
                                <div class="col-sm-8">
                                    <input name="XeroFile" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;Browse" />
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="col-md-6  margin-top">
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Active:</label>
                                <div class="col-sm-8" id="XeroStatusDiv">
                                    <input id="XeroStatus" class="subcatstatus" Divid="XeroStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($XeroDbData->Status) && $XeroDbData->Status==1){ ?>   checked="checked"<?php } ?> >
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Chart of Accounts Mapping(For Journal Posting)
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="col-md-6  margin-top">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-4 control-label">Invoice:</label>
                            <div class="col-sm-8">
                                <input type="text"  class="form-control" name="InvoiceAccount" value="{{isset($XeroData['InvoiceAccount'])?$XeroData['InvoiceAccount']:""}}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 margin-top">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-4 control-label">Payment:</label>
                            <div class="col-sm-8">
                                <input type="text"  class="form-control" name="PaymentAccount" value="{{isset($XeroData['PaymentAccount'])?$XeroData['PaymentAccount']:""}}" />
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>

                    <?php $count=0; ?>
                    @if(!empty($TaxLists)&& count($TaxLists)>0)
                        @foreach($TaxLists as $TaxList)
                            <div class="col-md-6  margin-top">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-4 control-label">{{$TaxList->Title}}:</label>
                                    <div class="col-sm-8">
                                        <input type="text"  class="form-control" name="Tax[{{$TaxList->TaxRateId}}]" value="{{isset($XeroData['Tax'][$TaxList->TaxRateId])?$XeroData['Tax'][$TaxList->TaxRateId]:""}}" />
                                    </div>
                                </div>
                            </div>
                            <?php $count++; ?>
                            @if($count%2 == 0)
                                <div class="clear"></div>
                            @endif
                        @endforeach
                    @endif

                </div>
            </div>

            <!-- Xero form end-->
        </div>
        <!-- Xero End-->
        <!-- Exact -->
        <?php
        $ExactDbData = IntegrationConfiguration::GetIntegrationDataBySlug(SiteIntegration::$ExactSlug);
        $ExactData   = isset($ExactDbData->Settings)?json_decode($ExactDbData->Settings,true):"";
        ?>
        <div class="subcategorycontent" id="subcategorycontent{{$ExactDbData->Slug}}">
            <!-- Exact form start-->

            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Details
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6  margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* Client ID:</label>
                                <div class="col-sm-8">
                                    <input type="text"  class="form-control" name="ExactClientID" value="{{isset($ExactData['ExactClientID'])?$ExactData['ExactClientID']:''}}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 margin-top">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-4 control-label">* Client Secret:</label>
                                <div class="col-sm-8">
                                    <input type="password"  class="form-control" name="ExactClientSecret" value="{{isset($ExactData['ExactClientSecret'])?$ExactData['ExactClientSecret']:''}}" />
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="col-md-6  margin-top">
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Active:</label>
                                <div class="col-sm-8" id="ExactStatusDiv">
                                    <input id="ExactStatus" class="subcatstatus" Divid="ExactStatusDiv" name="Status" type="checkbox" value="1" <?php if(isset($ExactDbData->Status) && $ExactDbData->Status==1){ ?>   checked="checked"<?php } ?> >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php
            $CompanyID = User::get_companyID();
            $Companies = Reseller::where(array("tblReseller.Status"=>1,"tblAccount.Status"=>1))
                    ->join('tblAccount' , 'tblAccount.AccountID' , '=' , 'tblReseller.AccountID')
                    ->Join('tblCompany' , 'tblCompany.CompanyID' , '=' , 'tblReseller.ChildCompanyID')
                    ->where(['tblReseller.CompanyID' => $CompanyID])
                    ->get(['tblCompany.CompanyName', 'tblCompany.CompanyID']);

            $MainCompany = Company::find($CompanyID);
            ?>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Division (For Invoice Posting)
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="col-md-6  margin-top">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{$MainCompany->CompanyName}}:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="Division[{{$MainCompany->CompanyID}}]" value="{{isset($ExactData['Division'][$MainCompany->CompanyID])?$ExactData['Division'][$MainCompany->CompanyID]:""}}" />
                            </div>
                        </div>
                    </div>
                    <?php foreach($Companies as $Company) { ?>
                    <div class="col-md-6  margin-top">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{$Company->CompanyName}}:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="Division[{{$Company->CompanyID}}]" value="{{isset($ExactData['Division'][$Company->CompanyID])?$ExactData['Division'][$Company->CompanyID]:""}}" />
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>

            @foreach(ExactAuthentication::$MappingTypes as $MappingKey => $MappingText)
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            G/L Code Mapping - {{$MappingText}} (For Invoice Posting)
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php $count=0; ?>
                        @if(!empty($AllMappingElements[$MappingKey])&& count($AllMappingElements[$MappingKey])>0)
                            @foreach($AllMappingElements[$MappingKey] as $key=>$Prod)
                                <div class="col-md-6  margin-top">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{$Prod}}:</label>
                                        <div class="col-sm-8">
                                            <input type="text"  class="form-control" name="{{$MappingKey}}[{{$key}}]" value="{{isset($ExactData[$MappingKey][$key])?$ExactData[$MappingKey][$key]:""}}" />
                                        </div>
                                    </div>
                                </div>
                                <?php $count++; ?>
                                @if($count%2 == 0)
                                    <div class="clear"></div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Exact form end-->
        </div>
        <!-- Exact End-->

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
    var SubCatID = '';
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
          var catid     = $("#rootwizard-2 input[name='category']:checked").attr('catid');
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
           var importcat   =  $("#rootwizard-2 input[name='subcategoryfld']:checked").val();
           var subcatid    =  $("#rootwizard-2 input[name='subcategoryfld']:checked").attr('subcatid');
           var parent_id   =  $("#rootwizard-2 input[name='subcategoryfld']:checked").attr('parent_id');
           var ForeignID   =  $("#rootwizard-2 input[name='subcategoryfld']:checked").attr('ForeignID');

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
            var quickbookconfirm = true;
            var check = false;
            var sec_cat = $("#SubcategoryForm input[name='secondcategory']").val();
          var formData = new FormData($(this)[0]);
            if(sec_cat=='QuickBook'){
                $('#SubcategoryForm input:text').each(function()
                {
                    if( !$(this).val() ) {
                        check = true;
                    }
                });

            }
            if(check){
                var result = confirm("Mappings are not setup correctly under Chart of Accounts Mapping.\nSystem won't be able to post Invoices.\n\nAre you sure you want to continue?")
                if(!result){
                    quickbookconfirm = false;
                }
            }
            var redirecturl = baseurl+ "/quickbook";
            console.log(formData);
            if(quickbookconfirm) {
                $.ajax({
                    url: "{{URL::to('/integration/update')}}", //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        $('.btn.save').button('loading');
                    },
                    success: function (response) {
                        $(".save_template").button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            reloadJobsDrodown(0);
                            if (response.quickbookredirect == '1') {
                                //location.href=redirecturl;
                            } else {
                                location.reload();
                            }
                            //location.reload();
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
            }else{
                setTimeout( function(){$(".save_template").button('reset')},10);
                return false;
            }
        });
    
    
    $('.manageSubcat').click(function(e) {
            $('#SubcategoryModal .modal-dialog').removeClass('modal-lg');
      $('#SubcategoryModalContent').html('');
            SubCatID     =  $(this).attr('data-id');
      var DataTitle    =  $(this).attr('data-title'); 
      var SubCatid     =  $(this).attr("data-subcatid");     
      var SubcatContent    =  $('#'+SubCatID).html();         
      var parent_slug      =  $(this).attr('parent_Slug');
      var ForeignID      =  $(this).attr('ForeignID');

      $('#SubcategoryModalContent').html(SubcatContent);
      $('#SubcategoryModal .modal-title').html(DataTitle);
      
       if(parent_slug=='billinggateway' && ForeignID!=0){ ///gateway 
        window.open(baseurl+'/gateway/'+ForeignID, '_blank');
        return false;
       }

            if(parent_slug=='accounting'){
                $('#SubcategoryModal .modal-dialog').addClass('modal-lg');
                if(DataTitle=='QuickBook'){
                    $('#quickbook-connect').show();
                }else{
                    $('#quickbook-connect').hide();
                }
                if(DataTitle=='Exact'){
                    $('#exact-connect').show();
                }else{
                    $('#exact-connect').hide();
                }
            }else{
                $('#quickbook-connect').hide();
                $('#exact-connect').hide();
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

            makeSwitch(SubCatID);

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

    $(document).on("click",'#TestImapConnection',function(e){
      $(this).button('loading');
      var email    =  $('#EmailTrackingEmail').val();
      var server   =  $('#EmailTrackingServer').val();
      var password =  $('#EmailTrackingPassword').val();
      
            e.preventDefault();
         var formData = new FormData($('#SubcategoryForm')[0]);
      
       $.ajax({
                url:"{{URL::to('/integration/checkimapconnection')}}", //Server script to process data
                type: 'POST',
                dataType: 'json',
                beforeSend: function(){
                    $('.btn.save').button('loading');
                },
                success: function(response) {
                    $(".save_template").button('reset');
          $('.test-connection').button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);                       
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

        $('#exact-connect').on('click', function() {
            $('#exact-connect-form').submit();
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

    function makeSwitch(SubCatID) {
        $('#'+SubCatID).find('.custom-switch').each(function(index, element) {
            if($(this).prop('checked') == true)
            {
                biuldSwicth('#'+$(this).attr('Divid'),$(this).attr('name'),'#SubcategoryModal','checked');
            }
            else
            {
                biuldSwicth('#'+$(this).attr('Divid'),$(this).attr('name'),'#SubcategoryModal','');
            }
        });
    }
    function getNumber($item){
        var txt = $item;
        var numb = txt.match(/\d/g);
        numb = numb.join("");
        return numb;
    }

    function createCloneRow(tblID)
    {
        var $item = $('#SubcategoryModal #' + tblID + ' tr:last').attr('id');
        var numb = getNumber($item);
        numb++;

        $("#SubcategoryModal #"+$item).clone().appendTo('#SubcategoryModal #' + tblID + ' tbody');

        var row = "selectedRow";

        $('#SubcategoryModal #' + tblID + ' tr:last').attr('id', row + '-' + numb);

        $('#SubcategoryModal #' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'Country-' + numb).prop('selectedIndex',0);
        $('#SubcategoryModal #' + tblID + ' tr:last').children('td:eq(3)').children('input').attr('name', 'VATCode-' + numb).val('');

        $('#SubcategoryModal #' + tblID + ' tr:last').children('td:eq(4)').children('a').attr('id', "deleteRow-"+numb);

        $('#SubcategoryModal #' + tblID + ' tr:last').children('td:eq(4)').find('a').removeClass('hidden');

        makeSwitch(SubCatID);
    }

    function deleteRow(id, tblID)
    {
        var rowCount = $("#SubcategoryModal #" + tblID + " > tbody").children().length;

        //if(confirm("Are You Sure?")) {
            if (rowCount > 1) {
                $("#SubcategoryModal #" + id).closest("tr").remove();
            } /*else {
                toastr.error("You cannot delete. At least one component is required.", "Error", toastr_opts);
            }*/
            return false;
        //}
    }

</script>
<script type="text/javascript" src="<?php echo URL::to('/'); ?>/assets/js/jquery.bootstrap.wizard.min.js" ></script>
<script type="text/javascript" src="https://appcenter.intuit.com/Content/IA/intuit.ipp.anywhere.js"></script>
<script type="text/javascript">
    intuit.ipp.anywhere.setup({
        menuProxy: '{{ URL::to('/quickbook')}}',
        grantUrl: '{{ URL::to('/quickbook/oauth')}}'
    });
</script>
@stop

@section('footer_ext')
    @parent
<div class="modal fade" id="SubcategoryModal" data-backdrop="static">
  <div  class="modal-dialog">
  <form id="SubcategoryForm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Subcategory</h4>
      </div>
      <div class="modal-body" id="SubcategoryModalContent">
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-success btn-sm btn-icon icon-left popover-primary" data-original-title="QuickBook Authorize"
                  data-content="Click to Authorize Neon to connect to Quickbook. Please click on save first." data-placement="top" data-trigger="hover" data-toggle="popover"  id="quickbook-connect"  onclick="intuit.ipp.anywhere.controller.onConnectToIntuitClicked();"><i class="fa fa-lock"></i>Authorize</button>
          <button type="button" class="btn btn-success btn-sm btn-icon icon-left popover-primary" data-original-title="Exact Authorize"
                  data-content="Click to Authorize Neon to connect to Exact. Please click on save first." data-placement="top" data-trigger="hover" data-toggle="popover"  id="exact-connect" ><i class="fa fa-lock"></i>Authorize</button>
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


    <form id="exact-connect-form" action="{{URL::to('/').'/exact'}}" method="get"></form>
@stop
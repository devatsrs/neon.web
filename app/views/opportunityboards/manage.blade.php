@extends('layout.main')

@section('content')
<style>

    .file-input-wrapper{
        height: 26px;
    }
    #rating i.entypo-star{
        font-size: 15px;
    }

</style>
<div id="content">
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('opportunityboards')}}">Opportunity Board</a>
        </li>
        <li class="active">
            <strong>{{$OpportunityBoard->OpportunityBoardName}}</strong>
        </li>
    </ol>

        <div class="row">
            <div class="col-md-12 clearfix">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form id="search-opportunity-filter" method="get"  action="" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                    <div class="panel panel-primary" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Filter
                            </div>
                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Opportunity Name</label>
                                <div class="col-sm-2">
                                    <input class="form-control" name="opportunityName"  type="text" >
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Account Owner</label>
                                <div class="col-sm-2">
                                    {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("class"=>"select2"))}}
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Company</label>
                                <div class="col-sm-2">
                                    {{Form::select('AccountID',$leads,Input::get('AccountID'),array("class"=>"select2"))}}
                                </div>
                            </div>
                            <p style="text-align: right;">
                                <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                    <i class="entypo-search"></i>
                                    Search
                                </button>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <p style="text-align: right;">
            <a href="javascript:void(0)" id="add-opportunity" class="btn btn-primary ">
                <i class="entypo-plus"></i>
                Add New Opportunity
            </a>
        </p>

        <section class="deals-board">
            <div id="board-start" class="board" style="height: 500px;">
            </div>
            <form id="cardorder" method="POST" />
                <input type="hidden" name="cardorder" />
                <input type="hidden" name="OpportunityBoardColumnID" />
            </form>
        </section>
    <script>
        var $searchFilter = {};
        var currentDrageable = '';
        $(document).ready(function ($) {
            var opportunity = [
                                'OpportunityBoardColumnID',
                                'OpportunityBoardColumnName',
                                'OpportunityID',
                                'OpportunityName',
                                'BackGroundColour',
                                'TextColour',
                                'Company',
                                'ContactName',
                                'Owner',
                                'UserID',
                                'Phone',
                                'Email',
                                'OpportunityBoardID',
                                'AccountID',
                                'Tags',
                                'Rating'
                                ];
            var readonly = ['Company','Phone','Email','ContactName'];
            var OpportunityBoardID = "{{$id}}";
            var usetId = "{{User::get_userID()}}";
            getOpportunities();
            $('#add-opportunity').click(function(){
                $('#add-edit-opportunity-form').trigger("reset");
                var select = ['UserID','AccountID','OpportunityBoardID'];
                for(var i = 0 ; i< opportunity.length; i++){
                    var elem = $('#add-edit-opportunity-form [name="'+opportunity[i]+'"]');
                    if(select.indexOf(opportunity[i])!=-1){
                        elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        if(opportunity[i]=='UserID'){
                            $('#add-edit-opportunity-form [name="UserID"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption(usetId);
                            $('#add-edit-opportunity-form [name="leadcheck"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption('No');
                        }
                    } else{
                        elem.val('');
                        if(opportunity[i]=='Tags'){
                            elem.val('').trigger("change");
                        }
                    }
                }
                $('#add-edit-modal-opportunity [name="OpportunityBoardID"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption(OpportunityBoardID);

                setcolor($('#add-edit-modal-opportunity [name="BackGroundColour"]'),'#303641');
                setcolor($('#add-edit-modal-opportunity [name="TextColour"]'),'#ffffff');
                setrating(1);
                $('#add-edit-modal-opportunity h4').text('Add Opportunity');
                $('#add-edit-modal-opportunity').modal('show');
            });

            $('#add-edit-opportunity-form [name="leadcheck"]').change(function(){
                var lead = $('#add-edit-modal-opportunity').find('.leads');
                if($(this).val()=='Yes') {
                    lead.removeClass('hidden');
                    setunsetreadonly('',true);
                }else{
                    lead.addClass('hidden');
                    setunsetreadonly('',false);
                }
            });

            $('#add-edit-opportunity-form [name="AccountID"]').change(function(){
                var AccountID = $(this).val();
                if(AccountID) {
                    var url = baseurl + '/opportunity/' + AccountID + '/getlead';
                    $.ajax({
                        url: url,  //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            setunsetreadonly(response[0],true);
                            var state = response[0].AccountType==0?true:false;
                            $('#add-edit-opportunity-form [name="leadOrAccount"]').bootstrapSwitch('setState', state, false);
                            //$('#add-edit-opportunity-form [name="leadOrAccount"]').prop('checked', checked);
                        },
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                }else{
                    if($('#add-edit-opportunity-form [name="leadcheck"]').val()=='Yes'){
                        setunsetreadonly('',true);
                    }else{
                        setunsetreadonly('',false);
                    }
                }
            });
            //@ToDO:fix button change issue.
            $('#add-edit-opportunity-form [name="leadOrAccount"]').on('change',function(){
                var check=1;
                if($(this).prop("checked")){
                    $('#leadlable').text('Existing lead');
                    $('.leads label').text('Lead');
                }else{
                    $('#leadlable').text('Existing Account');
                    $('.leads label').text('Account');
                    check = 2;
                }
                var url = baseurl + '/opportunity/'+check+'/getDropdownLeadAccount';
                getLeadOrAccount(url);
                if($('#add-edit-opportunity-form [name="leadcheck"]').val()=='Yes'){
                    setunsetreadonly('',true);
                }else{
                    setunsetreadonly('',false);
                }
            });

            $('#search-opportunity-filter').submit(function(e){
                e.preventDefault();
                getOpportunities();
            });

            $('#add-edit-opportunity-form').submit(function(e){
                e.preventDefault();
                var update_new_url = '';
                var opportunityID = $('#add-edit-opportunity-form').find('[name="OpportunityID"]').val();
                if( typeof opportunityID != 'undefined' && opportunityID != ''){
                    update_new_url = baseurl + '/opportunity/'+opportunityID+'/update';
                }else{
                    update_new_url = baseurl + '/opportunity/create';
                }
                var formData = new FormData($('#add-edit-opportunity-form')[0]);
                $.ajax({
                    url: update_new_url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-edit-modal-opportunity').modal('hide');
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $("#opportunity-update").button('reset');
                        getOpportunities();
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $(document).on('click','#board-start ul.sortable-list li i.edit-deal',function(e){
                e.stopPropagation();
                var rowHidden = $(this).parents('.tile-stats').siblings('.row-hidden');
                var select = ['UserID','AccountID','OpportunityBoardID'];
                var color = ['BackGroundColour','TextColour'];
                for(var i = 0 ; i< opportunity.length; i++){
                    var val = rowHidden.find('input[name="'+opportunity[i]+'"]').val();
                    var elem = $('#add-edit-opportunity-form [name="'+opportunity[i]+'"]');
                    if(select.indexOf(opportunity[i])!=-1){
                        if(opportunity[i]=='AccountID'){
                            if(val>0) {
                                $('#add-edit-opportunity-form [name="leadcheck"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption('Yes');
                            }else{
                                $('#add-edit-opportunity-form [name="leadcheck"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption('No');
                                val = '';
                            }
                        }
                        elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption(val);
                    } else{
                        elem.val(val);
                        if(color.indexOf(opportunity[i])!=-1){
                            setcolor(elem,val);
                        }else if(opportunity[i]=='Rating'){
                            setrating(val);
                        }else if(opportunity[i]=='Tags'){
                            elem.val(val).trigger("change");
                        }
                    }
                }
                $('#add-edit-modal-opportunity h4').text('Edit Opportunity');
                $('#add-edit-modal-opportunity').modal('show');
            });

            $(document).on('mousedown','#board-start ul.sortable-list li',function(e){
                //setting Class for current draggable item
                $(this).addClass('dragging');
            });

            $(document).on('mouseup','#board-start ul.sortable-list li',function(e){
                //remove Class for current draggable item
                $(this).removeClass('dragging');
            });

            $(document).on('click','#board-start ul.sortable-list li',function(){
                $('#add-opportunity-comments-form').trigger("reset");
                $('#allComments,#attachments').empty();
                var opportunityID = $(this).attr('data-id');
                var opportunityName = $(this).attr('data-name');
                $('#add-opportunity-comments-form [name="OpportunityID"]').val(opportunityID);
                $('#add-opportunity-attachment-form [name="OpportunityID"]').val(opportunityID);
                getComments();
                getOpportunityAttachment();
                $('#add-view-modal-opportunity-comments h4.modal-title').text(opportunityName);
                $('#add-view-modal-opportunity-comments').modal('show');
            });

            $('#add-opportunity-comments-form').submit(function(e){
                e.preventDefault();
                var formData = new FormData($('#add-opportunity-comments-form')[0]);
                var url = baseurl + '/opportunitycomment/create';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-opportunity-comments-form').trigger("reset");
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $("#commentadd").button('reset');
                        $('#add-opportunity-comments-form').trigger("reset");
                        $('#commentadd').siblings('.file-input-name').empty();
                        getComments();
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
            $(document).on('change','#add-opportunity-attachment-form input[type="file"]',function(){
                var opportunityID = $('#add-opportunity-attachment-form [name="OpportunityID"]').val();
                var formData = new FormData($('#add-opportunity-attachment-form')[0]);
                var url = baseurl + '/opportunity/'+opportunityID+'/saveattachment';
                var top = $(this).offset().top;
                top = top-300;
                $('#attachment_processing').css('top',top);
                $('#attachment_processing').removeClass('hidden');
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-opportunity-attachment-form').trigger("reset");
                            $('#attachment_processing').addClass('hidden');
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $('#add-opportunity-attachment-form').trigger("reset");
                        $('#addattachmentop .file-input-name').empty();
                        getOpportunityAttachment();
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $(document).on('click','#attachments i.delete-file',function(){
                var con = confirm('Do you delete current attachment?');
                if(!con){
                    return true;
                }
                var opportunityID = $('#add-opportunity-attachment-form [name="OpportunityID"]').val();
                var attachmentID = $(this).attr('data-id');
                var url = baseurl + '/opportunity/'+opportunityID+'/deleteattachment/'+attachmentID;
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        getOpportunityAttachment();
                    },
                    // Form data
                    data: [],
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $(document).on('mouseover','#rating i',function(){
                var currentrateid = $(this).attr('rate-id');
                setrating(currentrateid);
            });
            $(document).on('click','#rating i',function(){
                var currentrateid = $(this).attr('rate-id');
                $('#rating input[name="Rating"]').val(currentrateid);
                setrating(currentrateid);
            });
            $(document).on('mouseleave','#rating',function(){
                var defultrateid = $('#rating input[name="Rating"]').val();
                setrating(defultrateid);
            });

            $(".tags").select2({
                tags:{{$tags}}
            });

            $('#add-edit-modal-opportunity .reset').click(function(){
                var colorPicker = $(this).parents('.form-group').find('[type="text"].colorpicker');
                var color = $(this).attr('data-color');
                setcolor(colorPicker,color);
            });


            function initSortable(){
                // Code using $ as usual goes here.
                $('#board-start .sortable-list').sortable({
                    connectWith: '.sortable-list',
                    placeholder: 'placeholder',
                    start: function() {
                        //setting current draggable item
                        currentDrageable = $('#board-start ul.sortable-list li.dragging');
                    },
                    stop: function() {
                        postorder();
                        //de-setting draggable item after submit order.
                        currentDrageable = '';
                    }
                });
            }

            function getOpportunities(){
                var formData = new FormData($('#search-opportunity-filter')[0]);
                var url = baseurl + '/opportunity/'+OpportunityBoardID+'/ajax_opportunity';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#board-start').html(response);
                        initSortable();
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function getComments(){
                var opportunityID = $('#add-opportunity-comments-form [name="OpportunityID"]').val();
                var url = baseurl +'/opportunitycomments/'+opportunityID+'/ajax_opportunitycomments';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#allComments').html(response);
                    },
                    // Form data
                    data: [],
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function getOpportunityAttachment(){
                var opportunityID = $('#add-opportunity-comments-form [name="OpportunityID"]').val();
                var url = baseurl +'/opportunity/'+opportunityID+'/ajax_getattachments';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#attachments').html(response);
                    },
                    // Form data
                    data: [],
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function getLeadOrAccount(url){
                var formData = new FormData($('#add-edit-opportunity-form')[0]);
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        var elem = $('#add-edit-opportunity-form [name="AccountID"]');
                        //elem.select2('destroy');
                        elem.empty();
                        $.each(response.result,function(i,item){
                            elem.append('<option value="'+i+'">'+item+'</option>');
                        });
                        elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        //opts = {allowClear: attrDefault(elem, 'allowClear', false)};
                        //elem.select2(opts);
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function fillColumns(){
                var url = baseurl + '/opportunityboardcolumn/{{$id}}/ajax_datacolumn';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $('#deals-dashboard').empty();
                        $(response).each(function(i,item){
                            $('#deals-dashboard').append(builditem(item));
                            initdrageable();
                        });
                    },
                    // Form data
                    //data: {},
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function postorder(){
                saveOrder();
                url = baseurl + '/opportunity/'+OpportunityBoardID+'/updateColumnOrder';
                var formData = new FormData($('#cardorder')[0]);
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            getOpportunities();
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                            fillColumns();
                        }
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

            function saveOrder() {
                var selectedCards = new Array();
                var currentColumn = currentDrageable.parents('li.board-column');
                var OpportunityBoardColumnID = currentColumn.attr('data-id');
                currentColumn.find('ul.board-column-list li.count-cards').each(function() {
                    selectedCards.push($(this).attr("data-id"));
                });
                $('#cardorder [name="cardorder"]').val(selectedCards);
                $('#cardorder [name="OpportunityBoardColumnID"]').val(OpportunityBoardColumnID);
            }

            function setrating(currentrateid){
                $('#rating i').css('color','black');
                $('#rating i').each(function(){
                    var rateid = $(this).attr('rate-id');
                    if(currentrateid<rateid){
                        return false;
                    }
                    $(this).css('color','red');
                });
            }

            function setunsetreadonly(data,status){
                for(var i = 0 ; i< readonly.length; i++){
                    $('#add-edit-opportunity-form [name="'+readonly[i]+'"]').val('');
                    $('#add-edit-opportunity-form [name="'+readonly[i]+'"]').prop('readonly', status);
                    if(data){
                        $('#add-edit-opportunity-form [name="'+readonly[i]+'"]').val(data[readonly[i]]);
                    }
                }
            }

            function setcolor(elem,color){
                elem.colorpicker('destroy');
                elem.val(color);
                elem.colorpicker({color:color});
                elem.siblings('.input-group-addon').find('.color-preview').css('background-color', color);
            }

        });
    </script>
</div>
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="add-edit-modal-opportunity">
        <div class="modal-dialog" style="width: 70%;">
            <div class="modal-content">
                <form id="add-edit-opportunity-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Opportunity</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Account Owner *</label>
                                    <div class="col-sm-4">
                                        {{Form::select('UserID',$account_owners,'',array("class"=>"select2"))}}
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Opportunity Name *</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="OpportunityName" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Lead/Account</label>
                                    <div class="col-sm-4">
                                        <div class="make-switch switch-small" data-text-label="<i class='entypo-user'></i>" data-on-label="Lead" data-off-label="Account">
                                            <input type="checkbox" name="leadOrAccount" checked>
                                        </div>
                                    </div>
                                    <label id="leadlable" for="field-5" class="control-label col-sm-2">Existing Lead</label>
                                    <div class="col-sm-4">
                                        <?php $leadcheck = ['No'=>'No','Yes'=>'Yes']; ?>
                                        {{Form::select('leadcheck',$leadcheck,'',array("class"=>"selectboxit"))}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="leads hidden">
                                        <label for="field-5" class="control-label col-sm-2">Leads</label>
                                        <div class="col-sm-4">
                                            {{Form::select('AccountID',$leads,'',array("class"=>"select2"))}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Company</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="Company" class="form-control" id="field-5">
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Contact Name</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="ContactName" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Phone Number</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="Phone" class="form-control" id="field-5">
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Email Address</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="Email" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Select Board</label>
                                    <div class="col-sm-4">
                                        {{Form::select('OpportunityBoardID',$boards,'',array("class"=>"selectboxit"))}}
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Select Background</label>
                                    <div class="col-sm-3 input-group">
                                        <input name="BackGroundColour" type="text" class="form-control colorpicker" value="" />
                                        <div class="input-group-addon">
                                            <i class="color-preview"></i>
                                        </div>
                                    </div>
                                    <div class="col-sm-1">
                                        <button class="btn btn-xs btn-danger reset" data-color="#303641" type="button">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Text Color</label>
                                    <div class="col-sm-3 input-group">
                                        <input name="TextColour" type="text" class="form-control colorpicker" value="" />
                                        <div class="input-group-addon">
                                            <i class="color-preview"></i>
                                        </div>
                                    </div>
                                    <div class="col-sm-1">
                                        <button class="btn btn-xs btn-danger reset" data-color="#ffffff" type="button">Reset</button>
                                    </div>
                                    <label for="field-5" class="control-label col-sm-2">Tags</label>
                                    <div class="col-sm-4 input-group">
                                        <input class="form-control tags" name="Tags" type="text" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="input-1" class="control-label col-sm-2">Rate This</label>
                                    <div id="rating" class="col-sm-4">
                                        <i rate-id="1" class="entypo-star"></i>
                                        <i rate-id="2" class="entypo-star"></i>
                                        <i rate-id="3" class="entypo-star"></i>
                                        <i rate-id="4" class="entypo-star"></i>
                                        <i rate-id="5" class="entypo-star"></i>
                                        <input type="hidden" name="Rating" value="1" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="OpportunityID">
                        <button type="submit" id="opportunity-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add-view-modal-opportunity-comments">
        <div id="card-features-details" class="modal-dialog">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Opportunity Name</h4>
                    </div>
                    <div class="modal-body">
                        <form id="add-opportunity-comments-form" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <div class="col-md-12 text-left">
                                    <h4>Add Comment</h4>
                                </div>
                                <div class="col-md-12">
                                    <textarea class="form-control" name="CommentText" placeholder="Write a comment."></textarea>
                                </div>
                                <div class="col-sm-12 pull-right end-buttons" style="text-align: right;">
                                    <input type="hidden" name="OpportunityID" >
                                    <input type="file" name="commentattachment[]" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" multiple="1" data-label="<i class='entypo-attach'></i>Attachments" />&nbsp;
                                    <button data-loading-text="Loading..." id="commentadd" class="add btn btn-primary btn-sm btn-icon icon-left" type="submit" style="visibility: visible;">
                                        <i class="entypo-floppy"></i>
                                        Add Comment
                                    </button>
                                </div>
                            </div>
                        </form>
                        <br>
                        <div id="allComments" class="form-group">

                        </div>
                        <div id="attachments" class="form-group">
                        </div>
                        <div id="attachment_processing" class="dataTables_processing hidden">Processing...</div>
                        <form id="add-opportunity-attachment-form" method="post" enctype="multipart/form-data">
                            <div class="col-md-12" id="addattachmentop" style="text-align: right;">
                                <input type="file" name="opportunityattachment[]" data-loading-text="Loading..." class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" multiple="1" data-label="<i class='entypo-attach'></i>Add Attachments" />
                                <input type="hidden" name="OpportunityID" >
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
            </div>
        </div>
    </div>

@stop

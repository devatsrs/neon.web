@extends('layout.main')

@section('content')
<style>

    .file-input-wrapper{
        height: 26px;
    }

</style>
<div id="content">
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="javascript:void(0)">Opportunities</a>
        </li>
        <li>
            <a href="{{URL::to('opportunityboards')}}">Board</a>
        </li>
        <li class="active">
            <strong>{{$OpportunityBoard->OpportunityBoardName}}</strong>
        </li>
    </ol>

        <div class="row">
            <div class="col-md-12 clearfix">
                <h2>Sales Opportunity
                </h2>
            </div>
        </div>
    {{--<!- @todo:search fix->--}}
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
                                'AccountID'
                                ];
            var OpportunityBoardID = "{{$id}}";
            getOpportunities();
            $('#add-opportunity').click(function(){
                $('#add-edit-opportunity-form').trigger("reset");
                var select = ['UserID','AccountID','OpportunityBoardID'];
                for(var i = 0 ; i< opportunity.length; i++){
                    if(select.indexOf(opportunity[i])!=-1){
                        $('#add-edit-opportunity-form [name="'+opportunity[i]+'"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                        if(opportunity[i]=='UserID'){
                            $('#add-edit-opportunity-form [name="leadcheck"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption('No');
                        }
                    } else{
                        $('#add-edit-opportunity-form [name="'+opportunity[i]+'"]').val('');
                    }
                }
                $('#add-edit-modal-opportunity h4').text('Add Opportunity');
                $('#add-edit-modal-opportunity').modal('show');
            });

            $('#add-edit-opportunity-form [name="leadcheck"]').change(function(){
                var lead = $('#add-edit-opportunity-form').find('.leads');
                if($(this).val()=='Yes') {
                    lead.removeClass('hidden');
                }else{
                    lead.addClass('hidden');
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
                            var AccountName = response[0].AccountName;
                            var Phone = response[0].Phone;
                            var Email = response[0].Email;
                            var ContactName = response[0].ContactName;
                            $('#add-edit-opportunity-form [name="Company"]').val(AccountName);
                            $('#add-edit-opportunity-form [name="Company"]').prop('readonly', true);
                            $('#add-edit-opportunity-form [name="Phone"]').val(Phone);
                            $('#add-edit-opportunity-form [name="Phone"]').prop('readonly', true);
                            $('#add-edit-opportunity-form [name="Email"]').val(Email);
                            $('#add-edit-opportunity-form [name="Email"]').prop('readonly', true);
                            $('#add-edit-opportunity-form [name="ContactName"]').val(ContactName);
                            $('#add-edit-opportunity-form [name="ContactName"]').prop('readonly', true);
                        },
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                }else{
                    $('#add-edit-opportunity-form [name="Company"]').prop('readonly', false);
                    $('#add-edit-opportunity-form [name="Phone"]').prop('readonly', false);
                    $('#add-edit-opportunity-form [name="Email"]').prop('readonly', false);
                    $('#add-edit-opportunity-form [name="ContactName"]').prop('readonly', false);
                }
            });

            $('#search-opportunity-filter').submit(function(e){
                e.preventDefault();
                getOpportunities();
            });

            $('#add-edit-opportunity-form').submit(function(e){
                //@ToDo: fix html edit button
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
                for(var i = 0 ; i< opportunity.length; i++){
                    var val = rowHidden.find('input[name="'+opportunity[i]+'"]').val();
                    if(select.indexOf(opportunity[i])!=-1){
                        if(opportunity[i]=='AccountID'){
                            if(val>0) {
                                $('#add-edit-opportunity-form [name="leadcheck"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption('Yes');
                            }else{
                                $('#add-edit-opportunity-form [name="leadcheck"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption('No');
                                val = '';
                            }
                        }
                        $('#add-edit-opportunity-form [name="'+opportunity[i]+'"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption(val);
                    } else{
                        $('#add-edit-opportunity-form [name="'+opportunity[i]+'"]').val(val);
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
                var opportunityID = $(this).attr('data-id');
                $('#add-opportunity-comments-form [name="OpportunityID"]').val(opportunityID);
                $('#add-opportunity-attachment-form [name="OpportunityID"]').val(opportunityID);
                getComments();
                getOpportunityAttachment();
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
                $(this).button('Loading..');
                var opportunityID = $('#add-opportunity-attachment-form [name="OpportunityID"]').val();
                var formData = new FormData($('#add-opportunity-attachment-form')[0]);
                var url = baseurl + '/opportunity/'+opportunityID+'/saveattachment';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-opportunity-attachment-form').trigger("reset");
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(this).buttons('reset');
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

        });
    </script>
</div>
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="add-edit-modal-opportunity">
        <div class="modal-dialog">
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
                                    <label for="field-5" class="control-label">Account Owner *</label>
                                    {{Form::select('UserID',$account_owners,'',array("class"=>"select2"))}}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Opportunity Name *</label>
                                    <input type="text" name="OpportunityName" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Existing Lead</label>
                                    <?php $leadcheck = ['No'=>'No','Yes'=>'Yes']; ?>
                                    {{Form::select('leadcheck',$leadcheck,'',array("class"=>"selectboxit"))}}
                                </div>
                            </div>
                            <div class="col-md-12 leads hidden">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Leads</label>
                                    {{Form::select('AccountID',$leads,'',array("class"=>"select2"))}}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Company</label>
                                    <input type="text" name="Company" class="form-control" id="field-5">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Contact Name</label>
                                    <input type="text" name="ContactName" class="form-control" id="field-5">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Phone Number</label>
                                    <input type="text" name="Phone" class="form-control" id="field-5">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Email Address</label>
                                    <input type="text" name="Email" class="form-control" id="field-5">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Select Board </label>
                                    {{Form::select('OpportunityBoardID',$boards,'',array("class"=>"selectboxit"))}}
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Select Background </label>
                                    <div class="input-group">
                                        <input name="BackGroundColour" type="text" class="form-control colorpicker" data-format="hex" value="#ffffff" />
                                        <div class="input-group-addon">
                                            <i class="color-preview"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Text Color</label>
                                    <div class="input-group">
                                        <input name="TextColour" type="text" class="form-control colorpicker" data-format="hex" value="#ffffff" />
                                        <div class="input-group-addon">
                                            <i class="color-preview"></i>
                                        </div>
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
                                <div class="col-sm-7 pull-right end-buttons">
                                    <input type="file" name="commentattachment[]" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" multiple="1" data-label="<i class='entypo-attach'></i>Attachments" />
                                    <input type="hidden" name="OpportunityID" >
                                    <button data-loading-text="Loading..." id="commentadd" class="add btn btn-primary btn-sm btn-icon icon-left pull-right" type="submit" style="visibility: visible;">
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
                        <form id="add-opportunity-attachment-form" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <div id="addattachmentop" class="col-md-3 pull-right">
                                        <input type="file" name="opportunityattachment[]" data-loading-text="Loading..." class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" multiple="1" data-label="<i class='entypo-attach'></i>Add Attachments" />
                                        <input type="hidden" name="OpportunityID" >
                                    </div>
                                </div>
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

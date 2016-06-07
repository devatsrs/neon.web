@extends('layout.main')

@section('content')
<style>

    .file-input-wrapper{
        height: 26px;
    }

    .margin-top{
        margin-top:10px;
    }
    .margin-top-group{
        margin-top:15px;
    }
    .paddingleft-0{
        padding-left: 3px;
    }
    .paddingright-0{
        padding-right: 0px;
    }
    #add-modal-opportunity .btn-xs{
        padding:0px;
    }
    .resizevertical{
        resize:vertical;
    }

    .file-input-names span{
        cursor: pointer;
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
            <strong>{{$Board->BoardName}}</strong>
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
                                <label for="field-1" class="col-sm-1 control-label">Name</label>
                                <div class="col-sm-2">
                                    <input class="form-control" name="opportunityName"  type="text" >
                                </div>
                                @if(User::is_admin())
                                    <label for="field-1" class="col-sm-1 control-label">Account Owner</label>
                                    <div class="col-sm-2">
                                        {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("class"=>"select2"))}}
                                    </div>
                                @endif
                                <label for="field-1" class="col-sm-1 control-label">Company</label>
                                <div class="col-sm-2">
                                    {{Form::select('AccountID',$leadOrAccount,Input::get('AccountID'),array("class"=>"select2"))}}
                                </div>
                                <label for="field-1" class="col-sm-1 control-label">Tags</label>
                                <div class="col-sm-2">
                                    <input class="form-control opportunitytags" name="Tags" type="text" >
                                </div>

                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-1 control-label">Status</label>
                                <div class="col-sm-4">
                                    {{Form::select('Status[]', Opportunity::$status, Opportunity::$defaultSelectedStatus ,array("class"=>"select2","multiple"=>"multiple"))}}
                                </div>

                                <label class="col-sm-1 control-label">Close</label>
                                <div class="col-sm-1">
                                    <p class="make-switch switch-small">
                                        <input name="opportunityClosed" type="checkbox" value="{{Opportunity::Close}}">
                                    </p>
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
            <a href="javascript:void(0)" class="btn btn-primary opportunity">
                <i class="entypo-plus"></i>
                Add New Opportunity
            </a>
        </p>

        <section class="deals-board" >

                <div id="board-start" class="board" style="height: 600px;" >
                </div>

            <form id="cardorder" method="POST" />
                <input type="hidden" name="cardorder" />
                <input type="hidden" name="BoardColumnID" />
            </form>
        </section>
    <script>
        var $searchFilter = {};
        var currentDrageable = '';
        var fixedHeader = false;
        $(document).ready(function ($) {
            var opportunity = [
                'BoardColumnID',
                'BoardColumnName',
                'OpportunityID',
                'OpportunityName',
                'BackGroundColour',
                'TextColour',
                'Company',
                'Title',
                'FirstName',
                'LastName',
                'Owner',
                'UserID',
                'Phone',
                'Email',
                'BoardID',
                'AccountID',
                'Tags',
                'Rating',
                'TaggedUsers',
                'Status'
            ];

            @if(empty($message)){
                var allow_extensions  =   '{{$response_extensions}}';
            }@else {
                var allow_extensions  =  '';
                toastr.error({{'"'.$message.'"'}}, "Error", toastr_opts);
            }
            @endif;
            var readonly = ['Company','Phone','Email','Title','FirstName','LastName'];
            var BoardID = "{{$BoardID}}";
            var board = $('#board-start');
            var email_file_list     =    new Array();
            var token               =   '{{$token}}';
            var max_file_size_txt   =   '{{$max_file_size}}';
            var max_file_size       =   '{{str_replace("M","",$max_file_size)}}';

            board.perfectScrollbar({minScrollbarLength: 20,handlers: ['click-rail','drag-scrollbar', 'keyboard', 'wheel', 'touch']});
            board.on('mouseenter',function(){
                board.perfectScrollbar('update');
            });

            $( window ).resize(function() {
                board.perfectScrollbar('update');
            });


            getOpportunities();

            $('#search-opportunity-filter').submit(function(e){
                e.preventDefault();
                getOpportunities();
            });


            $(document).on('click','#board-start ul.sortable-list li button.edit-deal',function(e){
                e.stopPropagation();
                var rowHidden = $(this).parents('.tile-stats').children('div.row-hidden');
                var select = ['UserID','BoardID','TaggedUsers','Title','Status'];
                var color = ['BackGroundColour','TextColour'];
                for(var i = 0 ; i< opportunity.length; i++){
                    var val = rowHidden.find('input[name="'+opportunity[i]+'"]').val();
                    var elem = $('#edit-opportunity-form [name="'+opportunity[i]+'"]');
                    //console.log(opportunity[i]+' '+val);
                    if(select.indexOf(opportunity[i])!=-1){
                        if(opportunity[i]=='TaggedUsers'){
                            var taggedUsers = rowHidden.find('[name="TaggedUsers"]').val();
                            $('#edit-opportunity-form [name="TaggedUsers[]"]').select2('val', taggedUsers.split(','));
                        }else {
                            if(opportunity[i]=='Status' && val=='{{Opportunity::Close}}'){
                                biuldSwicth('.make','#edit-opportunity-form','checked');
                            }else if(opportunity[i]=='Status' && val!='{{Opportunity::Close}}'){
                                biuldSwicth('.make','#edit-opportunity-form','');
                            }
                            elem.selectBoxIt().data("selectBox-selectBoxIt").selectOption(val);
                        }
                    } else{
                        elem.val(val);
                        if(color.indexOf(opportunity[i])!=-1){
                            setcolor(elem,val);
                        }else if(opportunity[i]=='Rating'){
                            elem.val(val).trigger('change');
                        }else if(opportunity[i]=='Tags'){
                            elem.val(val).trigger("change");
                        }
                    }
                }
                $('#edit-modal-opportunity h4').text('Edit Opportunity');
                $('#edit-modal-opportunity').modal('show');
            });

            $(document).on('click','#board-start ul.sortable-list li',function(){
                $('#add-opportunity-comments-form').trigger("reset");
                $('.sendmail').removeClass('hidden');
                var rowHidden = $(this).children('div.row-hidden');
                $('#allComments,#attachments').empty();
                var opportunityID = rowHidden.find('[name="OpportunityID"]').val();
                var accountID = rowHidden.find('[name="AccountID"]').val();
                var opportunityName = rowHidden.find('[name="OpportunityName"]').val();
                if(!accountID){
                    $('.sendmail').addClass('hidden');
                }
                $('#add-opportunity-comments-form [name="OpportunityID"]').val(opportunityID);
                $('#add-opportunity-attachment-form [name="OpportunityID"]').val(opportunityID);
                $('#add-opportunity-attachment-form [name="AccountID"]').val(accountID);
                $('#add-opportunity-comments-form [name="AccountID"]').val(accountID);
                $('#add-view-modal-opportunity-comments h4.modal-title').text(opportunityName);
                getComments();
                getOpportunityAttachment();
                autosizeUpdate();
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
                            email_file_list = [];
                            $(".file-input-names").empty();
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-opportunity-comments-form').trigger("reset");
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $("#commentadd").button('reset');
                        $('#add-opportunity-comments-form').trigger("reset");
                        $('#commentadd').siblings('.file-input-name').empty();
                        autosizeUpdate();
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
                var con = confirm('Are you sure you want to delete this attachments?');
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
                        getComments();
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

            $(document).on('click','#addTtachment',function(){
                $('#filecontrole1').click();
            });

            $(document).on('change','#filecontrole',function(e){
                var files = e.target.files;
                var fileText = '';
                for(i=0;i<files.length;i++){
                    fileText+=files[i].name+'<br>';
                }
                $('#commentadd').siblings('.file-input-name').html(fileText);
            });
            $(document).on('click','.viewattachments',function(){
                $(this).siblings('.comment-attachment').toggleClass('hidden');
            });


            $(document).on('change','#filecontrole1',function(e){
                e.stopImmediatePropagation();
                e.preventDefault();
                var files     = e.target.files;
                var fileText    = new Array();
                var file_check   = 1;
                var local_array   =  new Array();
                var filesArr = Array.prototype.slice.call(files);
                filesArr.forEach(function(f) {
                    var ext_current_file  = f.name.split('.').pop();
                    if(allow_extensions.indexOf(ext_current_file.toLowerCase()) > -1 ) {
                        var name_file = f.name;
                        var index_file = email_file_list.indexOf(f.name);
                        if(index_file >-1 ) {
                            ShowToastr("error",f.name+" file already selected.");
                        } else if(bytesToSize(f.size)) {
                            ShowToastr("error",f.name+" file size exceeds then upload limit ("+max_file_size_txt+"). Please select files again.");
                            file_check = 0;
                            return false;
                        }else {
                            //email_file_list.push(f.name);
                            local_array.push(f.name);
                        }
                    } else {
                        ShowToastr("error",ext_current_file+" file type not allowed.");
                    }
                });
                if(local_array.length>0 && file_check==1) {
                    email_file_list = email_file_list.concat(local_array);

                    var formData = new FormData($('#add-opportunity-comments-form')[0]);
                    var url = baseurl + '/opportunity/upload_file';
                    $.ajax({
                        url: url,  //Server script to process data
                        type: 'POST',
                        success: function (response) {
                            if (isJson(response)) {
                                var response_json  =  JSON.parse(response);
                                ShowToastr("error",response_json.message);
                            } else {
                                $('#card-features-details').find('.file-input-names').html(response);
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
            });

            $(document).on("click",".del_attachment",function(ee){
                var file_delete_url  =  baseurl + '/opportunity/delete_attachment_file';
                var del_file_name   =  $(this).attr('del_file_name');
                $(this).parent().remove();
                var index_file = email_file_list.indexOf(del_file_name);
                email_file_list.splice(index_file, 1);
                $.ajax({
                    url: file_delete_url,
                    type: 'POST',
                    dataType: 'html',
                    data:{file:del_file_name,token_attachment:token},
                    async :false,
                    success: function(response1) {}
                });
            });

            $('#add-view-modal-opportunity-comments').on('shown.bs.modal', function(event){
                email_file_list = [];
                $(".file-input-names").empty();
                var file_delete_url  =  baseurl + '/opportunity/delete_attachment_file';
                $.ajax({
                    url: file_delete_url,
                    type: 'POST',
                    dataType: 'html',
                    data:{token_attachment:token,destroy:1},
                    async :false,
                    success: function(response1) {}
                });

            });

            $(document).on('mouseover','#attachments a',
                    function(){
                        var a = $(this).attr('alt');
                        $(this).html(a);
                    }
            );

            $(document).on('mouseout','#attachments a',function(){
                var a = $(this).attr('alt');
                if(a.length>8){
                    a  = a.substring(0,8)+"..";
                }
                $(this).html(a);
            });

            function initEnhancement(){
                board.find('.board-column-list').perfectScrollbar({minScrollbarLength: 20,handlers: ['click-rail','drag-scrollbar', 'keyboard', 'wheel', 'touch']});
                board.find('.board-column-list').on('mouseenter',function(){
                    $(this).perfectScrollbar('update');
                });

                $( window ).resize(function() {
                    board.find('.board-column-list').perfectScrollbar('update');
                });
            }
            function initSortable(){
                // Code using $ as usual goes here.
                $('#board-start .sortable-list').sortable({
                    connectWith: '.sortable-list',
                    placeholder: 'placeholder',
                    start: function() {
                        //setting current draggable item
                        currentDrageable = $('#board-start ul.sortable-list li.dragging');
                    },
                    stop: function(ev,ui) {
                        postorder(ui.item);
                        //de-setting draggable item after submit order.
                        currentDrageable = '';
                    }
                });
            }

            function initToolTip(){
                $('[data-toggle="tooltip"]').each(function(i, el)
                {
                    var $this = $(el),
                            placement = attrDefault($this, 'placement', 'top'),
                            trigger = attrDefault($this, 'trigger', 'hover'),
                            popover_class = $this.hasClass('tooltip-secondary') ? 'tooltip-secondary' : ($this.hasClass('tooltip-primary') ? 'tooltip-primary' : ($this.hasClass('tooltip-default') ? 'tooltip-default' : ''));

                    $this.tooltip({
                        placement: placement,
                        trigger: trigger
                    });
                    $this.on('shown.bs.tooltip', function(ev)
                    {
                        var $tooltip = $this.next();

                        $tooltip.addClass(popover_class);
                    });
                });
            }

            function autosizeUpdate(){
                $('.autogrow').trigger('autosize.resize');
            }

            function biuldSwicth(container,formID,checked){
                var make = '<span class="make-switch switch-small">';
                make += '<input name="opportunityClosed" value="{{Opportunity::Close}}" '+checked+' type="checkbox">';
                make +='</span>';

                var container = $(formID).find(container);
                container.empty();
                container.html(make);
                container.find('.make-switch').bootstrapSwitch();
            }

            function getOpportunities(){
                var formData = new FormData($('#search-opportunity-filter')[0]);
                var url = baseurl + '/opportunity/'+BoardID+'/ajax_opportunity';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        board.html(response);
                        initEnhancement();
                        initSortable();
                        initToolTip();
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
                $('#comment_processing').removeClass('hidden');
                var opportunityID = $('#add-opportunity-comments-form [name="OpportunityID"]').val();
                var url = baseurl +'/opportunitycomments/'+opportunityID+'/ajax_opportunitycomments';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#comment_processing').addClass('hidden');
                        if(response.status){
                            toastr.error(response.message, "Error", toastr_opts);
                        }else {
                            $('#allComments').html(response);
                            $('#allComments .perfect-scrollbar').perfectScrollbar({minScrollbarLength: 20,handlers: ['click-rail','drag-scrollbar', 'keyboard', 'wheel', 'touch']});
                            $('#allComments .perfect-scrollbar').on('mouseenter',function(){
                                $(this).perfectScrollbar('update');
                            });
                        }
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
                var url = baseurl + '/opportunityboardcolumn/{{$BoardID}}/ajax_datacolumn';
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

            function postorder(elem){
                saveOrder(elem);
                url = baseurl + '/opportunity/'+BoardID+'/updateColumnOrder';
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

            function saveOrder(elem) {
                var selectedCards = new Array();
                var currentColumn = elem.parents('li.board-column');
                var BoardColumnID = currentColumn.attr('data-id');
                currentColumn.find('ul.board-column-list li.count-cards').each(function() {
                    selectedCards.push($(this).attr("data-id"));
                });
                $('#cardorder [name="cardorder"]').val(selectedCards);
                $('#cardorder [name="BoardColumnID"]').val(BoardColumnID);
            }

            function setcolor(elem,color){
                elem.colorpicker('destroy');
                elem.val(color);
                elem.colorpicker({color:color});
                elem.siblings('.input-group-addon').find('.color-preview').css('background-color', color);
            }

            function bytesToSize(filesize) {
                var sizeInMB = (filesize / (1024*1024)).toFixed(2);
                if(sizeInMB>max_file_size)
                {return 1;}else{return 0;}
            }
        });
    </script>
</div>
@include('opportunityboards.opportunitymodal')
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="edit-modal-opportunity">
        <div class="modal-dialog" style="width: 70%;">
            <div class="modal-content">
                <form id="edit-opportunity-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Opportunity</h4>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-12 text-left">
                                <label for="field-5" class="control-label col-sm-2">Tag User</label>
                                <div class="col-sm-10">
                                    <?php unset($account_owners['']); ?>
                                    {{Form::select('TaggedUsers[]',$account_owners,[],array("class"=>"select2","multiple"=>"multiple"))}}
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Account Owner *</label>
                                    <div class="col-sm-8">
                                        {{Form::select('UserID',$account_owners,'',array("class"=>"select2"))}}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Opportunity Name *</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="OpportunityName" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="input-1" class="control-label col-sm-4">Rate This</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="knob" data-min="0" data-max="5" data-width="85" data-height="85" name="Rating" value="0" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top-group pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">First Name*</label>
                                    <div class="col-sm-8">
                                        <div class="input-group" style="width: 100%;">
                                            <div class="input-group-addon" style="padding: 0px; width: 85px;">
                                                <?php $NamePrefix_array = array( ""=>"-None-" ,"Mr"=>"Mr", "Miss"=>"Miss" , "Mrs"=>"Mrs" ); ?>
                                                {{Form::select('Title', $NamePrefix_array, '' ,array("class"=>"selectboxit"))}}
                                            </div>
                                            <input type="text" name="FirstName" class="form-control" id="field-5">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Last Name*</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="LastName" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Company*</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="Company" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Phone Number*</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="Phone" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Email Address*</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="Email" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top-group pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Status</label>
                                    <div class="col-sm-8 input-group">
                                        {{Form::select('Status', Opportunity::$status, '' ,array("class"=>"selectboxit"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Select Board*</label>
                                    <div class="col-sm-8">
                                        {{Form::select('BoardID',$boards,'',array("class"=>"selectboxit"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Tags</label>
                                    <div class="col-sm-8 input-group">
                                        <input class="form-control opportunitytags" name="Tags" type="text" >
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top-group pull-left">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Close</label>
                                    <div class="col-sm-8 make">
                                        <p class="make-switch switch-small">
                                            <input name="opportunityClosed" type="checkbox" value="{{Opportunity::Close}}">
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!--<div class="col-md-6 margin-top-group pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Select Background</label>
                                    <div class="col-sm-7 input-group paddingright-0">
                                        <input name="BackGroundColour" type="text" class="form-control colorpicker" value="" />
                                        <div class="input-group-addon">
                                            <i class="color-preview"></i>
                                        </div>
                                    </div>
                                    <div class="col-sm-1 paddingleft-0">
                                        <a class="btn btn-primary btn-xs reset" data-color="#303641" href="javascript:void(0)">
                                            <i class="entypo-ccw"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>-->

                            <!--<div class="col-md-6 margin-top-group pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Text Color</label>
                                    <div class="col-sm-7 input-group paddingright-0">
                                        <input name="TextColour" type="text" class="form-control colorpicker" value="" />
                                        <div class="input-group-addon">
                                            <i class="color-preview"></i>
                                        </div>
                                    </div>
                                    <div class="col-sm-1 paddingleft-0">
                                        <a class="btn btn-primary btn-xs reset" data-color="#ffffff" href="javascript:void(0)">
                                            <i class="entypo-ccw"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>-->
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

    <div class="modal fade" id="add-view-modal-opportunity-comments" data-backdrop="static">
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
                                    <textarea class="form-control autogrow resizevertical" name="CommentText" placeholder="Write a comment."></textarea>
                                </div>
                                <div class="col-md-11">
                                </div>
                                <div class="col-md-1">
                                    <p class="comment-box-options">
                                        <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)">
                                            <i class="entypo-attach"></i>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-sm-6 pull-left end-buttons sendmail" style="text-align: left;">
                                    <label for="field-5" class="control-label">Send Mail To Customer:</label>
                                    <span id="label-switch" class="make-switch switch-small">
                                        <input name="PrivateComment" value="1" type="checkbox">
                                    </span>
                                </div>
                                <div class="col-sm-6 pull-right end-buttons" style="text-align: right;">
                                    <input type="hidden" name="OpportunityID" >
                                    <input type="hidden" name="AccountID" >
                                    <button data-loading-text="Loading..." id="commentadd" class="add btn btn-primary btn-sm btn-icon icon-left" type="submit" style="visibility: visible;">
                                        <i class="entypo-floppy"></i>
                                        Add Comment
                                    </button>
                                    <br>
                                    <div class="file_attachment">
                                        <div class="file-input-names"></div>
                                        <input id="filecontrole1" type="file" name="commentattachment[]" class="hidden" multiple="1" data-label="<i class='entypo-attach'></i>Attachments" />&nbsp;
                                        <input  type="hidden" name="token_attachment" value="{{$token}}" />
                                    </div>
                                </div>
                            </div>
                        </form>
                        <br>
                        <div id="comment_processing" class="dataTables_processing hidden">Processing...</div>
                        <div id="allComments" class="form-group">

                        </div>
                        <div id="attachments" class="form-group">
                        </div>
                        <div id="attachment_processing" class="dataTables_processing hidden">Processing...</div>
                        <form id="add-opportunity-attachment-form" method="post" enctype="multipart/form-data">
                            <div class="col-md-8"></div>
                            <div class="col-md-4" id="addattachmentop" style="text-align: right;">
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

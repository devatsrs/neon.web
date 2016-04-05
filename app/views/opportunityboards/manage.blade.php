@extends('layout.main')

@section('content')
<style>

    .file-input-wrapper{
        height: 26px;
    }
    #rating i.entypo-star{
        font-size: 15px;
    }

    #add-modal-opportunity .margin-top{
        margin-top:10px;
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
                                <label for="field-1" class="col-sm-1 control-label">Opportunity Name</label>
                                <div class="col-sm-3">
                                    <input class="form-control" name="opportunityName"  type="text" >
                                </div>
                                @if(User::is_admin())
                                    <label for="field-1" class="col-sm-1 control-label">Account Owner</label>
                                    <div class="col-sm-3">
                                        {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("class"=>"select2"))}}
                                    </div>
                                @endif
                                <label for="field-1" class="col-sm-1 control-label">Company</label>
                                <div class="col-sm-3">
                                    {{Form::select('AccountID',$leadOrAccount,Input::get('AccountID'),array("class"=>"select2"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-1 control-label">Tags</label>
                                <div class="col-sm-3">
                                    <input class="form-control opportunitytags" name="Tags" type="text" >
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

        <section class="deals-board">
            <div id="board-start" class="board scroller" style="height: 500px;">
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
                'ContactName',
                'Owner',
                'UserID',
                'Phone',
                'Email',
                'BoardID',
                'AccountID',
                'Tags',
                'Rating',
                'TaggedUser'
            ];
            var readonly = ['Company','Phone','Email','ContactName'];
            var BoardID = "{{$BoardID}}";
            getOpportunities();

            $('#search-opportunity-filter').submit(function(e){
                e.preventDefault();
                getOpportunities();
            });


            $(document).on('click','#board-start ul.sortable-list li i.edit-deal',function(e){
                e.stopPropagation();
                var rowHidden = $(this).parents('.tile-stats').children('div.row-hidden');
                var select = ['UserID','BoardID','TaggedUser'];
                var color = ['BackGroundColour','TextColour'];
                for(var i = 0 ; i< opportunity.length; i++){
                    var val = rowHidden.find('input[name="'+opportunity[i]+'"]').val();
                    var elem = $('#edit-opportunity-form [name="'+opportunity[i]+'"]');
                    //console.log(opportunity[i]+' '+val);
                    if(select.indexOf(opportunity[i])!=-1){
                        if(opportunity[i]=='TaggedUser'){
                            var taggedUser = rowHidden.find('[name="TaggedUser"]').val();
                            $('#edit-opportunity-form [name="TaggedUser[]"]').select2('val', taggedUser.split(','));
                        }else {
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
                var rowHidden = $(this).children('div.row-hidden');
                $('#allComments,#attachments').empty();
                var opportunityID = rowHidden.find('[name="OpportunityID"]').val();
                var accountID = rowHidden.find('[name="AccountID"]').val();
                var opportunityName = rowHidden.find('[name="OpportunityName"]').val();
                $('#add-opportunity-comments-form [name="OpportunityID"]').val(opportunityID);
                $('#add-opportunity-attachment-form [name="OpportunityID"]').val(opportunityID);
                $('#add-opportunity-attachment-form [name="AccountID"]').val(accountID);
                $('#add-view-modal-opportunity-comments h4.modal-title').text(opportunityName);
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

            $('#addTtachment').click(function(){
                $('#filecontrole').click();
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

            $('#board-start').scroll(function(){
                if(fixedHeader){
                    var header = $('#board-start .header');
                    var left = $('#board-start').scrollLeft();
                    header.css('right',left-1009);
                }
            });

            function initEnhancement(){
                var board = $('#board-start');
                var height = board.find('ul.board-inner li:first-child').height();
                var width = board.find('.board-inner').width();
                board.height(height+230);
                board.find('.header').width(width *2);
                $(document).on('scroll',function(){
                    if(board.offset().top < $(document).scrollTop() && !fixedHeader){
                        fixedHeader = true;
                        board.find('.header').addClass('fixed');
                    }else if(board.offset().top > $(document).scrollTop() && fixedHeader){
                        fixedHeader = false;
                        board.find('.header').removeClass('fixed');
                    }
                });

                var nicescroll_defaults = {
                    cursorcolor: '#d4d4d4',
                    cursorborder: '1px solid #ccc',
                    railpadding: {right: 3},
                    cursorborderradius: 1,
                    autohidemode: true,
                    sensitiverail: false
                };

                board.niceScroll(nicescroll_defaults);
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
                    stop: function() {
                        postorder();
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

            function getOpportunities(){
                var formData = new FormData($('#search-opportunity-filter')[0]);
                var url = baseurl + '/opportunity/'+BoardID+'/ajax_opportunity';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#board-start').html(response);
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
                var opportunityID = $('#add-opportunity-comments-form [name="OpportunityID"]').val();
                var url = baseurl +'/opportunitycomments/'+opportunityID+'/ajax_opportunitycomments';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'html',
                    success: function (response) {
                        $('#allComments').html(response);
                        var nicescroll_defaults = {
                            cursorcolor: '#d4d4d4',
                            cursorborder: '1px solid #ccc',
                            railpadding: {right: 3},
                            cursorborderradius: 1,
                            autohidemode: true,
                            sensitiverail: false
                        };
                        $('#allComments .fancyscroll').niceScroll(nicescroll_defaults);

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

            function postorder(){
                saveOrder();
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

            function saveOrder() {
                var selectedCards = new Array();
                var currentColumn = currentDrageable.parents('li.board-column');
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
                                <label for="field-5" class="control-label col-sm-2">Tag User *</label>
                                <div class="col-sm-10">
                                    <?php unset($account_owners['']); ?>
                                    {{Form::select('TaggedUser[]',$account_owners,[],array("class"=>"select2","multiple"=>"multiple"))}}
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

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Company</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="Company" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Contact Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="ContactName" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Phone Number</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="Phone" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Email Address</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="Email" class="form-control" id="field-5">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Select Board</label>
                                    <div class="col-sm-8">
                                        {{Form::select('BoardID',$boards,'',array("class"=>"selectboxit"))}}
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6 margin-top pull-right">
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
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Tags</label>
                                    <div class="col-sm-8 input-group">
                                        <input class="form-control opportunitytags" name="Tags" type="text" >
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
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
                                        <!--<button class="btn btn-xs btn-danger reset" data-color="#ffffff" type="button">Reset</button>-->
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
                                    <textarea class="form-control resizevertical" name="CommentText" placeholder="Write a comment."></textarea>
                                    <p class="comment-box-options">
                                        <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)">
                                            <i class="entypo-attach"></i>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-sm-6 pull-left end-buttons" style="text-align: left;">
                                    <label for="field-5" class="control-label">Send Mail To Customer:</label>
                                    <span id="label-switch" class="make-switch switch-mini">
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
                                    <input id="filecontrole" type="file" name="commentattachment[]" class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left hidden" multiple="1" data-label="<i class='entypo-attach'></i>Attachments" />&nbsp;
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

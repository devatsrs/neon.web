@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="search-task-filter" method="get"  action="" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Subject</label>
                    <input class="form-control" name="taskName"  type="text" >
                </div>
                @if(User::is_admin())
                    <div class="form-group">
                        <label for="field-1" class="control-label">Assign To</label>
                        {{Form::select('AccountOwner',$account_owners,User::get_userID(),array("class"=>"select2"))}}
                    </div>
                @endif
                <div class="form-group">
                    <label class="control-label">Priority</label><br/>
                    <p class="make-switch switch-small">
                        <input name="Priority" type="checkbox" value="1" >
                    </p>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Status</label>
                    {{Form::select('TaskStatus',[''=>'Select a Status']+$taskStatus,'',array("class"=>"select2 small"))}}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Company</label>
                    {{Form::select('AccountIDs',$leadOrAccount,'',array("class"=>"select2"))}}
                </div>
                <div class="form-group">
                    <label class="control-label">Close</label><br/>
                    <p class="make-switch switch-small">
                        <input name="taskClosed" type="checkbox" value="{{Task::Close}}">
                    </p>
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Due Date</label>
                    {{Form::select('DueDateFilter',Task::$tasks,'',array("class"=>"select2 small"))}}
                </div>
                <div class="form-group">
                    <div class="hidden tohidden">
                        <input autocomplete="off" id="DueDateFrom" placeholder="From" type="text" name="DueDateFrom" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="hidden tohidden">
                        <input autocomplete="off" id="DueDateTo" placeholder="To" type="text" name="DueDateTo" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value="" />
                    </div>
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')
<style>

    .file-input-wrapper{
        height: 26px;
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
    #add-modal-task .btn-xs{
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
        <li class="active">
            <strong>{{$Board[0]->BoardName}}</strong>
        </li>
    </ol>
    <h3>Tasks</h3>
    <div class="row">
        <div class="col-md-12 clearfix">
        </div>
    </div>

        <p id="tools">
            <a class="btn btn-primary toggle grid active" title="Grid View" href="javascript:void(0)"><i class="entypo-book-open"></i></a>
            <a class="btn btn-primary toggle list" title="List View" href="javascript:void(0)"><i class="entypo-list"></i></a>
            @if(User::checkCategoryPermission('Task','Add'))
                <a href="javascript:void(0)" class="btn btn-primary pull-right task">
                    <i class="entypo-plus"></i>
                    Add Task
                </a>
            @endif
            @if(User::checkCategoryPermission('TaskBoard','Configure'))
                <a href="{{URL::to('task/'.$Board[0]->BoardID.'/configure')}}" class="btn btn-primary pull-right">
                    <i class="entypo-cog"></i>
                    Configure Board
                </a>
            @endif
        </p>

        <section class="deals-board">
            <table class="table table-bordered datatable hidden" id="taskGrid">
                <thead>
                <tr>
                    <th width="30%" >Subject</th>
                    <th width="10%" >Due Date</th>
                    <th width="10%" >Status</th>
                    <th width="20%">Assigned To</th>
                    <th width="20%">Related To</th>
                    <th width="10%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div id="board-start" class="board scroller" style="height: 500px;">
            </div>
            <form id="cardorder" method="POST" />
                <input type="hidden" name="cardorder" />
                <input type="hidden" name="BoardColumnID" />
            </form>
        </section>
    <?php $BoardID = $Board[0]->BoardID; ?>
    <script>
        var $searchFilter = {};
        var currentDrageable = '';
        var fixedHeader = false;
        $(document).ready(function(){

            $('#filter-button-toggle').show();

            var task = [
                'BoardColumnID',
                'BoardColumnName',
                'SetCompleted',
                'TaskID',
                'UsersIDs',
                'Users',
                'AccountIDs',
                'company',
                'Subject',
                'Description',
                'DueDate',
                'StartTime',
                'TaskStatus',
                'Priority',
                'PriorityText',
                'TaggedUsers',
                'BoardID',
                'userName',
                'taskClosed'
            ];
            @if(empty($message)){
                var allow_extensions  =   '{{$response_extensions}}';
            }@else {
                var allow_extensions  =  '';
                toastr.error({{'"'.$message.'"'}}, "Error", toastr_opts);
            }
            @endif;
            var BoardID = '{{$Board[0]->BoardID}}';
            var board = $('#board-start');
            var emailFileList     =    new Array();
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
            getTask();
            data_table = $("#taskGrid").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/task/"+BoardID+"/ajax_task_grid",
                "fnServerParams": function (aoData) {
                    aoData.push(
                            {"name": "taskName", "value": $searchFilter.taskName},
                            {"name": "AccountOwner","value": $searchFilter.AccountOwner},
                            {"name": "Priority","value": $searchFilter.Priority},
                            {"name": "DueDateFilter","value": $searchFilter.DueDateFilter},
                            {"name": "DueDateFrom","value": $searchFilter.DueDateFrom},
                            {"name": "DueDateTo","value": $searchFilter.DueDateTo},
                            {"name": "TaskStatus","value": $searchFilter.TaskStatus},
                            {"name": "AccountIDs","value": $searchFilter.AccountIDs},
                            {"name": "taskClosed","value": $searchFilter.taskClosed}
                    );
                },
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[1, 'desc']],
                "aoColumns": [
                    {
                        "bSortable": true, //Subject
                        mRender: function (id, type, full) {
                            return '<div class="'+(full[13] == "1"?'priority':'normal')+' inlinetable">&nbsp;</div>'+'<div class="inlinetable">'+full[8]+'</div>';
                        }
                    },
                    {
                        "bSortable": true, //Due Date
                        mRender: function (id, type, full) {
                            return full[10]!='0000-00-00'?full[10]:'';
                        }
                    },
                    {
                        "bSortable": true, //Status
                        mRender: function (id, type, full) {
                            return full[1];
                        }
                    },
                    {
                        "bSortable": true, //Assign To
                        mRender: function (id, type, full) {
                            return full[5];
                        }
                    },
                    {
                        "bSortable": true, //Related To
                        mRender: function (id, type, full) {
                            return full[7];
                        }
                    },
                    {
                        "bSortable": false, //action
                        mRender: function (id, type, full) {
                            action = '<div class = "hiddenRowData" >';
                            for(var i = 0 ; i< task.length; i++){
                                action += '<input type = "hidden"  name = "' + task[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                            }
                            action += '</div>';
                            @if(User::checkCategoryPermission('Task','Edit'))
                            action += ' <a data-id="' + full[2] + '" class="edit-deal btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                            @endif
                            return action;
                        }
                    }
                ],
                "oTableTools": {
                    "aButtons": [
                    ]
                },
                "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                    $('td:eq(0)', nRow).css('padding', '0');
                }
                ,
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    $(this)
                    if($('#tools .active').hasClass('grid')){
                        $('#taskGrid_wrapper').addClass('hidden');
                        $('#taskGrid').addClass('hidden');
                    }else{
                        $('#taskGrid_wrapper').removeClass('hidden');
                        $('#taskGrid').removeClass('hidden');
                    }
                }

            });
            getRecord();
            $('#search-task-filter').submit(function(e){
                e.preventDefault();
                getRecord();
            });
            @if(User::checkCategoryPermission('Task','Edit'))
            $(document).on('click','#board-start ul.sortable-list li button.edit-deal,#taskGrid .edit-deal',function(e){
                e.stopPropagation();
                if($(this).is('a')){
                    var rowHidden = $(this).prev('div.hiddenRowData');
                }else {
                    var rowHidden = $(this).parents('.tile-stats').children('div.row-hidden');
                }
                var select = ['UsersIDs','AccountIDs','TaskStatus','TaggedUsers'];
                var color = ['BackGroundColour','TextColour'];
                for(var i = 0 ; i< task.length; i++){
                    var val = rowHidden.find('input[name="'+task[i]+'"]').val();
                    var elem = $('#edit-task-form [name="'+task[i]+'"]');
                    if(select.indexOf(task[i])!=-1){
                        if(task[i]=='TaggedUsers') {
                            $('#edit-task-form [name="' + task[i] + '[]"]').select2('val', val.split(','));
                        }else {
                            elem.val(val).trigger("change");
                        }
                    } else{
                        elem.val(val);
                        if(color.indexOf(task[i])!=-1){
                            /*setcolor(elem,val);*/
                        }else if(task[i]=='Tags'){
                            elem.val(val).trigger("change");
                        }else if(task[i]=='Priority'){
                            if(val==1) {
                                biuldSwicth('.make','Priority','#edit-modal-task','checked');
                            }else{
                                biuldSwicth('.make','Priority','#edit-modal-task','');
                            }
                        }else if(task[i]=='taskClosed'){
                            if(val==1) {
                                biuldSwicth('.taskClosed','taskClosed','#edit-modal-task','checked');
                            }else{
                                biuldSwicth('.taskClosed','taskClosed','#edit-modal-task','');
                            }
                        }else if(task[i]=='DueDate' || task[i]=='StartTime'){
                            if(val=='0000-00-00' || val=='00:00:00'){
                                elem.val('');
                            }
                        }
                    }
                }
                $('#edit-modal-task h4').text('Edit Task');
                $('#edit-modal-task').modal('show');
            });
            @endif
            $('#tools .toggle').click(function(){
                if($(this).hasClass('list')){
                    $(this).addClass('active');
                    $(this).siblings('.toggle').removeClass('active');
                    $('#board-start').addClass('hidden');
                    $('#taskGrid_wrapper,#taskGrid').removeClass('hidden');
                }else{
                    $(this).addClass('active');
                    $(this).siblings('.toggle').removeClass('active');
                    $('#board-start').removeClass('hidden');
                    $('#taskGrid_wrapper,#taskGrid').addClass('hidden');
                }
            });
            @if(User::checkCategoryPermission('TaskComment','View'))
            $(document).on('click','#board-start ul.sortable-list li',function(){
                $('#add-task-comments-form').trigger("reset");
                $('.sendmail').removeClass('hidden');
                var rowHidden = $(this).children('div.row-hidden');
                $('#allComments,#attachments').empty();
                var taskID = rowHidden.find('[name="TaskID"]').val();
                var accountID = rowHidden.find('[name="AccountIDs"]').val();
                var subject = rowHidden.find('[name="Subject"]').val();
                if(!accountID){
                    $('.sendmail').addClass('hidden');
                }
                $('#add-task-comments-form [name="TaskID"]').val(taskID);
                $('#add-task-attachment-form [name="TaskID"]').val(taskID);
                $('#add-task-attachment-form [name="AccountID"]').val(accountID);
                $('#add-task-comments-form [name="AccountID"]').val(accountID);
                $('#add-view-modal-task-comments h4.modal-title').text(subject);
                getComments();
                getTaskAttachment();
                autosizeUpdate();
                $('#add-view-modal-task-comments').modal('show');
            });
            @endif
            @if(User::checkCategoryPermission('TaskComment','Add'))
            $('#add-task-comments-form').submit(function(e){
                e.preventDefault();
                var formData = new FormData($('#add-task-comments-form')[0]);
                var url = baseurl + '/taskcomment/create';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            emailFileList = [];
                            $(".file-input-names").empty();
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-task-comments-form').trigger("reset");
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $("#commentadd").button('reset');
                        $('#add-task-comments-form').trigger("reset");
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
            @endif
            @if(User::checkCategoryPermission('TaskAttachment','Add'))
            $(document).on('change','#add-task-attachment-form input[type="file"]',function(){
                var taskID = $('#add-task-attachment-form [name="TaskID"]').val();
                var formData = new FormData($('#add-task-attachment-form')[0]);
                var url = baseurl + '/task/'+taskID+'/saveattachment';
                $('#attachment_processing').removeClass('hidden');
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#add-task-attachment-form').trigger("reset");
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $('#attachment_processing').addClass('hidden');
                        $('#add-task-attachment-form').trigger("reset");
                        $('#addattachmentop .file-input-name').empty();
                        getComments();
                        getTaskAttachment();
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
            @endif
            @if(User::checkCategoryPermission('TaskAttachment','Delete'))
            $(document).on('click','#attachments i.delete-file',function(){
                var con = confirm('Are you sure you want to delete this attachments?');
                if(!con){
                    return true;
                }
                var taskID = $('#add-task-attachment-form [name="TaskID"]').val();
                var attachmentID = $(this).attr('data-id');
                var url = baseurl + '/task/'+taskID+'/deleteattachment/'+attachmentID;
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
                        getTaskAttachment();
                    },
                    // Form data
                    data: [],
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
            @endif
            $(document).on('click','#addTtachment',function(){
                $('#filecontrole').click();
            });

            $(document).on('click','.viewattachments',function(){
                $(this).siblings('.comment-attachment').toggleClass('hidden');
            });

            $('#search-task-filter [name="DueDateFilter"]').change(function(){
                if($(this).val()==3){
                    $('#search-task-filter .tohidden').removeClass('hidden');
                }else{;
                    $('#search-task-filter .tohidden').addClass('hidden');
                }
            });

            $(document).on('change','#filecontrole',function(e){
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
                        var index_file = emailFileList.indexOf(f.name);
                        if(index_file >-1 ) {
                            ShowToastr("error",f.name+" file already selected.");
                        } else if(bytesToSize(f.size)) {
                            ShowToastr("error",f.name+" file size exceeds then upload limit ("+max_file_size_txt+"). Please select files again.");
                            file_check = 0;
                            return false;
                        }else {
                            //emailFileList.push(f.name);
                            local_array.push(f.name);
                        }
                    } else {
                        ShowToastr("error",ext_current_file+" file type not allowed.");
                    }
                });
                if(local_array.length>0 && file_check==1) {
                    emailFileList = emailFileList.concat(local_array);

                    var formData = new FormData($('#add-task-comments-form')[0]);
                    var url = baseurl + '/task/uploadfile';
                    $.ajax({
                        url: url,  //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if(response.status =='success'){
                                $('#card-features-details').find('.file-input-names').html(response.data.text);
                                $('#card-features-details').find('[name="attachmentsinfo"]').val(JSON.stringify(response.data.attachmentsinfo));

                            }else{
                                toastr.error(response.message, "Error", toastr_opts);
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
                var url  =  baseurl + '/task/deleteattachmentfile';
                var fileName   =  $(this).attr('del_file_name');
                var attachmentsinfo = $('#card-features-details').find('[name="attachmentsinfo"]').val();
                if(!attachmentsinfo){
                    return true;
                }
                attachmentsinfo = jQuery.parseJSON(attachmentsinfo);
                $(this).parent().remove();
                var fileIndex = emailFileList.indexOf(fileName);
                var fileinfo = attachmentsinfo[fileIndex];
                emailFileList.splice(fileIndex, 1);
                attachmentsinfo.splice(fileIndex, 1);
                $('#card-features-details').find('[name="attachmentsinfo"]').val(JSON.stringify(attachmentsinfo));
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data:{file:fileinfo},
                    async :false,
                    success: function(response) {
                        if(response.status =='success'){

                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }
                });
            });

            $('#add-view-modal-task-comments').on('shown.bs.modal', function(event){
                emailFileList = [];
                $(".file-input-names").empty();
                $('#add-task-comments-form').trigger("reset");
                $('#commentadd').siblings('.file-input-name').empty();
                $('#card-features-details').find('[name="attachmentsinfo"]').val('');
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

            function biuldSwicth(container,name,formID,checked){
                var make = '<span class="make-switch switch-small">';
                make += '<input name="'+name+'" value="{{Task::Close}}" '+checked+' type="checkbox">';
                make +='</span>';

                var container = $(formID).find(container);
                container.empty();
                container.html(make);
                container.find('.make-switch').bootstrapSwitch();
            }


            function getRecord(){
                $searchFilter.taskName = $("#search-task-filter [name='taskName']").val();
                $searchFilter.AccountOwner = $("#search-task-filter [name='AccountOwner']").val();
                $searchFilter.Priority = $("#search-task-filter [name='Priority']").prop("checked");
                $searchFilter.DueDateFilter = $("#search-task-filter [name='DueDateFilter']").val();
                $searchFilter.DueDateFrom = $("#search-task-filter [name='DueDateFrom']").val();
                $searchFilter.DueDateTo = $("#search-task-filter [name='DueDateTo']").val();
                $searchFilter.TaskStatus = $("#search-task-filter [name='TaskStatus']").val();
                $searchFilter.AccountIDs = $("#search-task-filter [name='AccountIDs']").val();
                $searchFilter.taskClosed = $("#search-task-filter [name='taskClosed']").prop("checked");
                console.log($searchFilter);
                getTask();
                data_table.fnFilter('',0);
            }

            function getTask(){
                var formData = new FormData($('#search-task-filter')[0]);
                var url = baseurl + '/task/'+BoardID+'/ajax_task_board';
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
                var taskID = $('#add-task-comments-form [name="TaskID"]').val();
                var url = baseurl +'/taskcomments/'+taskID+'/ajax_taskcomments';
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

            function getTaskAttachment(){
                var taskID = $('#add-task-comments-form [name="TaskID"]').val();
                var url = baseurl +'/task/'+taskID+'/ajax_getattachments';
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
                var url = baseurl + '/taskboardcolumn/{{$BoardID}}/ajax_datacolumn';
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
                url = baseurl + '/task/'+BoardID+'/updateColumnOrder';
                var formData = new FormData($('#cardorder')[0]);
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){
                            getRecord();
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
                console.log(BoardColumnID);
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
@include('taskboards.taskmodal')
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="edit-modal-task">
        <div class="modal-dialog" style="width: 70%;">
            <div class="modal-content">
                <form id="edit-task-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Task</h4>
                    </div>
                    <div class="modal-body">

                        <div class="row">

                            <div class="col-md-12 text-left">
                                <label for="field-5" class="control-label col-sm-2">Tag User</label>
                                <div class="col-sm-10" style="padding: 0px 10px;">
                                    <?php unset($account_owners['']); ?>
                                    {{Form::select('TaggedUsers[]',$account_owners,[],array("class"=>"select2","multiple"=>"multiple"))}}
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Task Status *</label>
                                    <div class="col-sm-8">
                                        {{Form::select('TaskStatus',$taskStatus,'',array("class"=>"select2 small"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Assign To*</label>
                                    <div class="col-sm-8">
                                        {{Form::select('UsersIDs',$account_owners,'',array("class"=>"select2"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Task Subject *</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="Subject" class="form-control" id="field-5" placeholder="">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Due Date</label>
                                    <div class="col-sm-5">
                                        <input autocomplete="off" type="text" name="DueDate" class="form-control datepicker "  data-date-format="yyyy-mm-dd" value="" />
                                    </div>
                                    <div class="col-sm-3">
                                        <input type="text" name="StartTime" data-minute-step="5" data-show-meridian="false" data-default-time="23:59:59" value="23:59:59" data-show-seconds="true" data-template="dropdown" class="form-control timepicker">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-4">Company</label>
                                    <div class="col-sm-8">
                                        {{Form::select('AccountIDs',$leadOrAccount,'',array("class"=>"select2"))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 margin-top pull-right">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Priority</label>
                                    <div class="col-sm-3 make">
                                        <span class="make-switch switch-small">
                                            <input name="Priority" value="1" type="checkbox">
                                        </span>
                                    </div>
                                    <label class="col-sm-2 control-label">Close</label>
                                    <div class="col-sm-3 taskClosed">
                                        <p class="make-switch switch-small">
                                            <input name="taskClosed" type="checkbox" value="{{Task::Close}}">
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 margin-top pull-left">
                                <div class="form-group">
                                    <label for="field-5" class="control-label col-sm-2">Description</label>
                                    <div class="col-sm-10">
                                        <textarea name="Description" class="form-control textarea autogrow resizevertical"> </textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="TaskID">
                        <button type="submit" id="task-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

    <div class="modal fade" id="add-view-modal-task-comments" data-backdrop="static">
        <div id="card-features-details" class="modal-dialog">
            <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Task Name</h4>
                    </div>
                    <div class="modal-body">
                        <form id="add-task-comments-form" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-12 text-left">
                                    <h4>Add Comment</h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <textarea class="form-control autogrow resizevertical" name="CommentText" placeholder="Write a comment."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 pull-left end-buttons sendmail" style="text-align: left;">
                                    <label for="field-5" class="control-label">Send Mail To Customer:</label>
                                    <span id="label-switch" class="make-switch switch-small">
                                        <input name="PrivateComment" value="1" type="checkbox">
                                    </span>
                                </div>
                                <div class="col-sm-6 pull-right end-buttons" style="text-align: right;">
                                    <p class="comment-box-options">
                                        <a id="addTtachment" class="btn-sm btn-white btn-xs" title="Add an attachment…" href="javascript:void(0)">
                                            <i class="entypo-attach"></i>
                                        </a>
                                    </p>
                                    <input type="hidden" name="TaskID" >
                                    <input type="hidden" name="AccountID" >
                                    <button data-loading-text="Loading..." id="commentadd" class="add btn btn-primary btn-sm btn-icon icon-left" type="submit" style="visibility: visible;">
                                        <i class="entypo-floppy"></i>
                                        Add Comment
                                    </button>
                                    <br>
                                    <div class="file_attachment">
                                        <div class="file-input-names"></div>
                                        <input id="filecontrole" type="file" name="commentattachment[]" class="hidden" multiple data-label="<i class='entypo-attach'></i>Attachments" />&nbsp;
                                        <input  type="hidden" name="token_attachment" value="{{$token}}" />
                                        <input type="hidden" name="attachmentsinfo" >
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="co-md-12">
                                <div id="comment_processing" class="dataTables_processing hidden">Processing...</div>
                                <div id="attachment_processing" class="dataTables_processing hidden">Processing...</div>
                                <div id="allComments"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="co-md-12">
                                <div id="attachments" class="form-group"></div>
                            </div>
                        </div>
                        <form id="add-task-attachment-form" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8"></div>
                                <div class="col-md-4" id="addattachmentop" style="text-align: right;">
                                    <input type="file" name="taskattachment[]" data-loading-text="Loading..." class="form-control file2 inline btn btn-primary btn-sm btn-icon icon-left" multiple data-label="<i class='entypo-attach'></i>Add Attachments" />
                                    <input type="hidden" name="TaskID" >
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

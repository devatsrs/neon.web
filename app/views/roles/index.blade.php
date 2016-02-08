@extends('layout.main')

@section('content')

    <style>
        .controle{
            width:100%;
        }
        .scroll{
            height: 400px;
            overflow: auto;
        }
        .disabledTab{
            pointer-events: none;
        }
    </style>

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Roles</a>
        </li>
    </ol>

    <h3>Roles</h3>
    <br>
    @if( User::is_admin() or User::is('BillingManager'))
        <p style="text-align: right;">
            <a href="#" id="add-new-role" class="btn btn-primary ">
                <i class="entypo-plus"></i>
                Add New Role
            </a>
            <!--<a href="#" id="add-new-permission" class="btn btn-primary ">
                <i class="entypo-plus"></i>
                Add New Permission
            </a>-->
        </p>
    @endif
    <div class="tab-content">
        <form id="add-edit-role-form" method="post">
            <div class="row">
                <div class="col-md-6 leftsection">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#lefttab1" data-toggle="tab">Users</a></li>
                        <li><a href="#lefttab2" id="leftgroup" data-toggle="tab">Roles</a></li>
                        <li><a href="#lefttab3" id="leftaction" data-toggle="tab">Permissions</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="lefttab1">
                            <div class="form-group">
                                <div class="col-sm-6">
                                    <input type="text" name="txtleftuser" class="form-control" placeholder="User Search" value="">
                                </div>
                                <div class="col-sm-10 scroll">
                                <table class="clear table table-bordered datatable controle user">
                                    <thead>
                                    <tr>
                                        <th width="10%">
                                            <div class="checkbox">
                                                <input type="checkbox" name="checkbox[]" class="selectall">
                                            </div>
                                        </th>
                                        <th width="90%">Users</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(count($users))
                                        @foreach($users as $index=>$user)
                                            <tr search="{{strtolower($user)}}">
                                                <td>
                                                    <div class="checkbox">
                                                        {{Form::checkbox("UserIds[]" , $index ) }}
                                                    </div>
                                                </td>
                                                <td>{{$user}}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="lefttab2">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <input type="text" name="txtleftgroup" class="form-control" placeholder="Role Sreach" value="">
                                    </div>
                                    <div class="col-sm-10 scroll">
                                        <table class="clear table table-bordered datatable controle role">
                                            <thead>
                                            <tr>
                                                <th width="10%">
                                                    <div class="checkbox">
                                                        <input type="checkbox" name="checkbox[]" class="selectall">
                                                    </div>
                                                </th>
                                                <th width="90%">Roles</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(count($roles))
                                                @foreach($roles as $index=>$role)
                                                    <tr search="{{strtolower($role)}}">
                                                        <td>
                                                            <div class="checkbox">
                                                                {{Form::checkbox("RoleIds[]" , $index ) }}
                                                            </div>
                                                        </td>
                                                        <td>{{$role}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="lefttab3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <input type="text" name="vender" class="form-control" placeholder="Permission Search" value="">
                                    </div>
                                    <div class="col-sm-10 scroll">
                                        <table class="clear table table-bordered datatable controle resource">
                                            <thead>
                                            <tr>
                                                <th width="10%">
                                                    <div class="checkbox">
                                                        <input type="checkbox" name="checkbox[]" class="selectall">
                                                    </div>
                                                </th>
                                                <th width="90%">Permissions</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(count($resources))
                                                @foreach($resources as $index=>$resource)
                                                    <tr search="{{strtolower($resource->ResourceCategoryName)}}">
                                                        <td>
                                                            <div class="checkbox">
                                                                {{Form::checkbox("ResourceIds[]" , $resource->ResourceCategoryID ) }}
                                                            </div>
                                                        </td>
                                                        <td>{{$resource->ResourceCategoryName}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 rightsection">
                    <ul class="nav nav-tabs">
                        <li><a href="#righttab1" data-toggle="tab">Users</a></li>
                        <li class="active"><a href="#righttab2" data-toggle="tab">Roles</a></li>
                        <li><a href="#righttab3" data-toggle="tab">Permissions</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane" id="righttab1">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <input type="text" name="vender" class="form-control" placeholder="User search" value="">
                                    </div>
                                    <div class="col-sm-10 scroll">
                                        <table class="clear table table-bordered datatable controle user">
                                            <thead>
                                            <tr>
                                                <th width="10%">
                                                    <div class="checkbox">
                                                        <input type="checkbox" name="checkbox[]" class="selectall">
                                                    </div>
                                                </th>
                                                <th width="90%">Users</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(count($users))
                                                @foreach($users as $index=>$user)
                                                    <tr search="{{strtolower($user)}}">
                                                        <td>
                                                            <div class="checkbox">
                                                                {{Form::checkbox("UserIds[]" , $index ) }}
                                                            </div>
                                                        </td>
                                                        <td>{{$user}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane active" id="righttab2">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <input type="text" name="vender" class="form-control"placeholder="Role Search" value="">
                                    </div>
                                    <div class="col-sm-10 scroll">
                                        <table class="clear table table-bordered datatable controle role">
                                            <thead>
                                            <tr>
                                                <th width="10%">
                                                    <div class="checkbox">
                                                        <input type="checkbox" name="checkbox[]" class="selectall">
                                                    </div>
                                                </th>
                                                <th width="90%">Roles</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(count($roles))
                                                @foreach($roles as $index=>$role)
                                                    <tr search="{{strtolower($role)}}">
                                                        <td>
                                                            <div class="checkbox">
                                                                {{Form::checkbox("RoleIds[]" , $index ) }}
                                                            </div>
                                                        </td>
                                                        <td>{{$role}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="tab-pane" id="righttab3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <input type="text" name="vender" class="form-control" placeholder="Permission search" value="">
                                    </div>
                                    <div class="col-sm-10 scroll">
                                        <table class="clear table table-bordered datatable controle resource">
                                            <thead>
                                            <tr>
                                                <th width="10%">
                                                    <div class="checkbox">
                                                        <input type="checkbox" name="checkbox[]" class="selectall">
                                                    </div>
                                                </th>
                                                <th width="90%">Permissions</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(count($resources))
                                                @foreach($resources as $index=>$resource)
                                                    <tr search="{{strtolower($resource->ResourceCategoryName)}}">
                                                        <td>
                                                            <div class="checkbox">
                                                                {{Form::checkbox("ResourceIds[]" , $resource->ResourceCategoryID ) }}
                                                            </div>
                                                        </td>
                                                        <td>{{$resource->ResourceCategoryName}}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <p style="text-align: right;">
                <button type="submit" id="role-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                    <i class="entypo-floppy"></i>
                    Save
                </button>
            </p>
        </form>

            <script type="text/javascript">
                var userpermission;
                jQuery(document).ready(function ($) {
                    disable();
                    $('#add-new-role,#add-new-permission').on('click',function(e){
                        e.preventDefault();
                        var self = $(this);
                        if(self.attr('id')=='add-new-role'){
                            $('#add-modal-role-permission .role').show();
                            $('#add-modal-role-permission .permission').hide();
                            $('#add-modal-role-permission h4').text('Add New Role');
                            $('#add-role-permission-form').find('[name="type"]').val('role');
                        }else{
                            $('#add-modal-role-permission .role').hide();
                            $('#add-modal-role-permission .permission').show();
                            $('#add-modal-role-permission h4').text('Add New Permission');
                            $('#add-role-permission-form').find('[name="type"]').val('permission');
                        }
                        $('#add-modal-role-permission').modal('show');
                    });

                    $('input[type="text"]').on('keyup',function(){
                        var s = $(this).val();
                       var table =  $(this).parents('.tab-pane').find('table');
                        $(table).find('tbody tr:hidden').show();
                        $(table).find('tbody tr').each(function() {
                            if(this.getAttribute("search").indexOf(s.toLowerCase()) != 0){
                                $(this).hide();
                            }
                        });
                    });//key up.
                    $(document).on('click','#add-edit-role-form .table tbody tr input[type="checkbox"]',function(e){
                        // How to check if checkbox was clicked before doing the alert below?
                        e.stopPropagation();
                        var self = $(this);
                        self = self.parents('tr');
                        checkseletected(self,e);
                    });
                    $(document).on('click','#add-edit-role-form .table tbody tr',function(e){
                        var self = $(this);
                        checkseletected(self,e);
                    });

                    $('.nav-tabs li a').click(function(e){
                        e.preventDefault();                        
                    });

                    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                        //alert('hi');return false;
                        var self = $(this);
                        var parent = $(self).parents('.col-md-6');
                        if(parent.hasClass('leftsection')){
                            var type = $(parent).find('div.active table tbody tr input[type="checkbox"]:first').attr('name');
                            type = type.replace('[]','');
                            resetcheckboxs(type,'.leftsection');
                            resetcheckboxs('ResourceIds','.rightsection');
                            resetcheckboxs('UserIds','.rightsection');
                            resetcheckboxs('RoleIds','.rightsection');
                            var text = $(self).text();
                            $('.rightsection ul li').each(function(){
                                var el = $(this);
                                el.removeClass('active');
                            });
                            $('.rightsection .tab-content .tab-pane').each(function(){
                                var el = $(this);
                                el.removeClass('active');
                            });
                            if(text=='Users'){
                                $('.rightsection ul li a[href="#righttab2"]').parent().addClass('active');
                                $('#righttab2').addClass('active');
                            }else if(text=='Roles'){
                                $('.rightsection ul li a[href="#righttab1"]').parent().addClass('active');
                                $('#righttab1').addClass('active');
                            }else if(text=='Permissions'){
                                $('.rightsection ul li a[href="#righttab1"]').parent().addClass('active');
                                $('#righttab1').addClass('active');
                            }
                        }else if(parent.hasClass('rightsection')){
                            var text = $(self).text();
                            if(text=='Permissions'){
                                var tab = $('.leftsection ul li.active a').text();
                                if(tab=='Users'){
                                    resetcheckboxs('UserIds','.leftsection');
                                    resetcheckboxs('ResourceIds','.rightsection');
                                }
                            }else{
                                if(text=='Users'){
                                    resetcheckboxs('UserIds','.rightsection');
                                }else if(text=='Roles'){
                                    resetcheckboxs('RoleIds','.rightsection');
                                }
                            }
                        }
                        disable();
                        var table = $('.leftsection div.active table');
                        if($(table).find('input[type="checkbox"]:checked').length == 0){
                            return true;
                        }
                        getdata();
                    });

                    $('#add-role-permission-form').submit(function(e){
                        e.preventDefault();
                        var type = $('#add-role-permission-form').find('[name="type"]').val();
                        var formData = new FormData($('#add-role-permission-form')[0]);
                        var url = baseurl + '/roles/storerole';
                        if(type=='permission'){
                            url = baseurl + '/roles/storepermission';
                        }
                        $.ajax({
                            url: url,  //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                if(response.status =='success'){
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $(".save").button('reset');
                                    $('#add-modal-role-permission').modal('hide');
                                    location.reload();
                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                    $(".save").button('reset');
                                }
                            },
                            // Form data
                            data: formData,
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    });

                    $('#add-edit-role-form').submit(function(e){
                        e.preventDefault();
                        var lefttable = $('.leftsection div.active table');
                        var righttable = $('.rightsection div.active table');
                        var leftdata = extractdata(lefttable);
                        var rightdata = extractdata(righttable);
                        var lefttext = $('.leftsection ul li.active a').text();
                        var righttext = $('.rightsection ul li.active a').text();

                        var type;
                        if(righttext=='Permissions'){
                            type = 'Permissions';
                        }else if(righttext=='Users'){
                            type = 'Users';
                        }else if(righttext=='Roles'){
                            type = 'Roles';
                        }
                        rightdata = permission_decian(rightdata,type);
                        leftdata = leftdata.concat(rightdata);
                        var url = baseurl + '/roles/update';
                        $.ajax({
                            url: url,  //Server script to process data
                            type: 'POST',
                            contentType: "application/json",
                            dataType: 'json',
                            success: function (response) {
                                if(response.status =='success'){
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $(".save").button('reset');
                                    $(".savetest").button('reset');
                                    $('#modal-BulkMail').modal('hide');
                                    reloadJobsDrodown(0);
                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                    $(".save").button('reset');
                                    $(".savetest").button('reset');
                                }
                                $('.file-input-name').text('');
                                $('#attachment').val('');
                            },
                            // Form data
                            data: JSON.stringify(leftdata)
                        });
                    });

                    function getdata() {
                        var lefttable = $('.leftsection div.active table');
                        var righttable = $('.rightsection div.active table');
                        var leftType = lefttable.find('tbody tr input[type="checkbox"]:first').attr('name');
                        var righttype = righttable.find('tbody tr input[type="checkbox"]:first').attr('name');
                        var url = '';
                        leftType = leftType.replace('[]','');
                        righttype = righttype.replace('[]','');
                        if(leftType=='UserIds'){
                            if(righttype=='RoleIds'){
                                url = baseurl + '/roles/ajax_role_list/user';
                            }else if(righttype=='ResourceIds'){
                                url = baseurl + '/roles/ajax_resource_list/user';
                            }
                        }else if(leftType=='RoleIds'){
                            if(righttype=='UserIds'){
                                url = baseurl + '/roles/ajax_user_list/role';
                            }else if(righttype=='ResourceIds'){
                                url = baseurl + '/roles/ajax_resource_list/role';
                            }
                        }else if(leftType=='ResourceIds'){
                            if(righttype=='UserIds'){
                                url = baseurl + '/roles/ajax_user_list/resource';
                            }else if(righttype=='RoleIds'){
                                url = baseurl + '/roles/ajax_role_list/resource';
                            }
                        }
                        var leftdata = extractdata(lefttable);
                        if(leftdata.length==0){
                            return false;
                        }
                        $.ajax({
                            url: url,  //Server script to process data
                            type: 'POST',
                            contentType: "application/json",
                            dataType: 'json',
                            success: function (response){
                                if(response.status =='success'){
                                    if(response.result) {
                                        userpermission = response.result;
                                        var table = righttable;
                                        $(table).find('tbody > tr').remove();
                                        $(table).find('tbody').append('<tr search=""></tr>');
                                        $.each(response.result, function (key, val) {
                                            var newRow = '';
                                            var name = '';
                                            var id = '';
                                            var txtname = '';
                                            var checked = val.Checked != null ? 'checked = "checked"' : '';
                                            var selected = val.Checked != null ? 'class = "selected"' : '';
                                            if('AddRemove' in val){
                                                if(val.AddRemove == 'remove'){
                                                    checked = '';
                                                    selected = '';
                                                }else if(val.AddRemove == 'add'){
                                                    checked = 'checked = "checked"';
                                                    selected = 'class = "selected"';
                                                }
                                            }
                                            if (righttype == 'RoleIds') {
                                                name = val.RoleName;
                                                id = val.RoleID;
                                            } else if (righttype == 'ResourceIds') {
                                                name = val.ResourceCategoryName;
                                                id = val.ResourceCategoryID;
                                            } else if (righttype == 'UserIds') {
                                                name = val.UserName;
                                                id = val.UserID;
                                            }
                                            newRow = '<tr '+selected+' search="' + name.toLowerCase() + '">';
                                            newRow += '  <td>';
                                            newRow += '    <div class="checkbox ">';
                                            newRow += '      <input type="checkbox" value="' + id + '" name="' + righttype + '[]" ' + checked + '>';
                                            newRow += '    </div>';
                                            newRow += '  </td>';
                                            newRow += '  <td>' + name + '</td>';
                                            newRow += '  </tr>';
                                            $(table).find('tbody>tr:last').after(newRow);
                                        });
                                    }
                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $("#Role-update").button('reset');
                            },
                            data: JSON.stringify(leftdata)
                        });
                    }

                    $('.selectall').on('click',function(){
                        var self = $(this);
                        var is_checked = $(self).is(':checked');
                        self.parents('table').find('tbody tr').each(function(i, el) {
                            if (is_checked) {
                                if($(this).is(':visible')) {
                                    $(this).find('input[type="checkbox"]').prop("checked", true);
                                    $(this).addClass('selected');
                                }
                            } else {
                                $(this).find('input[type="checkbox"]').prop("checked", false);
                                $(this).removeClass('selected');
                            }
                        });
                    });

                    function resetcheckboxs(type,section){
                        if(type=='UserIds'){
                            table = $(section).find('table').eq(0);//reset user
                        }else if(type=='RoleIds'){
                            table = $(section).find('table').eq(1);//reset role
                        }else if(type=='ResourceIds'){
                            table = $(section).find('table').eq(2);//reset resource
                        }

                        $(table).find('tbody>tr').each(function(i, el) {
                            $(this).find('input[type="checkbox"]').prop("checked", false);
                            $(this).removeClass('selected');
                        });
                        $(table).find('thead>tr').each(function(i, el) {
                            $(this).find('input[type="checkbox"]').prop("checked", false);
                        });
                    }

                    function extractdata(table){
                        var array = [];
                        $(table).find('tbody tr input[type="checkbox"]').each(function(i, el) {
                            if($(this).is(':checked')){
                                var inputname = $(this).attr('name');
                                var value = $(this).val();
                                var obj;
                                if(inputname=='UserIds[]'){
                                    obj = {user:value};
                                }else if(inputname=='RoleIds[]'){
                                    obj = {role:value};
                                }else if(inputname=='ResourceIds[]'){
                                    obj = {resource:value};
                                }
                                array.push(obj);
                            }
                        });
                        return array;
                    }

                    function disable(){
                        var tab = $('.leftsection ul li.active a').text();
                        $('.rightsection ul li a').each(function(){
                            var el = $(this);
                            if(el.text() == tab){
                                el.parent().addClass('hidden');
                            }else{
                                el.parent().removeClass('hidden');
                            }
                        });
                    }

                    function permission_decian(selectdata,type){
                        var postdata = [];
                        var needale;
                        $(userpermission).each(function(i,el){
                            if(type=='Users'){
                                needale = el.UserID;
                            }else if(type == 'Permissions'){
                                needale = el.ResourceCategoryID;
                            }else if(type == 'Roles'){
                                needale = el.RoleID;
                            }
                            if(!is_inarray(needale,selectdata,'remove',type)){
                                var obj;
                                if (el.hasOwnProperty('AddRemove')) {
                                    if(el.Checked != null || (el.AddRemove == 'remove')){
                                        if (type == 'Permissions') {
                                            obj = {resource: needale, AddRemove: 'remove'};
                                        }else if (type == 'Users') {
                                            obj = {user: needale, AddRemove: 'remove'};
                                        }
                                        postdata.push(obj);
                                    }
                                }else if(el.Checked != null){
                                    if (type == 'Roles') {
                                        obj = {role: needale, AddRemove: 'remove'};
                                    } else if (type == 'Users') {
                                        obj = {user: needale, AddRemove: 'remove'};
                                    } else if(type == 'Permissions'){
                                        obj = {resource:needale,AddRemove:'remove'};
                                    }
                                    postdata.push(obj);
                                }
                            }
                        });

                        $(selectdata).each(function(i,el){
                            if(type=='Users'){
                                needale = el.user;
                            }else if(type=='Permissions'){
                                needale = el.resource;
                            }else if(type=='Roles'){
                                needale = el.role;
                            }
                            if(is_inarray(needale,userpermission,'add',type)){
                                var obj;
                                if(type=='Users'){
                                    obj = {user:needale,AddRemove:'add'};
                                }else if(type=='Permissions'){
                                    obj = {resource:needale,AddRemove:'add'};
                                }else if(type=='Roles'){
                                    obj = {role:needale,AddRemove:'add'};
                                }
                                postdata.push(obj);
                            }
                        });
                        return postdata;
                    }

                    function is_inarray(needal,array,action,type){
                        var check = false;
                        $(array).each(function(i,el){
                            var current;
                            if(action == 'remove') {
                                if(type =='Users'){
                                    current = el.user;
                                }else if(type =='Permissions'){
                                    current = el.resource;
                                }else if(type =='Roles'){
                                    current = el.role;
                                }
                                if (current == needal) {
                                    check = true;
                                }
                            }else if(action == 'add') {
                                if(type =='Users'){
                                    current = el.UserID;
                                }else if(type =='Permissions'){
                                    current = el.ResourceCategoryID;
                                }else if(type =='Roles'){
                                    current = el.RoleID;
                                }
                                if (el.hasOwnProperty('AddRemove')) {
                                    if ((current == needal && el.Checked == null) || (current == needal && el.AddRemove == 'remove')) {
                                        check = true;
                                    }
                                }else{
                                    if ((current == needal && el.Checked == null)) {
                                        check = true;
                                    }                                }

                            }
                        });
                        return check;
                    }

                    function checkseletected(self,event){
                        var parent = self.parents('.col-md-6');
                        if(self.hasClass('selected')){
                            $(self).find('input[type="checkbox"]').prop("checked", false);
                            $(self).removeClass('selected');
                        }else{
                            var type = self.find('[type="checkbox"]').attr('name');
                            type = type.replace('[]','');
                            if(type == 'UserIds') {
                                var lefttable = $('.leftsection div.active table');
                                var righttable = $('.rightsection div.active table');
                                if (lefttable.hasClass('user') && righttable.hasClass('resource')) {
                                    if ($(lefttable).find('tr.selected').length == 1) {
                                        event.preventDefault();
                                        toastr.error('You can select one user at a time for permissions', "Success", toastr_opts);
                                        return true;
                                    }
                                }
                            }
                            $(self).find('input[type="checkbox"]').prop("checked", true);
                            $(self).addClass('selected');
                        }
                        if(parent.hasClass('leftsection')) {
                            var table = $('.leftsection div.active table');
                            if ($(table).find('input[type="checkbox"]:checked').length == 0) {                                
                                resetcheckboxs('UserIds', '.rightsection');
                                resetcheckboxs('RoleIds', '.rightsection');
                                resetcheckboxs('ResourceIds', '.rightsection');
                                return true;
                            }
                            getdata();
                        }
                    }

                });
            </script>

            @include('includes.errors')
            @include('includes.success')
    </div>
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="add-modal-role-permission">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-role-permission-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Role</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row role">
                            <div class="col-md-12">
                                <label class="col-sm-3 control-label">Role Name</label>
                                <div class="col-sm-5">
                                    <input class="form-control" name="RoleName" type="text" >
                                </div>
                            </div>
                        </div>
                        <div class="row permission">
                            <div class="col-md-12">
                                <label class="col-sm-3 control-label">Permission Name</label>
                                <div class="col-sm-5">
                                    <input class="form-control" name="ResourceName" type="text" >
                                </div>
                            </div>
                        </div>
                        <div class="row permission">
                            <div class="col-md-12">
                                <br />
                                <label class="col-sm-3 control-label">Permission Value</label>
                                <div class="col-sm-5">
                                    <input class="form-control" name="ResourceValue" type="text" >
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="type" value="role" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="role-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
@stop

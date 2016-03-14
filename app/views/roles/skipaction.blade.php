@extends('layout.main')

@section('content')
    <style>

        #table-1_processing{
            top:500px !important;
        }

    </style>
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Permission Skip Action</strong>
        </li>
    </ol>
    <h3>Permission Skip Action</h3>

    @include('includes.errors')
    @include('includes.success')

    <div class="tab-content">
        <div class="row">
            <div class="tab-content">
                <div class="tab-pane active" id="tab1" >
                    <div class="col-md-8">
                        <!--<h4>Actions</h4>-->
                        <form id="skip-action-form" method="post">

                            <div class="form-group">
                                <div class="col-sm-8">
                                    <input type="text" name="txtleftbulkvendor" class="form-control" placeholder="Action Search" value="">
                                </div>
                                <div class="col-sm-10 scroll" >
                                    <table class="clear table table-bordered datatable controle skipaction" id="table-1">
                                        <thead>
                                        <tr>
                                            <th width="10%">
                                                <div class="checkbox">
                                                    <input type="checkbox" name="checkbox[]" class="selectall">
                                                </div>
                                            </th>
                                            <th width="90%">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($actions))
                                            @foreach($actions as $index)
                                                <tr class="draggable @if($index['Checked']==1)selected @endif" search="{{strtolower($index['ResourceValue'])}}" >
                                                    <td>
                                                        <div class="checkbox">
                                                            {{Form::checkbox("ActionID[]" , $index['ResourceID'],$index['Checked'] ) }}
                                                        </div>
                                                    </td>
                                                    <td>{{$index['ResourceValue']}}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-10">
                                    <p style="text-align: right;">
                                        <button type="submit" id="vendor-deactive"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                            <i class="entypo-floppy"></i>
                                            Save
                                        </button>
                                        <input value="deactivate" name="action" type="hidden">
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var checked='';
        $(function() {
            //load_vendorGrid();

            $('input[type="text"]').on('keyup',function(){
                var s = $(this).val();
                var table =  $(this).parents('form').find('#table-1');
                $(table).find('tbody tr:hidden').show();
                $(table).find('tbody tr').each(function() {
                    if(this.getAttribute("search").indexOf(s.toLowerCase()) != 0){
                        $(this).hide();
                    }
                });
            });//key up.
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
            $(document).on('click','#skip-action-form .table tbody tr',function(){
                var self = $(this);
                if(self.hasClass('selected')){
                    $(this).find('input[type="checkbox"]').prop("checked", false);
                    $(this).removeClass('selected');
                }else{
                    $(this).find('input[type="checkbox"]').prop("checked", true);
                    $(this).addClass('selected');
                }
            });
            $('#skip-action-form').on('submit',function(e){
                e.preventDefault();
                var ajax_full_url = '{{Url::to('/roles/storpermissionaction')}}';
                submit_ajax(ajax_full_url,$('#skip-action-form').serialize());
                return false;
            });



            $(document).ajaxSuccess(function(event,response,ajaxOptions,responsedata) {
                if(responsedata.status == 'success'){
                    var data = responsedata.actiondata;
                    var table = $('#table-1');
                    $(table).find('tbody > tr').remove();
                    $(table).find('tbody').append('<tr search=""></tr>');
                    $.each(data, function (key, val) {
                        var Check='';
                        var Select='';
                        if(val.Checked==1){
                            Check='Checked';
                            Select='selected'
                        }
                        newRow = '<tr class="draggable '+Select+'" search="">';
                        newRow += '  <td>';
                        newRow += '    <div class="checkbox ">';
                        newRow += '      <input type="checkbox" value="' + val.ResourceID + '" name="ActionID[]" '+Check+'>';
                        newRow += '    </div>';
                        newRow += '  </td>';
                        newRow += '  <td>' + val.ResourceValue + '</td>';
                        newRow += '  </tr>';
                        $(table).find('tbody>tr:last').after(newRow);
                    });
                }
            });

            //Select Row on click
            /*$('#table-1 tbody').on('click', 'tr', function() {
                $(this).toggleClass('selected');
                if ($(this).hasClass("selected")) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            });*/

            // Select all
            $("#selectall").click(function(ev) {
                var is_checked = $(this).is(':checked');
                $('#table-1 tbody tr').each(function(i, el) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });

        });

    </script>
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
        .dataTables_filter label{
            display:none !important;
        }
        .dataTables_wrapper .export-data{
            display:none !important;
        }
        .border_left .dataTables_filter {
            border-left: 1px solid #eeeeee !important;
            border-top-left-radius: 3px;
        }
        #selectcheckbox{
            padding: 15px 10px;
        }
    </style>
@stop
@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('/rategenerators')}}">Rate Generator</a>
    </li>

    <li class="active">
        <strong>Update Rate Generator</strong>
    </li>
</ol>
<h3> Update Rate Generator</h3>
<div class="float-right">
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('rategenerators/'.$id.'/edit')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</div>



<div class="row">
    <div class="col-md-12">
         <ul class="nav nav-tabs bordered" >
                <li></li>
                <li ><a href="{{URL::to('rategenerators/rules/'.$id.'/edit/'.$RateRuleID)}}">Code</a></li>
                <li class="active"><a href="{{URL::to('rategenerators/rules/'.$id.'/edit_source/'.$RateRuleID)}}">Sources</a></li>
                <li ><a href="{{URL::to('rategenerators/rules/'.$id.'/edit_margin/'.$RateRuleID)}}">Margin</a></li>
            </ul>
         <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Rate Generator Rule Source Information
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

           
            <div class="panel-body">
                <div class="form-group">
                    <div class="" style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
                        <form  id="rategenerator-from"  action="{{URL::to('rategenerators/rules/'.$id.'/update_source/'.$RateRuleID)}}" method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-md-3" >Search Sources</label>
                                    <div class="col-md-3">
                                        <input type="text" value="" placeholder="" id="vendorSearch" class="form-control" name="vender">
                                    </div>

                                    <label class="control-label col-md-3" >Select Sources</label>
                                    <div class="col-md-3">
                                    {{Form::select('Sources',array( "all"=>"All","selected"=>"Selected"),$rategenerator->Sources , array("class"=>"select2 small","id"=>'Sourcess'))}}
                                    </div>
                                </div>
                            </div>
                            <div>
                            <table class="clear table table-bordered datatable" id="table-4">
                                <thead>
                                    <tr>
                                        <th><div class="checkbox ">
                                    <input type="checkbox" id="selectall" name="checkbox[]" class="">
                                </div>
                                </th>
                                <th>Vendor</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if(count($vendors))
                                    @foreach($vendors as $vendor)
                                    <tr search="{{strtolower($vendor->AccountName)}}" class="odd gradeX {{(in_array($vendor->AccountID, $rategenerator_sources))?'selected':''}}">
                                        <td>
                                            <div class="checkbox ">
                                                {{Form::checkbox("AccountIds[]" , $vendor->AccountID , (in_array($vendor->AccountID, $rategenerator_sources))?True:FALSE  ) }}
                                            </div>
                                        </td>
                                        <td>{{$vendor->AccountName}}</td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function($) {
        $('#table-4 tbody').on('click', 'tr', function() {
            $(this).toggleClass('selected');
            if ($(this).hasClass("selected")) {
                $(this).find('.checkbox input').prop("checked", true);
            } else {
                $(this).find('.checkbox input').prop("checked", false);
            }
        });

        $("#selectall").click(function(ev) {

            var is_checked = $(this).is(':checked');

            $('#table-4 tbody tr').each(function(i, el) {
                if (is_checked) {
                    $(this).find('.checkbox input').prop("checked", true);
                    $(this).addClass('selected');
                } else {
                    $(this).find('.checkbox input').prop("checked", false);
                    $(this).removeClass('selected');

                }

            });

        });

        $("#Sourcess").change(function(){
            var selected = $(this).val();
            if(selected=='selected'){
                $('#table-4 tbody tr:not(.selected)').hide();
            }else{
                $('#table-4 tbody tr').show();
            }
        });

        $("#vendorSearch").keyup(function(){
            var s = $(this).val();
            var selected = $('#Sourcess').val();
            if(selected=='selected'){
                $("#table-4 tr.selected:hidden").show();
            }else{
                $("#table-4 tr:hidden").show();
            }

            $('#table-4').find('tbody tr').each(function() {
                if(this.getAttribute("search").indexOf(s.toLowerCase()) != 0){
                    $(this).hide();
                }
            });
        });//key up.

        $(".save.btn").click(function(ev) {
            $("#rategenerator-from").submit();
        });
    });
</script>
@include('includes.ajax_submit_script', array('formID'=>'rategenerator-from' , 'url' => ('rategenerators/rules/'.$id.'/update_source/'.$RateRuleID)))
@stop         

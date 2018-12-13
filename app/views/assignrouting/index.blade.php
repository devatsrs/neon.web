@extends('layout.main')

@section('filter')

    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="ratetable_filter">
                {{--<div class="form-group">
                    <label for="Search" class="control-label">Search</label>
                    <input class="form-control" name="Search" id="Search"  type="text" >
                </div>--}}
                <div class="form-group">
                    <label class="control-label" for="field-1">Apply To</label>
                    {{ Form::select('level', ["S"=>"Service","T"=>"Trunk","A"=>"Account",], 'T', array("class"=>"select2 level","data-type"=>"level")) }}
                </div>

                <div class="form-group hidden S">
                    <label class="control-label" for="field-1">Service</label>
                    {{ Form::select('services', $allservice, '', array("class"=>"select2","data-type"=>"service")) }}
                </div>
                <div class="form-group T">
                    <label class="control-label" for="field-1">Trunk</label>
                    {{ Form::select('TrunkID', $trunks, '', array("class"=>"select2","data-type"=>"trunk")) }}
                </div>
                
                <div class="form-group">
                    <label for="field-1" class="control-label">Account</label>
                    {{Form::select('SourceCustomers[]', $all_customers, array() ,array("class"=>"form-control select2",'multiple','id'=>"Customerlist"))}}
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
    

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>

            <a href="{{URL::to('routingprofiles')}}">Routing Profiles</a>
        </li>
        <li class="active">
            <strong>Assign Routing Profile</strong>
        </li>
    </ol>
    <h3>Assign Routing Profile</h3>
<p style="text-align: right;">
            <a href="#" id="add-new-rate-table" class="btn btn-primary" title="Assign Routing Profile">
                Assign Profile
            </a>

    </p>
    <br>
    
    <div class="cler row">
        <div class="col-md-12">
            <form role="form" id="form1" method="post" class="form-horizontal form-groups-bordered validate" novalidate>

                <div class="form-group">
                    <div class="col-md-12">
                        {{-- Service Level --}}
                        <table class="table table-bordered datatable" id="table-service">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="table-service_selectall" id="table-service_selectall" name="service_selectall[]" /></th>
                                <th>Account Name</th>
                                <th>Routing Profile</th>
                                <th id="servicenametd">Service Name</th>
                                <th >Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                       

                    </div>
                </div>

            </form>
        </div>
    </div>
    
    
    
    <div class="modal fade" id="modal-add-new-rate-table">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk Apply Routing Profile</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="selected_customer">
                        <input type="hidden" name="selected_level">
                        <div class="allpage"><input type="hidden" name="chk_allpageschecked" value="N" ></div>
                        <div class="row T">

                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label for="field-5" class="control-label">Routing Profile</label>
                                    {{Form::select('RoutingProfile', $routingprofile, '',array("class"=>"form-control select2","id"=>"RoutingProfile"))}}
                                </div>
                            </div>

                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="codedeck-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Apply
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
    <script>
            $("#add-new-rate-table").click(function(ev) {

                ev.preventDefault();

                $("#modal-add-new-rate-table [name='InboundRateTable']").select2('val', '');
                $("#modal-add-new-rate-table [name='OutboundRateTable']").select2('val', '');
                $("#modal-add-new-rate-table [name='ServiceID']").select2('val', '');

                /*$('#ServiceID').select2('enable');
                $("#modal-add-new-rate-table [name='AccountServiceId']").val('');*/
                $('#modal-add-new-rate-table').modal('show', {backdrop: 'static'});
                /* Get selected Customer */
                var favorite = [];
                $.each($("input[name='customer[]']:checked"), function(){
                    favorite.push($(this).val());
                });
                $.unique(favorite);
                $("input[name='selected_customer']").val(favorite.join(","));
                var level = $("#ratetable_filter select[name='level']").val();
                $("input[name='selected_level']").val(level);
                /* Get selected Customer */


            });
            </script>
@stop
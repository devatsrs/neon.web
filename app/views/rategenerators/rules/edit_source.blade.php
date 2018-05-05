<div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">

                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>


                <div class="panel-body">
                    <form  id="rategenerator-source-from"  action="{{URL::to('rategenerators/rules/'.$id.'/update_source/'.$RateRuleID)}}" method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                        <div class="form-group">
                            <div class="" style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
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
                            </div>
                        </div>
                        {{--<p style="text-align: right;"><br></p>
                        <p style="text-align: right;">
                            <button type="submit"  class="save source btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                <i class="entypo-floppy"></i>
                                Save
                            </button>
                        </p>--}}
                    </form>



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
                var s = $("#vendorSearch").val();

                $('#table-4 tbody tr').each(function(i, el) {
                    if (is_checked) {
                        if(this.getAttribute("search").indexOf(s.toLowerCase()) != 0){
                            $(this).find('.checkbox input').prop("checked", false);
                            $(this).removeClass('selected');
                        } else {
                            $(this).find('.checkbox input').prop("checked", true);
                            $(this).addClass('selected');
                        }
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



            /*$("#rategenerator-source-from").submit(function(e){
                e.preventDefault();
                var _url = $(this).attr("action");
                var formData = new FormData($('#rategenerator-source-from')[0]);
                submit_ajax_withfile(_url,formData);

                return false;
            });*/

        });
    </script>


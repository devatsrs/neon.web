<div class="row page_section incident">
    <div class="col-md-12" >
        <form id="post_form_{{$NoticeBoardPost->NoticeBoardPostID}}" method=""  action="" class="form-horizontal post_form form-groups-bordered validate" novalidate>
        <div class="panel panel-default make_round">
            <div class="panel-heading make_round {{$NoticeBoardPost->Type}}">
                <div class="panel-title white">
                    {{$NoticeBoardPost->Title}}
                </div>

                @if(Session::get('customer') == 0 && User::checkCategoryPermission('NoticeBoardPost','Delete'))
                <div class="panel-options ">
                    <a href="#" class="white delete_post" data-id="{{$NoticeBoardPost->NoticeBoardPostID}}"><i class="entypo-trash"></i></a>
                    <a data-rel="collapse" href="#" class="white"><i class="entypo-down-open"></i></a>
                    <strong class="incident_time white">Updated  {{\Carbon\Carbon::createFromTimeStamp(strtotime($NoticeBoardPost->updated_at))->diffForHumans() }}</strong>
                </div>
                @else
                    <div class="panel-options ">
                        <strong class="incident_time white">Updated  {{\Carbon\Carbon::createFromTimeStamp(strtotime($NoticeBoardPost->updated_at))->diffForHumans() }}</strong>
                    </div>
                @endif
            </div>
            <div class="panel-body section_border_1 no_top_border make_round make_round_bottom_only">
                @if(Session::get('customer') == 0)
                <div class="form-group">

                    <label for="field-1" class="col-md-2 control-label">Title*</label>
                    <div class="col-md-4">
                        <input type="text" name="Title" class="form-control" id="field-1" placeholder="" value="{{$NoticeBoardPost->Title}}" />
                    </div>

                    <label for="field-1" class="col-md-2 control-label">Type*</label>
                    <div class="col-md-4">
                        {{Form::select('Type',array('post-success'=>'Success','post-error'=>'Error','post-info'=>'Information','post-warning'=>'Warning'),$NoticeBoardPost->Type,array("class"=>"select2 post_type"))}}
                    </div>

                    <div class="col-xs-12 col-md-12">
                        <label for="subject">Detail *</label>
                        <textarea class="form-control" name="Detail" id="txtNote" rows="5" placeholder="Add Note...">{{$NoticeBoardPost->Detail}}</textarea>
                    </div>

                </div>
                @else
                    <div class="col-xs-12 col-md-12">
                        <p>{{$NoticeBoardPost->Detail}}</p>
                    </div>
                @endif
                <input type="hidden" name="NoticeBoardPostID" value="{{$NoticeBoardPost->NoticeBoardPostID}}">
                @if(Session::get('customer') == 0 && User::checkCategoryPermission('NoticeBoardPost','Edit'))
                <div class="row">
                    <div class="col-md-12">

                            <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left save_post pull-right" data-loading-text="loading">
                                <i class="entypo-floppy"></i>
                                Save
                            </button>

                    </div>
                </div>
                @endif

            </div>
        </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        show_summerinvoicetemplate($("[name=Detail]"));
    });
</script>
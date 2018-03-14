<div class="row page_section incident">
    <div class="col-md-12" >
        <form id="post_form_{{$NoticeBoardPost->NoticeBoardPostID}}" method=""  action="" class="form-horizontal post_form form-groups-bordered validate" novalidate>
        <div class="panel panel-default make_round"  data-collapsed="1">
            <div class="panel-heading  make_round panel-collapse {{$NoticeBoardPost->Type}}" data-rel="collapse" >
                <div class="panel-title ">
                    {{$NoticeBoardPost->Title}}
                </div>

                @if(Session::get('customer') == 0)
                    <div class="panel-options ">
                        <strong class="incident_time">{{cus_lang("CUST_PANEL_PAGE_NOTICEBOARD_MSG_UPDATED")}}  {{\Carbon\Carbon::createFromTimeStamp(strtotime($NoticeBoardPost->updated_at))->diffForHumans() }}</strong>
                        @if(User::checkCategoryPermission('NoticeBoardPost','Edit'))
                            <a href="#" class="save_post" data-original-title="Save" title="" data-placement="top" data-toggle="tooltip" data-id="{{$NoticeBoardPost->NoticeBoardPostID}}"><i class="entypo-floppy"></i></a>
                        @endif
                        @if(User::checkCategoryPermission('NoticeBoardPost','Delete'))
                            <a href="#" class="delete_post" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip" data-id="{{$NoticeBoardPost->NoticeBoardPostID}}"><i class="entypo-trash"></i></a>
                        @endif
                    </div>
                @else
                    <div class="panel-options ">
                        <strong class="incident_time">{{cus_lang("CUST_PANEL_PAGE_NOTICEBOARD_MSG_UPDATED")}}  {{\Carbon\Carbon::createFromTimeStamp(strtotime($NoticeBoardPost->updated_at))->diffForHumans() }}</strong>
                    </div>
                @endif
            </div>
            <div class="panel-body section_border_1 no_top_border make_round make_round_bottom_only" style="display: none">
                @if(Session::get('customer') == 0)
                <div class="row">

                    <label for="field-1" class="col-md-2 control-label">Title*</label>
                    <div class="col-md-4">
                        <input type="text" name="Title" class="form-control" id="field-1" placeholder="" value="{{$NoticeBoardPost->Title}}" />
                    </div>

                    <label for="field-1" class="col-md-2 control-label">Type*</label>
                    <div class="col-md-4">
                        {{Form::select('Type',array('post-none'=>'None','post-error'=>'Error','post-info'=>'Information','post-warning'=>'Warning'),$NoticeBoardPost->Type,array("class"=>"select2 post_type"))}}
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
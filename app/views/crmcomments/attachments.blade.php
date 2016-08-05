<div class="col-md-12">
    @if(!empty($attachementPaths))
        @if(count($attachementPaths)>0)
            @foreach($attachementPaths as $index=>$attachement)
                <div class="col-md-2 attachment">
                    <div data-trigger="fileinput" class="fileinput-new thumbnail">
                        <i data-id="{{$index}}" class="entypo-cancel pull-right delete-file"></i>
                        <img alt="..." height="60" width="60" src="{{getimageicons($attachement->filepath)}}" class="img-responsive">
                        <p class="text-center"><a href="{{ URL::to($type.'/'.$id.'/getattachment/'.$index)}}" alt="{{$attachement->filename}}" target="_blank">{{strlen($attachement->filename)>8?substr($attachement->filename,0,8).'..':$attachement->filename}}</a></p>
                    </div>
                    <!-- <a class="text-center" target="_blank" href="./assets/pdf/pdf.pdf">Remove File</a>-->
                </div>
            @endforeach
        @endif
    @endif
    @if(!empty($message))
        {{$message}}
    @endif
    <div class="clear"></div>
</div>
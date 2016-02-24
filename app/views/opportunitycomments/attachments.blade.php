<div class="col-md-12">
    @if(!empty($attachementPaths))
        @foreach($attachementPaths as $index=>$attachement)
            <div class="col-md-3 attachment">
                <div data-trigger="fileinput" class="fileinput-new thumbnail">
                    <i data-id="{{$index}}" class="entypo-cancel pull-right delete-file"></i>
                    <img alt="..." src="http://localhost:1234/rm/branches/abubakarlinux/neon.web/public/assets/images/PDF-300x300.png" class="img-responsive">
                    <p class="text-center"><a href="{{validfilepath($attachement->filepath)}}" target="_blank">{{$attachement->filename}}</a></p>
                </div>
                <!-- <a class="text-center" target="_blank" href="./assets/pdf/pdf.pdf">Remove File</a>-->
            </div>
        @endforeach
    @endif
    <div class="clear"></div>
</div>
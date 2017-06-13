@foreach($NoticeBoardPosts as  $NoticeBoardPost)
@include('noticeboard.single')
@endforeach
<script>
    $(document).ready(function() {
        show_summerinvoicetemplate($("[name=Detail]"));
    });
</script>
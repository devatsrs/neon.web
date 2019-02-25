<script type="text/javascript">
/**
 * Created by deven on 07/07/2015.
 */
$(document).ready(function(){
	
var lineNo = 0;
var count = 0;
	 $('button#removerow').on('click', function(e){
	    e.preventDefault();
        $. each($("input[id='bremove']:checked"), function(){
        var row = $(this).parent().parent();
        row.remove();
        
         });
    });

    $('#add-row').on('click', function(e){
        e.preventDefault();
        var itemrow = $('#rowContainer .itemrow').clone();
       itemrow.removeAttr('class');
        itemrow.find('select.select22').each(function(i,item){
            buildselect2(item);
        });

        $('#InvoiceTable > tbody').append(itemrow);
        nicescroll();
		$("textarea.autogrow").autosize();
        
    });
$("input.Price").change(function(){
alert();
});
    
});
</script>
<style>
#InvoiceTable.table > tbody > tr > td > div > a > span.select2-chosen { width:80px;}
</style>
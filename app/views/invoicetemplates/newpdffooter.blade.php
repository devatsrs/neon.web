@extends('layout.print')

@section('content')
<style type="text/css">
	#pdf_footer {
		bottom: 0;
		/*border-top: 1px solid #aaaaaa;  */
		color: #555555;
		font-size: 10px;
}
#pdf_footer table {
	width:100%;
}
</style>
	<!-- footer section start -->
	<div id="pdf_footer">
		{{nl2br($InvoiceTemplate->FooterTerm)}}
		
	</div>
	<!-- footer section start -->
</div> <!-- invoicebody(class) section end -->


 @stop
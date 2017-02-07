@extends('layout.print')

@section('content')
<style type="text/css">
	#pdf_footer {
    bottom: 0;
    border-top: 0.1pt solid #aaa;
    left: 0;
    right: 0;
    color: #aaa;
    font-size: 10px;
    text-align: center;
}
#pdf_footer table {
	width:100%;
}
</style>
<div id="pdf_footer">
    <table>
        <tbody>
            <tr>
                <td>
                    {{nl2br($InvoiceTemplate->FooterTerm)}}
                </td>
            </tr>
        </tbody>
    </table>
</div>


 @stop
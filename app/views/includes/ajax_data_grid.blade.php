<script type="text/javascript">

/*!
 * jQuery prototypal inheritance plugin boilerplate
 * Author: Alex Sexton, Scott Gonzalez
 * Further changes: @addyosmani
 * Licensed under the MIT license
 */

// myObject - an object representing a concept that you want
// to model (e.g. a car)
var AjaxDataTable = {
   options: {
    "bDestroy": true,
    "bProcessing":true,
    "bServerSide":true,
    "sPaginationType": "bootstrap",
    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>"
  },
  init: function( options, elem ) {

    console.log("Initializing...");
    // Mix in the passed-in options with the default options
    this.options = $.extend( {}, this.options, options );

    // Save the element reference, both as a jQuery
    // reference and a normal reference
    this.elem  = elem;
    this.$elem = $(elem);
    this.data_table = {};
    // Build the DOM's initial structure
    this._build();
    console.log("build done...");
    // return this so that we can chain and use the bridge with less code.
    return this;
  },
  _build: function(){
    console.log("build start...");
    console.log(this.options);
    console.log("data_table start..."+this.elem);
    this.data_table = $('#table-subscription').dataTable(this.options);
    console.log("data_table done ...");
  },
  myMethod: function( msg ){
     console.log("myMethod triggered");
    // this.$elem.append('<p>'+msg+'</p>');
  }
};

// Object.create support test, and fallback for browsers without it
if ( typeof Object.create !== "function" ) {
    Object.create = function (o) {
        function F() {}
        F.prototype = o;
        return new F();
    };
}

// Create a plugin based on a defined object
$.plugin = function( name, object ) {
  $.fn[name] = function( options ) {
    return this.each(function() {

      if ( ! $.data( this, name ) ) {
        console.log("in Each");
        $.data( this, name, Object.create(object).init(options, this ) );
      }
    });
  };
};

// Usage:
// With myObject, we could now essentially do this:
// $.plugin('myobj', myObject);

// and at this point we could do the following
// $('#elem').myobj({name: "John"});
// var inst = $('#elem').data('myobj');
// inst.myMethod('I am a method');





/******************** ************************************ */
    /*jQuery(document).ready(function ($) {

        function CustomerDataTableGrid(tableID , ajax_url , render_columns, sortging_columns,search_filter_fuction,download_button,fnDrawCallback){

            var data_table = $("#table-4").dataTable({
                 "bDestroy": true,
                 "bProcessing":true,
                 "bServerSide":true,
                 "sAjaxSource": ajax_url,
                 "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                 "sPaginationType": "bootstrap",
                 "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                 "aaSorting": sortging_columns,
                  "fnServerParams": search_filter_fuction,
                  "aoColumns":render_columns,
                 "oTableTools": oTableTools,
                "fnDrawCallback": fnDrawCallback
            });
            $('#'+tableID+' tbody').on('click', 'tr', function() {
                $(this).toggleClass('selected');
                if ($(this).hasClass('selected')) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            });
        }
    });*/
</script>
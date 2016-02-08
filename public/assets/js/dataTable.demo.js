
/*global SyntaxHighlighter*/
//SyntaxHighlighter.config.tagName = 'code';

$(document).ready( function () {
    if ( ! $.fn.dataTable ) {
        return;
    }
    var dt110 = $.fn.dataTable.Api ? true : false;

    // Work around for WebKit bug 55740
    var info = $('div.info');

    if ( info.height() < 115 ) {
        info.css( 'min-height', '8em' );
    }

    var escapeHtml = function ( str ) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    };

    // css
    var cssContainer = $('div.tabs div.css');
    if ( $.trim( cssContainer.find('code').text() ) === '' ) {
        cssContainer.find('code, p:eq(0), div').css('display', 'none');
    }

    // init html
    var table = $('<p/>').append( $('table').clone() ).html();
    $('div.tabs div.table').append(
        '<code class="multiline brush: html;">\t\t\t'+
            escapeHtml( table )+
            '</code>'
    );
    //SyntaxHighlighter.highlight({}, $('#display-init-html')[0]);

    // Allow the demo code to run if DT 1.9 is used
    if ( dt110 ) {
        // json
        var ajaxTab = $('ul.tabs li').eq(3).css('display', 'none');

        $(document).on( 'init.dt', function ( e, settings ) {
            var api = new $.fn.dataTable.Api( settings );

            var show = function ( str ) {
                ajaxTab.css( 'display', 'block' );
                $('div.tabs div.ajax code').remove();

                // Old IE :-|
                try {
                    str = JSON.stringify( str, null, 2 );
                } catch ( e ) {}

                $('div.tabs div.ajax').append(
                    '<code class="multiline brush: js;">'+str+'</code>'
                );
                SyntaxHighlighter.highlight( {}, $('div.tabs div.ajax code')[0] );
            };

            // First draw
            var json = api.ajax.json();
            if ( json ) {
                show( json );
            }

            // Subsequent draws
            api.on( 'xhr.dt', function ( e, settings, json ) {
                show( json );
            } );
        } );

        // php
        var phpTab = $('ul.tabs li').eq(4).css('display', 'none');

        $(document).on( 'init.dt.demoSSP', function ( e, settings ) {
            if ( settings.oFeatures.bServerSide ) {
                if ( $.isFunction( settings.ajax ) ) {
                    return;
                }
                $.ajax( {
                    url: '../resources/examples.php',
                    data: {
                        src: settings.sAjaxSource || settings.ajax.url || settings.ajax
                    },
                    dataType: 'text',
                    type: 'post',
                    success: function ( txt ) {
                        phpTab.css( 'display', 'block' );
                        $('div.tabs div.php').append(
                            '<code class="multiline brush: php;">'+txt+'</code>'
                        );
                        SyntaxHighlighter.highlight( {}, $('div.tabs div.php code')[0] );
                    }
                } );
            }
        } );
    }
    else {
        $('ul.tabs li').eq(3).css('display', 'none');
        $('ul.tabs li').eq(4).css('display', 'none');
    }

    // Tabs
    $('ul.tabs').on( 'click', 'li', function () {
        $('ul.tabs li.active').removeClass('active');
        $(this).addClass('active');

        $('div.tabs>div')
            .css('display', 'none')
            .eq( $(this).index() ).css('display', 'block');
    } );
    $('ul.tabs li.active').click();
} );



/**
 * Created by deven on 8/30/14.
 */




// Register an API method that will empty the pipelined data, forcing an Ajax
// fetch on the next draw (i.e. `table.clearPipeline().draw()`)
/* $.fn.dataTable.Api.register( 'clearPipeline()', function () {
 return this.iterator( 'table', function ( settings ) {
 settings.clearCache = true;
 } );
 } );*/


//
// DataTables initialisation
//





$(document).ready(function() {

    $.fn.dataTable.pipeline = function ( opts ) {
        // Configuration options
        var conf = $.extend( {
            pages: 5,     // number of pages to cache
            url: '',      // script url
            data: null,   // function or object with parameters to send to the server
            // matching how `ajax.data` works in DataTables
            method: 'GET' // Ajax HTTP method
        }, opts );

        // Private variables for storing the cache
        var cacheLower = -1;
        var cacheUpper = null;
        var cacheLastRequest = null;
        var cacheLastJson = null;

        return function ( request, drawCallback, settings ) {
            var ajax          = false;
            var requestStart  = request.start;
            var drawStart     = request.start;
            var requestLength = request.length;
            var requestEnd    = requestStart + requestLength;

            if ( settings.clearCache ) {
                // API requested that the cache be cleared
                ajax = true;
                settings.clearCache = false;
            }
            else if ( cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper ) {
                // outside cached data - need to make a request
                ajax = true;
            }
            else if ( JSON.stringify( request.order )   !== JSON.stringify( cacheLastRequest.order ) ||
                JSON.stringify( request.columns ) !== JSON.stringify( cacheLastRequest.columns ) ||
                JSON.stringify( request.search )  !== JSON.stringify( cacheLastRequest.search )
                ) {
                // properties changed (ordering, columns, searching)
                ajax = true;
            }

            // Store the request for checking next time around
            cacheLastRequest = $.extend( true, {}, request );

            if ( ajax ) {
                // Need data from the server
                if ( requestStart < cacheLower ) {
                    requestStart = requestStart - (requestLength*(conf.pages-1));

                    if ( requestStart < 0 ) {
                        requestStart = 0;
                    }
                }

                cacheLower = requestStart;
                cacheUpper = requestStart + (requestLength * conf.pages);

                request.start = requestStart;
                request.length = requestLength*conf.pages;

                // Provide the same `data` options as DataTables.
                if ( $.isFunction ( conf.data ) ) {
                    // As a function it is executed with the data object as an arg
                    // for manipulation. If an object is returned, it is used as the
                    // data object to submit
                    var d = conf.data( request );
                    if ( d ) {
                        $.extend( request, d );
                    }
                }
                else if ( $.isPlainObject( conf.data ) ) {
                    // As an object, the data given extends the default
                    $.extend( request, conf.data );
                }

                settings.jqXHR = $.ajax( {
                    "type":     conf.method,
                    "url":      conf.url,
                    "data":     request,
                    "dataType": "json",
                    "cache":    false,
                    "success":  function ( json ) {
                        cacheLastJson = $.extend(true, {}, json);

                        if ( cacheLower != drawStart ) {
                            json.data.splice( 0, drawStart-cacheLower );
                        }
                        json.data.splice( requestLength, json.data.length );

                        drawCallback( json );
                    }
                } );
            }
            else {
                json = $.extend( true, {}, cacheLastJson );
                json.draw = request.draw; // Update the echo for each response
                json.data.splice( 0, requestStart-cacheLower );
                json.data.splice( requestLength, json.data.length );

                drawCallback(json);
            }
        }
    };




} );
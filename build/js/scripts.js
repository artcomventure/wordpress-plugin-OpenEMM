(function() {

    // determines if event listeners support options instead of only boolean (useCapture)
    // @link https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
    var eventOptionsSupported = false;
    try { window.addEventListener('test', null, Object.defineProperty({}, 'passive', { get: function() { eventOptionsSupported = true; } }));
    } catch(err) {}

    // ---

    var $forms = document.getElementsByClassName( 'openemm-form' );

    var busy = false;
    function openemmFormLabelSize( p ) {
        // we throttle/debounce calculations
        if ( !busy ) return busy = window.requestAnimationFrame( openemmFormLabelSize );
        // requestAnimationFrame passes the timestamp as parameter
        // in other cases we wait till the next requestAnimationFrame is called
        else if ( !(!isNaN(parseFloat(p)) && isFinite(p)) ) return;

        // now we know requestAnimationFrame is calling
        // so we run the calculations

        Array.prototype.forEach.call( $forms, function( $form ) {
            Array.prototype.forEach.call( $form.querySelectorAll( 'input[type="text"], select' ), function( $input ) {
                var $formItem = $input.parentElement;
                while ( $formItem.className.split( / +/ ).indexOf( 'form-item' ) < 0 && $formItem.parentElement ) {
                    $formItem = $formItem.parentElement;
                }

                if ( !$formItem.parentElement ) return;

                var $label = $input.previousElementSibling;
                if ( !$label || $label.tagName.toLowerCase() !== 'label' ) return;

                var style = window.getComputedStyle( $input );

                $label.style.lineHeight = $input.offsetHeight + 'px';
                $label.style.paddingLeft = parseInt(style.paddingLeft) + parseInt(style.borderLeftWidth) + 'px';

                // attach label as placeholder functionality (once)
                if ( $formItem.className.split( / +/ ).indexOf( 'has-label-placeholder' ) < 0 ) {
                    var classes = $formItem.className.trim().split( / +/ );
                    $formItem.className = classes.join( ' ' ) + ' has-label-placeholder';

                    ['input', 'change', 'blur'].forEach( function( event ) {
                        $input.addEventListener( event, function() {
                            $label.style.display = $input.value.trim() ? 'none' : '';
                        }, eventOptionsSupported ? {passive: true} : false )
                    } );

                    $input.dispatchEvent( new Event( 'blur', { bubbles: true } ) );
                }
            } );

            // ajaxify form (once)
            if ( $form.className.split( / +/ ).indexOf( 'ajaxify' ) < 0 ) {
                $form.addEventListener( 'submit', function( e ) {
                    e.preventDefault();

                    // remove 'old' submit messages
                    Array.prototype.forEach.call( $form.querySelectorAll( '.success, .error' ), function( $element ) {
                        var classes = $element.className.trim().split( / +/ );

                        if ( classes.indexOf( 'note' ) >= 0 || classes.indexOf( 'message' ) >= 0 ) {
                            return $element.style.display = 'none';
                        }

                        $element.className = classes.filter( function( className ) {
                            return ['success', 'error'].indexOf( className ) < 0;
                        } ).join( ' ' );
                    } );

                    var classes = $form.className.trim().split( / +/ );
                    if ( classes.indexOf( 'submitting' ) >= 0 ) return;
                    classes.push( 'submitting' );
                    $form.className = classes.join( ' ' );

                    var FD = new FormData(this).entries(),
                        data = []; // data to send

                    // loop through form fields and collect data
                    var input = FD.next();
                    while ( input.value !== undefined ) {
                        data.push(encodeURIComponent(input.value[0]) + '=' + encodeURIComponent(input.value[1]));
                        input = FD.next();
                    }

                    var XHR = (window.XMLHttpRequest ? new XMLHttpRequest()
                        : (window.ActiveXObject ? new ActiveXObject( 'Microsoft.XMLHTTP' ) : null));
                    if ( !XHR ) return undefined;

                    XHR.addEventListener( 'loadend', function() {
                        var classes = $form.className.trim().split( / +/ );
                        $form.className = classes.filter( function( className ) {
                            return className != 'submitting';
                        } ).join( ' ' );
                    } ); // abort, load or error

                    XHR.addEventListener('error', function() {} );

                    XHR.addEventListener('load', function() {
                        // maybe json
                        var message;
                        try {
                            message = JSON.parse( this.responseText );
                            // json means message
                            var $message = document.createElement( 'p' );
                            $message.className = 'message ' + message.type||'status';
                            $message.innerHTML = message.message;

                            $form.appendChild( $message );
                        }
                        catch ( e ) {}

                        if ( !message ) {
                            $form.innerHTML = this.responseText;
                            openemmFormLabelSize();
                        }
                    } );

                    XHR.open('POST', $form.getAttribute( 'action' )||'/openemm/submit');
                    XHR.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    XHR.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    XHR.send( data.join('&').replace(/%20/g, '+') );
                }, false );

                var classes = $form.className.trim().split( / +/ );
                $form.className = classes.join( ' ' ) + ' ajaxify';
            }
        } );

        // release the function for the next round
        busy = false;
    }

    openemmFormLabelSize();
    window.addEventListener( 'load', openemmFormLabelSize, eventOptionsSupported ? {passive: true} : false );
    window.addEventListener( 'resize', openemmFormLabelSize, eventOptionsSupported ? {passive: true} : false );

})();

// window.requestAnimationFrame
// http://paulirish.com/2011/requestanimationframe-for-smart-animating/
// http://my.opera.com/emoller/blog/2011/12/20/requestanimationframe-for-smart-er-animating
// requestAnimationFrame polyfill by Erik Möller. fixes from Paul Irish and Tino Zijdel
// MIT license
(function () {
    var lastTime = 0;
    var vendors = ['ms', 'moz', 'webkit', 'o'];
    for ( var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x ) {
        window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
        window.cancelAnimationFrame = window[vendors[x] + 'CancelAnimationFrame']
            || window[vendors[x] + 'CancelRequestAnimationFrame'];
    }

    if ( !window.requestAnimationFrame )
        window.requestAnimationFrame = function ( callback, element ) {
            var currTime = new Date().getTime();
            var timeToCall = Math.max( 0, 16 - (currTime - lastTime) );
            var id = window.setTimeout( function () {
                    callback( currTime + timeToCall );
                },
                timeToCall );
            lastTime = currTime + timeToCall;
            return id;
        };

    if ( !window.cancelAnimationFrame )
        window.cancelAnimationFrame = function ( id ) {
            clearTimeout( id );
        };
}());
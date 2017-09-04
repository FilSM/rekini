var fsmMultiAction;

(function ($) {
    
    "use strict";
    fsmMultiAction = function (opts) {
        $('.' + opts.css).off('click.krajee').on('click.krajee', function (e, options) {
            
            options = options || {};
            if (!options.proceed) {
            
                var $btn = $(this); 
                var $grid = opts.grid;
                var lib = window[opts.lib];
                var keys = $grid ? $('#' + $grid).yiiGridView('getSelectedRows') : 0;
                //var disabled = $btn.attr('disabled');
                if(keys.length > 0){
                    var attribute = !empty(opts.aButton) ? 'href' : (!empty(opts.vButton) ? 'value' : null);
                    var href = $btn.attr(attribute);
                    var oldHref = $btn.prop('oldHref');
                    if(oldHref == undefined){
                        $btn.prop('oldHref', href);
                    }else{
                        href = oldHref;
                    }
                    var ids = keys.join();
                    href = href + '?ids=' + ids;
                    $btn.attr(attribute, href);
                }else if($grid){
                    //e.stopPropagation();
                    //e.preventDefault();                
                    return false;
                }

                e.stopPropagation();
                e.preventDefault();
                if(!empty(opts.msg)){
                    lib.confirm(opts.msg, function (result) {
                        if (!result) {
                            return;
                        }
                        doClick();
                    });
                }else{
                    doClick();
                }
                
                function doClick(){
                    if(!empty(opts.aButton)){
                        $btn.attr('disabled', true); 
                        $btn.addClass('fsm-multi-action-process');
                        $btn.find('span.img-loader').show(); 
                        $btn.data('method', 'post').trigger('click', {proceed: true});
                    }else if(!empty(opts.vButton)){
                        $btn.trigger('click', {proceed: true});
                    }
                }
            }
        });
    };
    
})(jQuery);

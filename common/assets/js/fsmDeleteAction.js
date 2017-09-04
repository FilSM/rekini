var fsmActionDialog;

(function ($) {
    
    "use strict";
    fsmActionDialog = function (opts) {
        $('.' + opts.css).off('click.krajee').on('click.krajee', function (e, options) {
            
            options = options || {};
            if (!options.proceed) {
            
                var $btn = $(this); 
                var $grid = opts.grid;
                var lib = window[opts.lib];
                var keys = $grid ? $('#' + $grid).yiiGridView('getSelectedRows') : 0;
                //var disabled = $btn.attr('disabled');
                if(keys.length > 0){
                    var href = $btn.attr('href');
                    var oldHref = $btn.prop('oldHref');
                    if(oldHref == undefined){
                        $btn.prop('oldHref', href);
                    }else{
                        href = oldHref;
                    }
                    var ids = keys.join();
                    href = href + '?ids=' + ids;
                    $btn.attr('href', href);
                }else if($grid){
                    //e.stopPropagation();
                    //e.preventDefault();                
                    return false;
                }

                e.stopPropagation();
                e.preventDefault();
                lib.confirm(opts.msg, function (result) {
                    if (!result) {
                        return;
                    }
                    if (opts.pjax) {
                        $.ajax({
                            url: $btn.attr('href'),
                            type: 'post',
                            beforeSend: function() {
                                $btn.addClass('kv-delete-cell');
                            },
                            complete: function () {
                                $btn.removeClass('kv-delete-cell');
                            },
                            error: function (xhr, status, error) {
                                lib.alert('There was an error with your request.' + xhr.responseText);
                            }
                        }).done(function (data) {
                            $.pjax.reload({container: '#' + opts.pjaxContainer});
                        });
                    } else {
                        $btn.attr('disabled', true); 
                        //$btn.addClass('fsm-delete-action'); 
                        $btn.parent().find('span.img-loader').show(); 
                        //$btn.parent().find('span.img-loader').addClass('fsm-delete-action'); 
                        $btn.data('method', 'post').trigger('click', {proceed: true});
                    }
                });
            }
        });
    };
    
    $(document).ready(function () {
        
        $('.select-on-check-all', '.page-index').click(function(){
            var checked = $(this).prop('checked');
            $('#btn-dialog-selected', '.page-index').attr('disabled', !checked);
        });
        $('[name="selection[]"]', '.page-index').click(function(){
            var checkedCB = $('[name="selection[]"]:checked');
            var checked = checkedCB.length > 0;
            $('#btn-dialog-selected', '.page-index').attr('disabled', !checked);
        });
        
    });
    
})(jQuery);

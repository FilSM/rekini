(function ($) {
    
    var oldModalWindowContent = '';
    var $select2 = null;
    var currentUrl = '';
    
    //$('.show-modal-button').off('click.modal').on('click.modal', showModalButtonClick);
    $('body').off('click.modal').on('click.modal', '.show-modal-button', showModalButtonClick);
    
    function showModalButtonClick(e, options){
        options = options || {};
        var $this = $(this);
        var isMultiSelectBtn = $this.hasClass('btn-multi-select');
        if (isMultiSelectBtn && !options.proceed) {
            return false;
        }
        
        $select2 = $this.closest('.input-group').find('select');
        currentUrl = window.location.toString();
        
        var modalWindow = $('#modal-window');
        if (modalWindow.length === 0) {
            return false;
        }/*else if (options.proceed || (modalWindow.length > 0)) {
            modalWindow.each(function(){
                $(this).modal('hide');
            })
        }*/
        oldModalWindowContent = modalWindow.find('#modalContent').html();

        modalWindow.draggable({
            handle: "#modalHeader"
        });
        
        modalWindow.modal('show')
            .find('#modalContent')
            .load($this.attr('value'));
        
        modalWindow.on('hidden.bs.modal', function (e) {
            modalWindow.find('#modalContent').html(oldModalWindowContent);
        })
        
        //dynamiclly set the header for the modal
        document.getElementById('modalHeaderTitle').innerHTML = '<h4>' + $this.attr('title') + '</h4>';
    }
    
    $(document).on('click', '.show-new-tab-button', function () {
        var $this = $(this);
        $select2 = $this.closest('.input-group').find('select');
        
        var url = $this.attr('value');
        var refreshBtn = $this.closest('.input-group-btn').find('.refresh-list-button');
        if(refreshBtn.length > 0){
            refreshBtn.css('display', 'inline');
            refreshBtn.data('select2', $select2);
        }
        var win = window.open(url, '_blank');
        win.focus();
    });
    
    $(document).on('click', '.refresh-list-button', function () {
        var $this = $(this);
        var url = $this.attr('value');
        $.post(
            url, 
            null, 
            function (result) {
                var data = result.data;
                var updatedSelect = $this.data('select2');
                var id = $(updatedSelect).attr('id');
                var $s2Options = $(updatedSelect).attr('data-s2-options');
                var configSelect2 = eval($(updatedSelect).attr('data-krajee-select2'));
                configSelect2.data = data;
                
                $('#'+id).find('option:not(:first)').remove();
                //updatedSelect.select2(configSelect2);
                $.when(updatedSelect.select2(configSelect2)).done(initS2Loading(id, $s2Options));
            }, 
            'json'
        );
        $this.css({display: 'none'});
    });
    
    $(document).on('submit', '#modal-window form[data-pjax]', function(event) {
        //$('#modal-window').find('#close-button').click();
        //$.pjax.submit(event, '#pjax-container')
        return false;
    })     

    $(document).on('pjax:error', '#modal-pjax', function (event, xhr, textStatus, options) {
        //alert('Failed to load the page');
        event.preventDefault();
        if (textStatus == 'abort') {
            window.location = currentUrl;
            //window.history.back();
        } 
    })

    $(document).on('pjax:success', '#modal-pjax', function (event, xhr, textStatus, options) {
        $('#modal-window').find('#close-button').click();

        var result = JSON.parse(xhr);
        if(result && (result.data != undefined)){
            var data = result.data;
            var selectedId = result.selected;
            var $s2Options = $($select2).attr('data-s2-options');
            var depdropOptions = $($select2).attr('data-krajee-depdrop');

            var configSelect2 = eval($($select2).attr('data-krajee-select2'));
            configSelect2.data = data;
            var id = $($select2).attr('id');
            $('#'+id).find('option:not(:first)').remove();
            //$select2.select2(configSelect2);
            if(depdropOptions != undefined){
                $($select2).on('depdrop:afterChange', function(){
                    $(this).val(selectedId).trigger("change");
                });
                depdropOptions = eval($($select2).attr('data-krajee-depdrop'));
                var depends = depdropOptions.depends;
                if(!empty(depends)){
                    var lastElement = depends[depends.length - 1];
                    $('#' + lastElement).trigger('depdrop:change');
                }
            }else{
                $.when($select2.select2(configSelect2)).done(initS2Loading(id, $s2Options));
                $($select2).val(selectedId).trigger("change");
            }
        }
        if(result == 'reload'){
            var timeoutTimer;
            timeoutTimer = window.setTimeout( function() {
                window.clearTimeout( timeoutTimer )
                location.reload(true);
            }, 500 );
        }
        //window.history.back();
    });
    
    $(document).ready(function () {
        $('form div.required label').attr('title', 'Required field');
            
        var resetButton = $('form button[type=reset]');
        resetButton.click(function(){
            var href = document.location.toString().split('?', 1)[0];
            document.location = href;
            return false;
        });        
    });
    
    var numberFields = $('form').find('.number-field');
    if(numberFields.length > 0){
        numberFields.keypress(function(e){
            if (e.which == 44){
                if(this.value === ''){
                    this.value = '0';
                }
                this.value = this.value + String.fromCharCode(46);
            }
            return false;
        });
    }
    
})(jQuery);

function checkRequiredInputs(form){
    /*
    var timeoutTimer;
    timeoutTimer = window.setTimeout( function() {
        var requiredInputs = form.find('.form-group.required select, [aria-required="true"]');
        $.each(requiredInputs, function (index, input) {
            var id = $(input).attr('id');
            form.yiiActiveForm('updateAttribute', id);
        });
        window.clearTimeout( timeoutTimer )
    }, 500 );
    */
    var requiredInputs = form.find('.form-group.required select, [aria-required="true"]');
    $.each(requiredInputs, function (index, input) {
        var id = $(input).attr('id');
        form.yiiActiveForm('updateAttribute', id);
    });
}


function filRound(value, decimals) {
    var result = round(value, decimals);
    var parts = explode('.', result);
    if (parts.length == 1) {
        result = result + '.' + str_pad('', decimals, '0');
    } else {
        result = parts[0] + '.' + str_pad(parts[1], decimals, '0');
    }
    return result;
}
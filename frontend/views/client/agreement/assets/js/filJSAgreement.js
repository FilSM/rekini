(function ($) {

    $(document).ready(function () {

        var form = $('#agreement-form');
        
        form.find('#cbx-loan-agreement').on('change', function() {
            var checked = $(this).val() == 1;
            if(checked){
                form.find('#rate-data-container').show('slow');
            }else{
                form.find('#rate-data-container').hide('slow');
                form.find('#rate-data-container input[type="text"]').val('');
            }
            //console.log('checkbox changed');
        }).trigger('change');
    });
    
})(window.jQuery);     
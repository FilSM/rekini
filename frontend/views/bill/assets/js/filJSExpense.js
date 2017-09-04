(function ($) {

    $(document).ready(function () {
        
        var form = $("#expense-form");
        
        form.on("change", "#expense-summa, #expense-vat", 
            function(e) {
                var summa = parseFloat(form.find('#expense-summa').val());
                var summaVat = parseFloat(form.find('#expense-vat').val());
                summa = !isNaN(summa) ? filRound(summa, 2) : '0.00';
                summaVat = !isNaN(summaVat) ? filRound(summaVat, 2) : '0.00';
                
                var total = filRound(parseFloat(summa) + parseFloat(summaVat), 2);
                form.find('#expense-total').prop('value', total);
            }
        );
            
    });
    
})(window.jQuery);     
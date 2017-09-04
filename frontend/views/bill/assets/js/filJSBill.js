(function ($) {

    $(document).ready(function () {
        
        var sidebarToggleBotton = $('.bill-index .toggle-button');
        if(sidebarToggleBotton.length != 0){
            sidebarToggleBotton.click(function(){
                var billSearchBlock = $('.bill-index .bill-search');
                var sidebarIsVisible = $('.bill-index .bill-search').is(":visible");
                if(sidebarIsVisible){
                    billSearchBlock.hide();
                } else {
                    billSearchBlock.show();
                }
                $('table#bill-list').floatThead('reflow');
            });
        }
        
        var form = $("#bill-form");
        var clientTax = 0;
        var vatTaxable = 1;
        
        form.on("change", ".vat-revers input", function(){
            var $this = $(this);
            var checked = ($this.val() == 1);
            var idArr = $(this).attr('id').split('-')
            var index = idArr[1];
            var vatControl = form.find('#billproduct-'+index+'-vat');
            vatControl.prop('disabled', checked);
            if(checked){
                form.find('#billproduct-'+index+'-vat').val('');
            }else{
                form.find('#billproduct-'+index+'-vat').val(0);
            }
            vatControl.change();
        });
        form.find('.vat-revers input[value="1"]').change();
        
        var currentValue = form.find('[name="Bill[doc_type]"]').val();
        switch (currentValue) {
            case 'avans':
            case 'bill':
            default:
                form.find('#waybill-data').hide("slow");
                break;
            case 'invoice':
                form.find('#waybill-data').show("slow");
                break;
        }
        
        var accordingContractState = 0;
        
        form.find('#bill-according_contract').on('init.bootstrapSwitch switchChange.bootstrapSwitch', checkAccordingContractState);
        
        function checkAccordingContractState(){
            accordingContractState = (arguments.length == 1 ? arguments[0] : (arguments[1] != undefined ? arguments[1] : null));
            var inputList = form.find('#product-data-container').find('.product-item-input');
            var selectList = form.find('#product-data-container').find('.product-item-select');
            var measureSelect = form.find('#product-data-container').find('.measure-item-select');
            switch (accordingContractState) {
                case false:
                default:
                    inputList.closest('.form-group').hide();
                    selectList.closest('.form-group').show();
                    
                    inputList.val('');
                    break;
                case true:
                    selectList.closest('.form-group').hide();
                    inputList.closest('.form-group').show();
                    
                    selectList.val('').change();
                    break;
            }            
            //measureSelect.closest('.input-group').find('.show-modal-button').prop('disabled', accordingContractState).css({display: (accordingContractState ? 'inline' : 'none')});
            measureSelect.closest('.input-group').find('.show-modal-button').css({display: (accordingContractState ? 'inline' : 'none')});
            measureSelect.prop('disabled', !accordingContractState);
        }

        form.find('#client-id').change(function(e) {
            var clientId = $(this).val();
            var url = appUrl + '/client/ajax-get-tax';
            $.get(
                url,
                {id: clientId}, 
                function (data) {
                    data = JSON.parse(data);
                    var tax = data[0].tax;
                    clientTax = tax;
                }
            );
        });

        form.find('#bill-doc_type button').click(function () {
            var billType = $(this).val();
            //form.find('#bill-doc_type button').removeClass('active');

            switch (billType) {
                case 'avans':
                case 'bill':
                default:
                    form.find('#waybill-data').hide("slow");
                    form.find('#waybill-data input[type="text"]').val('');
                    break;
                case 'invoice':
                    form.find('#waybill-data').show("slow");
                    break;
            }
            
            var id = form.find('#bill-id').val();
            var url = appUrl + '/bill/ajax-get-last-number';
            $.get(
                url,
                {
                    id: id,
                    doc_type: billType
                }, 
                function (data) {
                    form.find('#bill-doc_number').val(data);
                },
                'json'
            );            
        });
        
        form.on("change", ".product-item-select", 
            function(e) {
                var id = $(this).val();
                var idArr = $(this).attr('id').split('-')
                var index = idArr[1];
                if(!accordingContractState){
                    form.find('#billproduct-'+index+'-measure_id').val('').change();
                }
                
                var url = appUrl + "/product/ajax-get-measure";
                $.get(
                    url,
                    {id: id}, 
                    function (data) {
                        //data = JSON.parse(data);
                        var measure = data[0].measure;
                        if(!accordingContractState){
                            form.find('#billproduct-'+index+'-measure_id').val(measure.id).change();
                        }
                        
                        if(!empty(clientTax)){
                            form.find('#billproduct-'+index+'-vat').prop('value', clientTax);
                        }else{
                            form.find('#billproduct-'+index+'-vat').val('');
                        }
                    },
                    'json'
                );
                //alert('selected id = ' + id); 
            }
        );
        
        form.on("change", ".bill-product-amount, .bill-product-price, .bill-product-vat", 
            function(e) {
                var idArr = $(this).attr('id').split('-')
                var index = idArr[1];
                
                var amount = parseFloat(form.find('#billproduct-'+index+'-amount').val());
                var price = parseFloat(form.find('#billproduct-'+index+'-price').val());
                var vat = parseFloat(form.find('#billproduct-'+index+'-vat').val());
                
                var summa = (!isNaN(amount) && !isNaN(price)) ? filRound(amount * price, 2) : '0.00';
                var summaVat = !isNaN(vat) ? filRound((summa * vat) / 100, 2) : '0.00';
                var total = filRound(parseFloat(summa) + parseFloat(summaVat), 2);
                
                form.find('#billproduct-'+index+'-summa').prop('value', summa);
                form.find('#billproduct-'+index+'-summa_vat').prop('value', summaVat);
                form.find('#billproduct-'+index+'-total').prop('value', total);
                recalcBillSumma();
            }
        );
        form.find('.bill-product-amount').trigger('change');
        
        form.find('.dynamicform_wrapper').bind("afterInsert", 
            function(e) {
                checkAccordingContractState(accordingContractState);
                form.find('.bill-product-vat').each(function(index, item){
                    if((form.find('#billproduct-'+index+'-product_id').val() === '') && !empty(clientTax)){
                        $(item).val(clientTax);
                    }
                });
            }
        );
        
        form.find('.dynamicform_wrapper').bind("afterDelete", 
            function(e) {
                recalcBillSumma();
            }
        );

        function recalcBillSumma(){
            var billSumma = 0;
            form.find('.bill-product-summa').each(function(index, item){
                var val = $(item).val();
                if(val){
                    billSumma += parseFloat($(item).val());
                }
            });
            billSumma = filRound(billSumma, 2);
            form.find('#bill-summa').prop('value', billSumma);
            
            var billSumma = 0;
            form.find('.bill-product-summa_vat').each(function(index, item){
                var val = $(item).val();
                if(val){
                    billSumma += parseFloat($(item).val());
                }
            });
            billSumma = filRound(billSumma, 2);
            form.find('#bill-vat').prop('value', billSumma);
            
            var billSumma = 0;
            form.find('.bill-product-total').each(function(index, item){
                var val = $(item).val();
                if(val){
                    billSumma += parseFloat($(item).val());
                }
            });
            billSumma = filRound(billSumma, 2);
            form.find('#bill-total').prop('value', billSumma);
        }
            
        $('#bill-search').find('#billsearch-project_id, #billsearch-agreement_id, #billsearch-first_client_id').on('select2:select', function (evt) {
            $('table#bill-list').floatThead('reflow');
            var id 
        });
            
        $('#bill-search').find('#billsearch-project_id, #billsearch-agreement_id, #billsearch-first_client_id').on('select2:unselect', function (evt) {
            $('table#bill-list').floatThead('reflow');
        });
        
        $('#bill-search #billsearch-project_id').on('change', function () {
            var ids = $(this).val(); 
            var url = appUrl + (!empty(ids) ? "/project/ajax-get-agreement-list" : "/agreement/ajax-get-full-agreement-list");
            $.post(
                url,
                {
                    depdrop_parents: {ids: ids},
                }, 
                function (data) {
                    //data = JSON.parse(data);
                    var newData = [];
                    if(!empty(data) && !empty(data.output)){
                        $.each(data.output, function(key, item){
                            newData.push({id: item.id, text: item.name})
                        });
                    }
                    var id = 'bill-search #billsearch-agreement_id';
                    var updatedSelect = $('#'+id);
                    var $s2Options = $(updatedSelect).attr('data-s2-options');
                    var configSelect2 = eval($(updatedSelect).attr('data-krajee-select2'));
                    configSelect2.data = newData;

                    $('#'+id).find('option').remove();
                    //updatedSelect.select2(configSelect2);
                    $.when(updatedSelect.select2(configSelect2)).done(initS2Loading(id, $s2Options));                    
                },
                'json'
            );            
        });
        
        $('.select-on-check-all', '.bill-index').click(function(){
            var checked = $(this).prop('checked');
            var btn = $('#bill-write-on-basis-many', '.bill-index');
            if(btn.length > 0){
                btn.attr('disabled', !checked);
            }
            var btn = $('#bill-payments-create-many', '.bill-index');
            if(btn.length > 0){
                btn.attr('disabled', !checked);
            }
        });
        $('[name="selection[]"]', '.bill-index').click(function(){
            var checkedCB = $('[name="selection[]"]:checked');
            var checked = checkedCB.length > 0;
            var btn = $('#bill-write-on-basis-many', '.bill-index');
            if(btn.length > 0){
                btn.attr('disabled', !checked);
            }
            var btn = $('#bill-payments-create-many', '.bill-index');
            if(btn.length > 0){
                btn.attr('disabled', !checked);
            }
        });
            
        form.find('#project-id').change(function(){
            var $this = $(this);
            var id = $this.val();
            var emptyId = empty(id);

            var btnAddAgreement = form.find('#bill-agreement_id').closest('.input-group').find('.show-modal-button');
            var href = btnAddAgreement.attr('value').split('?');
            btnAddAgreement.attr('value', href[0] + '?project_id=' + id);
            btnAddAgreement.prop('disabled', emptyId);

            form.find('#project-static-text').hide('slow').find('.form-control-static').html('');

            var url = appUrl + "/project/ajax-get-model";
            $.get(
                url,
                {id: id}, 
                function (data) {
                    if(!(vatTaxable = data.vat_taxable)){
                        var message = lajax.t('This project is non taxable and you will not have the opportunity to charge VAT');
                        form.find('#project-static-text').show('slow').find('.form-control-static').html(message);
                    }
                }
            );                    
        });
    });
    
})(window.jQuery);     
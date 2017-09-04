(function ($) {

    $(document).ready(function () {

        var form = $('#client-form');
        var currentType = form.find('[name="Client[client_type]"]').val();
        var currentVat = form.find('[name="Client[vat_payer]"]').val();
        var currentLegalAddress = form.find('#client-legal_address').val();
        var currentOfficeAddress = form.find('#client-office_address').val();
        
        var labelsName = form.find('#client-name').data('labels');
        var labelsRegNumber = form.find('#client-reg_number').data('labels');
        var labelsLegalAddress = form.find('#legal-address-data').data('labels');
        var labelsOfficeAddress = form.find('#office-address-data').data('labels');
        var labelsCheckboxAddress = form.find('#cbx-use-address-container label').data('labels');
        
        if(currentType == 'physical'){
            form.find('.field-client-name label').html(labelsName.physical);
            form.find('.field-client-reg_number label').html(labelsRegNumber.physical);
            form.find('#legal-address-data h3').html(labelsLegalAddress.physical);
            form.find('#office-address-data h3').html(labelsOfficeAddress.physical);
            form.find('#cbx-use-address-container label').html(labelsCheckboxAddress.physical);
            //form.find('#legaladdress-apartment_number').show();
            //form.find('#officeaddress-apartment_number').show();
        }
        
        if(currentVat == 0){
            form.find('.field-client-vat_number, .field-client-tax').hide();
        }
        
        var addressSame = ((currentLegalAddress == '') && (currentOfficeAddress == '')) || (currentLegalAddress == currentOfficeAddress);
        if(addressSame){
            form.find('#office-address-data-container').hide();
        }
        
        form.find('#client-client_type button').click(function () {
            var $this = $(this);
            if($this.hasClass( "active" )){
                return false;
            }
            var currentType = $(this).val();
            switch (currentType) {
                case 'physical':
                default:
                    form.find('.field-client-name label').html(labelsName.physical);
                    form.find('.field-client-reg_number label').html(labelsRegNumber.physical);
                    form.find('#legal-address-data h3').html(labelsLegalAddress.physical);
                    form.find('#office-address-data h3').html(labelsOfficeAddress.physical);
                    form.find('#cbx-use-address-container label').html(labelsCheckboxAddress.physical);
                    //form.find('#legaladdress-apartment_number').show("slow");
                    //form.find('#officeaddress-apartment_number').show("slow");
                    
                    form.find('#btn-lursoft-search').hide().closest('.input-group').css({display: 'block'}).find('input').css({borderRadius: '4px'});
                    break;
                case 'legal':
                    form.find('.field-client-name label').html(labelsName.legal);
                    form.find('.field-client-reg_number label').html(labelsRegNumber.legal);
                    form.find('#legal-address-data h3').html(labelsLegalAddress.legal);
                    form.find('#office-address-data h3').html(labelsOfficeAddress.legal);
                    form.find('#cbx-use-address-container label').html(labelsCheckboxAddress.legal);
                    //form.find('#legaladdress-apartment_number').hide("slow");
                    //form.find('#officeaddress-apartment_number').hide("slow");
                    
                    form.find('#btn-lursoft-search').show().closest('.input-group').css({display: 'table'}).find('input').css({borderRadius: '0px 4px 4px 0px'});
                    break;
            }
        });

        form.find('#client-vat_payer button').click(function () {
            var $this = $(this);
            if($this.hasClass( "active" )){
                return;
            }
            var value = form.find('[name="Client[vat_payer]"]').val();
            switch (value) {
                case '0':
                default:
                    form.find('.field-client-vat_number, .field-client-tax').show("slow");
                    currentVat = 1;
                    break;
                case '1':
                    form.find('.field-client-vat_number, .field-client-tax').hide("slow");
                    form.find('.field-client-vat_number, .field-client-tax').find('input[type="text"]').val('')
                    currentVat = 0;
                    break;
            }
        });
        
        form.find('#cbx-use-legal-address').on('change', function() {
            addressSame = ($(this).val() == 1);
            if(addressSame){
                form.find('#office-address-data-container').hide('slow');
                form.find('#office-address-data-container input[type="text"]').val('');
                form.find('#office-address-data-container select').val('').change();
            }else{
                form.find('#office-address-data-container').show('slow');
            }
            //console.log('checkbox changed');
        });

        form.on("change", ".bank-item-select", 
            function(e) {
                var id = $(this).val();
                var idArr = $(this).attr('id').split('-')
                var index = idArr[1];

                var url = appUrl + "/bank/ajax-get-swift";
                $.get(
                    url,
                    {id: id}, 
                    function (data) {
                        data = JSON.parse(data);
                        var swift = data[0].swift;
                        form.find('#clientbank-'+index+'-swift').val(swift);
                    });
                //alert('selected id = ' + id); 
            }
        );
    
        form.find('#btn-lursoft-search').click(function(e) {
            var $this = $(this);
            $this.removeClass('btn-default btn-success btn-danger').addClass('btn-warning');
            form.find('#btn-vies-search').removeClass('btn-default btn-success btn-danger').addClass('btn-default');
            var url = $(this).attr('value');
            var name = form.find('#client-name').val();
            var code = form.find('#client-reg_number').val();
            
            //form.yiiActiveForm('validate', true);
            $.get(
                url,
                {name: name, code: code},
                function (data) {
                    if(empty(data)){
                        $this.removeClass('btn-warning').addClass('btn-danger');
                        //alert('No data!');
                        return false;
                    }
                    //data = JSON.parse(data);
                    name = data.firm + (!empty(data.type) ? ' ' + data.type : '');
                    var vatPayer = !empty(data.pvncode);
                    var vatNumber = (!empty(data.pvncode) ? data.pvncode : '')
                    var address = data.address.adress_full + ', ' + 'LV' + data.address.index;
                    
                    form.find('#client-name').val(name);
                    if(vatPayer && (currentVat == 0)){
                        //form.find('[name="Client[vat_payer]"]').val(1);
                        form.find('#client-vat_payer button').not('.active').trigger('click');
                    }                    
                    form.find('#client-vat_number').val(vatNumber);
                    form.find('#client-legal_address').val(address);
                    form.find('#client-legal_country_id').val('').change();
                    if(!addressSame){
                        form.find('#client-office_address').val(address);
                        form.find('#client-office_country_id').val('').change();
                    }
                    $this.removeClass('btn-warning').addClass('btn-success');
                    
                }).done(function (data) {
                    checkRequiredInputs(form);
                    //alert('YES!!!');
                });
            //alert('selected id = ' + id); 
        });
        
        form.find('#btn-vies-search').click(function(e) {
            var $this = $(this);
            $this.removeClass('btn-default btn-success btn-danger').addClass('btn-warning');
            form.find('#btn-lursoft-search').removeClass('btn-default btn-success btn-danger').addClass('btn-default');
            var url = $(this).attr('value');
            var code = form.find('#client-vat_number').val();
            
            $.get(
                url,
                {code: code},
                function (data) {
                    if(empty(data)){
                        $this.removeClass('btn-warning').addClass('btn-danger');
                        //alert('No data!');
                        return false;
                    }
                    //data = JSON.parse(data);
                    var clientInput = form.find('#client-name');
                    var regNumberInput = form.find('#client-reg_number');
                    var addressInput = form.find('#client-legal_address');
                    var name = clientInput.val();
                    var regNumber = regNumberInput.val();
                    var address = addressInput.val();
                    
                    if(empty(name) && !empty(data.name)){
                        clientInput.val(data.name);
                        clientInput.closest('.form-group').removeClass('has-error').find('.help-block').html('');
                    }
                    if(empty(regNumber && !empty(data.vatNumber))){
                        regNumberInput.val(data.vatNumber);
                        regNumberInput.closest('.form-group').removeClass('has-error').find('.help-block').html('');
                    }
                    if(empty(address) && !empty(data.address)){
                        addressInput.val(data.address);
                        form.find('#client-legal_country_id').val('').change();
                        if(!addressSame){
                            form.find('#client-office_address').val(data.address);
                            form.find('#client-office_country_id').val('').change();
                        }
                    }
                    $this.removeClass('btn-warning').addClass('btn-success');
                }).always(function (data) {
                    checkRequiredInputs(form);
                    //form.yiiActiveForm('validateAttribute', 'client-reg_number');
                    //alert('YES!!!');
                });
            //alert('selected id = ' + id); 
        });

        form.find('.btn-iban-search').click(function(e) {
            var $this = $(this);
            $this.removeClass('btn-default btn-success btn-danger').addClass('btn-warning');
            var url = $(this).attr('value');
            var ibanInput = $(this).closest('.input-group').find('input');
            var iban = ibanInput.val();
            
            $.get(
                url,
                {iban: iban},
                function (result) {
                    $this.removeClass('btn-warning');
                    if(empty(result)){
                        $this.addClass('btn-danger');
                        //alert('No data!');
                        return false;
                    }
                    $this.addClass('btn-success');
                });
            //alert('selected id = ' + id); 
        });

    });
    
})(window.jQuery);     
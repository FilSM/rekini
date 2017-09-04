<?php
return [
    'appSector' => 'finance',
    'brandLabel' => 'Eventus invoices v.1.0.0',    
    'brandOwner' => 'Eventus SIA',
    'adminEmail' => 'filsm@inbox.lv',
    'supportEmail' => 'noreplay@eventus.lv',
    
    'user.passwordResetTokenExpire' => 3600,
    
    'googleMapsApiKey' => 'AIzaSyBo7DbxQhLDjD2MiJeruS73zhXAZdK0EpA',
    'googleMapsLibraries' => 'places',
    'googleMapsLanguage' => 'LV',
    
    'uploadPath' => dirname(dirname(__DIR__)) . '/uploads/',
    
    'DatePickerPluginOptions' => [
        'format' => 'dd-M-yyyy',
        'todayBtn' => true,
        'todayHighlight' => true,
        'autoclose' => true,
        'weekStart' => 1,
    ],
    
    'PjaxModalOptions' => [
        'id' => 'modal-pjax',
        'enablePushState' => false,
        'enableReplaceState' => false,
        'clientOptions' => [
            'skipOuterContainers' => true,
        ],         
    ],
    
    'EMAILS_DEFAULT_CONTACT' => 
        "
        <div>
            <strong>Eventus SIA</strong> <br/>
            <table style=\"font-family: 'Tahoma'; font-size: 12px; color: #434343\">
                <tr>
                    <td>Phone:</td>
                    <td>+371 67103331</td>
                </tr>
                <tr>
                    <td>Fax:</td>
                    <td>+371 67103338</td>
                </tr>
                <tr>
                    <td style=\"vertical-align: top;\">Adrese:</td>
                    <td>
                        Valdo Business Centre <br/>
                        58a Bauskas str., 5th floor <br/> 
                        LV-1004, Riga, Latvia
                    </td>
                </tr>
                <tr>
                    <td>E-mail:</td>
                    <td><a href='mailto:info@eventus.lv' style='font-weight: bold; color: #434343'>info@eventus.lv</a></td>
                </tr>
                <tr>
                    <td>WEB:</td>
                    <td><a href='http://www.eventus.lv' style='font-weight: bold; color: #434343'>www.eventus.lv</a></td>
                </tr>
            </table>
        </div>
        ",
];

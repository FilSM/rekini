<?php

use yii\widgets\MaskedInput;

use kartik\password\PasswordInput;

/**
 * @var yii\widgets\ActiveForm    $form
 * @var dektrium\user\models\User $user
 */

?>

<?= $form->field($user, 'username')->
        textInput(['maxlength' => 25])->
        hint(isset($user) && ($user->scenario == 'from-client') ? Yii::t('fsmuser', 'Leave it blank and we will generate your username from of your email address.') : null) ?>

<?= $form->field($user, 'email')->widget(MaskedInput::classname(), [
        'clientOptions' => [
            'alias' => 'email',
        ],
    ]); 
?>  

<?= $form->field($user, 'password')->widget(PasswordInput::classname());?>

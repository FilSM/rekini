<?php

?>

<?= $this->render('@backend/views/address/address/_location-gMap', [
    'form' => $form,
    'addressModel' => $addressModel,
]) ?>

<?= $this->render('@backend/views/address/address/_location-fields', [
    'model' => $model,
    'addressModel' => $addressModel,
    'form' => $form,
]) ?>
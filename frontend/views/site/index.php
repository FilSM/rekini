<?php

use yii\helpers\Url;
use linchpinstudios\backstretch\Backstrech;
/* @var $this yii\web\View */

$this->title = Yii::$app->params['brandLabel'];
//\common\assets\BgStretcherAsset::register($this);

?>
<div id='front-page' class="site-index">

<?php
/*
$script = <<< JS
var path = frontendImagesPath;
//  Initialize Backgound Stretcher
jQuery('.site-index').bgStretcher({
    images: [path+'bg-01.jpg'],
    //images: [path+'bg-01.jpg', path+'bg-02.jpg', path+'bg-03.jpg'],
    imageWidth: 1920,
    imageHeight: 1080,
    //slideShow: false
    slideShowSpeed: 'slow',
    nextSlideDelay: 5000
});
JS;
$this->registerJs($script);
 * 
 */

$imgPath = Url::to('@web/frontend/assets/images', true).'/';
echo Backstrech::widget([
    //'clickEvent' => false,
    'images' => [
        //['image' => $imgPath.'bg-01.jpg'],
        ['image' => $imgPath.'bg-02.jpg'],
        //['image' => $imgPath.'bg-03.jpg'],
    ],
    'options' => [
        'duration' => 3000,
        'fade' => 750,
    ],
]);

?>
      
</div>
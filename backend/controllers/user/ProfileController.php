<?php

namespace backend\controllers\user;

use Yii;
use yii\web\NotFoundHttpException;

use dektrium\user\controllers\ProfileController as BaseProfileController;

use common\models\user\FSMUser;

class ProfileController extends BaseProfileController {

    public function actionShow($id) {
        $profile = $this->finder->findProfileById($id);

        if ($profile === null) {
            throw new NotFoundHttpException();
        }
        $isAdmin = FSMUser::getIsPortalAdmin();
        $isOwner = $isAdmin || FSMUser::getIamOwner();
        $userId = Yii::$app->user->getId();
        
        return $this->render('show', [
            'profile' => $profile,
            'isAdmin' => $isAdmin,
            'isOwner' => $isOwner,
            'itIsMyProfile' => $userId == $id,
        ]);
    }

}

<?php

namespace backend\controllers\user;

use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
//use yii\web\GroupUrlRule;

use dektrium\user\controllers\SettingsController as BaseSettingsController;

use common\traits\AjaxValidationTrait;
use common\models\user\FSMUser;
use common\models\mainclass\FSMBaseModel;
use common\models\client\Client;

class SettingsController extends BaseSettingsController {
    
    use AjaxValidationTrait;

    /**
     * Displays page where user can update account settings (username, email or password).
     * @return string|\yii\web\Response
     */
    public function actionAccount() {
        if (!isset($_GET['id'])) {
            $userId = Yii::$app->user->identity->getId();
        } else {
            $userId = $_GET['id'];
        }

        $user = $this->finder->findUserById($userId);
        $arrForCreate = [
            'class' => \common\models\user\FSMSettingsForm::className(),
            'user'  => $user,
        ];
        
        $currentUserId = Yii::$app->user->identity->getId();
        if(FSMUser::getIsPortalAdmin($currentUserId)){
            $arrForCreate['scenario'] = 'admin_update';
        }
                
        /** @var SettingsForm $model */
        $model = Yii::createObject($arrForCreate); 
        
        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxValidation($model);
        }        

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($isPjax) {
                // JSON response is expected in case of successful save
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => true];
            }else{
                Yii::$app->session->setFlash('success', Yii::t('user', 'Your account details have been updated'));
                return $this->refresh();
            }
        }
        
        $model['role'] = Yii::$app->authManager->getArrRolesByUser($userId);
        $profile = $this->finder->findProfileById($userId);
        $roleList = FSMUser::getRoleList();
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
                'profile' => $profile,
                'roleList' => $roleList,
                'isModal' => true,
            ]);
        }else{
            return $this->render('account', [
                'model' => $model,
                'profile' => $profile,
                'roleList' => $roleList,
            ]);
        }
    }
    
    public function actionNetworks() {
        if (!isset($_GET['id'])) {
            $userId = Yii::$app->user->identity->getId();
        } else {
            $userId = $_GET['id'];
        }

        $model = $this->finder->findProfileById($userId);
        $user = $model->user;
        return $this->render('networks', [
            'user' => $user,
            'profile' => $model,
        ]);
    }
    
    /**
     * Shows profile settings form.
     *
     * @return string|Yii\web\Response
     */
    public function actionProfile() {
        if (!isset($_GET['id'])) {
            $userId = Yii::$app->user->identity->getId();
        } else {
            $userId = $_GET['id'];
        }

        $model = $this->finder->findProfileById($userId);

        $this->performAjaxMultipleValidation([
            'FSMProfile' => $model, 
        ]);        

        if (FSMBaseModel::loadMultiple([
                'FSMProfile' => $model, 
            ], Yii::$app->request->post(), '')){
            
            $transaction = Yii::$app->getDb()->beginTransaction(); 
            try {
                
                if($model->save()){
                    $transaction->commit();
                    Yii::$app->getSession()->setFlash('success', Yii::t('user', 'Profile updated successfully'));
                }else{
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! '.$model->errorMessage);
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->refresh();                
            }        
        }else{
            $isAdmin = FSMUser::getIsPortalAdmin();
            $isOwner = $isAdmin || FSMUser::getIamOwner();

            $languageList = \common\models\Language::getEnabledLanguageList();
            return $this->render('profile', [
                'model' => $model,
                'languageList' => $languageList,
                'client' => $model->client,
                'isAdmin' => $isAdmin,
                'isOwner' => $isOwner,
            ]);
        }
   }

    /**
     * Shows email settings form.
     *
     * @return string|Yii\web\Response
     */
    public function actionEmail() {
        if (!isset($_GET['id'])) {
            $userId = Yii::$app->user->identity->getId();
        } else {
            $userId = $_GET['id'];
        }

        $model = $this->finder->findUserById($userId);
        $profile = $this->finder->findProfileById($userId);
        $model->scenario = 'update_email';

        if ($model->load(Yii::$app->getRequest()->post()) && $model->updateEmail()) {
            return $this->refresh();
        }

        return $this->render('email', [
                    'model' => $model,
                    'profile' => $profile,
        ]);
    }

    /**
     * Shows password settings form.
     *
     * @return string|Yii\web\Response
     */
    public function actionPassword() {
        if (!isset($_GET['id'])) {
            $userId = Yii::$app->user->identity->getId();
        } else {
            $userId = $_GET['id'];
        }

        $model = $this->finder->findUser(['id' => $userId])->one();
        $profile = $this->finder->findProfileById($userId);
        $model->scenario = 'update_password';

        if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
            Yii::$app->getSession()->setFlash('settings_saved', Yii::t('user', 'Password has been changed'));
            return $this->refresh();
        }

        return $this->render('password', [
                    'model' => $model,
                    'profile' => $profile,
        ]);
    }

}

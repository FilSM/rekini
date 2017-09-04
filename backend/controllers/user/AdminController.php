<?php

namespace backend\controllers\user;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;

use dektrium\user\controllers\AdminController as BaseAdminController;
use kartik\widgets\AlertBlock;

use common\models\user\FSMUser;
use common\models\user\search\FSMUserSearch;
use common\models\client\Client;

use common\assets\UIAsset;
use common\assets\ButtonDeleteAsset;

class AdminController extends BaseAdminController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge(
            $behaviors, 
            [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                            'matchCallback' => function () {
                                $userId = Yii::$app->user->identity->getId();
                                return FSMUser::getIsPortalAdmin($userId);
                            }
                        ],
                    ]
                ]                
            ]
        );
        return $behaviors;
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex() {
        UIAsset::register(Yii::$app->getView());
        ButtonDeleteAsset::register(Yii::$app->getView());
                
        $searchModel  = Yii::createObject(FSMUserSearch::className());
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        
        $userModel = Yii::createObject(FSMUser::className());
        $dataFilterRoles = $userModel->RoleList; 
        $itIsList = Client::getClientItIsList(); 
        
        $isAdmin = FSMUser::getIsPortalAdmin();
        $isOwner = $isAdmin || FSMUser::getIamOwner();
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'dataFilterRoles' => $dataFilterRoles,
            'itIsList' => $itIsList,
            'isAdmin' => $isAdmin,
            'isOwner' => $isOwner,
        ]);
    }
    
    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionCreate() {
        /** @var User $user */
        $user = Yii::createObject([
            'class'    => FSMUser::className(),
            'scenario' => 'create',
        ]);
        
        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxValidation($user);
        }  
	
        if ($user->load(Yii::$app->request->post()) && $user->create()) {
            if ($isPjax) {
                // JSON response is expected in case of successful save
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => true];
            }else{
                Yii::$app->getSession()->setFlash('success', Yii::t('user', 'User has been created'));
                return $this->redirect(['/user/profile/show', 'id' => $user->id]);
            }            
        }
        
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'user' => $user,
                'isModal' => true,
            ]);
        }else{
            return $this->render('create', [
                'user' => $user
            ]);
        }        
    }
    
    public function actionDeleteSelected($ids) {
        if(empty($ids)){
            return $this->redirect(['index']);
        }
        $idsArr = explode(',', $ids);
        $transaction = Yii::$app->getDb()->beginTransaction(); 
        try {
            $result = true;
            foreach ($idsArr as $id) {
                $model = $this->findModel($id);
                $event = $this->getUserEvent($model);
                $this->trigger(self::EVENT_BEFORE_DELETE, $event);
                if(!$result = $model->delete()){
                    break;
                }
                $this->trigger(self::EVENT_AFTER_DELETE, $event);                
            }
            if($result){
            // delete into DB
            //if(Address::deleteAll(['id' => $idsArr])){
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success', Yii::t('common', '{count, number} {count, plural, =1{entry} other{entries}} remove!.',  ['count' => count($idsArr)]));
            }else{
                $transaction->rollBack();
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
        } finally {
            //return $this->redirect(['index']);
        }        
        $backUrl = \common\models\mainclass\FSMBaseModel::getBackURL('index');
        return $this->redirect($backUrl);
    }
}

<?php

namespace common\traits;

use Yii;
use yii\helpers\Html;
use yii\base\Model;
use yii\web\Response;
use yii\widgets\ActiveForm;

use common\models\mainclass\FSMBaseModel;

trait AjaxValidationTrait {

    /**
     * Performs ajax validation.
     * @param Model $model
     * @throws \yii\base\ExitException
     */
    protected function performAjaxValidation(Model $model) {
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = ActiveForm::validate($model);
            Yii::$app->response->send();
            Yii::$app->end();
        }
    }

    /**
     * Performs ajax validation.
     * @param $model (array) Array of Model 
     * @throws \yii\base\ExitException
     */
    protected function performAjaxMultipleValidation($models) {
        $models = (array) $models;
        if (Yii::$app->request->isAjax && FSMBaseModel::loadMultiple($models, Yii::$app->request->post(), '')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = json_encode(self::validateMultiple($models));
            echo $result;
            Yii::$app->end();
        }
    }

    /**
     * Validates an array of model instances and returns an error message array indexed by the attribute IDs.
     * This is a helper method that simplifies the way of writing AJAX validation code for tabular input.
     *
     * For example, you may use the following code in a controller action to respond
     * to an AJAX validation request:
     *
     * ```php
     * // ... load $models ...
     * if (Yii::$app->request->isAjax) {
     *     Yii::$app->response->format = Response::FORMAT_JSON;
     *     return ActiveForm::validateMultiple($models);
     * }
     * // ... respond to non-AJAX request ...
     * ```
     *
     * @param array $models an array of models to be validated.
     * @param mixed $attributes list of attributes that should be validated.
     * If this parameter is empty, it means any attribute listed in the applicable
     * validation rules should be validated.
     * @return array the error message array indexed by the attribute IDs.
     */
    public static function validateMultiple($models, $attributes = null) {
        $result = [];
        /* @var $model Model */
        foreach ($models as $i => $model) {
            $model->validate($attributes);
            foreach ($model->getErrors() as $attribute => $errors) {
                if (is_numeric($i)) {
                    $result[Html::getInputId($model, "[$i]" . $attribute)] = $errors;
                } else {
                    $result[Html::getInputId($model, $attribute)] = $errors;
                }
            }
        }
        return $result;
    }

}

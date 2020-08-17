<?php

use app\models\Products;
use yii\bootstrap\BaseHtml;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Orders */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="orders-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'dt_add')->textInput() ?>

    <?= $form->field($model, 'status')->listBox($model::getStatusesLables()) ?>

    <?= BaseHtml::activeCheckboxList($model, 'products', (ArrayHelper::map(Products::find()->all(), 'product_id' , 'name'))) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

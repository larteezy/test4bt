<?php

namespace app\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_products".
 *
 * @property int $op_id ID связи товара с заказом
 * @property int $order_id ID товара
 * @property int $product_id ID продукта
 *
 * @property Orders $order
 * @property Products $product
 */
class OrderProducts extends \yii\db\ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'order_products';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['order_id', 'product_id'], 'required'],
			[['order_id', 'product_id'], 'default', 'value' => null],
			[['order_id', 'product_id'], 'integer'],
			[['order_id', 'product_id'], 'unique', 'targetAttribute' => ['order_id', 'product_id']],
			[['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Orders::className(), 'targetAttribute' => ['order_id' => 'order_id']],
			[['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::className(), 'targetAttribute' => ['product_id' => 'product_id']],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function behaviors()
	{
		return [
			'class' => AttributeBehavior::className(),
			'attributes' => [
				ActiveRecord::EVENT_BEFORE_INSERT => 'product',
				ActiveRecord::EVENT_BEFORE_DELETE => 'product',
			],
			'value' => function ($event) {
				$Product = new $this->product;

				switch ($event->name) {
					case ActiveRecord::EVENT_BEFORE_INSERT:
						$Product->count = $Product->count ? --$Product->count : $Product->count;
					break;
					case ActiveRecord::EVENT_BEFORE_DELETE:
						$Product->count++;
					break;
				}

				return $Product;
			},
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'op_id' => 'Op ID',
			'order_id' => 'Order ID',
			'product_id' => 'Product ID',
		];
	}

	/**
	 * Gets query for [[Order]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getOrder()
	{
		return $this->hasOne(Orders::className(), ['order_id' => 'order_id']);
	}

	/**
	 * Gets query for [[Product]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getProduct()
	{
		return $this->hasOne(Products::className(), ['product_id' => 'product_id']);
	}
}

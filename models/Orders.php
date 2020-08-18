<?php

namespace app\models;

use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;

/**
 * This is the model class for table "orders".
 *
 * @property int $order_id ID заказа
 * @property string $dt_add Дата создания заказа
 * @property float $price Сумма товаров заказа
 * @property int $status Статус заказа:
 1 - Заказ создан
2 - Заказ оплачен
3 - Заказ завершен
*
* @property OrderProducts[] $orderProducts
* @property Products[] $products
*/
class Orders extends \yii\db\ActiveRecord
{
	/** @var integer Статус заказа: создан */
	const STATUS_CREATED = 1;

	/** @var integer Статус заказа: оплачен */
	const STATUS_PAID = 2;

	/** @var integer Статус заказа: завершен */
	const STATUS_COMPLETED = 3;

	/** @var array Все существующие статусы заказа и их текстовые значения*/
	const STATUSES_LABLES = [
		self::STATUS_CREATED => 'Created',
		self::STATUS_PAID => 'Paid',
		self::STATUS_COMPLETED => 'Completed',
	];

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'orders';
	}

	/**
	 * Получает все статусы заказа
	 *
	 * @return array
	 */
	public static function getAllStatuses(): array
	{
		return array_keys(self::STATUSES_LABLES);
	}

	/**
	 * Получает массив текстовых значений статусов
	 *
	 * @return array
	 */
	public static function getStatusesLables(): array
	{
		$statusesArray = [];
		foreach (self::STATUSES_LABLES as $statusNumber => $statusLabel) {
			$statusesArray[$statusNumber] = Yii::t('app', $statusLabel);
		}

		return $statusesArray;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['dt_add'], 'safe'],
			// [['price'], 'compare', 'compareValue' => 0, 'operator' => '>='],
			// [['price'], 'number'],
			// [['price'], 'default', 'value' => 0],
			// [['products'], 'required'],
			[['status'], 'default', 'value' => null],
			[['status'], 'in', 'range' => self::getAllStatuses()],
			[['status'], 'integer'],
		];
	}

	public function load($data, $formname = null)
	{
		$result = parent::load($data, $formname);

		if (!$result) {
			return $result;
		}

		$this->updateProductsByIds((array) $data['Orders']['products']);

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
            [
                'class' => SaveRelationsBehavior::className(),
                'relations' => [
                    'products',
                ],
            ],
        ];
	}

	//Использовать транзакции для SaveRelationsBehavior
	public function transactions()
	{
		return [
			self::SCENARIO_DEFAULT => self::OP_ALL,
		];
	}

	public function updateProductsByIds(array $idsArray)
	{
		if (!$idsArray) {
			return false;
		}
		$productsPost = Products::findAll($idsArray);
		$this->products = $productsPost;

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'order_id' => Yii::t('app', 'Order ID'),
			'dt_add' => Yii::t('app', 'Order creation date'),
			'price' => Yii::t('app', 'Products price'),
			'status' => Yii::t('app', 'Order status'),
		];
	}

	/**
	 * Gets query for [[OrderProducts]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getOrderProducts()
	{
		return $this->hasMany(OrderProducts::className(), ['order_id' => 'order_id']);
	}

	/**
	 * Gets query for [[Products]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getProducts()
	{
		return $this->hasMany(Products::className(), ['product_id' => 'product_id'])->viaTable('order_products', ['order_id' => 'order_id']);
	}
}

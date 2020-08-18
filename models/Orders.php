<?php

namespace app\models;

use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

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
	/** @var int Статус заказа: создан */
	const STATUS_CREATED = 1;

	/** @var int Статус заказа: оплачен */
	const STATUS_PAID = 2;

	/** @var int Статус заказа: завершен */
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
			//Если успею дойти до авторизации, то можн добавить изменение этих параметров для админов
			// [['dt_add'], 'safe'],
			// [['price'], 'compare', 'compareValue' => 0, 'operator' => '>='],
			// [['price'], 'number'],
			// [['price'], 'default', 'value' => 0],

			// нормализует значение "products" используя функцию "normalizePhone"
			[['products'], 'filter', 'filter' => ['app\models\Products', 'findAll']],
			[['products'], 'required'],
			[['status'], 'required'],
			[['status'], 'default', 'value' => self::STATUS_CREATED],
			[['status'], 'in', 'range' => self::getAllStatuses()],
			[['status'], 'integer'],
		];
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
			[
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['dt_add'],
                ],
                //Вместо метки времени UNIX используется datetime:
                'value' => new Expression('NOW()'),
			],
			[
				'class' => AttributeBehavior::className(),
				'attributes' => [
					ActiveRecord::EVENT_BEFORE_INSERT => 'price',
					ActiveRecord::EVENT_BEFORE_UPDATE => 'price',
				],
				'value' => function ($event) {
					return array_sum(array_column($this->products, 'price'));
				},
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

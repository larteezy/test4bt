<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "products".
 *
 * @property int $product_id ID товара
 * @property string $name Название товара
 * @property float $price Цена товара
 * @property int $count Кол-во товаров
 *
 * @property OrderProducts[] $orderProducts
 * @property Orders[] $orders
 */
class Products extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price'], 'number'],
            [['price', 'count'], 'compare', 'compareValue' => 0, 'operator' => '>='],
            [['count'], 'default', 'value' => 0],
            [['count'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('app', 'Product ID'),
            'name' => Yii::t('app', 'Name'),
            'price' => Yii::t('app', 'Price'),
            'count' => Yii::t('app', 'Product Quantity'),
        ];
    }

    /**
     * Возвращает доступные для продажи товары
     *
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function getAvailableProducts(array $idsArray = [])
    {
        return Products::find()->where('count > 0')->all();
    }

    /**
     * Gets query for [[OrderProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProducts::className(), ['product_id' => 'product_id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Orders::className(), ['order_id' => 'order_id'])->viaTable('order_products', ['product_id' => 'product_id']);
    }
}

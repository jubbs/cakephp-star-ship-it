<?php

declare(strict_types=1);

namespace StarShipIt\Model\Entity;

use Cake\ORM\Entity;

/**
 * ShippingLog Entity
 *
 * @property int $id
 * @property int $order_number
 * @property string $carrier_name
 * @property string $carrier_service
 * @property \Cake\I18n\FrozenTime $shipment_date
 * @property string $tracking_number
 * @property string $tracking_status
 * @property \Cake\I18n\FrozenTime $last_updated_date
 * @property \Cake\I18n\FrozenTime $created
 */
class ShippingLog extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'order_number' => true,
        'carrier_name' => true,
        'carrier_service' => true,
        'shipment_date' => true,
        'tracking_number' => true,
        'tracking_status' => true,
        'last_updated_date' => true,
        'created' => true,
    ];
}

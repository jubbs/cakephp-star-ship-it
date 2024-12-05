<?php

declare(strict_types=1);

namespace StarShipIt\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Core\Configure;
use DateTime;
use Cake\ORM\TableRegistry;
use Cake\Datasource\Exception\RecordNotFoundException;
use InvalidArgumentException;

/**
 * ShippingLog Model
 *
 * @method \App\Model\Entity\ShippingLog newEmptyEntity()
 * @method \App\Model\Entity\ShippingLog newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ShippingLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ShippingLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\ShippingLog findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ShippingLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ShippingLog[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ShippingLog|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ShippingLog saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ShippingLog[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ShippingLog[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ShippingLog[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ShippingLog[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ShippingLogTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('shipping_log');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('order_number')
            ->requirePresence('order_number', 'create')
            ->notEmptyString('order_number');

        $validator
            ->scalar('carrier_name')
            ->maxLength('carrier_name', 255)
            ->requirePresence('carrier_name', 'create')
            ->notEmptyString('carrier_name');

        $validator
            ->scalar('carrier_service')
            ->maxLength('carrier_service', 255)
            ->requirePresence('carrier_service', 'create')
            ->notEmptyString('carrier_service');

        $validator
            ->dateTime('shipment_date');

        $validator
            ->scalar('tracking_number')
            ->maxLength('tracking_number', 255);

        $validator
            ->scalar('tracking_status')
            ->maxLength('tracking_status', 255)
            ->requirePresence('tracking_status', 'create')
            ->notEmptyString('tracking_status');

        $validator
            ->dateTime('last_updated_date')
            ->requirePresence('last_updated_date', 'create')
            ->notEmptyDateTime('last_updated_date');

        return $validator;
    }


    /** Log the entry and change the status on the Order
     * 
     * $data = [
     * 'shipment_date' => '2021-09-01 00:00:00',
     * 'last_updated_date' => '2021-09-01 00:00:00',
     * 'order_number' => 1,
     * 'carrier_name' => 'Australia Post',
     * 'carrier_service' => '7B05',
     * 'tracking_number' => 'QQQ001737901000931501',
     * 'tracking_status' => 'Dispatched'
     * ];
     */


    public function post(array $data)
    {
        if (isset($data['shipment_date']) && !empty($data['shipment_date'])) $data['shipment_date'] = new DateTime($data['shipment_date']);


        if (isset($data['last_updated_date']) && !empty($data['last_updated_date'])) {
            $data['last_updated_date'] = new DateTime($data['last_updated_date']);
        } else {
            $data['last_updated_date'] = new DateTime();
        }

        $return_data = ['status' => 'success', 'msg' => ''];

        if (Configure::check('StarShipIt.ORDER_TABLE_CLASS')) {
            $order_class = Configure::read('StarShipIt.ORDER_TABLE_CLASS');
            $orders_table = TableRegistry::getTableLocator()->get($order_class);
            try {
                $order_entity = $orders_table->get($data['order_number']);
                $order_entity->{Configure::read('StarShipIt.ORDER_STATUS_FIELD')} = $data['tracking_status'];

                // If any event callbacks are set, call them
                if (Configure::check('StarShipIt.EVENT_PROCESSES')) {
                    $events = Configure::read('StarShipIt.EVENT_PROCESSES');
                    foreach ($events as $event_name => $callback) {
                        if ($data['tracking_status'] == $event_name) {
                            // Collect the properties or the order entity

                            $variables = [];
                            foreach ($callback[2] as $value) {

                                if ($order_entity->has($value)) {
                                    $variables[] = $order_entity->get($value);
                                } else {
                                    $variables[] = $value;
                                }
                            }
                            call_user_func_array([$callback[0], $callback[1]], $variables);
                        }
                    }
                }


                if (!$orders_table->save($order_entity)) {
                    $return_data = ['status' => 'error', 'msg' => $order_entity->getErrors()];
                }
            } catch (RecordNotFoundException | InvalidArgumentException $e) {
                $return_data = ['status' => 'error', 'msg' => $e->getMessage() . " " . json_encode($data)];
            }
        }
        $slog = $this->newEmptyEntity();
        $slog = $this->patchEntity($slog, $data);
        if ($this->save($slog)) {
            return $return_data;
        } else {
            return ['status' => 'error', 'msg' => $slog->getErrors()];
        }
    }
}

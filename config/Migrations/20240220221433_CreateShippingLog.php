<?php

declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateShippingLog extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('shipping_log');
        $table->addColumn('order_number', 'integer', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('carrier_name', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => false,
        ]);
        $table->addColumn('carrier_service', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => false,
        ]);
        $table->addColumn('shipment_date', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('tracking_number', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => false,
        ]);
        $table->addColumn('tracking_status', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => false,
        ]);
        $table->addColumn('last_updated_date', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->create();
    }
}

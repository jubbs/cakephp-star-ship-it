<?php

namespace StarShipIt\Controller\Component;


use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Starshipit\ApiModels\Order;
use Cake\Log\Log;
use stdClass;

class ExportComponent extends Component
{
    public function exportCsv($orders)
    {

        if (empty($orders)) {
            throw new \Exception('provided array was empty');
        }

        $order_data = [];
        foreach ($orders as $order) {
            /** If the user has passed a Starshipit Model then use that  otherwise call the getStarShipItOrder on the entity*/
            if ($order instanceof \Starshipit\ApiModels\Order) {
                $order_data[] = $order;
            } else {
                $order_data[] = $order->getStarShipItOrder();
            }
        }

        // open the output stream
        $file = fopen('php://output', 'w') or die('Cannot open output buffer');

        $columnNames = [
            'Order Number',
            'Date',
            'To Name',
            'Destination Building',
            'Destination Street',
            'Destination Suburb',
            'Destination City',
            'Destination Postcode',
            'Destination State',
            'Destination Country',
            'Destination Email',
            'Destination Phone',
            'Item Name',
            'Item Price',
            'Qty',
            'Company',
        ];


        // // write column names
        if (null !== $columnNames) {
            fputcsv($file, $columnNames);
        }

        // write data array
        foreach ($order_data as $row) {

            $total_value = 0;
            $total_quantity = 0;
            $price = 0;
            foreach ($row->items as $item) {
                $total_value += $item->value;
                $total_quantity += $item->quantity;
                $price = $total_value / $total_quantity;
            }
            $row_array = [];
            $row_array[] = $row->order_number;
            $row_array[] = date('d/m/Y');
            $row_array[] = $row->destination->name;
            $row_array[] = "";
            $row_array[] = $row->destination->street;
            $row_array[] = $row->destination->suburb;
            $row_array[] = $row->destination->city;
            $row_array[] = $row->destination->post_code;
            $row_array[] = $row->destination->state;
            $row_array[] = $row->destination->country;
            $row_array[] = $row->destination->email;
            $row_array[] = $row->destination->phone;
            $row_array[] = "Nutritional Supplements";
            $row_array[] = $price;
            $row_array[] = $total_quantity;
            $row_array[] = $row->destination->company;


            fputcsv($file, $row_array);
        }

        // release buffer
        fclose($file) or die('Cannot close output buffer or file');

        return;
    }
}

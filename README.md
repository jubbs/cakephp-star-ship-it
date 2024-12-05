# StarShipIt plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require jubbs/star-ship-it

```

Create the Database log table
bin/cake migrations migrate -p StarShipIt

if there is shipping_status field in the order model then it will be updated with the tracking status

## Configuration

in app_local.php add the following

```
    'StarShipIt' => [
        'API_KEY' => '<<>>',
        'SUBSCRIPTION_KEY' => '<<>>',
        'ORDER_TABLE_CLASS' => 'Order', // The class name of the order table blank if not used
        'ORDER_STATUS_FIELD' => 'shipping_status', // The field name of the order status blank if not used
        'LOG' => false, // Log the requests and responses to debug.log
    ],
```

### Event Callbacks

Need to create the database table for the event callbacks

```
bin/cake migrations migrate -p StarShipIt
```

To set the location of the callback in you starshipit account go to

https://app2.starshipit.com/settings/SettingsNotifications

Notification Endpoint URL = https://<<YourDomain>>/star-ship-it/event

### Event Callback processes

You can have functions run when an event is received from StarShipIt

Within the config of the app

Pass and array with the key of the event and the value of the class and function to run
and an array of the parameters to pass to the function if the name exists on the order object
then that value will be passed to the function otherwise the text.
In this example the order id and the text 'StarShipIt' will be passed to the emailInvoice function in the Mail class

```
'EVENT_PROCESSES' => [
        'Dispatched' => ['\App\Util\Mail', 'emailInvoice', ['id', 'StarShipIt']],
]

```

### Usage

There are two options to create the StarShipIt order

You can instantiate the StarShipIt/ApiModels/Order class and populate the fields and then call the create method

```

$ssiOrder = new \StarShipIt\ApiModelsOrder();
$ssiOrder->setOrderNumber('1234');
$ssiOrder->setOrderDate('2020-01-01');
$ssiOrder->setOrderStatus('Processing');
$ssiOrder->setOrderTotal('100.00');
..
$result = $this->ShipIt->createShippingOrder($order);

```

Or you can add a getStarShipItOrder function to your order Entity class and pass that object directly to the createShippingOrder function on the ShipItComponent

```

class Order extends Entity
{

    public function getStarShipItOrder()
    {

        $result = new stdClass();

        $result->order_number = $this->id;
        $result->items = [];

        $di = json_decode($this->delivery_address);

        $result->destination = (object)[
            "name" => $di->name ?? ($di->attention ?? null),
            "company" => $di->company ?? null,
            "street" => $di->address_1,
            "suburb" => $di->address_2 ?? null,
            "city" => $di->town ?? ($di->city ?? null),
            "state" => $di->region ?? null,
            "post_code" => $di->post_code ?? null,
            "country" => $di->country ?? null,
            "email" => $di->email ?? null,
            "phone" => $di->phone ?? null,
            "delivery_instructions" => $di->courier_instructions ?? null
        ];
        if ($di->country == "New Zealand") {
            $result->currency = "NZD";
        } elseif ($di->country == "Australia") {
            $result->currency = "AUD";
        }

        foreach ($this->order_products as $line_item) {

            $value = 0;
            if ($line_item->qty) {
                $value = $line_item->price / $line_item->qty;
            }


            if (!$line_item->backordered) { // Dont send backordered items
                $result->items[] = (object)[
                    "item_id" => $line_item->id,
                    "description" => $line_item->product->name,
                    "quantity" => $line_item->qty,
                    "value" => $value
                    //  "tarrif_code" => $line_item->product->tarrif_code ?? "00000000",
                ];
            }
        }

        return $result;
    }
}


$order = $this->Orders->get($id);
$result = $this->ShipIt->createShippingOrder($order);
```

```

```

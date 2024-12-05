<?php

namespace StarShipIt\Controller\Component;

use Cake\Http\Client;
use Cake\Http\Client\Exception\NetworkException;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Starshipit\ApiModels\Order;
use Cake\Log\Log;
use stdClass;
use StarShipIt\Lib\APIWrapper;



class ShipItComponent extends Component
{

    private $apiwrapper;

    public function initialize(array $config): void
    {
        $this->apiwrapper = new APIWrapper();
    }

    /**
     * Create a shipping order
     * @param \Starshipit\ApiModels\Order| $order
     * 
     * You can pass an instance of the Starshipit Order Model or a CakePHP Entity that has a getStarShipItOrder method
     */
    public function createShippingOrder(Order|Entity $order): array
    {
        return $this->apiwrapper->createShippingOrder($order);
    }


    public function testConnection()
    {
        return $this->apiwrapper->testConnection();
    }
}

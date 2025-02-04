<?php

namespace StarShipIt\Lib;

use Cake\Http\Client;
use Cake\Http\Client\Exception\NetworkException;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Starshipit\ApiModels\Order;
use Cake\Log\Log;
use stdClass;

class APIWrapper extends Component
{
    private $headers;
    private $http_timeout = 45;


    public function __construct()
    {
        $this->headers = [
            'Content-Type' => 'application/json',
            'StarShipIT-Api-Key' => Configure::read('StarShipIt.API_KEY'),
            'Ocp-Apim-Subscription-Key' => Configure::read('StarShipIt.SUBSCRIPTION_KEY')
        ];
    }

    /**
     * Create a shipping order
     * @param \Starshipit\ApiModels\Order| $order
     * 
     * You can pass an instance of the Starshipit Order Model or a CakePHP Entity that has a getStarShipItOrder method
     */
    public function createShippingOrder(Order|Entity $order): array
    {
        $order_data = new stdClass();
        /** If the user has passed a Starshipit Model then use that  otherwise call the getStarShipItOrder on the entity*/
        if ($order instanceof \Starshipit\ApiModels\Order) {
            $order_data->order = $order;
        } else {
            $order_data->order = $order->getStarShipItOrder();
        }
        // debug($order_data);
        // exit;
        $http = new Client();

        $json_request = $this->build_json($order_data);

        if (Configure::read('StarShipIt.LOG')) {
            Log::write('debug', 'StarShipIt Request: ' . $json_request);
        }

        try {
            $response = $http->post('https://api.starshipit.com/api/orders', $json_request, [
                'headers' => $this->headers,
                'timeout' => $this->http_timeout
            ]);
        } catch (NetworkException $e) {
            $errors = [[
                'message' => 'Network Error:',
                'details' => "Issues with connection to StarShipIt. " . $e->getMessage()
            ]];
            return ['success' => false, 'errors' => $errors];
        } catch (\Exception $e) {
            $errors = [[
                'message' => 'General Error',
                'details' => "There was an error processing the request to StarShipIt: " . $e->getMessage()
            ]];
            return ['success' => false, 'errors' => $errors];
        }

        $json_response = $response->getJson();

        if (Configure::read('StarShipIt.LOG')) {
            Log::write('debug', 'StarShipIt Response: ' . serialize($json_response));
        }
        return $json_response;
    }

    private function build_json($object)
    {
        $vars = get_object_vars($object);

        $filtered_vars = array_filter($vars, function ($value) {
            return !is_null($value);
        });

        return json_encode($filtered_vars);
    }

    public function testConnection()
    {
        $http = new Client();
        try {
            $response = $http->get('https://api.starshipit.com/api/orders', [], [
                'headers' => $this->headers
            ]);
        } catch (NetworkException $e) {
            $errors = [[
                'message' => 'Network Error:',
                'details' => "Cound not connect to Could not connect to StarShipIt." . $e->getMessage()
            ]];
            return ['success' => false, 'errors' => $errors];
        } catch (\Exception $e) {
            $errors = [[
                'message' => 'General Error',
                'details' => "There was an error processing the request to StarShipIt: " . $e->getMessage()
            ]];
            return ['success' => false, 'errors' => $errors];
        }

        if ($response->getStatusCode() != 200) {
            $errors = [[
                'message' => 'General Error',
                'details' => "There was an error processing the request to StarShipIt: " . $response->getReasonPhrase()
            ]];
            return ['succeeded' => false, 'errors' => $errors];
        } else {
            $json_response = $response->getJson();
        }


        if (Configure::read('StarShipIt.LOG')) {
            Log::write('debug', 'StarShipIt Response: ' . serialize($json_response));
        }
        return $json_response;
    }
    public function getShippingOrder($order_id): array
    {

        $http = new Client();

        if (Configure::read('StarShipIt.LOG')) {
            Log::write('debug', 'StarShipIt Request: https://api.starshipit.com/api/orders?order_number='.$order_id);
        }

        try {
            $response = $http->get('https://api.starshipit.com/api/orders', ['order_number'=>$order_id],  [
                'headers' => $this->headers,
                'timeout' => $this->http_timeout
            ]);

        } catch (NetworkException $e) {
            $errors = [[
                'message' => 'Network Error:',
                'details' => "Issues with connection to StarShipIt. " . $e->getMessage()
            ]];
            return ['success' => false, 'errors' => $errors];
        } catch (\Exception $e) {
            $errors = [[
                'message' => 'General Error',
                'details' => "There was an error processing the request to StarShipIt: " . $e->getMessage()
            ]];
            return ['success' => false, 'errors' => $errors];
        }

        $json_response = $response->getJson();

        if (Configure::read('StarShipIt.LOG')) {
            Log::write('debug', 'StarShipIt Response: ' . serialize($json_response));
        }
        return $json_response;
    }
}

<?php

declare(strict_types=1);

namespace StarShipIt\Controller;

use StarShipIt\Controller\AppController;


class ShippingLogController extends AppController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['event']);
    }

    public function event()
    {
        $this->autoRender = false;
        $this->response = $this->response->withType('application/json');
        $this->response = $this->response->withStringBody(json_encode($this->ShippingLog->post($this->request->getData())));
        return $this->response;
    }
}

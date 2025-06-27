<?php

namespace Omnipay\NetCash\Message;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class CompletePurchaseRequest
 * @package Omnipay\NetCash\Message
 */
class CompletePurchaseRequest extends PurchaseRequest
{
    /**
     * Prepare and get data
     * @return mixed|void
     */
    public function getData()
    {
        return $this->validateRequest($this->httpRequest->request);
    }

    /**
     * Send data and return response
     *
     * @param mixed $data
     *
     * @return \Omnipay\Common\Message\ResponseInterface|\Omnipay\NetCash\Message\CompletePurchaseResponse
     */
    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }

    /**
     * Validate precheck request and interrupt process with just 'OK' if it is passed
     *
     * @param \Symfony\Component\HttpFoundation\ParameterBag $requestData
     *
     * @return array;
     */
    protected function validatePrecheckRequest(ParameterBag $requestData)
    {
        return [];
    }

    /**
     * Validate request and return data, merchant has to echo with just 'OK' at the end
     *
     * @param \Symfony\Component\HttpFoundation\ParameterBag $requestData
     *
     * @return array
     */
    protected function validateRequest(ParameterBag $requestData)
    {
        // Check if request is precheck or final
        $this->validatePrecheckRequest($requestData);

        $data = $requestData->all();
        $data['success'] = false;

        // Check for required request data
        if ($requestData->has('Reference') &&
            $requestData->has('Amount') &&
            $requestData->has('RequestTrace')) {


            if($requestData->has('TransactionAccepted') && $requestData->get('TransactionAccepted') == "true")
                $data['success'] = true;
        }

        return $data;
    }
}
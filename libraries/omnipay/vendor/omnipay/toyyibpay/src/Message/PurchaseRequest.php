<?php

namespace Omnipay\ToyyibPay\Message;

/**
 * toyyibPay Create a Bill Request
 */
class PurchaseRequest extends AbstractRequest
{
    protected $apiEndpoint = 'index.php/api/createBill';

    protected function guardParameters()
    {
        $this->validate(
            'userSecretKey',
            'categoryCode',
            'billName',
            'billDescription',
            'billPriceSetting',
            'billPayorInfo',
            'billAmount',
            'billReturnUrl',
            'billCallbackUrl',
            'billExternalReferenceNo',
            'billTo',
            'billEmail',
            'billPhone'
        );
    }

    protected function createResponse($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    public function getData()
    {
        $this->guardParameters();

        $data = array(
            'userSecretKey' => $this->getUserSecretKey(),
            'categoryCode' => $this->getCategoryCode(),
            'billName' => $this->getBillName(),
            'billDescription' => $this->getBillDescription(),
            'billPriceSetting' => $this->getBillPriceSetting(),
            'billPayorInfo' => $this->getBillPayorInfo(),
            'billAmount' => $this->getBillAmount(),
            'billReturnUrl' => $this->getBillReturnUrl(),
            'billCallbackUrl' => $this->getBillCallbackUrl(),
            'billExternalReferenceNo' => $this->getBillExternalReferenceNo(),
            'billTo' => $this->getBillTo(),
            'billEmail' => $this->getBillEmail(),
            'billPhone' => $this->getBillPhone()
        );

        return $data;
    }

    public function sendData($data)
    {
        $data['apiEndpoint'] = $this->apiEndpoint;
        $httpResponse = $this->sendRequest($data);

        $dataResponse['BillCode'] = $httpResponse[0]['BillCode'];
        $dataResponse['redirectUrl'] = $this->getEndpoint() . $httpResponse[0]['BillCode'];

        return $this->createResponse($dataResponse);
    }
}

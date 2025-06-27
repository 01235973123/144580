<?php

namespace Omnipay\NetCash\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * Class PurchaseRequest
 * @package Omnipay\NetCash\Message
 */
class PurchaseRequest extends AbstractRequest
{

    /**
     * Set the request dataset used by shopping carts
     *
     * @param string $value
     *
     * @return $this
     */
    public function setDataSet($value)
    {
        return $this->setParameter('dataSet', $value);
    }

    /**
     * Get the request dataset
     * @return mixed
     */
    public function getDataSet()
    {
        return $this->getParameter('dataSet');
    }

    /**
     * Set the request email
     *
     * @param string $value
     *
     * @return $this
     */
    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    /**
     * Get the request email
     * @return mixed
     */
    public function getEmail()
    {
        return $this->getParameter('email');
    }

    /**
     * Set the request phone
     *
     * @param string $value
     *
     * @return $this
     */
    public function setPhone($value)
    {
        return $this->setParameter('phone', $value);
    }

    /**
     * Get the request phone
     * @return mixed
     */
    public function getPhone()
    {
        return $this->getParameter('phone');
    }

    /**
     * Set the request budget
     *
     * @param string $value
     *
     * @return $this
     */
    public function setBudget($value)
    {
        return $this->setParameter('dubget', $value);
    }

    /**
     * Get the request budget
     * @return mixed
     */
    public function getBudget()
    {
        return $this->getParameter('dubget');
    }

    /**
     * Set the request Extra field one
     *
     * @param string $value
     *
     * @return $this
     */
    public function setExtraFieldOne($value)
    {
        return $this->setParameter('extraFieldOne', $value);
    }

    /**
     * Get the request Extra field one
     * @return mixed
     */
    public function getExtraFieldOne()
    {
        return $this->getParameter('extraFieldOne');
    }

    /**
     * Set the request Extra field two
     *
     * @param string $value
     *
     * @return $this
     */
    public function setExtraFieldTwo($value)
    {
        return $this->setParameter('extraFieldTwo', $value);
    }

    /**
     * Get the request Extra field two
     * @return mixed
     */
    public function getExtraFieldTwo()
    {
        return $this->getParameter('extraFieldTwo');
    }

    /**
     * Set the request Extra field three
     *
     * @param string $value
     *
     * @return $this
     */
    public function setExtraFieldThree($value)
    {
        return $this->setParameter('extraFieldThree', $value);
    }

    /**
     * Get the request Extra field three
     * @return mixed
     */
    public function getExtraFieldThree()
    {
        return $this->getParameter('extraFieldThree');
    }

    /**
     * Sets the request Pay Now Service Key.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setServiceKey($value)
    {
        return $this->setParameter('serviceKey', $value);
    }

    /**
     * Get the request Pay Now Service Key.
     * @return mixed
     */
    public function getServiceKey()
    {
        return $this->getParameter('serviceKey');
    }

    /**
     * Sets the request Pay Now Vendor Key.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setVendorKey($value)
    {
        return $this->setParameter('vendorKey', $value);
    }

    /**
     * Get the request Pay Now Vendor Key.
     * @return mixed
     */
    public function getVendorKey()
    {
        return $this->getParameter('vendorKey');
    }

    /**
     * Prepare data to send
     * @return array
     */
    public function getData()
    {
        $this->validate('amount', 'serviceKey', 'vendorKey');

        return  [
            'm1'      => $this->getServiceKey(),
            'm2'      => $this->getVendorKey(),
            'p2'      => $this->getTransactionId(),
            'p3'      => $this->getDescription(),
            'p4'      => $this->getAmount(),
            'Budget'  => $this->getBudget(),
            'm4'      => $this->getExtraFieldOne(),
            'm5'      => $this->getExtraFieldTwo(),
            'm6'      => $this->getExtraFieldThree(),
            'm9'      => $this->getEmail(),
            'm11'     => $this->getPhone(),
            'm10'     => $this->getDataSet(),
        ];
    }

    /**
     * Send data and return response instance
     *
     * @param mixed $data
     *
     * @return \Omnipay\Common\Message\ResponseInterface|\Omnipay\NetCash\Message\PurchaseResponse
     */
    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }
}
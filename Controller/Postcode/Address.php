<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Controller\Postcode;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use O2TI\AutoCompleteAddressBr\Model\Api\AddressFinder;
use Magento\Framework\Phrase;

/**
 * Controller Address - Uses AddressFinder Model to complete Address by API.
 */
class Address extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var AddressFinder
     */
    protected $addressFinder;

    /**
     * @param Context       $context
     * @param JsonFactory   $resultJsonFactory
     * @param AddressFinder $addressFinder
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AddressFinder $addressFinder        
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addressFinder = $addressFinder;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $zipcode = $this->getRequest()->getParam("zipcode");

        if (!$zipcode) {
            return $resultJson->setData([
                "success" => false,
                "messages" => __("Zipcode parameter is required.")
            ]);
        }

        $result = $this->addressFinder->getAddressByZipcode($zipcode);

        return $resultJson->setData($result);
    }
}


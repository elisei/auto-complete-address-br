<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use O2TI\AutoCompleteAddressBr\Api\AddressFinderInterface;
use O2TI\AutoCompleteAddressBr\Api\Data\AddressInterface;
use O2TI\AutoCompleteAddressBr\Api\Data\AddressInterfaceFactory;
use O2TI\AutoCompleteAddressBr\Model\Api\AddressFinder as AddressFinderLogic;
use Magento\Framework\Phrase;

/**
 * Implementation of the Address Finder API.
 */
class AddressFinder implements AddressFinderInterface
{
    /**
     * @var AddressFinderLogic
     */
    protected $addressFinderLogic;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var \O2TI\AutoCompleteAddressBr\Helper\Config
     */
    protected $configHelper;

    /**
     * @param AddressFinderLogic $addressFinderLogic
     * @param AddressInterfaceFactory $addressFactory
     * @param \O2TI\AutoCompleteAddressBr\Helper\Config $configHelper
     */
    public function __construct(
        AddressFinderLogic $addressFinderLogic,
        AddressInterfaceFactory $addressFactory,
        \O2TI\AutoCompleteAddressBr\Helper\Config $configHelper
    ) {
        $this->addressFinderLogic = $addressFinderLogic;
        $this->addressFactory = $addressFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function findByZipcode(string $zipcode)
    {
        /** @var AddressInterface $addressDataObject */
        $addressDataObject = $this->addressFactory->create();

        try {
            $result = $this->addressFinderLogic->getAddressByZipcode($zipcode);

            if (!$result["success"]) {
                $errorMessage = $result["messages"] instanceof Phrase ? $result["messages"]->__toString() : (
                    is_string($result["messages"]) ? $result["messages"] : "Zipcode not found or error retrieving address."
                );
                $addressDataObject->setSuccess(false);
                $addressDataObject->setMessages($errorMessage);
                throw new NoSuchEntityException(__($errorMessage));
            }

            $addressDataObject->setSuccess(true);

            $lineToStreet = $this->configHelper->getConfigForRelationShip("street"); // e.g., '1'
            $lineToDistrict = $this->configHelper->getConfigForRelationShip("district"); // e.g., '2'

            if (isset($result["street"][$lineToStreet])) {
                $addressDataObject->setStreetLine1($result["street"][$lineToStreet]);
            }
            if (isset($result["street"][$lineToDistrict])) {
                $addressDataObject->setStreetLine2($result["street"][$lineToDistrict]);
            }

            if (isset($result["city"])) {
                $addressDataObject->setCity($result["city"]);
            }
            if (isset($result["region_id"])) {
                $addressDataObject->setRegionId((int)$result["region_id"]);
            }
            if (isset($result["country_id"])) {
                $addressDataObject->setCountryId($result["country_id"]);
            }
            if (isset($result["provider"])) {
                $addressDataObject->setProvider($result["provider"]);
            }

        } catch (NoSuchEntityException $exc) {
            $addressDataObject->setSuccess(false);
            $addressDataObject->setMessages($exc->getMessage());

            throw new LocalizedException(__($exc->getMessage()));
        } catch (\Exception $exc) {
            $addressDataObject->setSuccess(false);
            $addressDataObject->setMessages($exc->getMessage());
            throw new LocalizedException(__("An error occurred while fetching the address: %1", $exc->getMessage()));
        }

        return $addressDataObject;
    }
}


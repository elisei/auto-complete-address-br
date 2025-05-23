<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use O2TI\AutoCompleteAddressBr\Api\Data\AddressInterface;

/**
 * Address Data Model implementation.
 */
class Address extends AbstractExtensibleObject implements AddressInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSuccess(): bool
    {
        return (bool)$this->_get(self::SUCCESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setSuccess(bool $success)
    {
        return $this->setData(self::SUCCESS, $success);
    }

    /**
     * {@inheritdoc}
     */
    public function getStreetLine1(): ?string
    {
        return $this->_get(self::STREET_LINE_1);
    }

    /**
     * {@inheritdoc}
     */
    public function setStreetLine1(string $streetLine1)
    {
        return $this->setData(self::STREET_LINE_1, $streetLine1);
    }

    /**
     * {@inheritdoc}
     */
    public function getStreetLine2(): ?string
    {
        return $this->_get(self::STREET_LINE_2);
    }

    /**
     * {@inheritdoc}
     */
    public function setStreetLine2(string $streetLine2)
    {
        return $this->setData(self::STREET_LINE_2, $streetLine2);
    }

    /**
     * {@inheritdoc}
     */
    public function getCity(): ?string
    {
        return $this->_get(self::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity(string $city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionId(): ?int
    {
        $regionId = $this->_get(self::REGION_ID);
        return $regionId === null ? null : (int)$regionId;
    }

    /**
     * {@inheritdoc}
     */
    public function setRegionId(int $regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryId(): ?string
    {
        return $this->_get(self::COUNTRY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryId(string $countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider(): ?string
    {
        return $this->_get(self::PROVIDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setProvider(string $provider)
    {
        return $this->setData(self::PROVIDER, $provider);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(): ?string
    {
        return $this->_get(self::MESSAGES);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessages(string $messages)
    {
        return $this->setData(self::MESSAGES, $messages);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(\O2TI\AutoCompleteAddressBr\Api\Data\AddressExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}


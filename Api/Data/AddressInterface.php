<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Address Data Interface
 * @api
 * @since 1.0.0
 */
interface AddressInterface extends ExtensibleDataInterface
{
    const SUCCESS = 'success';
    const STREET_LINE_1 = 'street_line_1';
    const STREET_LINE_2 = 'street_line_2';
    const CITY = 'city';
    const REGION_ID = 'region_id';
    const COUNTRY_ID = 'country_id';
    const PROVIDER = 'provider';
    const MESSAGES = 'messages'; // For error messages

    /**
     * Get success status.
     *
     * @return bool
     */
    public function getSuccess(): bool;

    /**
     * Set success status.
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess(bool $success);

    /**
     * Get street line 1.
     *
     * @return string|null
     */
    public function getStreetLine1(): ?string;

    /**
     * Set street line 1.
     *
     * @param string $streetLine1
     * @return $this
     */
    public function setStreetLine1(string $streetLine1);

    /**
     * Get street line 2.
     *
     * @return string|null
     */
    public function getStreetLine2(): ?string;

    /**
     * Set street line 2.
     *
     * @param string $streetLine2
     * @return $this
     */
    public function setStreetLine2(string $streetLine2);

    /**
     * Get city.
     *
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * Set city.
     *
     * @param string $city
     * @return $this
     */
    public function setCity(string $city);

    /**
     * Get region ID.
     *
     * @return int|null
     */
    public function getRegionId(): ?int;

    /**
     * Set region ID.
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId(int $regionId);

    /**
     * Get country ID.
     *
     * @return string|null
     */
    public function getCountryId(): ?string;

    /**
     * Set country ID.
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId(string $countryId);

    /**
     * Get provider name.
     *
     * @return string|null
     */
    public function getProvider(): ?string;

    /**
     * Set provider name.
     *
     * @param string $provider
     * @return $this
     */
    public function setProvider(string $provider);

    /**
     * Get error messages.
     *
     * @return string|null
     */
    public function getMessages(): ?string;

    /**
     * Set error messages.
     *
     * @param string $messages
     * @return $this
     */
    public function setMessages(string $messages);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \O2TI\AutoCompleteAddressBr\Api\Data\AddressExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \O2TI\AutoCompleteAddressBr\Api\Data\AddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\O2TI\AutoCompleteAddressBr\Api\Data\AddressExtensionInterface $extensionAttributes);
}


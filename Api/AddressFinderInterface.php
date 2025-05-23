<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Api;

/**
 * Interface for retrieving address information based on zipcode.
 * @api
 * @since 1.0.0
 */
interface AddressFinderInterface
{
    /**
     * Find address by zipcode.
     *
     * @param string $zipcode The zipcode to search for.
     * @return \O2TI\AutoCompleteAddressBr\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the zipcode is not found or invalid.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function findByZipcode(string $zipcode);
}


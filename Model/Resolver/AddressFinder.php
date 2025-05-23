<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use O2TI\AutoCompleteAddressBr\Api\AddressFinderInterface;

/**
 * Resolver for the findAddressByZipcode GraphQL query.
 */
class AddressFinder implements ResolverInterface
{
    /**
     * @var AddressFinderInterface
     */
    private $addressFinder;

    /**
     * @param AddressFinderInterface $addressFinder
     */
    public function __construct(
        AddressFinderInterface $addressFinder
    ) {
        $this->addressFinder = $addressFinder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        if (!isset($args["zipcode"]) || empty(trim($args["zipcode"]))) {
            throw new GraphQlInputException(__("\'zipcode\' argument is required."));
        }

        $zipcode = trim($args["zipcode"]);
        $zipcode = preg_replace("/[^0-9]/", "", $zipcode);

        if (strlen($zipcode) !== 8) {
             throw new GraphQlInputException(__("Invalid zipcode format. It must contain 8 digits."));
        }

        $resultData = [
            "success" => false,
            "street_line_1" => null,
            "street_line_2" => null,
            "city" => null,
            "region_id" => null,
            "country_id" => null,
            "provider" => null,
            "messages" => null
        ];

        try {
            $address = $this->addressFinder->findByZipcode($zipcode);

            $resultData = [
                "success" => $address->getSuccess(),
                "street_line_1" => $address->getStreetLine1(),
                "street_line_2" => $address->getStreetLine2(),
                "city" => $address->getCity(),
                "region_id" => $address->getRegionId(),
                "country_id" => $address->getCountryId(),
                "provider" => $address->getProvider(),
                "messages" => $address->getMessages()
            ];

        } catch (NoSuchEntityException $e) {
            $resultData["success"] = false;
            $resultData["messages"] = $e->getMessage();
        } catch (\Exception $e) {
            $resultData["success"] = false;
            $resultData["messages"] = __("An internal error occurred: %1", $e->getMessage())->__toString();
        }

        return $resultData;
    }
}


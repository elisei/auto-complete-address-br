<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use O2TI\AutoCompleteAddressBr\Helper\Config;

/**
 *  CheckoutAutoCompleteAddressBr - Change Components.
 */
class CheckoutAutoCompleteAddressBr
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Change Components in Create Account.
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function changeCreateAccount(array $jsLayout): ?array
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['identification-step'])) {
            // phpcs:ignore
            $createAccountFields = &$jsLayout['components']['checkout']['children']['steps']['children']['identification-step']['children']['identification']['children']['createAccount']['children']['create-account-fieldset']['children'];
            $createAccountFields = $this->changeComponentFields($createAccountFields);
        }

        return $jsLayout;
    }

    /**
     * Change Components in Shipping.
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function changeShippingFields(array $jsLayout): ?array
    {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step'])) {
            // phpcs:ignore
            $shippingFields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
            $shippingFields = $this->changeComponentFields($shippingFields);
        }

        return $jsLayout;
    }

    /**
     * Change Components in Billing.
     *
     * @param array $jsLayout
     *
     * @return array
     */
    public function changeBillingFields(array $jsLayout): array
    {
        // phpcs:ignore
        foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as &$payment) {
            if (isset($payment['children']['form-fields'])) {
                $billingFields = &$payment['children']['form-fields']['children'];
                $billingFields = $this->changeComponentFields($billingFields);
            }
        }
        // phpcs:ignore
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form'])) {
            // phpcs:ignore
            $billingAddressOnPage = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children'];
            $billingAddressOnPage = $this->changeComponentFields($billingAddressOnPage);
        }
        // phpcs:ignore
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['beforeMethods']['children']['billing-address-form'])) {
            // phpcs:ignore
            $billingAddressOnPage = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['beforeMethods']['children']['billing-address-form']['children']['form-fields']['children'];
            $billingAddressOnPage = $this->changeComponentFields($billingAddressOnPage);
        }

        return $jsLayout;
    }

    /**
     * Change Components at Fields.
     *
     * @param array $fields
     *
     * @return array
     */
    public function changeComponentFields(array $fields): array
    {
        foreach ($fields as $key => $data) {
            if ($key === 'postcode') {
                $defaultPosition = (int) $fields[$key]['sortOrder'];
                $fields[$key]['sortOrder'] = $defaultPosition;
                $fields[$key]['component'] = 'O2TI_AutoCompleteAddressBr/js/view/form/element/postcode';
                if ($this->config->useInputMasking()) {
                    // phpcs:ignore
                    $fields[$key]['component'] = 'O2TI_AutoCompleteAddressBr/js/view/form/element/O2TI/InputMasking/postcode';
                }
            }
            if ($this->config->isHideTargetFields()) {
                if ($key === 'street') {
                    foreach ($fields[$key]['children'] as $arrayPosition => $streetLine) {
                        // phpcs:ignore
                        $fields[$key]['children'][$arrayPosition]['component'] = 'O2TI_AutoCompleteAddressBr/js/view/form/element/street-inline';
                    }
                }
                if ($key === 'city') {
                    $fields[$key]['component'] = 'O2TI_AutoCompleteAddressBr/js/view/form/element/city';
                }
                if ($key === 'region_id') {
                    $fields[$key]['component'] = 'O2TI_AutoCompleteAddressBr/js/view/form/element/region';
                }
                if ($key === 'country_id') {
                    $fields[$key]['component'] = 'O2TI_AutoCompleteAddressBr/js/view/form/element/country';
                }
            }

            continue;
        }

        return $fields;
    }

    /**
     * Select Components for Change.
     *
     * @param LayoutProcessor $layoutProcessor
     * @param callable        $proceed
     * @param array           $args
     *
     * @return array
     */
    public function aroundProcess(LayoutProcessor $layoutProcessor, callable $proceed, array $args): array
    {
        $jsLayout = $proceed($args);
        if ($this->config->isEnabled()) {
            $jsLayout = $this->changeCreateAccount($jsLayout);
            $jsLayout = $this->changeShippingFields($jsLayout);
            $jsLayout = $this->changeBillingFields($jsLayout);
            $layoutProcessor = $layoutProcessor;
        }

        return $jsLayout;
    }
}

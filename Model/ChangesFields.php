<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Model;

use O2TI\AutoCompleteAddressBr\Helper\Config;

/**
 *  ChangesFields - Change Compoments for Inputs.
 */
class ChangesFields
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
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
}

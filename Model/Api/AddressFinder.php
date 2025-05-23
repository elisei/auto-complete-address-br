<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Model\Api;

use InvalidArgumentException;
use Laminas\Http\ClientFactory;
use Laminas\Http\Request;
use Magento\Directory\Model\Region;
use Magento\Framework\Serialize\Serializer\Json;
use O2TI\AutoCompleteAddressBr\Helper\Config;
use Magento\Framework\Phrase;

/**
 * Model to find address data based on zipcode using external APIs.
 */
class AddressFinder
{
    /**
     * @var ClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var Region
     */
    protected $region;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ClientFactory $httpClientFactory
     * @param Region        $region
     * @param Json          $json
     * @param Config        $config
     */
    public function __construct(
        ClientFactory $httpClientFactory,
        Region $region,
        Json $json,
        Config $config
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->region = $region;
        $this->json = $json;
        $this->config = $config;
    }

    /**
     * Get formatted address data by zipcode.
     *
     * @param string $zipcode
     * @return array
     */
    public function getAddressByZipcode(string $zipcode): array
    {
        $zipcode = preg_replace('/[^0-9]/', '', $zipcode);
        $result = ['success' => false];

        if (empty($zipcode)) {
             $result['messages'] = __('Zipcode cannot be empty.');
             return $result;
        }

        $apiData = $this->fetchDataFromApi($zipcode);

        if (!$apiData['success']) {
            return $apiData;
        }

        $formattedData = $this->formatApiResponse($apiData);

        return $this->prepareFinalResponse($formattedData);
    }

    /**
     * Fetch data from the configured external API.
     *
     * @param string $zipcode
     * @return array
     */
    protected function fetchDataFromApi(string $zipcode): array
    {
        $client = $this->httpClientFactory->create();
        $api = $this->config->getConfigForDeveloper('api');
        $url = 'https://viacep.com.br/ws/'.$zipcode.'/json/'; // Default

        if ($api === 'republicavirtual') {
            $url = 'http://cep.republicavirtual.com.br/web_cep.php?cep='.$zipcode.'&formato=jsonp';
        }

        $result = ['success' => false];

        try {
            $client->setUri($url);
            $client->setMethod(Request::METHOD_GET);
            $client->setOptions(['timeout' => 5]);
            $responseBody = $client->send()->getBody();

            if ($api === 'republicavirtual' && strpos(trim($responseBody), '(') === 0) {
                 $responseBody = preg_replace('/^[^\(]+\(|\)$/', '', $responseBody);
            }

            $apiResult = $this->json->unserialize($responseBody);

            if ($api === 'viacep' && isset($apiResult['erro']) && $apiResult['erro'] === true) {
                 $result['messages'] = __('Zipcode not found or invalid.');
            } elseif ($api === 'republicavirtual' && isset($apiResult['resultado']) && $apiResult['resultado'] == 0) {
                 $result['messages'] = __('Zipcode not found or invalid.');
            } elseif (empty($apiResult)) {
                 $result['messages'] = __('Empty response from API.');
            } else {
                 $result = $apiResult;
                 $result['success'] = true;
                 $result['provider'] = $api;
            }

        } catch (InvalidArgumentException $exc) {
            $result['messages'] = __('Error processing API response: %1', [$exc->getMessage()]);
        } catch (\Exception $e) {
             $result['messages'] = __('Error connecting to address API: %1', [$e->getMessage()]);
        }

        return $result;
    }

    /**
     * Format the raw API response into a standardized structure.
     *
     * @param array $data Raw data from API fetch, including 'success' and 'provider'.
     * @return array Standardized address data.
     */
    protected function formatApiResponse(array $data): array
    {
        $api = $data['provider'] ?? null;
        $formatted = [
            'success' => $data['success'],
            'street' => '',
            'district' => '',
            'city' => '',
            'uf_code' => '',
            'uf_id' => '',
            'provider' => $api,
            'original_data' => $data
        ];

        if (!$data['success']) {
            $formatted['messages'] = $data['messages'] ?? __('Unknown API error.');
            return $formatted;
        }

        if ($api === 'viacep') {
            $formatted['street'] = $data['logradouro'] ?? '';
            $formatted['district'] = isset($data['bairro']) ? trim($data['bairro']) : '';
            $formatted['city'] = $data['localidade'] ?? '';
            $formatted['uf_code'] = $data['uf'] ?? '';
        } elseif ($api === 'republicavirtual') {
            $tipoLogradouro = $data['tipo_logradouro'] ?? '';
            $logradouro = $data['logradouro'] ?? '';
            $formatted['street'] = trim($tipoLogradouro . ' ' . $logradouro);
            $formatted['district'] = isset($data['bairro']) ? trim($data['bairro']) : '';
            $formatted['city'] = $data['cidade'] ?? '';
            $formatted['uf_code'] = $data['uf'] ?? '';
        }

        if (!empty($formatted['uf_code'])) {
            try {
                $region = $this->region->loadByCode($formatted['uf_code'], 'BR');
                $formatted['uf_id'] = $region->getId() ?: '';
            } catch (\Exception $e) {
                $formatted['uf_id'] = '';
            }
        }

        return $formatted;
    }


     /**
     * Prepare the final response structure based on module configuration.
     *
     * @param array $formattedData Standardized address data from formatApiResponse.
     * @return array Final response array.
     */
    protected function prepareFinalResponse(array $formattedData): array
    {
        if (!$formattedData['success']) {
            if (!isset($formattedData['messages'])) {
                 $formattedData['messages'] = __('Failed to retrieve address data.');
            }
            return [
                'success' => false,
                'messages' => $formattedData['messages']
            ];
        }

        $lineToStreet = $this->config->getConfigForRelationShip('street');
        $lineToDistrict = $this->config->getConfigForRelationShip('district');

        $finalResponse = [
            'success'        => true,
            'street'         => [
                $lineToStreet    => $formattedData['street'],
                $lineToDistrict  => $formattedData['district'],
            ],
            'city'           => $formattedData['city'],
            'country_id'     => 'BR',
            'region_id'      => $formattedData['uf_id'],
            'provider'       => $formattedData['provider'],
        ];

        return $finalResponse;
    }
}


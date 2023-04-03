<?php
/**
 * Copyright © O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * See COPYING.txt for license details.
 */

namespace O2TI\AutoCompleteAddressBr\Controller\Postcode;

use InvalidArgumentException;
use Laminas\Http\ClientFactory;
use Laminas\Http\Request;
use Magento\Directory\Model\Region;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;
use O2TI\AutoCompleteAddressBr\Helper\Config;

/**
 *  Controller Address - Complete Address by API.
 */
class Address extends Action implements HttpGetActionInterface
{
    /**
     * @var ClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

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
     * @param Context       $context
     * @param ClientFactory $httpClientFactory
     * @param JsonFactory   $resultJsonFactory
     * @param Region        $region
     * @param Json          $json
     * @param Config        $config
     */
    public function __construct(
        Context $context,
        ClientFactory $httpClientFactory,
        JsonFactory $resultJsonFactory,
        Region $region,
        Json $json,
        Config $config
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->region = $region;
        $this->json = $json;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $result = ['success'=>false];
        $return = $this->resultJsonFactory->create();

        if ($zipcode = $this->getRequest()->getParam('zipcode')) {
            $zipcode = preg_replace('/[^0-9]/', '', $zipcode);

            $data = $this->getApiServices($zipcode);

            if (!$data['success']) {
                return $return->setData($data);
            }

            $result = $this->getApiFormartReturn($data);
        }

        return $return->setData($result);
    }

    /**
     * Get API.
     *
     * @param string $zipcode
     *
     * @return array
     */
    public function getApiServices(string $zipcode): array
    {
        $client = $this->httpClientFactory->create();
        $api = $this->config->getConfigForDeveloper('api');
        $url = 'http://endereco.ecorreios.com.br/app/enderecoCep.php?cep='.$zipcode;

        if ($api === 'ecorreios') {
            $url = 'http://endereco.ecorreios.com.br/app/enderecoCep.php?cep='.$zipcode;
        } elseif ($api === 'viacep') {
            $url = 'https://viacep.com.br/ws/'.$zipcode.'/json/';
        } elseif ($api === 'republicavirtual') {
            $url = 'http://cep.republicavirtual.com.br/web_cep.php?cep='.$zipcode.'&formato=jsonp';
        }

        $result = ['success' => false];

        try {
            $client->setUri($url);
            $client->setMethod(Request::METHOD_GET);
            $responseBody = $client->send()->getBody();
            $result = $this->json->unserialize($responseBody);
            $result['success'] = true;
        } catch (InvalidArgumentException $exc) {
            $result['messages'] = $exc->getMessage();
        }

        return $result;
    }

    /**
     * Get Format Return API.
     *
     * @param array $data
     *
     * @return array
     */
    public function getApiFormartReturn(array $data): array
    {
        $api = $this->config->getConfigForDeveloper('api');

        if (isset($data['uf'])) {
            $region = $this->region->loadByCode($data['uf'], 'BR');
            $regionId = $region->getId();
        }

        if ($api === 'ecorreios') {
            $data = $this->getFormatECorreios($data);
        } elseif ($api === 'viacep') {
            $data = $this->getFormatViaCep($data);
        } elseif ($api === 'republicavirtual') {
            $data = $this->getFormatRepublicaVirtual($data);
        }

        $apiData = [
            'success'   => $data['success'],
            'street'    => $data['street'],
            'district'  => $data['district'],
            'city'      => $data['city'],
            'uf'        => isset($regionId) ? $regionId : '',
            'provider'  => $this->config->getConfigForDeveloper('api'),
        ];

        $result = $this->getRelationShipReturn($apiData);

        return $result;
    }

    /**
     * Get Format Return API ECorreios.
     *
     * @param array $data
     *
     * @return array
     */
    public function getFormatECorreios(array $data): array
    {
        $data['street'] = isset($data['logradouro']) ? $data['logradouro'] : '';
        $data['district'] = isset($data['bairro']) ? trim($data['bairro']) : '';
        $data['city'] = isset($data['cidade']) ? $data['cidade'] : '';

        return $data;
    }

    /**
     * Get Format Return API ViaCep.
     *
     * @param array $data
     *
     * @return array
     */
    public function getFormatViaCep(array $data): array
    {
        $data['street'] = isset($data['logradouro']) ? $data['logradouro'] : '';
        $data['district'] = isset($data['bairro']) ? trim($data['bairro']) : '';
        $data['city'] = isset($data['localidade']) ? $data['localidade'] : '';

        return $data;
    }

    /**
     * Get Format Return API Republica Virtual.
     *
     * @param array $data
     *
     * @return array
     */
    public function getFormatRepublicaVirtual(array $data): array
    {
        $data['street'] = isset($data['logradouro']) ? $data['logradouro'] : '';
        $data['district'] = isset($data['bairro']) ? trim($data['bairro']) : '';
        $data['city'] = isset($data['cidade']) ? $data['cidade'] : '';

        return $data;
    }

    /**
     * Get Return Formated.
     *
     * @param array $apiData
     *
     * @return array
     */
    public function getRelationShipReturn(array $apiData): array
    {
        $lineToStreet = $this->config->getConfigForRelationShip('street');
        $lineToDistrict = $this->config->getConfigForRelationShip('district');

        $data = [
            'success'        => $apiData['success'],
            'street'         => [
                $lineToStreet    => $apiData['street'],
                $lineToDistrict  => $apiData['district'],
            ],
            'city'           => $apiData['city'],
            'country_id'     => 'BR',
            'region_id'      => $apiData['uf'],
            'provider'       => $apiData['provider'],
        ];

        return $data;
    }
}

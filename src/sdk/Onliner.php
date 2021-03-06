<?php

namespace ozerich\shop\plugins\onliner\sdk;

use ozerich\shop\plugins\onliner\sdk\models\Manufacture;
use ozerich\shop\plugins\onliner\sdk\models\Product;
use ozerich\shop\plugins\onliner\sdk\models\Section;

class Onliner
{
    const HOST = 'https://b2bapi.onliner.by';

    private $clientId;

    private $clientSecret;

    private $accessToken;

    private $accessTokenExpiresAt;

    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct($clientId, $clientSecret)
    {
        $this->clientId = $clientId;

        $this->clientSecret = $clientSecret;

        $this->httpClient = new HttpClient(self::HOST);
    }

    private function auth()
    {
        $response = $this->httpClient->postRequest('/oauth/token', [
            'grant_type' => 'client_credentials'
        ], null, [$this->clientId, $this->clientSecret]);

        $this->accessToken = $response['access_token'];
        $this->accessTokenExpiresAt = time() + $response['expires_in'] - 1;
    }

    private function checkAuth()
    {
        if (!$this->accessToken || $this->accessTokenExpiresAt <= time()) {
            $this->auth();
        }
    }

    private function getRequest($url)
    {
        return $this->httpClient->getRequest($url, 'Bearer ' . $this->accessToken);
    }

    /**
     * @return Section[]
     */
    public function sections()
    {
        $this->checkAuth();

        $result = [];

        $response = $this->getRequest('/sections');
        foreach ($response as $id => $name) {
            $item = new Section();
            $item->setId($id);
            $item->setName($name);
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param $sectionId
     * @return Manufacture[]
     */
    public function manufactures($sectionId)
    {
        $this->checkAuth();

        $result = [];

        $response = $this->getRequest('/sections/' . $sectionId . '/manufacturers');
        foreach ($response as $id => $name) {
            $item = new Manufacture();
            $item->setId($id);
            $item->setName($name);
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param $sectionId
     * @param $manufactureId
     * @return Manufacture[]
     */
    public function products($sectionId, $manufactureId)
    {
        $this->checkAuth();

        $result = [];

        $response = $this->getRequest('/sections/' . $sectionId . '/manufacturers/' . $manufactureId . '/products');
        foreach ($response as $id => $name) {
            $item = new Product();
            $item->setId($id);
            $item->setName($name);
            $result[] = $item;
        }

        return $result;
    }
}
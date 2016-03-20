<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WegDeService
 * @package AppBundle\Service
 */
class WegDeService
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @var string $airportApiUserKey
     */
    protected $airportApiUserKey;

    /**
     * @var string
     */
    protected $wegDeApiKey;

    public function __construct(EntityManager $entityManager, Container $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->airportApiUserKey = $this->container->getParameter('airport_api_user_key');
        $this->wegDeApiKey = $this->container->getParameter('weg_de_apikey');
    }

    /**
     * @param array $params
     */
    public function getTravel(array $params = array())
    {
        $buzz = $this->container->get('buzz');
        $url = $this->getAirportBaseUrl();
        $buzz->get($url);
    }

    /**
     * @param $lat
     * @param $lon
     * @param $country
     * @return array|\Buzz\Message\MessageInterface|mixed
     */
    public function getTravelByLocation($lat, $lon, $country)
    {
        //Request the nearest airports
        $airpotUrl = $this->getAirportBaseUrl() . "/airport/nearest/" . $lat . "/" . $lon;
        $params = ['user_key' => $this->airportApiUserKey, 'maxAirports' => 10];
        $headers = ['Accept' => 'application/json'];

        $response = array();
        $response = json_decode($this->request($airpotUrl, $params, $headers)->getContent(), true);

        $airportCodes = array();

        if (isset($response['success']) && $response['success']) {
            $airports = $response['airports'];

            foreach ($airports as $airport) {
                $airportCodes[] = $airport['code'];
            }
        }

        //Request packages for given location
        $travelBaseUrl = $this->getTravelBaseUrl();
        $params = [
            'channel' => 'PACKAGE',
            'country' => $this->convertCountryName($country),
            'departureAirport' => $this->prepareAirportCodes($airportCodes),
            'apikey' => $this->wegDeApiKey];

        $travelUrl = $this->addParamsToUrl($travelBaseUrl, $params);

        $response = $this->request($travelUrl);

        return $response;
    }

    /**
     * @param $country
     * @return string
     */
    public function convertCountryName($country)
    {
        return $this->container->get('smarttvguide.service.country')->nameToCode($country);
    }

    public function prepareAirportCodes($airportCodes)
    {
        $codesAsString = '';

        foreach($airportCodes as $airportCode)
        {
            $codesAsString .= $airportCode  . ',';
        }

        return substr($codesAsString, 0, -1);
    }

    public function getTravelBaseUrl()
    {
        return "http://api.7hack.de/weg.de/v1/products";
    }

    /**
     * @return mixed
     */
    public function getAirportBaseUrl()
    {
        return $this->container->getParameter('airport_api_base_url');
    }

    /**
     * @param $url
     * @param array $params
     * @return string
     */
    public function addParamsToUrl($url, array $params = array())
    {
        $i = 0;
        foreach ($params as $param => $value)
        {
            if($param && $value)
            {
                if($i == 0)
                {
                    $url .= '?' . $param . '=' . $value;
                }
                else{
                    $url .= '&' . $param . '=' . $value;
                }
            }
            $i++;
        }
        return $url;
    }

    public function request($url, array $params = array(), array $headers = array())
    {
        $buzz = $this->container->get('buzz');
        $preparedUrl = $this->addParamsToUrl($url, $params);
        $response = $buzz->get($preparedUrl, $headers);

        return $response;
    }

}
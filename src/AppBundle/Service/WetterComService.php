<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WetterComService
 * @package AppBundle\Service
 */
class WetterComService
{
    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var Container $container
     */
    protected $container;

    public function __construct(EntityManager $entityManager, Container $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->username = $this->container->getParameter('wetter_com_username');
        $this->password = $this->container->getParameter('wetter_com_password');
        $this->baseUrl = $this->container->getParameter('wetter_com_base_url');
    }

    public function getWeather($search, array  $filter = array())
    {
        $cityCode = $this->getCityCode($search);

        if($cityCode)
        {
            $response = $this->getWeatherByCityCode($cityCode);
            return $response;
        }
        return false;
    }

    public function getWeatherByCityCode($cityCode)
    {
        $url = $this->baseUrl . "/forecast/weather/city/". $cityCode . "/user/" . $this->username . "/cs/" . $this->generateCheckSum([$cityCode]);
        $buzz = $this->container->get('buzz');

        $response = $buzz->get($url);

        $content = json_decode($response->getContent(), true);

        return $content;
    }

    public function getCityCode($search)
    {
        $preparedSearch = strtolower(urlencode($search));

        $url = $this->baseUrl . "/location/index/search/". $preparedSearch . "/user/" . $this->username . "/cs/" . $this->generateCheckSum([$preparedSearch]);
        $buzz = $this->container->get('buzz');

        $response = $buzz->get($url);

        $cityCode = '';

        $content = json_decode($response->getContent(), true);

        if(
            isset($content['search']) &&
            isset($content['search']['result']) &&
            isset($content['search']['result'][0])
            )
        {
            $cityCode = isset($content['search']['result'][0]['city_code']) ? $content['search']['result'][0]['city_code'] : false;
        }

        return $cityCode;
    }

    /**
     * @param array $additionals
     * @return string
     */
    public function generateCheckSum(array $additionals = array())
    {
        $checkumString = $this->username . $this->password;

        foreach ($additionals as $additional)
        {
            $checkumString .= $additional;
        }

        return md5($checkumString);
    }

}
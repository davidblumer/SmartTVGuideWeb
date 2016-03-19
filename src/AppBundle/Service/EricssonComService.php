<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use phpDocumentor\Reflection\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EricssonComService
{
    /** @var EntityManager $entityManager */
    protected $entityManager;

    /** @var $container */
    protected $container;

    public function __construct($entityManager, $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    public function getCurrentProgram($show)
    {
        $output = [];
        $searchKeys = ['searchableTitles', 'searchableTextItems'];
        $key = $this->container->getParameter('ericsson_api_key');
        $url = $this->container->getParameter('ericsson_api_url');

        $show = str_replace('_', '%20', $show);

        $datetime = new \DateTime();
        $hour = $datetime->format('H');
        $startTime = $datetime->format('Y-m-d\T'.($hour-2).':i:s\Z');
        $endTime = $datetime->format('Y-m-d\T'.($hour).':i:s\Z');

        $filters = 'filter={"criteria":[{"term":"publishedStartDateTime","operator":"atLeast","value":"'.$startTime.'"},{"term":"publishedStartDateTime","operator":"atMost","value":"'.$endTime.'"},{"term":"sourceName","operator":"in","values":["'.$show.'"]}],"operator":"and"}';

        $ch = curl_init($url . '?numberOfResults=1&' . $filters . '&api_key=' . $key);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $responseArray = json_decode($response, true);

        if(empty($responseArray)) {
            throw new Exception('Failed');
        }

        foreach($searchKeys as $searchKey) {
            $output = array_merge($output, $this->getData($responseArray, $searchKey));
        }

        return array_merge(array_unique($output), []);
    }

    public function getData($inputArray, $type)
    {
        $return = [];
        if($type == 'searchableTitles') {
            $contentType = $this->translateTitleKeys();
        }
        elseif($type == 'searchableTextItems') {
            $contentType = $this->translateTextKeys();
        }
        foreach($contentType as $searchKey) {
            foreach ($inputArray[0][$type] as $key => $value) {
                foreach ($value as $innerKey => $innerValue) {
                    if ($innerKey == 'type' && $innerValue == $searchKey) {
                        if (array_key_exists('DE', $value['value'])) {
                            array_push($return, $value['value']['DE']);
                        }
                    }
                }
            }
        }
        return $return;
    }

    public function translateTitleKeys()
    {
        $translations = [
            'main',
            'episodeTitle',
            'seriesTitle'
        ];
        return $translations;
    }

    public function translateTextKeys()
    {
        $translations = [
            'long'
        ];
        return $translations;
    }
}
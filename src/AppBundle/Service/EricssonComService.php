<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use phpDocumentor\Reflection\Exception;

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
        $searchKeys = ['searchableTitles', 'searchableTextItems', 'contributions'];
        $key = $this->container->getParameter('ericsson_api_key');
        $url = $this->container->getParameter('ericsson_api_url');

        $show = str_replace('_', '%20', $show);

        $startDatetime = new \DateTime('-2Hours');
        $endDatetime = new \DateTime();
        $startTime = $startDatetime->format('Y-m-d\TH:i:s\Z');
        $endTime = $endDatetime->format('Y-m-d\TH:i:s\Z');

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
        if($type == 'contributions') {
            $contentType = $this->getContKeys();
            return $this->filterName($contentType, $inputArray, $type);
        }
        elseif($type == 'searchableTitles') {
            $contentType = $this->getTitleKeys();
            return $this->filterType($contentType, $inputArray, $type);
        }
        elseif($type == 'searchableTextItems') {
            $contentType = $this->getTextKeys();
            return $this->filterType($contentType, $inputArray, $type);
        }

    }

    public function filterName($contentType, $inputArray, $type)
    {
        $return = [];
        foreach($contentType as $searchKey) {
            foreach ($inputArray[0][$type] as $kek) {
                if(key_exists($searchKey, $kek)) {
                    array_push($return, $kek[$searchKey]['DE']['givenName'].' '.$kek[$searchKey]['DE']['lastName']);
                }
            }
        }
        return $return;
    }

    public function filterType($contentType, $inputArray, $type)
    {
        $return = [];
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

    public function getTitleKeys()
    {
        $translations = [
            'main',
            'episodeTitle',
            'seriesTitle'
        ];

        return $translations;
    }

    public function getTextKeys()
    {
        $translations = [
            'long'
        ];

        return $translations;
    }

    public function getContKeys()
    {
        $translations = [
            'contributorNames'
        ];

        return $translations;
    }
}
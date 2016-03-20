<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use phpDocumentor\Reflection\Exception;

class TwitterService
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

    public function getTweets($actors)
    {
        $buzz = $this->container->get('buzz');

        foreach($actors as $actor) {
            $parameters = 'q='.str_replace(' ', '%20', $actor);
        }

        $headers =  ['Authorization' => 'OAuth oauth_consumer_key="2PA8v8O5ryUG996R7lonVbSqR", oauth_nonce="781930a1a0ea96e5c3a6e97f2d7349a9", oauth_signature="hywFHB9Nwy5sSmjBLPv0IRdpN0w%3D", oauth_signature_method="HMAC-SHA1", oauth_timestamp="1458440179", oauth_token="128516180-SoJUHuAkceMNoVLeRPhvNdV20G3xrsoaleBHHlnJ", oauth_version="1.0"'];

        $response = $buzz->get('https://api.twitter.com/1.1/users/search.json?'.$parameters, $headers)->getContent();

        dump($response);
        die();

        return $response;
    }
}
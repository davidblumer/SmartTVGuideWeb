<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use phpDocumentor\Reflection\Exception;
use Symfony\Component\DependencyInjection\Container;

class TwitterService
{
    /** @var EntityManager $entityManager */
    protected $entityManager;

    /** @var Container $container */
    protected $container;

    public function __construct($entityManager, $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    public function getTweets($actors)
    {
        $twitter = $this->container->get('endroid.twitter');

        $tweets = array();

        foreach($actors as $actor) {
            $actorFullName = $actor['givenName'] . ' ' . $actor['lastName'];
            $response = $twitter->query('users/search', 'GET', 'json', array('q' => $actorFullName, 'count' => 1));
            $actorTwitterSite = json_decode($response->getContent(), true);

            if(isset($actorTwitterSite[0]) && isset($actorTwitterSite[0]['id']))
            {
                $actorId = $actorTwitterSite[0]['id'];

                $response = $twitter->query('statuses/user_timeline', 'GET', 'json', array('user_id' => $actorId, 'count' => 3));
                $actorTweets = json_decode($response->getContent(), true);
                $tweets[][$actorFullName] = $actorTweets;
            }
        }

        return $tweets;
    }
}
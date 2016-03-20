<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class TwitterController extends FOSRestController
{
    /**
     *
     *
     * @Rest\Get(path="/api/tweets/{search}")
     *
     * @ApiDoc(
     *  section="Show",
     *  description="Returns current tweets",
     *  parameters={
     *      {"name"="search", "dataType"="string", "required"=true, "description"="A search term for the required city. (e.q.: postal code or name)"}
     *  }
     * )
     */
    public function indexAction(Request $request, $search)
    {
        $ericssonService = $this->get('smarttvguide.service.ericssonservice');

        $actors = $ericssonService->getCurrentActors($search);

        $service = $this->get('smarttvguide.service.twitterservice');

        $data = $service->getTweets($actors);

        $response = new Response(json_encode(array()), 404, array('Content-Type' => 'application/json'));

        if($data)
        {
            $response = new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
        }

        return $response;
    }
}

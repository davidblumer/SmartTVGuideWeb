<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class WetterComController extends FOSRestController
{
    /**
     *
     *
     * @Rest\Get(path="/api/weather/{search}")
     *
     * @ApiDoc(
     *  section="Wetter",
     *  description="Returns weather data from weather.com by a string with name of city or postalcode",
     *  parameters={
     *      {"name"="search", "dataType"="string", "required"=true, "description"="A search term for the required city. (e.q.: postal code or name)"}
     *  }
     * )
     */
    public function weatherSearchAction(Request $request, $search)
    {
        $weatherService = $this->get('smarttvguide.service.wettercomservice');

        $weather = $weatherService->getWeather($search);

        $response = new Response('{}', 404, array('Content-Type' => 'application/json'));

        if($weather)
        {
            $response = new Response(json_encode($weather), 200, array('Content-Type' => 'application/json'));
        }

        return $response;
    }

}

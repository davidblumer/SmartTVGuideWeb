<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class WegDeController
 * @package AppBundle\Controller
 * @author Felix Baeder <felix.baeder@socialbit.de>
 */
class WegDeController extends FOSRestController
{

    /**
     *
     * @Rest\Get(path="/api/travels")
     *
     */
    public function travelAction(Request $request)
    {
        $travelService = $this->get('smarttvguide.service.wegdeservice');

        $params = $request->query->all();

        $travels = $travelService->getTravels($params);

        $response = new Response('{}', 404, array('Content-Type' => 'application/json'));

        if($travels)
        {
            $response = new Response($travels->getContent(), 200, array('Content-Type' => 'application/json'));
        }


        return $response;
    }

    /**
     *
     * @Rest\Post(path="/api/travel/match")
     *
     * @ApiDoc(
     *  section="Wetter",
     *  description="Returns weather data from weather.com by a string with name of city or postalcode",
     *  parameters={
     *      {"name"="city", "dataType"="string", "required"=true, "description"="The name of city"},
     *      {"name"="country", "dataType"="string", "required"=true, "description"="The name of country"},
     *      {"name"="lat", "dataType"="string", "required"=true, "description"="Latitude of current position to get the nearest airport(s)"},
     *      {"name"="lon", "dataType"="string", "required"=true, "description"="Longitude of current position to get the nearest airport(s)"}
     *  }
     * )
     */
    public function findTravelByGeolocationAction(Request $request)
    {
        $travelService = $this->get('smarttvguide.service.wegdeservice');

        $data = json_decode($request->getContent(), true);

        $response = new Response('{}', 404, array('Content-Type' => 'application/json'));

        $lat = $this->validateParam($data, 'lat');
        $lon = $this->validateParam($data, 'lon');
        $country = $this->validateParam($data, 'country');

        if($lat && $lon && $country)
        {
            $travels = $travelService->getTravelByLocation($lat, $lon, $country);

            if($travels)
            {
                $response = new Response($travels->getContent(), 200, array('Content-Type' => 'application/json'));
            }
        }

        return $response;
    }

    public function validateParam($array, $paraName)
    {
        return isset($array[$paraName]) ? $array[$paraName] : false;
    }
}
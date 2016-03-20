<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class EricssonComController extends FOSRestController
{
    /**
     *
     *
     * @Rest\Get(path="/api/show/{search}")
     *
     * @ApiDoc(
     *  section="Show",
     *  description="Returns current show",
     *  parameters={
     *      {"name"="search", "dataType"="string", "required"=true, "description"="A search term for the required city. (e.q.: postal code or name)"}
     *  }
     * )
     */
    public function indexAction(Request $request, $search)
    {
        $service = $this->get('smarttvguide.service.ericssonservice');

        $data = $service->getCurrentProgram($search);

        return new Response(json_encode($data), 200, array('Content-Type' => 'application/json'));
    }
}

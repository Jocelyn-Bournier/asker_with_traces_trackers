<?php

namespace SimpleIT\ClaireExerciseBundle\Controller\Api;

use SimpleIT\ClaireExerciseBundle\Service\TraceService;
use SimpleIT\ClaireExerciseBundle\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

/*
class TraceController extends AbstractController
{
    /**
     * @param Request $request
     * @return JsonResponse
     * 
     **//*
    public function saveTraceAction(Request $request): JsonResponse
    {
        return new JsonResponse(['message' => 'Test OK'], 200);
    }
}
*/

class TraceController extends AbstractController
{

    /**
     * Save a trace
     * @param Request $request
     * @return JsonResponse
    **/

    public function saveTraceAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['user_id']) || !isset($data['type']) || !isset($data['dd']) || !isset($data['df']) || !isset($data['content']) || !isset($data['context'])) {
            return new JsonResponse('Missing parameters', 400);
        }

        $user_id = $data['user_id'];
        $type = $data['type'];
        $dd = new \DateTime($data['dd']);
        $df = new \DateTime($data['df']);
        $content = $data['content'];
        $context = $data['context'];

        $trace_serv = new TraceService($this->getDoctrine()->getManager());

        $trace = $trace_serv->saveTrace($user_id, $type, $dd, $df, $content, $context);

        return new JsonResponse($trace->getInteractionId(), 201);
    }
    
}

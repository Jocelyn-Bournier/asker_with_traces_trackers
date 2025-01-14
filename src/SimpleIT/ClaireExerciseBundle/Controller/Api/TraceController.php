<?php

namespace SimpleIT\ClaireExerciseBundle\Controller\Api;

use SimpleIT\ClaireExerciseBundle\Service\TraceService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

#[Route('/api/trace')]
class TraceController extends AbstractController
{
    private TraceService $traceService;

    public function __construct(TraceService $traceService)
    {
        $this->traceService = $traceService;
    }

    /**
     * Get the current user's id
     *
     * @throws InsufficientAuthenticationException
     * @return int
     */
    protected function getUserId()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->get('security.token_storage')->getToken()->getUser()->getId();
        } else {
            throw new InsufficientAuthenticationException();
        }
    }

    #[Route('/save', methods: ['POST'])]
    /**
     * Save a trace
     * @param Request $request
     * @return JsonResponse
    **/
    public function saveTrace(Request $request): JsonResponse
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

        $trace = $this->traceService->saveTrace($user_id, $type, $dd, $df, $content, $context);

        return new JsonResponse($trace->getInteractionId(), 201);
    }
    
}
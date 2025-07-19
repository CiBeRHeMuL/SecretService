<?php

namespace App\Presentation\Api\Controller;

use App\Presentation\Api\OpenApi\Attribute as LOA;
use App\Presentation\Api\Response\Model\Common\SuccessResponse;
use App\Presentation\Api\Response\Response;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    #[Route('/health', name: 'health', methods: ['GET'])]
    #[OA\Tag('health')]
    #[LOA\SuccessResponse('boolean')]
    public function health(): JsonResponse
    {
        return Response::success(new SuccessResponse(true));
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

final class MeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $u = $this->getUser();
        if (!$u) {
            return $this->json(['authenticated' => false]);
        }

        return $this->json([
            'authenticated' => true,
            'username' => $u->getUserIdentifier(),
            'roles' => $u->getRoles(),
        ]);
    }
}

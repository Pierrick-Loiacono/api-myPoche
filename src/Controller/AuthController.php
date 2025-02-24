<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AuthController extends AbstractController
{
    #[Route('/api/auth/me', name: 'app_auth_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(UserInterface $user): JsonResponse
    {
        // Si aucun utilisateur n'est connecté, renvoyer une réponse d'erreur
        if (!$user || !$user instanceof Utilisateurs) {
            return null;
        }

        // Renvoyer les informations de l'utilisateur
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/api/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $response = new JsonResponse(['message' => 'Logout successful']);
        $response->headers->clearCookie('auth_token');
        return $response;
    }
}

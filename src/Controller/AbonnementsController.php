<?php

namespace App\Controller;

use App\Repository\AbonnementsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class AbonnementsController extends AbstractController
{
    #[Route('/api/abonnements', name: 'app_abonnements', methods: ['GET'])]
    public function abonnements(AbonnementsRepository $aborepo, SerializerInterface $serializer): JsonResponse
    {
        $abonnements = $aborepo->findBy([], ['label' => 'DESC']);
        $json = $serializer->serialize($abonnements, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}

<?php

namespace App\Controller;

use App\Repository\TransactionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TransactionsController extends AbstractController
{
    #[Route('/api/transactions', name: 'app_transactions', methods: ['GET'])]
    public function transactions(TransactionsRepository $trepo, SerializerInterface $serializer): JsonResponse
    {
        $transactions = $trepo->findBy([], ['date' => 'DESC']);
        $json = $serializer->serialize($transactions, 'json', ['groups' => 'transactions:read']);

        return new JsonResponse($json, 200, [], true);
    }
}

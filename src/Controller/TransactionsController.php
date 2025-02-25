<?php

namespace App\Controller;

use App\Repository\TransactionsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TransactionsController extends AbstractController
{
    #[Route('/api/transactions', name: 'app_transactions', methods: ['GET'])]
    public function transactions(TransactionsRepository $trepo): JsonResponse
    {
        $transactions = $trepo->findBy([], ['date' => 'DESC']);

        $data = [];
        foreach ($transactions as $transaction) {
            $data[] = [
                'id' => $transaction->getId(),
                'label' => $transaction->getLabel(),
                'amount' => $transaction->getMontant(),
                'date' => $transaction->getDate()->format('d/m/Y'),
                'type' => $transaction->getTransactionsTypes()->getCode(),
            ];
        }

        return new JsonResponse($data);
    }
}

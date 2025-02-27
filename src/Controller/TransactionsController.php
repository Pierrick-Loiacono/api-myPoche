<?php

namespace App\Controller;

use App\Entity\Transactions;
use App\Entity\Utilisateurs;
use App\Entity\TransactionsTypes;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionsRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\TransactionsTypesRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TransactionsController extends AbstractController
{
    #[Route('/api/transactions', name: 'app_transactions', methods: ['GET'])]
    public function transactions(TransactionsRepository $trepo, SerializerInterface $serializer): JsonResponse
    {
        $transactions = $trepo->findBy([], ['date' => 'DESC']);
        $json = $serializer->serialize($transactions, 'json');

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/api/new/transactions', name: 'new_transactions', methods: ['POST'])]
    public function postNewDevis(Request $request, EntityManagerInterface $entityManager, TransactionsTypesRepository $transactionsTypesRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true); // Convertit le JSON en tableau PHP

        if (!$data) {
            return new JsonResponse(['error' => 'Données invalides'], 400); // Données sont invalides
        }

        $transactionType = $transactionsTypesRepository->findOneBy(['code' => $data['type']]);
        $date = new \DateTime($data['date']); // 🔥 Convertit proprement

        // Création de l'entité
        $transaction = new Transactions();
        $transaction->setDate($date);
        $transaction->setLabel($data['label']);

        // Gestion du montant
        $montant = str_replace('-', '', $data['montant']); // Supprime les tirets, surtout le - au début
        $montant = str_replace(',', '.', $data['montant']); // Remplace les virgules par des points
        $montant = number_format((float) $montant, 2, '.', ''); // Assure 2 décimales
        $transaction->setMontant((string) $montant);

        // On récupérer le Type de transaction
        if ($transactionType) {
            $transaction->setTransactionsTypes($transactionType);
        } else {
            return new JsonResponse(['error' => 'Erreur rencontrée sur le type de transaction'], 400); // Données sont invalides
        }
        //Temporaire, a modifier une fois le système de connexion côté frontend en place
        $transaction->setUtilisateur($entityManager->getRepository(Utilisateurs::class)->findOneBy(['email' => 'test@gmail.com']));
        
        // Validation des données
        $errors = $validator->validate($transaction);

        // S'il y a des erreurs de validation
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = [
                    'field' => $error->getPropertyPath(), // 🔥 Nom du champ concerné
                    'message' => $error->getMessage(),    // 🛑 Message d'erreur précis
                ];
            }
            return new JsonResponse(['errors' => $errorMessages], 400); // Retourner les erreurs de validation
        }

        try {
            $entityManager->persist($transaction);
            $entityManager->flush();

            // Retourner une réponse JSON avec un message de succès
            return new JsonResponse(['message' => 'Transaction ajoutée'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'enregistrement des données : ' . $e->getMessage()], 500);
        }

    }
}

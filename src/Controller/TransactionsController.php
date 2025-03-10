<?php

namespace App\Controller;

use NumberFormatter;
use App\Entity\Transactions;
use App\Entity\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionsRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\TransactionsTypesRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TransactionsController extends AbstractController
{
    #[Route('/api/transactions', name: 'app_transactions', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function transactions(TransactionsRepository $trepo, SerializerInterface $serializer, Security $security): JsonResponse
    {
        // Récupération de toutes les transactions #TODO : Optimiser pour récupérer les transactions du mois
        $transactions = $trepo->findBy(['utilisateur' => $security->getUser()->getId()], ['date' => 'DESC']);
        $jsonTransactions = $serializer->serialize($transactions, 'json');

        // Calcul du total des transactions #TODO : Optimiser en ayant un champ total dans la table de l'utilisateur
        $formatter = new NumberFormatter('fr_FR', NumberFormatter::DECIMAL);

        $totalAmount = array_sum(array_map(fn($t) => $t->getMontant(), $transactions));

        // Calcul du total des transactions du mois en cours #TODO : Optimiser via requete dans le repo
        $currentMonth = (new \DateTime())->format('Y-m'); // Ex: "2025-03"
        $totalMonthAmount = array_sum(array_map(
            fn($t) => $t->getDate()->format('Y-m') === $currentMonth ? $t->getMontant() : 0,
            $transactions
        ));

        $json = [
            'transactions' => json_decode($jsonTransactions, true),
            'montantTotal' => $formatter->format($totalAmount). ' €',
            'montantTotalMoisEnCours' => $formatter->format($totalMonthAmount). ' €',
        ];

        return new JsonResponse($json, 200);
    }

    #[Route('/api/new/transactions', name: 'new_transactions', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function postNewDevis(Request $request, Security $security, EntityManagerInterface $entityManager, TransactionsTypesRepository $transactionsTypesRepository, ValidatorInterface $validator): JsonResponse
    {
        try {

            $data = json_decode($request->getContent(), true); // Convertit le JSON en tableau PHP

            if (!$data) {
                return new JsonResponse(['error' => 'Données invalides'], 400); // Données sont invalides
            }

            // Vérification du type de transaction
            $transactionType = $transactionsTypesRepository->findOneBy(['code' => $data['type']]);
            if (!$transactionType) {
                return new JsonResponse(['error' => 'Erreur rencontrée sur le type de transaction'], 400); // Données sont invalides
            }

            try {
                $date = new \DateTime($data['date']);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Format de date invalide'], 400);
            }

            // Gestion du montant
            $montant = str_replace(',', '.', $data['montant']); // Remplace les virgules par des points
            $montant = (float) number_format($montant, 2, '.', ''); // Assure 2 décimales
            // Gestion des montants en fonction du type de transaction
            if ($transactionType->getCode() == 'OUT' && $montant > 0) {
                $montant = -$montant; // Évite les montant positif
                // $montant = $montant; // Évite les montant positif
            } elseif ($transactionType->getCode() === 'IN' && $montant < 0) {
                $montant = abs($montant); // Évite les montant en négatif
            }

            // Création de l'entité
            $transaction = new Transactions();
            $transaction->setDate($date);
            $transaction->setLabel($data['label']);
            $transaction->setTransactionsTypes($transactionType);
            $transaction->setMontant((string) $montant);
            //Temporaire, a modifier une fois le système de connexion côté frontend en place
            $transaction->setUtilisateur($entityManager->getRepository(Utilisateurs::class)->findOneBy(['id' => $security->getUser()->getId()]));
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

            $entityManager->persist($transaction);
            $entityManager->flush();

            // Retourner une réponse JSON avec un message de succès
            return new JsonResponse(['message' => 'Transaction ajoutée'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'enregistrement des données : ' . $e->getMessage()], 500);
        }
    }
}

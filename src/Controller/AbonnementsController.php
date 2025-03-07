<?php

namespace App\Controller;

use App\Entity\Abonnements;
use App\Entity\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AbonnementsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;

class AbonnementsController extends AbstractController
{
    #[Route('/api/abonnements', name: 'app_abonnements', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function abonnements(AbonnementsRepository $aborepo, SerializerInterface $serializer, Security $security): JsonResponse
    {
        $abonnements = $aborepo->findBy(['utilisateur' => $security->getUser()->getId()], ['label' => 'DESC']);
        $json = $serializer->serialize($abonnements, 'json');

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/api/new/abonnement', name: 'new_abonnements', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function postNewDevis(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, Security $security): JsonResponse
    {
        try {

            $data = json_decode($request->getContent(), true); // Convertit le JSON en tableau PHP

            if (!$data) {
                return new JsonResponse(['error' => 'Données invalides'], 400); // Données sont invalides
            }

            // Vérification du type de transaction

            try {
                $prochaineDatePrelevement = new \DateTime($data['date']);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Format de date invalide'], 400);
            }

            $frequence = (int) number_format($data['frequence'], 0, '.', ''); // Assure qucune decimal
            // Gestion du montant
            $montant = str_replace(',', '.', $data['montant']); // Remplace les virgules par des points
            $montant = (float) number_format($montant, 2, '.', ''); // Assure 2 décimales
            // Gestion des montants en fonction du type de transaction

            if ($montant < 0) {
                $montant = abs($montant); // Évite les montant en négatif
            }
            // Création de l'entité
            $abonnement = new Abonnements();
            $abonnement->setLabel($data['label']);
            $abonnement->setMontant((string) $montant);
            $abonnement->setDateCreation(new \DateTime());
            $abonnement->setProchainPrelevement($prochaineDatePrelevement);
            $abonnement->setFrequence($frequence);
            //Temporaire, a modifier une fois le système de connexion côté frontend en place
            $abonnement->setUtilisateur($entityManager->getRepository(Utilisateurs::class)->findOneBy(['id' => $security->getUser()->getId()]));
            // Validation des données
            $errors = $validator->validate($abonnement);
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

            $entityManager->persist($abonnement);
            $entityManager->flush();

            // Retourner une réponse JSON avec un message de succès
            return new JsonResponse(['message' => 'Abonnement ajouté'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'enregistrement des données : ' . $e->getMessage()], 500);
        }
    }
}

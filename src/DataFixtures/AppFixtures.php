<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Pays;
use App\Entity\Devis;
use App\Entity\Clients;
use App\Entity\DevisStatut;
use App\Entity\Transactions;
use App\Entity\TransactionsTypes;
use App\Entity\Utilisateurs;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher) {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $plainPassword = 'password';

        $utilisateur = new Utilisateurs;
        $utilisateur->setUuid(Uuid::v7());
        $utilisateur->setEmail('test@gmail.com');
        $utilisateur->setRoles(['ROLE_ADMIN']);
        $utilisateur->setNom('Test');
        $utilisateur->setPrenom('Test');
        $utilisateur->setNumero('0606060606');
        $utilisateur->setDesactiver(false);
        $password = $this->userPasswordHasher->hashPassword($utilisateur, $plainPassword);
        $utilisateur->setPassword($password);
        $manager->persist($utilisateur);

        $types = new TransactionsTypes;
        $types->setLabel('Virement');
        $types->setCode('VIREMENT');
        $manager->persist($types);

        $types = new TransactionsTypes;
        $types->setLabel('Entrée d\'argent');
        $types->setCode('IN');
        $manager->persist($types);

        $types = new TransactionsTypes;
        $types->setLabel('Sortie d\'argent');
        $types->setCode('OUT');
        $manager->persist($types);

        for($i=0; $i<30; $i++){
            $transaction = new Transactions;
            $transaction->setLabel($faker->sentence(6, true));
            $transaction->setMontant($faker->randomFloat(2, 0, 1000));
            $transaction->setDate($faker->dateTimeBetween('-1 years', 'now'));
            $transaction->setTransactionsTypes($types);
            $transaction->setUtilisateur($utilisateur);
            $manager->persist($transaction);
        }

        $manager->flush();
    }
}

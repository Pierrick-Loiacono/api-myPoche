<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Pays;
use App\Entity\Devis;
use App\Entity\Clients;
use App\Entity\DevisStatut;
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

        $manager->flush();
    }
}

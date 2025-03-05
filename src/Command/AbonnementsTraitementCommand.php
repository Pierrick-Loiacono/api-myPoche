<?php

namespace App\Command;

use App\Entity\Transactions;
use App\Entity\TransactionsTypes;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AbonnementsRepository;
use Symfony\Component\Console\Command\Command;
use App\Repository\TransactionsTypesRepository;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:abonnements:traitement',
    description: 'Créer une transaction pour les abonnements en cours',
)]
class AbonnementsTraitementCommand extends Command
{
    private $abonnementsRepository;
    private $transactionsTypesRepository;
    private $entityManager;

    public function __construct(AbonnementsRepository $abonnementsRepository, TransactionsTypesRepository $transactionsTypesRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->abonnementsRepository = $abonnementsRepository;
        $this->transactionsTypesRepository = $transactionsTypesRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $abos = $this->abonnementsRepository->findAbonnementsATraiter();

        $transactionType = $this->transactionsTypesRepository->findOneBy(['code' => 'OUT']);

        foreach ($abos as $abo) {
            $transaction = new Transactions();
            $transaction->setDate(new \DateTime());
            $transaction->setMontant($abo->getMontant());
            $transaction->setLabel('Prélèvement abonnement : ' . $abo->getLabel());
            $transaction->setTransactionsTypes($transactionType);
            $transaction->setUtilisateur($abo->getUtilisateur());


            $abo->setProchainPrelevement((clone $abo->getProchainPrelevement())->modify('+' . $abo->getFrequence() . ' month'));
            $this->entityManager->persist($transaction);
        }

        $this->entityManager->flush();

        $output->writeln('Nombre d\'abonnements à traiter : ' . count($abos));

        return Command::SUCCESS;
    }
}

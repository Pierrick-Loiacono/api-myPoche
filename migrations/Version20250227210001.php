<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227210001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abonnements (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, label VARCHAR(255) NOT NULL, date_creation DATE NOT NULL, prochain_prelevement DATE NOT NULL, montant NUMERIC(15, 2) NOT NULL, frequence SMALLINT NOT NULL, INDEX IDX_4788B767FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abonnements ADD CONSTRAINT FK_4788B767FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abonnements DROP FOREIGN KEY FK_4788B767FB88E14F');
        $this->addSql('DROP TABLE abonnements');
    }
}

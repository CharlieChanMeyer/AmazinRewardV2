<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230320092931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE nb_code_user_event (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, event_id INT NOT NULL, nb_code INT DEFAULT 1 NOT NULL, INDEX IDX_5E8672FDA76ED395 (user_id), INDEX IDX_5E8672FD71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE nb_code_user_event ADD CONSTRAINT FK_5E8672FDA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE nb_code_user_event ADD CONSTRAINT FK_5E8672FD71F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nb_code_user_event DROP FOREIGN KEY FK_5E8672FDA76ED395');
        $this->addSql('ALTER TABLE nb_code_user_event DROP FOREIGN KEY FK_5E8672FD71F7E88B');
        $this->addSql('DROP TABLE nb_code_user_event');
    }
}

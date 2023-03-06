<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230302013133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE events_users (events_id INT NOT NULL, users_id INT NOT NULL, INDEX IDX_A43F6DCF9D6A1065 (events_id), INDEX IDX_A43F6DCF67B3B43D (users_id), PRIMARY KEY(events_id, users_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE events_users ADD CONSTRAINT FK_A43F6DCF9D6A1065 FOREIGN KEY (events_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events_users ADD CONSTRAINT FK_A43F6DCF67B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events_users DROP FOREIGN KEY FK_A43F6DCF9D6A1065');
        $this->addSql('ALTER TABLE events_users DROP FOREIGN KEY FK_A43F6DCF67B3B43D');
        $this->addSql('DROP TABLE events_users');
    }
}

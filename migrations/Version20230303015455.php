<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230303015455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events ADD smtpemail VARCHAR(100) NOT NULL, ADD smtppassword VARCHAR(300) NOT NULL, ADD email_header VARCHAR(300) NOT NULL, ADD email_body LONGTEXT NOT NULL, ADD email_subject VARCHAR(300) NOT NULL, ADD email_alt_body LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE events DROP smtpemail, DROP smtppassword, DROP email_header, DROP email_body, DROP email_subject, DROP email_alt_body');
    }
}

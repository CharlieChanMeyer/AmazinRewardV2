<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230302121828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE code_amazon (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, amazon_code VARCHAR(300) NOT NULL, used INT NOT NULL, INDEX IDX_FEDDD0D571F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE history_log (id INT AUTO_INCREMENT NOT NULL, email_id_id INT NOT NULL, amazon_code_id_id INT NOT NULL, event_id_id INT NOT NULL, datetime DATETIME NOT NULL, INDEX IDX_6190350AE209DFD8 (email_id_id), UNIQUE INDEX UNIQ_6190350ABD072309 (amazon_code_id_id), INDEX IDX_6190350A3E5F2F7B (event_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE code_amazon ADD CONSTRAINT FK_FEDDD0D571F7E88B FOREIGN KEY (event_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE history_log ADD CONSTRAINT FK_6190350AE209DFD8 FOREIGN KEY (email_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE history_log ADD CONSTRAINT FK_6190350ABD072309 FOREIGN KEY (amazon_code_id_id) REFERENCES code_amazon (id)');
        $this->addSql('ALTER TABLE history_log ADD CONSTRAINT FK_6190350A3E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_amazon DROP FOREIGN KEY FK_FEDDD0D571F7E88B');
        $this->addSql('ALTER TABLE history_log DROP FOREIGN KEY FK_6190350AE209DFD8');
        $this->addSql('ALTER TABLE history_log DROP FOREIGN KEY FK_6190350ABD072309');
        $this->addSql('ALTER TABLE history_log DROP FOREIGN KEY FK_6190350A3E5F2F7B');
        $this->addSql('DROP TABLE code_amazon');
        $this->addSql('DROP TABLE history_log');
    }
}

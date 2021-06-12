<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210601130354 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE multimedia ADD project_id INT NOT NULL');
        $this->addSql('ALTER TABLE multimedia ADD CONSTRAINT FK_61312863166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_61312863166D1F9C ON multimedia (project_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE multimedia DROP FOREIGN KEY FK_61312863166D1F9C');
        $this->addSql('DROP INDEX IDX_61312863166D1F9C ON multimedia');
        $this->addSql('ALTER TABLE multimedia DROP project_id');
    }
}

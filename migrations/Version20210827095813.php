<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210827095813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE square_meter_price_project');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE square_meter_price_project (square_meter_price_id INT NOT NULL, project_id INT NOT NULL, INDEX IDX_842C34B7166D1F9C (project_id), INDEX IDX_842C34B71D7BF326 (square_meter_price_id), PRIMARY KEY(square_meter_price_id, project_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE square_meter_price_project ADD CONSTRAINT FK_842C34B7166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE square_meter_price_project ADD CONSTRAINT FK_842C34B71D7BF326 FOREIGN KEY (square_meter_price_id) REFERENCES square_meter_price (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}

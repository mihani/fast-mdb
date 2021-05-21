<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210510155017 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, firstname VARCHAR(50) DEFAULT NULL, lastname VARCHAR(50) NOT NULL, email VARCHAR(255) DEFAULT NULL, mobile_number VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_4C62E638F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE project ADD notary_id INT DEFAULT NULL, ADD estate_agent_id INT DEFAULT NULL, ADD seller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEACC994D3 FOREIGN KEY (notary_id) REFERENCES contact (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7C3696E4 FOREIGN KEY (estate_agent_id) REFERENCES contact (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE8DE820D9 FOREIGN KEY (seller_id) REFERENCES contact (id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEACC994D3 ON project (notary_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE7C3696E4 ON project (estate_agent_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE8DE820D9 ON project (seller_id)');
        $this->addSql('ALTER TABLE contact ADD estate_agency_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact CHANGE firstname firstname VARCHAR(50) NOT NULL, CHANGE lastname lastname VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE user ADD active TINYINT(1) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094F5E237E06 ON company (name)');
        $this->addSql('ALTER TABLE contact CHANGE firstname firstname VARCHAR(50) DEFAULT NULL, CHANGE mobile_number mobile_number VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD website VARCHAR(255) DEFAULT NULL, ADD notary_office VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD company_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TABLE square_meter_price (id INT AUTO_INCREMENT NOT NULL, city_code VARCHAR(15) NOT NULL, price INT NOT NULL, year VARCHAR(8) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE square_meter_price_project (square_meter_price_id INT NOT NULL, project_id INT NOT NULL, INDEX IDX_842C34B71D7BF326 (square_meter_price_id), INDEX IDX_842C34B7166D1F9C (project_id), PRIMARY KEY(square_meter_price_id, project_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE square_meter_price_project ADD CONSTRAINT FK_842C34B71D7BF326 FOREIGN KEY (square_meter_price_id) REFERENCES square_meter_price (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE square_meter_price_project ADD CONSTRAINT FK_842C34B7166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE square_meter_price ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE square_meter_price CHANGE price price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE square_meter_price CHANGE city_code insee_code VARCHAR(15) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE contact');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEACC994D3');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE7C3696E4');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE8DE820D9');
        $this->addSql('DROP INDEX IDX_2FB3D0EEACC994D3 ON project');
        $this->addSql('DROP INDEX IDX_2FB3D0EE7C3696E4 ON project');
        $this->addSql('DROP INDEX IDX_2FB3D0EE8DE820D9 ON project');
        $this->addSql('ALTER TABLE project DROP notary_id, DROP estate_agent_id, DROP seller_id');
        $this->addSql('ALTER TABLE contact DROP estate_agency_name');
        $this->addSql('ALTER TABLE contact CHANGE firstname firstname VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lastname lastname VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE `user` DROP active');
        $this->addSql('DROP INDEX UNIQ_4FBF094F5E237E06 ON company');
        $this->addSql('ALTER TABLE contact CHANGE firstname firstname VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE mobile_number mobile_number VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE contact DROP website, DROP notary_office');
        $this->addSql('ALTER TABLE contact DROP company_name');
        $this->addSql('ALTER TABLE square_meter_price_project DROP FOREIGN KEY FK_842C34B71D7BF326');
        $this->addSql('DROP TABLE square_meter_price');
        $this->addSql('DROP TABLE square_meter_price_project');
        $this->addSql('ALTER TABLE square_meter_price DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE square_meter_price CHANGE price price INT NOT NULL');
        $this->addSql('ALTER TABLE square_meter_price CHANGE insee_code city_code VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}

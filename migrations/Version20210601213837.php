<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210601213837 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE simulator_conf (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, purchase_fee DOUBLE PRECISION NOT NULL, estate_agency_purchase_fee DOUBLE PRECISION NOT NULL, geometer_fee DOUBLE PRECISION NOT NULL, architect_fee DOUBLE PRECISION NOT NULL, study_office_fee DOUBLE PRECISION NOT NULL, insurance_fee DOUBLE PRECISION NOT NULL, care_fee DOUBLE PRECISION NOT NULL, estate_agency_sale_fee DOUBLE PRECISION NOT NULL, bank_interest DOUBLE PRECISION NOT NULL, bank_engagement_commission DOUBLE PRECISION NOT NULL, bank_admin_fee DOUBLE PRECISION NOT NULL, intermediation_fee DOUBLE PRECISION NOT NULL, unexpected_fee DOUBLE PRECISION NOT NULL, acceptable_margin DOUBLE PRECISION NOT NULL, mains_drainage_tax DOUBLE PRECISION NOT NULL, development_tax DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_EB62DCA3979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE simulator_info (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, purchase_fee DOUBLE PRECISION NOT NULL, estate_agency_purchase_fee DOUBLE PRECISION NOT NULL, geometer_fee DOUBLE PRECISION NOT NULL, architect_fee DOUBLE PRECISION NOT NULL, study_office_fee DOUBLE PRECISION NOT NULL, insurance_fee DOUBLE PRECISION NOT NULL, care_fee DOUBLE PRECISION NOT NULL, estate_agency_sale_fee DOUBLE PRECISION NOT NULL, bank_interest DOUBLE PRECISION NOT NULL, bank_engagement_commission DOUBLE PRECISION NOT NULL, bank_admin_fee DOUBLE PRECISION NOT NULL, intermediation_fee DOUBLE PRECISION NOT NULL, unexpected_fee DOUBLE PRECISION NOT NULL, acceptable_margin DOUBLE PRECISION NOT NULL, mains_drainage_tax DOUBLE PRECISION NOT NULL, development_tax DOUBLE PRECISION NOT NULL, has_estate_agency_sale_fee TINYINT(1) NOT NULL, has_estate_agency_purchase_fee TINYINT(1) NOT NULL, has_urbanism_prior_declaration TINYINT(1) NOT NULL, has_urbanism_building_permits TINYINT(1) NOT NULL, has_urbanism_planning_permission TINYINT(1) NOT NULL, has_insurance TINYINT(1) NOT NULL, has_intermediation_fee TINYINT(1) NOT NULL, has_vat_on_margin TINYINT(1) NOT NULL, has_mains_drainage_tax TINYINT(1) NOT NULL, sale_price INT DEFAULT NULL, purchase_price INT DEFAULT NULL, works_cost INT DEFAULT NULL, financial_contribution INT DEFAULT NULL, UNIQUE INDEX UNIQ_3418645C166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE simulator_conf ADD CONSTRAINT FK_EB62DCA3979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE simulator_info ADD CONSTRAINT FK_3418645C166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE simulator_conf');
        $this->addSql('DROP TABLE simulator_info');
    }
}

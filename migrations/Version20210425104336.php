<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210425104336 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(180) NOT NULL, lastname VARCHAR(180) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE TABLE logger_dvf (id INT AUTO_INCREMENT NOT NULL, addresses_not_found JSON DEFAULT NULL, addresses_timeout JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, address_line1 VARCHAR(255) NOT NULL, address_line2 VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(7) NOT NULL, city VARCHAR(150) NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, address_id INT NOT NULL, user_id INT NOT NULL, company_id INT NOT NULL, state VARCHAR(125) NOT NULL, cadastral_plan_number VARCHAR(10) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_2FB3D0EEF5B7AF75 (address_id), INDEX IDX_2FB3D0EEA76ED395 (user_id), INDEX IDX_2FB3D0EE979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE urban_document (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, archive_link VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, urban_portal_id VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, api_updated_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_EF623934166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE urban_file (id INT AUTO_INCREMENT NOT NULL, urban_document_id INT NOT NULL, link VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_B7D6F252E81B055D (urban_document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE urban_document ADD CONSTRAINT FK_EF623934166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE urban_file ADD CONSTRAINT FK_B7D6F252E81B055D FOREIGN KEY (urban_document_id) REFERENCES urban_document (id)');
        $this->addSql('ALTER TABLE company ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD insee_code VARCHAR(10) DEFAULT NULL');
        $this->addSql('CREATE TABLE goods_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project ADD goods_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE201AE29E FOREIGN KEY (goods_type_id) REFERENCES goods_type (id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE201AE29E ON project (goods_type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649979B1AD6');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('ALTER TABLE `user` DROP created_at, DROP updated_at');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE logger_dvf');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEF5B7AF75');
        $this->addSql('ALTER TABLE urban_document DROP FOREIGN KEY FK_EF623934166D1F9C');
        $this->addSql('ALTER TABLE urban_file DROP FOREIGN KEY FK_B7D6F252E81B055D');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE urban_document');
        $this->addSql('DROP TABLE urban_file');
        $this->addSql('ALTER TABLE company DROP deleted_at');
        $this->addSql('ALTER TABLE `user` DROP deleted_at');
        $this->addSql('ALTER TABLE address DROP insee_code');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE201AE29E');
        $this->addSql('DROP TABLE goods_type');
        $this->addSql('DROP INDEX IDX_2FB3D0EE201AE29E ON project');
        $this->addSql('ALTER TABLE project DROP goods_type_id');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Utils\StringUtils;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210510072336 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO goods_type (name, slug, created_at, updated_at)
            VALUE ('Maison', '".StringUtils::slugify("Maison")."', NOW(), NOW()),
            ('Appartement', '".StringUtils::slugify("Appartement")."', NOW(), NOW()),
            ('Terrain', '".StringUtils::slugify("Terrain")."', NOW(), NOW())");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

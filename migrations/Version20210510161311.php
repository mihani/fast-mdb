<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210510161311 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD notary_id INT DEFAULT NULL, ADD estate_agent_id INT DEFAULT NULL, ADD seller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEACC994D3 FOREIGN KEY (notary_id) REFERENCES contact (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7C3696E4 FOREIGN KEY (estate_agent_id) REFERENCES contact (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE8DE820D9 FOREIGN KEY (seller_id) REFERENCES contact (id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EEACC994D3 ON project (notary_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE7C3696E4 ON project (estate_agent_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE8DE820D9 ON project (seller_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EEACC994D3');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE7C3696E4');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE8DE820D9');
        $this->addSql('DROP INDEX IDX_2FB3D0EEACC994D3 ON project');
        $this->addSql('DROP INDEX IDX_2FB3D0EE7C3696E4 ON project');
        $this->addSql('DROP INDEX IDX_2FB3D0EE8DE820D9 ON project');
        $this->addSql('ALTER TABLE project DROP notary_id, DROP estate_agent_id, DROP seller_id');
    }
}

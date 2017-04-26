<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170425171912 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE promotions ADD offer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE promotions ADD CONSTRAINT FK_EA1B303453C674EE FOREIGN KEY (offer_id) REFERENCES offers (id)');
        $this->addSql('CREATE INDEX IDX_EA1B303453C674EE ON promotions (offer_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE promotions DROP FOREIGN KEY FK_EA1B303453C674EE');
        $this->addSql('DROP INDEX IDX_EA1B303453C674EE ON promotions');
        $this->addSql('ALTER TABLE promotions DROP offer_id');
    }
}

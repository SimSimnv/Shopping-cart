<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170418180945 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3AF346685E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE offers ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE offers ADD CONSTRAINT FK_DA46042712469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_DA46042712469DE2 ON offers (category_id)');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_user_roles_roles');
        $this->addSql('DROP INDEX fk_54fcd59fd60322ac ON user_roles');
        $this->addSql('CREATE INDEX IDX_54FCD59FD60322AC ON user_roles (role_id)');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_user_roles_roles FOREIGN KEY (role_id) REFERENCES roles (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE offers DROP FOREIGN KEY FK_DA46042712469DE2');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP INDEX IDX_DA46042712469DE2 ON offers');
        $this->addSql('ALTER TABLE offers DROP category_id');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FD60322AC');
        $this->addSql('DROP INDEX idx_54fcd59fd60322ac ON user_roles');
        $this->addSql('CREATE INDEX FK_54FCD59FD60322AC ON user_roles (role_id)');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FD60322AC FOREIGN KEY (role_id) REFERENCES roles (id)');
    }
}

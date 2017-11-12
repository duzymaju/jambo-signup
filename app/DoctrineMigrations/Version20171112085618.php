<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171112085618 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE patrol ADD status SMALLINT NOT NULL AFTER name, ADD activation_hash CHAR(32) NOT NULL AFTER status');
        $this->addSql('UPDATE patrol SET status = 1, activation_hash = MD5(CONCAT(id, "migration", created_at))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BFB23715CFA1EBA ON patrol (activation_hash)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_BFB23715CFA1EBA ON patrol');
        $this->addSql('ALTER TABLE patrol DROP status, DROP activation_hash');
    }
}

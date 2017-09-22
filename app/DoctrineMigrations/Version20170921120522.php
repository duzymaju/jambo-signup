<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170921120522 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE patrol (id INT AUTO_INCREMENT NOT NULL, leader_id INT DEFAULT NULL, troop_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, district_id SMALLINT UNSIGNED DEFAULT NULL, comments VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_BFB237173154ED4 (leader_id), INDEX IDX_BFB2371263060AC (troop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE patrol ADD CONSTRAINT FK_BFB237173154ED4 FOREIGN KEY (leader_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE patrol ADD CONSTRAINT FK_BFB2371263060AC FOREIGN KEY (troop_id) REFERENCES troop (id)');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11263060AC');
        $this->addSql('DROP INDEX IDX_D79F6B11263060AC ON participant');
        $this->addSql('ALTER TABLE participant CHANGE troop_id patrol_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11A7B49BA9 FOREIGN KEY (patrol_id) REFERENCES patrol (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B11A7B49BA9 ON participant (patrol_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11A7B49BA9');
        $this->addSql('DROP TABLE patrol');
        $this->addSql('DROP INDEX IDX_D79F6B11A7B49BA9 ON participant');
        $this->addSql('ALTER TABLE participant CHANGE patrol_id troop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11263060AC FOREIGN KEY (troop_id) REFERENCES troop (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B11263060AC ON participant (troop_id)');
    }
}

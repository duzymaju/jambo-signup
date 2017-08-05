<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170803154055 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE action (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, type VARCHAR(40) NOT NULL, object_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_47CC8C92A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, troop_id INT DEFAULT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, address VARCHAR(255) NOT NULL, phone VARCHAR(15) NOT NULL, email VARCHAR(40) NOT NULL, shirt_size SMALLINT DEFAULT NULL, sex CHAR(1) NOT NULL, birth_date DATE NOT NULL, grade_id SMALLINT DEFAULT NULL, status SMALLINT NOT NULL, activation_hash CHAR(32) NOT NULL, district_id SMALLINT UNSIGNED DEFAULT NULL, pesel BIGINT DEFAULT NULL, father_name VARCHAR(50) DEFAULT NULL, comments VARCHAR(255) DEFAULT NULL, emergency_info VARCHAR(100) DEFAULT NULL, emergency_phone VARCHAR(15) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D79F6B11E7927C74 (email), UNIQUE INDEX UNIQ_D79F6B115CFA1EBA (activation_hash), INDEX IDX_D79F6B11263060AC (troop_id), INDEX p_index_1 (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE troop (id INT AUTO_INCREMENT NOT NULL, leader_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, status SMALLINT NOT NULL, activation_hash CHAR(32) NOT NULL, district_id SMALLINT UNSIGNED DEFAULT NULL, comments VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_FAAD534C5CFA1EBA (activation_hash), UNIQUE INDEX UNIQ_FAAD534C73154ED4 (leader_id), INDEX t_index_1 (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE action ADD CONSTRAINT FK_47CC8C92A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11263060AC FOREIGN KEY (troop_id) REFERENCES troop (id)');
        $this->addSql('ALTER TABLE troop ADD CONSTRAINT FK_FAAD534C73154ED4 FOREIGN KEY (leader_id) REFERENCES participant (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE troop DROP FOREIGN KEY FK_FAAD534C73154ED4');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11263060AC');
        $this->addSql('ALTER TABLE action DROP FOREIGN KEY FK_47CC8C92A76ED395');
        $this->addSql('DROP TABLE action');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE troop');
        $this->addSql('DROP TABLE user');
    }
}

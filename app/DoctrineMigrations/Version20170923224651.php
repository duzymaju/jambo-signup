<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170923224651 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE participant ADD member_number VARCHAR(255) NOT NULL AFTER district_id, ADD special_diet VARCHAR(255) DEFAULT NULL AFTER pesel, CHANGE father_name guardian_name VARCHAR(100) NOT NULL AFTER comments, CHANGE emergency_phone guardian_phone VARCHAR(15) NOT NULL AFTER guardian_name, DROP emergency_info, CHANGE district_id district_id SMALLINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE patrol ADD methodology_group_id SMALLINT UNSIGNED NOT NULL AFTER district_id, CHANGE district_id district_id SMALLINT UNSIGNED NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE participant CHANGE district_id district_id SMALLINT UNSIGNED DEFAULT NULL, ADD emergency_info VARCHAR(100) DEFAULT NULL COLLATE utf8_general_ci AFTER comments, CHANGE guardian_phone emergency_phone VARCHAR(15) DEFAULT NULL COLLATE utf8_general_ci AFTER emergency_info, CHANGE guardian_name father_name VARCHAR(50) DEFAULT NULL COLLATE utf8_general_ci AFTER pesel, DROP special_diet, DROP member_number');
        $this->addSql('ALTER TABLE patrol DROP methodology_group_id, CHANGE district_id district_id SMALLINT UNSIGNED DEFAULT NULL');
    }
}

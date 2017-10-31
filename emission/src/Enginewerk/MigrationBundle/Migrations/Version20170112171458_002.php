<?php
namespace Enginewerk\MigrationBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170112171458_002 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610A76ED395');
        $this->addSql('ALTER TABLE file CHANGE createdAt created_at DATETIME NOT NULL, CHANGE updatedAt updated_at DATETIME NOT NULL, CHANGE complete complete TINYINT(1) NOT NULL, CHANGE fileid file_id VARCHAR(16) NOT NULL, CHANGE filehash file_hash VARCHAR(41) NOT NULL, CHANGE expirationdate expiration_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE file_block DROP FOREIGN KEY FK_29982715AFC548A5');
        $this->addSql('DROP INDEX IDX_29982715AFC548A5 ON file_block');
        $this->addSql('ALTER TABLE file_block CHANGE fileId file_id INT UNSIGNED DEFAULT NULL, CHANGE rangeStart range_start BIGINT UNSIGNED NOT NULL, CHANGE rangeEnd range_end BIGINT UNSIGNED NOT NULL, CHANGE filehash file_hash VARCHAR(41) NOT NULL');
        $this->addSql('ALTER TABLE file_block ADD CONSTRAINT FK_2998271593CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('CREATE INDEX IDX_2998271593CB796C ON file_block (file_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610A76ED395');
        $this->addSql('ALTER TABLE file CHANGE created_at createdAt DATETIME NOT NULL, CHANGE updated_at updatedAt DATETIME NOT NULL, CHANGE complete complete TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE file_id fileId VARCHAR(16) NOT NULL COLLATE utf8_unicode_ci, CHANGE file_hash fileHash VARCHAR(41) NOT NULL COLLATE utf8_unicode_ci, CHANGE expiration_date expirationDate DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE file_block DROP FOREIGN KEY FK_2998271593CB796C');
        $this->addSql('DROP INDEX IDX_2998271593CB796C ON file_block');
        $this->addSql('ALTER TABLE file_block CHANGE range_start rangeStart BIGINT UNSIGNED NOT NULL, CHANGE range_end rangeEnd BIGINT UNSIGNED NOT NULL, CHANGE file_id fileId INT UNSIGNED NOT NULL, CHANGE file_hash fileHash VARCHAR(41) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE file_block ADD CONSTRAINT FK_29982715AFC548A5 FOREIGN KEY (fileId) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_29982715AFC548A5 ON file_block (fileId)');
    }
}

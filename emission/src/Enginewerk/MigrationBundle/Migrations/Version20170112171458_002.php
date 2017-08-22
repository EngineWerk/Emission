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
        $this->addSql('ALTER TABLE file ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, DROP createdAt, DROP updatedAt, CHANGE complete complete TINYINT(1) NOT NULL, CHANGE fileid file_id VARCHAR(16) NOT NULL, CHANGE filehash file_hash VARCHAR(41) NOT NULL, CHANGE expirationdate expiration_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE file_block DROP FOREIGN KEY FK_29982715AFC548A5');
        $this->addSql('DROP INDEX IDX_29982715AFC548A5 ON file_block');
        $this->addSql('ALTER TABLE file_block ADD file_id INT UNSIGNED DEFAULT NULL, ADD range_start BIGINT UNSIGNED NOT NULL, ADD range_end BIGINT UNSIGNED NOT NULL, DROP rangeStart, DROP rangeEnd, DROP fileId, CHANGE filehash file_hash VARCHAR(41) NOT NULL');
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
        $this->addSql('ALTER TABLE file ADD createdAt DATETIME NOT NULL, ADD updatedAt DATETIME NOT NULL, DROP created_at, DROP updated_at, CHANGE complete complete TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE file_id fileId VARCHAR(16) NOT NULL COLLATE utf8_unicode_ci, CHANGE file_hash fileHash VARCHAR(41) NOT NULL COLLATE utf8_unicode_ci, CHANGE expiration_date expirationDate DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE file_block DROP FOREIGN KEY FK_2998271593CB796C');
        $this->addSql('DROP INDEX IDX_2998271593CB796C ON file_block');
        $this->addSql('ALTER TABLE file_block ADD rangeStart BIGINT UNSIGNED NOT NULL, ADD rangeEnd BIGINT UNSIGNED NOT NULL, ADD fileId INT UNSIGNED NOT NULL, DROP file_id, DROP range_start, DROP range_end, CHANGE file_hash fileHash VARCHAR(41) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE file_block ADD CONSTRAINT FK_29982715AFC548A5 FOREIGN KEY (fileId) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_29982715AFC548A5 ON file_block (fileId)');
    }
}

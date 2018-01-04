<?php
namespace Enginewerk\Shared\Infrastructure\Persistence\Doctrine\Sf3DoctrineMigrationBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170110160719_001 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE file (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, fileId VARCHAR(16) NOT NULL, fileHash VARCHAR(41) NOT NULL, checksum VARCHAR(32) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(128) NOT NULL, size BIGINT UNSIGNED NOT NULL, expirationDate DATETIME DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, complete TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_8C9F3610A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_block (id INT UNSIGNED AUTO_INCREMENT NOT NULL, fileHash VARCHAR(41) NOT NULL, size INT UNSIGNED NOT NULL, rangeStart BIGINT UNSIGNED NOT NULL, rangeEnd BIGINT UNSIGNED NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, fileId INT UNSIGNED NOT NULL, INDEX IDX_29982715AFC548A5 (fileId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE binary_block (id INT AUTO_INCREMENT NOT NULL, urn VARCHAR(255) NOT NULL, checksum VARCHAR(40) NOT NULL, size INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitation (code VARCHAR(6) NOT NULL, email VARCHAR(256) NOT NULL, sent TINYINT(1) NOT NULL, PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user (id INT AUTO_INCREMENT NOT NULL, invitation_id VARCHAR(6) DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', google VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_957A647992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_957A6479A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_957A6479C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_957A6479A35D7AF0 (invitation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE file_block ADD CONSTRAINT FK_29982715AFC548A5 FOREIGN KEY (fileId) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A6479A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES invitation (code)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE file_block DROP FOREIGN KEY FK_29982715AFC548A5');
        $this->addSql('ALTER TABLE fos_user DROP FOREIGN KEY FK_957A6479A35D7AF0');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610A76ED395');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE file_block');
        $this->addSql('DROP TABLE binary_block');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE fos_user');
    }
}

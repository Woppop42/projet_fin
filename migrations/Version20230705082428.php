<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230705082428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, annonceur_id INT DEFAULT NULL, chercheur_id INT DEFAULT NULL, jeux_id INT DEFAULT NULL, sujet VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8A8E26E9C8764012 (annonceur_id), INDEX IDX_8A8E26E9F0950B34 (chercheur_id), INDEX IDX_8A8E26E9EC2AA9D2 (jeux_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9C8764012 FOREIGN KEY (annonceur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9F0950B34 FOREIGN KEY (chercheur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9EC2AA9D2 FOREIGN KEY (jeux_id) REFERENCES jeux (id)');
        $this->addSql('ALTER TABLE messagerie ADD conversation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE messagerie ADD CONSTRAINT FK_14E8F60C9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('CREATE INDEX IDX_14E8F60C9AC0396 ON messagerie (conversation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messagerie DROP FOREIGN KEY FK_14E8F60C9AC0396');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9C8764012');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9F0950B34');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9EC2AA9D2');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP INDEX IDX_14E8F60C9AC0396 ON messagerie');
        $this->addSql('ALTER TABLE messagerie DROP conversation_id');
    }
}

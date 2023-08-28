<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230828092659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE plateforme_user (plateforme_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B7970A49391E226B (plateforme_id), INDEX IDX_B7970A49A76ED395 (user_id), PRIMARY KEY(plateforme_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE plateforme_user ADD CONSTRAINT FK_B7970A49391E226B FOREIGN KEY (plateforme_id) REFERENCES plateforme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plateforme_user ADD CONSTRAINT FK_B7970A49A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE plateforme_user DROP FOREIGN KEY FK_B7970A49391E226B');
        $this->addSql('ALTER TABLE plateforme_user DROP FOREIGN KEY FK_B7970A49A76ED395');
        $this->addSql('DROP TABLE plateforme_user');
    }
}

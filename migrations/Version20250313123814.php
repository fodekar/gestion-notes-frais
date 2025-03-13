<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313123814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users_companies (user_id UUID NOT NULL, company_id UUID NOT NULL, PRIMARY KEY(user_id, company_id))');
        $this->addSql('CREATE INDEX IDX_E439D0DBA76ED395 ON users_companies (user_id)');
        $this->addSql('CREATE INDEX IDX_E439D0DB979B1AD6 ON users_companies (company_id)');
        $this->addSql('COMMENT ON COLUMN users_companies.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN users_companies.company_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE users_companies ADD CONSTRAINT FK_E439D0DBA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users_companies ADD CONSTRAINT FK_E439D0DB979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE users_companies DROP CONSTRAINT FK_E439D0DBA76ED395');
        $this->addSql('ALTER TABLE users_companies DROP CONSTRAINT FK_E439D0DB979B1AD6');
        $this->addSql('DROP TABLE users_companies');
    }
}

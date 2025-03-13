<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250312233442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE companies (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN companies.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE expense_notes (id UUID NOT NULL, user_id UUID NOT NULL, company_id UUID NOT NULL, date DATE NOT NULL, amount NUMERIC(10, 2) NOT NULL, type VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A21301B6A76ED395 ON expense_notes (user_id)');
        $this->addSql('CREATE INDEX IDX_A21301B6979B1AD6 ON expense_notes (company_id)');
        $this->addSql('COMMENT ON COLUMN expense_notes.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN expense_notes.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN expense_notes.company_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN expense_notes.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN expense_notes.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('COMMENT ON COLUMN users.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN users.birth_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE expense_notes ADD CONSTRAINT FK_A21301B6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expense_notes ADD CONSTRAINT FK_A21301B6979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE expense_notes DROP CONSTRAINT FK_A21301B6A76ED395');
        $this->addSql('ALTER TABLE expense_notes DROP CONSTRAINT FK_A21301B6979B1AD6');
        $this->addSql('DROP TABLE companies');
        $this->addSql('DROP TABLE expense_notes');
        $this->addSql('DROP TABLE users');
    }
}

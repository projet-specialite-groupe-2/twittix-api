<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250514091213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#49 add created at on messages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN message.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE message DROP created_at');
    }
}

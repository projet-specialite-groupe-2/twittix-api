<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250419102524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[#41] Remove password field from user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP password');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD password VARCHAR(255) NOT NULL');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250518082247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#57 Change content length from 255 to 280';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE twit ALTER content TYPE VARCHAR(280)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE twit ALTER content TYPE VARCHAR(255)');
    }
}

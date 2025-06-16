<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250209165953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[#13] Create Twit entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE twit (id SERIAL NOT NULL, author_id INT NOT NULL, content VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, parent INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_77F0F3C9F675F31B ON twit (author_id)');
        $this->addSql('COMMENT ON COLUMN twit.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE twit ADD CONSTRAINT FK_77F0F3C9F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE twit DROP CONSTRAINT FK_77F0F3C9F675F31B');
        $this->addSql('DROP TABLE twit');
    }
}

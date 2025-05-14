<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250331093323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#22 Create repost entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE repost (id SERIAL NOT NULL, author_id INT NOT NULL, twit_id INT NOT NULL, comment VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DD3446C5F675F31B ON repost (author_id)');
        $this->addSql('CREATE INDEX IDX_DD3446C57A1328EA ON repost (twit_id)');
        $this->addSql('COMMENT ON COLUMN repost.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE repost ADD CONSTRAINT FK_DD3446C5F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE repost ADD CONSTRAINT FK_DD3446C57A1328EA FOREIGN KEY (twit_id) REFERENCES twit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE repost DROP CONSTRAINT FK_DD3446C5F675F31B');
        $this->addSql('ALTER TABLE repost DROP CONSTRAINT FK_DD3446C57A1328EA');
        $this->addSql('DROP TABLE repost');
    }
}

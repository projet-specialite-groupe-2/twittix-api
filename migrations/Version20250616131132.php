<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250616131132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_twit table to manage viewed twits by users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_twit (user_id INT NOT NULL, twit_id INT NOT NULL, PRIMARY KEY(user_id, twit_id))');
        $this->addSql('CREATE INDEX IDX_D71BF00A76ED395 ON user_twit (user_id)');
        $this->addSql('CREATE INDEX IDX_D71BF007A1328EA ON user_twit (twit_id)');
        $this->addSql('ALTER TABLE user_twit ADD CONSTRAINT FK_D71BF00A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_twit ADD CONSTRAINT FK_D71BF007A1328EA FOREIGN KEY (twit_id) REFERENCES twit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_twit DROP CONSTRAINT FK_D71BF00A76ED395');
        $this->addSql('ALTER TABLE user_twit DROP CONSTRAINT FK_D71BF007A1328EA');
        $this->addSql('DROP TABLE user_twit');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250303181540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#15 - Create conversation and user_conversation tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE conversation (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, picture_path VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN conversation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE user_conversation (user_id INT NOT NULL, conversation_id INT NOT NULL, PRIMARY KEY(user_id, conversation_id))');
        $this->addSql('CREATE INDEX IDX_A425AEBA76ED395 ON user_conversation (user_id)');
        $this->addSql('CREATE INDEX IDX_A425AEB9AC0396 ON user_conversation (conversation_id)');
        $this->addSql('ALTER TABLE user_conversation ADD CONSTRAINT FK_A425AEBA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_conversation ADD CONSTRAINT FK_A425AEB9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_conversation DROP CONSTRAINT FK_A425AEBA76ED395');
        $this->addSql('ALTER TABLE user_conversation DROP CONSTRAINT FK_A425AEB9AC0396');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE user_conversation');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250404084459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '#33 Adds user blocking';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_blocks (blocker_id INT NOT NULL, blocked_id INT NOT NULL, PRIMARY KEY(blocker_id, blocked_id))');
        $this->addSql('CREATE INDEX IDX_ABBF8E45548D5975 ON user_blocks (blocker_id)');
        $this->addSql('CREATE INDEX IDX_ABBF8E4521FF5136 ON user_blocks (blocked_id)');
        $this->addSql('ALTER TABLE user_blocks ADD CONSTRAINT FK_ABBF8E45548D5975 FOREIGN KEY (blocker_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_blocks ADD CONSTRAINT FK_ABBF8E4521FF5136 FOREIGN KEY (blocked_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_blocks DROP CONSTRAINT FK_ABBF8E45548D5975');
        $this->addSql('ALTER TABLE user_blocks DROP CONSTRAINT FK_ABBF8E4521FF5136');
        $this->addSql('DROP TABLE user_blocks');
    }
}

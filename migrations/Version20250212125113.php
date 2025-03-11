<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212125113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD meeting_room_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7CCC5381E FOREIGN KEY (meeting_room_id) REFERENCES meeting_room (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7CCC5381E ON event (meeting_room_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7CCC5381E');
        $this->addSql('DROP INDEX IDX_3BAE0AA7CCC5381E ON event');
        $this->addSql('ALTER TABLE event DROP meeting_room_id');
    }
}

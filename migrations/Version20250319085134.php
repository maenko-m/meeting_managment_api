<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250319085134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9E6EA9495E237E06 ON meeting_room (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9E6EA9492583B3CC ON meeting_room (calendar_code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_9E6EA9495E237E06 ON meeting_room');
        $this->addSql('DROP INDEX UNIQ_9E6EA9492583B3CC ON meeting_room');
    }
}

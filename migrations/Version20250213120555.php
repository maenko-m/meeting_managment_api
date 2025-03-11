<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213120555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE meeting_room_access (meeting_room_id INT NOT NULL, employee_id INT NOT NULL, INDEX IDX_6B47E3E0CCC5381E (meeting_room_id), INDEX IDX_6B47E3E08C03F15C (employee_id), PRIMARY KEY(meeting_room_id, employee_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meeting_room_access ADD CONSTRAINT FK_6B47E3E0CCC5381E FOREIGN KEY (meeting_room_id) REFERENCES meeting_room (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_room_access ADD CONSTRAINT FK_6B47E3E08C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_room ADD is_public TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meeting_room_access DROP FOREIGN KEY FK_6B47E3E0CCC5381E');
        $this->addSql('ALTER TABLE meeting_room_access DROP FOREIGN KEY FK_6B47E3E08C03F15C');
        $this->addSql('DROP TABLE meeting_room_access');
        $this->addSql('ALTER TABLE meeting_room DROP is_public');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313123959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employee CHANGE organization_id organization_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5D9F75A1E7927C74 ON employee (email)');
        $this->addSql('ALTER TABLE event CHANGE employee_id employee_id INT NOT NULL, CHANGE meeting_room_id meeting_room_id INT NOT NULL');
        $this->addSql('ALTER TABLE meeting_room CHANGE office_id office_id INT NOT NULL');
        $this->addSql('ALTER TABLE office CHANGE organization_id organization_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meeting_room CHANGE office_id office_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_5D9F75A1E7927C74 ON employee');
        $this->addSql('ALTER TABLE employee CHANGE organization_id organization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE office CHANGE organization_id organization_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event CHANGE employee_id employee_id INT DEFAULT NULL, CHANGE meeting_room_id meeting_room_id INT DEFAULT NULL');
    }
}

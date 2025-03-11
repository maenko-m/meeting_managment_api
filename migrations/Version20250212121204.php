<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212121204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE employee (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, patronymic VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(512) NOT NULL, INDEX IDX_5D9F75A132C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, employee_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date DATE NOT NULL, time_start TIME NOT NULL, time_end TIME NOT NULL, INDEX IDX_3BAE0AA78C03F15C (employee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_employee (event_id INT NOT NULL, employee_id INT NOT NULL, INDEX IDX_1B739C7171F7E88B (event_id), INDEX IDX_1B739C718C03F15C (employee_id), PRIMARY KEY(event_id, employee_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_room (id INT AUTO_INCREMENT NOT NULL, office_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(512) NOT NULL, photo_path VARCHAR(512) NOT NULL, size SMALLINT NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_9E6EA949FFA0C224 (office_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE office (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, INDEX IDX_74516B0232C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organization (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A132C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA78C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_employee ADD CONSTRAINT FK_1B739C7171F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_employee ADD CONSTRAINT FK_1B739C718C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meeting_room ADD CONSTRAINT FK_9E6EA949FFA0C224 FOREIGN KEY (office_id) REFERENCES office (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE office ADD CONSTRAINT FK_74516B0232C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A132C8A3DE');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA78C03F15C');
        $this->addSql('ALTER TABLE event_employee DROP FOREIGN KEY FK_1B739C7171F7E88B');
        $this->addSql('ALTER TABLE event_employee DROP FOREIGN KEY FK_1B739C718C03F15C');
        $this->addSql('ALTER TABLE meeting_room DROP FOREIGN KEY FK_9E6EA949FFA0C224');
        $this->addSql('ALTER TABLE office DROP FOREIGN KEY FK_74516B0232C8A3DE');
        $this->addSql('DROP TABLE employee');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_employee');
        $this->addSql('DROP TABLE meeting_room');
        $this->addSql('DROP TABLE office');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

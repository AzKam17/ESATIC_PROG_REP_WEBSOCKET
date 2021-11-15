<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211110132233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER DEFAULT NULL, sender VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE INDEX IDX_B6BD307F54177093 ON message (room_id)');
        $this->addSql('CREATE TABLE room (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rooms_manager_id INTEGER DEFAULT NULL, lib VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE INDEX IDX_729F519B127BFC5D ON room (rooms_manager_id)');
        $this->addSql('CREATE TABLE rooms_manager (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE rooms_manager');
    }
}

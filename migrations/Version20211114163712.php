<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211114163712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_B6BD307F54177093');
        $this->addSql('CREATE TEMPORARY TABLE __temp__message AS SELECT id, room_id, sender, content, type FROM message');
        $this->addSql('DROP TABLE message');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER DEFAULT NULL, sender VARCHAR(255) NOT NULL COLLATE BINARY, content VARCHAR(255) NOT NULL COLLATE BINARY, type VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_B6BD307F54177093 FOREIGN KEY (room_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO message (id, room_id, sender, content, type) SELECT id, room_id, sender, content, type FROM __temp__message');
        $this->addSql('DROP TABLE __temp__message');
        $this->addSql('CREATE INDEX IDX_B6BD307F54177093 ON message (room_id)');
        $this->addSql('DROP INDEX IDX_729F519B127BFC5D');
        $this->addSql('CREATE TEMPORARY TABLE __temp__room AS SELECT id, rooms_manager_id, lib FROM room');
        $this->addSql('DROP TABLE room');
        $this->addSql('CREATE TABLE room (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rooms_manager_id INTEGER DEFAULT NULL, lib VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_729F519B127BFC5D FOREIGN KEY (rooms_manager_id) REFERENCES rooms_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO room (id, rooms_manager_id, lib) SELECT id, rooms_manager_id, lib FROM __temp__room');
        $this->addSql('DROP TABLE __temp__room');
        $this->addSql('CREATE INDEX IDX_729F519B127BFC5D ON room (rooms_manager_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_B6BD307F54177093');
        $this->addSql('CREATE TEMPORARY TABLE __temp__message AS SELECT id, room_id, sender, content, type FROM message');
        $this->addSql('DROP TABLE message');
        $this->addSql('CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, room_id INTEGER DEFAULT NULL, sender VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO message (id, room_id, sender, content, type) SELECT id, room_id, sender, content, type FROM __temp__message');
        $this->addSql('DROP TABLE __temp__message');
        $this->addSql('CREATE INDEX IDX_B6BD307F54177093 ON message (room_id)');
        $this->addSql('DROP INDEX IDX_729F519B127BFC5D');
        $this->addSql('CREATE TEMPORARY TABLE __temp__room AS SELECT id, rooms_manager_id, lib FROM room');
        $this->addSql('DROP TABLE room');
        $this->addSql('CREATE TABLE room (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rooms_manager_id INTEGER DEFAULT NULL, lib VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO room (id, rooms_manager_id, lib) SELECT id, rooms_manager_id, lib FROM __temp__room');
        $this->addSql('DROP TABLE __temp__room');
        $this->addSql('CREATE INDEX IDX_729F519B127BFC5D ON room (rooms_manager_id)');
    }
}

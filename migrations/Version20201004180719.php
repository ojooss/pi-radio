<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201004180719 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE station ADD COLUMN updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE station ADD COLUMN image_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE station ADD COLUMN image_original_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE station ADD COLUMN image_mime_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE station ADD COLUMN image_size INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE station ADD COLUMN image_dimensions CLOB DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__station AS SELECT id, name, url, sequence_nr FROM station');
        $this->addSql('DROP TABLE station');
        $this->addSql('CREATE TABLE station (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(128) NOT NULL, url VARCHAR(255) NOT NULL, sequence_nr INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO station (id, name, url, sequence_nr) SELECT id, name, url, sequence_nr FROM __temp__station');
        $this->addSql('DROP TABLE __temp__station');
    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Initial setup of DB-tables, with default data for podcasts
 */
class Version20131210210910 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "sqlite", "Migration can only be executed safely on 'sqlite'.");
        
        $this->addSql("CREATE TABLE podcast (id VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description CLOB NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE TABLE podcast_item (id INTEGER NOT NULL, podcast_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, date DATETIME NOT NULL, duration INTEGER DEFAULT NULL, file_url VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_F43FBB52786136AB ON podcast_item (podcast_id)");

        $this->addSql("
            INSERT INTO podcast (id, title, description)
            VALUES ('comm_ogg', 'Open Badges Community Call (Ogg Vorbis)', 'Recording of the weekly Open Badges community call')
        ");
        $this->addSql("
            INSERT INTO podcast (id, title, description)
            VALUES ('comm_mp3', 'Open Badges Community Call (MP3)', 'Recording of the weekly Open Badges community call')
        ");
        $this->addSql("
            INSERT INTO podcast (id, title, description)
            VALUES ('rbsd_ogg', 'Open Badges Research & System Design Call (Ogg Vorbis)', 'Recording of the weekly Open Badges research and system design call')
        ");
        $this->addSql("
            INSERT INTO podcast (id, title, description)
            VALUES ('rbsd_mp3', 'Open Badges Research & System Design Call (MP3)', 'Recording of the weekly Open Badges research and system design call')
        ");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "sqlite", "Migration can only be executed safely on 'sqlite'.");
        
        $this->addSql("DROP TABLE podcast");
        $this->addSql("DROP TABLE podcast_item");
    }
}

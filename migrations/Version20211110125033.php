<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211110125033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates `messenger_messages` table ';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (
                            id BIGSERIAL NOT NULL, 
                            body TEXT NOT NULL, 
                            headers TEXT NOT NULL, 
                            queue_name VARCHAR(190) NOT NULL, 
                            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                            available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                            delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;'
        );

        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');

        $this->addSql('ALTER TABLE comment ALTER state SET DEFAULT \'submitted\'');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');

    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211110130445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates Unique indexs for ´messenger_message´ table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_messenger_messages_queue_name ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_messenger_messages_available_at ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_messenger_messages_delivered_at ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX IDX_messenger_messages_queue_name');
        $this->addSql('DROP INDEX IDX_messenger_messages_available_at');
        $this->addSql('DROP INDEX IDX_messenger_messages_delivered_at');
    }
}

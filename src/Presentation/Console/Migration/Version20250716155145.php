<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250716155145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
        CREATE TABLE message
        (
            id          uuid                           NOT NULL,
            text_hash   text                           NOT NULL,
            created_at  timestamp(0) without time zone NOT NULL,
            valid_until timestamp(0) without time zone NOT NULL,
            PRIMARY KEY (id)
        )
        SQL,
        );
        $this->addSql('CREATE INDEX message_valid_until_idx ON message (valid_until)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE message');
    }
}

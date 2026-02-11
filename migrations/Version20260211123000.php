<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change chapitre.content column to TEXT (allow NULL) to remove JSON constraint';
    }

    public function up(Schema $schema): void
    {
        // Platform-specific SQL for MySQL/MariaDB
        $this->addSql('ALTER TABLE chapitre MODIFY content LONGTEXT NULL');
    }

    public function down(Schema $schema): void
    {
        // Attempt to revert to JSON column if supported; adjust manually if needed.
        $this->addSql('ALTER TABLE chapitre MODIFY content JSON NULL');
    }
}

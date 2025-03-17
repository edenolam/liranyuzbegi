<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250310173835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song ADD cover_image VARCHAR(255) DEFAULT NULL, ADD audio_file VARCHAR(255) DEFAULT NULL, DROP image, DROP music');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE song ADD image VARCHAR(255) DEFAULT NULL, ADD music VARCHAR(255) DEFAULT NULL, DROP cover_image, DROP audio_file');
    }
}

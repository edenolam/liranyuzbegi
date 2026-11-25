<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261125000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the editable workshop announcement for the homepage';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('workshop_announcement');
        $table->addColumn('id', 'integer');
        $table->addColumn('title', 'string', ['length' => 255]);
        $table->addColumn('venue', 'string', ['length' => 255]);
        $table->addColumn('period', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('workshop_announcement');
    }
}

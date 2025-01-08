<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219193637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2512469DE2');
        $this->addSql('ALTER TABLE task ADD attachments VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX fk_527edb2512469de2 ON task');
        $this->addSql('CREATE INDEX IDX_527EDB2512469DE2 ON task (category_id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2512469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2512469DE2');
        $this->addSql('ALTER TABLE task DROP attachments');
        $this->addSql('DROP INDEX idx_527edb2512469de2 ON task');
        $this->addSql('CREATE INDEX FK_527EDB2512469DE2 ON task (category_id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2512469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }
}

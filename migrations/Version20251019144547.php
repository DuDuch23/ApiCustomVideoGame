<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251019144547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CCF1F1BA5E237E06 ON editor (name)');
        $this->addSql('ALTER TABLE user CHANGE subcription_to_newsletter subscription_to_newsletter TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE video_game CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_24BC6C502B36786B ON video_game (title)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_64C19C15E237E06 ON category');
        $this->addSql('DROP INDEX UNIQ_CCF1F1BA5E237E06 ON editor');
        $this->addSql('ALTER TABLE `user` CHANGE subscription_to_newsletter subcription_to_newsletter TINYINT(1) NOT NULL');
        $this->addSql('DROP INDEX UNIQ_24BC6C502B36786B ON video_game');
        $this->addSql('ALTER TABLE video_game CHANGE description description VARCHAR(255) NOT NULL');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241101115354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Implementation of the security part of issue.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX USER_LOGIN_UNIQUE ON `user`');
        $this->addSql('ALTER TABLE `user` ADD `roles` JSON NOT NULL COMMENT "(DC2Type:json)", CHANGE `phone` `phone` VARCHAR(8) NOT NULL, CHANGE `pass` `pass` varchar(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX `UNIQUE_USER_LOGIN` ON `user` (`login`)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX `UNIQUE_USER_LOGIN` ON `user`');
        $this->addSql('ALTER TABLE `user` DROP `roles`, CHANGE `phone` `phone` VARCHAR(8) DEFAULT NULL, CHANGE `pass` `pass` varchar(8) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX `USER_LOGIN_UNIQUE` ON `user` (`login`)');
    }
}

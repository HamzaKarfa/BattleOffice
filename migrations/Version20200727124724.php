<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200727124724 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398C0C74BEC');
        $this->addSql('DROP INDEX UNIQ_F5299398C0C74BEC ON `order`');
        $this->addSql('ALTER TABLE `order` CHANGE delivery_order_id_id delivery_order_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398ECFE8C54 FOREIGN KEY (delivery_order_id) REFERENCES delivery_order (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5299398ECFE8C54 ON `order` (delivery_order_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398ECFE8C54');
        $this->addSql('DROP INDEX UNIQ_F5299398ECFE8C54 ON `order`');
        $this->addSql('ALTER TABLE `order` CHANGE delivery_order_id delivery_order_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398C0C74BEC FOREIGN KEY (delivery_order_id_id) REFERENCES delivery_order (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5299398C0C74BEC ON `order` (delivery_order_id_id)');
    }
}

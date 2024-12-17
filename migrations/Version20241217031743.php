<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217031743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart_items (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, cart_id INTEGER NOT NULL, item_id INTEGER NOT NULL, quantity INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_BEF484451AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BEF48445126F525E FOREIGN KEY (item_id) REFERENCES items (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BEF484451AD5CDBF ON cart_items (cart_id)');
        $this->addSql('CREATE INDEX IDX_BEF48445126F525E ON cart_items (item_id)');
        $this->addSql('CREATE TABLE carts (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user INTEGER NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_4E004AAC8D93D649 FOREIGN KEY (user) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E004AAC8D93D649 ON carts (user)');
        $this->addSql('CREATE TABLE categories (id INTEGER NOT NULL, category_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE installments (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, payment_id INTEGER NOT NULL, installment_no INTEGER NOT NULL, amount NUMERIC(65, 2) NOT NULL, later_fee NUMERIC(65, 2) NOT NULL, due_date DATETIME NOT NULL, paid BOOLEAN NOT NULL, late_fee NUMERIC(65, 2) NOT NULL, CONSTRAINT FK_FE90068C4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FE90068C4C3A3BB ON installments (payment_id)');
        $this->addSql('CREATE TABLE items (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, item_category_id INTEGER NOT NULL, item_code VARCHAR(255) NOT NULL, item_name VARCHAR(255) NOT NULL, item_price NUMERIC(65, 2) NOT NULL, item_image VARCHAR(255) DEFAULT NULL, item_description CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_E11EE94DF22EC5D4 FOREIGN KEY (item_category_id) REFERENCES categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E11EE94DF22EC5D4 ON items (item_category_id)');
        $this->addSql('CREATE TABLE order_histories (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, order_items CLOB NOT NULL --(DC2Type:json)
        , status SMALLINT NOT NULL, total_amount NUMERIC(65, 2) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_7376B55BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7376B55BA76ED395 ON order_histories (user_id)');
        $this->addSql('CREATE TABLE payments (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, order_history_id INTEGER NOT NULL, payment_method NUMERIC(65, 2) NOT NULL, status SMALLINT NOT NULL, paid_at DATETIME NOT NULL, created_at DATETIME NOT NULL, CONSTRAINT FK_65D29B32ADDF7907 FOREIGN KEY (order_history_id) REFERENCES order_histories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_65D29B32ADDF7907 ON payments (order_history_id)');
        $this->addSql('CREATE TABLE transactions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, payment_id INTEGER DEFAULT NULL, installment_id INTEGER DEFAULT NULL, transaction_for SMALLINT NOT NULL, amount VARCHAR(255) NOT NULL, bank_code VARCHAR(255) NOT NULL, bank_tran_no VARCHAR(255) NOT NULL, card_type VARCHAR(255) NOT NULL, order_info VARCHAR(255) NOT NULL, pay_date VARCHAR(255) NOT NULL, response_code VARCHAR(255) NOT NULL, tmn_code VARCHAR(255) NOT NULL, transaction_no VARCHAR(255) NOT NULL, transaction_status VARCHAR(255) NOT NULL, txn_ref VARCHAR(255) NOT NULL, secure_hash VARCHAR(255) NOT NULL, CONSTRAINT FK_EAA81A4C4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EAA81A4CF03B5436 FOREIGN KEY (installment_id) REFERENCES installments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EAA81A4C4C3A3BB ON transactions (payment_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EAA81A4CF03B5436 ON transactions (installment_id)');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, status SMALLINT NOT NULL, credit_limit NUMERIC(65, 2) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON users (username)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cart_items');
        $this->addSql('DROP TABLE carts');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE installments');
        $this->addSql('DROP TABLE items');
        $this->addSql('DROP TABLE order_histories');
        $this->addSql('DROP TABLE payments');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240831184115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE api_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE empresa_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE endereco_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE pessoa_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE api_token (id INT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7BA2F5EB5F37A13B ON api_token (token)');
        $this->addSql('CREATE INDEX IDX_7BA2F5EBA76ED395 ON api_token (user_id)');
        $this->addSql('CREATE TABLE empresa (id INT NOT NULL, nome VARCHAR(255) NOT NULL, nome_fantasia VARCHAR(255) NOT NULL, cnpj VARCHAR(14) NOT NULL, razao_social VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8D75A50C8C6906B ON empresa (cnpj)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8D75A5094787B27 ON empresa (razao_social)');
        $this->addSql('CREATE TABLE endereco (id INT NOT NULL, empresa_id INT NOT NULL, cep VARCHAR(8) NOT NULL, logradouro VARCHAR(255) NOT NULL, numero VARCHAR(255) NOT NULL, complemento VARCHAR(255) DEFAULT NULL, cidade VARCHAR(255) NOT NULL, uf VARCHAR(2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F8E0D60E521E1991 ON endereco (empresa_id)');
        $this->addSql('CREATE TABLE pessoa (id INT NOT NULL, nome VARCHAR(255) NOT NULL, sexo VARCHAR(1) NOT NULL, data_nascimento DATE NOT NULL, cpf VARCHAR(11) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1CDFAB823E3E11F0 ON pessoa (cpf)');
        $this->addSql('CREATE TABLE pessoa_empresa (pessoa_id INT NOT NULL, empresa_id INT NOT NULL, PRIMARY KEY(pessoa_id, empresa_id))');
        $this->addSql('CREATE INDEX IDX_DE556A67DF6FA0A5 ON pessoa_empresa (pessoa_id)');
        $this->addSql('CREATE INDEX IDX_DE556A67521E1991 ON pessoa_empresa (empresa_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, pessoa_id INT DEFAULT NULL, credential VARCHAR(180) NOT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649DF6FA0A5 ON "user" (pessoa_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_CREDENTIAL ON "user" (credential)');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EBA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE endereco ADD CONSTRAINT FK_F8E0D60E521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pessoa_empresa ADD CONSTRAINT FK_DE556A67DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pessoa_empresa ADD CONSTRAINT FK_DE556A67521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql(sprintf("INSERT INTO \"user\" VALUES (1, null, 'admin', true, now(), null, '%s', '%s')", '$2y$13$W428ccn4.2lcG2j/Ip.WEuPwjavB0LMa7b0XlFE4FqcinC3x/dnZu', '["ROLE_ADMIN"]'));
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE api_token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE empresa_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE endereco_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE pessoa_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE api_token DROP CONSTRAINT FK_7BA2F5EBA76ED395');
        $this->addSql('ALTER TABLE endereco DROP CONSTRAINT FK_F8E0D60E521E1991');
        $this->addSql('ALTER TABLE pessoa_empresa DROP CONSTRAINT FK_DE556A67DF6FA0A5');
        $this->addSql('ALTER TABLE pessoa_empresa DROP CONSTRAINT FK_DE556A67521E1991');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649DF6FA0A5');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('DROP TABLE empresa');
        $this->addSql('DROP TABLE endereco');
        $this->addSql('DROP TABLE pessoa');
        $this->addSql('DROP TABLE pessoa_empresa');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

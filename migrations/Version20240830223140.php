<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240830223140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE empresa_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE endereco_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE pessoa_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE socio_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE empresa (id INT NOT NULL, nome VARCHAR(255) NOT NULL, nome_fantasia VARCHAR(255) NOT NULL, cnpj VARCHAR(14) NOT NULL, razao_social VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE endereco (id INT NOT NULL, empresa_id INT NOT NULL, cep VARCHAR(8) NOT NULL, logradouro VARCHAR(255) NOT NULL, numero VARCHAR(255) NOT NULL, complemento VARCHAR(255) DEFAULT NULL, cidade VARCHAR(255) NOT NULL, uf VARCHAR(2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F8E0D60E521E1991 ON endereco (empresa_id)');
        $this->addSql('CREATE TABLE pessoa (id INT NOT NULL, nome VARCHAR(255) NOT NULL, sexo VARCHAR(1) NOT NULL, data_nascimento DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE socio (id INT NOT NULL, pessoa_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_38B65309DF6FA0A5 ON socio (pessoa_id)');
        $this->addSql('CREATE TABLE socio_empresa (socio_id INT NOT NULL, empresa_id INT NOT NULL, PRIMARY KEY(socio_id, empresa_id))');
        $this->addSql('CREATE INDEX IDX_9C2F5003DA04E6A9 ON socio_empresa (socio_id)');
        $this->addSql('CREATE INDEX IDX_9C2F5003521E1991 ON socio_empresa (empresa_id)');
        $this->addSql('ALTER TABLE endereco ADD CONSTRAINT FK_F8E0D60E521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE socio ADD CONSTRAINT FK_38B65309DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE socio_empresa ADD CONSTRAINT FK_9C2F5003DA04E6A9 FOREIGN KEY (socio_id) REFERENCES socio (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE socio_empresa ADD CONSTRAINT FK_9C2F5003521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD pessoa_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP name');
        $this->addSql('ALTER TABLE "user" ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649DF6FA0A5 ON "user" (pessoa_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649DF6FA0A5');
        $this->addSql('DROP SEQUENCE empresa_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE endereco_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE pessoa_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE socio_id_seq CASCADE');
        $this->addSql('ALTER TABLE endereco DROP CONSTRAINT FK_F8E0D60E521E1991');
        $this->addSql('ALTER TABLE socio DROP CONSTRAINT FK_38B65309DF6FA0A5');
        $this->addSql('ALTER TABLE socio_empresa DROP CONSTRAINT FK_9C2F5003DA04E6A9');
        $this->addSql('ALTER TABLE socio_empresa DROP CONSTRAINT FK_9C2F5003521E1991');
        $this->addSql('DROP TABLE empresa');
        $this->addSql('DROP TABLE endereco');
        $this->addSql('DROP TABLE pessoa');
        $this->addSql('DROP TABLE socio');
        $this->addSql('DROP TABLE socio_empresa');
        $this->addSql('DROP INDEX UNIQ_8D93D649DF6FA0A5');
        $this->addSql('ALTER TABLE "user" ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP pessoa_id');
        $this->addSql('ALTER TABLE "user" ALTER updated_at SET NOT NULL');
    }
}

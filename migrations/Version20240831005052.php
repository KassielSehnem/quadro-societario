<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240831005052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE socio_id_seq CASCADE');
        $this->addSql('CREATE TABLE pessoa_empresa (pessoa_id INT NOT NULL, empresa_id INT NOT NULL, PRIMARY KEY(pessoa_id, empresa_id))');
        $this->addSql('CREATE INDEX IDX_DE556A67DF6FA0A5 ON pessoa_empresa (pessoa_id)');
        $this->addSql('CREATE INDEX IDX_DE556A67521E1991 ON pessoa_empresa (empresa_id)');
        $this->addSql('ALTER TABLE pessoa_empresa ADD CONSTRAINT FK_DE556A67DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pessoa_empresa ADD CONSTRAINT FK_DE556A67521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE socio_empresa DROP CONSTRAINT fk_9c2f5003da04e6a9');
        $this->addSql('ALTER TABLE socio_empresa DROP CONSTRAINT fk_9c2f5003521e1991');
        $this->addSql('ALTER TABLE socio DROP CONSTRAINT fk_38b65309df6fa0a5');
        $this->addSql('DROP TABLE socio_empresa');
        $this->addSql('DROP TABLE socio');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7BA2F5EB5F37A13B ON api_token (token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8D75A50C8C6906B ON empresa (cnpj)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8D75A5094787B27 ON empresa (razao_social)');
        $this->addSql('ALTER TABLE pessoa ADD cpf VARCHAR(11) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1CDFAB823E3E11F0 ON pessoa (cpf)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE socio_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE socio_empresa (socio_id INT NOT NULL, empresa_id INT NOT NULL, PRIMARY KEY(socio_id, empresa_id))');
        $this->addSql('CREATE INDEX idx_9c2f5003521e1991 ON socio_empresa (empresa_id)');
        $this->addSql('CREATE INDEX idx_9c2f5003da04e6a9 ON socio_empresa (socio_id)');
        $this->addSql('CREATE TABLE socio (id INT NOT NULL, pessoa_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_38b65309df6fa0a5 ON socio (pessoa_id)');
        $this->addSql('ALTER TABLE socio_empresa ADD CONSTRAINT fk_9c2f5003da04e6a9 FOREIGN KEY (socio_id) REFERENCES socio (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE socio_empresa ADD CONSTRAINT fk_9c2f5003521e1991 FOREIGN KEY (empresa_id) REFERENCES empresa (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE socio ADD CONSTRAINT fk_38b65309df6fa0a5 FOREIGN KEY (pessoa_id) REFERENCES pessoa (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pessoa_empresa DROP CONSTRAINT FK_DE556A67DF6FA0A5');
        $this->addSql('ALTER TABLE pessoa_empresa DROP CONSTRAINT FK_DE556A67521E1991');
        $this->addSql('DROP TABLE pessoa_empresa');
        $this->addSql('DROP INDEX UNIQ_7BA2F5EB5F37A13B');
        $this->addSql('DROP INDEX UNIQ_B8D75A50C8C6906B');
        $this->addSql('DROP INDEX UNIQ_B8D75A5094787B27');
        $this->addSql('DROP INDEX UNIQ_1CDFAB823E3E11F0');
        $this->addSql('ALTER TABLE pessoa DROP cpf');
    }
}

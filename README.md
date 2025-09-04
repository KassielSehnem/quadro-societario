# API para Quadro Societário
Demonstração básica de uma API feita com PHP e Symfony, para ser consumida por qualquer front-end.

## Como executar
Para executar o projeto, será necessário seguir alguns pré-requisitos:

### Pré-requisitos
Você precisará de:

```
PHP 8.1+, Symfony CLI e Docker ou PostgreSQL
```

### Instalação
Após garantir que tenha tudo acima, você deverá executar o seguinte comando na pasta do projeto:

```
symfony composer install
```

Após a conclusão, se você for utilizar o Docker para o banco de dados, rode o comando:

```
docker compose up
```

Caso esteja utilizando o PostgreSQL na sua máquina, será necessário modificar o DATABASE_URL no arquivo .env para apontar para o mesmo e deverá ser criado um novo banco de dados.
Após o banco de dados estar configurado e rodando, execute o comando na pasta do projeto:

```
symfony console doctrine:migrations:migrate
```

Este comando vai criar todas as tabelas e relações necessárias para o projeto funcionar.

## Testando a API
A API necessita de um Token para cada requisição feita, então precisamos gerar esse Token para ser utilizado. Será necessário fazer uma requisição POST para a rota /api/login com o seguinte corpo:

```
{
    "username": "your-user-name",
    "password": "your-password"
}
```

Para facilitar os testes, um usuário foi criado automaticamente, com o usuário e senha "admin".

Após receber o seu Token de autenticação, lembre-se de incluí-lo no header de todas as suas requisições, com a Key do Header sendo:

```
X-AUTH-TOKEN
```

E o Value sendo o Token recebido.

### Rotas

As rotas a serem utilizadas da API estão documentadas na rota:

```
GET /api/doc
```
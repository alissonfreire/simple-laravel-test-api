### Resumo do foi implementado

- Setup da aplicação (Dockerfile, docker-compose.yml e ngnix.conf)
- Pipeline de CI usando GitHub Actions
- Rotas de autenticação usando Laravel Sanctum & feature tests
- Documentação usando Swagger
- Uso do repository pattern & service para o CRUD dos todos

### Como executar

> É necessário ter o docker e docker-compose instalado

Execute os seguintes comandos:

```bash
# clone o repositório
git clone https://github.com/alissonfreire/simple-laravel-test-api.git

cd simple-laravel-test-api

# execute os containers
docker compose up -d

# setup das variaveis e do banco
cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

E basta acessar `http://localhost/api/documentation`

### Criar o banco caso ele não exista
Para criar o banco (`simple_test_api`) basta acessar phpmyadmin(`http://localhost/8081`) com usuário `default` e senha `secret`
Crie o banco entre nele execute o sql:

```sql
GRANT ALL PRIVILEGES ON simple_test_api.* TO 'deafult'@'%';
```

### Para rodar os testes:

```bash
docker compose exec app php artisan test
```

# UsoApp - API REST em PHP 8.3 Puro

API em PHP 8.3

---

## Estrutura Criada

```text
project/
│
├── public/
│   └── index.php
│
├── config/
│   └── config.php
│
├── routes/
│   └── api.php
│
├── src/
│   ├── Controllers/
│   │   └── TesteController.php
│   ├── Services/
│   │   
│   ├── Models/
│   │   
│   ├── Repositories/
│   │   
│   ├── Middleware/
│   │   
│   ├── Http/
│   │   ├── Router.php
│   │   ├── Request.php
│   │   ├── Route.php
│   │   └── Response.php
│   └── Utils/
│       └── HttpClient.php
│
├── tests/
│   ├── Services/
│   │   
│   ├── Controllers/
│   │   └── TesteControllerTest.php
│   └── Repositories/
│
├── docker/
│   └── nginx/
│       └── default.conf
├── .env
├── .env.example
├── composer.json
├── phpunit.xml
├── Dockerfile
└── docker-compose.yml
```

---

## Principais Componentes Implementados

1. **`public/index.php`**: Ponto único de entrada responsável por carregar o Composer, `.env`, configurações, roteador e executar a requisição.
2. **`src/Http/Request.php`**: Encapsula parâmetros de query, headers, body, verbos HTTP e JSON payload.
3. **`src/Http/Response.php`**: Manipula respostas HTTP com envio facilitado de JSON via `Response::json($data, $status)`.
4. **`src/Http/Router.php` & `Route.php`**: Roteador com suporte a verbos HTTP (`GET`, `POST`, `PUT`, `DELETE`) e suporte a encadeamento de middlewares via `->middleware(...)`.
5. **`src/Controllers/TesteController.php`**: Controller simples tratando a rota `/teste`.
6. **`routes/api.php`**: Registro de rota.
7. **`src/Utils/HttpClient.php`**: Abstração sobre o Guzzle (`GuzzleHttp\Client`) para futuras requisições a APIs externas.
8. **Configuração para Banco Externo**: `.env` e `config/config.php` configurados para ler credenciais sem subir container de banco no Docker.
9. **Infraestrutura Docker**: Containers `php` (PHP 8.3-FPM) e `nginx` servindo a porta `8080`.
10. **PHPUnit**: Suíte de testes configurada com script no `composer.json` (`composer test`).

---

## Como Executar

### 1. Iniciar os Containers Docker

```bash
docker compose up -d --build
```

### 2. Instalar Dependências do Composer (primeira execução)

```bash
docker compose exec php composer install
```

### 3. Testar a Rota GET `/teste`

```bash
curl http://localhost:8080/teste
```

**Resposta esperada:**

```json
{
    "success": true,
    "message": "API funcionando"
}
```

### 4. Executar os Testes Automatizados

```bash
# UsoApp - API REST em PHP 8.3

API legada em migração gradual para uma estrutura organizada por rotas, middlewares, controllers, services e repositories. O projeto preserva as URLs e os contratos de resposta existentes e usa BigQuery e bancos externos quando necessário.

## Stack

- PHP 8.3 com PHP-FPM
- Nginx Alpine
- Composer e autoload PSR-4
- Guzzle para chamadas HTTP externas
- Dotenv para configuração por ambiente
- PHPUnit 10.5 para testes
- Swagger/OpenAPI para documentação
- BigQuery para consultas de indicadores

## Estrutura

```text
public/index.php       Bootstrap e ponto de entrada HTTP
routes/api.php         Registro das rotas da aplicação
config/config.php      Configurações carregadas do ambiente
src/Http               Request, Response, Router, Route e Container
src/Middleware         Autenticação, CORS e contratos de middleware
src/Controllers        Controllers HTTP, incluindo BigQuery e Swagger
src/Services           Regras de negócio
src/Repositories       BigQuery e persistência no banco externo
src/Utils              Utilitários, como o cliente HTTP e validação de token
public/openapi.json    Especificação OpenAPI publicada
tests/                 Testes unitários e de integração por camada
docker/                Configuração do Nginx
```

## Fluxo da aplicação

```text
public/index.php
    -> Request
    -> Router
    -> Middleware
    -> Controller
    -> Service
    -> Repository / API externa
    -> Response
```

O bootstrap carrega o autoload do Composer, lê o `.env`, cria o container e despacha a requisição pelo roteador. A autenticação das rotas protegidas é centralizada no `AuthMiddleware`.

## Rotas disponíveis

| Método | Rota | Estado | Autenticação |
| --- | --- | --- | --- |
| GET | `/teste` | Health check da API | Não |
| GET | `/swagger` | Swagger UI | Não |
| GET | `/APIv3/bigquery/orders` | Controller BigQuery | `Auth` ou `Authorization` |
| POST | `/APIv3/bigquery/consulta/indicadores-chats-finalizados/{empresa}` | Fluxo de indicadores em migração | `Auth` ou `Authorization` |
| POST | `/APIv3/bigquery/consulta/rechamadasNps/{empresa}` | Consulta de rechamadas e NPS migrada | `Auth` ou `Authorization` |
| POST | `/APIv3/bigquery/consulta/bd-nps/{empresa}` | Placeholder legado | `Auth` ou `Authorization` |
| POST | `/APIv3/bigquery/consulta/resposta-sms/{empresa}` | Placeholder legado | `Auth` ou `Authorization` |
| POST | `/APIv3/bigquery/consulta/helpdesk/{empresa}` | Placeholder legado | `Auth` ou `Authorization` |
| POST | `/APIv3/bigquery/consulta/status-meta/{empresa}` | Placeholder legado | `Auth` ou `Authorization` |

Os detalhes formais dos endpoints estão em [`public/openapi.json`](public/openapi.json) e podem ser visualizados em `http://localhost:8080/swagger` após iniciar a aplicação.

## Autenticação

As rotas protegidas validam o token legado usando a configuração `tokens_api[$caso][$empresa]`. O header preferencial é:

```http
Auth: seu-token
```

`Authorization` também é aceito como fallback:

```http
Authorization: Bearer seu-token
```

Quando o token está ausente ou inválido, a API responde HTTP `401` com `WWW-Authenticate: Bearer`.

## Indicadores de chats finalizados

O fluxo está organizado da seguinte forma:

```text
ConsultaController
    -> IndicadoresChatService
    -> BigQueryRepository
    -> BigQuery

IndicadoresChatService
    -> IndicadoresRepository
    -> Banco externo
```

As etapas já migradas incluem validação da entrada, extração do request, resolução da configuração Google Cloud, consulta dos indicadores no BigQuery, inclusão de `dados_adicionais`, montagem do body final, resolução do banco, validação de `ID_CHAT`, verificação de duplicidade, preparação e execução do `INSERT` com prepared statement.

Regras preservadas do legado:

- URLs e contratos de resposta não foram alterados.
- O banco de dados continua externo; nenhum banco é criado pelo Docker Compose.
- O limite permanece em 10 indicadores.
- `ID_CHAT` é único para a inserção.
- Conflitos usam `error_code` `409`.
- Credenciais e tokens devem permanecer no `.env`, nunca no README ou no código versionado.

## Rechamadas e NPS

O endpoint `POST /APIv3/bigquery/consulta/rechamadasNps/{empresa}` foi migrado incrementalmente para o fluxo:

```text
ConsultaController
    -> RechamadasNpsService
    -> BigQueryRepository
    -> BigQuery
```

O `ConsultaController` recebe o body e o parâmetro `emp` (ou a empresa da rota), enquanto o `RechamadasNpsService` preserva a sequência da função legada: valida o corpo, extrai `cliente`, `numero` e `documento`, valida a empresa, resolve a configuração Google Cloud, monta a query, consulta o BigQuery e monta a resposta.

As quatro combinações originais de filtro por `numero` e `documento` foram preservadas. Os valores são enviados ao `BigQueryRepository` como parâmetros nomeados, sem interpolação direta no SQL.

Há cobertura unitária para a validação de corpo e para a consulta com parâmetros nomeados em [`tests/Services/BigQuery/RechamadasNpsServiceTest.php`](tests/Services/BigQuery/RechamadasNpsServiceTest.php).

## Configuração

1. Copie o arquivo de exemplo:

   ```bash
   cp .env.example .env
   ```

2. Preencha no `.env` as credenciais necessárias para o ambiente:

   - `APP_NAME`, `APP_ENV` e `APP_DEBUG`
   - tokens em `TOKEN_REQ_*`
   - autenticação do Google Cloud em `GOOGLE_BQ_*`
   - conexão com bancos externos em `DB_*`
   - integrações Huggy, Gupshup, Zenvia, RabbitMQ e Alares, conforme o fluxo utilizado

O arquivo [`config/config.php`](config/config.php) centraliza a leitura dessas variáveis. O `.env`.

## Execução com Docker

Na primeira execução, suba os containers e instale as dependências:

```bash
docker compose up -d --build
docker compose exec php composer install
```

O Nginx publica a API em `http://localhost:8080`. O serviço `php` usa PHP 8.3-FPM, e o código local é montado em `/var/www/html` nos dois containers.

Verifique a aplicação:

```bash
curl http://localhost:8080/teste
```

Resposta esperada:

```json
{
    "success": true,
    "message": "API funcionando"
}
```

Para parar os containers:

```bash
docker compose down
```

## Testes

Execute toda a suíte dentro do container PHP:

```bash
docker compose exec php composer test
```

Ou, com PHP e dependências instalados localmente:

```bash
composer test
```

O PHPUnit descobre os testes no diretório `tests/` conforme [`phpunit.xml`](phpunit.xml).

## OpenAPI

O Swagger UI está disponível em `/swagger` e lê a especificação de [`public/openapi.json`](public/openapi.json). Para regenerar o arquivo a partir dos atributos OpenAPI dos controllers:

```bash
docker compose exec php vendor/bin/openapi src -o public/openapi.json
```


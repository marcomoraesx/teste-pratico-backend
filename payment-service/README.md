
# Multigateways Payment API (Laravel 12)

Este repositório contém a aplicação `payment-service`, uma API construída em Laravel 12 preparada para uso em ambientes Docker (Sail) e desenvolvida com enfoque em TDD. É uma aplicação de nível 3 (pleno/sênior) que implementa um fluxo de vendas com múltiplos produtos, gateways de pagamento com autenticação e um sistema de roles de usuários.

## Visão geral do negócio

- O sistema registra vendas compostas por múltiplos produtos e quantidades.
- O valor da compra é calculado automaticamente a partir dos produtos selecionados e suas quantidades.
- Gateways de pagamento são tratados como provedores externos com autenticação e integração via classes de provider.
- Transações são criadas para pagamentos por cartão (crédito/débito) e vinculadas a vendas.

Roles de usuários e permissões (escopo do negócio):

- ADMIN: pode realizar todas as operações (CRUD completo, reembolsos, gestão de gateways).
- MANAGER: gerencia produtos e usuários.
- FINANCE: gerencia produtos e pode realizar reembolsos.
- USER: ações não cobertas acima.

O projeto foi desenvolvido com Test-Driven Development (TDD) — existe uma suíte de testes com Pest/PHPUnit cobrindo controllers e serviços.

## Arquitetura e componentes principais

- Laravel 12 como framework.
- Serviços (Service classes) encapsulam a lógica de negócio: UserService, ProductService, GatewayService, SaleService, etc.
- Providers de pagamento (ex.: `App\Services\Providers\PagSeguroGatewayProvider`) seguem uma interface/contrato e são registrados dinamicamente para orquestração de pagamento.
- Docker Compose (Sail) com containers para a aplicação, banco MySQL e serviços de mock para gateways quando necessário.

## Requisitos

- **Docker e Docker Compose** instalados na máquina de desenvolvimento.
- **Acesso à internet** para baixar imagens e dependências (na primeira execução).

> **Observações por sistema operacional:**
>
> - **Windows:** é recomendado utilizar o WSL e instalar o Docker/Docker Compose dentro da distribuição Linux, pois o Laravel Sail funciona melhor nesse ambiente. Um guia rápido para isso pode ser encontrado [aqui](https://gist.github.com/dehsilvadeveloper/c3bdf0f4cdcc5c177e2fe9be671820c7).  
>   Caso não queira usar WSL, é possível instalar Docker e Docker Compose diretamente no Windows, mas será necessário executar os comandos do projeto dentro dos containers, o que pode dificultar o processo de análise.
>
> - **Linux:** basta ter Docker e Docker Compose instalados; todos os comandos do projeto funcionarão normalmente.

## Instalação e execução (comandos)

1. Clone o repositório
```bash
git clone https://github.com/marcomoraesx/teste-pratico-backend.git
cd payment-service
```

2. Copiar as variáveis de ambiente
```bash
cp .env.example .env
```

3. Instale dependências PHP com Composer via container (recomendado, evita divergências locais):

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_PROCESS_TIMEOUT=0 \
    -e COMPOSER_MAX_PARALLEL_HTTP=1 \
    composer:latest \
    composer install --ignore-platform-reqs --prefer-dist --no-progress
```

4. Alias útil para Sail (válido apenas na sessão atual do terminal):

```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

5. Subir containers com Sail/Docker Compose:

```bash
./vendor/bin/sail up -d
```

6. Executar migrations/seeders (dentro do container):

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

7. Rodar a suíte de testes (dentro do container):

```bash
./vendor/bin/sail test
```

8. Parar containers com Sail/Docker compose:

```bash
./vendor/bin/sail stop
```

Observação: para comandos alternativos que usem `sail` alias criado, substitua `./vendor/bin/sail` por `sail` na sessão atual.

## Estrutura de rotas e documentação

- Todas as rotas estão definidas em `routes/api.php` e agrupadas por recursos: `auth`, `users`, `products`, `gateways`, `customers`, `sales`.
- A Postman Collection (`payment-service/teste-pratico-backend-collection.postman_collection.json`) e também sua Postman Environment (`payment-service/teste-pratico-backend-environment.postman_environment.json`) contém com todos os endpoints, onde a aba "Docs" de cada endpoint traz a sua descrição completa, parâmetros e exemplos de resposta.

Principais endpoints (resumo):

- POST /api/auth/login — autenticação
- POST /api/users — registro de usuário (permissão)
- GET /api/products/list — listagem paginada de produtos
- POST /api/products — criar produto (permissão)
- PATCH /api/gateways/{gateway_id}/change-priority — alterar prioridade do gateway
- PATCH /api/gateways/{gateway_id}/activate-or-deactivate — ativar/desativar gateway
- GET /api/customers/list — lista de clientes
- POST /api/sales — registrar venda (acesse docs para payload completo)
- PATCH /api/sales/{sale_id}/refund — efetuar reembolso

Consulte a Postman Collection para a lista completa com descrições e exemplos.

## Gateways de pagamento

- Cada gateway tem uma classe implementando um contrato/inteface (`App\Contracts\PaymentGatewayInterface`) e oferece métodos como `authenticate()`, `process()` e `refund()`.
- O provider central (`PaymentServiceProvider`) busca gateways ativos no banco (`gateways`), instancia as classes e compõe um `PaymentProcessor` que tenta processar pagamentos na ordem de prioridade.
- Autenticação e reuso de token são responsabilidade do provider do gateway.

Maior desafio enfrentado:

- Configurar pacotes e integrações (ex.: Spatie permissions). A solução veio lendo cuidadosamente as documentações oficiais e seguindo padrões do Laravel.

Como adicionar um novo gateway (passos mínimos):

1. Criar a classe de integração do gateway em `App\Services\Providers`, implementando o contrato em `App\Contracts\PaymentGatewayInterface`.
2. Adicionar credenciais/configuração no `.env` e `config/services.php`.
3. Persistir uma entrada na tebela `gateways` do MySQL, apontando o `class_name` (ex.: `MyGatewayProvider`) e marcar `is_active` como `true`.
4. O `PaymentServiceProvider` irá instanciar automaticamente a implementação na execução.

## Foco em pagamentos por cartão (crédito/débito)

- O fluxo implementado cria uma transação quando o método de pagamento for `CREDIT_CARD` ou `DEBIT_CARD`.
- A integração com gateway retorna um identificador externo (`external_transaction_id`) que é persistido em `transactions`.
- Reembolsos acionam o provider do gateway para realizar a operação externa e atualizam o status da transação e da venda internamente.

## Possíveis melhorias

- Aceitar múltiplas formas de pagamento numa mesma venda, gerando múltiplas transações (split payment).
- Melhorar resiliência: hoje, se todos os gateways falharem, o comportamento é tratado, mas se houver falha parcial durante o processo (ex.: gateway já solicitado para debitar o valor para o cliente), seria necessário garantir que o total não fosse debitado novamente, o uso de uma estrutura baseadas em eventos seria o ideal para resolver esse problema.
- Adicionar monitoramento e retry para comunicações com gateways externos.
- Cobertura de testes adicional para fluxos concorrentes e cargas elevadas.

## Observações sobre desenvolvimento e TDD

- O projeto foi construído com testes automatizados (Pest + PHPUnit) cobrindo controllers e services. Sempre que possível, novos recursos devem ter testes unitários e de integração.
- Para escrever testes que impliquem chamadas a gateways externos, use mocks ou providers de teste. Há exemplos em `tests/Unit` que mostram como mockar `PaymentService` e como bindar um provider fake no container para evitar requests HTTP reais.

## Notas sobre Docker/Sail

- A imagem do PHP/Composer é usada para instalação de dependências localmente via container, evitando diferenças na máquina do desenvolvedor e não forçando a instalação do Composer na máquina.
- Sail (Docker Compose) orquestra serviços com default: app (PHP), database (MySQL) e, adicionado manualmente, containers de mock para gateways.
- Se usar a máquina local sem Docker, garanta PHP, Composer e MySQL compatíveis com as versões requeridas pelo projeto.

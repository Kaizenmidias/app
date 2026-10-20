# F01 — Harness HTTP real

Componentes:

- `tests/Support/RealHttp/HttpServer.php`: servidor PHP temporário em loopback, porta configurável e quatro workers solicitados no Linux.
- `tests/Support/RealHttp/HttpClient.php`: cliente cURL com cookie jar próprio, CSRF e login real.
- `tests/Support/RealHttp/FixtureManager.php`: fixtures identificáveis e limpeza restrita ao usuário criado pela execução.
- `tests/Support/real-http-f01.php`: coordenação do servidor, autenticação sequencial, barreira e dois processos independentes.
- `tests/Support/real-http-f01-worker.php`: autenticação própria e POST real de reordenação.

Execução futura, somente no workspace isolado:

```sh
php tests/Support/real-http-f01.php
```

O runner exige `APP_ENV=testing`, `MYSQL_TEST_ENABLED=true`, valida `TestingTargetGuard` e rejeita execução fora de um caminho terminado em `testing`. Não executa migrations, workers persistentes ou scraping.

Cada worker usa cookie jar separado, aguarda arquivos `barrier.ready.N`, recebe `barrier.go` com timeout de dez segundos e chama `POST /pipeline/stages/reorder` com `_token` e uma sequência completa de IDs. Falhas de autenticação, redirects para `/login`, timeout, exit code diferente de zero ou posições finais inválidas fazem a execução falhar.

A barreira demonstra clientes independentes liberados juntos e registra PID, status, duração e Location. Ela não prova sozinha sobreposição interna das transações MySQL; essa propriedade permanece inconclusiva sem instrumentação do servidor.

Os cenários de exclusão versus movimentação, exclusão versus criação, isolamento entre usuários e retry esgotado permanecem pendentes.

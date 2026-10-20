# Valida??o MySQL 8.4 isolada

A su?te SQLite continua sendo a valida??o padr?o local. Os testes MySQL s? s?o habilitados quando `APP_ENV=testing`, `MYSQL_TEST_ENABLED=true` e `DB_DATABASE` coincide com `MYSQL_TEST_DATABASE`. Os nomes `kaizen_partners`, `production` e `prod` s?o bloqueados.

Configure vari?veis fora do reposit?rio, por exemplo `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=kaizen_partners_test`, `MYSQL_TEST_DATABASE=kaizen_partners_test`, `DB_USERNAME` e `DB_PASSWORD`. O banco deve ser criado exclusivamente para testes e nunca reutilizar credenciais de produ??o.

Execute migrations somente nesse banco isolado e depois `php artisan test --filter=MySqlConcurrencyTest`. A su?te verifica a constraint UNIQUE e `lockForUpdate`; os cen?rios com dois processos devem usar o worker auxiliar e processos separados. SQLite n?o comprova locks InnoDB.

Antes de remover o ambiente, confirme novamente `APP_ENV=testing`, o nome permitido e o host de testes. N?o use `migrate:fresh` sem confirma??o expl?cita do banco de testes.

## Compose e migrations protegidas

O Compose exige as vari?veis externas `MYSQL_TEST_PASSWORD` e `MYSQL_TEST_ROOT_PASSWORD`; sem elas ele falha. Use um arquivo local ignorado pelo Git ou vari?veis da sess?o.

Antes de migrations, configure exatamente `APP_ENV=testing`, `MYSQL_TEST_ENABLED=true`, `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=13306`, `DB_DATABASE=kaizen_partners_test`, `MYSQL_TEST_DATABASE=kaizen_partners_test` e `DB_USERNAME=kaizen_test`. O comando `php artisan testing:migrate` valida essas vari?veis, consulta a identidade efetiva (`DATABASE()`, `CURRENT_USER()`, host e porta) e bloqueia qualquer diverg?ncia. Ele nunca aceita o banco de produ??o.

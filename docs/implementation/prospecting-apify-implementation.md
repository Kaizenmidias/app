# Implementação da prospecção Apify

Checklist executivo derivado da auditoria de funcionamento da rota `/prospecting`.

## Escopo e segurança

- [x] Auditoria do fluxo atual concluída.
- [x] Contrato do actor `compass/crawler-google-places` conferido na documentação oficial.
- [x] Estado da produção e dos workers inspecionado sem alteração operacional.
- [x] Nenhuma credencial, token, lead real ou dump será versionado.
- [x] Harness HTTP legado separado do pacote de runtime.

## Fase 0 — estabilização operacional

- [x] Corrigir o scheduler da aplicação para executar `scraper:reconcile`.
- [x] Unificar a fonte de verdade da capacidade de execução.
- [x] Reconciliar contadores de capacidade com jobs realmente ativos.
- [x] Reiniciar workers de forma controlada após troca de release.
- [x] Impedir submissões duplicadas simultâneas por usuário.
- [x] Classificar e encerrar jobs órfãos sem apagar dados de leads.

## Fase 1 — contrato único da API

- [x] Tornar `/api/prospecting/jobs` o único fluxo do frontend.
- [x] Remover dependência funcional de `/api/v1/jobs`.
- [x] Validar termo, localização, quantidade `20|50|100` e lista do próprio usuário.
- [x] Enviar ao actor apenas os campos necessários.
- [x] Remover fallback silencioso para Gosom.
- [x] Preservar CSRF e isolamento por usuário.

## Fase 2 — submissão e estado durável

- [x] Responder `202` após persistir o job, sem chamada externa síncrona.
- [x] Implementar estados operacionais `pending`, `working`, `importing`, `refilling`, `partial` e `failed`.
- [x] Registrar cada run Apify em uma tentativa própria (migration 000011 aplicada).
- [x] Implementar correlação e reconciliação de submissões ambíguas.
- [x] Implementar cancelamento real da run externa.

## Fase 3 — importação e deduplicação

- [x] Preservar a chave única por usuário e Place ID.
- [x] Tornar a importação idempotente sob concorrência.
- [x] Registrar candidatos por busca para separar duplicados preexistentes de repetições.
- [x] Associar leads novos à lista escolhida.
- [x] Enriquecer registros existentes sem contá-los como novos.

## Fase 4 — reposição controlada

- [x] Buscar profundidade acumulada, não apenas a quantidade restante.
- [x] Definir limite de candidatos e tentativas, respeitando duracao maxima configurada.
- [x] Parar apos tentativa sem novos leads.
- [x] Finalizar como parcial quando a oferta disponível acabar.
- [x] Não prometer 20/50/100 quando o provider não tiver leads suficientes.

## Fase 5 — frontend

- [x] Exibir o overlay somente após a validação dos campos.
- [x] Garantir exatamente um POST por clique válido.
- [x] Consultar apenas o job ativo.
- [x] Encerrar o overlay em todos os estados terminais.
- [x] Remover downloads CSV usados como polling interno.
- [x] Abrir modal de resultados com relatório e botão “Ir para Leads”.
- [x] Corrigir versionamento automático dos assets.
- [x] Corrigir strings UTF-8 corrompidas.

## Fase 6 — validação e publicação

- [x] Testes do payload exato enviado ao actor.
- [x] Testes de todos os estados remotos.
- [x] Testes MySQL de deduplicação concorrente.
- [x] Testes de reposição com duplicados.
- [x] Testes de isolamento entre usuários.
- [x] Teste do scheduler e da reconciliação.
- [ ] Teste de navegador do fluxo completo.
- [ ] Smoke test real autorizado e limitado.
- [x] Deploy incremental com rollback documentado (release `32f2a7f` ativada após smoke test).

Evidência operacional adicional: timer systemd `kaizen-partners-schedule.timer` e worker `kaizen-partners-queue.service` ativos em produção; execução manual de `scraper:reconcile` concluída sem erro em 30/10/2026.

## Evidência

- Testes locais completos: **89 testes aprovados, 337 assertions e 9 testes MySQL ignorados pela indisponibilidade local do serviço isolado**.
- Testes no MySQL isolado `kaizen-mysql-test`: **5 testes aprovados e 17 assertions**; o container foi iniciado somente para a validação e encerrado ao final.
- Backup consistente anterior à migration: `/www/backups/kaizen-partners/20261030T215240Z-pre-prospecting-candidates/kaizen_partners-pre-000012.sql.gz`.
- SHA-256 do backup: `ef384aa0148f04b425a8020fa74f17b269043b00473c6a4f11d8e8b20e533e2d`; arquivo não vazio, gzip íntegro e hash conferido.
- Migration `2026_10_30_000012_create_prospecting_job_candidates` aplicada em produção no lote 10.
- Release ativa: `/www/wwwroot/app.kaizenmidias.com/releases/32f2a7f`.
- Smoke HTTP sem autenticação: `/login` 200; rotas privadas e APIs 302 para login; assets 200; HTML público antigo 404.
- Nenhum erro do worker ou scheduler foi registrado nos 15 minutos posteriores à ativação. As entradas `production.ERROR` encontradas no arquivo de log pertencem a releases anteriores.
- Permanecem deliberadamente não marcados: teste visual autenticado em navegador e smoke pago de ponta a ponta na Apify. Não foram simulados como aprovados para evitar consumo de créditos ou uso de dados sem uma execução de validação explicitamente controlada.

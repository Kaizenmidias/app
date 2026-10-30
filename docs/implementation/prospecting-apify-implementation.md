# Implementação da prospecção Apify

Checklist executivo derivado da auditoria de funcionamento da rota `/prospecting`.

## Escopo e segurança

- [x] Auditoria do fluxo atual concluída.
- [x] Contrato do actor `compass/crawler-google-places` conferido na documentação oficial.
- [x] Estado da produção e dos workers inspecionado sem alteração operacional.
- [x] Nenhuma credencial, token, lead real ou dump será versionado.
- [ ] Harness HTTP legado separado do pacote de runtime.

## Fase 0 — estabilização operacional

- [ ] Corrigir o scheduler da aplicação para executar `scraper:reconcile`.
- [ ] Unificar a fonte de verdade da capacidade de execução.
- [x] Reconciliar contadores de capacidade com jobs realmente ativos.
- [ ] Reiniciar workers de forma controlada após troca de release.
- [x] Impedir submissões duplicadas simultâneas por usuário.
- [ ] Classificar e encerrar jobs órfãos sem apagar dados de leads.

## Fase 1 — contrato único da API

- [x] Tornar `/api/prospecting/jobs` o único fluxo do frontend.
- [x] Remover dependência funcional de `/api/v1/jobs`.
- [ ] Validar termo, localização, quantidade `20|50|100` e lista do próprio usuário.
- [ ] Enviar ao actor apenas os campos necessários.
- [ ] Remover fallback silencioso para Gosom.
- [ ] Preservar CSRF e isolamento por usuário.

## Fase 2 — submissão e estado durável

- [ ] Responder `202` após persistir o job, sem chamada externa síncrona.
- [ ] Implementar estados `pending`, `submitting`, `running`, `importing`, `refilling`, `completed`, `partial`, `failed` e `cancelled`.
- [x] Registrar cada run Apify em uma tentativa própria (migration 000011 aplicada).
- [ ] Implementar correlação e reconciliação de submissões ambíguas.
- [ ] Implementar cancelamento real da run externa.

## Fase 3 — importação e deduplicação

- [ ] Preservar a chave única por usuário e Place ID.
- [ ] Tornar a importação idempotente sob concorrência.
- [ ] Registrar candidatos por busca para separar duplicados preexistentes de repetições.
- [ ] Associar leads novos à lista escolhida.
- [ ] Enriquecer registros existentes sem contá-los como novos.

## Fase 4 — reposição controlada

- [ ] Buscar profundidade acumulada, não apenas a quantidade restante.
- [ ] Definir limite de candidatos, tentativas, duração e custo.
- [ ] Parar após tentativas sem novos leads.
- [ ] Finalizar como parcial quando a oferta disponível acabar.
- [ ] Não prometer 20/50/100 quando o provider não tiver leads suficientes.

## Fase 5 — frontend

- [ ] Exibir o overlay somente após a validação dos campos.
- [ ] Garantir exatamente um POST por clique válido.
- [ ] Consultar apenas o job ativo.
- [ ] Encerrar o overlay em todos os estados terminais.
- [ ] Remover downloads CSV usados como polling interno.
- [ ] Abrir modal de resultados com relatório e botão “Ir para Leads”.
- [ ] Corrigir versionamento automático dos assets.
- [ ] Corrigir strings UTF-8 corrompidas.

## Fase 6 — validação e publicação

- [ ] Testes do payload exato enviado ao actor.
- [ ] Testes de todos os estados remotos.
- [ ] Testes MySQL de deduplicação concorrente.
- [ ] Testes de reposição com duplicados.
- [ ] Testes de isolamento entre usuários.
- [ ] Teste do scheduler e da reconciliação.
- [ ] Teste de navegador do fluxo completo.
- [ ] Smoke test real autorizado e limitado.
- [x] Deploy incremental com rollback documentado (release ca87cbb ativada apos smoke test).

Evidencia operacional adicional: timer systemd `kaizen-partners-schedule.timer` ativo em producao; execucao manual de `scraper:reconcile` concluida sem erro em 30/10/2026.

## Evidência

Os itens marcados como concluídos nesta primeira versão representam apenas auditoria e inspeção. Nenhuma correção funcional deste checklist é considerada implementada até possuir teste correspondente e registro de publicação.

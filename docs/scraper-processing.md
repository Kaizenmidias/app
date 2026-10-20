# Processamento do scraper

Jobs passam de pending para working depois da aquisi??o transacional da capacidade. O identificador externo ? persistido antes do polling. A importa??o ocorre somente em estado terminal confirmado pelo Gosom.

A coluna polling_claimed_at persiste a posse do polling. Uma marca vazia ou com mais de dez minutos pode ser recuperada ap?s rein?cio de worker. O lock de cache por job reduz concorr?ncia entre processos ativos.

Timeouts padr?o: HTTP 60s, job 75s, retry_after 120s, polling 10s e dura??o l?gica 3600s. Worker recomendado: php artisan queue:work database --sleep=3 --tries=3 --timeout=75 --backoff=30,120,300

Jobs unknown conservam a vaga. O comando php artisan scraper:unknown apenas lista casos para investiga??o e nunca libera capacidade ou reenvia buscas automaticamente.

Os testes locais usam SQLite isolado e n?o comprovam concorr?ncia real, locks InnoDB ou m?ltiplos processos; isso deve ser validado em MySQL 8.4.

-- Baby Food - migração opcional para instalações anteriores
-- Use somente se você já instalou uma versão antiga que possuía planos/assinaturas.
-- A versão nova não cria nem utiliza essas tabelas.

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS subscriptions;
DROP TABLE IF EXISTS plans;
SET FOREIGN_KEY_CHECKS = 1;

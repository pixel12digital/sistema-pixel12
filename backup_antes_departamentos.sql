-- BACKUP COMPLETO ANTES DA IMPLEMENTAÇÃO DE DEPARTAMENTOS
-- Execute este backup antes de qualquer modificação

-- Backup da estrutura atual
CREATE TABLE backup_canais_comunicacao_original AS SELECT * FROM canais_comunicacao;
CREATE TABLE backup_mensagens_comunicacao_original AS SELECT * FROM mensagens_comunicacao;

-- Verificar dados atuais
SELECT 'CANAIS ATUAIS' as tipo, COUNT(*) as quantidade FROM canais_comunicacao
UNION ALL
SELECT 'MENSAGENS ATUAIS' as tipo, COUNT(*) as quantidade FROM mensagens_comunicacao;

-- Mostrar canais específicos que vamos modificar
SELECT 
    id, 
    nome_exibicao, 
    identificador, 
    status, 
    porta,
    'CANAL A SER DEPARTAMENTALIZADO' as observacao
FROM canais_comunicacao 
WHERE porta IN (3000, 3001) OR nome_exibicao LIKE '%Financeiro%' OR nome_exibicao LIKE '%Comercial%';

SELECT 'Backup concluído com sucesso!' as resultado; 
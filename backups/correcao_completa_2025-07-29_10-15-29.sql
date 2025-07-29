-- Backup de correção de duplicatas - 2025-07-29 10:15:29
-- Cliente duplicado: ID 4295
-- Cliente principal: ID 156

-- Cliente duplicado
INSERT INTO clientes VALUES ('4295', 'cus_000096887334', 'Valdirene Cravo e  Canela Home', '', '', '', '', '', '', '', '', '', '', '', 'Brasil', '0', '', '', '', '', '', '03454769990', '2025-07-15 11:55:28', '2025-07-15 11:55:28', '');

-- Cobranças do cliente duplicado
INSERT INTO cobrancas VALUES ('61522', 'pay_47w9g0g71jx60kmk', '4295', '400.00', 'RECEIVED', '2024-10-19', '2024-10-19', '2024-10-19 00:00:00', '2025-07-29 07:56:42', 'Cobrança gerada automaticamente a partir de Pix recebido.', 'PIX', 'PIX', 'https://www.asaas.com/i/47w9g0g71jx60kmk', '', '');

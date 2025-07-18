// Correção para DDD 47 - Deve usar 8 dígitos (sem o 9 adicional)

// DDDs que SEMPRE usam 9 dígitos (celular)
const dddCom9Digitos = ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24', '27', '28', '31', '32', '33', '34', '35', '37', '38', '41', '42', '43', '44', '45', '46', '48', '49', '51', '53', '54', '55', '61', '62', '63', '64', '65', '66', '67', '68', '69', '71', '73', '74', '75', '77', '79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '96', '97', '98', '99'];

// DDDs que usam 8 dígitos (fixo) - INCLUINDO O 47
const dddCom8Digitos = ['23', '25', '26', '29', '36', '39', '40', '47', '50', '52', '56', '57', '58', '59', '60', '70', '72', '76', '78', '80', '90'];

// Função corrigida
function formatarNumeroBrasileiro(numero) {
    // Remover espaços, traços e parênteses
    let numeroLimpo = numero.replace(/[\s\-\(\)]/g, '');
    
    // Se já tem @c.us, retornar como está
    if (numeroLimpo.includes('@')) {
        return numeroLimpo;
    }
    
    // Verificar se é um número brasileiro (começa com 55)
    if (numeroLimpo.startsWith('55')) {
        numeroLimpo = numeroLimpo.substring(2); // Remove o 55
    }
    
    // Verificar se tem DDD (2 dígitos)
    if (numeroLimpo.length >= 10) {
        const ddd = numeroLimpo.substring(0, 2);
        const numeroSemDDD = numeroLimpo.substring(2);
        
        if (dddCom9Digitos.includes(ddd)) {
            // DDD que usa 9 dígitos - garantir que tenha 9 dígitos
            if (numeroSemDDD.length === 8) {
                numeroLimpo = ddd + '9' + numeroSemDDD; // Adiciona o 9
            } else if (numeroSemDDD.length === 9) {
                numeroLimpo = ddd + numeroSemDDD; // Mantém como está
            }
        } else if (dddCom8Digitos.includes(ddd)) {
            // DDD que usa 8 dígitos - remover o 9 se houver
            if (numeroSemDDD.length === 9 && numeroSemDDD.startsWith('9')) {
                numeroLimpo = ddd + numeroSemDDD.substring(1); // Remove o 9
            } else if (numeroSemDDD.length === 8) {
                numeroLimpo = ddd + numeroSemDDD; // Mantém como está
            }
        }
    }
    
    // Retornar com código do país 55
    return '55' + numeroLimpo + '@c.us';
}

// Teste para DDD 47
console.log('Teste DDD 47:');
console.log('Entrada: 47996164699');
console.log('Saída:', formatarNumeroBrasileiro('47996164699'));
console.log('Esperado: 554796164699@c.us (sem o 9)'); 
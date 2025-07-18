// Função para formatar números brasileiros
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
        const numeroSemDdd = numeroLimpo.substring(2);
        
        // DDDs que SEMPRE usam 9 dígitos (celular)
        const dddCom9Digitos = [11, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 24, 27, 28, 31, 32, 33, 34, 35, 37, 38, 41, 42, 43, 44, 45, 46, 47, 48, 49, 51, 53, 54, 55, 61, 62, 63, 64, 65, 66, 67, 68, 69, 71, 73, 74, 75, 77, 79, 81, 82, 83, 84, 85, 86, 87, 88, 89, 91, 92, 93, 94, 95, 96, 97, 98, 99];
        
        if (dddCom9Digitos.includes(parseInt(ddd))) {
            // DDD que usa 9 dígitos - garantir que tem 9 dígitos
            if (numeroSemDdd.length === 8) {
                numeroSemDdd = '9' + numeroSemDdd; // Adiciona o 9
            }
        } else {
            // DDD que usa 8 dígitos - remover o 9 se tiver
            if (numeroSemDdd.length === 9 && numeroSemDdd.startsWith('9')) {
                numeroSemDdd = numeroSemDdd.substring(1); // Remove o 9
            }
        }
        
        // Retornar no formato correto: 55 + DDD + número + @c.us
        return '55' + ddd + numeroSemDdd + '@c.us';
    }
    
    // Se não tem DDD, assumir que é um número local
    return '55' + numeroLimpo + '@c.us';
} 
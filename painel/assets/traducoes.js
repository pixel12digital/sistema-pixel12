/**
 * Sistema de Traduções - Pixel12 Digital
 * Converte termos em inglês para português na interface
 */

class Traducoes {
    constructor() {
        this.traducoes = {
            // Termos técnicos
            'Console': 'Consola',
            'top': 'Superior',
            'Template': 'Modelo',
            'Status': 'Estado',
            'undefined': 'Indefinido',
            'isConnected': 'Está conectado',
            'true': 'Verdadeiro',
            'false': 'Falso',
            'ID': 'Identificador',
            'Dashboard': 'Painel de Controle',
            
            // Status de cobranças
            'PENDING': 'Aguardando Pagamento',
            'OVERDUE': 'Vencido',
            'RECEIVED': 'Recebido',
            'CONFIRMED': 'Confirmado',
            'CANCELLED': 'Cancelado',
            
            // Mensagens do sistema
            'Loading...': 'Carregando...',
            'Error': 'Erro',
            'Success': 'Sucesso',
            'Warning': 'Aviso',
            'Info': 'Informação',
            'Close': 'Fechar',
            'Save': 'Salvar',
            'Cancel': 'Cancelar',
            'Edit': 'Editar',
            'Delete': 'Excluir',
            'Add': 'Adicionar',
            'Search': 'Buscar',
            'Filter': 'Filtrar',
            'Export': 'Exportar',
            'Import': 'Importar',
            'Refresh': 'Atualizar',
            'Next': 'Próximo',
            'Previous': 'Anterior',
            'First': 'Primeiro',
            'Last': 'Último',
            'Page': 'Página',
            'of': 'de',
            'Total': 'Total',
            'Records': 'Registros',
            'No data found': 'Nenhum dado encontrado',
            'Are you sure?': 'Tem certeza?',
            'Yes': 'Sim',
            'No': 'Não',
            'OK': 'OK',
            
            // Mensagens de erro
            'Connection error': 'Erro de conexão',
            'Network error': 'Erro de rede',
            'Server error': 'Erro do servidor',
            'Timeout': 'Tempo esgotado',
            'Invalid data': 'Dados inválidos',
            'Required field': 'Campo obrigatório',
            'Invalid format': 'Formato inválido',
            
            // Mensagens de sucesso
            'Data saved successfully': 'Dados salvos com sucesso',
            'Operation completed': 'Operação concluída',
            'Changes applied': 'Alterações aplicadas',
            
            // Termos de monitoramento
            'Monitoring': 'Monitoramento',
            'Scheduled': 'Agendado',
            'Sent': 'Enviado',
            'Failed': 'Falhou',
            'Pending': 'Pendente',
            'Active': 'Ativo',
            'Inactive': 'Inativo',
            'Enabled': 'Habilitado',
            'Disabled': 'Desabilitado',
            
            // Termos de WhatsApp
            'WhatsApp': 'WhatsApp',
            'Message': 'Mensagem',
            'Chat': 'Conversa',
            'Contact': 'Contato',
            'Phone': 'Telefone',
            'Mobile': 'Celular',
            'Number': 'Número',
            'Text': 'Texto',
            'Send': 'Enviar',
            'Received': 'Recebido',
            'Delivered': 'Entregue',
            'Read': 'Lido',
            
            // Termos financeiros
            'Invoice': 'Fatura',
            'Payment': 'Pagamento',
            'Amount': 'Valor',
            'Due Date': 'Data de Vencimento',
            'Balance': 'Saldo',
            'Credit': 'Crédito',
            'Debit': 'Débito',
            'Transaction': 'Transação',
            'Account': 'Conta',
            'Customer': 'Cliente',
            'Client': 'Cliente',
            'Supplier': 'Fornecedor',
            'Vendor': 'Vendedor',
            
            // Termos de interface
            'Settings': 'Configurações',
            'Configuration': 'Configuração',
            'Preferences': 'Preferências',
            'Profile': 'Perfil',
            'User': 'Usuário',
            'Password': 'Senha',
            'Login': 'Entrar',
            'Logout': 'Sair',
            'Register': 'Cadastrar',
            'Forgot Password': 'Esqueci a Senha',
            'Remember me': 'Lembrar de mim',
            'Username': 'Nome de usuário',
            'Email': 'E-mail',
            'Name': 'Nome',
            'Address': 'Endereço',
            'City': 'Cidade',
            'State': 'Estado',
            'Country': 'País',
            'Zip Code': 'CEP',
            'Phone Number': 'Número de telefone',
            'Date': 'Data',
            'Time': 'Hora',
            'Description': 'Descrição',
            'Notes': 'Observações',
            'Comments': 'Comentários',
            'Category': 'Categoria',
            'Type': 'Tipo',
            'Priority': 'Prioridade',
            'High': 'Alta',
            'Medium': 'Média',
            'Low': 'Baixa',
            'Normal': 'Normal',
            'Urgent': 'Urgente',
            'Important': 'Importante',
            
            // Termos de relatórios
            'Report': 'Relatório',
            'Reports': 'Relatórios',
            'Chart': 'Gráfico',
            'Graph': 'Gráfico',
            'Statistics': 'Estatísticas',
            'Analytics': 'Análises',
            'Summary': 'Resumo',
            'Details': 'Detalhes',
            'Overview': 'Visão Geral',
            'History': 'Histórico',
            'Log': 'Registro',
            'Activity': 'Atividade',
            'Events': 'Eventos',
            'Actions': 'Ações',
            'Operations': 'Operações',
            
            // Termos de sistema
            'System': 'Sistema',
            'Application': 'Aplicação',
            'Module': 'Módulo',
            'Function': 'Função',
            'Feature': 'Recurso',
            'Tool': 'Ferramenta',
            'Utility': 'Utilitário',
            'Service': 'Serviço',
            'API': 'API',
            'Database': 'Banco de dados',
            'Backup': 'Backup',
            'Restore': 'Restaurar',
            'Update': 'Atualizar',
            'Upgrade': 'Atualização',
            'Install': 'Instalar',
            'Uninstall': 'Desinstalar',
            'Version': 'Versão',
            'Release': 'Lançamento',
            'Build': 'Compilação',
            'Debug': 'Depurar',
            'Test': 'Testar',
            'Production': 'Produção',
            'Development': 'Desenvolvimento',
            'Environment': 'Ambiente',
            'Server': 'Servidor',
            'Client': 'Cliente',
            'Host': 'Host',
            'Domain': 'Domínio',
            'URL': 'URL',
            'Link': 'Link',
            'File': 'Arquivo',
            'Folder': 'Pasta',
            'Directory': 'Diretório',
            'Path': 'Caminho',
            'Size': 'Tamanho',
            'Format': 'Formato',
            'Type': 'Tipo',
            'Extension': 'Extensão',
            'Download': 'Baixar',
            'Upload': 'Enviar',
            'Share': 'Compartilhar',
            'Copy': 'Copiar',
            'Paste': 'Colar',
            'Cut': 'Recortar',
            'Select': 'Selecionar',
            'Select All': 'Selecionar Tudo',
            'Clear': 'Limpar',
            'Reset': 'Redefinir',
            'Default': 'Padrão',
            'Custom': 'Personalizado',
            'Advanced': 'Avançado',
            'Basic': 'Básico',
            'Simple': 'Simples',
            'Complex': 'Complexo',
            'Easy': 'Fácil',
            'Hard': 'Difícil',
            'Fast': 'Rápido',
            'Slow': 'Lento',
            'Big': 'Grande',
            'Small': 'Pequeno',
            'Large': 'Grande',
            'Medium': 'Médio',
            'New': 'Novo',
            'Old': 'Antigo',
            'Recent': 'Recente',
            'Latest': 'Mais recente',
            'Previous': 'Anterior',
            'Next': 'Próximo',
            'Current': 'Atual',
            'Future': 'Futuro',
            'Past': 'Passado',
            'Today': 'Hoje',
            'Yesterday': 'Ontem',
            'Tomorrow': 'Amanhã',
            'Week': 'Semana',
            'Month': 'Mês',
            'Year': 'Ano',
            'Day': 'Dia',
            'Hour': 'Hora',
            'Minute': 'Minuto',
            'Second': 'Segundo',
            'Morning': 'Manhã',
            'Afternoon': 'Tarde',
            'Evening': 'Noite',
            'Night': 'Noite',
            'Monday': 'Segunda-feira',
            'Tuesday': 'Terça-feira',
            'Wednesday': 'Quarta-feira',
            'Thursday': 'Quinta-feira',
            'Friday': 'Sexta-feira',
            'Saturday': 'Sábado',
            'Sunday': 'Domingo',
            'January': 'Janeiro',
            'February': 'Fevereiro',
            'March': 'Março',
            'April': 'Abril',
            'May': 'Maio',
            'June': 'Junho',
            'July': 'Julho',
            'August': 'Agosto',
            'September': 'Setembro',
            'October': 'Outubro',
            'November': 'Novembro',
            'December': 'Dezembro'
        };
    }

    /**
     * Traduz um texto do inglês para português
     */
    traduzir(texto) {
        if (!texto) return texto;
        
        // Verificar se existe tradução direta
        if (this.traducoes[texto]) {
            return this.traducoes[texto];
        }
        
        // Verificar se existe tradução case-insensitive
        const textoLower = texto.toLowerCase();
        for (const [ingles, portugues] of Object.entries(this.traducoes)) {
            if (ingles.toLowerCase() === textoLower) {
                return portugues;
            }
        }
        
        // Se não encontrar tradução, retorna o texto original
        return texto;
    }

    /**
     * Traduz elementos da página
     */
    traduzirPagina() {
        // Traduzir textos em elementos
        this.traduzirElementos();
        
        // Traduzir placeholders
        this.traduzirPlaceholders();
        
        // Traduzir títulos
        this.traduzirTitulos();
        
        // Traduzir botões
        this.traduzirBotoes();
        
        // Traduzir tabelas
        this.traduzirTabelas();
    }

    /**
     * Traduz elementos de texto
     */
    traduzirElementos() {
        const elementos = document.querySelectorAll('span, div, p, label, h1, h2, h3, h4, h5, h6');
        
        elementos.forEach(elemento => {
            if (elemento.childNodes.length === 1 && elemento.childNodes[0].nodeType === 3) {
                const textoOriginal = elemento.textContent.trim();
                const textoTraduzido = this.traduzir(textoOriginal);
                
                if (textoTraduzido !== textoOriginal) {
                    elemento.textContent = textoTraduzido;
                }
            }
        });
    }

    /**
     * Traduz placeholders
     */
    traduzirPlaceholders() {
        const inputs = document.querySelectorAll('input[placeholder], textarea[placeholder]');
        
        inputs.forEach(input => {
            const placeholderOriginal = input.getAttribute('placeholder');
            const placeholderTraduzido = this.traduzir(placeholderOriginal);
            
            if (placeholderTraduzido !== placeholderOriginal) {
                input.setAttribute('placeholder', placeholderTraduzido);
            }
        });
    }

    /**
     * Traduz títulos
     */
    traduzirTitulos() {
        const elementos = document.querySelectorAll('[title]');
        
        elementos.forEach(elemento => {
            const tituloOriginal = elemento.getAttribute('title');
            const tituloTraduzido = this.traduzir(tituloOriginal);
            
            if (tituloTraduzido !== tituloOriginal) {
                elemento.setAttribute('title', tituloTraduzido);
            }
        });
    }

    /**
     * Traduz botões
     */
    traduzirBotoes() {
        const botoes = document.querySelectorAll('button, input[type="button"], input[type="submit"]');
        
        botoes.forEach(botao => {
            const textoOriginal = botao.textContent.trim();
            const textoTraduzido = this.traduzir(textoOriginal);
            
            if (textoTraduzido !== textoOriginal) {
                botao.textContent = textoTraduzido;
            }
        });
    }

    /**
     * Traduz cabeçalhos de tabelas
     */
    traduzirTabelas() {
        const tabelas = document.querySelectorAll('table');
        
        tabelas.forEach(tabela => {
            const cabecalhos = tabela.querySelectorAll('th');
            
            cabecalhos.forEach(cabecalho => {
                const textoOriginal = cabecalho.textContent.trim();
                const textoTraduzido = this.traduzir(textoOriginal);
                
                if (textoTraduzido !== textoOriginal) {
                    cabecalho.textContent = textoTraduzido;
                }
            });
        });
    }

    /**
     * Traduz status específicos
     */
    traduzirStatus(status) {
        const statusTraducoes = {
            'PENDING': 'Aguardando Pagamento',
            'OVERDUE': 'Vencido',
            'RECEIVED': 'Recebido',
            'CONFIRMED': 'Confirmado',
            'CANCELLED': 'Cancelado',
            'agendada': 'Agendada',
            'enviada': 'Enviada',
            'cancelada': 'Cancelada',
            'erro': 'Erro',
            'alta': 'Alta',
            'normal': 'Normal',
            'baixa': 'Baixa'
        };
        
        return statusTraducoes[status] || status;
    }
}

// Instanciar e usar o sistema de traduções
const traducoes = new Traducoes();

// Traduzir a página quando carregar
document.addEventListener('DOMContentLoaded', () => {
    traducoes.traduzirPagina();
});

// Exportar para uso global
window.traducoes = traducoes; 
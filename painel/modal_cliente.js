// Modal de Cliente - Reutilizável
class ModalCliente {
    constructor(options = {}) {
        this.options = {
            modalId: 'modalCliente',
            btnOpenId: 'btnNovoCliente',
            onSuccess: null,
            onError: null,
            ...options
        };
        
        this.init();
    }
    
    init() {
        this.createModal();
        this.bindEvents();
    }
    
    createModal() {
        // Verificar se o modal já existe
        if (document.getElementById(this.options.modalId)) {
            return;
        }
        
        const modalHTML = `
            <div class="modal-overlay" id="${this.options.modalId}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Cadastrar Novo Cliente</h3>
                        <button class="modal-close" id="btnFecharModal">&times;</button>
                    </div>
                    <div class="modal-body" id="modalClienteContent">
                        <!-- Conteúdo será carregado via AJAX -->
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    bindEvents() {
        // Abrir modal
        $(document).on('click', `#${this.options.btnOpenId}`, () => {
            this.open();
        });

        // Fechar modal
        $(document).on('click', '#btnFecharModal, #modalCliente', (e) => {
            if (e.target === e.currentTarget) {
                this.close();
            }
        });

        // Fechar modal com ESC
        $(document).on('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });

        // Delegar eventos do formulário
        $(document).on('click', '#btnBuscarCliente', () => {
            this.buscarCliente();
        });

        // Submissão do formulário
        $(document).on('submit', '#formCliente', (e) => {
            e.preventDefault();
            this.submitForm();
        });
    }
    
    open() {
        $('#modalClienteContent').html('<p style="text-align: center; color: #a259e6;">Carregando...</p>');
        $(`#${this.options.modalId}`).fadeIn();
        
        // Carregar conteúdo do formulário
        $.get('cliente_form.php', (data) => {
            $('#modalClienteContent').html(data);
        });
    }
    
    close() {
        $(`#${this.options.modalId}`).fadeOut();
    }
    
    isOpen() {
        return $(`#${this.options.modalId}`).is(':visible');
    }
    
    buscarCliente() {
        const cpfCnpj = $('#cpf_cnpj').val().replace(/\D/g, '');
        if (!cpfCnpj) { 
            this.showMessage('Informe o CPF ou CNPJ.', 'error');
            return; 
        }
        
        this.showMessage('Buscando cliente...', 'info');
        
        $.get('cliente_busca.php', { cpfCnpj }, (resp) => {
            if (resp.success) {
                $('#clienteFormFields').removeClass('hidden');
                $('#nome').val(resp.data.name).prop('readonly', true);
                $('#email').val(resp.data.email).prop('readonly', true);
                $('#telefone').val(resp.data.mobilePhone).prop('readonly', true);
                this.showMessage('Cliente já cadastrado no Asaas!', 'info');
                $('#formCliente button[type=submit]').prop('disabled', true);
            } else {
                $('#clienteFormFields').removeClass('hidden');
                $('#nome, #email, #telefone').val('').prop('readonly', false);
                this.showMessage('Cliente não encontrado. Preencha os dados para cadastrar.', 'info');
                $('#formCliente button[type=submit]').prop('disabled', false);
            }
        }, 'json').fail(() => {
            this.showMessage('Erro ao buscar cliente. Tente novamente.', 'error');
        });
    }
    
    submitForm() {
        const formData = $('#formCliente').serialize();
        
        $.post('cliente_add.php', formData, (resp) => {
            if (resp.success) {
                this.showMessage('Cliente cadastrado com sucesso!', 'success');
                
                // Callback de sucesso
                if (this.options.onSuccess) {
                    this.options.onSuccess(resp);
                }
                
                setTimeout(() => {
                    this.close();
                    // Recarregar página se não houver callback personalizado
                    if (!this.options.onSuccess) {
                        location.reload();
                    }
                }, 1500);
            } else {
                this.showMessage(resp.message || 'Erro ao cadastrar cliente.', 'error');
                
                // Callback de erro
                if (this.options.onError) {
                    this.options.onError(resp);
                }
            }
        }, 'json').fail(() => {
            this.showMessage('Erro ao cadastrar cliente. Tente novamente.', 'error');
            
            // Callback de erro
            if (this.options.onError) {
                this.options.onError({ message: 'Erro de conexão' });
            }
        });
    }
    
    showMessage(message, type = 'info') {
        $('#msgCliente').text(message).removeClass('success error info').addClass(type);
    }
}

// CSS para o modal (pode ser incluído em um arquivo CSS separado)
const modalCSS = `
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.7);
    display: none;
    justify-content: center;
    align-items: flex-start;
    padding-top: 60px;
    z-index: 1000;
    overflow: auto;
}
.modal-content {
    background: #232836;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.modal-header {
    padding: 1.5rem 2rem 1rem 2rem;
    border-bottom: 1px solid #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-title {
    color: #a259e6;
    font-size: 1.3rem;
    font-weight: bold;
    margin: 0;
}
.modal-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}
.modal-close:hover {
    background: #333;
}
.modal-body {
    padding: 1rem 2rem 2rem 2rem;
}
.form-container { 
    max-width: 100%; 
    margin: 0; 
    background: transparent; 
    border-radius: 0; 
    box-shadow: none; 
    padding: 0; 
}
.form-container h2 { 
    display: none; 
}
.form-container label { 
    display: block; 
    margin-bottom: 0.5rem; 
    color: #a259e6; 
    font-weight: bold;
}
.form-container input[type=text], 
.form-container input[type=email] { 
    width: 100%; 
    padding: 10px; 
    border-radius: 6px; 
    border: 1px solid #a259e6; 
    background: #181c23; 
    color: #fff; 
    margin-bottom: 1.2rem; 
    box-sizing: border-box;
}
.form-container button { 
    background: #a259e6; 
    color: #fff; 
    border: none; 
    border-radius: 6px; 
    padding: 10px 20px; 
    font-size: 1rem; 
    font-weight: bold; 
    cursor: pointer; 
    transition: background 0.2s; 
    margin-right: 10px;
}
.form-container button:hover { 
    background: #7c2ae8; 
}
.form-container .msg { 
    margin-bottom: 1rem; 
    padding: 10px;
    border-radius: 6px;
    font-weight: bold;
}
.form-container .msg.success {
    background: #4caf50;
    color: #fff;
}
.form-container .msg.error {
    background: #f44336;
    color: #fff;
}
.form-container .msg.info {
    background: #2196f3;
    color: #fff;
}
.form-container .hidden { 
    display: none; 
}
`;

// Injetar CSS se não existir
if (!document.getElementById('modal-cliente-css')) {
    const style = document.createElement('style');
    style.id = 'modal-cliente-css';
    style.textContent = modalCSS;
    document.head.appendChild(style);
} 
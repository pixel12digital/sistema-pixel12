// Funções para edição e exclusão de mensagens no chat
// Função showToast (caso não exista)
if (typeof showToast !== 'function') {
  window.showToast = function(msg, tipo) {
    const toast = document.createElement('div');
    toast.textContent = msg;
    toast.style = `position:fixed;top:24px;right:24px;z-index:9999;padding:12px 22px;background:${tipo==='success'?'#bbf7d0':'#fee2e2'};color:${tipo==='success'?'#166534':'#b91c1c'};border-radius:8px;font-weight:500;box-shadow:0 2px 8px #0002;transition:opacity 0.3s;`;
    document.body.appendChild(toast);
    setTimeout(()=>{
      toast.style.opacity = '0';
      setTimeout(()=>{toast.remove();}, 300);
    }, 2500);
  }
}

// Função para determinar o caminho correto da API
function getApiPath(apiName) {
  // Se estamos no iframe (detalhes_cliente.php)
  if (window.location.pathname.includes('/api/detalhes_cliente.php')) {
    return `../api/${apiName}`;
  }
  // Se estamos no chat.php
  else if (window.location.pathname.includes('/chat.php')) {
    return `api/${apiName}`;
  }
  // Fallback
  else {
    return `api/${apiName}`;
  }
}

// Função para editar mensagens
window.editarMensagem = function(id, textoAtual) {
  console.log('Editando mensagem ID:', id, 'Texto atual:', textoAtual);
  const novoTexto = prompt("Editar mensagem:", textoAtual);
  if (novoTexto === null || novoTexto.trim() === "") return;
  
  console.log('Novo texto:', novoTexto);
  
  const apiPath = getApiPath('editar_mensagem.php');
  console.log('Usando API path:', apiPath);
  
  fetch(apiPath, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + encodeURIComponent(id) + "&mensagem=" + encodeURIComponent(novoTexto.trim())
  })
  .then(r => {
    console.log('Status da resposta:', r.status);
    return r.json();
  })
  .then(resp => {
    console.log('Resposta do servidor:', resp);
    if (resp.success) {
      // Atualizar o texto da mensagem diretamente no DOM
      const mensagemElement = document.querySelector(`[data-mensagem-id="${id}"]`);
      if (mensagemElement) {
        const conteudoElement = mensagemElement.querySelector('.mensagem-conteudo');
        if (conteudoElement) {
          conteudoElement.textContent = novoTexto.trim();
        }
      }
      showToast("Mensagem editada com sucesso!", "success");
    } else {
      showToast("Erro ao editar: " + (resp.error || "Erro desconhecido"), "error");
    }
  })
  .catch(error => {
    console.error('Erro na requisição:', error);
    showToast("Erro ao conectar ao servidor: " + error.message, "error");
  });
};

// Função para excluir mensagens
window.excluirMensagem = function(id) {
  console.log('Excluindo mensagem ID:', id);
  if (!confirm("Tem certeza que deseja excluir esta mensagem?")) return;
  
  const apiPath = getApiPath('excluir_mensagem.php');
  console.log('Usando API path:', apiPath);
  
  fetch(apiPath, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + encodeURIComponent(id)
  })
  .then(r => {
    console.log('Status da resposta:', r.status);
    return r.json();
  })
  .then(resp => {
    console.log('Resposta do servidor:', resp);
    if (resp.success) {
      // Remover a mensagem do DOM diretamente
      const mensagemElement = document.querySelector(`[data-mensagem-id="${id}"]`);
      if (mensagemElement) {
        mensagemElement.remove();
      }
      showToast("Mensagem excluída com sucesso!", "success");
    } else {
      showToast("Erro ao excluir: " + (resp.error || "Erro desconhecido"), "error");
    }
  })
  .catch(error => {
    console.error('Erro na requisição:', error);
    showToast("Erro ao conectar ao servidor: " + error.message, "error");
  });
};

// Log para debug
console.log('Chat functions carregadas. Contexto:', window.location.pathname);
console.log('editarMensagem disponível:', typeof window.editarMensagem === 'function');
console.log('excluirMensagem disponível:', typeof window.excluirMensagem === 'function'); 
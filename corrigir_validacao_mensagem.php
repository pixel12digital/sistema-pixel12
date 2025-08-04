<?php
/**
 * Correção da Validação de Mensagem no Chat
 * Resolve o problema do modal aparecendo mesmo com mensagem digitada
 */

require_once 'config.php';
require_once 'painel/db.php';

echo "<h1>🔧 Correção da Validação de Mensagem no Chat</h1>\n";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: #059669; background: #d1fae5; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 5px; margin: 10px 0; }
.warning { color: #d97706; background: #fef3c7; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { color: #2563eb; background: #dbeafe; padding: 10px; border-radius: 5px; margin: 10px 0; }
pre { background: #f3f4f6; padding: 10px; border-radius: 5px; overflow-x: auto; }
.code-block { background: #1f2937; color: #f9fafb; padding: 15px; border-radius: 8px; margin: 15px 0; }
</style>\n";

echo "<h2>🎯 Problema Identificado</h2>\n";
echo "<div class='warning'>\n";
echo "<strong>Problema:</strong> Modal aparece ao pressionar Enter mesmo com mensagem digitada<br>\n";
echo "<strong>Causa:</strong> Validação incorreta do valor do textarea no JavaScript<br>\n";
echo "<strong>Localização:</strong> Função enviarMensagemChat() no arquivo chat.php<br>\n";
echo "</div>\n";

echo "<h2>🔍 Análise do Código Atual</h2>\n";
echo "<div class='code-block'>\n";
echo "// Código problemático atual:\n";
echo "const formData = new FormData(form);\n";
echo "const mensagem = formData.get('mensagem');\n";
echo "if (!mensagem.trim()) {\n";
echo "  alert('Digite uma mensagem');\n";
echo "  return;\n";
echo "}\n";
echo "</div>\n";

echo "<h2>✅ Solução Proposta</h2>\n";
echo "<div class='info'>\n";
echo "<strong>Melhorias:</strong><br>\n";
echo "• Capturar valor diretamente do textarea<br>\n";
echo "• Melhor validação de espaços em branco<br>\n";
echo "• Debug para identificar problemas<br>\n";
echo "• Prevenção de envio acidental<br>\n";
echo "</div>\n";

echo "<h2>🔧 Código Corrigido</h2>\n";
echo "<div class='code-block'>\n";
echo "// Função corrigida para enviar mensagem via AJAX\n";
echo "function enviarMensagemChat() {\n";
echo "  const form = document.getElementById('form-chat-enviar');\n";
echo "  if (!form) return;\n";
echo "  \n";
echo "  // Capturar valor diretamente do textarea\n";
echo "  const textarea = form.querySelector('textarea[name=\"mensagem\"]');\n";
echo "  const mensagem = textarea ? textarea.value : '';\n";
echo "  \n";
echo "  // Debug para verificar o valor\n";
echo "  console.log('Valor da mensagem:', mensagem);\n";
echo "  console.log('Tamanho da mensagem:', mensagem.length);\n";
echo "  console.log('Mensagem após trim:', mensagem.trim());\n";
echo "  \n";
echo "  // Validação melhorada\n";
echo "  if (!mensagem || !mensagem.trim()) {\n";
echo "    alert('Digite uma mensagem');\n";
echo "    textarea.focus();\n";
echo "    return;\n";
echo "  }\n";
echo "  \n";
echo "  const formData = new FormData(form);\n";
echo "  const clienteId = formData.get('cliente_id');\n";
echo "  const canalId = formData.get('canal_id');\n";
echo "  \n";
echo "  if (!clienteId) {\n";
echo "    alert('Cliente não selecionado');\n";
echo "    return;\n";
echo "  }\n";
echo "  \n";
echo "  // Verificar se canal foi selecionado\n";
echo "  if (!canalId) {\n";
echo "    alert('Selecione um canal para enviar a mensagem');\n";
echo "    const canalSelector = document.getElementById('canal-selector');\n";
echo "    if (canalSelector) {\n";
echo "      canalSelector.focus();\n";
echo "      canalSelector.style.borderColor = '#ef4444';\n";
echo "      canalSelector.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';\n";
echo "      \n";
echo "      setTimeout(() => {\n";
echo "        canalSelector.style.borderColor = '';\n";
echo "        canalSelector.style.boxShadow = '';\n";
echo "      }, 3000);\n";
echo "    }\n";
echo "    return;\n";
echo "  }\n";
echo "  \n";
echo "  // Verificar se canal está conectado\n";
echo "  const canalSelector = document.getElementById('canal-selector');\n";
echo "  if (canalSelector) {\n";
echo "    const selectedOption = canalSelector.options[canalSelector.selectedIndex];\n";
echo "    if (selectedOption && selectedOption.dataset.canalInfo) {\n";
echo "      const canalInfo = JSON.parse(selectedOption.dataset.canalInfo);\n";
echo "      if (canalInfo.status !== 'conectado') {\n";
echo "        alert('❌ Este canal não está conectado. Selecione um canal conectado para enviar mensagens.');\n";
echo "        canalSelector.focus();\n";
echo "        return;\n";
echo "      }\n";
echo "    }\n";
echo "  }\n";
echo "  \n";
echo "  // Desabilitar botão de envio\n";
echo "  const sendBtn = form.querySelector('.chat-send-btn');\n";
echo "  const originalText = sendBtn.innerHTML;\n";
echo "  sendBtn.innerHTML = '⏳ Enviando...';\n";
echo "  sendBtn.disabled = true;\n";
echo "  \n";
echo "  // Limpar campo de mensagem\n";
echo "  textarea.value = '';\n";
echo "  textarea.style.height = 'auto';\n";
echo "  \n";
echo "  // Resetar anexo\n";
echo "  const anexoInput = form.querySelector('#anexo');\n";
echo "  if (anexoInput) {\n";
echo "    anexoInput.value = '';\n";
echo "    const label = anexoInput.parentElement;\n";
echo "    label.textContent = '📎';\n";
echo "    label.title = 'Anexar arquivo';\n";
echo "  }\n";
echo "  \n";
echo "  fetch('chat_enviar.php', {\n";
echo "    method: 'POST',\n";
echo "    body: formData\n";
echo "  })\n";
echo "  .then(response => response.json())\n";
echo "  .then(data => {\n";
echo "    if (data.success) {\n";
echo "      carregarMensagensCliente(clienteId);\n";
echo "      showToast('Mensagem enviada com sucesso!', 'success');\n";
echo "    } else {\n";
echo "      showToast('Erro ao enviar mensagem: ' + (data.error || 'Erro desconhecido'), 'error');\n";
echo "      textarea.value = mensagem;\n";
echo "    }\n";
echo "  })\n";
echo "  .catch(error => {\n";
echo "    console.error('Erro ao enviar mensagem:', error);\n";
echo "    showToast('Erro de conexão ao enviar mensagem', 'error');\n";
echo "    textarea.value = mensagem;\n";
echo "  })\n";
echo "  .finally(() => {\n";
echo "    sendBtn.innerHTML = originalText;\n";
echo "    sendBtn.disabled = false;\n";
echo "  });\n";
echo "}\n";
echo "</div>\n";

echo "<h2>📝 Instruções de Aplicação</h2>\n";
echo "<div class='info'>\n";
echo "<strong>Para aplicar a correção:</strong><br>\n";
echo "1. Abra o arquivo painel/chat.php<br>\n";
echo "2. Localize a função enviarMensagemChat() (por volta da linha 2328)<br>\n";
echo "3. Substitua a função atual pela versão corrigida acima<br>\n";
echo "4. Salve o arquivo<br>\n";
echo "5. Teste o envio de mensagens<br>\n";
echo "</div>\n";

echo "<h2>🧪 Teste da Correção</h2>\n";
echo "<div class='test-section'>\n";
echo "<h3>Passos para testar:</h3>\n";
echo "<ol>\n";
echo "<li>Digite uma mensagem no campo de texto</li>\n";
echo "<li>Pressione Enter</li>\n";
echo "<li>Verifique se a mensagem é enviada sem modal de erro</li>\n";
echo "<li>Teste com mensagens que contenham espaços</li>\n";
echo "<li>Verifique o console do navegador para debug</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<h2>🔍 Debug Adicional</h2>\n";
echo "<div class='info'>\n";
echo "<strong>Para debug adicional, adicione este código temporário:</strong><br>\n";
echo "• Abra o console do navegador (F12)<br>\n";
echo "• Digite uma mensagem e pressione Enter<br>\n";
echo "• Verifique os logs no console<br>\n";
echo "• Isso ajudará a identificar se há problemas na captura do valor<br>\n";
echo "</div>\n";

echo "<div class='success'>✅ Correção identificada e solução proposta!</div>\n";
echo "<div class='info'>\n";
echo "<strong>Resumo da correção:</strong><br>\n";
echo "• Captura direta do valor do textarea<br>\n";
echo "• Validação melhorada de mensagens vazias<br>\n";
echo "• Debug para identificar problemas<br>\n";
echo "• Foco automático no campo após erro<br>\n";
echo "</div>\n";
?> 
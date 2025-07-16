// CRUD de Planos de Assinatura - JS

document.addEventListener('DOMContentLoaded', function () {
  carregarPlanos();

  // Abrir modal novo plano
  document.getElementById('btn-nova-assinatura').onclick = function() {
    abrirModalPlano();
  };

  // Fechar modal
  document.getElementById('close-modal-novo-plano').onclick = function() {
    fecharModalPlano();
  };

  // Submeter novo plano
  document.getElementById('form-novo-plano').onsubmit = function(e) {
    e.preventDefault();
    salvarPlano();
  };
});

function carregarPlanos() {
  fetch('api/planos_assinatura_listar.php')
    .then(r => r.json())
    .then(planos => {
      renderizarTabelaPlanos(planos);
    });
}

function renderizarTabelaPlanos(planos) {
  const tbody = document.getElementById('assinaturas-tbody');
  tbody.innerHTML = '';
  if (!planos.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Nenhum plano cadastrado.</td></tr>';
    return;
  }
  planos.forEach((plano, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="px-3 py-2 text-center">${idx + 1}</td>
      <td class="px-3 py-2">${plano.nome}</td>
      <td class="px-3 py-2">${plano.descricao || '-'}</td>
      <td class="px-3 py-2">R$ ${parseFloat(plano.valor).toFixed(2)}</td>
      <td class="px-3 py-2">${capitalize(plano.periodicidade)}</td>
      <td class="px-3 py-2">
        <button class="btn-editar-plano" data-id="${plano.id}">✏️</button>
        <button class="btn-excluir-plano" data-id="${plano.id}" style="margin-left:8px;">🗑️</button>
        ${plano.ativo == 0 ? '<span style="color:#b91c1c;font-size:0.95em;margin-left:8px;">(Inativo)</span>' : ''}
      </td>
    `;
    tbody.appendChild(tr);
  });
  // Eventos editar/excluir
  document.querySelectorAll('.btn-editar-plano').forEach(btn => {
    btn.onclick = function() {
      editarPlano(this.getAttribute('data-id'));
    };
  });
  document.querySelectorAll('.btn-excluir-plano').forEach(btn => {
    btn.onclick = function() {
      excluirPlano(this.getAttribute('data-id'));
    };
  });
}

function abrirModalPlano(plano) {
  document.getElementById('modal-novo-plano').style.display = 'flex';
  document.getElementById('status-novo-plano').textContent = '';
  if (plano) {
    document.getElementById('plano-nome').value = plano.nome;
    document.getElementById('plano-desc').value = plano.descricao;
    document.getElementById('plano-valor').value = plano.valor;
    document.getElementById('plano-periodicidade').value = plano.periodicidade;
    document.getElementById('form-novo-plano').setAttribute('data-id', plano.id);
  } else {
    document.getElementById('plano-nome').value = '';
    document.getElementById('plano-desc').value = '';
    document.getElementById('plano-valor').value = '';
    document.getElementById('plano-periodicidade').value = 'mensal';
    document.getElementById('form-novo-plano').removeAttribute('data-id');
  }
}

function fecharModalPlano() {
  document.getElementById('modal-novo-plano').style.display = 'none';
}

function salvarPlano() {
  const form = document.getElementById('form-novo-plano');
  const id = form.getAttribute('data-id');
  const nome = document.getElementById('plano-nome').value.trim();
  const descricao = document.getElementById('plano-desc').value.trim();
  const valor = document.getElementById('plano-valor').value;
  const periodicidade = document.getElementById('plano-periodicidade').value;
  const statusDiv = document.getElementById('status-novo-plano');
  if (!nome || !valor || !periodicidade) {
    statusDiv.textContent = 'Preencha todos os campos obrigatórios.';
    return;
  }
  const dados = new FormData();
  dados.append('nome', nome);
  dados.append('descricao', descricao);
  dados.append('valor', valor);
  dados.append('periodicidade', periodicidade);
  let url = 'api/planos_assinatura_criar.php';
  if (id) {
    dados.append('id', id);
    url = 'api/planos_assinatura_editar.php';
  }
  fetch(url, { method: 'POST', body: dados })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        statusDiv.textContent = 'Plano salvo com sucesso!';
        setTimeout(() => {
          fecharModalPlano();
          carregarPlanos();
        }, 900);
      } else {
        statusDiv.textContent = resp.error || 'Erro ao salvar plano.';
      }
    })
    .catch(() => {
      statusDiv.textContent = 'Erro ao conectar ao servidor.';
    });
}

function editarPlano(id) {
  fetch('api/planos_assinatura_listar.php')
    .then(r => r.json())
    .then(planos => {
      const plano = planos.find(p => String(p.id) === String(id));
      if (plano) abrirModalPlano(plano);
    });
}

function excluirPlano(id) {
  if (!confirm('Deseja realmente excluir/desativar este plano?')) return;
  const dados = new FormData();
  dados.append('id', id);
  fetch('api/planos_assinatura_excluir.php', { method: 'POST', body: dados })
    .then(r => r.json())
    .then(resp => {
      if (resp.success) {
        alert(resp.excluido ? 'Plano excluído!' : 'Plano desativado (existem assinaturas vinculadas).');
        carregarPlanos();
      } else {
        alert(resp.error || 'Erro ao excluir/desativar plano.');
      }
    })
    .catch(() => {
      alert('Erro ao conectar ao servidor.');
    });
}

function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
} 
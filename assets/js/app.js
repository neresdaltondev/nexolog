// Menu lateral no celular
document.getElementById('toggleMenu')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('open');
});

// Frete: adicionar e remover linhas de produto dinamicamente
const listaItens = document.getElementById('itens');
if (listaItens) {
  const modelo = document.getElementById('tpl-item');

  document.getElementById('addItem').addEventListener('click', () => {
    listaItens.appendChild(modelo.content.cloneNode(true));
    listaItens.lastElementChild.querySelector('select').focus();
  });

  listaItens.addEventListener('click', (ev) => {
    const botao = ev.target.closest('.btn-remove');
    if (!botao) return;
    // mantém sempre pelo menos uma linha na tela
    if (listaItens.children.length > 1) {
      botao.closest('.item-row').remove();
    } else {
      const linha = botao.closest('.item-row');
      linha.querySelector('select').value = '';
      linha.querySelector('input').value = 1;
    }
  });

  // Origem e destino não podem ser iguais
  const origem = document.getElementById('origem_id');
  const destino = document.getElementById('destino_id');
  const validarRota = () => {
    destino.setCustomValidity(origem.value && origem.value === destino.value
      ? 'Origem e destino devem ser diferentes.' : '');
  };
  origem.addEventListener('change', validarRota);
  destino.addEventListener('change', validarRota);
}

// Endereço: máscara de CEP e preenchimento automático (API ViaCEP)
const cep = document.getElementById('cep');
if (cep) {
  cep.addEventListener('input', () => {
    const d = cep.value.replace(/\D/g, '').slice(0, 8);
    cep.value = d.length > 5 ? d.slice(0, 5) + '-' + d.slice(5) : d;
  });
  cep.addEventListener('blur', async () => {
    const d = cep.value.replace(/\D/g, '');
    if (d.length !== 8) return;
    try {
      const r = await fetch(`https://viacep.com.br/ws/${d}/json/`);
      const j = await r.json();
      if (j.erro) return;
      document.getElementById('logradouro').value = j.logradouro || '';
      document.getElementById('bairro').value = j.bairro || '';
      document.getElementById('cidade').value = j.localidade || '';
      document.getElementById('uf').value = j.uf || '';
      document.getElementById('numero').focus();
    } catch (e) { /* sem internet: o usuário digita manualmente */ }
  });
}

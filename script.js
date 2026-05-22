// Espera todo o HTML carregar antes de rodar o JS
document.addEventListener('DOMContentLoaded', () => {

    /* ==============================================================
       1. FUNCIONALIDADES DO KANBAN (Página: Projetos.html)
    ============================================================== */
    const containerQuadros = document.getElementById('todos-os-quadros');
    
    if (containerQuadros) { 
        
        function inicializarEventosQuadro(quadro) {
            const colunasKanban = quadro.querySelectorAll('.kanban-cartoes');
            let cartoes = quadro.querySelectorAll('.kanban-cartao');

            // 1. Configurar arrastar e soltar
            cartoes.forEach(cartao => configurarCartaoArrastavel(cartao, quadro));

            colunasKanban.forEach(coluna => {
                coluna.addEventListener('dragover', (e) => {
                    e.preventDefault(); 
                    const cartaoSendoArrastado = document.querySelector('.dragging');
                    if (cartaoSendoArrastado) {
                        coluna.appendChild(cartaoSendoArrastado); 
                    }
                });

                coluna.addEventListener('drop', (e) => {
                    const cartao = document.querySelector('.dragging');
                    if (!cartao) return;
                    const cabecalhoColuna = coluna.parentElement.querySelector('h3').innerText;

                    if (cabecalhoColuna.includes('Fazendo')) {
                        const temPerfil = cartao.querySelector('.perfil-btn');
                        if (!temPerfil) {
                            let iniciais = prompt("Quem está assumindo esta tarefa? (Ex: Juliano");
                            if (iniciais && iniciais.trim() !== "") {
                                iniciais = iniciais.trim().substring(0, 2).toUpperCase();
                                const divPerfil = document.createElement('div');
                                divPerfil.classList.add('perfil-btn', 'miniatura');
                                divPerfil.innerText = iniciais;
                                const rodape = cartao.querySelector('.cartao-rodape');
                                if(rodape) rodape.appendChild(divPerfil);
                            }
                        }
                    }
                    if (cabecalhoColuna.includes('Concluído')) {
                        const textoData = cartao.querySelector('.cartao-data');
                        if(textoData) textoData.innerText = "Finalizado";
                    }
                });
            });

            // 2. Botões "+ Adicionar um cartão"
            quadro.querySelectorAll('.btn-add-cartao').forEach(botao => {
                const novoBotao = botao.cloneNode(true);
                botao.parentNode.replaceChild(novoBotao, botao);

                novoBotao.addEventListener('click', (e) => {
                    criarCartao(e.target.previousElementSibling, quadro);
                });
            });

            // 3. Botão "+ Nova Tarefa" no topo do quadro
            const btnNovaTarefa = quadro.querySelector('.btn-nova-tarefa');
            if (btnNovaTarefa) {
                const novoBtnTarefa = btnNovaTarefa.cloneNode(true);
                btnNovaTarefa.parentNode.replaceChild(novoBtnTarefa, btnNovaTarefa);

                novoBtnTarefa.addEventListener('click', () => {
                    const primeiraColuna = quadro.querySelector('.kanban-cartoes');
                    criarCartao(primeiraColuna, quadro);
                });
            }

            // 4. NOVIDADE: Botão Apagar Quadro (Lixeirinha)
            const btnApagarQuadro = quadro.querySelector('.btn-apagar-quadro');
            if (btnApagarQuadro) {
                const novoBtnApagar = btnApagarQuadro.cloneNode(true);
                btnApagarQuadro.parentNode.replaceChild(novoBtnApagar, btnApagarQuadro);

                novoBtnApagar.addEventListener('click', () => {
                    const senhaDigitada = prompt("ATENÇÃO: Para excluir este quadro, digite a senha do administrador:");
                    
                    if (senhaDigitada === null) {
                        return; // O usuário clicou em cancelar
                    }
                    
                    if (senhaDigitada === "1234") {
                        // Verifica se existe uma linha separadora <hr> antes deste quadro e a apaga também
                        const elementoAnterior = quadro.previousElementSibling;
                        if (elementoAnterior && elementoAnterior.tagName.toLowerCase() === 'hr') {
                            elementoAnterior.remove();
                        }
                        // Apaga o quadro inteiro da tela
                        quadro.remove();
                        alert("Quadro excluído com sucesso.");
                    } else {
                        alert("Senha incorreta! O quadro não foi excluído.");
                    }
                });
            }
        }

        // Função de arrastar
        function configurarCartaoArrastavel(cartao, quadro) {
            cartao.setAttribute('draggable', 'true'); 
            cartao.addEventListener('dragstart', () => {
                cartao.classList.add('dragging'); 
                cartao.style.opacity = '0.5';
            });
            cartao.addEventListener('dragend', () => {
                cartao.classList.remove('dragging');
                cartao.style.opacity = '1';
                atualizarContadoresGerais(); 
            });
        }

        // Função de criar cartão
        function criarCartao(colunaDestino, quadro) {
            const textoTarefa = prompt("Qual é o nome da nova tarefa?");
            if (textoTarefa && textoTarefa.trim() !== "") {
                const novoCartao = document.createElement('div');
                novoCartao.classList.add('kanban-cartao');
                novoCartao.innerHTML = `
                    <p class="cartao-titulo">${textoTarefa}</p>
                    <div class="cartao-rodape"><span class="cartao-data">Novo</span></div>
                `;
                configurarCartaoArrastavel(novoCartao, quadro); 
                colunaDestino.appendChild(novoCartao); 
                atualizarContadoresGerais();
            }
        }

        // Atualiza contadores
        function atualizarContadoresGerais() {
            document.querySelectorAll('.kanban-coluna').forEach(col => {
                const contador = col.querySelector('.contador-tarefas');
                if (contador) contador.innerText = col.querySelectorAll('.kanban-cartao').length;
            });
        }

        // Inicializa o primeiro quadro
        document.querySelectorAll('.quadro-wrapper').forEach(q => inicializarEventosQuadro(q));

        // Criar Novo Quadro
        const btnNovoQuadro = document.getElementById('btn-novo-quadro');
        if(btnNovoQuadro) {
            btnNovoQuadro.addEventListener('click', () => {
                const nomeQuadro = prompt("Qual será o nome do seu Novo Quadro?");
                
                if(nomeQuadro && nomeQuadro.trim() !== "") {
                    // Novo HTML incluindo o botão de lixeira no cabeçalho
                    const htmlNovoQuadro = `
                        <hr class="linha-separadora">
                        <div class="quadro-wrapper">
                            <div class="kanban-cabecalho">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <h2 class="titulo-quadro">${nomeQuadro}</h2>
                                    <button class="btn-apagar-quadro" title="Apagar Quadro">🗑️</button>
                                </div>
                                <button class="btn-primario btn-nova-tarefa">+ Nova Tarefa</button>
                            </div>
                            <div class="kanban-board">
                                <div class="kanban-coluna">
                                    <div class="coluna-cabecalho"><h3> A Fazer</h3><span class="contador-tarefas">0</span></div>
                                    <div class="kanban-cartoes"></div>
                                    <button class="btn-add-cartao">+ Adicionar um cartão</button>
                                </div>
                                <div class="kanban-coluna">
                                    <div class="coluna-cabecalho"><h3> Fazendo</h3><span class="contador-tarefas">0</span></div>
                                    <div class="kanban-cartoes"></div>
                                    <button class="btn-add-cartao">+ Adicionar um cartão</button>
                                </div>
                                <div class="kanban-coluna">
                                    <div class="coluna-cabecalho"><h3> Concluído</h3><span class="contador-tarefas">0</span></div>
                                    <div class="kanban-cartoes"></div>
                                    <button class="btn-add-cartao">+ Adicionar um cartão</button>
                                </div>
                            </div>
                        </div>
                    `;

                    containerQuadros.insertAdjacentHTML('beforeend', htmlNovoQuadro);

                    const todosQuadros = document.querySelectorAll('.quadro-wrapper');
                    const quadroRecemCriado = todosQuadros[todosQuadros.length - 1];
                    inicializarEventosQuadro(quadroRecemCriado);

                    quadroRecemCriado.scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
    }


    /* ==============================================================
       2. FUNCIONALIDADES DE DOCUMENTOS (Página: Documentos.html)
    ============================================================== */
    const barraPesquisa = document.querySelector('.barra-pesquisa input');
    
    if (barraPesquisa) { 
        const inputTitulo = document.querySelector('.input-titulo');
        const inputTexto = document.querySelector('.input-texto');
        const inputIndex = document.getElementById('document_index');
        const corpoTabela = document.querySelector('.tabela-docs tbody');
        let linhaEmEdicao = null; 

        barraPesquisa.addEventListener('input', (e) => {
            const textoPesquisa = e.target.value.toLowerCase();
            document.querySelectorAll('.tabela-docs tbody tr').forEach(linha => {
                linha.style.display = linha.innerText.toLowerCase().includes(textoPesquisa) ? '' : 'none';
            });
        });

        document.querySelectorAll('.sidebar-docs a').forEach(link => {
            link.addEventListener('click', (evento) => {
                evento.preventDefault(); 
                        let termoBusca = evento.target.innerText.replace(/[^\w\sà-úÀ-Ú]/g, '').trim(); 
        });

        if (corpoTabela) {
            corpoTabela.addEventListener('click', (e) => {
                linhaEmEdicao = e.target.closest('tr');
                if (!linhaEmEdicao) return;

                inputTitulo.value = linhaEmEdicao.querySelector('td').innerText;
                inputTexto.value = linhaEmEdicao.dataset.conteudo || '';
                inputIndex.value = linhaEmEdicao.dataset.index || '';
                inputTitulo.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }

        const btnNovoDoc = document.getElementById('btn-novo-doc');
        if(btnNovoDoc) {
            btnNovoDoc.addEventListener('click', () => {
                linhaEmEdicao = null; 
                inputTitulo.value = '';
                inputTexto.value = '';
                inputIndex.value = '';
                inputTitulo.focus();
            });
        }

        const btnSalvar = document.getElementById('btn-salvar-documento');
        if (btnSalvar) {
            btnSalvar.addEventListener('click', (evento) => {
                const tituloDigitado = inputTitulo.value.trim();
                if (tituloDigitado === '') {
                    evento.preventDefault();
                    alert("Por favor, dê um título ao seu documento!");
                }
            });
        }

        const btnCancelar = document.getElementById('btn-cancelar-documento');
        if (btnCancelar) {
            btnCancelar.addEventListener('click', () => {
                linhaEmEdicao = null;
                inputTitulo.value = '';
                inputTexto.value = '';
                inputIndex.value = '';
            });
        }
    }


    /* ==============================================================
       3. FUNCIONALIDADES DE LOGIN
    ============================================================== */
    const formLogin = document.getElementById('formLogin');
    if (formLogin) { 
        formLogin.addEventListener('submit', (evento) => {
            evento.preventDefault(); 
            window.location.href = 'index.html';
        });
    }
});
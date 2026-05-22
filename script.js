document.addEventListener('DOMContentLoaded', () => {
    initDocuments();
    initKanban();
});

function initDocuments() {
    const form = document.getElementById('document-form');

    if (!form) {
        return;
    }

    const apiUrl = window.portalConfig?.apiUrl || 'index.php?page=api';
    const idInput = document.getElementById('document-id');
    const titleInput = document.getElementById('document-title');
    const contentInput = document.getElementById('document-content');
    const attachmentInput = document.getElementById('document-attachment');
    const newButton = document.getElementById('btn-new-document');
    const cancelButton = document.getElementById('btn-cancel-document');

    document.querySelectorAll('[data-document-download]').forEach((link) => {
        link.addEventListener('click', (event) => event.stopPropagation());
    });

    document.querySelectorAll('[data-delete-document]').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.stopPropagation();

            const row = button.closest('[data-document-row]');
            const title = row?.dataset.title || 'este documento';

            if (!row || !confirm(`Excluir "${title}"?`)) {
                return;
            }

            const response = await postForm(apiUrl, {
                action: 'delete_document',
                document_id: row.dataset.id,
            });

            if (!response.ok) {
                alert(response.message || 'Nao foi possivel excluir o documento.');
                return;
            }

            if (idInput.value === row.dataset.id) {
                clearForm();
            }

            const tbody = row.parentElement;
            row.remove();

            if (tbody && tbody.querySelectorAll('[data-document-row]').length === 0) {
                tbody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Nenhum documento encontrado.</td>
                    </tr>
                `);
            }
        });
    });

    document.querySelectorAll('[data-document-row]').forEach((row) => {
        row.addEventListener('click', () => {
            idInput.value = row.dataset.id || '';
            titleInput.value = row.dataset.title || '';
            contentInput.value = row.dataset.content || '';

            if (attachmentInput) {
                attachmentInput.value = '';
            }

            titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            titleInput.focus();
        });
    });

    function clearForm() {
        idInput.value = '';
        titleInput.value = '';
        contentInput.value = '';

        if (attachmentInput) {
            attachmentInput.value = '';
        }

        titleInput.focus();
    }

    if (newButton) {
        newButton.addEventListener('click', clearForm);
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', clearForm);
    }

    form.addEventListener('submit', (event) => {
        if (titleInput.value.trim() === '') {
            event.preventDefault();
            alert('Informe um titulo para salvar o documento.');
            return;
        }

        if (attachmentInput?.files?.[0] && attachmentInput.files[0].size > 5 * 1024 * 1024) {
            event.preventDefault();
            alert('O arquivo precisa ter no maximo 5 MB.');
        }
    });
}

function initKanban() {
    const container = document.getElementById('boards-container');

    if (!container) {
        return;
    }

    const apiUrl = window.portalConfig?.apiUrl || 'index.php?page=api';
    const newBoardButton = document.getElementById('btn-new-board');
    const boardModal = document.getElementById('board-modal');
    const boardForm = document.getElementById('board-edit-form');
    const boardIdInput = document.getElementById('board-edit-id');
    const boardTitleInput = document.getElementById('board-edit-title');
    const taskModal = document.getElementById('task-modal');
    const taskForm = document.getElementById('task-edit-form');
    const taskIdInput = document.getElementById('task-edit-id');
    const taskTitleInput = document.getElementById('task-edit-title');
    const taskAssigneeInput = document.getElementById('task-edit-assignee');
    const feedbackContainer = document.getElementById('kanban-feedback');
    let activeBoard = null;
    let activeTask = null;

    setupBoardEvents(container);

    if (newBoardButton) {
        newBoardButton.addEventListener('click', async () => {
            const title = prompt('Nome do novo quadro:');

            if (!title || title.trim() === '') {
                return;
            }

            setButtonBusy(newBoardButton, true, 'Criando...');

            try {
                const response = await postForm(apiUrl, {
                    action: 'create_board',
                    title: title.trim(),
                });

                if (!response.ok) {
                    showToast(response.message || 'Nao foi possivel criar o quadro.', 'error', feedbackContainer);
                    return;
                }

                container.insertAdjacentHTML('afterbegin', renderBoard(response.board));
                container.querySelector('[data-empty-boards]')?.remove();
                const board = container.querySelector(`[data-board-id="${response.board.id}"]`);
                setupBoardEvents(board);
                flashElement(board);
                showToast('Projeto criado no kanban.', 'success', feedbackContainer);
            } finally {
                setButtonBusy(newBoardButton, false);
            }
        });
    }

    boardForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const title = boardTitleInput.value.trim();

        if (!activeBoard || title === '') {
            boardTitleInput.focus();
            return;
        }

        setModalBusy(boardForm, true, 'Salvando alteracoes do projeto...');
        activeBoard.classList.add('is-saving');

        try {
            const response = await postForm(apiUrl, {
                action: 'update_board',
                board_id: boardIdInput.value,
                title,
            });

            if (!response.ok) {
                setModalStatus(boardForm, response.message || 'Nao foi possivel atualizar o projeto.', 'error');
                showToast(response.message || 'Nao foi possivel atualizar o projeto.', 'error', feedbackContainer);
                return;
            }

            activeBoard.querySelector('[data-board-title]').textContent = response.board.title;
            setModalStatus(boardForm, 'Projeto salvo com sucesso.', 'success');
            flashElement(activeBoard);
            showToast('Projeto atualizado no kanban.', 'success', feedbackContainer);
            window.setTimeout(() => closeDialog(boardModal), 450);
        } finally {
            activeBoard?.classList.remove('is-saving');
            setModalBusy(boardForm, false);
        }
    });

    taskForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const title = taskTitleInput.value.trim();
        const assignee = taskAssigneeInput.value.trim();

        if (!activeTask || title === '') {
            taskTitleInput.focus();
            return;
        }

        setModalBusy(taskForm, true, 'Salvando alteracoes da tarefa...');
        activeTask.classList.add('is-saving');

        try {
            const response = await postForm(apiUrl, {
                action: 'update_task',
                task_id: taskIdInput.value,
                title,
                assignee_initials: assignee,
            });

            if (!response.ok) {
                setModalStatus(taskForm, response.message || 'Nao foi possivel atualizar a tarefa.', 'error');
                showToast(response.message || 'Nao foi possivel atualizar a tarefa.', 'error', feedbackContainer);
                return;
            }

            activeTask.querySelector('[data-task-title]').textContent = response.task.title;
            setTaskAssignee(activeTask, response.task.assignee_initials);
            setModalStatus(taskForm, 'Tarefa salva com sucesso.', 'success');
            flashElement(activeTask);
            showToast('Tarefa atualizada no kanban.', 'success', feedbackContainer);
            window.setTimeout(() => closeDialog(taskModal), 450);
        } finally {
            activeTask?.classList.remove('is-saving');
            setModalBusy(taskForm, false);
        }
    });

    boardModal?.querySelector('[data-close-board-modal]')?.addEventListener('click', () => closeDialog(boardModal));
    taskModal?.querySelector('[data-close-task-modal]')?.addEventListener('click', () => closeDialog(taskModal));

    function setupBoardEvents(scope) {
        scope.querySelectorAll('.task-card').forEach((card) => {
            if (card.dataset.ready === '1') {
                return;
            }

            card.dataset.ready = '1';
            card.addEventListener('dragstart', () => card.classList.add('dragging'));
            card.addEventListener('dragend', () => card.classList.remove('dragging'));
        });

        scope.querySelectorAll('[data-task-list]').forEach((list) => {
            if (list.dataset.ready === '1') {
                return;
            }

            list.dataset.ready = '1';

            list.addEventListener('dragover', (event) => {
                event.preventDefault();
                const card = document.querySelector('.task-card.dragging');

                if (!card || card.parentElement === list && !list.contains(card)) {
                    return;
                }

                const afterElement = getDragAfterElement(list, event.clientY);

                if (afterElement) {
                    list.insertBefore(card, afterElement);
                } else {
                    list.appendChild(card);
                }
            });

            list.addEventListener('drop', async () => {
                const card = list.querySelector('.task-card.dragging');

                if (!card) {
                    return;
                }

                const column = list.closest('.kanban-column');
                const assignee = needsAssignee(column, card)
                    ? prompt('Quem assumiu esta tarefa? Use as iniciais.')
                    : '';

                column.classList.add('is-saving');
                card.classList.add('is-saving');

                try {
                    const response = await postForm(apiUrl, {
                        action: 'move_task',
                        task_id: card.dataset.taskId,
                        column_id: column.dataset.columnId,
                        assignee_initials: assignee || '',
                        task_order: taskIdsInList(list),
                    });

                    if (!response.ok) {
                        showToast(response.message || 'Nao foi possivel mover a tarefa.', 'error', feedbackContainer);
                        window.location.reload();
                        return;
                    }

                    syncMovedTask(card, column, response.task, assignee);
                    updateCounters(card.closest('.board'));
                    flashElement(card);
                    showToast('Tarefa movida e salva.', 'success', feedbackContainer);
                } finally {
                    column.classList.remove('is-saving');
                    card.classList.remove('is-saving');
                }
            });
        });

        scope.querySelectorAll('[data-add-task]').forEach((button) => {
            if (button.dataset.ready === '1') {
                return;
            }

            button.dataset.ready = '1';
            button.addEventListener('click', () => {
                const column = button.closest('.kanban-column');
                const form = column.querySelector('[data-task-form]');
                const input = column.querySelector('[data-task-input]');

                button.classList.add('hidden');
                form.classList.remove('hidden');
                form.classList.add('grid');
                input.focus();
            });
        });

        scope.querySelectorAll('[data-task-form]').forEach((form) => {
            if (form.dataset.ready === '1') {
                return;
            }

            form.dataset.ready = '1';
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const column = form.closest('.kanban-column');
                const input = column.querySelector('[data-task-input]');
                const submitButton = form.querySelector('button[type="submit"]');
                const title = input.value.trim();

                if (title === '') {
                    input.focus();
                    return;
                }

                setButtonBusy(submitButton, true, 'Salvando...');
                column.classList.add('is-saving');

                try {
                    const response = await postForm(apiUrl, {
                        action: 'create_task',
                        board_id: column.dataset.boardId,
                        column_id: column.dataset.columnId,
                        title,
                    });

                    if (!response.ok) {
                        showToast(response.message || 'Nao foi possivel criar a tarefa.', 'error', feedbackContainer);
                        return;
                    }

                    column.querySelector('[data-task-list]').insertAdjacentHTML('beforeend', renderTask(response.task));
                    const card = column.querySelector(`[data-task-id="${response.task.id}"]`);
                    input.value = '';
                    hideTaskForm(column);
                    setupBoardEvents(column);
                    updateCounters(column.closest('.board'));
                    flashElement(card);
                    showToast('Tarefa criada e salva.', 'success', feedbackContainer);
                } finally {
                    column.classList.remove('is-saving');
                    setButtonBusy(submitButton, false);
                }
            });
        });

        scope.querySelectorAll('[data-cancel-task]').forEach((button) => {
            if (button.dataset.ready === '1') {
                return;
            }

            button.dataset.ready = '1';
            button.addEventListener('click', () => hideTaskForm(button.closest('.kanban-column')));
        });

        scope.querySelectorAll('[data-edit-board]').forEach((button) => {
            if (button.dataset.ready === '1') {
                return;
            }

            button.dataset.ready = '1';
            button.addEventListener('click', () => {
                activeBoard = button.closest('.board');
                boardIdInput.value = activeBoard.dataset.boardId || '';
                boardTitleInput.value = activeBoard.querySelector('[data-board-title]')?.textContent.trim() || '';
                clearModalStatus(boardForm);
                openDialog(boardModal);
                boardTitleInput.focus();
            });
        });

        scope.querySelectorAll('[data-edit-task]').forEach((button) => {
            if (button.dataset.ready === '1') {
                return;
            }

            button.dataset.ready = '1';
            button.addEventListener('click', () => {
                activeTask = button.closest('.task-card');
                taskIdInput.value = activeTask.dataset.taskId || '';
                taskTitleInput.value = activeTask.querySelector('[data-task-title]')?.textContent.trim() || '';
                taskAssigneeInput.value = activeTask.dataset.assignee || '';
                clearModalStatus(taskForm);
                openDialog(taskModal);
                taskTitleInput.focus();
            });
        });

        scope.querySelectorAll('[data-delete-task]').forEach((button) => {
            if (button.dataset.ready === '1') {
                return;
            }

            button.dataset.ready = '1';
            button.addEventListener('click', async () => {
                const card = button.closest('.task-card');
                const title = card.querySelector('[data-task-title]')?.textContent.trim() || 'esta tarefa';

                if (!confirm(`Excluir "${title}"?`)) {
                    return;
                }

                setButtonBusy(button, true, 'Excluindo...');
                card.classList.add('is-saving');

                try {
                    const response = await postForm(apiUrl, {
                        action: 'delete_task',
                        task_id: card.dataset.taskId,
                    });

                    if (!response.ok) {
                        showToast(response.message || 'Nao foi possivel excluir a tarefa.', 'error', feedbackContainer);
                        return;
                    }

                    const board = card.closest('.board');
                    card.remove();
                    updateCounters(board);
                    showToast('Tarefa excluida do kanban.', 'success', feedbackContainer);
                } finally {
                    card.classList.remove('is-saving');
                    setButtonBusy(button, false);
                }
            });
        });

        scope.querySelectorAll('[data-delete-board]').forEach((button) => {
            if (button.dataset.ready === '1') {
                return;
            }

            button.dataset.ready = '1';
            button.addEventListener('click', async () => {
                const board = button.closest('.board');
                const title = board.querySelector('h2')?.textContent.trim() || 'este quadro';

                if (!confirm(`Excluir "${title}" e todas as tarefas?`)) {
                    return;
                }

                setButtonBusy(button, true, 'Excluindo...');
                board.classList.add('is-saving');

                try {
                    const response = await postForm(apiUrl, {
                        action: 'delete_board',
                        board_id: board.dataset.boardId,
                    });

                    if (!response.ok) {
                        showToast(response.message || 'Nao foi possivel excluir o quadro.', 'error', feedbackContainer);
                        return;
                    }

                    board.remove();
                    showToast('Projeto excluido do kanban.', 'success', feedbackContainer);
                } finally {
                    board.classList.remove('is-saving');
                    setButtonBusy(button, false);
                }
            });
        });
    }

    function needsAssignee(column, card) {
        return column.querySelector('h3')?.textContent.trim() === 'Fazendo' &&
            !card.dataset.assignee;
    }
}

function openDialog(dialog) {
    if (!dialog) {
        return;
    }

    if (typeof dialog.showModal === 'function') {
        dialog.showModal();
        return;
    }

    dialog.setAttribute('open', '');
}

function closeDialog(dialog) {
    if (!dialog) {
        return;
    }

    if (typeof dialog.close === 'function') {
        dialog.close();
        return;
    }

    dialog.removeAttribute('open');
}

function hideTaskForm(column) {
    const form = column.querySelector('[data-task-form]');
    const input = column.querySelector('[data-task-input]');
    const button = column.querySelector('[data-add-task]');

    input.value = '';
    form.classList.add('hidden');
    form.classList.remove('grid');
    button.classList.remove('hidden');
}

function getDragAfterElement(list, y) {
    const cards = [...list.querySelectorAll('.task-card:not(.dragging)')];

    return cards.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset, element: child };
        }

        return closest;
    }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
}

function taskIdsInList(list) {
    return [...list.querySelectorAll('.task-card')].map((card) => card.dataset.taskId);
}

function syncMovedTask(card, column, task, assignee) {
    setTaskAssignee(card, task?.assignee_initials || assignee || card.dataset.assignee || '');

    const status = card.querySelector('footer span');

    if (status && task?.status_label) {
        status.textContent = task.status_label;
    }
}

function setTaskAssignee(card, assignee) {
    const normalized = String(assignee || '').trim().toUpperCase().slice(0, 8);
    const label = card.querySelector('[data-task-assignee]');

    card.dataset.assignee = normalized;

    if (label) {
        label.textContent = `Responsavel: ${normalized || 'sem encarregado'}`;
    }
}

function setModalBusy(form, isBusy, message = '') {
    const submitButton = form?.querySelector('[data-modal-submit]');

    form?.querySelectorAll('input, button').forEach((element) => {
        element.disabled = isBusy;
    });

    if (submitButton) {
        setButtonBusy(submitButton, isBusy, 'Salvando...');
    }

    if (isBusy && message) {
        setModalStatus(form, message, 'pending');
    }
}

function setModalStatus(form, message, type = 'pending') {
    const status = form?.querySelector('[data-modal-status]');

    if (!status) {
        return;
    }

    status.textContent = message;
    status.classList.remove('hidden', 'border-white/10', 'border-emerald-400/30', 'border-tuiured/30', 'bg-white/10', 'bg-emerald-400/15', 'bg-tuiured/15', 'text-slate-200', 'text-emerald-100', 'text-rose-100');

    if (type === 'success') {
        status.classList.add('border-emerald-400/30', 'bg-emerald-400/15', 'text-emerald-100');
        return;
    }

    if (type === 'error') {
        status.classList.add('border-tuiured/30', 'bg-tuiured/15', 'text-rose-100');
        return;
    }

    status.classList.add('border-white/10', 'bg-white/10', 'text-slate-200');
}

function clearModalStatus(form) {
    const status = form?.querySelector('[data-modal-status]');

    if (!status) {
        return;
    }

    status.textContent = '';
    status.classList.add('hidden');
}

function setButtonBusy(button, isBusy, label = 'Salvando...') {
    if (!button) {
        return;
    }

    if (isBusy) {
        button.dataset.originalText = button.textContent;
        button.textContent = label;
        button.disabled = true;
        return;
    }

    button.textContent = button.dataset.originalText || button.textContent;
    button.disabled = false;
    delete button.dataset.originalText;
}

function flashElement(element) {
    if (!element) {
        return;
    }

    element.classList.remove('is-confirmed');
    void element.offsetWidth;
    element.classList.add('is-confirmed');
}

function showToast(message, type = 'success', container = null) {
    if (!container) {
        alert(message);
        return;
    }

    const toast = document.createElement('div');
    toast.className = [
        'rounded-2xl border px-4 py-3 text-sm font-extrabold shadow-2xl shadow-black/30 backdrop-blur',
        type === 'error'
            ? 'border-tuiured/30 bg-tuiured/20 text-rose-100'
            : 'border-emerald-400/30 bg-emerald-400/20 text-emerald-100',
    ].join(' ');
    toast.textContent = message;
    container.appendChild(toast);

    window.setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-0.35rem)';
    }, 2600);

    window.setTimeout(() => toast.remove(), 3200);
}

async function postForm(url, values) {
    const body = new URLSearchParams();

    Object.entries(values).forEach(([key, value]) => {
        if (Array.isArray(value)) {
            value.forEach((item) => body.append(`${key}[]`, item));
            return;
        }

        body.append(key, value ?? '');
    });

    try {
        const request = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
            },
            body,
        });
        const text = await request.text();
        const payload = text ? JSON.parse(text) : {};

        if (!request.ok && payload.ok !== false) {
            return { ok: false, message: 'A acao nao foi concluida no servidor.' };
        }

        return payload;
    } catch (error) {
        return {
            ok: false,
            message: 'Nao foi possivel confirmar a acao. Verifique a conexao e tente novamente.',
        };
    }
}

function updateCounters(board) {
    if (!board) {
        return;
    }

    board.querySelectorAll('.kanban-column').forEach((column) => {
        const counter = column.querySelector('.task-count');

        if (counter) {
            counter.textContent = column.querySelectorAll('.task-card').length;
        }
    });
}

function renderBoard(board) {
    const columns = (board.columns || []).map((column) => `
        <section class="kanban-column min-w-[260px] rounded-[1.35rem] border border-white/10 bg-black/20 p-4" data-column-id="${escapeHtml(column.id)}" data-board-id="${escapeHtml(board.id)}">
            <header class="mb-3 flex items-center justify-between gap-3">
                <h3 class="font-extrabold text-slate-100">${escapeHtml(column.title)}</h3>
                <span class="task-count rounded-full bg-white/10 px-3 py-1 text-sm font-extrabold text-slate-300">0</span>
            </header>
            <div class="task-list grid min-h-16 gap-3" data-task-list></div>
            ${renderTaskForm()}
            <button class="mt-3 w-full rounded-2xl border border-dashed border-white/20 px-4 py-3 font-extrabold text-slate-300 transition hover:-translate-y-0.5 hover:bg-white/10 hover:text-white" type="button" data-add-task>Adicionar tarefa</button>
        </section>
    `).join('');

    return `
        <article class="board rounded-[1.75rem] border border-white/10 bg-slate-950/70 p-5 shadow-2xl shadow-black/20 backdrop-blur" data-board-id="${escapeHtml(board.id)}">
            <header class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <h2 class="text-2xl font-extrabold" data-board-title>${escapeHtml(board.title)}</h2>
                <div class="flex flex-wrap gap-2">
                    <button class="rounded-2xl border border-white/10 bg-white/10 px-4 py-2 font-extrabold text-white transition hover:-translate-y-0.5 hover:bg-white/15" type="button" data-edit-board>Editar projeto</button>
                    <button class="rounded-2xl border border-tuiured/30 bg-tuiured/10 px-4 py-2 font-extrabold text-rose-100 transition hover:-translate-y-0.5 hover:bg-tuiured/20" type="button" data-delete-board>Excluir</button>
                </div>
            </header>
            <div class="grid gap-4 overflow-x-auto lg:grid-cols-3">${columns}</div>
        </article>
    `;
}

function renderTask(task) {
    const assignee = String(task.assignee_initials || '').trim().toUpperCase();

    return `
        <article class="task-card rounded-2xl border border-white/10 bg-white/[0.07] p-4 shadow-lg shadow-black/10" draggable="true" data-task-id="${escapeHtml(task.id)}" data-assignee="${escapeHtml(assignee)}">
            <div class="mb-4 flex items-start justify-between gap-3">
                <p class="leading-6" data-task-title>${escapeHtml(task.title)}</p>
                <div class="flex shrink-0 gap-2">
                    <button class="rounded-xl border border-white/10 px-2 py-1 text-xs font-extrabold text-slate-300 transition hover:bg-white/10 hover:text-white" type="button" data-edit-task>Editar</button>
                    <button class="rounded-xl border border-tuiured/30 bg-tuiured/10 px-2 py-1 text-xs font-extrabold text-rose-100 transition hover:bg-tuiured/20" type="button" data-delete-task>Excluir</button>
                </div>
            </div>
            <footer class="flex items-center justify-between gap-3">
                <span class="text-sm font-bold text-slate-400">${escapeHtml(task.status_label || 'Em aberto')}</span>
                <span class="rounded-xl bg-white/10 px-3 py-2 text-xs font-extrabold text-slate-200" data-task-assignee>Responsavel: ${escapeHtml(assignee || 'sem encarregado')}</span>
            </footer>
        </article>
    `;
}

function renderTaskForm() {
    return `
        <form class="mt-3 hidden gap-2" data-task-form>
            <input class="h-11 rounded-2xl border border-white/10 bg-white/10 px-3 text-sm font-bold text-white outline-none placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" type="text" data-task-input placeholder="Nova tarefa">
            <div class="grid grid-cols-2 gap-2">
                <button class="rounded-2xl bg-gradient-to-r from-tuiublue to-blue-700 px-3 py-2 text-sm font-extrabold text-white" type="submit">Salvar</button>
                <button class="rounded-2xl border border-white/10 px-3 py-2 text-sm font-extrabold text-slate-300" type="button" data-cancel-task>Cancelar</button>
            </div>
        </form>
    `;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

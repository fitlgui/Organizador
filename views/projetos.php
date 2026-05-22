<main class="mx-auto max-w-7xl px-4 py-8">
    <section class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="mb-2 text-xs font-extrabold uppercase tracking-[0.18em] text-tuiublue">Projetos</p>
            <h1 class="text-4xl font-extrabold md:text-5xl">Kanban da temporada</h1>
        </div>
        <button class="h-12 rounded-2xl bg-gradient-to-r from-tuiublue to-blue-700 px-5 font-extrabold text-white shadow-lg shadow-tuiublue/20 transition hover:-translate-y-0.5" type="button" id="btn-new-board">Criar quadro</button>
    </section>

    <section id="boards-container" class="grid gap-7">
        <?php if ($boards === []): ?>
            <article class="rounded-[1.5rem] border border-dashed border-white/20 bg-slate-950/70 p-8 text-center text-slate-300" data-empty-boards>
                <h2 class="text-2xl font-extrabold text-white">Nenhum quadro criado ainda</h2>
                <p class="mt-2 font-semibold">Crie o primeiro quadro para organizar as tarefas da equipe.</p>
            </article>
        <?php endif; ?>
        <?php foreach ($boards as $board): ?>
            <?php if ($board === []) continue; ?>
            <article class="board rounded-[1.75rem] border border-white/10 bg-slate-950/70 p-5 shadow-2xl shadow-black/20 backdrop-blur" data-board-id="<?= (int) $board['id'] ?>">
                <header class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <h2 class="text-2xl font-extrabold" data-board-title><?= h($board['title']) ?></h2>
                    <div class="flex flex-wrap gap-2">
                        <button class="rounded-2xl border border-white/10 bg-white/10 px-4 py-2 font-extrabold text-white transition hover:-translate-y-0.5 hover:bg-white/15" type="button" data-edit-board>Editar projeto</button>
                        <button class="rounded-2xl border border-tuiured/30 bg-tuiured/10 px-4 py-2 font-extrabold text-rose-100 transition hover:-translate-y-0.5 hover:bg-tuiured/20" type="button" data-delete-board>Excluir</button>
                    </div>
                </header>

                <div class="grid gap-4 overflow-x-auto lg:grid-cols-3">
                    <?php foreach ($board['columns'] as $column): ?>
                        <section class="kanban-column min-w-[260px] rounded-[1.35rem] border border-white/10 bg-black/20 p-4" data-column-id="<?= (int) $column['id'] ?>" data-board-id="<?= (int) $board['id'] ?>">
                            <header class="mb-3 flex items-center justify-between gap-3">
                                <h3 class="font-extrabold text-slate-100"><?= h($column['title']) ?></h3>
                                <span class="task-count rounded-full bg-white/10 px-3 py-1 text-sm font-extrabold text-slate-300"><?= count($column['tasks']) ?></span>
                            </header>

                            <div class="task-list grid min-h-16 gap-3" data-task-list>
                                <?php foreach ($column['tasks'] as $task): ?>
                                    <article class="task-card rounded-2xl border border-white/10 bg-white/[0.07] p-4 shadow-lg shadow-black/10" draggable="true" data-task-id="<?= (int) $task['id'] ?>" data-assignee="<?= h($task['assignee_initials'] ?: '') ?>">
                                        <div class="mb-4 flex items-start justify-between gap-3">
                                            <p class="leading-6" data-task-title><?= h($task['title']) ?></p>
                                            <div class="flex shrink-0 gap-2">
                                                <button class="rounded-xl border border-white/10 px-2 py-1 text-xs font-extrabold text-slate-300 transition hover:bg-white/10 hover:text-white" type="button" data-edit-task>Editar</button>
                                                <button class="rounded-xl border border-tuiured/30 bg-tuiured/10 px-2 py-1 text-xs font-extrabold text-rose-100 transition hover:bg-tuiured/20" type="button" data-delete-task>Excluir</button>
                                            </div>
                                        </div>
                                        <footer class="flex items-center justify-between gap-3">
                                            <span class="text-sm font-bold text-slate-400"><?= h($task['status_label'] ?: 'Em aberto') ?></span>
                                            <span class="rounded-xl bg-white/10 px-3 py-2 text-xs font-extrabold text-slate-200" data-task-assignee>
                                                Responsavel: <?= h($task['assignee_initials'] ?: 'sem encarregado') ?>
                                            </span>
                                        </footer>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <form class="mt-3 hidden gap-2" data-task-form>
                                <input class="h-11 rounded-2xl border border-white/10 bg-white/10 px-3 text-sm font-bold text-white outline-none placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" type="text" data-task-input placeholder="Nova tarefa">
                                <div class="grid grid-cols-2 gap-2">
                                    <button class="rounded-2xl bg-gradient-to-r from-tuiublue to-blue-700 px-3 py-2 text-sm font-extrabold text-white" type="submit">Salvar</button>
                                    <button class="rounded-2xl border border-white/10 px-3 py-2 text-sm font-extrabold text-slate-300" type="button" data-cancel-task>Cancelar</button>
                                </div>
                            </form>
                            <button class="mt-3 w-full rounded-2xl border border-dashed border-white/20 px-4 py-3 font-extrabold text-slate-300 transition hover:-translate-y-0.5 hover:bg-white/10 hover:text-white" type="button" data-add-task>Adicionar tarefa</button>
                        </section>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <div id="kanban-feedback" class="fixed right-4 top-24 z-50 grid w-[min(92vw,24rem)] gap-2" aria-live="polite" aria-atomic="true"></div>

    <dialog id="board-modal" class="w-[min(92vw,32rem)] rounded-[1.5rem] border border-white/10 bg-slate-950 p-0 text-white shadow-2xl shadow-black/40 backdrop:bg-black/70">
        <form class="grid gap-4 p-6" method="dialog" id="board-edit-form">
            <input type="hidden" id="board-edit-id">
            <div>
                <p class="mb-2 text-xs font-extrabold uppercase tracking-[0.18em] text-tuiublue">Projeto</p>
                <h2 class="text-2xl font-extrabold">Editar projeto</h2>
                <p class="mt-2 text-sm font-semibold text-slate-400">Renomeie o quadro. Ao salvar, o titulo atualiza imediatamente no kanban.</p>
            </div>
            <label class="grid gap-2 font-bold text-slate-200">
                Nome do projeto
                <input class="h-12 rounded-2xl border border-white/10 bg-white/10 px-4 text-white outline-none placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" type="text" id="board-edit-title" required>
            </label>
            <p class="hidden rounded-2xl border px-4 py-3 text-sm font-bold" data-modal-status></p>
            <div class="flex flex-wrap justify-end gap-3">
                <button class="h-12 rounded-2xl border border-white/10 bg-white/10 px-5 font-extrabold text-white transition hover:bg-white/15" type="button" data-close-board-modal>Cancelar</button>
                <button class="h-12 rounded-2xl bg-gradient-to-r from-tuiublue to-blue-700 px-5 font-extrabold text-white shadow-lg shadow-tuiublue/20 disabled:cursor-wait disabled:opacity-60" type="submit" data-modal-submit>Salvar</button>
            </div>
        </form>
    </dialog>

    <dialog id="task-modal" class="w-[min(92vw,34rem)] rounded-[1.5rem] border border-white/10 bg-slate-950 p-0 text-white shadow-2xl shadow-black/40 backdrop:bg-black/70">
        <form class="grid gap-4 p-6" method="dialog" id="task-edit-form">
            <input type="hidden" id="task-edit-id">
            <div>
                <p class="mb-2 text-xs font-extrabold uppercase tracking-[0.18em] text-tuiublue">Tarefa</p>
                <h2 class="text-2xl font-extrabold">Editar tarefa</h2>
                <p class="mt-2 text-sm font-semibold text-slate-400">Atualize a descricao e deixe o responsavel visivel no cartao.</p>
            </div>
            <label class="grid gap-2 font-bold text-slate-200">
                Nome da tarefa
                <input class="h-12 rounded-2xl border border-white/10 bg-white/10 px-4 text-white outline-none placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" type="text" id="task-edit-title" required>
            </label>
            <label class="grid gap-2 font-bold text-slate-200">
                Responsavel
                <input class="h-12 rounded-2xl border border-white/10 bg-white/10 px-4 text-white outline-none placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" type="text" id="task-edit-assignee" maxlength="8" placeholder="Ex: JF">
            </label>
            <p class="hidden rounded-2xl border px-4 py-3 text-sm font-bold" data-modal-status></p>
            <div class="flex flex-wrap justify-end gap-3">
                <button class="h-12 rounded-2xl border border-white/10 bg-white/10 px-5 font-extrabold text-white transition hover:bg-white/15" type="button" data-close-task-modal>Cancelar</button>
                <button class="h-12 rounded-2xl bg-gradient-to-r from-tuiublue to-blue-700 px-5 font-extrabold text-white shadow-lg shadow-tuiublue/20 disabled:cursor-wait disabled:opacity-60" type="submit" data-modal-submit>Salvar</button>
            </div>
        </form>
    </dialog>
</main>

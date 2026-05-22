<main class="mx-auto grid max-w-7xl gap-6 px-4 py-8 lg:grid-cols-[240px_1fr]">
    <aside class="h-fit rounded-[1.5rem] border border-white/10 bg-slate-950/70 p-5 shadow-2xl shadow-black/20 backdrop-blur lg:sticky lg:top-28">
        <p class="mb-4 text-xs font-extrabold uppercase tracking-[0.18em] text-tuiublue">Categorias</p>
        <div class="grid gap-2">
            <a class="rounded-2xl px-3 py-2 font-bold text-slate-300 no-underline transition hover:bg-white/10 hover:text-white" href="<?= h(route_url('documentos', ['q' => 'Mecânica'])) ?>">Mecânica & Design</a>
            <a class="rounded-2xl px-3 py-2 font-bold text-slate-300 no-underline transition hover:bg-white/10 hover:text-white" href="<?= h(route_url('documentos', ['q' => 'Programação'])) ?>">Programação & Autônomo</a>
            <a class="rounded-2xl px-3 py-2 font-bold text-slate-300 no-underline transition hover:bg-white/10 hover:text-white" href="<?= h(route_url('documentos', ['q' => 'Gestão'])) ?>">Gestão & Marketing</a>
            <a class="rounded-2xl px-3 py-2 font-bold text-slate-300 no-underline transition hover:bg-white/10 hover:text-white" href="<?= h(route_url('documentos', ['q' => 'Ata'])) ?>">Atas de Reunião</a>
        </div>
    </aside>

    <section class="grid gap-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="mb-2 text-xs font-extrabold uppercase tracking-[0.18em] text-tuiublue">Documentos</p>
                <h1 class="text-4xl font-extrabold md:text-5xl">Biblioteca da equipe</h1>
            </div>
            <a class="inline-flex h-12 items-center justify-center rounded-2xl bg-emerald-400 px-5 font-extrabold text-emerald-950 no-underline transition hover:-translate-y-0.5" href="https://drive.google.com/drive/folders/1ma3IiRQ9hUQKY1gMS4HG45zitu5vhwEq">Acessar Drive</a>
        </div>

        <?php if ($notice): ?>
            <p class="rounded-2xl border p-4 font-bold <?= $noticeType === 'error' ? 'border-tuiured/30 bg-tuiured/15 text-rose-100' : 'border-emerald-400/30 bg-emerald-400/15 text-emerald-100' ?>"><?= h($notice) ?></p>
        <?php endif; ?>

        <form class="grid gap-3 md:grid-cols-[1fr_auto_auto]" method="get">
            <input type="hidden" name="page" value="documentos">
            <input class="h-12 rounded-2xl border border-white/10 bg-white/10 px-4 text-white outline-none placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" type="search" name="q" value="<?= h($search) ?>" placeholder="Pesquisar documentos...">
            <button class="h-12 rounded-2xl border border-white/10 bg-white/10 px-5 font-extrabold text-white transition hover:-translate-y-0.5" type="submit">Pesquisar</button>
            <button class="h-12 rounded-2xl bg-gradient-to-r from-tuiublue to-blue-700 px-5 font-extrabold text-white shadow-lg shadow-tuiublue/20 transition hover:-translate-y-0.5" type="button" id="btn-new-document">Novo documento</button>
        </form>

        <section class="rounded-[1.5rem] border border-white/10 bg-slate-950/70 p-5 shadow-2xl shadow-black/20 backdrop-blur">
            <h2 class="mb-4 text-2xl font-extrabold">Documentos recentes</h2>
            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full border-collapse">
                    <thead>
                    <tr class="border-b border-white/10 text-left text-xs uppercase tracking-[0.14em] text-slate-400">
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Autor</th>
                        <th class="px-4 py-3">Arquivo</th>
                        <th class="px-4 py-3 text-right">Acoes</th>
                        <th class="px-4 py-3">Última modificação</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr
                            class="cursor-pointer border-b border-white/10 transition hover:bg-white/[0.06]"
                            data-document-row
                            data-id="<?= (int) $document['id'] ?>"
                            data-title="<?= h($document['title']) ?>"
                            data-content="<?= h($document['content']) ?>"
                        >
                            <td class="px-4 py-4 font-bold"><?= h($document['title']) ?></td>
                            <td class="px-4 py-4 text-slate-300"><?= h($document['author_name']) ?></td>
                            <td class="px-4 py-4 text-slate-300">
                                <?php if (!empty($document['file_id'])): ?>
                                    <a class="font-bold text-tuiublue no-underline hover:text-white" href="<?= h(route_url('download', ['file_id' => (int) $document['file_id']])) ?>" data-document-download>
                                        <?= h($document['file_name']) ?>
                                        <span class="text-slate-400">(<?= h(format_bytes((int) $document['file_size'])) ?>)</span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400">Sem arquivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <button class="rounded-xl border border-tuiured/30 bg-tuiured/10 px-3 py-2 text-sm font-extrabold text-rose-100 transition hover:bg-tuiured/20" type="button" data-delete-document>Excluir</button>
                            </td>
                            <td class="px-4 py-4 text-slate-300"><?= h(date('d/m/Y H:i', strtotime($document['updated_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($documents === []): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">Nenhum documento encontrado.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-[1.5rem] border border-white/10 bg-slate-950/70 p-5 shadow-2xl shadow-black/20 backdrop-blur">
            <h2 class="mb-4 text-2xl font-extrabold">Criar ou editar documento</h2>
            <form method="post" enctype="multipart/form-data" id="document-form" class="grid gap-4">
                <input type="hidden" name="document_id" id="document-id">
                <input type="hidden" name="MAX_FILE_SIZE" value="<?= (int) $maxUploadBytes ?>">
                <input class="h-14 rounded-2xl border border-white/10 bg-white/10 px-4 text-xl font-extrabold text-white outline-none placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" type="text" name="title" id="document-title" placeholder="Título do documento" required>
                <textarea class="min-h-64 rounded-2xl border border-white/10 bg-white/10 p-4 leading-7 text-white outline-none placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" name="content" id="document-content" placeholder="Comece a escrever..."></textarea>
                <label class="grid gap-2 rounded-2xl border border-dashed border-white/20 bg-white/[0.06] p-4 font-bold text-slate-200">
                    Anexar arquivo (max. <?= h(format_bytes((int) $maxUploadBytes)) ?>)
                    <input class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm text-slate-300 file:mr-3 file:rounded-xl file:border-0 file:bg-tuiublue file:px-3 file:py-2 file:font-extrabold file:text-white" type="file" name="attachment" id="document-attachment">
                    <span class="text-sm font-semibold text-slate-400">Ao enviar um novo arquivo em um documento existente, ele substitui o anexo anterior.</span>
                </label>
                <div class="flex flex-wrap gap-3">
                    <button class="h-12 rounded-2xl bg-gradient-to-r from-tuiublue to-blue-700 px-5 font-extrabold text-white shadow-lg shadow-tuiublue/20 transition hover:-translate-y-0.5" type="submit">Salvar documento</button>
                    <button class="h-12 rounded-2xl border border-white/10 bg-white/10 px-5 font-extrabold text-white transition hover:-translate-y-0.5" type="button" id="btn-cancel-document">Cancelar</button>
                </div>
            </form>
        </section>
    </section>
</main>

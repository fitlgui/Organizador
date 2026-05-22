<main class="mx-auto max-w-7xl px-4 py-8">
    <section class="mb-6">
        <p class="mb-2 text-xs font-extrabold uppercase tracking-[0.18em] text-tuiublue">Configuracoes</p>
        <h1 class="text-4xl font-extrabold md:text-5xl">Uso do banco de dados</h1>
    </section>

    <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
            <span class="text-sm font-bold text-slate-400">Banco</span>
            <strong class="mt-2 block break-words text-2xl font-extrabold"><?= h($storage['database']) ?></strong>
        </article>
        <article class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
            <span class="text-sm font-bold text-slate-400">Uso total</span>
            <strong class="mt-2 block text-4xl font-extrabold"><?= h($storage['total_human']) ?></strong>
        </article>
        <article class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
            <span class="text-sm font-bold text-slate-400">Arquivos anexados</span>
            <strong class="mt-2 block text-4xl font-extrabold"><?= (int) $storage['files'] ?></strong>
            <span class="text-sm font-bold text-slate-400"><?= h($storage['file_human']) ?></span>
        </article>
        <article class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
            <span class="text-sm font-bold text-slate-400">Itens</span>
            <strong class="mt-2 block text-4xl font-extrabold"><?= (int) $storage['documents'] + (int) $storage['tasks'] ?></strong>
            <span class="text-sm font-bold text-slate-400"><?= (int) $storage['documents'] ?> docs, <?= (int) $storage['tasks'] ?> tarefas</span>
        </article>
    </section>

    <section class="mt-6 rounded-[1.5rem] border border-white/10 bg-slate-950/70 p-5 shadow-2xl shadow-black/20 backdrop-blur">
        <h2 class="mb-4 text-2xl font-extrabold">Resumo por tabela</h2>
        <div class="overflow-x-auto">
            <table class="min-w-[640px] w-full border-collapse">
                <thead>
                <tr class="border-b border-white/10 text-left text-xs uppercase tracking-[0.14em] text-slate-400">
                    <th class="px-4 py-3">Tabela</th>
                    <th class="px-4 py-3">Linhas estimadas</th>
                    <th class="px-4 py-3">Uso</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($storage['tables'] as $table): ?>
                    <tr class="border-b border-white/10">
                        <td class="px-4 py-4 font-bold"><?= h($table['name']) ?></td>
                        <td class="px-4 py-4 text-slate-300"><?= (int) $table['rows'] ?></td>
                        <td class="px-4 py-4 text-slate-300"><?= h($table['human']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

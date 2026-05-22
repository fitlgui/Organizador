<main class="grid min-h-screen place-items-center px-4 py-10">
    <section class="w-full max-w-2xl rounded-[2rem] border border-white/10 bg-slate-950/80 p-8 shadow-2xl shadow-black/40 backdrop-blur">
        <img src="Syle.png" alt="Logo Tuiutech" class="mb-4 h-20 w-auto">
        <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-tuiublue">Portal Tuiutech</p>
        <h1 class="mb-4 text-3xl font-extrabold md:text-5xl">Configuração necessária</h1>

        <?php if (!empty($missing)): ?>
            <p class="mb-5 leading-7 text-slate-300">Preencha o arquivo <code class="rounded bg-white/10 px-2 py-1 text-slate-100">.env</code> com as variáveis abaixo para conectar ao MySQL:</p>
            <pre class="overflow-x-auto rounded-2xl border border-white/10 bg-white/10 p-4 text-sm text-slate-100"><?php foreach ($missing as $key): ?><?= h($key) ?>=
<?php endforeach; ?></pre>
        <?php else: ?>
            <p class="mb-5 leading-7 text-slate-300">Não foi possível conectar ao banco de dados. Confira host, usuário, senha e nome do banco.</p>
            <?php if (!empty($errorMessage)): ?>
                <p class="rounded-2xl border border-tuiured/30 bg-tuiured/15 p-4 font-bold text-rose-100"><?= h($errorMessage) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<main class="grid min-h-screen place-items-center px-4 py-10">
    <section class="w-full max-w-md rounded-[2rem] border border-white/10 bg-slate-950/80 p-8 shadow-2xl shadow-black/40 backdrop-blur">
        <img src="Syle.png" alt="Logo Tuiutech" class="mb-4 h-20 w-auto">
        <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-tuiublue">Acesso da equipe</p>
        <h1 class="mb-3 text-3xl font-extrabold">Entrar no portal</h1>
        <p class="mb-6 leading-7 text-slate-400">Organize documentos, projetos e tarefas da Tuiutech em um só lugar.</p>

        <?php if ($error): ?>
            <p class="mb-4 rounded-2xl border border-tuiured/30 bg-tuiured/15 p-4 font-bold text-rose-100"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post" class="grid gap-4">
            <label class="grid gap-2 text-sm font-bold text-slate-300">
                E-mail
                <input class="h-12 rounded-2xl border border-white/10 bg-white/10 px-4 text-white outline-none transition placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" type="email" name="email" placeholder="voce@tuiutech.com" required>
            </label>
            <label class="grid gap-2 text-sm font-bold text-slate-300">
                Senha
                <input class="h-12 rounded-2xl border border-white/10 bg-white/10 px-4 text-white outline-none transition placeholder:text-slate-500 focus:border-tuiublue focus:ring-4 focus:ring-tuiublue/15" type="password" name="password" placeholder="Sua senha" required>
            </label>
            <button class="h-12 rounded-2xl bg-gradient-to-r from-tuiublue to-blue-700 font-extrabold text-white shadow-lg shadow-tuiublue/20 transition hover:-translate-y-0.5" type="submit">Entrar</button>
        </form>
    </section>
</main>

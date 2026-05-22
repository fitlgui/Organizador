<main class="mx-auto max-w-7xl px-4 py-8 md:py-12">
    <section class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/70 p-7 shadow-2xl shadow-black/30 backdrop-blur md:grid md:min-h-[360px] md:grid-cols-[1fr_270px] md:items-center md:gap-10 md:p-12">
        <div class="absolute inset-x-1/3 bottom-[-8rem] h-56 rounded-full bg-gradient-to-r from-tuiublue/40 to-tuiured/30 blur-3xl"></div>
        <div class="relative">
            <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.18em] text-tuiublue">Portal Tuiutech</p>
            <h1 class="mb-5 max-w-4xl text-4xl font-extrabold leading-none md:text-6xl">Operação da equipe, documentos e robótica no mesmo painel.</h1>
            <p class="max-w-2xl text-lg leading-8 text-slate-300">Um espaço rápido para acompanhar preparação, decisões técnicas e fluxo de projetos da temporada.</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="inline-flex h-12 items-center justify-center rounded-2xl bg-gradient-to-r from-tuiublue to-blue-700 px-5 font-extrabold text-white no-underline shadow-lg shadow-tuiublue/20 transition hover:-translate-y-0.5" href="<?= h(route_url('projetos')) ?>">Abrir projetos</a>
                <a class="inline-flex h-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10 px-5 font-extrabold text-white no-underline transition hover:-translate-y-0.5 hover:bg-white/15" href="<?= h(route_url('documentos')) ?>">Ver documentos</a>
            </div>
        </div>
        <div class="relative mt-8 rounded-[1.75rem] border border-white/10 bg-white/10 p-6 md:mt-0">
            <img src="Syle.png" alt="Logo Tuiutech" class="mb-4 h-auto w-44">
            <strong class="block text-4xl font-extrabold">FIRST</strong>
            <span class="font-bold text-slate-400">Robotics mindset</span>
        </div>
    </section>

    <section class="mt-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
            <span class="text-sm font-bold text-slate-400">Documentos</span>
            <strong class="mt-2 block text-4xl font-extrabold"><?= (int) $stats['documents'] ?></strong>
        </article>
        <article class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
            <span class="text-sm font-bold text-slate-400">Quadros</span>
            <strong class="mt-2 block text-4xl font-extrabold"><?= (int) $stats['boards'] ?></strong>
        </article>
        <article class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
            <span class="text-sm font-bold text-slate-400">Tarefas</span>
            <strong class="mt-2 block text-4xl font-extrabold"><?= (int) $stats['tasks'] ?></strong>
        </article>
        <article class="rounded-3xl border border-white/10 bg-white/[0.06] p-5">
            <span class="text-sm font-bold text-slate-400">Finalizadas</span>
            <strong class="mt-2 block text-4xl font-extrabold"><?= (int) $stats['done'] ?></strong>
        </article>
    </section>

    <section class="mt-5 grid gap-5 md:grid-cols-2">
        <a class="min-h-56 rounded-[1.75rem] border border-white/10 bg-gradient-to-br from-tuiublue/20 to-slate-900/90 p-7 text-white no-underline shadow-2xl shadow-black/20 transition hover:-translate-y-1" href="<?= h(route_url('documentos')) ?>">
            <span class="text-sm font-extrabold uppercase tracking-[0.16em] text-slate-400">Base de conhecimento</span>
            <strong class="mt-8 block text-3xl font-extrabold">Documentos</strong>
            <p class="mt-3 max-w-md leading-7 text-slate-300">Atas, decisões técnicas, listas de peças e registros da equipe.</p>
        </a>
        <a class="min-h-56 rounded-[1.75rem] border border-white/10 bg-gradient-to-br from-tuiured/20 to-slate-900/90 p-7 text-white no-underline shadow-2xl shadow-black/20 transition hover:-translate-y-1" href="<?= h(route_url('projetos')) ?>">
            <span class="text-sm font-extrabold uppercase tracking-[0.16em] text-slate-400">Temporada</span>
            <strong class="mt-8 block text-3xl font-extrabold">Projetos</strong>
            <p class="mt-3 max-w-md leading-7 text-slate-300">Kanban persistente para acompanhar tarefas de engenharia e gestão.</p>
        </a>
    </section>
</main>

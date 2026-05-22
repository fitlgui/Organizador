<?php
$pageTitle = $pageTitle ?? 'Portal Tuiutech';
$activePage = $activePage ?? '';
$minimal = $minimal ?? false;
$user = $user ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> - Portal Tuiutech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="min-h-screen bg-ink font-sans text-slate-50 antialiased">
<?php if (!$minimal): ?>
    <header class="sticky top-0 z-40 border-b border-white/10 bg-ink/85 px-4 py-3 shadow-2xl shadow-black/20 backdrop-blur md:px-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <a class="flex items-center gap-3 text-white no-underline" href="<?= h(route_url('home')) ?>">
                <img src="Syle.png" alt="Logo Tuiutech" class="h-12 w-16 object-contain">
                <span>
                    <strong class="block text-lg font-extrabold leading-tight">Tuiutech</strong>
                    <small class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">FIRST Robotics Team</small>
                </span>
            </a>

            <nav class="flex w-full gap-2 overflow-x-auto rounded-full border border-white/10 bg-white/5 p-1 md:w-auto" aria-label="Navegação principal">
                <a class="rounded-full px-4 py-2 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white <?= $activePage === 'home' ? 'bg-white/15 text-white' : '' ?>" href="<?= h(route_url('home')) ?>">Home</a>
                <a class="rounded-full px-4 py-2 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white <?= $activePage === 'documentos' ? 'bg-white/15 text-white' : '' ?>" href="<?= h(route_url('documentos')) ?>">Documentos</a>
                <a class="rounded-full px-4 py-2 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white <?= $activePage === 'projetos' ? 'bg-white/15 text-white' : '' ?>" href="<?= h(route_url('projetos')) ?>">Projetos</a>
                <a class="rounded-full px-4 py-2 text-sm font-bold text-slate-300 transition hover:bg-white/10 hover:text-white <?= $activePage === 'configuracoes' ? 'bg-white/15 text-white' : '' ?>" href="<?= h(route_url('configuracoes')) ?>">Config</a>
            </nav>

            <div class="flex items-center justify-between gap-3 md:justify-end">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-gradient-to-br from-tuiured to-tuiublue text-sm font-extrabold shadow-lg shadow-tuiublue/20"><?= h(initials($user['name'] ?? 'TT')) ?></span>
                <a class="rounded-full border border-white/10 px-4 py-2 text-sm font-bold text-slate-300 no-underline transition hover:border-tuiured/50 hover:bg-tuiured/15 hover:text-white" href="<?= h(route_url('logout')) ?>">Sair</a>
            </div>
        </div>
    </header>
<?php endif; ?>

<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute left-[-12rem] top-[-12rem] h-[30rem] w-[30rem] rounded-full bg-tuiublue/20 blur-3xl"></div>
    <div class="absolute right-[-10rem] top-10 h-[28rem] w-[28rem] rounded-full bg-tuiured/20 blur-3xl"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_26rem)]"></div>
</div>

<?= $content ?>

<script>
window.portalConfig = {
    apiUrl: "<?= h(route_url('api')) ?>"
};
</script>
<script src="script.js" defer></script>
</body>
</html>

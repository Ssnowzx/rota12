<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?= e($pageTitle ?? 'Painel Parceiro - Rota 12') ?></title>
    <link rel="icon" href="/public/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --accent: #dfff00;
            --bg: #111;
            --sidebar: #0d0d0d;
            --card: #1a1a1a;
            --border: rgba(255,255,255,0.07);
            --text: #fff;
            --muted: rgba(255,255,255,0.45);
            --sidebar-w: 240px;
        }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

        /* Sidebar */
        .pn-sidebar {
            width: var(--sidebar-w); min-width: var(--sidebar-w);
            background: var(--sidebar); border-right: 1px solid var(--border);
            display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh;
            z-index: 100; padding: 1.5rem 0;
        }
        .pn-logo { padding: 0 1.5rem 1.5rem; border-bottom: 1px solid var(--border); margin-bottom: 1rem; }
        .pn-logo img { height: 36px; }
        .pn-logo-sub { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 0.25rem; }
        .pn-nav { flex: 1; padding: 0.5rem 0; }
        .pn-nav a {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1.5rem; color: var(--muted); text-decoration: none;
            font-size: 1rem; font-weight: 500; transition: all 0.15s;
            border-left: 3px solid transparent;
        }
        .pn-nav a:hover, .pn-nav a.active {
            color: var(--text); background: rgba(255,255,255,0.04); border-left-color: var(--accent);
        }
        .pn-nav a i { width: 18px; text-align: center; font-size: 1rem; }
        .pn-user {
            padding: 1rem 1.5rem; border-top: 1px solid var(--border); margin-top: auto;
        }
        .pn-user-name { font-size: 1rem; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pn-user-role { font-size: 0.82rem; color: var(--accent); }
        .pn-logout { display: block; margin-top: 0.75rem; color: var(--muted); font-size: 0.9rem; text-decoration: none; }
        .pn-logout:hover { color: var(--text); }

        /* Main content */
        .pn-main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .pn-topbar {
            background: var(--sidebar); border-bottom: 1px solid var(--border);
            padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between;
        }
        .pn-topbar-title { font-size: 1.25rem; font-weight: 600; }
        .pn-content { padding: 2rem; flex: 1; }
        @media (max-width: 600px) {
            .pn-content { padding: 1rem; }
            .pn-topbar { padding: 1rem; }
        }

        /* Cards */
        .pn-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; display: flex; flex-direction: column; height: 100%; }
        .pn-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .pn-card-title { font-size: 1.05rem; font-weight: 600; }
        .pn-card-body { padding: 1.5rem; flex: 1; display: flex; flex-direction: column; }

        /* Metric cards */
        .pn-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .pn-metric {
            background: var(--card); border: 1px solid var(--border); border-radius: 12px;
            padding: 1.25rem 1.5rem;
            transition: all 0.15s;
        }
        .pn-metric:hover { border-color: var(--accent); background: rgba(223,255,0,0.02); }
        .pn-metric-label { font-size: 0.78rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .pn-metric-value { font-size: 2rem; font-weight: 700; }
        .pn-metric-sub { font-size: 0.85rem; color: var(--muted); margin-top: 0.25rem; }
        .pn-metric-icon { font-size: 1.25rem; color: var(--accent); margin-bottom: 0.75rem; }

        /* Grid 2 - ajustado para proporções iguais */
        .pn-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem; }
        @media (max-width: 900px) { .pn-grid-2 { grid-template-columns: 1fr; } }

        /* Table */
        .pn-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        .pn-table th {
            padding: 0.85rem 1rem;
            text-align: left;
            color: var(--muted);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 2px solid var(--border);
            background: rgba(255,255,255,0.02);
        }
        .pn-table td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: middle;
            font-size: 0.95rem;
        }
        .pn-table tbody tr:hover { background: rgba(255,255,255,0.02); }
        .pn-table tr:last-child td { border-bottom: none; }

        /* Badges */
        .pn-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .pn-badge-green { background: rgba(0,200,100,0.15); color: #00c864; }
        .pn-badge-yellow { background: rgba(223,255,0,0.15); color: var(--accent); }
        .pn-badge-red { background: rgba(255,80,80,0.15); color: #ff5050; }
        .pn-badge-gray { background: rgba(255,255,255,0.08); color: var(--muted); }

        /* Buttons */
        .pn-btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1.1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; border: none; transition: all 0.15s; }
        .pn-btn-primary { background: var(--accent); color: #111; }
        .pn-btn-primary:hover { background: #c8e600; }
        .pn-btn-ghost { background: rgba(255,255,255,0.06); color: var(--text); border: 1px solid var(--border); }
        .pn-btn-ghost:hover { background: rgba(255,255,255,0.1); }
        .pn-btn-danger { background: rgba(255,80,80,0.1); color: #ff5050; border: 1px solid rgba(255,80,80,0.2); }
        .pn-btn-danger:hover { background: rgba(255,80,80,0.2); }
        .pn-btn-sm { padding: 0.35rem 0.75rem; font-size: 0.78rem; }

        /* Flash messages */
        .pn-flash { padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.88rem; }
        .pn-flash-success { background: rgba(0,200,100,0.1); border: 1px solid rgba(0,200,100,0.2); color: #00c864; }
        .pn-flash-error { background: rgba(255,80,80,0.1); border: 1px solid rgba(255,80,80,0.2); color: #ff5050; }

        /* Form */
        .pn-form-group { margin-bottom: 1rem; }
        .pn-form-group label { display: block; font-size: 0.82rem; color: var(--muted); margin-bottom: 0.4rem; }
        .pn-form-control { width: 100%; background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 8px; color: #fff; padding: 0.65rem 0.9rem; font-size: 0.9rem; outline: none; box-sizing: border-box; }
        .pn-form-control:focus { border-color: var(--accent); }
        .pn-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        /* Chart container - melhoria de proporção */
        .pn-chart-container {
            position: relative;
            width: 100%;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pn-chart-container canvas {
            max-width: 100% !important;
        }

        /* Media query para gráficos em mobile */
        @media (max-width: 600px) {
            .pn-chart-container {
                min-height: 250px;
            }
        }

        /* Modal overlay */
        .pn-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 500; align-items: center; justify-content: center; }
        .pn-modal-overlay.open { display: flex; }
        .pn-modal { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 2rem; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; }
        .pn-modal-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.5rem; }
        .pn-modal-close { float: right; background: none; border: none; color: var(--muted); font-size: 1.2rem; cursor: pointer; }

        /* Table wrapper */
        .pn-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 0 0 12px 12px;
        }
        .pn-table-wrapper::-webkit-scrollbar { height: 4px; }
        .pn-table-wrapper::-webkit-scrollbar-track { background: rgba(255,255,255,0.04); }
        .pn-table-wrapper::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
        .pn-table-wrapper::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body>

<aside class="pn-sidebar">
    <div class="pn-logo">
        <img src="/assets/images/logo.png" alt="Rota 12"/>
        <div class="pn-logo-sub">Painel Parceiro</div>
    </div>
    <nav class="pn-nav">
        <?php $uri = $_SERVER['REQUEST_URI'] ?? ''; ?>
        <a href="/parceiro/dashboard" class="<?= str_contains($uri, '/dashboard') ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Dashboard
        </a>
        <a href="/parceiro/campanhas" class="<?= str_contains($uri, '/campanhas') ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Campanhas
        </a>
    </nav>
    <div class="pn-user">
        <div class="pn-user-name"><?= e($user['username'] ?? 'Parceiro') ?></div>
        <div class="pn-user-role"><i class="fas fa-store"></i> Parceiro</div>
        <a href="/sair" class="pn-logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</aside>

<main class="pn-main">
    <div class="pn-topbar">
        <span class="pn-topbar-title"><?= e($pageTitle ?? 'Dashboard') ?></span>
        <span style="font-size:0.82rem; color: var(--muted);"><?= e($user['email'] ?? '') ?></span>
    </div>
    <div class="pn-content">
        <?php
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        foreach ($flash as $f):
            $cls = $f['type'] === 'success' ? 'pn-flash-success' : 'pn-flash-error';
        ?>
        <div class="pn-flash <?= $cls ?>"><?= e($f['message']) ?></div>
        <?php endforeach; ?>
        <?= $content ?>
    </div>
</main>

</body>
</html>

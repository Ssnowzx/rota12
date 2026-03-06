<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?= e($pageTitle ?? 'Rota 12 - Estilo e Tradição') ?></title>
    <?php if (!empty($metaDescription)): ?>
    <meta name="description" content="<?= e($metaDescription) ?>"/>
    <?php else: ?>
    <meta name="description" content="Clube de benefícios para motociclistas. Economize em suas viagens com cupons exclusivos."/>
    <?php endif; ?>
    <link rel="icon" href="/public/favicon.ico" type="image/x-icon" sizes="16x16"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="/assets/css/app.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&family=Plus+Jakarta+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet"/>
</head>
<body>

<header class="main-header">
    <nav class="nav-container">
        <div class="logo">
            <a href="/">
                <img alt="Rota 12" width="150" height="55" src="/assets/images/logo.png"/>
            </a>
        </div>
        <div class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </div>
        <ul class="nav-links" id="navLinks">
            <li><a href="/#hero">Início</a></li>
            <li><a href="/cupons">Cupons</a></li>
            <li><a href="/#top-cupons">Top 10</a></li>
            <li><a href="/#planos">Planos</a></li>
            <li><a href="/sobre">Sobre</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['user_role'] ?? '') === 'partner'): ?>
                    <li><a href="/parceiro/dashboard" class="nav-btn-partner"><i class="fas fa-store"></i> Painel Parceiro</a></li>
                <?php else: ?>
                    <li><a href="/minha-conta" class="nav-btn-account"><i class="fas fa-user"></i> Minha Conta</a></li>
                <?php endif; ?>
                <li><a href="/sair" class="nav-btn-logout">Sair</a></li>
            <?php else: ?>
                <li><a href="/login" class="nav-btn-login">Login</a></li>
                <li><a href="/cadastro" class="nav-btn-cta">Cadastrar</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main>
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-col brand-col">
                <img alt="Rota 12" loading="lazy" width="120" height="45" class="footer-logo" src="/assets/images/logo.png"/>
                <h4 class="footer-heading">Sobre Nós</h4>
                <p>A viagem termina, mas as memórias ficam para sempre! Que tal desfrutar de tudo que a serra catarinense tem a oferecer pagando menos? Assine um de nossos planos e obtenha incríveis cupons de descontos.</p>
            </div>
            <div class="footer-col app-col">
                <h4 class="footer-heading">Baixe o Nosso App</h4>
                <p>Em breve nas lojas Android e IOS :-)</p>
                <div class="app-badges">
                    <div class="store-badge"><i class="fab fa-android"></i> Android</div>
                    <div class="store-badge"><i class="fab fa-apple"></i> iOS</div>
                </div>
            </div>
            <div class="footer-col links-col">
                <h4 class="footer-heading">Dúvidas Rápidas</h4>
                <ul class="footer-links">
                    <li><a href="/localizando-cupons">Localizando Cupons</a></li>
                    <li><a href="/como-usar">Como usar um Cupom</a></li>
                    <li><a href="/#planos">Planos de Assinatura</a></li>
                    <li><a href="/minha-conta">Minha Conta</a></li>
                </ul>
            </div>
            <div class="footer-col links-col">
                <h4 class="footer-heading">Institucional</h4>
                <ul class="footer-links">
                    <li><a href="/privacidade">Política de Privacidade</a></li>
                    <li><a href="/parceiro">Seja um Parceiro</a></li>
                    <li><a href="/contato">Contato</a></li>
                    <li><a href="/contato">Remova Minha Assinatura</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>Copyright &copy; <?= date('Y') ?> Rota 12 | Todos os direitos reservados.</p>
        </div>
    </div>
</footer>

<script>
(function(){
    var toggle = document.getElementById('mobileMenuToggle');
    var nav    = document.getElementById('navLinks');
    if(toggle && nav){
        toggle.addEventListener('click', function(){
            nav.classList.toggle('open');
        });
    }
})();
</script>

</body>
</html>

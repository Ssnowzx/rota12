<?php
/** @var object|array $page {title, content_html, meta_title, meta_description} */
?>

<div style="padding: 120px 2rem 4rem; max-width: 860px; margin: 0 auto;">

    <article class="page-content">
        <header style="margin-bottom: 3rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 2rem;">
            <h1 style="font-size: 2.8rem; line-height: 1.2; margin: 0;"><?= e($page['title']) ?></h1>
        </header>

        <div class="prose" style="color: var(--color-text-muted); line-height: 1.9; font-size: 1.05rem;">
            <?= $page['content_html'] ?>
        </div>
    </article>

</div>

<style>
.prose h1, .prose h2, .prose h3, .prose h4 {
    color: var(--color-text-main);
    margin: 2rem 0 1rem;
    font-family: var(--font-heading);
}
.prose h2 { font-size: 1.8rem; }
.prose h3 { font-size: 1.4rem; }
.prose p { margin: 0 0 1.5rem; }
.prose ul, .prose ol { padding-left: 1.5rem; margin: 0 0 1.5rem; }
.prose li { margin-bottom: 0.5rem; }
.prose a { color: var(--color-accent); }
.prose a:hover { text-decoration: underline; }
.prose img { max-width: 100%; height: auto; border-radius: var(--radius-md); margin: 1.5rem 0; }
.prose blockquote {
    border-left: 3px solid var(--color-accent);
    padding-left: 1.5rem;
    color: var(--color-text-muted);
    font-style: italic;
    margin: 2rem 0;
}
.prose strong { color: var(--color-text-main); }
.prose code { background: rgba(255,255,255,0.08); padding: 0.15rem 0.4rem; border-radius: 3px; font-family: monospace; }
.prose hr { border: none; border-top: 1px solid rgba(255,255,255,0.08); margin: 3rem 0; }
</style>

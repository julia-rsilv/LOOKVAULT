<?php
$pageTitle = "LOOKVAULT · Blog";
$pageDescription = "Dicas e tendências de moda.";
include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/pages.css">

<main>
    <section class="page-hero">
        <div class="container">
            <div class="page-hero-inner">
                <div class="page-hero-text">
                    <span class="page-hero-eyebrow"><i class="fas fa-newspaper"></i> Conteúdo</span>
                    <h1 class="page-hero-title">Nosso <em>Blog</em></h1>
                    <p class="page-hero-subtitle">Dicas, tendências e inspirações para seu guarda-roupa.</p>
                </div>
            </div>
        </div>
    </section>

    <section style="padding: 60px 0;">
        <div class="container">
            <div class="looks-grid-new">
                <article class="blog-card">
                    <div class="blog-card-image"><i class="fas fa-tshirt"></i></div>
                    <div class="blog-card-body">
                        <h3>Como organizar seu guarda-roupa por cores</h3>
                        <p>Aprenda a categorizar suas peças por tons e criar combinações incríveis.</p>
                        <a href="#">Leia mais <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
                <article class="blog-card">
                    <div class="blog-card-image"><i class="fas fa-chart-line"></i></div>
                    <div class="blog-card-body">
                        <h3>O custo por uso: como economizar com moda</h3>
                        <p>Descubra como calcular o valor real de cada peça e investir com inteligência.</p>
                        <a href="#">Leia mais <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
                <article class="blog-card">
                    <div class="blog-card-image"><i class="fas fa-heart"></i></div>
                    <div class="blog-card-body">
                        <h3>Tendências de moda para esta estação</h3>
                        <p>Fique por dentro das cores, tecidos e cortes que estão em alta.</p>
                        <a href="#">Leia mais <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
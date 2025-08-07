<?php
$page_title = "Início - Water Reminder";
include("../includes/header.php");
?>

<main>
    <section class="hero-section bg-primary text-white py-5">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <i class="bi bi-droplet-fill display-1 mb-4 opacity-75"></i>
                    <h1 class="display-4 fw-bold mb-4">Mantenha-se Hidratado</h1>
                    <p class="lead mb-4">
                        Um lembrete simples e eficaz para beber água regularmente e cuidar da sua saúde.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="./register.php" class="btn btn-light btn-lg px-4">
                            <i class="bi bi-person-plus me-2"></i>Começar Agora
                        </a>
                        <a href="./login.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Fazer Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <i class="bi bi-clock text-primary display-5 mb-3"></i>
                            <h5 class="card-title mt-3">Intervalos Simples</h5>
                            <p class="card-text text-muted">
                                Escolha seu intervalo: 30min, 1h, 2h... Receba lembretes automáticos.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <i class="bi bi-fire text-primary display-5 mb-3"></i>
                            <h5 class="card-title mt-3">Conte seus Streaks</h5>
                            <p class="card-text text-muted">
                                Veja quantos dias consecutivos você mantém o hábito de se hidratar.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <i class="bi bi-check-circle text-primary display-5 mb-3"></i>
                            <h5 class="card-title mt-3">Pura Simplicidade</h5>
                            <p class="card-text text-muted">
                                Sem complicações. Apenas lembretes na hora certa para beber água.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("../includes/footer.php"); ?>
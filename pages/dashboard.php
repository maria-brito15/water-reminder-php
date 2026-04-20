<?php
session_start();
$page_title = "Dashboard - Water Reminder";

require_once '../php/Database.php';
require_once '../php/Usuario.php';
require_once '../php/Streak.php';

date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit;
}

$db      = new Database();
$usuario = $db->getUsuarioPorId($_SESSION['user_id']);

if (!$usuario) {
    echo "Usuário Não Encontrado.";
    exit;
}

$erro    = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['beber_agua'])) {
    try {
        $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

        $streak = new Streak(
            $usuario->getStreak(),
            $usuario->getIntervalo(),
            $usuario->getUltimoGole()
        );

        if ($streak->isPrimeiroBeber()) {
            $novoStreak = 1;
        } elseif ($streak->verificarSeStreakQuebrou()) {
            $novoStreak = 1;
            $sucesso    = "Água Registrada! Streak Resetado Devido ao Tempo. 💧";
        } else {
            $novoStreak    = $usuario->getStreak();
            $diffSegundos  = $agora->getTimestamp() - $usuario->getUltimoGole()->getTimestamp();

            if ($diffSegundos >= 3600) {
                $novoStreak++;
            }
        }

        $db->atualizarUsuarioStreak($_SESSION['user_id'], $novoStreak);
        $db->atualizarUsuarioUltimoGole($_SESSION['user_id'], $agora);

        if (empty($sucesso)) {
            $sucesso = "Água Registrada com Sucesso! 💧";
        }

        $usuario = $db->getUsuarioPorId($_SESSION['user_id']);

    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$nome              = $usuario->getNome();
$intervaloSegundos = $usuario->getIntervalo();
$streak            = $usuario->getStreak();

$ultimoGole = $usuario->getUltimoGole();
$ultimoGole->setTimezone(new DateTimeZone('America/Sao_Paulo'));

$streakObj     = new Streak($streak, $intervaloSegundos, $ultimoGole);
$streakQuebrado = $streakObj->verificarSeStreakQuebrou();

$agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

$proximoLembrete = null;
$deveBeberAgua   = false;
$tempoRestante   = "";

if (!$streakObj->isPrimeiroBeber()) {
    $proximoLembrete = clone $ultimoGole;
    $proximoLembrete->add(new DateInterval('PT' . $intervaloSegundos . 'S'));

    if ($agora >= $proximoLembrete) {
        $deveBeberAgua = true;
    } else {
        $diffSegundos  = $proximoLembrete->getTimestamp() - $agora->getTimestamp();
        $horas         = floor($diffSegundos / 3600);
        $minutos       = floor(($diffSegundos % 3600) / 60);
        $tempoRestante = $horas > 0
            ? sprintf('%dh %02dm', $horas, $minutos)
            : sprintf('%dm', $minutos);
    }
} else {
    $deveBeberAgua = true;
}

function formatarIntervalo(int $segundos): string {
    if ($segundos < 60) {
        return $segundos . " segundos";
    }

    if ($segundos < 3600) {
        return intval($segundos / 60) . " minutos";
    }

    $horas   = $segundos / 3600;
    $horasInt = (int) floor($horas);
    $minutos  = (int) round(($horas - $horasInt) * 60);

    if ($minutos === 0) {
        return $horasInt === 1 ? "1 hora" : "{$horasInt} horas";
    }

    return "{$horasInt}h {$minutos}m";
}

$intervaloTexto = formatarIntervalo($intervaloSegundos);

include("../includes/header.php");
?>

<main class="py-4">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Olá, <?= htmlspecialchars($nome) ?>! 👋</h2>
                        <p class="text-muted mb-0">Como está sua hidratação hoje?</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="bi bi-person-gear me-1"></i>Perfil
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-1"></i>Sair
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <?php if ($streakQuebrado): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Atenção!</strong> Seu streak foi quebrado por passar muito tempo sem beber água. 
                Beba água agora para começar um novo streak!
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center p-5">
                        <?php if ($deveBeberAgua || $streakObj->isPrimeiroBeber()): ?>
                            <div class="text-primary mb-4">
                                <i class="bi bi-droplet-fill display-1"></i>
                            </div>
                            <h3 class="text-primary mb-3">
                                <?= $streakObj->isPrimeiroBeber() ? "Hora do primeiro gole!" : "Hora de beber água!" ?>
                            </h3>
                            <p class="text-muted mb-4">
                                <?php if ($streakObj->isPrimeiroBeber()): ?>
                                    Comece seu streak de hidratação agora!
                                <?php else: ?>
                                    <?= $streakQuebrado ? "Seu streak quebrou, mas você pode começar um novo!" : "Já passou do seu horário de beber água." ?>
                                <?php endif; ?>
                            </p>
                            <form method="POST" action="">
                                <button type="submit" name="beber_agua" value="1" class="btn btn-primary btn-lg px-5">
                                    <i class="bi bi-droplet-half me-2"></i>Bebi Água!
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="text-success mb-4">
                                <i class="bi bi-check-circle-fill display-1"></i>
                            </div>
                            <h3 class="text-success mb-3">Você Está em dia! 🎉</h3>
                            <p class="text-muted mb-4">
                                Próximo lembrete em <strong><?= $tempoRestante ?></strong>
                            </p>
                            <form method="POST" action="">
                                <button type="submit" name="beber_agua" value="1" class="btn btn-outline-primary">
                                    <i class="bi bi-droplet-half me-2"></i>Beber Água Mesmo Assim
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="row g-3 h-100">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center p-4">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="bi bi-fire <?= $streakQuebrado ? 'text-muted' : 'text-warning' ?> display-5 me-3"></i>
                                    <div class="text-start">
                                        <h2 class="mb-0 fw-bold <?= $streakQuebrado ? 'text-muted' : '' ?>"><?= $streak ?></h2>
                                        <small class="text-muted">
                                            <?= $streakQuebrado ? 'Streak Quebrado' : 'Dias Consecutivos' ?>
                                        </small>
                                    </div>
                                </div>
                                <p class="text-muted mb-0 small">
                                    <?= $streakQuebrado ? 'Beba Água para Recomeçar' : 'Seu Streak Atual' ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center p-4">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="bi bi-clock-history text-info display-6 me-3"></i>
                                    <div class="text-start">
                                        <h6 class="mb-0 fw-semibold">
                                            <?php if ($streakObj->isPrimeiroBeber()): ?>
                                                Nunca
                                            <?php else: ?>
                                                <?= $ultimoGole->format('H:i') ?>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php if (!$streakObj->isPrimeiroBeber()): ?>
                                                <?php
                                                $hoje = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
                                                echo $ultimoGole->format('Y-m-d') === $hoje->format('Y-m-d')
                                                    ? 'hoje'
                                                    : $ultimoGole->format('d/m/Y');
                                                ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <p class="text-muted mb-0 small">Último Gole</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center p-4">
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <i class="bi bi-bell text-primary display-6 me-3"></i>
                                    <div class="text-start">
                                        <h6 class="mb-0 fw-semibold">
                                            <?php if ($proximoLembrete && !$deveBeberAgua): ?>
                                                <?= $proximoLembrete->format('H:i') ?>
                                            <?php else: ?>
                                                Agora!
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">a cada <?= $intervaloTexto ?></small>
                                    </div>
                                </div>
                                <p class="text-muted mb-0 small">Próximo Lembrete</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-lightbulb text-warning me-2"></i>Dicas de Hidratação
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-droplet text-primary me-2 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Quantidade Ideal</h6>
                                        <small class="text-muted">Cerca de 35ml por kg de peso corporal por dia.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-sun text-warning me-2 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Clima Quente</h6>
                                        <small class="text-muted">Aumente o consumo em dias quentes ou durante exercícios.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-alarm text-info me-2 mt-1"></i>
                                    <div>
                                        <h6 class="mb-1">Regularidade</h6>
                                        <small class="text-muted">Beba pequenas quantidades regularmente ao invés de muito de uma vez.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
setTimeout(() => {
    location.reload();
}, 60000);

<?php if ($deveBeberAgua): ?>
if ('Notification' in window && Notification.permission === 'granted') {
    new Notification('💧 Water Reminder', {
        body: 'Hora de Beber Água!',
        icon: '/favicon.ico'
    });
} else if ('Notification' in window && Notification.permission !== 'denied') {
    Notification.requestPermission().then(permission => {
        if (permission === 'granted') {
            new Notification('💧 Water Reminder', {
                body: 'Hora de Beber Água!',
                icon: '/favicon.ico'
            });
        }
    });
}
<?php endif; ?>
</script>

<?php include("../includes/footer.php"); ?>

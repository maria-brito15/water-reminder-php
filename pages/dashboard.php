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

$db = new Database();
$usuario = $db->getUsuarioEspecifico($_SESSION['user_id']);

if (!$usuario) {
    echo "Usuário Não Encontrado.";
    exit;
}

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['beber_agua'])) {
    try {
        $streak = new Streak(
            $usuario->getStreak(),
            $usuario->getIntervalo(),
            $usuario->getUltimoGole()
        );
        
        $agoraFixo = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        
        if ($streak->verificarSeStreakQuebrou() && $usuario->getUltimoGole()->format('Y') != '1970') {
            $streak->zerarStreak();
            $db->atualizarUsuarioStreak($_SESSION['user_id'], 1);
            $db->atualizarUsuarioUltimoGole($_SESSION['user_id'], $agoraFixo);

            $sucesso = "Água Registrada! Streak Resetado Devido ao Tempo. 💧";
        } else {
            $novoStreak = $usuario->getStreak();
            $ultimoGole = $usuario->getUltimoGole();

            if ($ultimoGole->format('Y') == '1970') {
                $novoStreak = 1;
            } else {
                $diffSegundos = $agoraFixo->getTimestamp() - $ultimoGole->getTimestamp();
                
                if ($diffSegundos >= 3600) {
                    $novoStreak++;
                }
            }
            
            $db->atualizarUsuarioStreak($_SESSION['user_id'], $novoStreak);
            $db->atualizarUsuarioUltimoGole($_SESSION['user_id'], $agoraFixo);
            $sucesso = "Água Registrada com Sucesso! 💧";
        }
        
        $usuario = $db->getUsuarioEspecifico($_SESSION['user_id']);
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$nome = $usuario->getNome();
$intervaloSegundos = $usuario->getIntervalo();
$streak = $usuario->getStreak();

$ultimoGoleFixo = $usuario->getUltimoGole();
$ultimoGoleFixo->setTimezone(new DateTimeZone('America/Sao_Paulo'));

$streakObj = new Streak($streak, $intervaloSegundos, $ultimoGoleFixo);

$proximoLembreteFixo = null;
if ($ultimoGoleFixo->format('Y') != 1970) {
    $proximoLembreteFixo = clone $ultimoGoleFixo;
    $proximoLembreteFixo->add(new DateInterval('PT' . $intervaloSegundos . 'S'));
}

$agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
$deveBeberAgua = false;
$tempoRestante = "";

if ($proximoLembreteFixo) {
    if ($agora >= $proximoLembreteFixo) {
        $deveBeberAgua = true;
    } else {
        $diffSegundos = $proximoLembreteFixo->getTimestamp() - $agora->getTimestamp();
        $horas = floor($diffSegundos / 3600);
        $minutos = floor(($diffSegundos % 3600) / 60);
        
        if ($horas > 0) {
            $tempoRestante = sprintf('%dh %02dm', $horas, $minutos);
        } else {
            $tempoRestante = sprintf('%dm', $minutos);
        }
    }
} else {
    $deveBeberAgua = true;
}

$intervaloTexto = "";

if ($intervaloSegundos < 60) {
    $intervaloTexto = $intervaloSegundos . " segundos";
} else if ($intervaloSegundos < 3600) {
    $minutos = $intervaloSegundos / 60;
    $intervaloTexto = intval($minutos) . " minutos";
} else {
    $horas = $intervaloSegundos / 3600;
    if ($horas == 1) {
        $intervaloTexto = "1 hora";
    } else if ($horas == floor($horas)) {
        $intervaloTexto = intval($horas) . " horas";
    } else {
        $horasInt = floor($horas);
        $minutosRestantes = ($horas - $horasInt) * 60;
        $intervaloTexto = $horasInt . "h " . intval($minutosRestantes) . "m";
    }
}

$streakQuebrado = $streakObj->verificarSeStreakQuebrou() && $ultimoGoleFixo->format('Y') != '1970';

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
                        <?php if ($deveBeberAgua || $ultimoGoleFixo->format('Y') == 1970): ?>
                            <div class="text-primary mb-4">
                                <i class="bi bi-droplet-fill display-1"></i>
                            </div>
                            <h3 class="text-primary mb-3">
                                <?= $ultimoGoleFixo->format('Y') == 1970 ? "Hora do primeiro gole!" : "Hora de beber água!" ?>
                            </h3>
                            <p class="text-muted mb-4">
                                <?php if ($ultimoGoleFixo->format('Y') == 1970): ?>
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
                                            <?php if ($ultimoGoleFixo->format('Y') == 1970): ?>
                                                Nunca
                                            <?php else: ?>
                                                <?= $ultimoGoleFixo->format('H:i') ?>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php if ($ultimoGoleFixo->format('Y') != 1970): ?>
                                                <?php
                                                $hoje = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
                                                if ($ultimoGoleFixo->format('Y-m-d') == $hoje->format('Y-m-d')) {
                                                    echo "hoje";
                                                } else {
                                                    echo $ultimoGoleFixo->format('d/m/Y');
                                                }
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
                                            <?php if ($proximoLembreteFixo && !$deveBeberAgua): ?>
                                                <?= $proximoLembreteFixo->format('H:i') ?>
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
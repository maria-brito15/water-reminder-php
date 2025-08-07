<?php
session_start();
$page_title = "Perfil - Water Reminder";

require_once '../php/Database.php';
require_once '../php/Usuario.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $atualizacoesSucesso = [];
        
        if (!empty($_POST['nome']) && trim($_POST['nome']) !== $usuario->getNome()) {
            $novoNome = trim($_POST['nome']);
            
            if ($db->atualizarUsuarioNome($_SESSION['user_id'], $novoNome)) {
                $_SESSION['user_nome'] = $novoNome;
                $atualizacoesSucesso[] = "nome";
            }
        }
        
        if (!empty($_POST['nova_senha'])) {
            $novaSenha = $_POST['nova_senha'];
            $confirmarSenha = $_POST['confirmar_senha'] ?? '';
            
            if (strlen($novaSenha) < 6) {
                throw new Exception("Nova senha deve ter pelo menos 6 caracteres.");
            }
            
            if ($novaSenha !== $confirmarSenha) {
                throw new Exception("Nova senha e confirmação não coincidem.");
            }
            
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            if ($db->atualizarUsuarioSenha($_SESSION['user_id'], $senhaHash)) {
                $atualizacoesSucesso[] = "senha";
            }
        }
        
        if (isset($_POST['intervalo'])) {
            $novoIntervalo = floatval($_POST['intervalo']);
            
            $intervaloSegundos = (int)($novoIntervalo * 60 * 60);
            
            if ($intervaloSegundos !== $usuario->getIntervalo()) {
                if ($db->atualizarUsuarioIntervalo($_SESSION['user_id'], $intervaloSegundos)) {
                    $atualizacoesSucesso[] = "intervalo";
                }
            }
        }
        
        if (!empty($atualizacoesSucesso)) {
            $sucesso = "Perfil Atualizado com Sucesso! (" . implode(", ", $atualizacoesSucesso) . ")";
            $usuario = $db->getUsuarioEspecifico($_SESSION['user_id']);
        } else {
            $sucesso = "Nenhuma Alteração Foi Detectada.";
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_conta'])) {
    try {
        if ($db->removerUsuario($_SESSION['user_id'])) {
            session_destroy();
            setcookie('user_email', '', time() - 3600, "/");
            setcookie('user_hash', '', time() - 3600, "/");
            
            header("Location: ../index.php?msg=conta_excluida");
            exit;
        } else {
            throw new Exception("Erro ao Excluir Conta. Tente Novamente.");
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

$nome = $usuario->getNome();
$email = $usuario->getEmail();
$intervaloSegundos = $usuario->getIntervalo();
$streak = $usuario->getStreak();
$ultimoGole = $usuario->getUltimoGole();

$intervaloHoras = $intervaloSegundos / 3600;

include("../includes/header.php");
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-circle text-primary display-4"></i>
                            <h4 class="mt-3">Meu Perfil</h4>
                            <p class="text-muted">Gerencie suas Informações Pessoais</p>
                        </div>

                        <?php if (!empty($erro)): ?>
                            <div class="alert alert-danger text-center">
                                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($erro) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($sucesso)): ?>
                            <div class="alert alert-success text-center">
                                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($sucesso) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person me-2 text-primary"></i>Nome
                                </label>
                                <input type="text" name="nome" class="form-control" placeholder="Nome de Usuário" 
                                    value="<?= htmlspecialchars($nome) ?>" required>
                                <small class="form-text text-muted">Este nome será exibido no seu dashboard.</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-envelope me-2 text-primary"></i>Email
                                </label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" readonly>
                                <small class="form-text text-muted">O email não pode ser alterado.</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-lock me-2 text-primary"></i>Nova Senha
                                </label>
                                <input type="password" name="nova_senha" class="form-control" placeholder="Digite uma Nova Senha">
                                <small class="form-text text-muted">Deixe em branco para manter a senha atual (mínimo 6 caracteres).</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-lock-fill me-2 text-primary"></i>Confirmar Nova Senha
                                </label>
                                <input type="password" name="confirmar_senha" class="form-control" placeholder="Confirme a Nova Senha">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-clock me-2 text-primary"></i>Intervalo entre Lembretes
                                </label>
                                <select name="intervalo" class="form-select">
                                    <option value="0.5" <?= $intervaloHoras == 0.5 ? 'selected' : '' ?>>30 minutos</option>
                                    <option value="1" <?= $intervaloHoras == 1 ? 'selected' : '' ?>>1 hora</option>
                                    <option value="1.5" <?= $intervaloHoras == 1.5 ? 'selected' : '' ?>>1 hora e 30 minutos</option>
                                    <option value="2" <?= $intervaloHoras == 2 ? 'selected' : '' ?>>2 horas</option>
                                    <option value="2.5" <?= $intervaloHoras == 2.5 ? 'selected' : '' ?>>2 horas e 30 minutos</option>
                                    <option value="3" <?= $intervaloHoras == 3 ? 'selected' : '' ?>>3 horas</option>
                                    <option value="3.5" <?= $intervaloHoras == 3.5 ? 'selected' : '' ?>>3 horas e 30 minutos</option>
                                    <option value="4" <?= $intervaloHoras == 4 ? 'selected' : '' ?>>4 horas</option>
                                    <option value="4.5" <?= $intervaloHoras == 4.5 ? 'selected' : '' ?>>4 horas e 30 minutos </option>
                                </select>
                                <small class="form-text text-muted">Escolha com que frequência deseja receber lembretes.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Salvar Alterações
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Voltar ao Dashboard
                                </a>
                            </div>
                        </form>

                        <div class="mt-5 pt-4 border-top">
                            <h6 class="text-danger mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>Zona de Perigo
                            </h6>
                            <p class="text-muted small mb-3">
                                Estas ações são permanentes e não podem ser desfeitas.
                            </p>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bi bi-trash me-2"></i>Excluir Conta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Tem certeza de que deseja excluir sua conta <strong><?= htmlspecialchars($nome) ?></strong>?</p>
                    <p class="text-muted small mb-0">
                        <strong>Esta ação não pode ser desfeita.</strong> Todos os seus dados, incluindo histórico de lembretes e streaks, serão permanentemente removidos.
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="excluir_conta" value="1">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Tem CERTEZA ABSOLUTA? Esta ação é irreversível!')">
                            <i class="bi bi-trash me-2"></i>Sim, Excluir Conta.
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include("../includes/footer.php"); ?>
<?php
session_start();
$page_title = "Login - Water Reminder";

require_once '../php/Database.php';
require_once '../php/Login.php';

$erro = "";

$db    = new Database();
$login = new Login($db);

const COOKIE_DURACAO = 30 * 24 * 60 * 60; // 30 dias

function limparCookiesLogin(): void {
    setcookie('user_email', '', time() - 3600, "/", "", true, true);
    setcookie('user_hash',  '', time() - 3600, "/", "", true, true);
}

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_email'], $_COOKIE['user_hash'])) {
    try {
        $usuario = $db->getUsuarioPorEmail($_COOKIE['user_email']);

        if ($usuario && password_verify($usuario->getSenha(), $_COOKIE['user_hash'])) {
            $_SESSION['user_id']   = $usuario->getId();
            $_SESSION['user_nome'] = $usuario->getNome();

            header("Location: dashboard.php");
            exit;
        } else {
            limparCookiesLogin();
        }
    } catch (Exception $e) {
        limparCookiesLogin();
    }
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    try {
        $user = $login->autenticar($email, $senha);

        $_SESSION['user_id']   = $user->getId();
        $_SESSION['user_nome'] = $user->getNome();

        if (isset($_POST['remember'])) {
            $expiracao    = time() + COOKIE_DURACAO;
            $hashParaCookie = password_hash($user->getSenha(), PASSWORD_DEFAULT);

            setcookie('user_email', $email,         $expiracao, "/", "", true, true);
            setcookie('user_hash',  $hashParaCookie, $expiracao, "/", "", true, true);
        } else {
            limparCookiesLogin();
        }

        header("Location: dashboard.php");
        exit;

    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

include("../includes/header.php");
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <?php if (!empty($erro)): ?>
                            <div class="alert alert-danger text-center">
                                <?= htmlspecialchars($erro) ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mb-4">
                            <i class="bi bi-droplet-fill text-primary display-4"></i>
                            <h4 class="mt-3">Fazer Login</h4>
                            <p class="text-muted">Entre Na Sua Conta</p>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="exemplo@email.com" required
                                    value="<?= htmlspecialchars($_POST['email'] ?? $_COOKIE['user_email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <input type="password" name="senha" class="form-control" placeholder="*******" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember"
                                    <?= isset($_POST['remember']) || isset($_COOKIE['user_email']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="remember">Lembrar de Mim</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Não Tem Conta? <a href="register.php" class="text-decoration-none">Registre-se</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include("../includes/footer.php"); ?>

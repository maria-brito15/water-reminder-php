<?php

session_start();
$page_title = "Registro - Water Reminder";

require_once '../php/Database.php';
require_once '../php/Registro.php';

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    if ($senha !== $confirmar) {
        $erro = "As Senhas Não Coincidem.";
    } else {
        try {
            $db = new Database();
            $registro = new Registro($db);
            $usuario = $registro->registrar($nome, $email, $senha);

            $_SESSION['usuario_id'] = $usuario->getId();
            $_SESSION['usuario_nome'] = $usuario->getNome();

            header("Location: dashboard.php");
            exit;
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
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
                            <h4 class="mt-3">Criar Conta</h4>
                            <p class="text-muted">Junte-se ao Water Reminder</p>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" name="nome" class="form-control" placeholder="Nome de Usuario" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="exemplo@email.com" required 
                                value="<?= htmlspecialchars($_POST['email'] ?? '')?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <input type="password" name="senha" class="form-control" placeholder="*******" required">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar Senha</label>
                                <input type="password" name="confirmar_senha" class="form-control" placeholder="Confirme sua senha" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label d-flex flex-column" for="terms">
                                    <small>Concordo com os Termos de Uso</small>
                                    <small class="form-text text-muted">Checkbox puramente perfomartivo.</small>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-person-plus me-2"></i>Criar Conta
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Já Tem Conta? <a href="login.php" class="text-decoration-none">Fazer Login</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include("../includes/footer.php"); ?>
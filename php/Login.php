<?php 

require_once 'Database.php';

class Login {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function autenticar(string $email, string $senha): Usuario {
        $usuario = $this->db->getUsuarioEspecifico($email);

        if (!$usuario) {
            throw new Exception("Usuário não Encontrado.");
        }

        if (!password_verify($senha, $usuario->getSenha())) {
            throw new Exception("Senha Incorreta.");
        }

        return $usuario;
    }
}

?>
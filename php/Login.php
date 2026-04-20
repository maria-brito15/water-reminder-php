<?php

require_once 'Database.php';

class Login {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function autenticar(string $email, string $senha): Usuario {
        $usuario = $this->db->getUsuarioPorEmail($email);

        if (!$usuario) {
            throw new Exception("Email ou Senha incorretos.");
        }

        if (!password_verify($senha, $usuario->getSenha())) {
            throw new Exception("Email ou Senha incorretos.");
        }

        return $usuario;
    }
}

<?php

require_once 'Database.php';

class Registro {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function registrar(string $nome, string $email, string $senha): Usuario {
        if (empty($nome) || empty($email) || empty($senha)) {
            throw new Exception("Nome, Email e Senha são Obrigatórios.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email Inválido.");
        }

        return $this->db->adicionarUsuario([
            'nome' => $nome,
            'email' => $email,
            'senha' => $senha
        ]);
    }
}

?>
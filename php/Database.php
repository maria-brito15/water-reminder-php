<?php

require_once 'Usuario.php';

class Database {
    private string $arquivo;
    private array $dados;

    public function __construct(string $arquivo = '../db/db.json') {
        $this->arquivo = $arquivo;
        $this->carregar();
    }

    public function carregar(): void {
        if (!file_exists($this->arquivo)) {
            file_put_contents($this->arquivo, json_encode([]));
        }

        $conteudo = file_get_contents($this->arquivo);
        $this->dados = json_decode($conteudo, true) ?? [];
    }

    public function salvar(): void {
        file_put_contents($this->arquivo, json_encode($this->dados, JSON_PRETTY_PRINT));
    }

    public function todos(): array {
        return $this->dados;
    }

    public function limpar(): void {
        $this->dados = [];
        $this->salvar();
    }

    private function getProximoId(): int {
        $maiorId = 0;

        foreach ($this->dados as $usuario) {
            if (isset($usuario['id']) && $usuario['id'] > $maiorId) {
                $maiorId = $usuario['id'];
            }
        }

        return $maiorId + 1;
    }

    private function emailExiste(string $email): bool {
        foreach ($this->dados as $usuario) {
            if (isset($usuario['email']) && $usuario['email'] === $email) {
                return true;
            }
        }

        return false;
    }

    public function adicionarUsuario(array $dadosUsuario): Usuario {
        if (!isset($dadosUsuario['nome'], $dadosUsuario['email'], $dadosUsuario['senha'])) {
            throw new Exception("Campos Obrigatórios: Nome, Email e Senha.");
        }

        if ($this->emailExiste($dadosUsuario['email'])) {
            throw new Exception("E-mail Já Cadastrado.");
        }

        $id = $this->getProximoId();

        $senhaHash = password_hash($dadosUsuario['senha'], PASSWORD_DEFAULT);

        $usuario = new Usuario(
            $id,
            $dadosUsuario['nome'],
            $dadosUsuario['email'],
            $senhaHash,
            0, // intervalo
            0, // streak
            new DateTime('1970-01-01 00:00:00') // último gole zerado
        );

        $this->dados[] = $usuario->toArray();
        $this->salvar();

        return $usuario;
    }

    public function getUsuarioEspecifico($metodo): ?Usuario {
        foreach ($this->dados as $usuario) {
            if (is_int($metodo) && isset($usuario['id']) && $usuario['id'] === $metodo) {
                return new Usuario(
                    $usuario['id'],
                    $usuario['nome'],
                    $usuario['email'],
                    $usuario['senha'],
                    $usuario['intervalo'],
                    $usuario['streak'],
                    new DateTime($usuario['ultimoGole'])
                );
            } else if (is_string($metodo) && isset($usuario['email']) && $usuario['email'] === $metodo) {
                return new Usuario(
                    $usuario['id'],
                    $usuario['nome'],
                    $usuario['email'],
                    $usuario['senha'],
                    $usuario['intervalo'],
                    $usuario['streak'],
                    new DateTime($usuario['ultimoGole'])
                );
            }
        }
        
        return null;
}

    public function removerUsuario(int $id): bool {
        foreach ($this->dados as $i => $usuario) {
            if (isset($usuario['id']) && $usuario['id'] === $id) {
                array_splice($this->dados, $i, 1);
                $this->salvar();
                return true;
            }
        }
        return false;
    }

    public function atualizarUsuarioNome(int $id, string $novoNome): bool {
        return $this->atualizarCampo($id, 'nome', $novoNome);
    }

    public function atualizarUsuarioSenha(int $id, string $novaSenha): bool {
        return $this->atualizarCampo($id, 'senha', $novaSenha);
    }

    public function atualizarUsuarioIntervalo(int $id, int $novoIntervalo): bool {
        return $this->atualizarCampo($id, 'intervalo', $novoIntervalo);
    }

    public function atualizarUsuarioStreak(int $id, int $novoStreak): bool {
        return $this->atualizarCampo($id, 'streak', $novoStreak);
    }

    public function atualizarUsuarioUltimoGole(int $id, DateTime $novoUltimoGole): bool {
        return $this->atualizarCampo($id, 'ultimoGole', $novoUltimoGole->format('Y-m-d H:i:s'));
    }

    private function atualizarCampo(int $id, string $campo, mixed $novoValor): bool {
        foreach ($this->dados as &$usuario) {
            if (isset($usuario['id']) && $usuario['id'] === $id) {
                $usuario[$campo] = $novoValor;
                $this->salvar();
                return true;
            }
        }

        return false;
    }
}

?>
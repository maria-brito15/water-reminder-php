<?php

class Usuario {
    private int $id;
    private string $nome;
    private string $email;
    private string $senha;
    private int $intervalo;
    private int $streak;
    private DateTime $ultimoGole;

    public function __construct(
        int $id,
        string $nome,
        string $email,
        string $senha,
        int $intervalo,
        int $streak,
        DateTime $ultimoGole
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->intervalo = $intervalo;
        $this->streak = $streak;
        $this->ultimoGole = $ultimoGole;
    }

    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }
    public function getSenha(): string { return $this->senha; }
    public function getIntervalo(): int { return $this->intervalo; }
    public function getStreak(): int { return $this->streak; }
    public function getUltimoGole(): DateTime { return $this->ultimoGole; }

    public function setNome(string $nome): void { $this->nome = $nome; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setSenha(string $senha): void { $this->senha = $senha; }
    public function setIntervalo(int $intervalo): void { $this->intervalo = $intervalo; }
    public function setStreak(int $streak): void { $this->streak = $streak; }
    public function setUltimoGole(DateTime $ultimoGole): void { $this->ultimoGole = $ultimoGole; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'senha' => $this->senha,
            'intervalo' => $this->intervalo,
            'streak' => $this->streak,
            'ultimoGole' => $this->ultimoGole->format('Y-m-d H:i:s')
        ];
    }
}

?>
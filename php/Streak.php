<?php

class Streak {
    private int $streak;
    private int $intervalo;
    private DateTime $ultimoGole;

    public function __construct(int $streak = 0, int $intervalo = 0, ?DateTime $ultimoGole = null) {
        $this->streak = $streak;
        $this->intervalo = $intervalo;
        $this->ultimoGole = $ultimoGole ?? new DateTime('1970-01-01 00:00:00');
    }

    public function verificarSeStreakQuebrou(): bool {
        $agora = new DateTime();
        $diff = $agora->getTimestamp() - $this->ultimoGole->getTimestamp();

        return ($diff > $this->intervalo);
    }

    public function zerarStreak(): void {
        $this->streak = 0;
        $this->ultimoGole = new DateTime();
    }

    public function incrementarStreak(): void {
        $this->streak++;
        $this->ultimoGole = new DateTime();
    }
    
    public function getStreak(): int {return $this->streak;}
    public function getIntervalo(): int {return $this->intervalo;}
    public function getUltimoGole(): DateTime {return $this->ultimoGole;}

    public function setStreak(int $streak): void {$this->streak = $streak;}
    public function setIntervalo(int $intervalo): void {$this->intervalo = $intervalo;}
    public function setUltimoGole(DateTime $ultimoGole): void {$this->ultimoGole = $ultimoGole;}
}

?>
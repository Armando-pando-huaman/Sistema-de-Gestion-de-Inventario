<?php
class AccesoBloqueado extends RuntimeException {
    public int $espera;
    public function __construct(int $espera) {
        $this->espera = max(1, $espera);
        parent::__construct('Alcanzaste el máximo de 5 intentos fallidos en 15 minutos. Vuelve a intentar en '.(int)ceil($this->espera / 60).' minuto(s).');
    }
}

class LimiteAcceso {
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function autenticar(string $email, callable $verificar) {
        $clave = hash('sha256', mb_strtolower(trim($email), 'UTF-8'));
        $this->db->beginTransaction();
        try {
            // El bloqueo de fila serializa los intentos, incluso desde otras sesiones.
            $s = $this->db->prepare("INSERT INTO acceso_intentos(clave,fallos) VALUES(?,'[]') ON DUPLICATE KEY UPDATE clave=VALUES(clave)");
            $s->execute([$clave]);
            $s = $this->db->prepare('SELECT fallos FROM acceso_intentos WHERE clave=? FOR UPDATE');
            $s->execute([$clave]);
            $guardados = json_decode($s->fetchColumn(), true, 512, JSON_THROW_ON_ERROR);
            $ahora = (int)$this->db->query('SELECT UNIX_TIMESTAMP()')->fetchColumn();
            $fallos = array_values(array_filter($guardados, static fn($fecha) => $fecha > $ahora - 900));
            $usuario = false;
            if (count($fallos) < 5) {
                $usuario = $verificar();
                if ($usuario) $fallos = [];
                else $fallos[] = $ahora;
            }
            $s = $this->db->prepare('UPDATE acceso_intentos SET fallos=? WHERE clave=?');
            $s->execute([json_encode($fallos, JSON_THROW_ON_ERROR), $clave]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
        // Los intentos durante el bloqueo no prolongan la espera.
        if (count($fallos) >= 5) throw new AccesoBloqueado($fallos[0] + 900 - $ahora);
        return $usuario;
    }
}

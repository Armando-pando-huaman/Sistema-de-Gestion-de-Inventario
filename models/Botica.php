<?php
require_once __DIR__.'/Operacion.php';
class Botica {
    private PDO $db;
    public function __construct(?PDO $db = null) { $this->db = $db ?? (new Database())->getConnection(); }
    public function actual(): ?array { return $this->db->query('SELECT * FROM botica WHERE id=1')->fetch() ?: null; }
    public function tieneUsuarios(): bool { return (int)$this->db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() > 0; }
    public function permiteRegistro(): bool {
        $botica = $this->actual();
        return $botica ? (bool)$botica['registro_habilitado'] : !$this->tieneUsuarios();
    }
    private function exclusiva(callable $fn) {
        $lock = $this->db->prepare('SELECT GET_LOCK(?, 10)');
        $key = 'fv_identidad_'.substr(hash('sha256', (string)$this->db->query('SELECT DATABASE()')->fetchColumn()), 0, 32);
        $lock->execute([$key]);
        if ((int)$lock->fetchColumn() !== 1) throw new RuntimeException('Hay otra operacion de registro. Intenta nuevamente.');
        try { return Operacion::transaccion($this->db, $fn); }
        finally { $s=$this->db->prepare('SELECT RELEASE_LOCK(?)'); $s->execute([$key]); }
    }
    public function registrar(array $datos, ?string $foto, ?string $logo): int {
        $nombre = Operacion::texto($datos['nombre'] ?? '', 100);
        $email = trim((string)($datos['email'] ?? ''));
        $password = (string)($datos['password'] ?? '');
        if (strlen($email)>150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Ingresa un correo valido.');
        if (strlen($password)<8 || strlen($password)>72) throw new RuntimeException('La contrasena debe tener entre 8 y 72 caracteres.');
        if ($password !== ($datos['confirmacion'] ?? '')) throw new RuntimeException('Las contrasenas no coinciden.');
        return $this->exclusiva(function () use ($datos,$nombre,$email,$password,$foto,$logo) {
            $botica = $this->actual();
            $crear = ($datos['modo'] ?? '') === 'crear';
            if ($crear) {
                if ($botica || $this->tieneUsuarios()) throw new RuntimeException('La creacion inicial ya no esta disponible.');
                $tienda = Operacion::texto($datos['botica_nombre'] ?? '', 150);
                $this->db->prepare('INSERT INTO botica(id,nombre,registro_habilitado,logo) VALUES(1,?,?,?)')->execute([$tienda,isset($datos['registro_habilitado'])?1:0,$logo]);
            } else {
                if (!$botica || !$botica['registro_habilitado'] || (int)($datos['botica_id'] ?? 0)!==1) throw new RuntimeException('Esta botica no permite nuevos registros.');
            }
            $s=$this->db->prepare('SELECT COUNT(*) FROM usuarios WHERE email=?'); $s->execute([$email]);
            if ($s->fetchColumn()) throw new RuntimeException('Este correo ya esta registrado.');
            $rol = $crear ? 'admin' : 'vendedor';
            $this->db->prepare('INSERT INTO usuarios(nombre,email,password,rol,foto) VALUES(?,?,?,?,?)')->execute([$nombre,$email,password_hash($password,PASSWORD_DEFAULT),$rol,$foto]);
            $id=(int)$this->db->lastInsertId();
            Operacion::audit($this->db,'registro','usuarios',$id,['rol'=>$rol],$id);
            return $id;
        });
    }
    public function configurar(int $usuario, string $nombre, bool $registro, ?string $logo): ?string {
        return $this->exclusiva(function () use ($usuario,$nombre,$registro,$logo) {
            $s=$this->db->prepare("SELECT id FROM usuarios WHERE id=? AND activo=1 AND rol='admin' FOR UPDATE"); $s->execute([$usuario]);
            if (!$s->fetchColumn()) throw new RuntimeException('Solo el administrador puede configurar la botica.');
            $nombre=Operacion::texto($nombre,150); $antes=$this->actual();
            $this->db->prepare('INSERT INTO botica(id,nombre,registro_habilitado,logo) VALUES(1,?,?,?) ON DUPLICATE KEY UPDATE nombre=VALUES(nombre),registro_habilitado=VALUES(registro_habilitado),logo=VALUES(logo)')->execute([$nombre,(int)$registro,$logo ?? ($antes['logo'] ?? null)]);
            Operacion::audit($this->db,'configurar','botica',1,['nombre'=>$nombre,'registro_habilitado'=>$registro],$usuario);
            return $antes['logo'] ?? null;
        });
    }
    public function guardarPerfil(int $id, string $nombre, ?string $foto): ?string {
        return Operacion::transaccion($this->db,function () use ($id,$nombre,$foto) {
            $nombre=Operacion::texto($nombre,100);
            $s=$this->db->prepare('SELECT foto FROM usuarios WHERE id=? AND activo=1 FOR UPDATE'); $s->execute([$id]); $antes=$s->fetch();
            if (!$antes) throw new RuntimeException('Usuario no disponible.');
            $this->db->prepare('UPDATE usuarios SET nombre=?,foto=? WHERE id=?')->execute([$nombre,$foto ?? $antes['foto'],$id]);
            Operacion::audit($this->db,'perfil','usuarios',$id,[], $id);
            return $antes['foto'];
        });
    }
    public function crearVendedor(string $nombre, string $email, string $password): bool {
        return $this->exclusiva(function () use ($nombre,$email,$password) {
            $s=$this->db->prepare("SELECT id FROM usuarios WHERE id=? AND activo=1 AND rol='admin'"); $s->execute([$_SESSION['user_id'] ?? 0]);
            if (!$s->fetchColumn()) throw new RuntimeException('Esta operacion requiere un administrador.');
            $botica=$this->actual();
            if (!$botica || !$botica['registro_habilitado']) throw new RuntimeException('El registro de usuarios esta deshabilitado.');
            $this->db->prepare("INSERT INTO usuarios(nombre,email,password,rol) VALUES(?,?,?,'vendedor')")->execute([$nombre,$email,password_hash($password,PASSWORD_DEFAULT)]);
            Operacion::audit($this->db,'crear','usuarios',(int)$this->db->lastInsertId(),['nombre'=>$nombre,'email'=>$email,'rol'=>'vendedor']);
            return true;
        });
    }
}

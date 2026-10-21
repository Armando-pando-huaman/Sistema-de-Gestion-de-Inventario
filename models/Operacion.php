<?php
class Operacion {
    public static function entero($v, int $min = 1, int $max = 1000000): int {
        $n=filter_var($v,FILTER_VALIDATE_INT);
        if ($n===false || $n<$min || $n>$max) throw new RuntimeException('Cantidad inválida.');
        return $n;
    }
    public static function dinero($v, bool $cero = false): int {
        if (!is_scalar($v) || !preg_match('/^\d{1,7}(?:\.\d{1,2})?$/',(string)$v)) throw new RuntimeException('Importe inválido (máximo dos decimales).');
        $n=(int)round((float)$v*100);
        if ($n<($cero?0:1)) throw new RuntimeException('El importe debe ser mayor a cero.');
        return $n;
    }
    public static function texto($v, int $max = 150): string {
        if (!is_string($v) || trim($v)==='' || mb_strlen(trim($v))>$max) throw new RuntimeException('Completa el texto requerido (máximo '.$max.' caracteres).');
        return trim($v);
    }
    public static function fecha($v): ?string {
        if ($v==='' || $v===null) return null;
        if (!is_string($v)) throw new RuntimeException('Fecha inválida.');
        $d=DateTimeImmutable::createFromFormat('!Y-m-d',$v);
        if (!$d || $d->format('Y-m-d')!==$v) throw new RuntimeException('Fecha inválida.');
        return $v;
    }
    public static function audit(PDO $db, string $accion, string $entidad, ?int $id, array $datos=[], ?int $usuario=null): void {
        unset($datos['password'],$datos['csrf']);
        $s=$db->prepare('INSERT INTO auditoria(usuario_id,accion,entidad,entidad_id,datos) VALUES(?,?,?,?,?)');
        $s->execute([$usuario ?? ($_SESSION['user_id'] ?? null),$accion,$entidad,$id,json_encode($datos,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE)]);
    }
    public static function transaccion(PDO $db, callable $fn) {
        $db->beginTransaction();
        try { $resultado=$fn(); $db->commit(); return $resultado; }
        catch(Throwable $e) { if($db->inTransaction())$db->rollBack(); throw $e; }
    }
}

<?php
require_once __DIR__.'/operacion.php';
class Caja {
    public static function metodos(): array { return ['efectivo'=>'Efectivo','tarjeta'=>'Tarjeta','yape'=>'Yape','plin'=>'Plin','transferencia'=>'Transferencia']; }
    public static function abierta(PDO $db,int $usuario): array {
        // Bloquear usuario serializa aperturas simultáneas y el cierre con nuevas ventas.
        $db->prepare('SELECT id FROM usuarios WHERE id=? FOR UPDATE')->execute([$usuario]);
        $s=$db->prepare('SELECT * FROM cajas WHERE usuario_id=? AND cierre IS NULL ORDER BY id DESC LIMIT 1 FOR UPDATE');$s->execute([$usuario]);$c=$s->fetch();
        if(!$c)throw new RuntimeException('Abre tu caja antes de registrar ventas, reembolsos o movimientos de dinero.');return $c;
    }
    public static function registrar(PDO $db,int $usuario,string $tipo,string $metodo,int $centavos,string $motivo,?int $venta=null): int {
        if(!isset(self::metodos()[$metodo]))throw new RuntimeException('Medio de pago inválido.');
        $c=self::abierta($db,$usuario);
        if($metodo==='efectivo'&&in_array($tipo,['egreso','reembolso'],true)&&$centavos>(int)round(self::esperado($db,$c)*100))throw new RuntimeException('El efectivo disponible en tu caja no alcanza para este egreso o reembolso.');
        $db->prepare('INSERT INTO caja_movimientos(caja_id,usuario_id,tipo,metodo,importe,motivo,venta_id) VALUES(?,?,?,?,?,?,?)')->execute([$c['id'],$usuario,$tipo,$metodo,$centavos/100,$motivo,$venta]);
        return (int)$db->lastInsertId();
    }
    public static function esperado(PDO $db,array $c): float {
        $s=$db->prepare("SELECT COALESCE(SUM(CASE WHEN tipo IN ('venta','ingreso') THEN importe ELSE -importe END),0) FROM caja_movimientos WHERE caja_id=? AND metodo='efectivo'");$s->execute([$c['id']]);
        return round((float)$c['inicial']+(float)$s->fetchColumn(),2);
    }
    public static function operar(PDO $db,int $usuario,array $d): void {
        Operacion::transaccion($db,function()use($db,$usuario,$d){
            $accion=$d['accion']??'';
            if($accion==='abrir'){
                $db->prepare('SELECT id FROM usuarios WHERE id=? FOR UPDATE')->execute([$usuario]);
                $s=$db->prepare('SELECT id FROM cajas WHERE usuario_id=? AND cierre IS NULL FOR UPDATE');$s->execute([$usuario]);if($s->fetch())throw new RuntimeException('Ya tienes una caja abierta.');
                $valor=Operacion::dinero($d['importe']??'',true);
                $db->prepare('INSERT INTO cajas(usuario_id,inicial) VALUES(?,?)')->execute([$usuario,$valor/100]);
                Operacion::audit($db,'abrir','cajas',(int)$db->lastInsertId(),['inicial'=>$valor/100],$usuario);
            }elseif($accion==='cerrar'){
                $c=self::abierta($db,$usuario);$esperado=self::esperado($db,$c);$contado=Operacion::dinero($d['importe']??'',true)/100;
                $db->prepare('UPDATE cajas SET cierre=NOW(),contado=?,esperado=?,diferencia=? WHERE id=?')->execute([$contado,$esperado,round($contado-$esperado,2),$c['id']]);
                Operacion::audit($db,'cerrar','cajas',(int)$c['id'],['esperado'=>$esperado,'contado'=>$contado,'diferencia'=>$contado-$esperado],$usuario);
            }elseif(in_array($accion,['ingreso','egreso','gasto'],true)){
                if($accion==='gasto'&&($_SESSION['user_rol']??'')!=='admin')throw new RuntimeException('Solo el administrador registra gastos.');
                $importe=Operacion::dinero($d['importe']??'');$motivo=Operacion::texto($d['motivo']??'');
                $id=self::registrar($db,$usuario,$accion==='gasto'?'egreso':$accion,$d['metodo']??'',$importe,$motivo);
                if($accion==='gasto')$db->prepare('INSERT INTO gastos(concepto,importe,usuario_id,caja_movimiento_id) VALUES(?,?,?,?)')->execute([$motivo,$importe/100,$usuario,$id]);
                Operacion::audit($db,$accion,'caja_movimientos',$id,['importe'=>$importe/100,'motivo'=>$motivo],$usuario);
            }else throw new RuntimeException('Operación de caja inválida.');
        });
    }
}

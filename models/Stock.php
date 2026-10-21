<?php
require_once __DIR__.'/Operacion.php';
class Stock {
    public static function producto(PDO $db, int $id): array {
        $s=$db->prepare('SELECT * FROM productos WHERE id=? FOR UPDATE');$s->execute([$id]);$p=$s->fetch();
        if(!$p)throw new RuntimeException('Producto no encontrado.');
        return $p;
    }
    /** Todas las operaciones se invocan dentro de la transacción del documento. */
    public static function entrada(PDO $db,int $id,int $cantidad,string $codigo,?string $vence,?float $costo,bool $verificar=false): int {
        self::producto($db,$id);
        $s=$db->prepare('INSERT INTO lotes(producto_id,codigo,vencimiento,stock,costo_unidad,por_verificar) VALUES(?,?,?,?,?,?)');
        $s->execute([$id,Operacion::texto($codigo,80),$vence,$cantidad,$costo,(int)$verificar]);$lote=(int)$db->lastInsertId();
        $db->prepare('UPDATE productos SET stock=stock+? WHERE id=?')->execute([$cantidad,$id]);
        return $lote;
    }
    public static function salida(PDO $db,int $id,int $cantidad,?int $lote=null,bool $vencidos=false,bool $verificados=false): array {
        $p=self::producto($db,$id);
        if($cantidad<1 || $cantidad>(int)$p['stock'])throw new RuntimeException('Stock insuficiente de '.$p['nombre']);
        $sql='SELECT * FROM lotes WHERE producto_id=? AND stock>0';$args=[$id];
        if($lote){$sql.=' AND id=?';$args[]=$lote;}
        if(!$vencidos)$sql.=' AND (vencimiento IS NULL OR vencimiento>=CURDATE())';
        if($verificados)$sql.=' AND por_verificar=0';
        $sql.=' ORDER BY vencimiento IS NULL,vencimiento,id FOR UPDATE';
        $s=$db->prepare($sql);$s->execute($args);$filas=$s->fetchAll();
        if(array_sum(array_column($filas,'stock'))<$cantidad)throw new RuntimeException('No hay suficientes unidades disponibles en lotes vigentes de '.$p['nombre']);
        $resta=$cantidad;$asignados=[];
        foreach($filas as $fila){if(!$resta)break;$usar=min($resta,(int)$fila['stock']);
            $db->prepare('UPDATE lotes SET stock=stock-? WHERE id=?')->execute([$usar,$fila['id']]);
            $asignados[]=['lote_id'=>(int)$fila['id'],'cantidad'=>$usar,'costo_unidad'=>$fila['costo_unidad']];$resta-=$usar;
        }
        $db->prepare('UPDATE productos SET stock=stock-? WHERE id=?')->execute([$cantidad,$id]);return $asignados;
    }
    public static function movimiento(PDO $db,int $producto,string $tipo,int $cantidad,string $motivo,int $usuario,?int $venta=null,?int $lote=null,?string $referencia=null): void {
        $db->prepare('INSERT INTO movimientos(producto_id,tipo,cantidad,motivo,usuario_id,venta_id,lote_id,referencia) VALUES(?,?,?,?,?,?,?,?)')->execute([$producto,$tipo,$cantidad,$motivo,$usuario,$venta,$lote,$referencia]);
    }
}

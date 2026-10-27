<?php
require_once __DIR__.'/stock.php';
require_once __DIR__.'/caja.php';
class Devolucion {
    public static function registrar(PDO $db,int $venta,int $usuario,array $cantidades,string $motivo,string $metodo,bool $anular=false,bool $reintegrar=true): int {
        return Operacion::transaccion($db,function()use($db,$venta,$usuario,$cantidades,$motivo,$metodo,$anular,$reintegrar){
            Caja::abierta($db,$usuario);
            $motivo=Operacion::texto($motivo,255);
            $s=$db->prepare('SELECT * FROM ventas WHERE id=? FOR UPDATE');$s->execute([$venta]);$v=$s->fetch();
            if(!$v||in_array($v['estado'],['anulada','devuelta'],true))throw new RuntimeException('Venta inexistente o completamente reembolsada.');
            $s=$db->prepare('SELECT * FROM detalle_venta WHERE venta_id=? ORDER BY producto_id,id FOR UPDATE');$s->execute([$venta]);$detalles=$s->fetchAll();
            $total=0;$lineas=[];
            foreach($detalles as $d){
                $vendido=(int)($d['cantidad_unidades']??($d['cantidad']*($d['factor_unidades']??1)));
                $devuelto=(int)$d['devuelto_unidades'];$resta=$vendido-$devuelto;
                $n=$anular?$resta:Operacion::entero($cantidades[$d['id']]??0,0);
                if(!$n)continue;
                if($n>$resta)throw new RuntimeException('La devolución supera las unidades pendientes.');
                $nuevoImporte=(int)round((float)$d['subtotal']*100*($devuelto+$n)/$vendido);
                $importe=$nuevoImporte-(int)round((float)$d['reembolsado']*100);$total+=$importe;
                if($reintegrar)Stock::producto($db,(int)$d['producto_id']);
                $s=$db->prepare('SELECT * FROM venta_lotes WHERE detalle_id=? ORDER BY id FOR UPDATE');$s->execute([$d['id']]);$asignaciones=$s->fetchAll();$pendiente=$n;$costoDevuelto=0;
                foreach($asignaciones as $a){$usar=min($pendiente,(int)$a['cantidad']-(int)$a['devuelto']);if(!$usar)continue;
                    if($reintegrar){if($a['costo_unidad']===null)$costoDevuelto=null;elseif($costoDevuelto!==null)$costoDevuelto+=$usar*(float)$a['costo_unidad'];}
                    if($reintegrar){$db->prepare('UPDATE lotes SET stock=stock+? WHERE id=?')->execute([$usar,$a['lote_id']]);Stock::movimiento($db,(int)$d['producto_id'],'entrada',$usar,'Devolución de venta',$usuario,$venta,(int)$a['lote_id']);}
                    $db->prepare('UPDATE venta_lotes SET devuelto=devuelto+? WHERE id=?')->execute([$usar,$a['id']]);$pendiente-=$usar;if(!$pendiente)break;
                }
                if($pendiente && $asignaciones)throw new RuntimeException('Las asignaciones de lotes no coinciden. Revisa el historial.');
                if($reintegrar){
                    if($pendiente){$lote=Stock::entrada($db,(int)$d['producto_id'],$pendiente,'DEV-LEGACY-'.$venta,null,null,true);Stock::movimiento($db,(int)$d['producto_id'],'entrada',$pendiente,'Devolución de venta',$usuario,$venta,$lote);}
                    else $db->prepare('UPDATE productos SET stock=stock+? WHERE id=?')->execute([$n,$d['producto_id']]);
                }
                $db->prepare('UPDATE detalle_venta SET devuelto_unidades=devuelto_unidades+?,reembolsado=? WHERE id=?')->execute([$n,$nuevoImporte/100,$d['id']]);
                if($reintegrar&&!$asignaciones)$costoDevuelto=null;
                $lineas[]=[$d['id'],$n,$importe/100,$costoDevuelto];
            }
            if(!$lineas)throw new RuntimeException('Selecciona al menos una unidad pendiente.');
            $db->prepare('INSERT INTO devoluciones(venta_id,usuario_id,motivo,total,tipo,reintegrar) VALUES(?,?,?,?,?,?)')->execute([$venta,$usuario,$motivo,$total/100,$anular?'anulacion':'devolucion',(int)$reintegrar]);$id=(int)$db->lastInsertId();
            foreach($lineas as $l)$db->prepare('INSERT INTO devolucion_detalles(devolucion_id,detalle_id,cantidad,importe,costo_reintegrado) VALUES(?,?,?,?,?)')->execute([$id,...$l]);
            if($total)Caja::registrar($db,$usuario,'reembolso',$metodo,$total,'Reembolso venta #'.$venta,$venta);
            $s=$db->prepare('SELECT SUM(COALESCE(cantidad_unidades,cantidad*factor_unidades)-devuelto_unidades) FROM detalle_venta WHERE venta_id=?');$s->execute([$venta]);$restantes=(int)$s->fetchColumn();
            $db->prepare('UPDATE ventas SET estado=? WHERE id=?')->execute([$anular?'anulada':($restantes?'parcial':'devuelta'),$venta]);
            Operacion::audit($db,$anular?'anular':'devolver','ventas',$venta,['motivo'=>$motivo,'importe'=>$total/100,'reintegrar'=>$reintegrar,'lineas'=>$lineas],$usuario);return $id;
        });
    }
}

<?php
require_once __DIR__.'/stock.php';
require_once __DIR__.'/caja.php';
class Gestion {
    public PDO $db;
    public function __construct(?PDO $db=null){$this->db=$db??(new Database())->getConnection();}
    public function compra(array $d,int $usuario): int {
        $operacion=function()use($d,$usuario){
            $db=$this->db;$proveedor=Operacion::entero($d['proveedor_id']??null);
            $s=$db->prepare('SELECT id FROM proveedores WHERE id=? AND activo=1');$s->execute([$proveedor]);if(!$s->fetch())throw new RuntimeException('Proveedor no válido.');
            $documento=Operacion::texto($d['documento']??'',80);$lineas=$d['lineas']??[];
            if(!is_array($lineas)||!$lineas||count($lineas)>100)throw new RuntimeException('Agrega entre 1 y 100 productos a la compra.');
            usort($lineas,fn($a,$b)=>(int)($a['producto_id']??0)<=>(int)($b['producto_id']??0));
            $db->prepare('INSERT INTO compras(proveedor_id,documento,total,usuario_id) VALUES(?,?,0,?)')->execute([$proveedor,$documento,$usuario]);$id=(int)$db->lastInsertId();$total=0;
            foreach($lineas as $linea){
                $producto=Operacion::entero($linea['producto_id']??null);$p=Stock::producto($db,$producto);if(!$p['activo'])throw new RuntimeException('Reactiva el producto antes de comprarlo.');
                $cantidad=Operacion::entero($linea['cantidad']??null);$costoCent=Operacion::dinero($linea['costo_unidad']??'');$costo=$costoCent/100;$total+=$cantidad*$costoCent;
                if($total>999999999)throw new RuntimeException('El importe de la compra supera el máximo permitido.');
                $loteId=Stock::entrada($db,$producto,$cantidad,Operacion::texto($linea['lote']??'',80),Operacion::fecha($linea['vencimiento']??''),$costo);
                $db->prepare('INSERT INTO compra_detalles(compra_id,producto_id,lote_id,cantidad,costo_unidad) VALUES(?,?,?,?,?)')->execute([$id,$producto,$loteId,$cantidad,$costo]);
                Stock::movimiento($db,$producto,'entrada',$cantidad,'Compra / reposición',$usuario,null,$loteId,'Compra #'.$id);
                $db->prepare('UPDATE productos SET precio_compra=? WHERE id=?')->execute([$costo*max(1,(int)$p['unidades_por_caja']),$producto]);
            }
            $db->prepare('UPDATE compras SET total=? WHERE id=?')->execute([$total/100,$id]);
            Operacion::audit($db,'registrar','compras',$id,['total'=>$total/100,'lineas'=>count($lineas)],$usuario);return $id;
        };return $this->db->inTransaction()?$operacion():Operacion::transaccion($this->db,$operacion);
    }
    public function proveedor(array $d,int $usuario): void {
        Operacion::transaccion($this->db,function()use($d,$usuario){
            $this->db->prepare('INSERT INTO proveedores(nombre,documento,telefono) VALUES(?,?,?)')->execute([Operacion::texto($d['nombre']??''),trim((string)($d['documento']??'')),trim((string)($d['telefono']??''))]);
            Operacion::audit($this->db,'crear','proveedores',(int)$this->db->lastInsertId(),[],$usuario);
        });
    }
    public function verificarLote(array $d,int $usuario): void {
        Operacion::transaccion($this->db,function()use($d,$usuario){
            $id=Operacion::entero($d['id']??null);$s=$this->db->prepare('SELECT * FROM lotes WHERE id=? FOR UPDATE');$s->execute([$id]);$l=$s->fetch();if(!$l)throw new RuntimeException('Lote no encontrado.');
            if(!$l['por_verificar'])throw new RuntimeException('Este lote ya se verificó. No se puede alterar su costo histórico.');
            $this->db->prepare('UPDATE lotes SET codigo=?,vencimiento=?,costo_unidad=?,por_verificar=0 WHERE id=?')->execute([Operacion::texto($d['lote']??'',80),Operacion::fecha($d['vencimiento']??''),Operacion::dinero($d['costo_unidad']??'',true)/100,$id]);
            Operacion::audit($this->db,'verificar','lotes',$id,['antes'=>$l,'despues'=>['codigo'=>$d['lote'],'vencimiento'=>$d['vencimiento'],'costo_unidad'=>$d['costo_unidad']]],$usuario);
        });
    }
}

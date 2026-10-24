<?php
require_once __DIR__.'/Operacion.php';
require_once __DIR__.'/Botica.php';
class Usuario {
 private PDO $conn;
 public function __construct(){$this->conn=(new Database())->getConnection();}
 public function login($email,$password){$s=$this->conn->prepare('SELECT * FROM usuarios WHERE email=? AND activo=1 LIMIT 1');$s->execute([$email]);$u=$s->fetch();return $u&&password_verify($password,$u['password'])?$u:false;}
 public function getAll(){return $this->conn->query('SELECT id,nombre,email,rol,created_at FROM usuarios WHERE activo=1 ORDER BY id DESC')->fetchAll();}
 public function getById($id){$s=$this->conn->prepare('SELECT * FROM usuarios WHERE id=?');$s->execute([$id]);return $s->fetch();}
 public function existeEmail($email,$excluirId=null){$s=$this->conn->prepare('SELECT COUNT(*) FROM usuarios WHERE email=? AND id<>?');$s->execute([$email,$excluirId??0]);return $s->fetchColumn()>0;}
 public function crear($nombre,$email,$password,$rol='vendedor'){return (new Botica($this->conn))->crearVendedor($nombre,$email,$password);}
 public function actualizar($id,$nombre,$email,$rol,$password=null){return Operacion::transaccion($this->conn,function()use($id,$nombre,$email,$rol,$password){
  $admins=$this->conn->query("SELECT id FROM usuarios WHERE rol='admin' AND activo=1 ORDER BY id FOR UPDATE")->fetchAll(PDO::FETCH_COLUMN);$antes=$this->getById($id);
  if($antes['rol']==='admin'&&$rol!=='admin'&&count($admins)<2)throw new RuntimeException('Debe quedar al menos un administrador activo.');
  $sql='UPDATE usuarios SET nombre=?,email=?,rol=?';$args=[$nombre,$email,$rol];if($password){$sql.=',password=?';$args[]=password_hash($password,PASSWORD_DEFAULT);}$args[]=$id;
  $this->conn->prepare($sql.' WHERE id=?')->execute($args);unset($antes['password']);Operacion::audit($this->conn,'editar','usuarios',(int)$id,['antes'=>$antes,'despues'=>['nombre'=>$nombre,'email'=>$email,'rol'=>$rol],'cambio_password'=>(bool)$password]);return true;
 });}
 public function eliminar($id){return Operacion::transaccion($this->conn,function()use($id){
  $admins=$this->conn->query("SELECT id FROM usuarios WHERE rol='admin' AND activo=1 ORDER BY id FOR UPDATE")->fetchAll(PDO::FETCH_COLUMN);
  if((int)$id===(int)($_SESSION['user_id']??0))throw new RuntimeException('No puedes desactivar tu propio usuario.');
  if(in_array((string)$id,array_map('strval',$admins),true)&&count($admins)<2)throw new RuntimeException('Debe quedar un administrador activo.');
  $this->conn->prepare('UPDATE usuarios SET activo=0 WHERE id=?')->execute([$id]);Operacion::audit($this->conn,'desactivar','usuarios',(int)$id);return true;
 });}
}

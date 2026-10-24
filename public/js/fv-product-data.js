(() => {
 'use strict';
 const key = v => v.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[\s_-]/g,'');
 function date(value, gs1=false) {
  if(typeof value!=='string')return '';
  let y,m,d;
  if(gs1&&/^\d{6}$/.test(value)){y=2000+Number(value.slice(0,2));m=Number(value.slice(2,4));d=Number(value.slice(4,6));if(d===0)d=new Date(Date.UTC(y,m,0)).getUTCDate();}
  else if(/^\d{4}-\d{2}-\d{2}$/.test(value)){[y,m,d]=value.split('-').map(Number);}
  else if(/^\d{2}\/\d{2}\/\d{4}$/.test(value)){[d,m,y]=value.split('/').map(Number);}
  else return '';
  const check=new Date(Date.UTC(y,m-1,d));
  return y>=2000&&y<=2099&&check.getUTCFullYear()===y&&check.getUTCMonth()===m-1&&check.getUTCDate()===d?`${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`:'';
 }
 function decode(raw) {
  if(typeof raw!=='string'||raw.length>8192)throw new Error('El contenido del código es demasiado largo.');
  const text=raw.trim();if(!text)throw new Error('El código está vacío.');
  const data=Object.create(null),warnings=[];
  const add=(k,v)=>{if(typeof v==='string'||typeof v==='number')data[key(k)]=String(v).trim();};
  if(text.startsWith('{')){
   let json;try{json=JSON.parse(text);}catch(_){throw new Error('El QR contiene un JSON inválido.');}
   if(!json||Array.isArray(json)||typeof json!=='object')throw new Error('El QR no contiene una ficha de producto.');
   for(const obj of [json,json.producto,json.product])if(obj&&typeof obj==='object'&&!Array.isArray(obj))for(const [k,v] of Object.entries(obj))add(k,v);
  }else if(/^https?:\/\//i.test(text)){
   const url=new URL(text);for(const [k,v] of url.searchParams)add(k,v);
   const parts=url.pathname.split('/').filter(Boolean);for(let i=0;i<parts.length-1;i++)if(['01','10','17'].includes(parts[i])){add(parts[i],decodeURIComponent(parts[i+1]));i++;}
  }else if(/^\(01\)/.test(text)){
   for(const match of text.matchAll(/\((\d{2,4})\)([^()]*)/g))add(match[1],match[2]);
  }else if(/^(?:\][A-Za-z]\d)?01\d{14}/.test(text)){
   let rest=text.replace(/^\][A-Za-z]\d/,'');
   const fixed={'01':14,'17':6,'11':6,'13':6,'15':6};
   while(rest){rest=rest.replace(/^\x1d/,'');if(!rest)break;const ai=rest.slice(0,2);rest=rest.slice(2);
    if(fixed[ai]){if(rest.length<fixed[ai])throw new Error('Los datos GS1 están incompletos.');add(ai,rest.slice(0,fixed[ai]));rest=rest.slice(fixed[ai]);}
    else if(['10','21'].includes(ai)){const end=rest.indexOf('\x1d');if(end<0&&rest.length>20){warnings.push('El código GS1 contiene campos sin separador; revisa el lote.');break;}add(ai,end<0?rest:rest.slice(0,end));rest=end<0?'':rest.slice(end+1);}
    else{warnings.push('El código incluye campos GS1 no interpretados.');break;}
   }
  }else if(/(?:^|[;\r\n])\s*(?:codigo|nombre|name|barcode|gtin|descripcion|vencimiento)\s*[:=]/i.test(text)){
   for(const line of text.split(/[;\r\n]+/)){const match=line.match(/^\s*([^:=]+)\s*[:=]\s*(.*)$/);if(match)add(match[1],match[2]);}
  }else if(/^[A-Za-z0-9._/-]{1,50}$/.test(text))add('codigo',text);
  else throw new Error('El QR no contiene datos de producto reconocibles.');
  const pick=(...keys)=>keys.map(k=>data[key(k)]).find(v=>v!==undefined&&v!=='')||'';
  const result={codigo_barras:pick('01','codigo_barras','barcode','ean','gtin','codigo','code'),nombre:pick('nombre','name','product_name'),descripcion:pick('descripcion','description'),lote:pick('10','lote','batch','lot'),fecha_vencimiento:'',warnings};
  const exp=pick('17','fecha_vencimiento','expiry','expiration_date','expiration','expires','vencimiento','caducidad');
  if(exp){result.fecha_vencimiento=date(exp,!!data['17']);if(!result.fecha_vencimiento)warnings.push('No se cargó una fecha de vencimiento inválida; revísala en el envase.');}
  for(const [field,max] of [['codigo_barras',50],['nombre',150],['descripcion',4000],['lote',80]])if(result[field].length>max){result[field]='';warnings.push(`El campo ${field} supera el tamaño permitido y no se cargó.`);}
  if(!result.codigo_barras&&!result.nombre&&!result.descripcion&&!result.fecha_vencimiento&&!result.lote)throw new Error('El QR no incluye campos reconocibles para el producto.');
  return result;
 }
 window.FvProductData={decode};
 const form=document.querySelector('[data-product-autofill]');if(!form)return;
 const code=form.elements.codigo_barras,status=document.getElementById('fv-product-data-status'),link=document.getElementById('fv-existing-product');
 const automatic=new Map();let sequence=0,controller=null,lastIdentity=null;
 function fill(name,value){const input=form.elements[name];if(!input||!value||input.readOnly||input.disabled)return false;if(input.value.trim()&&input.value!==automatic.get(name))return false;input.value=value;automatic.set(name,value);input.dispatchEvent(new Event('change',{bubbles:true}));return true;}
 async function apply(raw){
  const current=++sequence;controller?.abort();link.hidden=true;status.textContent='';
  let info;try{info=decode(raw);}catch(error){status.textContent=error.message;return;}
  const identity=info.codigo_barras||raw;
  if(lastIdentity!==null&&lastIdentity!==identity){for(const [name,value] of automatic){const field=form.elements[name];if(field&&field.value===value)field.value='';}automatic.clear();}
  lastIdentity=identity;
  const loaded=[];
  for(const [field,label] of [['nombre','nombre'],['descripcion','descripción'],['fecha_vencimiento','vencimiento'],['lote_inicial','lote']])if(fill(field,field==='lote_inicial'?info.lote:info[field]))loaded.push(label);
  code.value=info.codigo_barras;code.setCustomValidity('');
  status.textContent=(loaded.length?'Cargado del código: '+loaded.join(', ')+'. ':'')+info.warnings.join(' ');
  if(!info.codigo_barras){status.textContent+=' El QR no incluía código identificador; puedes escribirlo si lo tienes.';return;}
  controller=new AbortController();const queried=info.codigo_barras;
  try{
   const response=await fetch('index.php?c=Producto&a=consultarCodigo&codigo='+encodeURIComponent(queried),{signal:controller.signal,headers:{Accept:'application/json'}});
   if(!response.ok)throw new Error('No se pudo consultar el catálogo local.');const result=await response.json();
   if(current!==sequence||code.value!==queried)return;
   if(result.producto){
    fill('nombre',result.producto.nombre);fill('descripcion',result.producto.descripcion);
    link.href='index.php?c=Producto&a=editar&id='+Number(result.producto.id);link.hidden=false;
    status.textContent+=' Este código ya existe'+(Number(result.producto.activo)?'':' en un producto inactivo')+'. Se completaron los campos vacíos desde el catálogo; abre su ficha para evitar duplicarlo. La fecha del lote no se copia de registros anteriores.';
   }else status.textContent+=result.ambiguo?' Hay varios productos con ese código; revisa el catálogo.':(loaded.length?'':' Código leído. No existe en el catálogo y no contiene nombre ni descripción; completa esos datos una sola vez.');
  }catch(error){if(error.name!=='AbortError'&&current===sequence)status.textContent+=' '+(error.message||'No se pudo consultar el catálogo.');}
 }
 code.addEventListener('fv:scan',event=>{event.preventDefault();apply(event.detail.value);});
 code.addEventListener('change',()=>{if(code.value.trim())apply(code.value);});
 code.addEventListener('input',()=>{sequence++;controller?.abort();link.hidden=true;status.textContent='';});
 code.addEventListener('keydown',event=>{if(event.key==='Enter'){event.preventDefault();if(code.value.trim())apply(code.value);}});
})();

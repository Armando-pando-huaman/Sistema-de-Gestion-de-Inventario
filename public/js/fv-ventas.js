(() => {
 'use strict';
 const form=document.getElementById('formVenta'), list=document.getElementById('listaProductos'), tbody=document.getElementById('carritoBody'), search=document.getElementById('codigo_barras'), hidden=document.getElementById('inputCarrito'), total=document.getElementById('totalTexto'), submit=document.getElementById('btnVender');
 if(!form||!list||!tbody||!search||!hidden||!total||!submit){console.error('FarmaVidaHC: faltan elementos del punto de venta');return;}
 const products=[...list.querySelectorAll('[data-producto]')].map(el=>{try{return {...JSON.parse(el.dataset.producto),el}}catch(e){console.error('Producto mal configurado',e);return null}}).filter(Boolean);
 const cart=new Map();
 const cents=n=>Math.round(Number(n)*100), money=n=>(n/100).toFixed(2);
 const options=p=>{
   if(!Number(p.fraccionable)) return [{id:'unidad',name:'Producto completo',factor:1,price:p.precio_venta}];
   const candidates=[{id:'unidad',name:'Unidad',factor:1,price:p.precio_unidad},{id:'blister',name:'Paquete / blíster',factor:Number(p.unidad),price:p.precio_blister},{id:'caja',name:'Caja',factor:Number(p.caja),price:p.precio_caja}];
   return candidates.filter(x=>Number.isSafeInteger(x.factor)&&x.factor>=1&&x.price!==null&&x.price!==''&&Number.isFinite(Number(x.price))&&Number(x.price)>0);
 };
 const used=id=>[...cart.values()].filter(x=>x.id===id).reduce((n,x)=>{const p=products.find(p=>p.id===id);if(!p) return n;const o=options(p).find(o=>o.id===x.presentacion); return n+x.cantidad*(o?.factor||1);},0);
 const payload=()=>[...cart.values()].map(({id,presentacion,cantidad})=>({id,presentacion,cantidad}));
 const sync=()=>{hidden.value=JSON.stringify(payload());};
 const el=(tag,cls,txt)=>{const e=document.createElement(tag);if(cls)e.className=cls;if(txt!==undefined)e.textContent=txt;return e;};
 function add(p,pres){
   const opts=options(p),opt=opts.find(o=>o.id===pres);
   if(!opt){alert('Esta presentación no tiene precio o factor configurado para '+p.nombre);return;}
   const key=`${p.id}:${pres}`,prev=cart.get(key),newCount=(prev?.cantidad||0)+1;
   if(used(p.id)+opt.factor>Number(p.stock)){alert('No hay stock suficiente de '+p.nombre);return;}
   cart.set(key,{id:p.id,presentacion:pres,cantidad:newCount});render();
 }
 // Cada producto tiene sus propios botones de presentación en el catálogo.
 for(const p of products){
   const old=p.el.querySelector('.fv-add');if(!old)continue;
   const wrap=el('div','d-flex flex-wrap gap-1 fv-presentaciones-botones');
   const opts=options(p);
   if(!opts.length){const warn=el('small','text-danger','Configura precios y presentaciones');wrap.append(warn);}
   for(const o of opts){
     const btn=el('button','btn btn-sm btn-outline-primary fv-add',`+ ${o.name} · S/ ${money(cents(o.price))}`);
     btn.type='button';btn.dataset.presentacion=o.id;
     btn.title=`Agregar ${o.name} (${o.factor} unidades de inventario)`;
     wrap.append(btn);
   }
   old.replaceWith(wrap);
 }
 function changePresentation(item,oldKey,p,newPres){const opt=options(p).find(x=>x.id===newPres);if(!opt)return;
   const otherUsed=used(p.id)-item.cantidad*options(p).find(x=>x.id===item.presentacion).factor;
   const destKey=`${p.id}:${newPres}`,existing=cart.get(destKey),newQty=item.cantidad+(existing?.cantidad||0);
   if(otherUsed-(existing?.cantidad||0)*opt.factor+newQty*opt.factor>Number(p.stock)){alert('Stock insuficiente para '+p.nombre);return;}
   cart.delete(oldKey);cart.set(destKey,{id:p.id,presentacion:newPres,cantidad:newQty});render();
 }
 function render(){tbody.replaceChildren();let sum=0;
   for(const [key,item] of cart){const p=products.find(p=>p.id===item.id);if(!p){cart.delete(key);continue;}const opts=options(p),opt=opts.find(x=>x.id===item.presentacion);if(!opt){cart.delete(key);continue;}
     const tr=el('tr'),productTd=el('td'),name=el('strong','d-block',p.nombre);productTd.append(name);
     productTd.append(el('div','small text-primary mt-1 fv-cart-presentation',opt.name+' · S/ '+money(cents(opt.price))+' por presentación'));
     productTd.append(el('small','d-block text-muted mt-1 fv-cart-stock',`Descuenta ${item.cantidad*opt.factor} unidades · Disponibles después de esta venta: ${Number(p.stock)-used(p.id)}`));tr.append(productTd);
     const qtyTd=el('td'),qty=el('input','form-control form-control-sm');qty.type='number';qty.min='1';qty.step='1';qty.classList.add('fv-cart-qty');qty.style.minWidth='72px';qty.value=item.cantidad;
     qty.addEventListener('change',()=>{const n=Number(qty.value);if(!Number.isSafeInteger(n)||n<1||used(p.id)+(n-item.cantidad)*opt.factor>Number(p.stock)){alert('Cantidad inválida o stock insuficiente');qty.value=item.cantidad;return;}item.cantidad=n;render();});qtyTd.append(qty);tr.append(qtyTd);
     const line=cents(opt.price)*item.cantidad;sum+=line;tr.append(el('td','text-nowrap fv-cart-subtotal','S/ '+money(line)));
     const removeTd=el('td'),remove=el('button','btn btn-sm btn-outline-danger fv-cart-remove','×');remove.type='button';remove.title='Eliminar esta presentación';remove.addEventListener('click',()=>{cart.delete(key);render();});removeTd.append(remove);tr.append(removeTd);tbody.append(tr);
   }
   if(!cart.size){const tr=el('tr'),td=el('td','text-muted text-center py-4','Escanea o agrega productos');td.colSpan=4;tr.append(td);tbody.append(tr);}
   total.textContent=money(sum);sync();submit.disabled=!cart.size||sum<=0||products.some(p=>used(p.id)>Number(p.stock));
 }
 list.addEventListener('click',e=>{const button=e.target.closest('.fv-add');if(!button)return;const p=products.find(p=>p.el===button.closest('[data-producto]'));if(p)add(p,button.dataset.presentacion||options(p)[0]?.id);});
 function filter(){const q=search.value.trim().toLocaleLowerCase();const mobile=window.matchMedia('(max-width:767.98px)').matches;products.forEach(p=>p.el.hidden=(mobile&&!q)||!p.el.dataset.search.includes(q));const exact=products.find(p=>q&&p.codigo&&String(p.codigo).toLocaleLowerCase()===q);if(exact){add(exact,options(exact)[0]?.id);search.value='';products.forEach(p=>p.el.hidden=mobile);}const hint=document.getElementById('fv-mobile-search-hint');if(hint){hint.hidden=!(mobile&&!search.value.trim());}}
 search.addEventListener('input',filter);
 window.addEventListener('resize',filter);
 search.addEventListener('keydown',e=>{if(e.key!=='Enter')return;e.preventDefault();const q=search.value.trim().toLocaleLowerCase();const p=products.find(p=>String(p.codigo||'').toLocaleLowerCase()===q)||products.find(p=>!p.el.hidden);if(p){add(p,options(p)[0]?.id);search.value='';filter();}});
 form.addEventListener('submit',e=>{sync();if(!cart.size){e.preventDefault();alert('Agrega productos al carrito');return;}if(products.some(p=>used(p.id)>Number(p.stock))){e.preventDefault();alert('Stock insuficiente');return;}if(!form.elements['csrf']?.value){e.preventDefault();alert('Recarga la página: falta token de sesión');return;}submit.disabled=true;});
 render();filter();
})();

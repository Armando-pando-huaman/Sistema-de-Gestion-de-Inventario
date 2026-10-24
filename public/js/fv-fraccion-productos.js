(() => {
const checkbox=document.getElementById('fv-frac'), fields=document.getElementById('fv-frac-fields');
if(!checkbox||!fields)return;
const form=checkbox.closest('form'), label=document.getElementById('fv-stock-label'), hint=document.getElementById('fv-stock-hint');
const isEdit=location.search.includes('a=editar');
function refresh(){const on=checkbox.checked;fields.hidden=!on;if(hint)hint.hidden=!on;
 for(const input of fields.querySelectorAll('input'))input.disabled=!on;
 const b=Number(form.elements['blisters_por_caja'].value)||0,u=Number(form.elements['unidades_por_blister'].value)||0;
 document.getElementById('fv-frac-total').textContent=on?String(b*u):'—';
 if(label)label.textContent=isEdit?(on?'Stock actual (unidades)':'Stock actual'):on?'Stock inicial (cajas) *':'Stock inicial (productos) *';
 if(hint)hint.textContent=isEdit?'El stock se consulta aquí; se modifica desde Movimientos. Puedes corregir los vencimientos en este formulario.':on?'Ingresa cajas completas: el sistema las convierte a unidades. El stock mínimo se expresa en unidades.':'Cada producto completo equivale a 1 unidad de inventario.';
 if(form.hasAttribute('data-initial-stock-units')){if(label)label.textContent=on?'Stock inicial (unidades mínimas) *':'Stock inicial (productos) *';if(hint)hint.textContent=on?'Las existencias iniciales se ingresan en unidades mínimas. Multiplica las cajas por las unidades por caja.':'Puedes agregar existencias iniciales en la sección opcional.';}
 const price=form.elements['precio_venta'];if(price){price.closest('.col-6, .col-md-3, div').hidden=on;price.disabled=on;}
}
checkbox.addEventListener('change',refresh);for(const x of fields.querySelectorAll('input'))x.addEventListener('input',refresh);refresh();
})();

(() => {
const checkbox=document.getElementById('fv-frac'), fields=document.getElementById('fv-frac-fields');
if(!checkbox||!fields)return;
const form=checkbox.closest('form'), label=document.getElementById('fv-stock-label'), hint=document.getElementById('fv-stock-hint');
const isEdit=location.search.includes('a=editar');
function refresh(){const on=checkbox.checked;fields.hidden=!on;
 for(const input of fields.querySelectorAll('input'))input.disabled=!on;
 const b=Number(form.elements['blisters_por_caja'].value)||0,u=Number(form.elements['unidades_por_blister'].value)||0;
 document.getElementById('fv-frac-total').textContent=on?String(b*u):'—';
 if(label)label.textContent=isEdit?'Stock actual (unidades mínimas) *':on?'Stock inicial (cajas completas) *':'Stock inicial (productos completos) *';
 if(hint)hint.textContent=isEdit?'Al editar, el stock se introduce en unidades mínimas. No cambies factores sin conciliar existencias.':on?'El sistema convierte las cajas ingresadas a pastillas automáticamente.':'Cada producto completo equivale a 1 unidad de inventario.';
 const price=form.elements['precio_venta'];if(price){price.closest('.col-6, .col-md-3, div').hidden=on;price.disabled=on;}
}
checkbox.addEventListener('change',refresh);for(const x of fields.querySelectorAll('input'))x.addEventListener('input',refresh);refresh();
})();

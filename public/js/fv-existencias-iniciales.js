(() => {
 const toggle=document.getElementById('fv-initial-stock'),fields=document.getElementById('fv-initial-stock-fields'),newProvider=document.getElementById('fv-new-provider'),providerFields=document.getElementById('fv-new-provider-fields');
 if(!toggle||!fields||!newProvider||!providerFields)return;
 function refresh(){
  const active=toggle.checked,create=active&&newProvider.checked;fields.hidden=!active;
  fields.querySelectorAll('input,select').forEach(input=>{input.disabled=!active;input.required=false;});
  for(const name of ['stock','costo_inicial','lote_inicial','documento_inicial'])fields.querySelector(`[name="${name}"]`).required=active;
  const provider=fields.querySelector('[name="proveedor_id"]');provider.disabled=!active||create;provider.required=active&&!create;
  providerFields.hidden=!create;providerFields.querySelectorAll('input').forEach(input=>{input.disabled=!create;input.required=create&&input.name==='proveedor_nombre';});
 }
 toggle.addEventListener('change',refresh);newProvider.addEventListener('change',refresh);refresh();
})();

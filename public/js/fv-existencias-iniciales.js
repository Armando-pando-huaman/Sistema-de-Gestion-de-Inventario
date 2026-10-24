(() => {
 const toggle=document.getElementById('fv-initial-stock'),fields=document.getElementById('fv-initial-stock-fields'),newProvider=document.getElementById('fv-new-provider'),providerFields=document.getElementById('fv-new-provider-fields');
 if(!toggle||!fields||!newProvider||!providerFields)return;
 function refresh(){
  const active=toggle.checked,create=active&&newProvider.checked;fields.hidden=!active;
  fields.querySelectorAll('input,select').forEach(input=>{input.disabled=!active;input.required=false;});
  for(const name of ['costo_inicial','lote_inicial']){const input=document.querySelector(`[name="${name}"]`);if(input)input.required=active;}
  const documentInput=fields.querySelector('[name="documento_inicial"]');
  if(documentInput){documentInput.disabled=!create;documentInput.required=create;documentInput.closest('[data-provider-document]').hidden=!create;}
  const stock=document.querySelector('[name="stock"]');if(stock){stock.required=true;stock.min=active?'1':'0';}
  const provider=fields.querySelector('[name="proveedor_id"]');provider.disabled=!active||create;provider.required=active&&!create;
  providerFields.hidden=!create;providerFields.querySelectorAll('input').forEach(input=>{input.disabled=!create;input.required=create&&input.name==='proveedor_nombre';});
 }
 toggle.addEventListener('change',refresh);newProvider.addEventListener('change',refresh);refresh();
})();

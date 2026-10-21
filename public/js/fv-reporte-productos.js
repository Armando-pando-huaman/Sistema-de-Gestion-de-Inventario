(() => {
 'use strict';
 const canvas=document.getElementById('graficoProductosVendidos'),source=document.getElementById('fv-product-sales-data');
 if(!canvas||!source||typeof Chart==='undefined')return;
 const report=JSON.parse(source.textContent),rows=report.porciones,total=Number(report.total);
 if(!total)return;
 const number=new Intl.NumberFormat('es-PE'),percent=n=>(100*n/total).toFixed(1)+'%';
 const rgb=hex=>{const value=hex.trim().replace('#','');return [0,2,4].map(i=>parseInt(value.slice(i,i+2),16));};
 const mix=(hex,target,ratio)=>'#'+rgb(hex).map((v,i)=>Math.round(v*(1-ratio)+target[i]*ratio).toString(16).padStart(2,'0')).join('');
 function colors(){const style=getComputedStyle(document.documentElement),primary=style.getPropertyValue('--care-primary').trim();return [primary,style.getPropertyValue('--care-dark').trim(),style.getPropertyValue('--care-primary-2').trim(),mix(primary,[255,255,255],.3),mix(primary,[0,0,0],.3),mix(primary,[255,255,255],.6)];}
 const readable=hex=>rgb(hex).reduce((sum,v,i)=>sum+v*[.299,.587,.114][i],0)>155?'#152630':'#fff';
 const labels={id:'fvProductShareLabels',afterDraw(chart){
  const {ctx}=chart,meta=chart.getDatasetMeta(0);if(!meta.data.length)return;
  ctx.save();ctx.textAlign='center';ctx.textBaseline='middle';
  meta.data.forEach((arc,i)=>{
   const units=Number(rows[i].unidades);if(units/total<.04||arc.circumference<.12)return;
   const pos=arc.tooltipPosition();ctx.fillStyle=readable(chart.data.datasets[0].backgroundColor[i]);
   ctx.font='bold 11px system-ui';ctx.fillText(percent(units),pos.x,pos.y-6);
   ctx.font='10px system-ui';ctx.fillText(number.format(units),pos.x,pos.y+8);
  });
  const arc=meta.data[0],style=getComputedStyle(document.documentElement);
  ctx.fillStyle=style.getPropertyValue('--care-ink').trim();ctx.font=`bold ${Math.min(25,arc.innerRadius/3)}px system-ui`;
  ctx.fillText(number.format(total),arc.x,arc.y-8,Math.max(20,arc.innerRadius*1.7));
  ctx.font='12px system-ui';ctx.fillText('unidades',arc.x,arc.y+16);ctx.restore();
 }};
 const chart=new Chart(canvas,{type:'doughnut',data:{labels:rows.map(r=>r.nombre),datasets:[{data:rows.map(r=>Number(r.unidades)),backgroundColor:colors(),borderWidth:2,borderColor:getComputedStyle(document.documentElement).getPropertyValue('--bs-body-bg').trim()||'#fff',hoverOffset:7}]},plugins:[labels],options:{responsive:true,maintainAspectRatio:false,cutout:'52%',layout:{padding:12},plugins:{legend:{display:false},tooltip:{callbacks:{label:context=>`${number.format(context.parsed)} unidades · ${percent(context.parsed)}`,afterLabel:context=>'Ingresos: S/ '+Number(rows[context.dataIndex].ingresos).toFixed(2)}}}}});
 function refresh(){const palette=colors();chart.data.datasets[0].backgroundColor=palette;chart.data.datasets[0].borderColor=getComputedStyle(document.documentElement).getPropertyValue('--bs-body-bg').trim()||'#fff';document.querySelectorAll('[data-donut-key]').forEach(key=>{key.style.backgroundColor=palette[Number(key.dataset.donutKey)];});chart.update('none');}
 refresh();new MutationObserver(refresh).observe(document.documentElement,{attributes:true,attributeFilter:['data-fv-palette','data-fv-mode']});
 window.addEventListener('beforeprint',()=>chart.resize(320,300));window.addEventListener('afterprint',()=>chart.resize());
})();

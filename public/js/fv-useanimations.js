(()=>{'use strict';
const base='icons/useanimations/';
const map={
 'bi-grid-1x2':'home.json','bi-house':'home.json','bi-shop':'home.json',
 'bi-person-fill':'user.json','bi-person-circle':'user.json','bi-person-plus':'user.json','bi-person-dash':'user.json','bi-people':'users.json',
 'bi-calendar3':'calendar.json','bi-calendar-event':'calendar.json','bi-calendar-month':'calendar.json','bi-calendar-check':'calendar.json','bi-calendar-x':'calendar.json',
 'bi-search':'searchtox.json','bi-pencil':'edit.json','bi-pencil-square':'edit.json','bi-trash3':'trash.json','bi-printer':'printer.json','bi-save':'save.json',
 'bi-check-circle':'checkcircle.json','bi-check-lg':'checkcircle.json','bi-exclamation-triangle':'alerttriangle.json','bi-exclamation-circle':'alerttriangle.json',
 'bi-arrow-left':'arrowleft.json','bi-arrow-right':'arrowright.json','bi-arrow-up-right':'arrowright.json','bi-arrow-down-circle':'arrowdown.json',
 'bi-box-arrow-right':'arrowright.json','bi-box-arrow-in-down':'download.json','bi-box-arrow-up-right':'upload.json',
 'bi-chevron-down':'chevrondown.json','bi-chevron-right':'chevronright.json','bi-list':'menu.json','bi-list-ul':'menu.json',
 'bi-plus-circle':'pluscircle.json','bi-plus-lg':'pluscircle.json','bi-palette':'settings.json','bi-layout-sidebar':'settings.json','bi-layout-sidebar-inset':'settings.json','bi-layout-text-window':'settings.json',
 'bi-bar-chart':'barchart.json','bi-pie-chart':'barchart.json','bi-graph-up-arrow':'barchart.json','bi-clipboard-data':'barchart.json','bi-journal-text':'barchart.json',
 'bi-boxes':'package.json','bi-box-seam':'box.json','bi-inbox':'inbox.json','bi-capsule':'package.json','bi-capsule-pill':'package.json','bi-tags':'package.json',
 'bi-cart3':'shoppingcart.json','bi-cart-check':'shoppingcart.json','bi-cart-plus':'shoppingcart.json','bi-basket':'shoppingcart.json',
 'bi-clock':'clock.json','bi-clock-history':'clock.json','bi-activity':'activity.json','bi-arrow-left-right':'activity.json',
 'bi-wallet2':'creditcard.json','bi-credit-card':'creditcard.json','bi-cash-stack':'creditcard.json','bi-currency-exchange':'creditcard.json',
 'bi-eye':'visibility.json','bi-eye-slash':'visibility.json','bi-lock':'lock.json','bi-envelope':'mail.json','bi-sun':'sun.json','bi-moon-stars':'moon.json',
 'bi-shield-check':'shield.json','bi-question-circle':'help.json','bi-power':'power.json','bi-lightning-charge-fill':'zap.json','bi-stars':'star.json','bi-trophy':'star.json',
 'bi-heart-pulse':'heart.json','bi-whatsapp':'share.json','bi-x-circle':'xoctagon.json','bi-x-lg':'xoctagon.json','bi-upc-scan':'camera.json',
 'bi-filter':'filter.json','bi-stack':'package.json','bi-circle-fill':'activity.json'
};
const selector='body.fv-app i.bi:not(.fv-option-check):not(.fv-lord-fallback)';
function addHeadings(){const c=(new URLSearchParams(location.search).get('c')||'dashboard').toLowerCase();const classes={venta:'bi-cart-check',producto:'bi-box-seam',reporte:'bi-bar-chart',movimiento:'bi-activity',gestion:'bi-credit-card'};const cls=classes[c]||'bi-home';document.querySelectorAll('.op-heading h2,.op-panel>h3').forEach(h=>{if(h.querySelector('i,.fv-ua-icon'))return;const i=document.createElement('i');i.className='bi '+cls+' fv-auto-heading-icon';i.setAttribute('aria-hidden','true');h.prepend(i)})}
function enhance(icon){if(icon.closest('.fv-ua-icon,.fv-shortcut-icon'))return;const file=[...icon.classList].map(c=>map[c]).find(Boolean)||'activity.json';const wrap=document.createElement('span');wrap.className='fv-ua-icon';wrap.setAttribute('aria-hidden','true');icon.before(wrap);wrap.appendChild(icon);const player=document.createElement('span');player.className='fv-ua-player';wrap.appendChild(player);const target=wrap.closest('a,button,h2,h3,h5,article,label')||wrap;const animation=window.lottie.loadAnimation({container:player,renderer:'svg',loop:false,autoplay:false,path:base+file,rendererSettings:{preserveAspectRatio:'xMidYMid meet'}});animation.addEventListener('DOMLoaded',()=>{animation.goToAndStop(0,true);wrap.classList.add('is-ready')});animation.addEventListener('data_failed',()=>{icon.classList.add('fv-ua-failed')});const play=()=>animation.goToAndPlay(0,true);target.addEventListener('mouseenter',play);target.addEventListener('focusin',play)}
function init(){if(!window.lottie){document.documentElement.classList.remove('fv-animated-icons');return;}addHeadings();document.querySelectorAll(selector).forEach(enhance)}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
})();

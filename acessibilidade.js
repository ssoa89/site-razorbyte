(function(){
  'use strict';
  var root=document.documentElement;
  var defaults={contrast:false,textSize:0,underline:false,highlight:false,motion:false,spacing:false,theme:'system'};
  var preferences=Object.assign({},defaults);
  try{
    var stored=JSON.parse(localStorage.getItem('preferencias-acessibilidade')||'{}');
    preferences=Object.assign(preferences,stored);
    if(typeof stored.large==='boolean'&&stored.textSize===undefined)preferences.textSize=stored.large?1:0;
  }catch(e){}
  preferences.textSize=Math.max(-2,Math.min(4,Number(preferences.textSize)||0));
  if(preferences.theme==='default')preferences.theme='system';
  if(!['system','light','dark'].includes(preferences.theme))preferences.theme='system';

  var widget=document.createElement('div');
  var isRazorbyte=!!document.querySelector('.site-header');
  root.classList.add(isRazorbyte?'a11y-site-razorbyte':'a11y-site-laco');
  widget.className='a11y-widget '+(isRazorbyte?'a11y-brand-razorbyte':'a11y-brand-laco');
  widget.innerHTML=
    '<section class="a11y-panel" id="a11y-panel" aria-labelledby="a11y-title" aria-describedby="a11y-description" hidden>'+ 
      '<div class="a11y-panel-head"><h2 id="a11y-title">Acessibilidade</h2><button type="button" class="a11y-close" aria-label="Fechar painel de acessibilidade">×</button></div>'+ 
      '<p id="a11y-description">Personalize a visualização. As preferências ficam salvas neste dispositivo.</p>'+ 
      '<div class="a11y-size-row"><span>Tamanho do texto</span><div class="a11y-size-actions"><button type="button" data-size="-1" aria-label="Diminuir texto">A−</button><output class="a11y-size-value" id="a11y-size-value" aria-live="polite">100%</output><button type="button" data-size="1" aria-label="Aumentar texto">A+</button></div></div>'+ 
      '<label for="a11y-theme">Tema de cores</label><select id="a11y-theme"><option value="system">Preferência do dispositivo</option><option value="light">Claro</option><option value="dark">Escuro</option></select>'+ 
      '<p class="a11y-status" id="a11y-theme-description">Usa a preferência de aparência configurada no dispositivo.</p>'+ 
      '<button type="button" class="a11y-option" data-pref="contrast" aria-pressed="false"><span>Alto contraste</span><span class="a11y-state">Desativado</span></button>'+ 
      '<button type="button" class="a11y-option" data-pref="highlight" aria-pressed="false"><span>Destacar links</span><span class="a11y-state">Desativado</span></button>'+ 
      '<button type="button" class="a11y-option" data-pref="motion" aria-pressed="false"><span>Reduzir animações</span><span class="a11y-state">Desativado</span></button>'+ 
      '<button type="button" class="a11y-option" data-pref="spacing" aria-pressed="false"><span>Aumentar espaçamento do texto</span><span class="a11y-state">Desativado</span></button>'+ 
      '<button type="button" class="a11y-reset">Restaurar padrão</button><p class="a11y-status" id="a11y-status" aria-live="polite"></p></section>'+ 
    '<button type="button" class="a11y-trigger" aria-expanded="false" aria-controls="a11y-panel" aria-label="Abrir opções de acessibilidade">'+
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="7" r="1"/><path d="M12 11v6m-4-6 4 2 4-2m-7 8 3-4 3 4"/></svg><span>Acessibilidade</span></button>';
  document.body.appendChild(widget);

  var trigger=widget.querySelector('.a11y-trigger'),panel=widget.querySelector('.a11y-panel');
  var status=widget.querySelector('#a11y-status');
  var colorScheme=window.matchMedia('(prefers-color-scheme: dark)');
  var scaledText=[];
  function save(){try{localStorage.setItem('preferencias-acessibilidade',JSON.stringify(preferences))}catch(e){}}
  function announce(message){status.textContent=message}
  function darkThemeActive(){return preferences.theme==='dark'||(preferences.theme==='system'&&colorScheme.matches)}
  function applyTextScale(){
    scaledText.forEach(function(item){
      if(item.inlineValue)item.element.style.setProperty('font-size',item.inlineValue,item.inlinePriority);
      else item.element.style.removeProperty('font-size');
    });
    scaledText=[];
    var scale=1+preferences.textSize*.1;
    if(scale===1)return;
    var walker=document.createTreeWalker(document.body,NodeFilter.SHOW_TEXT),node,items=[],seen=new Set();
    while((node=walker.nextNode())){
      var element=node.parentElement;
      if(!element||widget.contains(element)||!node.nodeValue.trim()||seen.has(element)||!element.getClientRects().length)continue;
      seen.add(element);
      items.push({element:element,baseSize:parseFloat(getComputedStyle(element).fontSize)||16,inlineValue:element.style.getPropertyValue('font-size'),inlinePriority:element.style.getPropertyPriority('font-size')});
    }
    document.querySelectorAll('input,textarea,select').forEach(function(element){
      if(widget.contains(element)||seen.has(element)||!element.getClientRects().length)return;
      seen.add(element);
      items.push({element:element,baseSize:parseFloat(getComputedStyle(element).fontSize)||16,inlineValue:element.style.getPropertyValue('font-size'),inlinePriority:element.style.getPropertyPriority('font-size')});
    });
    items.forEach(function(item){item.element.style.setProperty('font-size',(item.baseSize*scale)+'px');scaledText.push(item)});
  }
  function apply(){
    root.classList.toggle('a11y-high-contrast',!!preferences.contrast);
    root.classList.toggle('a11y-large-text',preferences.textSize>0);
    root.classList.toggle('a11y-underline',!!preferences.underline);
    root.classList.toggle('a11y-highlight-links',!!preferences.highlight);
    root.classList.toggle('a11y-reduced-motion',!!preferences.motion);
    root.classList.toggle('a11y-extra-spacing',!!preferences.spacing);
    var dark=darkThemeActive();
    root.classList.toggle('a11y-theme-dark',dark);
    root.classList.toggle('a11y-theme-light',!dark);
    applyTextScale();
    widget.querySelector('#a11y-size-value').value=Math.round((1+preferences.textSize*.1)*100)+'%';
    widget.querySelector('[data-size="-1"]').disabled=preferences.textSize<=-2;
    widget.querySelector('[data-size="1"]').disabled=preferences.textSize>=4;
    widget.querySelector('#a11y-theme').value=preferences.theme;
    widget.querySelectorAll('[data-pref]').forEach(function(button){
      var active=!!preferences[button.dataset.pref];
      button.setAttribute('aria-pressed',String(active));
      button.querySelector('.a11y-state').textContent=active?'Ativado':'Desativado';
    });
    widget.querySelector('#a11y-theme-description').textContent=preferences.theme==='system'?'Usa a preferência de aparência configurada no dispositivo.':(preferences.theme==='dark'?'Tema escuro ativado com as cores de identidade do projeto.':'Tema claro ativado com as cores de identidade do projeto.');
    document.dispatchEvent(new CustomEvent('a11ypreferencechange',{detail:Object.assign({},preferences,{large:preferences.textSize>0})}));
  }
  function close(restoreFocus){panel.hidden=true;trigger.setAttribute('aria-expanded','false');trigger.setAttribute('aria-label','Abrir opções de acessibilidade');if(restoreFocus)trigger.focus()}
  function open(){panel.hidden=false;trigger.setAttribute('aria-expanded','true');trigger.setAttribute('aria-label','Fechar opções de acessibilidade');widget.querySelector('.a11y-close').focus()}
  trigger.addEventListener('click',function(){if(panel.hidden)open();else close(false)});
  widget.querySelector('.a11y-close').addEventListener('click',function(){close(true)});
  widget.querySelectorAll('[data-size]').forEach(function(button){button.addEventListener('click',function(){
    var next=Math.max(-2,Math.min(4,preferences.textSize+Number(button.dataset.size)));
    if(next===preferences.textSize){announce(next===4?'Tamanho máximo atingido.':'Tamanho mínimo atingido.');return}
    preferences.textSize=next;apply();save();announce('Tamanho do texto atualizado.');
  })});
  widget.querySelector('#a11y-theme').addEventListener('change',function(event){preferences.theme=event.target.value;apply();save();announce(preferences.theme==='system'?'Preferência do dispositivo ativada.':(preferences.theme==='dark'?'Modo escuro ativado.':'Modo claro ativado.'))});
  widget.querySelectorAll('[data-pref]').forEach(function(button){button.addEventListener('click',function(){var key=button.dataset.pref;preferences[key]=!preferences[key];apply();save();announce(button.querySelector('span').textContent+' '+(preferences[key]?'ativado.':'desativado.'))})});
  widget.querySelector('.a11y-reset').addEventListener('click',function(){preferences=Object.assign({},defaults);apply();save();announce('Configurações restauradas.');close(true)});
  document.addEventListener('keydown',function(event){if(event.key==='Escape'&&!panel.hidden)close(true)});
  document.addEventListener('click',function(event){if(!panel.hidden&&!widget.contains(event.target))close(false)});
  if(colorScheme.addEventListener)colorScheme.addEventListener('change',function(){if(preferences.theme==='system')apply()});
  else if(colorScheme.addListener)colorScheme.addListener(function(){if(preferences.theme==='system')apply()});
  window.addEventListener('resize',function(){if(preferences.textSize)applyTextScale()});
  if('MutationObserver' in window)new MutationObserver(function(){if(preferences.textSize)applyTextScale()}).observe(document.body,{childList:true,subtree:true});
  apply();
})();

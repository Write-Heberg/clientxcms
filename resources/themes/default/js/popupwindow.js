/**
 * Custom Element - PopupWindow
 * @author CLIENTXCMS
 * @date 29-03-21
 * @example <a href="link" id="email-1" width="650" height="450" is="popup-window"></a>
 *
 */
!function(e,t){"use strict";if(t.get("li-li"))return;try{class l extends HTMLLIElement{}if(t.define("li-li",l,{extends:"li"}),!/is="li-li"/.test((new l).outerHTML))throw{}}catch(l){const n="attributeChangedCallback",s="connectedCallback",r="disconnectedCallback",{assign:i,create:o,defineProperties:a,setPrototypeOf:u}=Object,{define:c,get:d,upgrade:f,whenDefined:b}=t,g=o(null),h=e=>{for(let t=0,{length:l}=e;t<l;t++){const{attributeName:l,oldValue:s,target:r}=e[t],i=r.getAttribute(l);n in r&&(s!=i||null!=i)&&r[n](l,s,r.getAttribute(l),null)}},p=e=>{let t=e.getAttribute("is");return t&&(t=t.toLowerCase())in g?g[t]:null},v=(e,t)=>{const{Class:l}=t,n=l.observedAttributes||[];if(u(e,l.prototype),n.length){new MutationObserver(h).observe(e,{attributes:!0,attributeFilter:n,attributeOldValue:!0});const t=[];for(let l=0,{length:s}=n;l<s;l++)t.push({attributeName:n[l],oldValue:null,target:e});h(t)}},C=(e,t)=>{const l=e.querySelectorAll("[is]");for(let e=0,{length:n}=l;e<n;e++)t(l[e])},m=e=>{if(1!==e.nodeType)return;C(e,m);const t=p(e);t&&(e instanceof t.Class||v(e,t),s in e&&e[s]())},w=e=>{if(1!==e.nodeType)return;C(e,w);const t=p(e);t&&e instanceof t.Class&&r in e&&e[r]()};new MutationObserver(e=>{for(let t=0,{length:l}=e;t<l;t++){const{addedNodes:l,removedNodes:n}=e[t];for(let e=0,{length:t}=l;e<t;e++)m(l[e]);for(let e=0,{length:t}=n;e<t;e++)w(n[e])}}).observe(e,{childList:!0,subtree:!0}),a(t,{define:{value(l,n,s){if(l=l.toLowerCase(),s&&"extends"in s){g[l]=i({},s,{Class:n});const t=s.extends+'[is="'+l+'"]',r=e.querySelectorAll(t);for(let e=0,{length:t}=r;e<t;e++)m(r[e])}else c.apply(t,arguments)}},get:{value:e=>e in g?g[e].Class:d.call(t,e)},upgrade:{value(e){const l=p(e);!l||e instanceof l.Class?f.call(t,e):v(e,l)}},whenDefined:{value:e=>e in g?Promise.resolve():b.call(t,e)}});const{createElement:y}=e;a(e,{createElement:{value(l,n){const s=y.call(e,l);return n&&"is"in n&&(s.setAttribute("is",n.is),t.upgrade(s)),s}}})}}(document,customElements);

class PopupWindow extends HTMLAnchorElement {

    connectedCallback() {
        this.addEventListener("click", this.onClickEvent.bind(this))
    }

    disconnectedCallback() {
        this.removeEventListener("click")
    }

    onClickEvent(e) {
        e.preventDefault()
        this.open(this.href)
    }

    open(href) {

        var popupWidth = this.width || 640;
        var popupHeight = this.height || 320;
        var windowLeft = window.screenLeft || window.screenX;
        var windowTop = window.screenTop || window.screenY;
        var windowWidth = window.innerWidth || document.documentElement.clientWidth;
        var windowHeight = window.innerHeight || document.documentElement.clientHeight;
        var popupLeft = windowLeft + windowWidth / 2 - popupWidth / 2 ;
        var popupTop = windowTop + windowHeight / 2 - popupHeight / 2;
        window.open(href, this.id, 'scrollbars=yes, width=' + popupWidth + ', height=' + popupHeight + ', top=' + popupTop + ', left=' + popupLeft).focus()
    }
}
customElements.define("popup-window", PopupWindow, { extends: 'a' })

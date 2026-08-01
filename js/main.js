/* ── IziToPop Main JS ── */

const GAMES = [
  {id:1,name:"Free Fire",currency:"Dyaman",icon:"🔥",badge:"hot",color:"#FF6B35",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co1rgi.jpg",
   packages:[
    {amount:"100",unit:"Dyaman",price:"$0.99",htg:130,best:false},
    {amount:"310",unit:"Dyaman",price:"$2.99",htg:390,best:false},
    {amount:"520",unit:"Dyaman",price:"$4.99",htg:650,best:true},
    {amount:"1060",unit:"Dyaman",price:"$9.99",htg:1300,best:false},
    {amount:"2180",unit:"Dyaman",price:"$19.99",htg:2600,best:false},
    {amount:"5600",unit:"Dyaman",price:"$49.99",htg:6500,best:false},
  ],uid:"ID jwèt",uidHelp:"Egzanp: 123456789"},
  {id:2,name:"PUBG Mobile",currency:"UC",icon:"🎯",badge:"",color:"#F59E0B",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co1wkb.jpg",
   packages:[
    {amount:"60",unit:"UC",price:"$0.99",htg:130,best:false},
    {amount:"300",unit:"UC",price:"$4.99",htg:650,best:false},
    {amount:"600",unit:"UC",price:"$9.99",htg:1300,best:true},
    {amount:"1500",unit:"UC",price:"$24.99",htg:3250,best:false},
    {amount:"3000",unit:"UC",price:"$49.99",htg:6500,best:false},
  ],uid:"Player ID",uidHelp:"Egzanp: 5123456789"},
  {id:3,name:"Clash of Clans",currency:"Gold",icon:"🏰",badge:"new",color:"#10B981",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co1ycw.jpg",
   packages:[
    {amount:"$5",unit:"Pack",price:"$5.99",htg:780,best:false},
    {amount:"$10",unit:"Pack",price:"$10.99",htg:1430,best:true},
    {amount:"$20",unit:"Pack",price:"$20.99",htg:2730,best:false},
  ],uid:"Email Supercell",uidHelp:"Email kont Supercell ou"},
  {id:4,name:"Fortnite",currency:"V-Bucks",icon:"⚡",badge:"",color:"#6C63FF",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co3wk8.jpg",
   packages:[
    {amount:"1000",unit:"V-Bucks",price:"$7.99",htg:1040,best:false},
    {amount:"2800",unit:"V-Bucks",price:"$19.99",htg:2600,best:true},
    {amount:"5000",unit:"V-Bucks",price:"$31.99",htg:4160,best:false},
    {amount:"13500",unit:"V-Bucks",price:"$79.99",htg:10400,best:false},
  ],uid:"Epic Games ID",uidHelp:"Username Epic Games ou"},
  {id:5,name:"Mobile Legends",currency:"Dyaman",icon:"🌀",badge:"",color:"#3B82F6",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co20ke.jpg",
   packages:[
    {amount:"28",unit:"Dyaman",price:"$0.49",htg:65,best:false},
    {amount:"100",unit:"Dyaman",price:"$1.49",htg:195,best:false},
    {amount:"250",unit:"Dyaman",price:"$3.49",htg:455,best:true},
    {amount:"500",unit:"Dyaman",price:"$6.49",htg:845,best:false},
    {amount:"1000",unit:"Dyaman",price:"$12.99",htg:1690,best:false},
  ],uid:"User ID + Zone ID",uidHelp:"Egzanp: 12345678 (9876)"},
  {id:6,name:"Call of Duty",currency:"CP",icon:"🔫",badge:"",color:"#EF4444",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co5voi.jpg",
   packages:[
    {amount:"80",unit:"CP",price:"$1.09",htg:142,best:false},
    {amount:"400",unit:"CP",price:"$4.99",htg:650,best:true},
    {amount:"800",unit:"CP",price:"$9.99",htg:1300,best:false},
    {amount:"2000",unit:"CP",price:"$24.99",htg:3250,best:false},
  ],uid:"Activision ID",uidHelp:"Username#12345"},
  {id:7,name:"Steam",currency:"Gift Cards",icon:"🎮",badge:"",color:"#1B2838",
   img:"https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Steam_icon_logo.svg/512px-Steam_icon_logo.svg.png",
   packages:[
    {amount:"$5",unit:"Gift Card",price:"$5.99",htg:780,best:false},
    {amount:"$10",unit:"Gift Card",price:"$10.99",htg:1430,best:true},
    {amount:"$20",unit:"Gift Card",price:"$20.99",htg:2730,best:false},
    {amount:"$50",unit:"Gift Card",price:"$51.99",htg:6760,best:false},
  ],uid:"Email ou",uidHelp:"Kòd voye nan email ou dirèkteman"},
  {id:8,name:"PlayStation",currency:"Gift Cards",icon:"🕹️",badge:"",color:"#003791",
   img:"https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Playstation_logo_colour.svg/512px-Playstation_logo_colour.svg.png",
   packages:[
    {amount:"$10",unit:"PSN Card",price:"$10.99",htg:1430,best:false},
    {amount:"$25",unit:"PSN Card",price:"$25.99",htg:3380,best:true},
    {amount:"$50",unit:"PSN Card",price:"$50.99",htg:6630,best:false},
  ],uid:"Email ou",uidHelp:"Kòd voye nan email ou dirèkteman"},
];

let selectedGame = null;
let selectedPkg = null;

/* ── RENDER GAMES ── */
function renderGames(filter="all") {
  const grid = document.getElementById("gamesGrid");
  if(!grid) return;
  const list = filter==="all" ? GAMES : GAMES.filter(g=>g.currency===filter||g.name.toLowerCase().includes(filter));
  grid.innerHTML = list.map(g=>`
    <div class="game-card" onclick="openProduct(${g.id})">
      <div class="game-thumb">
        <img src="${g.img}" alt="${g.name}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" loading="lazy">
        <div class="game-thumb-placeholder" style="display:none">${g.icon}</div>
        ${g.badge?`<span class="game-badge badge-${g.badge}">${g.badge.toUpperCase()}</span>`:""}
      </div>
      <div class="game-info">
        <div class="game-name">${g.name}</div>
        <div class="game-currency">${g.currency}</div>
      </div>
    </div>`).join("");
}

/* ── CATEGORY FILTER ── */
function filterCat(cat,el) {
  document.querySelectorAll(".cat-pill").forEach(p=>p.classList.remove("active"));
  el.classList.add("active");
  renderGames(cat);
}

/* ── PRODUCT MODAL ── */
function openProduct(id) {
  selectedGame = GAMES.find(g=>g.id===id);
  selectedPkg = null;
  const m = document.getElementById("productModal");

  // header
  const coverEl = document.getElementById("productCover");
  coverEl.innerHTML = `<img src="${selectedGame.img}" alt="${selectedGame.name}" onerror="this.parentElement.innerHTML='${selectedGame.icon}'">`;
  document.getElementById("productName").textContent = selectedGame.name;
  document.getElementById("productSub").textContent = selectedGame.currency + " · Livrezon otomatik ⚡";

  // uid
  document.getElementById("uidLabel").textContent = selectedGame.uid;
  document.getElementById("uidInput").placeholder = selectedGame.uidHelp;
  document.getElementById("uidInput").value = "";

  // packages
  document.getElementById("packagesGrid").innerHTML = selectedGame.packages.map((p,i)=>`
    <div class="pkg" id="pkg${i}" onclick="selectPkg(${i})">
      ${p.best?'<div class="pkg-best">PI BON VALÈ</div>':""}
      <div class="pkg-diamonds"><span class="diamond-icon">💎</span> ${p.amount}</div>
      <div class="pkg-unit" style="font-size:0.7rem;color:var(--muted);margin-bottom:0.2rem">${p.unit}</div>
      <div class="pkg-price">${p.price} <span style="color:var(--muted2)">· ${p.htg} HTG</span></div>
    </div>`).join("");

  updateSummary();
  m.classList.add("open");
  document.body.style.overflow="hidden";
}

function selectPkg(i) {
  document.querySelectorAll(".pkg").forEach(p=>p.classList.remove("selected"));
  document.getElementById("pkg"+i)?.classList.add("selected");
  selectedPkg = selectedGame.packages[i];
  updateSummary();
}

function updateSummary() {
  const s = document.getElementById("orderSummaryCard");
  if(!selectedPkg){s.style.display="none";return;}
  s.style.display="block";
  document.getElementById("sumGame").textContent = selectedGame.name;
  document.getElementById("sumPkg").textContent = selectedPkg.amount+" "+selectedPkg.unit;
  document.getElementById("sumPrice").textContent = selectedPkg.price+" ("+selectedPkg.htg+" HTG)";
  document.getElementById("sumTotal").textContent = selectedPkg.price;
}

function proceedCheckout() {
  if(!selectedPkg){showToast("⚠️","Chwazi yon pake anvan");return;}
  const uid = document.getElementById("uidInput").value.trim();
  if(!uid){showToast("⚠️","Antre "+selectedGame.uid+" ou");document.getElementById("uidInput").focus();return;}
  closeModal("productModal");
  openCheckout();
}

/* ── CHECKOUT ── */
function openCheckout() {
  const m = document.getElementById("checkoutModal");
  document.getElementById("checkSumGame").textContent = selectedGame.name;
  document.getElementById("checkSumPkg").textContent = selectedPkg.amount+" "+selectedPkg.unit;
  document.getElementById("checkSumTotal").textContent = selectedPkg.price+" ("+selectedPkg.htg+" HTG)";
  document.getElementById("checkSumMethod").textContent = "—";
  setPayTab("moncash");
  m.classList.add("open");
  document.body.style.overflow="hidden";
}

function setPayTab(method) {
  document.querySelectorAll(".pay-tab").forEach(t=>t.classList.toggle("active",t.dataset.method===method));
  document.getElementById("checkSumMethod").textContent = method==="moncash"?"MonCash":method==="natcash"?"NatCash":"Kàt";
  const f = document.getElementById("payFields");
  if(method==="moncash"||method==="natcash") {
    f.innerHTML=`<div class="form-group"><label class="form-label">Nimewo ${method==="moncash"?"MonCash":"NatCash"}</label><input class="form-input" type="tel" placeholder="+509 XX XX XXXX"></div><p style="font-size:0.75rem;color:var(--muted);line-height:1.6;margin-top:0.25rem">Ou ap resevwa yon Push Notification pou konfime peman an.</p>`;
  } else {
    f.innerHTML=`<div class="form-group"><label class="form-label">Nimewo kàt</label><input class="form-input" type="text" placeholder="1234 5678 9012 3456" maxlength="19"></div><div class="form-row"><div class="form-group"><label class="form-label">Ekspirasyon</label><input class="form-input" type="text" placeholder="MM/YY" maxlength="5"></div><div class="form-group"><label class="form-label">CVV</label><input class="form-input" type="text" placeholder="123" maxlength="4"></div></div>`;
  }
}

function confirmPay() {
  const btn=document.getElementById("payBtn");
  btn.disabled=true; btn.textContent="Ap trete...";
  setTimeout(()=>{closeModal("checkoutModal");openSuccess();},2000);
}

/* ── SUCCESS ── */
function openSuccess() {
  const code = genCode();
  document.getElementById("successGame").textContent = selectedGame.name+" · "+selectedPkg.amount+" "+selectedPkg.unit;
  document.getElementById("successCode").textContent = code;
  document.getElementById("successPrice").textContent = selectedPkg.price+" ("+selectedPkg.htg+" HTG)";
  document.getElementById("successMethod").textContent = document.querySelector(".pay-tab.active")?.dataset.method==="moncash"?"MonCash":document.querySelector(".pay-tab.active")?.dataset.method==="natcash"?"NatCash":"Kàt";
  document.getElementById("successDate").textContent = new Date().toLocaleDateString("fr-HT",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"});
  document.getElementById("successModal").classList.add("open");
  document.body.style.overflow="hidden";
  launchConfetti();
}

function genCode() {
  const c="ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
  const s=()=>Array.from({length:4},()=>c[Math.floor(Math.random()*c.length)]).join("");
  return `${s()}-${s()}-${s()}-${s()}`;
}

function copyCode() {
  navigator.clipboard?.writeText(document.getElementById("successCode").textContent)
    .then(()=>showToast("✅","Kòd la kopye!"))
    .catch(()=>showToast("📋","Kopye kòd la manyèlman"));
}

function launchConfetti() {
  const emojis=["🎉","✨","🎮","💎","⚡","🌟"];
  const style=document.createElement("style");
  style.textContent="@keyframes cfetti{to{transform:translateY(110vh) rotate(720deg);opacity:0}}";
  document.head.appendChild(style);
  for(let i=0;i<20;i++){
    const e=document.createElement("div");
    e.textContent=emojis[Math.floor(Math.random()*emojis.length)];
    e.style.cssText=`position:fixed;top:-30px;left:${Math.random()*100}vw;font-size:${1+Math.random()*1.2}rem;pointer-events:none;z-index:9999;animation:cfetti ${1.5+Math.random()*2}s ease-in forwards`;
    document.body.appendChild(e);
    setTimeout(()=>e.remove(),4000);
  }
}

/* ── AUTH ── */
function openLogin(){closeAllModals();document.getElementById("loginModal").classList.add("open");document.body.style.overflow="hidden";}
function openRegister(){closeAllModals();document.getElementById("registerModal").classList.add("open");document.body.style.overflow="hidden";}
function handleLogin(){closeModal("loginModal");showToast("✅","Byenvini! Ou konekte.");}
function handleRegister(){closeModal("registerModal");showToast("🎉","Kont kreye! Byenvini sou IziToPop.");}

/* ── UTILS ── */
function closeAllModals(){document.querySelectorAll(".modal-overlay.open").forEach(m=>{m.classList.remove("open");});}
function closeModal(id){document.getElementById(id)?.classList.remove("open");document.body.style.overflow="";const b=document.getElementById("payBtn");if(b){b.disabled=false;b.textContent="💳 Peye kounye a";}}
function showToast(icon,msg){const t=document.getElementById("toast");document.getElementById("toastIcon").textContent=icon;document.getElementById("toastText").textContent=msg;t.classList.add("show");setTimeout(()=>t.classList.remove("show"),3000);}
function toggleMenu(){document.getElementById("mobileMenu")?.classList.toggle("open");}

document.addEventListener("click",e=>{if(e.target.classList.contains("modal-overlay"))closeModal(e.target.id);});
document.addEventListener("DOMContentLoaded",()=>renderGames());

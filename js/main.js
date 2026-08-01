/* ── IziToPop Main JS ── */

const GAMES = [
  {id:1,name:"Free Fire",currency:"Dyaman",icon:"🔥",badge:"hot",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co1rgi.jpg",
   packages:[
    {id:1,amount:"100",unit:"Dyaman",price:"$0.99",htg:130,best:false},
    {id:2,amount:"310",unit:"Dyaman",price:"$2.99",htg:390,best:false},
    {id:3,amount:"520",unit:"Dyaman",price:"$4.99",htg:650,best:true},
    {id:4,amount:"1060",unit:"Dyaman",price:"$9.99",htg:1300,best:false},
    {id:5,amount:"2180",unit:"Dyaman",price:"$19.99",htg:2600,best:false},
    {id:6,amount:"5600",unit:"Dyaman",price:"$49.99",htg:6500,best:false},
  ],uid:"ID jwèt",uidHelp:"Egzanp: 123456789"},
  {id:2,name:"PUBG Mobile",currency:"UC",icon:"🎯",badge:"",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co1wkb.jpg",
   packages:[
    {id:7,amount:"60",unit:"UC",price:"$0.99",htg:130,best:false},
    {id:8,amount:"300",unit:"UC",price:"$4.99",htg:650,best:false},
    {id:9,amount:"600",unit:"UC",price:"$9.99",htg:1300,best:true},
    {id:10,amount:"1500",unit:"UC",price:"$24.99",htg:3250,best:false},
    {id:11,amount:"3000",unit:"UC",price:"$49.99",htg:6500,best:false},
  ],uid:"Player ID",uidHelp:"Egzanp: 5123456789"},
  {id:3,name:"Clash of Clans",currency:"Gold",icon:"🏰",badge:"new",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co1ycw.jpg",
   packages:[
    {id:12,amount:"$5",unit:"Pack",price:"$5.99",htg:780,best:false},
    {id:13,amount:"$10",unit:"Pack",price:"$10.99",htg:1430,best:true},
    {id:14,amount:"$20",unit:"Pack",price:"$20.99",htg:2730,best:false},
  ],uid:"Email Supercell",uidHelp:"Email kont Supercell ou"},
  {id:4,name:"Fortnite",currency:"V-Bucks",icon:"⚡",badge:"",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co3wk8.jpg",
   packages:[
    {id:15,amount:"1000",unit:"V-Bucks",price:"$7.99",htg:1040,best:false},
    {id:16,amount:"2800",unit:"V-Bucks",price:"$19.99",htg:2600,best:true},
    {id:17,amount:"5000",unit:"V-Bucks",price:"$31.99",htg:4160,best:false},
    {id:18,amount:"13500",unit:"V-Bucks",price:"$79.99",htg:10400,best:false},
  ],uid:"Epic Games ID",uidHelp:"Username Epic Games ou"},
  {id:5,name:"Mobile Legends",currency:"Dyaman",icon:"🌀",badge:"",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co20ke.jpg",
   packages:[
    {id:19,amount:"28",unit:"Dyaman",price:"$0.49",htg:65,best:false},
    {id:20,amount:"100",unit:"Dyaman",price:"$1.49",htg:195,best:false},
    {id:21,amount:"250",unit:"Dyaman",price:"$3.49",htg:455,best:true},
    {id:22,amount:"500",unit:"Dyaman",price:"$6.49",htg:845,best:false},
    {id:23,amount:"1000",unit:"Dyaman",price:"$12.99",htg:1690,best:false},
  ],uid:"User ID + Zone ID",uidHelp:"Egzanp: 12345678 (9876)"},
  {id:6,name:"Call of Duty",currency:"CP",icon:"🔫",badge:"",
   img:"https://images.igdb.com/igdb/image/upload/t_cover_big/co5voi.jpg",
   packages:[
    {id:24,amount:"80",unit:"CP",price:"$1.09",htg:142,best:false},
    {id:25,amount:"400",unit:"CP",price:"$4.99",htg:650,best:true},
    {id:26,amount:"800",unit:"CP",price:"$9.99",htg:1300,best:false},
    {id:27,amount:"2000",unit:"CP",price:"$24.99",htg:3250,best:false},
  ],uid:"Activision ID",uidHelp:"Username#12345"},
  {id:7,name:"Steam",currency:"Gift Cards",icon:"🎮",badge:"",
   img:"https://upload.wikimedia.org/wikipedia/commons/thumb/8/83/Steam_icon_logo.svg/512px-Steam_icon_logo.svg.png",
   packages:[
    {id:28,amount:"$5",unit:"Gift Card",price:"$5.99",htg:780,best:false},
    {id:29,amount:"$10",unit:"Gift Card",price:"$10.99",htg:1430,best:true},
    {id:30,amount:"$20",unit:"Gift Card",price:"$20.99",htg:2730,best:false},
    {id:31,amount:"$50",unit:"Gift Card",price:"$51.99",htg:6760,best:false},
  ],uid:"Email ou",uidHelp:"Kòd voye nan email ou dirèkteman"},
  {id:8,name:"PlayStation",currency:"Gift Cards",icon:"🕹️",badge:"",
   img:"https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Playstation_logo_colour.svg/512px-Playstation_logo_colour.svg.png",
   packages:[
    {id:32,amount:"$10",unit:"PSN Card",price:"$10.99",htg:1430,best:false},
    {id:33,amount:"$25",unit:"PSN Card",price:"$25.99",htg:3380,best:true},
    {id:34,amount:"$50",unit:"PSN Card",price:"$50.99",htg:6630,best:false},
  ],uid:"Email ou",uidHelp:"Kòd voye nan email ou dirèkteman"},
];

let selectedGame = null;
let selectedPkg  = null;

/* ── RENDER GAMES ── */
function renderGames(filter="all") {
  const grid = document.getElementById("gamesGrid");
  if(!grid) return;
  const list = filter==="all" ? GAMES : GAMES.filter(g=>g.currency===filter||g.name.toLowerCase().includes(filter.toLowerCase()));
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
  if(!selectedGame) return;
  selectedPkg = null;

  const coverEl = document.getElementById("productCover");
  coverEl.innerHTML = `<img src="${selectedGame.img}" alt="${selectedGame.name}" onerror="this.parentElement.innerHTML='${selectedGame.icon}'">`;
  document.getElementById("productName").textContent = selectedGame.name;
  document.getElementById("productSub").textContent  = selectedGame.currency + " · Livrezon otomatik ⚡";
  document.getElementById("uidLabel").textContent    = selectedGame.uid;
  document.getElementById("uidInput").placeholder    = selectedGame.uidHelp;
  document.getElementById("uidInput").value          = "";

  document.getElementById("packagesGrid").innerHTML = selectedGame.packages.map((p,i)=>`
    <div class="pkg" id="pkg${i}" onclick="selectPkg(${i})">
      ${p.best?'<div class="pkg-best">PI BON VALÈ</div>':""}
      <div class="pkg-diamonds"><span class="diamond-icon">💎</span> ${p.amount}</div>
      <div style="font-size:0.7rem;color:var(--muted);margin-bottom:0.2rem">${p.unit}</div>
      <div class="pkg-price">${p.price} <span style="color:var(--muted2)">· ${p.htg} HTG</span></div>
    </div>`).join("");

  updateSummary();
  document.getElementById("productModal").classList.add("open");
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
  document.getElementById("sumGame").textContent  = selectedGame.name;
  document.getElementById("sumPkg").textContent   = selectedPkg.amount+" "+selectedPkg.unit;
  document.getElementById("sumTotal").textContent = selectedPkg.price+" ("+selectedPkg.htg+" HTG)";
}

function proceedCheckout() {
  if(!selectedPkg){showToast("⚠️","Chwazi yon pake anvan");return;}
  const uid = document.getElementById("uidInput").value.trim();
  if(!uid){showToast("⚠️","Antre "+selectedGame.uid+" ou");document.getElementById("uidInput").focus();return;}

  // Check login — if not logged in open login modal first
  const token = localStorage.getItem("izitopop_token");
  if(!token){
    closeModal("productModal");
    showToast("⚠️","Konekte anvan pou achte");
    setTimeout(()=>openLogin(),400);
    return;
  }

  closeModal("productModal");
  openCheckout();
}

/* ── CHECKOUT ── */
function openCheckout() {
  document.getElementById("checkSumGame").textContent   = selectedGame.name;
  document.getElementById("checkSumPkg").textContent    = selectedPkg.amount+" "+selectedPkg.unit;
  document.getElementById("checkSumTotal").textContent  = selectedPkg.price+" ("+selectedPkg.htg+" HTG)";
  document.getElementById("checkSumMethod").textContent = "—";
  setPayTab("moncash");
  document.getElementById("checkoutModal").classList.add("open");
  document.body.style.overflow="hidden";
}

function setPayTab(method) {
  document.querySelectorAll(".pay-tab").forEach(t=>t.classList.toggle("active",t.dataset.method===method));
  document.getElementById("checkSumMethod").textContent = method==="moncash"?"MonCash":method==="natcash"?"NatCash":"Kàt Kredi";
  const f = document.getElementById("payFields");
  if(method==="moncash"||method==="natcash"){
    f.innerHTML=`<div class="form-group"><label class="form-label">Nimewo ${method==="moncash"?"MonCash":"NatCash"}</label><input class="form-input" type="tel" placeholder="+509 XX XX XXXX"></div><p style="font-size:0.75rem;color:var(--muted);line-height:1.6;margin-top:0.25rem">⚡ Ou ap resevwa yon Push Notification pou konfime peman an.</p>`;
  } else {
    f.innerHTML=`<div class="form-group"><label class="form-label">Nimewo kàt</label><input class="form-input" type="text" placeholder="1234 5678 9012 3456" maxlength="19"></div><div class="form-row"><div class="form-group"><label class="form-label">Ekspirasyon</label><input class="form-input" type="text" placeholder="MM/YY" maxlength="5"></div><div class="form-group"><label class="form-label">CVV</label><input class="form-input" type="text" placeholder="123" maxlength="4"></div></div>`;
  }
}

function confirmPay() {
  const btn = document.getElementById("payBtn");
  btn.disabled=true;
  btn.textContent="Ap trete...";
  // Demo flow — replace with real payment API when MonCash/Stripe ready
  setTimeout(()=>{
    closeModal("checkoutModal");
    openSuccess();
  },2000);
}

/* ── SUCCESS ── */
function openSuccess() {
  const code = genCode();
  document.getElementById("successGame").textContent   = selectedGame.name+" · "+selectedPkg.amount+" "+selectedPkg.unit;
  document.getElementById("successCode").textContent   = code;
  document.getElementById("successPrice").textContent  = selectedPkg.price+" ("+selectedPkg.htg+" HTG)";
  document.getElementById("successMethod").textContent = document.querySelector(".pay-tab.active")?.dataset.method==="moncash"?"MonCash":document.querySelector(".pay-tab.active")?.dataset.method==="natcash"?"NatCash":"Kàt";
  document.getElementById("successDate").textContent   = new Date().toLocaleDateString("fr-HT",{day:"2-digit",month:"short",year:"numeric",hour:"2-digit",minute:"2-digit"});
  document.getElementById("successModal").classList.add("open");
  document.body.style.overflow="hidden";
  launchConfetti();
}

function genCode(){
  const c="ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
  const s=()=>Array.from({length:4},()=>c[Math.floor(Math.random()*c.length)]).join("");
  return `${s()}-${s()}-${s()}-${s()}`;
}

function copyCode(){
  navigator.clipboard?.writeText(document.getElementById("successCode").textContent)
    .then(()=>showToast("✅","Kòd la kopye!"))
    .catch(()=>showToast("📋","Kopye kòd la manyèlman"));
}

function launchConfetti(){
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

/* ── AUTH MODALS ── */
function openLogin(){
  closeAllModals();
  document.getElementById("loginModal").classList.add("open");
  document.body.style.overflow="hidden";
}
function openRegister(){
  closeAllModals();
  document.getElementById("registerModal").classList.add("open");
  document.body.style.overflow="hidden";
}

/* ── NAV UPDATE ── */
function updateNav(user){
  const loginBtn = document.getElementById("navLoginBtn");
  const authBtn  = document.getElementById("navAuthBtn");
  if(!authBtn) return;
  if(user){
    if(loginBtn) loginBtn.style.display="none";
    authBtn.textContent = user.first_name || "Dashboard";
    authBtn.onclick = ()=>{ window.location.href="/pages/dashboard.html"; };
  } else {
    if(loginBtn) loginBtn.style.display="";
    authBtn.textContent = "Kreye Kont";
    authBtn.onclick = openRegister;
  }
}

/* ── LOGIN ── */
async function handleLogin(){
  const email = document.querySelector("#loginModal input[type=email]")?.value?.trim();
  const pass  = document.querySelector("#loginModal input[type=password]")?.value;
  if(!email||!pass){showToast("⚠️","Antre email ak modpas ou");return;}

  const btn = document.querySelector("#loginModal .btn-full.btn-purple");
  const orig = btn.textContent;
  btn.textContent="Ap konekte..."; btn.disabled=true;

  try {
    const res  = await fetch("/api/auth/index.php?action=login",{
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body:JSON.stringify({email,password:pass})
    });
    const data = await res.json();
    if(!data.success) throw new Error(data.message);
    localStorage.setItem("izitopop_token", data.data.token);
    localStorage.setItem("izitopop_user",  JSON.stringify(data.data.user));
    closeModal("loginModal");
    showToast("✅","Byenvini "+data.data.user.first_name+"!");
    updateNav(data.data.user);
  } catch(e){
    showToast("❌", e.message||"Erè koneksyon");
  } finally {
    btn.textContent=orig; btn.disabled=false;
  }
}

/* ── REGISTER ── */
async function handleRegister(){
  const modal   = document.getElementById("registerModal");
  const inputs  = modal.querySelectorAll("input");
  const vals    = [...inputs].map(i=>i.value.trim());
  const [firstName,lastName,email,phone,password,refCode] = vals;

  if(!firstName||!email||!password){showToast("⚠️","Ranpli tout chanm obligatwa yo");return;}
  if(password.length<8){showToast("⚠️","Modpas dwe gen omwen 8 karaktè");return;}

  const btn  = modal.querySelector(".btn-full.btn-purple");
  const orig = btn.textContent;
  btn.textContent="Ap kreye kont..."; btn.disabled=true;

  try {
    const res  = await fetch("/api/auth/index.php?action=register",{
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body:JSON.stringify({first_name:firstName,last_name:lastName||"",email,phone,password,referral_code:refCode})
    });
    const data = await res.json();
    if(!data.success) throw new Error(data.message);
    localStorage.setItem("izitopop_token", data.data.token);
    localStorage.setItem("izitopop_user",  JSON.stringify(data.data.user));
    closeModal("registerModal");
    showToast("🎉","Byenvini "+data.data.user.first_name+"! Kont ou kreye.");
    updateNav(data.data.user);
  } catch(e){
    showToast("❌", e.message||"Erè enskripsyon");
  } finally {
    btn.textContent=orig; btn.disabled=false;
  }
}

/* ── LOGOUT ── */
function handleLogout(){
  const token = localStorage.getItem("izitopop_token");
  if(token) fetch("/api/auth/index.php?action=logout",{headers:{"Authorization":"Bearer "+token}}).catch(()=>{});
  localStorage.removeItem("izitopop_token");
  localStorage.removeItem("izitopop_user");
  updateNav(null);
  showToast("👋","Ou dekonekte");
}

/* ── UTILS ── */
function closeAllModals(){document.querySelectorAll(".modal-overlay.open").forEach(m=>m.classList.remove("open"));}
function closeModal(id){
  document.getElementById(id)?.classList.remove("open");
  document.body.style.overflow="";
  const b=document.getElementById("payBtn");
  if(b){b.disabled=false;b.textContent="💳 Peye kounye a";}
}
function showToast(icon,msg){
  const t=document.getElementById("toast");
  document.getElementById("toastIcon").textContent=icon;
  document.getElementById("toastText").textContent=msg;
  t.classList.add("show");
  setTimeout(()=>t.classList.remove("show"),3500);
}
function toggleMenu(){document.getElementById("mobileMenu")?.classList.toggle("open");}

/* ── CLOSE ON OVERLAY CLICK ── */
document.addEventListener("click",e=>{
  if(e.target.classList.contains("modal-overlay")) closeModal(e.target.id);
});

/* ── INIT ── */
document.addEventListener("DOMContentLoaded",()=>{
  renderGames();

  // Restore session
  const user = JSON.parse(localStorage.getItem("izitopop_user")||"null");
  updateNav(user);

  // Pre-fill referral code from URL
  const ref = new URLSearchParams(window.location.search).get("ref");
  if(ref) sessionStorage.setItem("ref_code", ref);
  const storedRef = sessionStorage.getItem("ref_code");
  if(storedRef){
    const inp = document.querySelector("#registerModal input[placeholder*='opsyon']");
    if(inp) inp.value = storedRef;
  }
});

/* ── IziTopUp Main JS ── */

const GAMES = [
  {id:1,name:"Free Fire",cat:"Top-Up",icon:"🔥",badge:"hot",packages:[
    {label:"100 Diamonds",amount:"100",unit:"Diamonds",price:45,htg:true},
    {label:"310 Diamonds",amount:"310",unit:"Diamonds",price:130,htg:true},
    {label:"520 Diamonds",amount:"520",unit:"Diamonds",price:215,htg:true},
    {label:"1060 Diamonds",amount:"1060",unit:"Diamonds",price:420,htg:true,best:true},
    {label:"2180 Diamonds",amount:"2180",unit:"Diamonds",price:820,htg:true},
    {label:"5600 Diamonds",amount:"5600",unit:"Diamonds",price:2000,htg:true},
  ],uid:"Player ID",uidHelp:"Antre ID jwèt ou (egz: 123456789)"},
  {id:2,name:"PUBG Mobile",cat:"Top-Up",icon:"⚔️",badge:"",packages:[
    {label:"60 UC",amount:"60",unit:"UC",price:55,htg:true},
    {label:"300 UC",amount:"300",unit:"UC",price:250,htg:true},
    {label:"600 UC",amount:"600",unit:"UC",price:490,htg:true,best:true},
    {label:"1500 UC",amount:"1500",unit:"UC",price:1180,htg:true},
    {label:"3000 UC",amount:"3000",unit:"UC",price:2300,htg:true},
    {label:"6000 UC",amount:"6000",unit:"UC",price:4400,htg:true},
  ],uid:"Player ID",uidHelp:"Antre PUBG UID ou (egz: 5123456789)"},
  {id:3,name:"Clash of Clans",cat:"Gift Card",icon:"🏰",badge:"new",packages:[
    {label:"$5 Pack",amount:"$5",unit:"USD",price:650,htg:true},
    {label:"$10 Pack",amount:"$10",unit:"USD",price:1280,htg:true,best:true},
    {label:"$20 Pack",amount:"$20",unit:"USD",price:2500,htg:true},
  ],uid:"Email kont",uidHelp:"Email kont Supercell ou"},
  {id:4,name:"Fortnite",cat:"V-Bucks",icon:"🎯",badge:"",packages:[
    {label:"1000 V-Bucks",amount:"1000",unit:"V-Bucks",price:850,htg:true},
    {label:"2800 V-Bucks",amount:"2800",unit:"V-Bucks",price:2100,htg:true,best:true},
    {label:"5000 V-Bucks",amount:"5000",unit:"V-Bucks",price:3700,htg:true},
    {label:"13500 V-Bucks",amount:"13500",unit:"V-Bucks",price:9500,htg:true},
  ],uid:"Epic ID",uidHelp:"Username Epic Games ou"},
  {id:5,name:"Mobile Legends",cat:"Top-Up",icon:"🌀",badge:"sale",packages:[
    {label:"28 Diamonds",amount:"28",unit:"Diamonds",price:30,htg:true},
    {label:"100 Diamonds",amount:"100",unit:"Diamonds",price:105,htg:true},
    {label:"250 Diamonds",amount:"250",unit:"Diamonds",price:250,htg:true,best:true},
    {label:"500 Diamonds",amount:"500",unit:"Diamonds",price:490,htg:true},
    {label:"1000 Diamonds",amount:"1000",unit:"Diamonds",price:950,htg:true},
  ],uid:"User ID + Zone ID",uidHelp:"Egz: 12345678 (9876)"},
  {id:6,name:"Call of Duty",cat:"CP Points",icon:"⚡",badge:"",packages:[
    {label:"80 CP",amount:"80",unit:"CP",price:120,htg:true},
    {label:"400 CP",amount:"400",unit:"CP",price:550,htg:true,best:true},
    {label:"800 CP",amount:"800",unit:"CP",price:1050,htg:true},
    {label:"2000 CP",amount:"2000",unit:"CP",price:2500,htg:true},
  ],uid:"Activision ID",uidHelp:"Username#12345"},
  {id:7,name:"Steam",cat:"Gift Card",icon:"🎮",badge:"",packages:[
    {label:"$5 Card",amount:"$5",unit:"USD",price:650,htg:true},
    {label:"$10 Card",amount:"$10",unit:"USD",price:1280,htg:true,best:true},
    {label:"$20 Card",amount:"$20",unit:"USD",price:2500,htg:true},
    {label:"$50 Card",amount:"$50",unit:"USD",price:6100,htg:true},
  ],uid:"Email Steam",uidHelp:"Pa nesesè pou gift cards — kòd voye nan email ou"},
  {id:8,name:"PlayStation",cat:"Gift Card",icon:"🕹️",badge:"",packages:[
    {label:"$10 PSN",amount:"$10",unit:"USD",price:1280,htg:true,best:true},
    {label:"$25 PSN",amount:"$25",unit:"USD",price:3100,htg:true},
    {label:"$50 PSN",amount:"$50",unit:"USD",price:6100,htg:true},
  ],uid:"Email",uidHelp:"Pa nesesè — kòd voye nan email ou"},
];

let selectedGame = null;
let selectedPkg = null;
let cartItems = [];
let isLoggedIn = false;

/* ── RENDER GAMES ── */
function renderGames(filter = "all") {
  const grid = document.getElementById("gamesGrid");
  if(!grid) return;
  const filtered = filter === "all" ? GAMES : GAMES.filter(g => g.cat === filter || g.name.toLowerCase().includes(filter));
  grid.innerHTML = filtered.map(g => `
    <a class="game-card" href="#" onclick="openProduct(${g.id});return false;">
      <div class="game-card-bar"></div>
      ${g.badge ? `<span class="game-badge badge-${g.badge}">${g.badge.toUpperCase()}</span>` : ""}
      <div class="game-card-thumb">${g.icon}</div>
      <div class="game-card-body">
        <div class="game-card-name">${g.name}</div>
        <div class="game-card-cat">${g.cat}</div>
        <div class="game-card-footer">
          <span class="game-card-price">Depi ${g.packages[0].price} HTG</span>
          <button class="game-card-btn" onclick="openProduct(${g.id});return false;">Achte</button>
        </div>
      </div>
    </a>`).join("");
}

/* ── PRODUCT MODAL ── */
function openProduct(id) {
  selectedGame = GAMES.find(g => g.id === id);
  selectedPkg = null;
  const m = document.getElementById("productModal");
  document.getElementById("productModalIcon").textContent = selectedGame.icon;
  document.getElementById("productModalName").textContent = selectedGame.name;
  document.getElementById("productModalCat").textContent = selectedGame.cat + " · Livrezon otomatik";
  document.getElementById("uidLabel").textContent = selectedGame.uid;
  document.getElementById("uidInput").placeholder = selectedGame.uidHelp;
  document.getElementById("uidInput").value = "";

  const pkgGrid = document.getElementById("packagesGrid");
  pkgGrid.innerHTML = selectedGame.packages.map((p,i) => `
    <div class="pkg" id="pkg-${i}" onclick="selectPkg(${i})">
      <div class="pkg-amount">${p.amount}</div>
      <div class="pkg-unit">${p.unit}</div>
      <div class="pkg-price">${p.price} HTG</div>
      ${p.best ? '<div class="pkg-badge">⭐ Meillè valè</div>' : ""}
    </div>`).join("");

  updateOrderSummary();
  m.classList.add("open");
  document.body.style.overflow = "hidden";
}

function selectPkg(i) {
  document.querySelectorAll(".pkg").forEach(el => el.classList.remove("selected"));
  document.getElementById("pkg-" + i)?.classList.add("selected");
  selectedPkg = selectedGame.packages[i];
  updateOrderSummary();
}

function updateOrderSummary() {
  const s = document.getElementById("orderSummary");
  if(!selectedPkg){ s.style.display = "none"; return; }
  s.style.display = "block";
  document.getElementById("summaryGame").textContent = selectedGame.name;
  document.getElementById("summaryPkg").textContent = selectedPkg.label;
  document.getElementById("summaryPrice").textContent = selectedPkg.price + " HTG";
  document.getElementById("summaryFee").textContent = "0 HTG";
  document.getElementById("summaryTotal").textContent = selectedPkg.price + " HTG";
}

function closeProductModal() {
  document.getElementById("productModal").classList.remove("open");
  document.body.style.overflow = "";
}

function proceedCheckout() {
  if(!selectedPkg){ showToast("⚠️", "Chwazi yon pake anvan"); return; }
  const uid = document.getElementById("uidInput").value.trim();
  if(!uid){ showToast("⚠️", "Antre " + selectedGame.uid + " ou"); document.getElementById("uidInput").focus(); return; }
  const tab = document.querySelector(".pay-tab.active")?.dataset.method || "moncash";
  closeProductModal();
  openCheckout({game: selectedGame, pkg: selectedPkg, uid, method: tab});
}

/* ── CHECKOUT MODAL ── */
function openCheckout({game, pkg, uid, method}) {
  const m = document.getElementById("checkoutModal");
  document.getElementById("checkoutSummary").innerHTML = `
    <div class="order-row"><span>${game.name} — ${pkg.label}</span><span>${pkg.price} HTG</span></div>
    <div class="order-row"><span>${game.uid}</span><span style="color:var(--light)">${uid}</span></div>
    <div class="order-row"><span>Frè tranzaksyon</span><span>0 HTG</span></div>
    <div class="order-row total"><span>Total</span><span>${pkg.price} HTG</span></div>`;
  setCheckoutMethod(method);
  m.classList.add("open");
  document.body.style.overflow = "hidden";
}

function setCheckoutMethod(method) {
  document.querySelectorAll(".pay-tab").forEach(t => t.classList.toggle("active", t.dataset.method === method));
  const fields = document.getElementById("paymentFields");
  if(method === "moncash") {
    fields.innerHTML = `<div class="form-group"><label class="form-label">Nimewo MonCash</label><input class="form-input" type="tel" placeholder="509 XXXX XXXX" maxlength="12"></div><p style="font-size:0.75rem;color:var(--slate);line-height:1.6">Apre ou klike "Peye", ou pral resevwa yon Push Notification sou MonCash ou pou konfime peman an.</p>`;
  } else if(method === "natcash") {
    fields.innerHTML = `<div class="form-group"><label class="form-label">Nimewo NatCash</label><input class="form-input" type="tel" placeholder="509 XXXX XXXX" maxlength="12"></div><p style="font-size:0.75rem;color:var(--slate);line-height:1.6">Apre ou klike "Peye", ou pral resevwa yon notifikasyon sou NatCash ou pou konfime peman an.</p>`;
  } else {
    fields.innerHTML = `
      <div class="form-group"><label class="form-label">Nimewo kàt</label><input class="form-input" type="text" placeholder="1234 5678 9012 3456" maxlength="19"></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Dat ekspirasyon</label><input class="form-input" type="text" placeholder="MM/YY" maxlength="5"></div>
        <div class="form-group"><label class="form-label">CVV</label><input class="form-input" type="text" placeholder="123" maxlength="4"></div>
      </div>`;
  }
}

function confirmPayment() {
  const btn = document.getElementById("payBtn");
  btn.disabled = true;
  btn.textContent = "Ap trete...";
  setTimeout(() => {
    closeModal("checkoutModal");
    openSuccess();
  }, 2200);
}

function openSuccess() {
  if(!selectedGame || !selectedPkg) return;
  const code = generateFakeCode();
  document.getElementById("successGame").textContent = selectedGame.name;
  document.getElementById("successPkg").textContent = selectedPkg.label;
  document.getElementById("successCode").textContent = code;
  document.getElementById("successModal").classList.add("open");
  document.body.style.overflow = "hidden";
  confetti();
}

function generateFakeCode() {
  const chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
  const seg = () => Array.from({length:4},()=>chars[Math.floor(Math.random()*chars.length)]).join("");
  return [seg(),seg(),seg(),seg()].join("-");
}

function copyCode() {
  const code = document.getElementById("successCode").textContent;
  navigator.clipboard?.writeText(code).then(()=>showToast("✅","Kòd la kopye!")).catch(()=>showToast("📋","Kopye kòd la manyèlman"));
}

function confetti() {
  const emojis = ["🎉","✨","🎮","💎","🔥","⚡"];
  for(let i=0;i<18;i++){
    const el = document.createElement("div");
    el.textContent = emojis[Math.floor(Math.random()*emojis.length)];
    el.style.cssText = `position:fixed;top:-20px;left:${Math.random()*100}vw;font-size:${1+Math.random()*1.5}rem;pointer-events:none;z-index:4000;animation:fall ${1.5+Math.random()*2}s linear forwards`;
    document.body.appendChild(el);
    setTimeout(()=>el.remove(), 4000);
  }
  const style = document.getElementById("confettiStyle") || document.createElement("style");
  style.id = "confettiStyle";
  style.textContent = "@keyframes fall{to{transform:translateY(110vh) rotate(360deg);opacity:0}}";
  document.head.appendChild(style);
}

/* ── AUTH MODAL ── */
function openLogin() {
  document.getElementById("loginModal").classList.add("open");
  document.getElementById("registerModal").classList.remove("open");
  document.body.style.overflow = "hidden";
}
function openRegister() {
  document.getElementById("registerModal").classList.add("open");
  document.getElementById("loginModal").classList.remove("open");
  document.body.style.overflow = "hidden";
}
function handleLogin() {
  isLoggedIn = true;
  closeModal("loginModal");
  showToast("✅","Byenvini! Ou konekte.");
  document.getElementById("navAuthBtn").textContent = "Mon Kont";
  document.getElementById("navAuthBtn").onclick = null;
}
function handleRegister() {
  isLoggedIn = true;
  closeModal("registerModal");
  showToast("🎉","Kont kreye! Byenvini sou IziTopUp.");
  document.getElementById("navAuthBtn").textContent = "Mon Kont";
}

/* ── UTILS ── */
function closeModal(id) {
  document.getElementById(id)?.classList.remove("open");
  document.body.style.overflow = "";
  const payBtn = document.getElementById("payBtn");
  if(payBtn){ payBtn.disabled = false; payBtn.textContent = "💳 Konfime Peman"; }
}
function showToast(icon, msg) {
  const t = document.getElementById("toast");
  document.getElementById("toastIcon").textContent = icon;
  document.getElementById("toastText").textContent = msg;
  t.classList.add("show");
  setTimeout(()=>t.classList.remove("show"), 3000);
}

/* ── CATEGORY FILTER ── */
function filterCat(cat, el) {
  document.querySelectorAll(".cat-pill").forEach(p=>p.classList.remove("active"));
  el.classList.add("active");
  renderGames(cat);
}

/* ── CLOSE ON OVERLAY CLICK ── */
document.addEventListener("click", e => {
  if(e.target.classList.contains("modal-overlay")) {
    document.querySelectorAll(".modal-overlay.open").forEach(m=>{ m.classList.remove("open"); document.body.style.overflow=""; });
  }
});

/* ── MOBILE MENU ── */
function toggleMenu() {
  document.getElementById("mobileMenu").classList.toggle("open");
}

/* ── INIT ── */
document.addEventListener("DOMContentLoaded", () => {
  renderGames();
});

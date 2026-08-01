/* ── IziToPop API Client ── */

const API_BASE = '/api';

// ── TOKEN MANAGEMENT ──
const Auth = {
  getToken: () => localStorage.getItem('izitopop_token'),
  setToken: (t) => localStorage.setItem('izitopop_token', t),
  removeToken: () => localStorage.removeItem('izitopop_token'),
  getUser: () => JSON.parse(localStorage.getItem('izitopop_user') || 'null'),
  setUser: (u) => localStorage.setItem('izitopop_user', JSON.stringify(u)),
  removeUser: () => localStorage.removeItem('izitopop_user'),
  isLoggedIn: () => !!localStorage.getItem('izitopop_token'),
  logout: () => {
    api('auth', 'logout', 'GET').catch(()=>{});
    Auth.removeToken();
    Auth.removeUser();
    updateNavForAuth(null);
  }
};

// ── CORE FETCH ──
async function api(endpoint, action, method = 'GET', body = null) {
  const url = `${API_BASE}/${endpoint}/index.php?action=${action}`;
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json' },
  };
  const token = Auth.getToken();
  if (token) opts.headers['Authorization'] = 'Bearer ' + token;
  if (body && method !== 'GET') opts.body = JSON.stringify(body);

  const res = await fetch(url, opts);
  const data = await res.json();
  if (!data.success) throw new Error(data.message || 'Erè sistèm');
  return data;
}

// ── AUTH ──
async function apiRegister(payload) {
  const data = await api('auth', 'register', 'POST', payload);
  Auth.setToken(data.data.token);
  Auth.setUser(data.data.user);
  return data;
}

async function apiLogin(email, password) {
  const data = await api('auth', 'login', 'POST', { email, password });
  Auth.setToken(data.data.token);
  Auth.setUser(data.data.user);
  return data;
}

// ── PRODUCTS ──
async function apiGetProducts() {
  return await api('products', 'list', 'GET');
}

// ── ORDERS ──
async function apiCreateOrder(payload) {
  return await api('orders', 'create', 'POST', payload);
}

async function apiGetOrders() {
  return await api('orders', 'list', 'GET');
}

async function apiGetOrderDetail(id) {
  const url = `${API_BASE}/orders/index.php?action=detail&id=${id}`;
  const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + Auth.getToken() } });
  return await res.json();
}

// ── PAYMENTS ──
async function apiInitiatePayment(orderId) {
  return await api('payments', 'initiate', 'POST', { order_id: orderId });
}

async function apiVerifyPayment(orderId) {
  const url = `${API_BASE}/payments/index.php?action=verify&order_id=${orderId}`;
  const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + Auth.getToken() } });
  return await res.json();
}

// ── REFERRALS / PROFILE ──
async function apiGetProfile() {
  return await api('referrals', 'profile', 'GET');
}

async function apiGetReferralStats() {
  return await api('referrals', 'stats', 'GET');
}

async function apiValidateCoupon(code, productId) {
  const url = `${API_BASE}/referrals/index.php?action=validate-coupon&code=${code}&product_id=${productId}`;
  const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + Auth.getToken() } });
  return await res.json();
}

// ── UPDATE NAV BASED ON AUTH STATE ──
function updateNavForAuth(user) {
  const navBtn   = document.getElementById('navAuthBtn');
  const navLogin = document.getElementById('navLoginBtn');
  if (!navBtn) return;
  if (user) {
    navBtn.textContent   = 'Dashboard';
    navBtn.onclick       = () => window.location.href = '/pages/dashboard.html';
    if (navLogin) navLogin.style.display = 'none';
  } else {
    navBtn.textContent   = 'Kreye Kont';
    navBtn.onclick       = openRegister;
    if (navLogin) navLogin.style.display = '';
  }
}

// ── HANDLE LOGIN FORM ──
async function handleLogin() {
  const email = document.querySelector('#loginModal input[type=email]')?.value?.trim();
  const pass  = document.querySelector('#loginModal input[type=password]')?.value;
  if (!email || !pass) return showToast('⚠️', 'Antre email ak modpas ou');

  const btn = document.querySelector('#loginModal .btn-full');
  btn.textContent = 'Ap konekte...'; btn.disabled = true;

  try {
    await apiLogin(email, pass);
    closeModal('loginModal');
    showToast('✅', 'Byenvini! Ou konekte.');
    updateNavForAuth(Auth.getUser());
  } catch(e) {
    showToast('❌', e.message);
  } finally {
    btn.textContent = 'Konekte'; btn.disabled = false;
  }
}

// ── HANDLE REGISTER FORM ──
async function handleRegister() {
  const modal = document.getElementById('registerModal');
  const inputs = modal.querySelectorAll('input');
  const [firstName, lastName, email, phone, password, refCode] = [...inputs].map(i => i.value.trim());

  if (!firstName || !email || !password) return showToast('⚠️', 'Ranpli tout chanm obligatwa yo');

  const btn = modal.querySelector('.btn-full');
  btn.textContent = 'Ap kreye kont...'; btn.disabled = true;

  try {
    await apiRegister({ first_name: firstName, last_name: lastName, email, phone, password, referral_code: refCode });
    closeModal('registerModal');
    showToast('🎉', 'Kont kreye! Byenvini sou IziToPop.');
    updateNavForAuth(Auth.getUser());
  } catch(e) {
    showToast('❌', e.message);
  } finally {
    btn.textContent = 'Kreye kont mwen gratis →'; btn.disabled = false;
  }
}

// ── LOAD REAL PRODUCTS ──
async function loadProducts(filter = 'all') {
  const grid = document.getElementById('gamesGrid');
  if (!grid) return;
  try {
    const data = await apiGetProducts();
    let products = data.data.products;
    if (filter !== 'all') products = products.filter(p => p.currency_name === filter || p.category === filter);

    grid.innerHTML = products.map(g => `
      <div class="game-card" onclick="openProduct(${g.id}, ${JSON.stringify(g).replace(/"/g,'&quot;')})">
        <div class="game-thumb">
          <img src="${g.image_url || ''}" alt="${g.name}" onerror="this.style.display='none'" loading="lazy">
          ${g.badge ? `<span class="game-badge badge-${g.badge}">${g.badge.toUpperCase()}</span>` : ''}
        </div>
        <div class="game-info">
          <div class="game-name">${g.name}</div>
          <div class="game-currency">${g.currency_name}</div>
        </div>
      </div>`).join('');

    // Store globally for modal use
    window._products = products;
  } catch(e) {
    // Fallback to static data
    renderGames(filter);
  }
}

// ── OVERRIDE proceedCheckout TO USE REAL API ──
async function proceedCheckoutReal() {
  if (!selectedPkg) { showToast('⚠️', 'Chwazi yon pake anvan'); return; }
  const uid = document.getElementById('uidInput')?.value?.trim();
  if (!uid) { showToast('⚠️', 'Antre ' + selectedGame.uid + ' ou'); return; }

  if (!Auth.isLoggedIn()) {
    closeModal('productModal');
    showToast('⚠️', 'Konekte anvan pou achte');
    openLogin();
    return;
  }

  const payMethod = document.querySelector('.pay-tab.active')?.dataset.method || 'moncash';

  const btn = document.querySelector('#productModal .btn-full');
  btn.textContent = 'Ap kreye kòmand...'; btn.disabled = true;

  try {
    const orderData = await apiCreateOrder({
      package_id:     selectedPkg.id,
      game_uid:       uid,
      payment_method: payMethod,
    });

    const order = orderData.data;
    closeModal('productModal');

    // Initiate payment
    const payData = await apiInitiatePayment(order.order_id);

    if (payMethod === 'moncash' || payMethod === 'natcash') {
      // Redirect to payment gateway
      if (payData.data.redirect_url) {
        window.location.href = payData.data.redirect_url;
      } else {
        openCheckoutWithOrder(order, payMethod);
      }
    } else {
      // Stripe — show card form
      openCheckoutWithOrder(order, payMethod, payData.data.client_secret);
    }
  } catch(e) {
    showToast('❌', e.message);
  } finally {
    btn.textContent = 'Kontinye →'; btn.disabled = false;
  }
}

// Poll for payment confirmation
async function pollPaymentStatus(orderId, maxAttempts = 20) {
  for (let i = 0; i < maxAttempts; i++) {
    await new Promise(r => setTimeout(r, 3000));
    try {
      const data = await apiVerifyPayment(orderId);
      const { payment_status, delivery_status } = data.data;
      if (payment_status === 'paid' && delivery_status === 'delivered') return 'delivered';
      if (payment_status === 'failed') return 'failed';
    } catch(e) { continue; }
  }
  return 'timeout';
}

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  const user = Auth.getUser();
  updateNavForAuth(user);

  // Try to load real products, fallback to static
  loadProducts().catch(() => renderGames());

  // Check for referral code in URL
  const params = new URLSearchParams(window.location.search);
  const ref = params.get('ref');
  if (ref) sessionStorage.setItem('referral_code', ref);

  // Pre-fill referral code in register form if present
  const storedRef = sessionStorage.getItem('referral_code');
  if (storedRef) {
    const refInput = document.querySelector('#registerModal input[placeholder*="opsyonèl"]');
    if (refInput) refInput.value = storedRef;
  }
});

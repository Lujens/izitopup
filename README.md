# IziTopUp — Frontend Static

## Paj yo nan pwojè sa a

```
izitopup/
├── index.html          ← Homepage (hero, games, checkout flow, login/register)
├── css/
│   └── style.css       ← Tout styles yo
├── js/
│   └── main.js         ← Tout interaktivite (games, modals, checkout demo)
└── pages/
    ├── dashboard.html  ← Dashboard itilizatè
    ├── faq.html        ← FAQ ak accordion
    └── contact.html    ← Fòm kontak + sipò
```

## Fonksyonalite ki demo-prè

- ✅ Homepage konplè ak hero, game cards, steps, peman section
- ✅ Catalogue 8 jwèt ak filtè kategori
- ✅ Modal achte — chwazi pake, antre Player ID
- ✅ Checkout modal — MonCash / NatCash / Kàt tabs
- ✅ Success modal ak kòd demo + konfeti animation + kopye kòd
- ✅ Login + Register modals
- ✅ Dashboard itilizatè ak kòmand yo, wallet, referans
- ✅ FAQ page ak accordion
- ✅ Contact / Sipò page
- ✅ Toast notifications
- ✅ Mobile responsive + hamburger menu
- ✅ Dark gaming aesthetic, Rajdhani + Inter

## Pou deplwaye sou Hostinger via GitHub

1. Push dossye `izitopup/` yo nan yon GitHub repo
2. Nan Hostinger hPanel → Git → Konekte repo a
3. Deploy branch `main`
4. Domain → Pwen domèn ou sou dossye `public_html` oswa rasin hosting la

## Pwochen etap pou backend reyèl

- [ ] Entegre MonCash API reyèl (Digicel endpoint)
- [ ] Entegre NatCash API reyèl (Natcom endpoint)
- [ ] Konekte Stripe pou kàt
- [ ] Base de done (MySQL sou Hostinger oswa Supabase)
- [ ] PHP backend oswa Node.js pou jere kòmand + stock
- [ ] Email otomatik (SMTP Brevo / Mailgun)
- [ ] Admin panel

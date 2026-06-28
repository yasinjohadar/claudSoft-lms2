@php($uid = $uid ?? 'default')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" role="img" aria-hidden="true" focusable="false">
  <defs>
    <linearGradient id="maleBg-{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0066B3"/>
      <stop offset="100%" stop-color="#3399E0"/>
    </linearGradient>
    <linearGradient id="maleShirt-{{ $uid }}" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#1A5A96"/>
      <stop offset="100%" stop-color="#004C8A"/>
    </linearGradient>
  </defs>
  <circle cx="100" cy="100" r="100" fill="url(#maleBg-{{ $uid }})"/>
  <ellipse cx="100" cy="182" rx="78" ry="42" fill="url(#maleShirt-{{ $uid }})"/>
  <rect x="84" y="122" width="32" height="26" rx="5" fill="#F0C4A8"/>
  <ellipse cx="100" cy="86" rx="42" ry="46" fill="#F0C4A8"/>
  <ellipse cx="56" cy="88" rx="7" ry="10" fill="#E5AD88"/>
  <ellipse cx="144" cy="88" rx="7" ry="10" fill="#E5AD88"/>
  <path d="M56 94 C56 46 74 34 100 32 C126 34 144 46 144 94 C144 66 126 54 100 52 C74 54 56 66 56 94 Z" fill="#2A2118"/>
  <path d="M56 76 Q100 48 144 76 L144 68 Q100 42 56 68 Z" fill="#3D3228"/>
  <ellipse cx="84" cy="90" rx="5.5" ry="6.5" fill="#2A2118"/>
  <ellipse cx="116" cy="90" rx="5.5" ry="6.5" fill="#2A2118"/>
  <circle cx="85.5" cy="88.5" r="1.6" fill="#ffffff"/>
  <circle cx="117.5" cy="88.5" r="1.6" fill="#ffffff"/>
  <path d="M74 76 Q84 72 92 76" stroke="#2A2118" stroke-width="2.8" fill="none" stroke-linecap="round"/>
  <path d="M108 76 Q116 72 126 76" stroke="#2A2118" stroke-width="2.8" fill="none" stroke-linecap="round"/>
  <path d="M100 98 L98 104" stroke="#D4956A" stroke-width="2.2" stroke-linecap="round" fill="none"/>
  <path d="M86 110 Q100 120 114 110" stroke="#C97858" stroke-width="2.6" fill="none" stroke-linecap="round"/>
  <path d="M68 148 L100 132 L132 148 L132 200 L68 200 Z" fill="url(#maleShirt-{{ $uid }})"/>
  <path d="M88 132 L100 140 L112 132" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round" opacity="0.35"/>
</svg>

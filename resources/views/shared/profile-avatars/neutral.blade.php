@php($uid = $uid ?? 'default')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" role="img" aria-hidden="true" focusable="false">
  <defs>
    <linearGradient id="neutralBg-{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0066B3"/>
      <stop offset="100%" stop-color="#3399E0"/>
    </linearGradient>
    <linearGradient id="neutralBody-{{ $uid }}" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#1A5A96"/>
      <stop offset="100%" stop-color="#004C8A"/>
    </linearGradient>
  </defs>
  <circle cx="100" cy="100" r="100" fill="url(#neutralBg-{{ $uid }})"/>
  <ellipse cx="100" cy="180" rx="74" ry="38" fill="url(#neutralBody-{{ $uid }})"/>
  <circle cx="100" cy="78" r="38" fill="#F0C4A8"/>
  <rect x="88" y="110" width="24" height="22" rx="4" fill="#F0C4A8"/>
  <path d="M62 82 C62 48 78 38 100 38 C122 38 138 48 138 82 C138 58 122 50 100 50 C78 50 62 58 62 82 Z" fill="#64748b"/>
  <ellipse cx="88" cy="80" rx="4.5" ry="5.5" fill="#334155"/>
  <ellipse cx="112" cy="80" rx="4.5" ry="5.5" fill="#334155"/>
  <path d="M90 92 Q100 98 110 92" stroke="#C97858" stroke-width="2.4" fill="none" stroke-linecap="round"/>
  <path d="M72 148 L100 134 L128 148 L128 200 L72 200 Z" fill="url(#neutralBody-{{ $uid }})"/>
</svg>

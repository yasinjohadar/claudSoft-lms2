@php($uid = $uid ?? 'default')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" role="img" aria-hidden="true" focusable="false">
  <defs>
    <linearGradient id="fBg-{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#7ec8f0"/>
      <stop offset="45%" stop-color="#4da6db"/>
      <stop offset="100%" stop-color="#0066B3"/>
    </linearGradient>
    <radialGradient id="fGlow-{{ $uid }}" cx="50%" cy="35%" r="55%">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.35"/>
      <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
    </radialGradient>
    <linearGradient id="fSkin-{{ $uid }}" x1="30%" y1="0%" x2="70%" y2="100%">
      <stop offset="0%" stop-color="#FFE8D6"/>
      <stop offset="100%" stop-color="#F5C4A8"/>
    </linearGradient>
    <linearGradient id="fHijabMain-{{ $uid }}" x1="20%" y1="0%" x2="80%" y2="100%">
      <stop offset="0%" stop-color="#ffffff"/>
      <stop offset="50%" stop-color="#F5FAFF"/>
      <stop offset="100%" stop-color="#D6EBFA"/>
    </linearGradient>
    <linearGradient id="fHijabShadow-{{ $uid }}" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#0066B3" stop-opacity="0.12"/>
      <stop offset="100%" stop-color="#0066B3" stop-opacity="0"/>
    </linearGradient>
    <linearGradient id="fAbaya-{{ $uid }}" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#3399E0"/>
      <stop offset="100%" stop-color="#004C8A"/>
    </linearGradient>
    <linearGradient id="fEye-{{ $uid }}" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#5C4033"/>
      <stop offset="100%" stop-color="#3D2817"/>
    </linearGradient>
    <linearGradient id="fLip-{{ $uid }}" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#E8928A"/>
      <stop offset="100%" stop-color="#D4736A"/>
    </linearGradient>
  </defs>

  <circle cx="100" cy="100" r="100" fill="url(#fBg-{{ $uid }})"/>
  <circle cx="100" cy="100" r="100" fill="url(#fGlow-{{ $uid }})"/>

  <!-- Abaya / shoulders -->
  <path d="M28 200 C38 158 68 138 100 136 C132 138 162 158 172 200 Z" fill="url(#fAbaya-{{ $uid }})"/>
  <path d="M62 148 C78 142 88 140 100 140 C112 140 122 142 138 148 L138 200 L62 200 Z" fill="#005999" opacity="0.25"/>

  <!-- Hijab back drape -->
  <path d="M34 118 C34 62 58 32 100 28 C142 32 166 62 166 118 C166 88 148 68 100 64 C52 68 34 88 34 118 Z" fill="#C5E3F7"/>
  <path d="M30 128 C36 88 64 58 100 54 C136 58 164 88 170 128 L178 200 L22 200 Z" fill="url(#fHijabMain-{{ $uid }})"/>

  <!-- Hijab side folds -->
  <path d="M30 130 C42 98 58 82 72 76 C58 92 48 118 46 148 Z" fill="url(#fHijabShadow-{{ $uid }})"/>
  <path d="M170 130 C158 98 142 82 128 76 C142 92 152 118 154 148 Z" fill="url(#fHijabShadow-{{ $uid }})"/>

  <!-- Face -->
  <ellipse cx="100" cy="96" rx="34" ry="38" fill="url(#fSkin-{{ $uid }})"/>

  <!-- Hijab frame around face -->
  <path d="M66 98 C66 58 80 46 100 44 C120 46 134 58 134 98 C134 72 120 60 100 58 C80 60 66 72 66 98 Z" fill="#ffffff"/>
  <path d="M62 102 C62 56 78 40 100 38 C122 40 138 56 138 102 C138 74 124 58 100 56 C76 58 62 74 62 102 Z" fill="url(#fHijabMain-{{ $uid }})"/>

  <!-- Neck -->
  <path d="M88 128 C92 122 108 122 112 128 L112 142 C108 146 92 146 88 142 Z" fill="#F0B896"/>

  <!-- Cheek blush -->
  <ellipse cx="78" cy="104" rx="10" ry="6" fill="#F4A5A0" opacity="0.35"/>
  <ellipse cx="122" cy="104" rx="10" ry="6" fill="#F4A5A0" opacity="0.35"/>

  <!-- Eyebrows -->
  <path d="M78 82 Q86 78 94 81" stroke="#B8886E" stroke-width="2.2" fill="none" stroke-linecap="round"/>
  <path d="M106 81 Q114 78 122 82" stroke="#B8886E" stroke-width="2.2" fill="none" stroke-linecap="round"/>

  <!-- Eyes -->
  <ellipse cx="84" cy="92" rx="9" ry="10" fill="#ffffff"/>
  <ellipse cx="116" cy="92" rx="9" ry="10" fill="#ffffff"/>
  <circle cx="85" cy="93" r="5.5" fill="url(#fEye-{{ $uid }})"/>
  <circle cx="117" cy="93" r="5.5" fill="url(#fEye-{{ $uid }})"/>
  <circle cx="86.5" cy="91" r="2" fill="#ffffff" opacity="0.9"/>
  <circle cx="118.5" cy="91" r="2" fill="#ffffff" opacity="0.9"/>
  <circle cx="83" cy="95" r="1.2" fill="#ffffff" opacity="0.5"/>

  <!-- Lashes -->
  <path d="M76 88 Q80 84 84 87" stroke="#8B6914" stroke-width="1.2" fill="none" stroke-linecap="round" opacity="0.5"/>
  <path d="M116 87 Q120 84 124 88" stroke="#8B6914" stroke-width="1.2" fill="none" stroke-linecap="round" opacity="0.5"/>

  <!-- Nose -->
  <path d="M100 98 Q99 104 100 106" stroke="#DEA882" stroke-width="1.8" fill="none" stroke-linecap="round"/>

  <!-- Smile -->
  <path d="M88 112 Q100 120 112 112" stroke="url(#fLip-{{ $uid }})" stroke-width="2.8" fill="none" stroke-linecap="round"/>
  <path d="M92 112 Q100 117 108 112" fill="#F0A8A0" opacity="0.45"/>

  <!-- Hijab front highlight -->
  <path d="M72 108 C80 96 88 90 100 88 C112 90 120 96 128 108" stroke="#ffffff" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0.55"/>
  <ellipse cx="100" cy="132" rx="22" ry="8" fill="#ffffff" opacity="0.18"/>
</svg>

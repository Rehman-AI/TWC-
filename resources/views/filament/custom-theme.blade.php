<style>
/* TWC Custom Theme — injected via renderHook */
:root {
    color-scheme: dark;
    --twc-bg: #09090b;
    --twc-bg-sidebar: #0c0c0f;
    --twc-bg-surface: #111114;
    --twc-bg-card: #16161a;
    --twc-bg-card-h: #1c1c21;
    --twc-accent: #e8227a;
    --twc-purple: #8b5cf6;
    --twc-green: #10b981;
    --twc-border: rgba(255,255,255,0.06);
    --twc-border-h: rgba(255,255,255,0.10);
    --twc-glow-1: #ff96a9;
    --twc-glow-2: #e8b4f0;
    --twc-glow-3: #ffb3c6;
    --twc-glow-4: #d44d8a;
    --twc-spring: cubic-bezier(0.34,1.56,0.64,1);
    --twc-ease: cubic-bezier(0.22,1,0.36,1);
}
@property --glow-angle { syntax:'<angle>'; initial-value:0deg; inherits:false }

body { background: var(--twc-bg) !important; }

/* Sidebar */
.fi-sidebar { background: var(--twc-bg-sidebar) !important; border-right: 1px solid var(--twc-border) !important; }
.fi-sidebar-item a:hover { background: rgba(232,34,122,0.08) !important; border-radius: 8px; transition: all 0.3s; }
.fi-sidebar-item [aria-current="page"] { background: rgba(232,34,122,0.10) !important; border-radius: 8px; }
.fi-sidebar-group-label { text-transform: uppercase !important; font-size: 10px !important; letter-spacing: 1.5px !important; opacity: 0.4 !important; font-weight: 600 !important; }
.fi-sidebar-item { animation: fadeSlideIn 0.3s cubic-bezier(0.22,1,0.36,1) both; }
.fi-sidebar-item:nth-child(1){animation-delay:.02s}.fi-sidebar-item:nth-child(2){animation-delay:.04s}.fi-sidebar-item:nth-child(3){animation-delay:.06s}.fi-sidebar-item:nth-child(4){animation-delay:.08s}.fi-sidebar-item:nth-child(5){animation-delay:.1s}
@keyframes fadeSlideIn{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:translateX(0)}}

/* Topbar glassmorphism */
.fi-topbar { background: rgba(9,9,11,0.85) !important; backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); border-bottom: 1px solid var(--twc-border) !important; }

/* Cards */
.fi-section, .fi-ta-ctn, .fi-wi { background: var(--twc-bg-card) !important; border: 1px solid var(--twc-border) !important; border-radius: 12px !important; transition: border-color 0.3s, box-shadow 0.3s; }
.fi-section:hover, .fi-ta-ctn:hover { border-color: var(--twc-border-h) !important; }

/* Table rows */
.fi-ta-row:hover td { background: rgba(232,34,122,0.04) !important; }
.fi-ta-row { border-bottom: 1px solid var(--twc-border) !important; }

/* Primary buttons — gradient glow */
.fi-btn-color-primary { background: linear-gradient(135deg,#8c0f60,#e8227a) !important; border: none !important; transition: transform 0.2s var(--twc-spring), box-shadow 0.3s; }
.fi-btn-color-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(232,34,122,0.35) !important; }
.fi-btn-color-primary:active { transform: translateY(0) scale(0.98); }

/* Inputs */
.fi-input-wrp { background: var(--twc-bg-surface) !important; border: 1px solid var(--twc-border) !important; border-radius: 8px !important; transition: border-color 0.2s; }
.fi-input-wrp:focus-within { border-color: rgba(232,34,122,0.5) !important; box-shadow: 0 0 0 3px rgba(232,34,122,0.1) !important; }

/* Login card glow border */
.fi-simple-main-ctn { background: var(--twc-bg-card) !important; border: 1px solid var(--twc-border) !important; border-radius: 16px !important; box-shadow: 0 25px 50px rgba(0,0,0,0.4) !important; position: relative; overflow: hidden; }
.fi-simple-main-ctn::before { content:''; position:absolute; inset:-1px; border-radius:17px; background: conic-gradient(from var(--glow-angle,0deg),var(--twc-glow-1),var(--twc-glow-2),var(--twc-glow-3),var(--twc-glow-4),var(--twc-glow-1)); animation: glowSpin 6s linear infinite; z-index:-1; opacity:0.12; }
@keyframes glowSpin{to{--glow-angle:360deg}}
.fi-simple-layout { background: var(--twc-bg) !important; }

/* Stat cards with blob */
.fi-wi-stats-overview-stat { background: var(--twc-bg-card) !important; border: 1px solid var(--twc-border) !important; border-radius: 12px !important; transition: transform 0.3s var(--twc-spring), border-color 0.3s, box-shadow 0.3s; position: relative; overflow: hidden; }
.fi-wi-stats-overview-stat:hover { transform: translateY(-2px); border-color: var(--twc-border-h) !important; box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
.fi-wi-stats-overview-stat::after { content:''; position:absolute; width:120px; height:120px; border-radius:50%; background:radial-gradient(circle,rgba(232,34,122,0.08),transparent 70%); top:-40px; right:-40px; pointer-events:none; animation: blobFloat 8s ease-in-out infinite; }
@keyframes blobFloat{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(-10px,10px) scale(1.1)}}

/* Badges */
.fi-badge { border-radius: 6px !important; transition: transform 0.2s var(--twc-spring); }
.fi-badge:hover { transform: scale(1.05); }

/* Page headings */
.fi-header-heading { font-weight: 700 !important; letter-spacing: -0.02em !important; }

/* Modals */
.fi-modal-window { background: var(--twc-bg-card) !important; border: 1px solid var(--twc-border) !important; border-radius: 16px !important; box-shadow: 0 25px 60px rgba(0,0,0,0.5) !important; }

/* Scrollbar */
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.07);border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:rgba(255,255,255,0.14)}

/* Empty state */
.fi-ta-empty-state-icon { color: rgba(232,34,122,0.3) !important; }
</style>
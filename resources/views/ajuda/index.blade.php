@extends('layouts.app')

@section('title', 'Ajuda & Documentação')

@section('styles')
<style>
    .help-layout { display: flex; gap: 1.25rem; }
    .help-sidebar { width: 14rem; flex-shrink: 0; display: flex; flex-direction: column; gap: 0.25rem; }
    .help-content { flex: 1; min-width: 0; }

    @media (max-width: 1024px) {
        .help-layout { flex-direction: column; }
        .help-sidebar { width: 100%; }
        .sidebar-desktop { display: none; }
    }
    @media (min-width: 1025px) {
        .sidebar-mobile { display: none; }
    }

    .sidebar-btn {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.625rem 0.75rem;
        border-radius: 0.75rem;
        border: none;
        background: transparent;
        cursor: pointer;
        text-align: left;
        width: 100%;
        transition: all 0.15s;
        color: var(--slate-600);
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
    }
    .sidebar-btn:hover { background: var(--slate-100); }
    .sidebar-btn.active { background: var(--navy); color: white; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
    .sidebar-icon {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        flex-shrink: 0;
    }
    .sidebar-btn.active .sidebar-icon { background: rgba(255,255,255,0.2) !important; color: white !important; }

    .step-item { display: flex; gap: 1rem; margin-bottom: 0.75rem; }
    .step-num {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 9999px;
        background: var(--navy);
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }
    .step-title { font-size: 0.875rem; font-weight: 600; color: var(--slate-800); }
    .step-desc { font-size: 0.875rem; color: var(--slate-500); margin-top: 0.125rem; }

    .tips-box {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-radius: 0.75rem;
        padding: 1rem;
    }
    .tips-label { font-size: 0.75rem; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem; }
    .tip-item { display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.875rem; color: #92400e; margin-bottom: 0.5rem; }
    .tip-dot { width: 0.375rem; height: 0.375rem; border-radius: 9999px; background: #fbbf24; flex-shrink: 0; margin-top: 0.5rem; }

    .faq-item { border: 1px solid var(--slate-100); border-radius: 0.75rem; overflow: hidden; margin-bottom: 0.5rem; }
    .faq-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.875rem 1rem;
        background: white;
        border: none;
        cursor: pointer;
        text-align: left;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--slate-700);
        transition: background 0.15s;
    }
    .faq-btn:hover { background: var(--slate-50); }
    .faq-answer { padding: 0 1rem 1rem; font-size: 0.875rem; color: var(--slate-600); line-height: 1.6; display: none; }
    .faq-item.open .faq-answer { display: block; }
    .faq-icon { transition: transform 0.15s; color: var(--slate-400); font-size: 0.7rem; }
    .faq-item.open .faq-icon { transform: rotate(180deg); }

    .role-tag {
        display: inline-flex;
        padding: 0.125rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 9999px;
        background: rgba(0,26,114,0.1);
        color: var(--navy);
    }

    .version-box {
        margin-top: 1rem;
        padding: 0.75rem;
        background: var(--slate-50);
        border: 1px solid var(--slate-100);
        border-radius: 0.75rem;
    }
    .version-label { font-size: 0.625rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.375rem; margin-bottom: 0.25rem; }
    .version-val { font-size: 0.75rem; font-weight: 600; color: var(--slate-700); }
</style>
@endsection

@section('content')
@php
    $activeId = request('section', $sections[0]['id'] ?? 'dashboard');
    $activeSection = collect($sections)->firstWhere('id', $activeId) ?? $sections[0];
@endphp

{{-- Header --}}
<div style="margin-bottom:1.25rem;">
    <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.75rem; color:var(--slate-400); margin-bottom:0.5rem;">
        <a href="/" style="color:var(--slate-400); text-decoration:none;">Dashboard</a>
        <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
        <span style="color:var(--slate-700); font-weight:500;">Ajuda & Documentação</span>
    </div>
    <div style="display:flex; align-items:center; gap:0.75rem;">
        <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:var(--navy); display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,.1);">
            <i class="fas fa-book-open" style="color:white; font-size:1rem;"></i>
        </div>
        <div>
            <h1 style="font-size:1.25rem; font-weight:700; color:var(--slate-900); margin:0;">Ajuda & Documentação</h1>
            <p style="font-size:0.75rem; color:var(--slate-400); margin:0.125rem 0 0;">
                Manual de utilização do sistema RHC Pedidos
                <span style="color:var(--slate-300); margin-left:0.5rem;">· v1.3.0</span>
            </p>
        </div>
    </div>
</div>

{{-- Mobile selector --}}
<div class="sidebar-mobile" style="margin-bottom:1rem;">
    <select class="rhc-select" style="width:100%;" onchange="window.location.href='{{ route('ajuda') }}?section=' + this.value">
        @foreach($sections as $s)
            <option value="{{ $s['id'] }}" {{ $activeId === $s['id'] ? 'selected' : '' }}>{{ $s['title'] }}</option>
        @endforeach
    </select>
</div>

{{-- Main layout --}}
<div class="help-layout">
    {{-- Sidebar --}}
    <nav class="help-sidebar sidebar-desktop">
        <div style="font-size:0.625rem; font-weight:700; color:var(--slate-400); text-transform:uppercase; letter-spacing:0.1em; padding:0 0.75rem; margin-bottom:0.5rem;">Módulos</div>
        @foreach($sections as $s)
            <a href="{{ route('ajuda', ['section' => $s['id']]) }}" class="sidebar-btn {{ $activeId === $s['id'] ? 'active' : '' }}">
                <span class="sidebar-icon" style="background:{{ $s['bg'] }}; color:{{ $s['color'] }};">
                    <i class="fas {{ $s['icon'] }}"></i>
                </span>
                {{ $s['title'] }}
            </a>
        @endforeach

        <div class="version-box">
            <div class="version-label"><i class="fas fa-info-circle"></i> Versão</div>
            <div class="version-val">v1.3.0</div>
            <div style="font-size:0.6875rem; color:var(--slate-400); margin-top:0.25rem;">Atualizado em 19/03/2026</div>
        </div>
    </nav>

    {{-- Content --}}
    <div class="help-content">
        <div class="rhc-card" style="overflow:hidden;">
            {{-- Section header --}}
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:{{ $activeSection['bg'] }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas {{ $activeSection['icon'] }}" style="color:{{ $activeSection['color'] }}; font-size:1rem;"></i>
                    </div>
                    <div>
                        <h2 style="font-size:1.125rem; font-weight:700; color:var(--slate-900); margin:0;">{{ $activeSection['title'] }}</h2>
                        <p style="font-size:0.75rem; color:var(--slate-400); margin:0.125rem 0 0;">{{ Str::limit($activeSection['description'], 80) }}</p>
                    </div>
                </div>
            </div>

            {{-- Section body --}}
            <div style="padding:1.5rem;">
                {{-- Description --}}
                <p style="font-size:0.875rem; color:var(--slate-600); line-height:1.6; margin-bottom:1.5rem;">
                    {{ $activeSection['description'] }}
                </p>

                @if(!empty($activeSection['roles']))
                    <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:1.5rem;">
                        <span style="font-size:0.75rem; font-weight:600; color:var(--slate-400); text-transform:uppercase; letter-spacing:0.05em;">Perfis:</span>
                        @foreach($activeSection['roles'] as $role)
                            <span class="role-tag">{{ $role }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- Steps --}}
                @if(!empty($activeSection['steps']))
                    <div style="margin-bottom:2rem;">
                        <h3 style="font-size:0.875rem; font-weight:700; color:var(--slate-800); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                            <i class="fas fa-circle-check" style="color:#10b981; font-size:0.8rem;"></i> Passo a passo
                        </h3>
                        @foreach($activeSection['steps'] as $i => $step)
                            <div class="step-item">
                                <div class="step-num">{{ $i + 1 }}</div>
                                <div>
                                    <div class="step-title">{{ $step['title'] }}</div>
                                    <div class="step-desc">{{ $step['desc'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Tips --}}
                @if(!empty($activeSection['tips']))
                    <div class="tips-box" style="margin-bottom:2rem;">
                        <div class="tips-label"><i class="fas fa-lightbulb"></i> Dicas úteis</div>
                        @foreach($activeSection['tips'] as $tip)
                            <div class="tip-item">
                                <div class="tip-dot"></div>
                                {{ $tip }}
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- FAQ --}}
                @if(!empty($activeSection['faq']))
                    <div>
                        <h3 style="font-size:0.875rem; font-weight:700; color:var(--slate-800); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                            <i class="fas fa-circle-question" style="color:var(--navy); font-size:0.8rem;"></i> Perguntas frequentes
                        </h3>
                        @foreach($activeSection['faq'] as $f)
                            <div class="faq-item">
                                <button class="faq-btn" onclick="this.parentElement.classList.toggle('open')">
                                    <span>{{ $f['q'] }}</span>
                                    <i class="fas fa-chevron-down faq-icon"></i>
                                </button>
                                <div class="faq-answer">{{ $f['a'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

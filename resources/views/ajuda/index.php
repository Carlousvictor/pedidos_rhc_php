<?php
$__title = 'Ajuda & Documentação';
ob_start();
?>
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
<?php $__styles = ob_get_clean();
include __DIR__ . '/../layouts/header.php';
?>
<?php 
    $activeId = request('section', $sections[0]['id'] ?? 'dashboard');
    $activeSection = collect($sections)->firstWhere('id', $activeId) ?? $sections[0];
 ?>


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


<div class="sidebar-mobile" style="margin-bottom:1rem;">
    <select class="rhc-select" style="width:100%;" onchange="window.location.href='<?= e(route('ajuda')) ?>?section=' + this.value">
        <?php foreach ($sections as $s): ?>
            <option value="<?= e($s['id']) ?>" <?= e($activeId === $s['id'] ? 'selected' : '') ?>><?= e($s['title']) ?></option>
        <?php endforeach; ?>
    </select>
</div>


<div class="help-layout">
    
    <nav class="help-sidebar sidebar-desktop">
        <div style="font-size:0.625rem; font-weight:700; color:var(--slate-400); text-transform:uppercase; letter-spacing:0.1em; padding:0 0.75rem; margin-bottom:0.5rem;">Módulos</div>
        <?php foreach ($sections as $s): ?>
            <a href="<?= e(route('ajuda', ['section' => $s['id']])) ?>" class="sidebar-btn <?= e($activeId === $s['id'] ? 'active' : '') ?>">
                <span class="sidebar-icon" style="background:<?= e($s['bg']) ?>; color:<?= e($s['color']) ?>;">
                    <i class="fas <?= e($s['icon']) ?>"></i>
                </span>
                <?= e($s['title']) ?>
            </a>
        <?php endforeach; ?>

        <div class="version-box">
            <div class="version-label"><i class="fas fa-info-circle"></i> Versão</div>
            <div class="version-val">v1.3.0</div>
            <div style="font-size:0.6875rem; color:var(--slate-400); margin-top:0.25rem;">Atualizado em 19/03/2026</div>
        </div>
    </nav>

    
    <div class="help-content">
        <div class="rhc-card" style="overflow:hidden;">
            
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:<?= e($activeSection['bg']) ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas <?= e($activeSection['icon']) ?>" style="color:<?= e($activeSection['color']) ?>; font-size:1rem;"></i>
                    </div>
                    <div>
                        <h2 style="font-size:1.125rem; font-weight:700; color:var(--slate-900); margin:0;"><?= e($activeSection['title']) ?></h2>
                        <p style="font-size:0.75rem; color:var(--slate-400); margin:0.125rem 0 0;"><?= e(Str::limit($activeSection['description'], 80)) ?></p>
                    </div>
                </div>
            </div>

            
            <div style="padding:1.5rem;">
                
                <p style="font-size:0.875rem; color:var(--slate-600); line-height:1.6; margin-bottom:1.5rem;">
                    <?= e($activeSection['description']) ?>
                </p>

                <?php if (!empty($activeSection['roles'])): ?>
                    <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; margin-bottom:1.5rem;">
                        <span style="font-size:0.75rem; font-weight:600; color:var(--slate-400); text-transform:uppercase; letter-spacing:0.05em;">Perfis:</span>
                        <?php foreach ($activeSection['roles'] as $role): ?>
                            <span class="role-tag"><?= e($role) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                
                <?php if (!empty($activeSection['steps'])): ?>
                    <div style="margin-bottom:2rem;">
                        <h3 style="font-size:0.875rem; font-weight:700; color:var(--slate-800); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                            <i class="fas fa-circle-check" style="color:#10b981; font-size:0.8rem;"></i> Passo a passo
                        </h3>
                        <?php foreach ($activeSection['steps'] as $i => $step): ?>
                            <div class="step-item">
                                <div class="step-num"><?= e($i + 1) ?></div>
                                <div>
                                    <div class="step-title"><?= e($step['title']) ?></div>
                                    <div class="step-desc"><?= e($step['desc']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                
                <?php if (!empty($activeSection['tips'])): ?>
                    <div class="tips-box" style="margin-bottom:2rem;">
                        <div class="tips-label"><i class="fas fa-lightbulb"></i> Dicas úteis</div>
                        <?php foreach ($activeSection['tips'] as $tip): ?>
                            <div class="tip-item">
                                <div class="tip-dot"></div>
                                <?= e($tip) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                
                <?php if (!empty($activeSection['faq'])): ?>
                    <div>
                        <h3 style="font-size:0.875rem; font-weight:700; color:var(--slate-800); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                            <i class="fas fa-circle-question" style="color:var(--navy); font-size:0.8rem;"></i> Perguntas frequentes
                        </h3>
                        <?php foreach ($activeSection['faq'] as $f): ?>
                            <div class="faq-item">
                                <button class="faq-btn" onclick="this.parentElement.classList.toggle('open')">
                                    <span><?= e($f['q']) ?></span>
                                    <i class="fas fa-chevron-down faq-icon"></i>
                                </button>
                                <div class="faq-answer"><?= e($f['a']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

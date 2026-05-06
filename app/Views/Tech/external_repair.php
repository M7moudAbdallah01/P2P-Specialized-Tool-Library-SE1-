<?php

$external_repairs = $data['external_repairs'] ?? [
    [
        'tool_name' => 'Generac Portable Generator',
        'shop' => 'Industrial Fix Masters',
        'sent_date' => '2026-04-20',
        'est_return' => '2026-05-15',
        'workflow_step' => 'Under Diagnosis'
    ],
    [
        'tool_name' => 'Hilti Concrete Saw',
        'shop' => 'Pro-Tool Services',
        'sent_date' => '2026-05-01',
        'est_return' => '2026-05-10',
        'workflow_step' => 'Waiting for Parts'
    ]
];
?>

<link rel="stylesheet" href="../../assets/css/external_repair.css">

<div class="container">
    <header class="header-flex">
        <h2 class="brand-text">EXTERNAL WORKFLOW MANAGEMENT</h2>
        <span class="status-badge">OUT-OF-FACILITY TRACKING</span>
    </header>

    <div class="integration-grid">
        <?php foreach ($external_repairs as $repair): ?>
            <div class="repair-card">
                <div class="tool-name">
                    <?= htmlspecialchars($repair['tool_name']); ?>
                    <i class="fas fa-external-link-alt" style="font-size: 0.8rem; color: var(--red);"></i>
                </div>

                <div class="integration-details">
                    <div class="detail-row">
                        <span class="label-text">Third-Party Shop:</span>
                        <span class="value-text"><?= htmlspecialchars($repair['shop']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label-text">Dispatch Date:</span>
                        <span class="value-text"><?= $repair['sent_date']; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label-text">Expected Return:</span>
                        <span class="value-text" style="color: var(--valid);"><?= $repair['est_return']; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label-text">Current Step:</span>
                        <span class="value-text" style="color: var(--red);"><?= $repair['workflow_step']; ?></span>
                    </div>
                </div>
                
                <button class="btn-sm" style="width: 100%; margin-top: 15px; border-color: var(--border2);">
                    UPDATE WORKFLOW LOG
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php

$tools = $data['tools'] ?? []; 
$issues = $data['issues'] ?? [
    ['name' => 'Motor Overheat', 'cost' => 110],
    ['name' => 'Bearing Replacement', 'cost' => 85],
    ['name' => 'Cable Fracture', 'cost' => 30]
];
$labor_cost = 50.00;
?>


<link rel="stylesheet" href="../../assets/css/repair_estimator.css">

<div class="container">
    <section class="function-card">
        <header class="card-head">
            <span class="brand-icon"><i class="fas fa-tools"></i></span>
            <h2 class="brand-text">REPAIR COST ESTIMATOR</h2>
        </header>

       
        <form action="../../Controllers/RepairController.php?action=save" method="POST" class="dark-form">
            <div class="form-grid">
                
                <div class="field-box">
                    <label for="tool_id">TOOL IDENTIFICATION</label>
                    <select name="tool_id" id="tool_id" class="custom-input" required>
                        <option value="">-- Select Tool --</option>
                        <?php foreach($tools as $tool): ?>
                            <option value="<?= $tool['id']; ?>">
                                <?= htmlspecialchars($tool['name']); ?> (#<?= $tool['serial']; ?>)
                            </option>
                        <?php endforeach; ?>
                        
                        
                        <?php if(empty($tools)): ?>
                            <option value="1">Hilti Rotary Hammer (#H-99)</option>
                            <option value="2">Dewalt Table Saw (#D-42)</option>
                        <?php endif; ?>
                    </select>
                </div>

                
                <div class="field-box">
                    <label for="issue">DIAGNOSED ISSUE</label>
                    <select name="issue" id="issue" class="custom-input" required>
                        <?php foreach($issues as $issue): ?>
                            <option value="<?= $issue['name']; ?>" data-price="<?= $issue['cost']; ?>">
                                <?= $issue['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

           
            <div class="summary-box">
                <div class="sum-row">
                    <span>Labor Cost:</span> 
                    <span class="val">$<?= number_format($labor_cost, 2); ?></span>
                </div>
                <div class="sum-row">
                    <span>Parts Estimate:</span> 
                    <span class="val" id="parts-val">$0.00</span>
                </div>
                <div class="sum-row total">
                    <span>GRAND TOTAL:</span> 
                    <span class="val" id="total-val">$<?= number_format($labor_cost, 2); ?></span>
                </div>
            </div>

            <button type="submit" name="save_estimate" class="btn-action">
                <i class="fas fa-save"></i> SAVE ESTIMATE
            </button>
        </form>
    </section>
</div>


<script>
document.getElementById('issue').addEventListener('change', function() {
    const labor = <?= $labor_cost; ?>;
    const parts = parseFloat(this.options[this.selectedIndex].getAttribute('data-price')) || 0;
    
    document.getElementById('parts-val').innerText = '$' + parts.toFixed(2);
    document.getElementById('total-val').innerText = '$' + (labor + parts).toFixed(2);
});
</script>

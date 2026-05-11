
<?php

$tools = $data['tools'] ?? []; 

$status = $_GET['status'] ?? null;
?>


<link rel="stylesheet" href="../../assets/Css/lockout.css">

<div class="container">
    <div class="lock-card">
        <div class="alert-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>EMERGENCY LOCKOUT</h3>
        </div>
        
        <?php if ($status === 'success'): ?>
            <div style="color: var(--valid); margin-bottom: 20px; font-weight: bold;">
                <i class="fas fa-check-circle"></i> SYSTEM LOCK EXECUTED SUCCESSFULLY
            </div>
        <?php endif; ?>

        <p class="warning-text">
            Immediate cancellation of all upcoming reservations for tools failing safety checks. 
            <br><strong>Proceed with extreme caution.</strong>
        </p>

        
        <form action="../../Controllers/AdminController.php?action=emergency_lock" method="POST" onsubmit="return confirm('WARNING: This will cancel all reservations for this tool. Are you sure?');">
            
            <div class="select-group">
                <label for="lock_tool_id">IDENTIFY COMPROMISED TOOL</label>
                <select name="lock_tool_id" id="lock_tool_id" class="danger-input" required>
                    <option value="">-- Search Tool Database --</option>
                    
                    <?php if (!empty($tools)): ?>
                        <?php foreach ($tools as $tool): ?>
                            <option value="<?= $tool['id']; ?>">
                                <?= htmlspecialchars($tool['name']); ?> [SN: <?= $tool['serial']; ?>]
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                       
                        <option value="101">Concrete Mixer #CM-04</option>
                        <option value="102">Industrial Drill #ID-09</option>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn-lock">
                <i class="fas fa-skull-crossbones"></i> EXECUTE SYSTEM LOCK
            </button>
        </form>
    </div>
</div>

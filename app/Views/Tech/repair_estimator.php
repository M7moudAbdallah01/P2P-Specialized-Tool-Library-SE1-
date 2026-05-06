<link rel="stylesheet" href="../../assets/css/repair_estimator.css">

<div class="container">
    <section class="function-card">
        <header class="card-head">
            <span class="brand-icon"><i class="fas fa-tools"></i></span>
            <h2 class="brand-text">REPAIR COST ESTIMATOR</h2>
        </header>

        <form action="../../Controllers/RepairController.php" method="POST" class="dark-form">
            <div class="form-grid">
                <div class="field-box">
                    <label>TOOL IDENTIFICATION</label>
                    <select name="tool_id" class="custom-input">
                        <option value="1">Hilti Rotary Hammer (#H-99)</option>
                        <option value="2">Dewalt Table Saw (#D-42)</option>
                    </select>
                </div>
                <div class="field-box">
                    <label>DIAGNOSED ISSUE</label>
                    <select name="issue" class="custom-input">
                        <option>Motor Overheat</option>
                        <option>Bearing Replacement</option>
                        <option>Cable Fracture</option>
                    </select>
                </div>
            </div>

            <div class="summary-box">
                <div class="sum-row"><span>Labor Cost:</span> <span class="val">$50.00</span></div>
                <div class="sum-row"><span>Parts Estimate:</span> <span class="val">$110.00</span></div>
                <div class="sum-row total"><span>GRAND TOTAL:</span> <span class="val">$160.00</span></div>
            </div>

            <button type="submit" class="btn-action">SAVE ESTIMATE</button>
        </form>
    </section>
</div>

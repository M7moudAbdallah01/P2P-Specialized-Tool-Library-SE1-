<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Engine</title>
    <link rel="stylesheet" href="../../../assets/Css/auth.css">
    <link rel="stylesheet" href="../../../assets/Css/pricingEngine.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-wrapper">
        <div class="login-container pricing-container">

            <div class="logo">
                <i class="fa-solid fa-tags pricing-icon"></i>
            </div>

            <h2>Pricing Engine</h2>

            <!-- Tool Selection -->
            <div class="pricing-section">
                <div class="section-label">
                    <i class="fa-solid fa-wrench"></i> Select Tool
                </div>
                <div class="input-box">
                    <i class="fa-solid fa-box"></i>
                    <select id="toolSelect">
                        <option value="" disabled selected>Choose a tool...</option>
                        <option value="20">Cordless Drill — Base $20/hr</option>
                        <option value="35">Angle Grinder — Base $35/hr</option>
                        <option value="15">Electric Sander — Base $15/hr</option>
                        <option value="50">Laser Level — Base $50/hr</option>
                        <option value="45">Air Compressor — Base $45/hr</option>
                    </select>
                </div>
            </div>

            <!-- Duration Type -->
            <div class="pricing-section">
                <div class="section-label">
                    <i class="fa-solid fa-clock"></i> Duration Type
                </div>
                <div class="duration-tabs">
                    <button type="button" class="tab-btn active" data-type="hourly">
                        <i class="fa-regular fa-clock"></i> Hourly
                    </button>
                    <button type="button" class="tab-btn" data-type="daily">
                        <i class="fa-regular fa-calendar-days"></i> Daily
                    </button>
                    <button type="button" class="tab-btn" data-type="weekly">
                        <i class="fa-solid fa-calendar-week"></i> Weekly
                    </button>
                </div>
            </div>

            <!-- Duration Amount -->
            <div class="pricing-section">
                <div class="section-label">
                    <i class="fa-solid fa-hashtag"></i> Duration Amount
                </div>
                <div class="input-box">
                    <i class="fa-solid fa-hourglass-half"></i>
                    <input type="number" id="durationInput" min="1" value="1" placeholder="Enter duration...">
                    <span class="duration-unit" id="durationUnit">hrs</span>
                </div>
            </div>

            <!-- Membership -->
            <div class="pricing-section">
                <div class="section-label">
                    <i class="fa-solid fa-id-card"></i> Membership Tier
                </div>
                <div class="membership-grid">
                    <label class="membership-card" data-discount="0">
                        <input type="radio" name="membership" value="0" checked>
                        <div class="membership-inner">
                            <i class="fa-regular fa-user"></i>
                            <span class="tier-name">Standard</span>
                            <span class="tier-discount">No discount</span>
                        </div>
                    </label>
                    <label class="membership-card" data-discount="10">
                        <input type="radio" name="membership" value="10">
                        <div class="membership-inner">
                            <i class="fa-solid fa-user-check"></i>
                            <span class="tier-name">Silver</span>
                            <span class="tier-discount">10% off</span>
                        </div>
                    </label>
                    <label class="membership-card" data-discount="20">
                        <input type="radio" name="membership" value="20">
                        <div class="membership-inner">
                            <i class="fa-solid fa-star"></i>
                            <span class="tier-name">Gold</span>
                            <span class="tier-discount">20% off</span>
                        </div>
                    </label>
                    <label class="membership-card" data-discount="35">
                        <input type="radio" name="membership" value="35">
                        <div class="membership-inner">
                            <i class="fa-solid fa-crown"></i>
                            <span class="tier-name">Platinum</span>
                            <span class="tier-discount">35% off</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Calculate Button -->
            <button type="button" id="calculateBtn">
                <i class="fa-solid fa-calculator"></i> Calculate Price
            </button>

            <!-- Result -->
            <div class="pricing-result" id="pricingResult">
                <div class="result-row">
                    <span class="result-label">Base Rate</span>
                    <span class="result-value" id="resBaseRate">—</span>
                </div>
                <div class="result-row">
                    <span class="result-label">Duration</span>
                    <span class="result-value" id="resDuration">—</span>
                </div>
                <div class="result-row">
                    <span class="result-label">Subtotal</span>
                    <span class="result-value" id="resSubtotal">—</span>
                </div>
                <div class="result-row discount-row" id="discountRow">
                    <span class="result-label">Membership Discount</span>
                    <span class="result-value discount-value" id="resDiscount">—</span>
                </div>
                <div class="result-divider"></div>
                <div class="result-row total-row">
                    <span class="result-label">Total</span>
                    <span class="result-value total-value" id="resTotal">—</span>
                </div>
            </div>

        </div>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> ToolLibrary. All rights reserved.</p>
    </div>

    <script>
        const multipliers = { hourly: 1, daily: 8, weekly: 40 };
        const unitLabels  = { hourly: 'hrs', daily: 'days', weekly: 'weeks' };
        const typeLabels  = { hourly: 'Hour(s)', daily: 'Day(s)', weekly: 'Week(s)' };

        let currentType = 'hourly';

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentType = btn.dataset.type;
                document.getElementById('durationUnit').textContent = unitLabels[currentType];
            });
        });

        document.querySelectorAll('.membership-card').forEach(card => {
            card.querySelector('input').addEventListener('change', () => {
                document.querySelectorAll('.membership-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
            });
        });

        document.getElementById('calculateBtn').addEventListener('click', () => {
            const baseRate = parseFloat(document.getElementById('toolSelect').value);
            const duration = parseInt(document.getElementById('durationInput').value);
            const discount = parseInt(document.querySelector('input[name="membership"]:checked').value);

            if (!baseRate || isNaN(duration) || duration < 1) {
                document.getElementById('pricingResult').classList.add('visible', 'error-state');
                document.getElementById('resTotal').textContent = 'Please fill all fields';
                return;
            }

            const mult     = multipliers[currentType];
            const subtotal = baseRate * mult * duration;
            const saving   = subtotal * (discount / 100);
            const total    = subtotal - saving;

            document.getElementById('resBaseRate').textContent  = `$${baseRate}/hr`;
            document.getElementById('resDuration').textContent  = `${duration} ${typeLabels[currentType]}`;
            document.getElementById('resSubtotal').textContent  = `$${subtotal.toFixed(2)}`;
            document.getElementById('resDiscount').textContent  = discount > 0 ? `-$${saving.toFixed(2)} (${discount}%)` : 'None';
            document.getElementById('resTotal').textContent     = `$${total.toFixed(2)}`;

            const discountRow = document.getElementById('discountRow');
            discountRow.style.display = discount > 0 ? 'flex' : 'none';

            const result = document.getElementById('pricingResult');
            result.classList.remove('error-state');
            result.classList.add('visible');
        });
    </script>

</body>
</html>
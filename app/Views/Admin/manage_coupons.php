<div style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Promotional Campaigns Management (Function 23)</h2>

    <div style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 30px;">
        <h3>Create New Coupon</h3>
        <form action="" method="POST" style="display: flex; flex-wrap: wrap; gap: 15px;">
            <input type="hidden" name="add_coupon" value="1">
            
            <div>
                <label>Code:</label><br>
                <input type="text" name="code" placeholder="SUMMER2026" required style="padding: 8px;">
            </div>
            
            <div>
                <label>Discount (%):</label><br>
                <input type="number" name="discount_percent" min="1" max="100" required style="padding: 8px; width: 80px;">
            </div>

            <div>
                <label>Category Target:</label><br>
                <select name="category_id" style="padding: 8px;">
                    <option value="">Apply to All (Global)</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Start Date:</label><br>
                <input type="date" name="start_date" required style="padding: 7px;">
            </div>

            <div>
                <label>End Date:</label><br>
                <input type="date" name="end_date" required style="padding: 7px;">
            </div>

            <div style="align-self: flex-end;">
                <button type="submit" style="background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">Create Campaign</button>
            </div>
        </form>
    </div>

    <h3>Existing Campaigns</h3>
    <table border="1" width="100%" style="border-collapse: collapse; text-align: left;">
        <thead style="background: #333; color: white;">
            <tr>
                <th style="padding: 10px;">Code</th>
                <th style="padding: 10px;">Discount</th>
                <th style="padding: 10px;">Target Category</th>
                <th style="padding: 10px;">Validity</th>
                <th style="padding: 10px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($coupons)): foreach ($coupons as $coupon): ?>
                <tr>
                    <td style="padding: 10px;"><strong><?= htmlspecialchars($coupon['code']) ?></strong></td>
                    <td style="padding: 10px;"><?= $coupon['discount_percent'] ?>%</td>
                    <td style="padding: 10px;"><?= htmlspecialchars($coupon['category_name'] ?? 'Global') ?></td>
                    <td style="padding: 10px;"><?= $coupon['start_date'] ?> to <?= $coupon['end_date'] ?></td>
                    <td style="padding: 10px;">
                        <?php 
                        $today = date('Y-m-d');
                        if ($today > $coupon['end_date']) echo '<span style="color:red;">Expired</span>';
                        elseif ($today < $coupon['start_date']) echo '<span style="color:orange;">Pending</span>';
                        else echo '<span style="color:green;">Active</span>';
                        ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5" align="center" style="padding: 20px;">No campaigns found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
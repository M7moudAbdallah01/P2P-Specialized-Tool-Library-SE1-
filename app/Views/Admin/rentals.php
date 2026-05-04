<?php
// تأمين: لو المتغير مش موجود، روح للكنترولر الأول
if (!isset($rentals)) {
    header("Location: ../../Controllers/RentalController.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rental Escalation Tracking | Function 19</title>
    <!-- ربط ملف الـ CSS اللي عملناه -->
    <link rel="stylesheet" href="/P2P-Specialized-Tool-Library-SE1-/app/assets/Css/rentals.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

    <div class="header-section">
        <h2>Rental Escalation Tracking </h2>
        <p>Monitoring late returns and applying penalty tiers automatically.</p>
    </div>

    <table class="rental-table">
        <thead>
            <tr>
                <th>Rental ID</th>
                <th>User Name</th>
                <th>Tool Name</th>
                <th>Due Date</th>
                <th>Penalty Fee</th>
                <th>Escalation Level</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rentals)): ?>
                <?php foreach($rentals as $rental): ?>
                <tr>
                    <td>#<?php echo $rental['rental_id']; ?></td>
                    <td><?php echo htmlspecialchars($rental['user_name'] ?? 'Unknown'); ?></td>
                    <td><?php echo htmlspecialchars($rental['tool_name'] ?? 'Unknown'); ?></td>
                    <td><?php echo $rental['return_date']; ?></td>
                    <td class="level-<?php echo $rental['escalation_level']; ?>">
                        $<?php echo number_format($rental['penalty_fee'], 2); ?>
                    </td>
                    <td>
                        <span class="level-<?php echo $rental['escalation_level']; ?>">
                            Level <?php echo $rental['escalation_level']; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge"><?php echo strtoupper($rental['status']); ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 50px;">No rental records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
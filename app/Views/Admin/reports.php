<?php
require_once "../../Models/Report.php";

$reportModel = new Report();
$totalRevenue = $reportModel->getTotalRevenue();
$zoneRevenue = $reportModel->getRevenueByZone();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Reports | Tool Hub</title>
    <!-- الربط بالملف الخارجي مباشرة -->
    <link rel="stylesheet" href="../../assets/Css/user_management.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="welcome">
        <h1>Financial Dashboard</h1>
        <p>Real-time revenue tracking and zone performance.</p>
    </div>

    <!-- كروت الإحصائيات -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">$<?php echo number_format($totalRevenue, 2); ?></div>
            <div class="stat-label">Completed Transactions</div>
        </div>
        
        <div class="stat-card" style="border-left-color: var(--accent-yellow);">
            <div class="stat-label">Active Zones</div>
            <div class="stat-value"><?php echo count($zoneRevenue); ?></div>
            <div class="stat-label">Monitored Locations</div>
        </div>
    </div>

    <!-- جدول أرباح المناطق -->
    <div class="card">
        <h3 style="margin-top:0; color: #fff;">Revenue by Zone</h3>
        <table>
            <thead>
                <tr>
                    <th>Zone Name</th>
                    <th style="text-align: right;">Total Revenue</th>
                    <th style="text-align: right;">Performance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($zoneRevenue)): ?>
                    <?php foreach($zoneRevenue as $zone): ?>
                        <tr>
                            <td><span class="tool-name"><?php echo htmlspecialchars($zone['zone_name']); ?></span></td>
                            <td style="text-align: right; color: var(--accent-green); font-weight: 600;">
                                $<?php echo number_format($zone['revenue'], 2); ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="progress-bar-bg">
                                    <?php 
                                        $percentage = ($totalRevenue > 0) ? ($zone['revenue'] / $totalRevenue) * 100 : 0; 
                                    ?>
                                    <div class="progress-bar-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center; padding:20px;">No data available yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
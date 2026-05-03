<?php
require_once "../../Models/User.php";

$userModel = new User();
$users = $userModel->getAll();

// دي حركة صايعة بتجيب مسار المشروع من الـ URL عشان الـ CSS يظبط دايماً
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/P2P-Specialized-Tool-Library-SE1-";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management | Tool Hub</title>
    
    <!-- ربط الـ CSS باستخدام المسار الكامل للمشروع -->
   <link rel="stylesheet" href="../../assets/Css/user_management.css?v=<?php echo time(); ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="welcome">
        <h1>User Suspension & Blacklist</h1>
        <p>Manage user access and enforce community policies.</p>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>User Information</th>
                    <th>Current Status</th>
                    <th style="text-align: right;">Management Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach($users as $user): ?>
                        <tr>
                            <td>
                                <div class="tool-name"><?php echo htmlspecialchars($user['name']); ?></div>
                                <div class="tool-sub"><?php echo htmlspecialchars($user['email']); ?></div>
                            </td>
                            <td>
                                <?php 
                                    $status = $user['status'] ?? 'active';
                                    $color = ($status == 'active') ? '#4ade80' : (($status == 'suspended') ? '#fbbf24' : '#f87171');
                                ?>
                                <span class="status-tag" style="color: <?php echo $color; ?>;">
                                    ● <?php echo strtoupper($status); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" action="../../Controllers/UserController.php" style="display: inline-flex; gap: 8px;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    
                                    <?php if ($status != 'active'): ?>
                                        <button type="submit" name="action" value="activate" class="btn btn-green">Reactivate</button>
                                    <?php endif; ?>

                                    <?php if ($status != 'suspended'): ?>
                                        <button type="submit" name="action" value="suspend" class="btn btn-ghost">Suspend</button>
                                    <?php endif; ?>

                                    <?php if ($status != 'blacklisted'): ?>
                                        <button type="submit" name="action" value="blacklist" class="btn btn-red">Blacklist</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center; padding: 40px; color: #888;">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
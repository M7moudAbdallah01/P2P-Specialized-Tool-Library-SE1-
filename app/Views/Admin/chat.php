<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();

$admin_id = intval($_SESSION['user_id']);
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {

    $receiver_id = intval($_POST['receiver_id'] ?? 0);
    $content     = trim($_POST['content'] ?? '');

    if ($receiver_id > 0 && $content !== '') {

        $stmt = $conn->prepare("
            INSERT INTO messages (
                sender_id,
                receiver_id,
                content,
                encrypted,
                is_read
            ) VALUES (?, ?, ?, 0, 0)
        ");

        $stmt->bind_param(
            "iis",
            $admin_id,
            $receiver_id,
            $content
        );

        if ($stmt->execute()) {
            header("Location: chat.php?user=" . $receiver_id);
            exit();
        } else {
            $error = "Failed to send message.";
        }

        $stmt->close();
    }
}


$selected_user_id = intval($_GET['user'] ?? 0);

$users = $conn->query("
    SELECT 
        u.user_id,
        u.name,
        u.email,
        u.role,
        (
            SELECT COUNT(*)
            FROM messages m
            WHERE m.sender_id = u.user_id
              AND m.receiver_id = $admin_id
              AND m.is_read = 0
        ) AS unread_count
    FROM users u
    WHERE u.user_id != $admin_id
      AND u.role IN ('client','technical')
    ORDER BY unread_count DESC, u.name ASC
");

if ($selected_user_id > 0) {
    $conn->query("
        UPDATE messages
        SET is_read = 1
        WHERE sender_id = $selected_user_id
          AND receiver_id = $admin_id
          AND is_read = 0
    ");
}

$messages = null;

if ($selected_user_id > 0) {

    $stmt = $conn->prepare("
        SELECT 
            m.*,
            s.name AS sender_name,
            r.name AS receiver_name
        FROM messages m
        JOIN users s ON m.sender_id = s.user_id
        JOIN users r ON m.receiver_id = r.user_id
        WHERE 
            (m.sender_id = ? AND m.receiver_id = ?)
            OR
            (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.message_id ASC
    ");

    $stmt->bind_param(
        "iiii",
        $admin_id,
        $selected_user_id,
        $selected_user_id,
        $admin_id
    );

    $stmt->execute();
    $messages = $stmt->get_result();
}

$selected_user = null;

if ($selected_user_id > 0) {
    $res = $conn->query("
        SELECT *
        FROM users
        WHERE user_id = $selected_user_id
        LIMIT 1
    ");

    if ($res && $res->num_rows > 0) {
        $selected_user = $res->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Admin Chat</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/chat.css">

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge role-admin">ADMIN</div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link">
            <i class="fa fa-gauge"></i> Dashboard
        </a>
        <a href="../Tools/tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
        </a>
        <a href="../Tools/categories.php" class="nav-link">
            <i class="fa fa-layer-group"></i> Categories
        </a>
        <a href="members.php" class="nav-link">
            <i class="fa fa-users"></i> Members
        </a>
        <a href="reservations.php" class="nav-link">
            <i class="fa fa-calendar"></i> Reservations
        </a>
        <a href="chat.php" class="nav-link active">
            <i class="fa fa-comments"></i> Chat
        </a>
        <a href="reports.php" class="nav-link">
            <i class="fa fa-scale-balanced"></i> Reports
        </a>
    </div>
</div>

<div class="layout-right">

    <div class="topbar">
        <div class="topbar-title">Admin Chat</div>
        <div class="topbar-right">
            <button class="avatar-btn admin-avatar">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Admin</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <div class="main-content">

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="chat-layout">

            <div class="chat-users">
                <?php while ($u = $users->fetch_assoc()): ?>
                    <a href="chat.php?user=<?= $u['user_id'] ?>"
                       class="chat-user <?= $selected_user_id == $u['user_id'] ? 'active' : '' ?>">

                        <div class="chat-user-info">
                            <strong><?= htmlspecialchars($u['name']) ?></strong>
                            <small><?= ucfirst($u['role']) ?></small>
                        </div>

                        <?php if ($u['unread_count'] > 0): ?>
                            <span class="unread-badge"><?= $u['unread_count'] ?></span>
                        <?php endif; ?>

                    </a>
                <?php endwhile; ?>
            </div>

            <!-- CHAT -->
            <div class="chat-box">

                <div class="chat-header">
                    <?php if ($selected_user): ?>
                        Chat with <?= htmlspecialchars($selected_user['name']) ?>
                    <?php else: ?>
                        Select a user to start chatting
                    <?php endif; ?>
                </div>

                <div class="chat-messages">

                    <?php if (!$selected_user): ?>
                        <div class="empty-chat">
                            <i class="fa fa-comments" style="font-size:40px;margin-bottom:10px;"></i>
                            <p>Select a member from the left panel.</p>
                        </div>

                    <?php elseif ($messages && $messages->num_rows > 0): ?>

                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="message <?= $msg['sender_id'] == $admin_id ? 'admin' : 'user' ?>">
                                <?= nl2br(htmlspecialchars($msg['content'])) ?>
                            </div>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <div class="empty-chat">
                            No messages yet. Start the conversation.
                        </div>
                    <?php endif; ?>

                </div>

                <?php if ($selected_user): ?>
                <form method="POST" class="chat-form">
                    <input type="hidden" name="receiver_id" value="<?= $selected_user_id ?>">
                    <textarea name="content" rows="2" placeholder="Type your message..." required></textarea>
                    <button type="submit" name="send_message">
                        <i class="fa fa-paper-plane"></i>
                    </button>
                </form>
                <?php endif; ?>

            </div>

        </div>

    </div>
</div>

</body>
</html>
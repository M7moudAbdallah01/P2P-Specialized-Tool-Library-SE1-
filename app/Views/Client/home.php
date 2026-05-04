<?php
// session_start();
// require_once __DIR__ . "../../../../Core/database.php";

// $db   = Database::getInstance();
// $conn = $db->getConnection();

// /* ── Stats ── */
// $r           = $conn->query("SELECT COUNT(*) AS c FROM tools WHERE availability = 1");
// $total_tools = $r->fetch_assoc()['c'];

// $r           = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role IN ('client','technical')");
// $total_users = $r->fetch_assoc()['c'];

// $r            = $conn->query("SELECT COUNT(*) AS c FROM reservation");
// $total_rents  = $r->fetch_assoc()['c'];

// /* ── Categories ── */
// $categories = $conn->query("
//     SELECT c.*, COUNT(t.tool_id) AS tool_count
//     FROM category c
//     LEFT JOIN tools t ON t.category_id = c.category_id AND t.availability = 1
//     GROUP BY c.category_id
//     ORDER BY tool_count DESC
//     LIMIT 4
// ");

// /* ── Top Tools ── */
// $top_tools = $conn->query("
//     SELECT t.*, c.name AS cat_name
//     FROM tools t
//     JOIN category c ON t.category_id = c.category_id
//     WHERE t.availability = 1
//     ORDER BY t.created_at DESC
//     LIMIT 3
// ");

// $logged_in  = isset($_SESSION['user_id']);
// $action_url = $logged_in ? '../' . $_SESSION['role'] . '/dashboard.php' : '../Auth/login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Home</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../app/assets/Css/style.css">
<link rel="stylesheet" href="../app/assets/Css/admin.css">

<style>
/* ---- Hero ---- */
.hero {
    text-align: center;
    padding: 48px 24px 36px;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border-radius: 16px;
    margin-bottom: 28px;
    color: #fff;
}
.hero h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 10px;
}
.hero h1 span { color: #ef4444; }
.hero p { color: #9ca3af; margin-bottom: 24px; font-size: 0.95rem; }
.btn-main {
    display: inline-block;
    background: #ef4444;
    color: #fff;
    padding: 11px 28px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: background .15s;
}
.btn-main:hover { background: #b91c1c; }

/* ---- Section title ---- */
.section-block { margin-top: 32px; }
.section-block h2 {
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: 16px;
    color: #111;
}

/* ---- Stats grid ---- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px;
}

.stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px 16px;
    text-align: center;
    transition: box-shadow .18s;
}
.stat-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.08); }
.stat-card i {
    font-size: 1.4rem;
    color: #ef4444;
    margin-bottom: 10px;
    display: block;
}
.stat-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
.stat-card p  { font-size: 0.78rem; color: #9ca3af; margin: 0; }

/* ---- Category card ---- */
.cat-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px 18px;
    text-decoration: none;
    color: inherit;
    transition: border-color .15s, box-shadow .15s;
}
.cat-card:hover { border-color: #ef4444; box-shadow: 0 3px 14px rgba(239,68,68,.1); }
.cat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: #fee2e2;
    color: #ef4444;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.cat-name { font-size: 0.9rem; font-weight: 600; }
.cat-count { font-size: 0.74rem; color: #9ca3af; }

/* ---- How steps ---- */
.how-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 22px 16px;
    text-align: center;
}
.how-num {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #fee2e2;
    color: #ef4444;
    font-size: 1.1rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
}
.how-card h3 { font-size: 0.9rem; font-weight: 700; margin-bottom: 6px; }
.how-card p  { font-size: 0.78rem; color: #9ca3af; margin: 0; }

/* ---- Tool card ---- */
.tool-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 18px;
    display: flex; flex-direction: column; gap: 6px;
    transition: box-shadow .18s;
}
.tool-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.08); }
.tool-cat  { font-size: 0.72rem; color: #ef4444; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.tool-name { font-size: 0.94rem; font-weight: 700; }
.tool-foot { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; }
.tool-price { font-size: 1rem; font-weight: 700; }
.tool-price small { font-size: 0.74rem; color: #9ca3af; font-weight: 400; }
.btn-rent {
    background: #fee2e2; color: #b91c1c;
    border: none; border-radius: 8px;
    padding: 6px 13px; font-size: 0.78rem; font-weight: 600;
    cursor: pointer; text-decoration: none; font-family: inherit;
    transition: background .15s;
}
.btn-rent:hover { background: #ef4444; color: #fff; }

/* ---- Platform stats ---- */
.pstat-card {
    background: #1a1a1a;
    color: #fff;
    border-radius: 12px;
    padding: 22px;
    text-align: center;
}
.pstat-num { font-size: 1.8rem; font-weight: 800; color: #ef4444; }
.pstat-lbl { font-size: 0.78rem; color: #9ca3af; margin-top: 4px; }

/* ---- Footer ---- */
.home-footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
    color: #9ca3af;
    font-size: 0.82rem;
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../app/assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <div class="role-badge" style="background:#6b7280;color:#fff;">GUEST</div>

    <div class="sidebar-nav">
        <a href="home.php" class="nav-link active">
            <i class="fa fa-home"></i> Home
        </a>
        <a href="../Auth/login.php" class="nav-link">
            <i class="fa fa-right-to-bracket"></i> Login
        </a>
        <a href="../Auth/register.php" class="nav-link">
            <i class="fa fa-user-plus"></i> Register
        </a>
    </div>
</div>

<!-- RIGHT SIDE -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Welcome</div>
        <div class="topbar-right">
            <a href="../Auth/login.php"    class="btn btn-ghost btn-sm" style="margin-right:8px;">Log in</a>
            <a href="../Auth/register.php" class="btn btn-red  btn-sm">Register</a>
        </div>
    </div>

    <div class="main-content">

        <!-- HERO -->
        <div class="hero">
            <h1>Welcome to <span>Tool Hub</span> 👋</h1>
            <p>Rent, share and discover specialized tools — easily and securely.</p>
            <a href="<?= $action_url ?>" class="btn-main">
                <i class="fa fa-bolt"></i> Get Started
            </a>
        </div>

        <!-- FEATURES -->
        <div class="section-block">
            <h2>Why Tool Hub?</h2>
            <div class="stats-grid">

                <div class="stat-card">
                    <i class="fa fa-wrench"></i>
                    <h3>Share Tools</h3>
                    <p>List your tools and earn money</p>
                </div>

                <div class="stat-card">
                    <i class="fa fa-calendar-check"></i>
                    <h3>Fast Booking</h3>
                    <p>Reserve tools instantly</p>
                </div>

                <div class="stat-card">
                    <i class="fa fa-shield-halved"></i>
                    <h3>Trusted &amp; Secure</h3>
                    <p>Trust-score verified members</p>
                </div>

                <div class="stat-card">
                    <i class="fa fa-comments"></i>
                    <h3>Built-in Chat</h3>
                    <p>Message owners directly</p>
                </div>

            </div>
        </div>

        <!-- CATEGORIES -->
        <div class="section-block">
            <h2>Browse Categories</h2>
            <div class="stats-grid">
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <a href="../Auth/login.php" class="cat-card">
                    <div class="cat-icon"><i class="fa fa-layer-group"></i></div>
                    <div>
                        <div class="cat-name"><?= htmlspecialchars($cat['name']) ?></div>
                        <div class="cat-count"><?= $cat['tool_count'] ?> tools</div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- HOW IT WORKS -->
        <div class="section-block">
            <h2>How it Works</h2>
            <div class="stats-grid">

                <div class="how-card">
                    <div class="how-num">1</div>
                    <h3>Register Account</h3>
                    <p>Create your free account in seconds</p>
                </div>

                <div class="how-card">
                    <div class="how-num">2</div>
                    <h3>Browse Tools</h3>
                    <p>Find the tool you need by category</p>
                </div>

                <div class="how-card">
                    <div class="how-num">3</div>
                    <h3>Make Reservation</h3>
                    <p>Book instantly and chat with the owner</p>
                </div>

                <div class="how-card">
                    <div class="how-num">4</div>
                    <h3>Use &amp; Return</h3>
                    <p>Get the job done, return and review</p>
                </div>

            </div>
        </div>

        <!-- TOP TOOLS -->
        <div class="section-block">
            <h2>Latest Tools</h2>
            <div class="stats-grid">
                <?php while ($t = $top_tools->fetch_assoc()): ?>
                <div class="tool-card">
                    <div class="tool-cat"><?= htmlspecialchars($t['cat_name']) ?></div>
                    <div class="tool-name"><?= htmlspecialchars($t['name']) ?></div>
                    <div class="tool-foot">
                        <div class="tool-price">
                            $<?= $t['base_price'] ?> <small>/day</small>
                        </div>
                        <a href="../Auth/login.php" class="btn-rent">
                            <i class="fa fa-calendar-plus"></i> Rent
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- PLATFORM STATS -->
        <div class="section-block">
            <h2>Platform Stats</h2>
            <div class="stats-grid">

                <div class="pstat-card">
                    <div class="pstat-num"><?= $total_tools ?>+</div>
                    <div class="pstat-lbl">Tools Available</div>
                </div>

                <div class="pstat-card">
                    <div class="pstat-num"><?= $total_users ?>+</div>
                    <div class="pstat-lbl">Active Members</div>
                </div>

                <div class="pstat-card">
                    <div class="pstat-num"><?= $total_rents ?>+</div>
                    <div class="pstat-lbl">Successful Rentals</div>
                </div>

            </div>
        </div>

        <!-- FOOTER -->
        <div class="home-footer">
            &copy; <?= date('Y') ?> Tool Hub — All Rights Reserved.
        </div>

    </div><!-- /.main-content -->
</div><!-- /.layout-right -->

</body>
</html>
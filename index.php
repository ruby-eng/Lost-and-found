<?php
require_once 'config/db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Get user info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lost and Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        header nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            font-size: 28px;
        }
        header a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            transition: opacity 0.3s;
        }
        header a:hover {
            opacity: 0.8;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .welcome p {
            color: #666;
            font-size: 16px;
        }
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        .card h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .card a {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: transform 0.2s;
        }
        .card a:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>Lost & Found</h1>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                <a href="auth/logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="welcome">
            <h2>Welcome to Lost and Found</h2>
            <p>This is your central hub for reporting lost or found items, claiming items, and managing your reports.</p>
        </div>

        <div class="quick-links">
            <div class="card">
                <h3>📝 Report Lost Item</h3>
                <p>Report an item you've lost and we'll help you find it.</p>
                <a href="report_page/report-lost.html">Report Lost Item</a>
            </div>

            <div class="card">
                <h3>📦 Report Found Item</h3>
                <p>Report items you've found to help reunite them with owners.</p>
                <a href="report_page/report-found.html">Report Found Item</a>
            </div>

            <div class="card">
                <h3>🔍 Browse Lost Items</h3>
                <p>Browse items reported as lost by other users.</p>
                <a href="browse_lost.php">Browse Lost Items</a>
            </div>

            <div class="card">
                <h3>🎁 Browse Found Items</h3>
                <p>Browse items reported as found that are available.</p>
                <a href="browse_found.php">Browse Found Items</a>
            </div>

            <div class="card">
                <h3>📋 My Claims</h3>
                <p>View claims you've made on lost or found items.</p>
                <a href="my_claims.php">View My Claims</a>
            </div>

            <div class="card">
                <h3>✅ Claims on My Items</h3>
                <p>Review and verify claims made on your reported items.</p>
                <a href="review_claims.php">Review Claims</a>
            </div>
        </div>
    </div>
</body>
</html>

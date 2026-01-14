<?php
session_start();
$admin_password = "Tadoboy"; 
$storage_dir = "uploads/";
$data_file = "download_counts.json";

if (isset($_POST['login_pass']) && $_POST['login_pass'] === $admin_password) $_SESSION['admin'] = true;
if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }

$counts = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];

if (isset($_SESSION['admin'])) {
    // Upload
    if (isset($_FILES['file_upload'])) {
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $storage_dir . basename($_FILES['file_upload']['name']));
        $counts[basename($_FILES['file_upload']['name'])] = 0;
    }
    // Rename/Modify
    if (isset($_POST['old_name']) && isset($_POST['new_name'])) {
        rename($storage_dir . $_POST['old_name'], $storage_dir . $_POST['new_name']);
        $counts[$_POST['new_name']] = $counts[$_POST['old_name']];
        unset($counts[$_POST['old_name']]);
    }
    // Delete
    if (isset($_GET['delete'])) {
        unlink($storage_dir . $_GET['delete']);
        unset($counts[$_GET['delete']]);
    }
    file_put_contents($data_file, json_encode($counts));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tadoboy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&family=Roboto&display=swap" rel="stylesheet">
    <style>
        body { background: #000510; color: white; font-family: 'Roboto'; margin: 0; padding: 20px; }
        .admin-container { max-width: 800px; margin: auto; }
        .card { background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; border: 1px solid #00d2ff; margin-bottom: 20px; }
        input { padding: 10px; border-radius: 5px; border: 1px solid #00d2ff; background: transparent; color: white; }
        .btn { background: #00d2ff; color: black; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-family: 'Orbitron'; }
        .btn-red { background: #ff4444; color: white; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        td, th { padding: 15px; border-bottom: 1px solid #333; }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1 style="font-family: 'Orbitron'; color: #00d2ff;">ADMIN CONTROL PANEL</h1>
        
        <?php if (!isset($_SESSION['admin'])): ?>
            <div class="card">
                <form method="POST">
                    <input type="password" name="login_pass" placeholder="Admin Password">
                    <button type="submit" class="btn">LOGIN</button>
                </form>
            </div>
        <?php else: ?>
            <div class="card">
                <h3>UPLOAD NEW FILE</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="file_upload">
                    <button type="submit" class="btn">UPLOAD</button>
                    <a href="?logout=1" style="color:red; float:right;">LOGOUT</a>
                </form>
            </div>

            <div class="card">
                <h3>MANAGE FILES</h3>
                <table>
                    <?php $files = array_diff(scandir($storage_dir), array('.', '..'));
                    foreach ($files as $file): ?>
                    <tr>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="old_name" value="<?php echo $file; ?>">
                                <input type="text" name="new_name" value="<?php echo $file; ?>">
                                <button type="submit" class="btn" style="padding:5px 10px;">MODIFY</button>
                            </form>
                        </td>
                        <td><a href="?delete=<?php echo $file; ?>" class="btn btn-red" onclick="return confirm('Delete?')">DEL</a></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if(empty($username) || empty($password)){
        $error = "Semua field harus diisi.";
    } else {
        // Ambil user
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();
            if(password_verify($password, $hashed_password)){
                // Sukses login
                $_SESSION['user_id'] = $id;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username atau email tidak ditemukan.";
        }
        $stmt->close();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Login</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username atau Email</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

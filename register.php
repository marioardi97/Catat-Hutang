<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi
    if(empty($username) || empty($email) || empty($password) || empty($confirm_password)){
        $error = "Semua field harus diisi.";
    } elseif($password !== $confirm_password){
        $error = "Password dan konfirmasi password tidak cocok.";
    } else {
        // Cek apakah username atau email sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0){
            $error = "Username atau email sudah digunakan.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if($stmt->execute()){
                header("Location: login.php");
                exit();
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
        $stmt->close();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Register</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

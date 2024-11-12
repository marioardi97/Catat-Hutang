<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}
?>

<div class="text-center">
    <h1>Selamat Datang di Dashboard Hutang</h1>
    <p>Kelola hutang Anda dengan mudah dan dapatkan pengingat pembayaran tepat waktu.</p>
    <a href="register.php" class="btn btn-primary">Mulai Sekarang</a>
</div>

<?php require_once 'includes/footer.php'; ?>

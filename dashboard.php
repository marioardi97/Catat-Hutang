<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Handle penambahan hutang
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_debt'])){
    $description = trim($_POST['description']);
    $amount = $_POST['amount'];
    $due_date = $_POST['due_date'];

    if(empty($description) || empty($amount) || empty($due_date)){
        $error = "Semua field harus diisi.";
    } else {
        $stmt = $conn->prepare("INSERT INTO debts (user_id, description, amount, due_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $_SESSION['user_id'], $description, $amount, $due_date);
        if($stmt->execute()){
            // Redirect setelah data berhasil ditambahkan
            header("Location: dashboard.php");
            exit(); // Pastikan tidak ada kode lainnya yang dieksekusi setelah redirect
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
        }
        $stmt->close();
    }
}

// Handle update status
if(isset($_GET['action']) && $_GET['action'] == 'mark_paid' && isset($_GET['id'])){
    $debt_id = $_GET['id'];
    // Pastikan hutang tersebut milik user
    $stmt = $conn->prepare("UPDATE debts SET status='Lunas' WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $debt_id, $_SESSION['user_id']);
    if($stmt->execute()){
        header("Location: dashboard.php");
        exit();
    }
    $stmt->close();
}

// Ambil daftar hutang yang belum lunas
$stmt = $conn->prepare("SELECT id, description, amount, due_date, status FROM debts WHERE user_id=? AND status='Belum Dibayar' ORDER BY due_date ASC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$debts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil riwayat hutang yang sudah lunas
$stmt = $conn->prepare("SELECT id, description, amount, due_date, status FROM debts WHERE user_id=? AND status='Lunas' ORDER BY due_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$payed_debts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil hutang yang akan jatuh tempo dalam waktu 7 hari ke depan
$stmt = $conn->prepare("SELECT id, description, amount, due_date FROM debts WHERE user_id=? AND status='Belum Dibayar' AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY due_date ASC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$upcoming_debts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row">
    <div class="col-md-6">
        <h2>Tambah Hutang</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif(isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="dashboard.php" method="POST">
            <input type="hidden" name="add_debt" value="1">
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <input type="text" class="form-control" id="description" name="description" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Jumlah</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Tanggal Jatuh Tempo</label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambah</button>
        </form>
    </div>
    <div class="col-md-6">
        <h2>Daftar Hutang</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>Jumlah</th>
                    <th>Tanggal Jatuh Tempo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($debts) > 0): ?>
                    <?php foreach($debts as $debt): ?>
                        <tr <?php if(strtotime($debt['due_date']) < strtotime(date('Y-m-d')) && $debt['status'] != 'Lunas') echo 'class="table-danger"'; ?>>
                            <td><?php echo htmlspecialchars($debt['description']); ?></td>
                            <td>Rp <?php echo number_format($debt['amount'], 2, ',', '.'); ?></td>
                            <td><?php echo date('d M Y', strtotime($debt['due_date'])); ?></td>
                            <td>
                                <?php if($debt['status'] == 'Lunas'): ?>
                                    <span class="badge bg-success">Lunas</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Belum Dibayar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($debt['status'] != 'Lunas'): ?>
                                    <a href="dashboard.php?action=mark_paid&id=<?php echo $debt['id']; ?>" class="btn btn-success btn-sm">Tandai Lunas</a>
                                <?php else: ?>
                                    <span class="text-success">Lunas</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada hutang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pengingat Hutang Jatuh Tempo -->
<?php if(count($upcoming_debts) > 0): ?>
    <div class="alert alert-warning mt-4">
        <h4>Pengingat Pembayaran</h4>
        <ul>
            <?php foreach($upcoming_debts as $debt): ?>
                <li>
                    Hutang "<?php echo htmlspecialchars($debt['description']); ?>" sebesar Rp <?php echo number_format($debt['amount'], 2, ',', '.'); ?> 
                    jatuh tempo pada <?php echo date('d M Y', strtotime($debt['due_date'])); ?>.
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Riwayat Hutang Lunas -->
<div class="row mt-4">
    <div class="col-md-12">
        <h2>Riwayat Hutang (Lunas)</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>Jumlah</th>
                    <th>Tanggal Jatuh Tempo</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($payed_debts) > 0): ?>
                    <?php foreach($payed_debts as $debt): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($debt['description']); ?></td>
                            <td>Rp <?php echo number_format($debt['amount'], 2, ',', '.'); ?></td>
                            <td><?php echo date('d M Y', strtotime($debt['due_date'])); ?></td>
                            <td><span class="badge bg-success">Lunas</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada riwayat hutang lunas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

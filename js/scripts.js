function showNotification(message) {
    // Memeriksa apakah izin telah diberikan
    if (Notification.permission === "granted") {
        // Tampilkan notifikasi
        new Notification("Pengingat Pembayaran", { body: message });
    } else if (Notification.permission !== "denied") {
        // Meminta izin dari pengguna untuk menampilkan notifikasi
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                // Tampilkan notifikasi setelah izin diberikan
                new Notification("Pengingat Pembayaran", { body: message });
            }
        });
    }
}

function checkReminders() {
    const debtData = JSON.parse(localStorage.getItem("debtData")) || [];
    const currentDate = new Date();
    
    debtData.forEach(debt => {
        if (!debt.status) {
            const dueDate = new Date(debt.dueDate);
            const timeDifference = dueDate - currentDate;
            const daysRemaining = Math.ceil(timeDifference / (1000 * 60 * 60 * 24));
            
            if (daysRemaining > 0 && daysRemaining <= 7) {
                const message = `Hutang ${debt.username} jatuh tempo dalam ${daysRemaining} hari!`;
                showNotification(message);
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    // Menampilkan log ke konsol untuk memastikan skrip berjalan
    console.log("scripts.js loaded");

    // Cek apakah ada elemen pengingat pembayaran
    const alertBox = document.querySelector('.alert-warning');
    
    // Jika ada pengingat pembayaran (alert-warning), tampilkan notifikasi
    if (alertBox) {
        console.log("Ada pengingat pembayaran yang perlu dilihat!");
        // Tampilkan pengingat sebagai notifikasi
        setTimeout(function() {
            alert("Ada hutang yang jatuh tempo dalam 3 hari ke depan!");
        }, 3000); // Notifikasi muncul setelah 3 detik
    }
});


// Mengecek pengingat setiap 24 jam
setInterval(checkReminders, 86400000);

<?php
require_once 'functions.php';
cekLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

if (hapusKontak($id)) {
    $_SESSION['success_message'] = 'Kontak berhasil dihapus!';
} else {
    $_SESSION['error_message'] = 'Gagal menghapus kontak!';
}

header("Location: index.php");
exit();

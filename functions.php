<?php
session_start();

$data_file = __DIR__ . '/data/kontak.json';
$users_file = __DIR__ . '/data/users.json';

if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}

if (!file_exists($users_file)) {
    $default_users = [
        [
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'nama_tampilan' => 'Administrator'
        ]
    ];
    file_put_contents($users_file, json_encode($default_users, JSON_PRETTY_PRINT));
}

if (!file_exists($data_file)) {
    file_put_contents($data_file, json_encode([], JSON_PRETTY_PRINT));
}

function getKontak() {
    global $data_file;
    $data = file_get_contents($data_file);
    return json_decode($data, true) ?? [];
}

function saveKontak($data) {
    global $data_file;
    return file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
}

function getUsers() {
    global $users_file;
    $data = file_get_contents($users_file);
    return json_decode($data, true) ?? [];
}

function getKontakById($id) {
    $kontak_list = getKontak();
    foreach ($kontak_list as $kontak) {
        if ($kontak['id'] == $id) {
            return $kontak;
        }
    }
    return null;
}

function tambahKontak($data) {
    $kontak_list = getKontak();
    
    $max_id = 0;
    foreach ($kontak_list as $kontak) {
        if ($kontak['id'] > $max_id) {
            $max_id = $kontak['id'];
        }
    }
    
    $data['id'] = $max_id + 1;
    $data['tanggal_dibuat'] = date('Y-m-d H:i:s');
    $data['terakhir_diupdate'] = date('Y-m-d H:i:s');
    
    $kontak_list[] = $data;
    return saveKontak($kontak_list);
}

function updateKontak($id, $data) {
    $kontak_list = getKontak();
    
    foreach ($kontak_list as $key => $kontak) {
        if ($kontak['id'] == $id) {
            $data['id'] = $id;
            $data['tanggal_dibuat'] = $kontak['tanggal_dibuat'];
            $data['terakhir_diupdate'] = date('Y-m-d H:i:s');
            $kontak_list[$key] = $data;
            return saveKontak($kontak_list);
        }
    }
    
    return false;
}

function hapusKontak($id) {
    $kontak_list = getKontak();
    
    foreach ($kontak_list as $kontak) {
        if ($kontak['id'] == $id && !empty($kontak['foto']) && file_exists($kontak['foto'])) {
            unlink($kontak['foto']);
        }
    }
    
    $kontak_list = array_filter($kontak_list, function($k) use ($id) {
        return $k['id'] != $id;
    });
    
    return saveKontak(array_values($kontak_list));
}

function cekLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function bersihkanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validasiNomorTelepon($nomor) {
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    
    if (strlen($nomor) < 10 || strlen($nomor) > 13) {
        return false;
    }
    
    $prefix = substr($nomor, 0, 2);
    if ($prefix != '08' && $prefix != '62') {
        return false;
    }
    
    return true;
}

function formatNomorTelepon($nomor) {
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    
    if (substr($nomor, 0, 1) == '0') {
        return '+62' . substr($nomor, 1);
    } elseif (substr($nomor, 0, 2) == '62') {
        return '+' . $nomor;
    }
    
    return $nomor;
}

function searchKontak($search = '', $kategori = '') {
    $kontak_list = getKontak();
    
    if (empty($search) && empty($kategori)) {
        return $kontak_list;
    }
    
    $hasil = array_filter($kontak_list, function($kontak) use ($search, $kategori) {
        $match_search = true;
        $match_kategori = true;
        
        if (!empty($search)) {
            $search = strtolower($search);
            $match_search = (
                stripos($kontak['nama_lengkap'], $search) !== false ||
                stripos($kontak['nomor_telepon'], $search) !== false ||
                stripos($kontak['email'] ?? '', $search) !== false
            );
        }
        
        if (!empty($kategori)) {
            $match_kategori = ($kontak['kategori'] == $kategori);
        }
        
        return $match_search && $match_kategori;
    });
    
    return array_values($hasil);
}

function paginateKontak($kontak_list, $page = 1, $per_page = 8) {
    $total = count($kontak_list);
    $total_pages = ceil($total / $per_page);
    $offset = ($page - 1) * $per_page;
    
    $kontak_page = array_slice($kontak_list, $offset, $per_page);
    
    return [
        'data' => $kontak_page,
        'total' => $total,
        'total_pages' => $total_pages,
        'current_page' => $page
    ];
}

<?php
require_once 'functions.php';
cekLogin();

$errors = [];
$success = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data['nama_lengkap'] = bersihkanInput($_POST['nama_lengkap']);
    $form_data['nomor_telepon'] = bersihkanInput($_POST['nomor_telepon']);
    $form_data['email'] = bersihkanInput($_POST['email']);
    $form_data['alamat'] = bersihkanInput($_POST['alamat']);
    $form_data['kategori'] = bersihkanInput($_POST['kategori']);
    $form_data['catatan'] = bersihkanInput($_POST['catatan']);
    
    if (empty($form_data['nama_lengkap'])) {
        $errors[] = 'Nama lengkap harus diisi!';
    } elseif (!preg_match("/^[a-zA-Z\s\.]+$/", $form_data['nama_lengkap'])) {
        $errors[] = 'Nama hanya boleh berisi huruf dan spasi!';
    } elseif (strlen($form_data['nama_lengkap']) < 3) {
        $errors[] = 'Nama minimal 3 karakter!';
    }
    
    if (empty($form_data['nomor_telepon'])) {
        $errors[] = 'Nomor telepon harus diisi!';
    } elseif (!validasiNomorTelepon($form_data['nomor_telepon'])) {
        $errors[] = 'Format nomor telepon tidak valid! Gunakan format Indonesia (08xx atau 62xx)';
    }
    
    if (!empty($form_data['email'])) {
        if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid!';
        }
    }
    
    if (empty($form_data['kategori'])) {
        $errors[] = 'Kategori harus dipilih!';
    }
    
    $foto_path = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024;
        
        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            $errors[] = 'Format foto harus JPG, PNG, atau GIF!';
        } elseif ($_FILES['foto']['size'] > $max_size) {
            $errors[] = 'Ukuran foto maksimal 2MB!';
        } else {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $new_filename = 'contact_' . time() . '_' . uniqid() . '.' . $file_extension;
            $foto_path = $upload_dir . $new_filename;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path)) {
                $errors[] = 'Gagal mengupload foto!';
                $foto_path = '';
            }
        }
    }
    
    if (empty($errors)) {
        $form_data['nomor_telepon'] = formatNomorTelepon($form_data['nomor_telepon']);
        $form_data['foto'] = $foto_path;
        
        if (tambahKontak($form_data)) {
            $_SESSION['success_message'] = 'Kontak berhasil ditambahkan!';
            header("Location: index.php");
            exit();
        } else {
            $errors[] = 'Gagal menyimpan data!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kontak - Sistem Manajemen Kontak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out;
        }
        
        .form-section {
            animation: fadeIn 0.8s ease-out;
            animation-fill-mode: both;
        }
        
        .form-section:nth-child(1) { animation-delay: 0.1s; }
        .form-section:nth-child(2) { animation-delay: 0.2s; }
        .form-section:nth-child(3) { animation-delay: 0.3s; }
        .form-section:nth-child(4) { animation-delay: 0.4s; }
        
        input:focus, select:focus, textarea:focus {
            transform: scale(1.02);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-blue-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 border-b-4 border-blue-700">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-700 p-3 rounded-lg">
                        <i class="fas fa-address-book text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-blue-900">Sistem Manajemen Kontak</h1>
                        <p class="text-sm text-gray-600">Tambah Kontak Baru</p>
                    </div>
                </div>
                <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8 animate-fadeIn">
            <div class="bg-blue-700 rounded-2xl shadow-xl p-6 border-4 border-blue-900 text-white">
                <h2 class="text-3xl font-bold">
                    <i class="fas fa-user-plus mr-3"></i>Tambah Kontak Baru
                </h2>
                <p class="text-blue-100 mt-2">Lengkapi formulir di bawah ini untuk menambahkan kontak baru</p>
            </div>
        </div>
        
        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 animate-fadeIn">
                <div class="bg-red-100 border-l-4 border-red-500 rounded-lg p-6 shadow-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3 mt-1"></i>
                        <div class="flex-1">
                            <h3 class="text-red-800 font-bold text-lg mb-2">Terdapat Kesalahan!</h3>
                            <ul class="list-disc list-inside text-red-700 space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border-2 border-blue-200">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Informasi Pribadi -->
                <div class="form-section">
                    <h3 class="text-xl font-bold text-blue-900 mb-4 flex items-center">
                        <i class="fas fa-id-card text-blue-700 mr-2"></i>
                        Informasi Pribadi
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="transform transition-all duration-300 hover:scale-105">
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-user mr-2 text-blue-700"></i>Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_lengkap" 
                                   value="<?php echo htmlspecialchars($form_data['nama_lengkap'] ?? ''); ?>"
                                   required
                                   class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-all"
                                   placeholder="Contoh: Ahmad Rizki">
                        </div>
                        
                        <div class="transform transition-all duration-300 hover:scale-105">
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-tags mr-2 text-blue-700"></i>Kategori <span class="text-red-500">*</span>
                            </label>
                            <select name="kategori" 
                                    required
                                    class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-all">
                                <option value="">Pilih Kategori</option>
                                <option value="Keluarga" <?php echo (isset($form_data['kategori']) && $form_data['kategori'] == 'Keluarga') ? 'selected' : ''; ?>>👨‍👩‍👧‍👦 Keluarga</option>
                                <option value="Teman" <?php echo (isset($form_data['kategori']) && $form_data['kategori'] == 'Teman') ? 'selected' : ''; ?>>👥 Teman</option>
                                <option value="Kerja" <?php echo (isset($form_data['kategori']) && $form_data['kategori'] == 'Kerja') ? 'selected' : ''; ?>>💼 Kerja</option>
                                <option value="Bisnis" <?php echo (isset($form_data['kategori']) && $form_data['kategori'] == 'Bisnis') ? 'selected' : ''; ?>>🤝 Bisnis</option>
                                <option value="Lainnya" <?php echo (isset($form_data['kategori']) && $form_data['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>📌 Lainnya</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Informasi Kontak -->
                <div class="form-section border-t pt-6">
                    <h3 class="text-xl font-bold text-blue-900 mb-4 flex items-center">
                        <i class="fas fa-phone-alt text-blue-700 mr-2"></i>
                        Informasi Kontak
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="transform transition-all duration-300 hover:scale-105">
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-mobile-alt mr-2 text-blue-700"></i>Nomor Telepon <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   name="nomor_telepon" 
                                   value="<?php echo htmlspecialchars($form_data['nomor_telepon'] ?? ''); ?>"
                                   required
                                   class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-all"
                                   placeholder="Contoh: 08123456789">
                            <p class="text-xs text-gray-500 mt-1">Format: 08xx atau 62xx</p>
                        </div>
                        
                        <div class="transform transition-all duration-300 hover:scale-105">
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-envelope mr-2 text-blue-700"></i>Email
                            </label>
                            <input type="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-all"
                                   placeholder="Contoh: email@domain.com">
                        </div>
                    </div>
                </div>
                
                <!-- Alamat & Foto -->
                <div class="form-section border-t pt-6">
                    <h3 class="text-xl font-bold text-blue-900 mb-4 flex items-center">
                        <i class="fas fa-map-marked-alt text-blue-700 mr-2"></i>
                        Detail Tambahan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="transform transition-all duration-300 hover:scale-105">
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-home mr-2 text-blue-700"></i>Alamat Lengkap
                            </label>
                            <textarea name="alamat" 
                                      rows="4"
                                      class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-all"
                                      placeholder="Masukkan alamat lengkap..."><?php echo htmlspecialchars($form_data['alamat'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="transform transition-all duration-300 hover:scale-105">
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-camera mr-2 text-blue-700"></i>Foto Profil
                            </label>
                            <div class="border-2 border-dashed border-blue-300 rounded-lg p-4 text-center hover:border-blue-700 transition-colors">
                                <input type="file" 
                                       name="foto" 
                                       id="foto"
                                       accept="image/jpeg,image/png,image/gif"
                                       class="hidden"
                                       onchange="previewImage(this)">
                                <label for="foto" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-blue-400 mb-2"></i>
                                    <p class="text-gray-600">Klik untuk upload foto</p>
                                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF (Max 2MB)</p>
                                </label>
                                <div id="preview" class="mt-4 hidden">
                                    <img src="" alt="Preview" class="max-h-40 mx-auto rounded-lg shadow-lg">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Catatan -->
                <div class="form-section border-t pt-6">
                    <h3 class="text-xl font-bold text-blue-900 mb-4 flex items-center">
                        <i class="fas fa-sticky-note text-blue-700 mr-2"></i>
                        Catatan Tambahan
                    </h3>
                    <div class="transform transition-all duration-300 hover:scale-105">
                        <textarea name="catatan" 
                                  rows="3"
                                  class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-all"
                                  placeholder="Tambahkan catatan penting tentang kontak ini..."><?php echo htmlspecialchars($form_data['catatan'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <a href="index.php" class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-save mr-2"></i>Simpan Kontak
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewImg = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>

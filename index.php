<?php
require_once 'functions.php';
cekLogin();

date_default_timezone_set('Asia/Jakarta');

$items_per_page = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$search = isset($_GET['search']) ? bersihkanInput($_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? bersihkanInput($_GET['kategori']) : '';

$kontak_filtered = searchKontak($search, $kategori_filter);

usort($kontak_filtered, function($a, $b) {
    return strtotime($b['tanggal_dibuat']) - strtotime($a['tanggal_dibuat']);
});

$pagination = paginateKontak($kontak_filtered, $page, $items_per_page);
$kontak_list = $pagination['data'];
$total_items = $pagination['total'];
$total_pages = $pagination['total_pages'];

$all_kontak = getKontak();
$kategori_list = array_unique(array_filter(array_column($all_kontak, 'kategori')));
sort($kategori_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Manajemen Kontak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .animate-slideIn {
            animation: slideIn 0.5s ease-out;
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .gradient-primary {
            background: #1e40af;
        }
        
        .gradient-secondary {
            background: #f59e0b;
        }
        
        .gradient-success {
            background: #0891b2;
        }
        
        .stagger-animation > * {
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }
        
        .stagger-animation > *:nth-child(1) { animation-delay: 0.1s; }
        .stagger-animation > *:nth-child(2) { animation-delay: 0.2s; }
        .stagger-animation > *:nth-child(3) { animation-delay: 0.3s; }
        .stagger-animation > *:nth-child(4) { animation-delay: 0.4s; }
        .stagger-animation > *:nth-child(5) { animation-delay: 0.5s; }
        .stagger-animation > *:nth-child(6) { animation-delay: 0.6s; }
        .stagger-animation > *:nth-child(7) { animation-delay: 0.7s; }
        .stagger-animation > *:nth-child(8) { animation-delay: 0.8s; }
    </style>
</head>
<body class="bg-blue-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 animate-slideIn border-b-4 border-blue-700">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-700 p-3 rounded-lg">
                        <i class="fas fa-address-book text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-blue-900">Sistem Manajemen Kontak</h1>
                        <p class="text-sm text-gray-600">Kelola semua kontak Anda</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right hidden md:block">
                        <p class="text-sm text-gray-700 font-semibold">
                            <i class="fas fa-user-circle mr-1 text-blue-700"></i>
                            <?php echo htmlspecialchars($_SESSION['nama_tampilan']); ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-clock mr-1"></i>
                            Login: <?php echo date('d/m/Y H:i', $_SESSION['login_time']); ?>
                        </p>
                    </div>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-300 shadow-md hover:shadow-lg">
                        <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-6 animate-fadeIn">
                <div class="bg-green-100 border-l-4 border-green-500 rounded-lg p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 text-2xl mr-3"></i>
                            <p class="text-green-700 font-semibold"><?php echo $_SESSION['success_message']; ?></p>
                        </div>
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-green-500 hover:text-green-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-6 animate-fadeIn">
                <div class="bg-red-100 border-l-4 border-red-500 rounded-lg p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 text-2xl mr-3"></i>
                            <p class="text-red-700 font-semibold"><?php echo $_SESSION['error_message']; ?></p>
                        </div>
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- Header Section -->
        <div class="mb-8 animate-fadeInUp">
            <div class="bg-blue-700 rounded-2xl shadow-xl p-6 md:p-8 text-white border-4 border-blue-900">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-3xl font-bold mb-2">
                            <i class="fas fa-users mr-2"></i>Daftar Kontak
                        </h2>
                        <p class="text-blue-100">Total: <strong><?php echo $total_items; ?> kontak</strong></p>
                    </div>
                    <a href="tambah-kontak.php" class="bg-white text-blue-700 px-6 py-3 rounded-lg font-bold hover:bg-blue-50 transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Kontak Baru
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Search & Filter -->
        <div class="mb-6 animate-fadeInUp" style="animation-delay: 0.2s;">
            <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-blue-200">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-search mr-2 text-blue-700"></i>Cari Kontak
                        </label>
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Cari berdasarkan nama, telepon, atau email..."
                               class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-colors">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-filter mr-2 text-blue-700"></i>Kategori
                        </label>
                        <select name="kategori" class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-colors">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($kategori_list as $kat): ?>
                                <option value="<?php echo htmlspecialchars($kat); ?>" <?php echo $kategori_filter == $kat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-3 flex justify-end space-x-2">
                        <button type="submit" class="bg-blue-700 text-white px-6 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                            <i class="fas fa-search mr-2"></i>Cari
                        </button>
                        <a href="index.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Contact Grid -->
        <?php if (count($kontak_list) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 stagger-animation">
                <?php foreach ($kontak_list as $kontak): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover border-2 border-blue-200">
                        <div class="bg-blue-700 p-4 text-white text-center border-b-4 border-blue-900">
                            <div class="w-20 h-20 mx-auto mb-3 rounded-full bg-white flex items-center justify-center shadow-lg overflow-hidden">
                                <?php if (!empty($kontak['foto']) && file_exists($kontak['foto'])): ?>
                                    <img src="<?php echo htmlspecialchars($kontak['foto']); ?>" alt="Foto" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-user text-4xl text-blue-900"></i>
                                <?php endif; ?>
                            </div>
                            <h3 class="font-bold text-lg truncate"><?php echo htmlspecialchars($kontak['nama_lengkap']); ?></h3>
                            <?php if (!empty($kontak['kategori'])): ?>
                                <span class="inline-block bg-white text-blue-700 text-xs px-3 py-1 rounded-full mt-2 font-semibold">
                                    <?php echo htmlspecialchars($kontak['kategori']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4 space-y-2">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-phone text-blue-700 w-6"></i>
                                <span class="text-sm truncate"><?php echo htmlspecialchars($kontak['nomor_telepon']); ?></span>
                            </div>
                            <?php if (!empty($kontak['email'])): ?>
                                <div class="flex items-center text-gray-700">
                                    <i class="fas fa-envelope text-blue-700 w-6"></i>
                                    <span class="text-sm truncate"><?php echo htmlspecialchars($kontak['email']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($kontak['alamat'])): ?>
                                <div class="flex items-start text-gray-700">
                                    <i class="fas fa-map-marker-alt text-blue-700 w-6 mt-1"></i>
                                    <span class="text-sm line-clamp-2"><?php echo htmlspecialchars($kontak['alamat']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="border-t p-4 flex justify-between bg-gray-50">
                            <a href="edit-kontak.php?id=<?php echo $kontak['id']; ?>" 
                               class="bg-white text-blue-700 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors text-sm font-semibold">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <a href="hapus-kontak.php?id=<?php echo $kontak['id']; ?>" 
                               onclick="return confirm('Yakin ingin menghapus kontak ini?')"
                               class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm font-semibold">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="flex justify-center animate-fadeInUp">
                    <div class="bg-white rounded-xl shadow-lg p-4 border-2 border-blue-200">
                        <div class="flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($kategori_filter) ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" 
                                   class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($kategori_filter) ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" 
                                   class="px-4 py-2 rounded-lg transition-colors <?php echo $i == $page ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($kategori_filter) ? '&kategori=' . urlencode($kategori_filter) : ''; ?>" 
                                   class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-lg p-12 text-center animate-fadeInUp border-2 border-blue-200">
                <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-700 mb-2">Belum Ada Kontak</h3>
                <p class="text-gray-500 mb-6">Mulai tambahkan kontak pertama Anda</p>
                <a href="tambah-kontak.php" class="inline-block bg-blue-700 text-white px-6 py-3 rounded-lg hover:bg-blue-800 transition-colors">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Kontak
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <footer class="bg-white shadow-lg mt-12 py-6 border-t-4 border-blue-700">
        <div class="container mx-auto px-4 text-center text-gray-600">
            <p><i class="fas fa-code mr-2 text-blue-700"></i>Dibuat oleh Riski Jaya Putra</p>
            <p class="text-sm mt-2">© 2025 Sistem Manajemen Kontak. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

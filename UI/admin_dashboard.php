<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($pdo)) {
    require_once 'db.php';
}

$userName = $_SESSION['user_name'] ?? 'Admin';
$userEmail = $_SESSION['user_email'] ?? 'admin@moobix.com';

$totalMovies = 0;
$todaysBookings = 0;
$revenueToday = 0;
$activeUsers = 0;

if (isset($pdo)) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM movies");
    $totalMovies = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(transaction_date) = CURDATE()");
    $todaysBookings = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT SUM(total_price) FROM transactions WHERE DATE(transaction_date) = CURDATE()");
    $revenueToday = $stmt->fetchColumn() ?: 0; // Default to 0 if null

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
    $activeUsers = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'");
    $adminCount = $stmt->fetchColumn();
    $userCount = $activeUsers - $adminCount;

    $stmt = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(transaction_date) = SUBDATE(CURDATE(), 1)");
    $yesterdayBookings = $stmt->fetchColumn();

    $growthText = "0% from yesterday";
    if ($yesterdayBookings > 0) {
        $growth = (($todaysBookings - $yesterdayBookings) / $yesterdayBookings) * 100;
        $sign = ($growth > 0) ? '+' : '';
        $growthText = $sign . number_format($growth, 0) . "% from yesterday";
    } elseif ($todaysBookings > 0) {
        $growthText = "+100% from yesterday";
    }
}

$nowShowing = [];
$heroMovie = ['id' => 0, 'title' => 'No Data', 'poster' => '', 'genre' => '-', 'price' => 0, 'synopsis' => ''];

try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT * FROM movies ORDER BY id DESC");
        $nowShowing = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT * FROM movies WHERE is_featured = 1 ORDER BY id DESC LIMIT 1");
        $heroMovie = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$heroMovie) {
            if (!empty($nowShowing)) {
                $heroMovie = $nowShowing[0];
            }
        }
    }
} catch (Exception $e) {
}

if (!function_exists('getPoster')) {
    function getPoster($filename)
    {
        if (empty($filename)) return 'https://dummyimage.com/400x600/ffffff/fff';
        if (strpos($filename, 'http') === 0) return $filename;
        return 'uploads/' . $filename; // Sesuaikan path ini
    }
}

if (!function_exists('safe')) {
    function safe($array, $key, $default = '-')
    {
        return isset($array[$key]) ? htmlspecialchars($array[$key]) : $default;
    }
}
?>

<main id="admin-dashboard" style="padding-top: 100px;">
    <div class="section-header">
        <span>🎬 Admin Control Center</span>
        <h2>MOVIE MANAGEMENT PANEL</h2>
    </div>

    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">

        <div style="display: flex; align-items: center; margin-bottom: 30px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(170, 43, 43, 0.1);">
            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; margin-right: 25px;">
                <?php echo strtoupper(substr($userName, 0, 1)); ?>
            </div>
            <div style="flex: 1;">
                <h3 style="margin: 0; color: #333; font-size: 28px;">Welcome back, <?php echo htmlspecialchars($userName); ?>! 👋</h3>
                <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">Last login: <?php echo date('d M Y H:i', $_SESSION['login_time'] ?? time()); ?></p>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0; color: #666; font-size: 14px;">Role: <span style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px; font-weight: bold;">ADMINISTRATOR</span></p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px;">
            <div style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(170, 43, 43, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">🎞️ Total Movies</h4>
                        <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo count($nowShowing); ?></p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="ph ph-film-reel"></i>
                    </div>
                </div>
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;">1 featured movie</p>
            </div>

            <div style="background: linear-gradient(135deg, #c62828, #b71c1c); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(198, 40, 40, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">📅 Today's Bookings</h4>
                        <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo $todaysBookings; ?></p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="ph ph-ticket"></i>
                    </div>
                </div>
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;">
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;"><?php echo $growthText; ?></p>
                </p>
            </div>

            <div style="background: linear-gradient(135deg, #d32f2f, #aa2b2b); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(211, 47, 47, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">💰 Revenue Today</h4>
                        <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo number_format($revenueToday, 0, ',', '.'); ?></p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="ph ph-currency-circle-dollar"></i>
                    </div>
                </div>
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;">Average ticket: Rp 50,000</p>
            </div>

            <div style="background: linear-gradient(135deg, #e53935, #c62828); color: white; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(229, 57, 53, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; opacity: 0.9;">👥 Active Users</h4>
                        <p style="font-size: 36px; font-weight: bold; margin: 0;"><?php echo $activeUsers; ?></p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                        <i class="ph ph-users"></i>
                    </div>
                </div>
                <p style="margin: 15px 0 0 0; font-size: 14px; opacity: 0.9;"><?php echo $adminCount; ?> admins, <?php echo $userCount; ?> users</p>
            </div>
        </div>


        <div style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 20px rgba(170, 43, 43, 0.1); margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="color: #333; margin: 0; font-size: 24px; display: flex; align-items: center; gap: 10px;">
                    <i class="ph ph-film-script" style="color: #aa2b2b;"></i>
                    Current Movies Database
                </h3>
                <div style="display: flex; gap: 15px;">
                    <button onclick="openAddMovieModal()" style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: bold; transition: transform 0.3s, box-shadow 0.3s;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(170, 43, 43, 0.4)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <i class="ph ph-plus-circle" style="font-size: 18px;"></i>
                        Add New Movie
                    </button>

                    <button onclick="refreshMovies()" style="background: #f5f5f5; border: 1px solid #ddd; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.3s;"
                        onmouseover="this.style.background='#e9e9e9';"
                        onmouseout="this.style.background='#f5f5f5';">
                        <i class="ph ph-arrows-clockwise"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #0e0000ff, #ceb1b5ff); border-radius: 12px; padding: 20px; margin-bottom: 25px; border-left: 5px solid #aa2b2b;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <img src="<?php echo getPoster(safe($heroMovie, 'poster')); ?>" alt="Featured" style="width: 80px; height: 120px; object-fit: cover; border-radius: 8px; border: 3px solid #aa2b2b;" onerror="this.src='https://dummyimage.com/400x600/ffffff/fff'">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 5px 0; color: #000000ff; font-size: 20px;"><?php echo safe($heroMovie, 'title'); ?> <span style="background: #aa2b2b; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px; margin-left: 10px;">FEATURED</span></h4>
                        <p style="margin: 0 0 5px 0; color: #000000ff; font-size: 14px;">Genre: <?php echo safe($heroMovie, 'genre'); ?> | Duration: <?php echo safe($heroMovie, 'duration', '2h 0min'); ?> | Price: Rp <?php echo number_format((int)safe($heroMovie, 'price', 0), 0, ',', '.'); ?></p>
                        <p style="margin: 0; color: #000000ff; font-size: 13px; max-width: 600px;"><?php echo substr(safe($heroMovie, 'synopsis'), 0, 150); ?>...</p>
                    </div>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #2C1E1C, #1F1514); color: white;">
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444; width: 50px;">ID</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444; width: 400px;">Movie Title</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444;">Genre</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444;">Price</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444; width: 150px;">Status</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 2px solid #444; width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($nowShowing)): ?>
                            <tr>
                                <td colspan="6" style="padding: 30px; text-align: center; color: #888;">No other movies found in database.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($nowShowing as $movie): ?>
                                <?php if ($movie['id'] != $heroMovie['id']): ?>
                                    <tr style="border-bottom: 1px solid #eee; transition: background 0.3s;">
                                        <td style="padding: 15px; color: #000000ff; font-weight: bold;"><?php echo safe($movie, 'id'); ?></td>
                                        <td style="padding: 15px;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <img src="<?php echo getPoster(safe($movie, 'poster')); ?>" alt="Poster" style="width: 40px; height: 60px; object-fit: cover; border-radius: 4px;" onerror="this.src='https://dummyimage.com/400x600/ffffff/fff'">
                                                <span style="font-weight: 500; color: #333;"><?php echo safe($movie, 'title'); ?></span>
                                            </div>
                                        </td>
                                        <td style="padding: 15px; color: #666;"><?php echo safe($movie, 'genre'); ?></td>
                                        <td style="padding: 15px; color: #aa2b2b; font-weight: bold;">Rp <?php echo number_format((int)safe($movie, 'price', 0), 0, ',', '.'); ?></td>
                                        <td style="padding: 15px;">
                                            <?php
                                            $status = safe($movie, 'status', 'showing');
                                            $statusColor = ($status == 'showing') ? '#aa2b2b' : (($status == 'coming_soon') ? '#ff9800' : '#607d8b');
                                            $statusText = ($status == 'showing') ? 'SHOWING' : (($status == 'coming_soon') ? 'COMING SOON' : 'ARCHIVED');
                                            ?>
                                            <span style="background: <?php echo $statusColor; ?>; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;"><?php echo $statusText; ?></span>
                                        </td>
                                        <td style="padding: 15px;">
                                            <div style="display: flex; gap: 8px;">
                                                <button onclick="showEditModal(<?php echo safe($movie, 'id'); ?>)" style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px; transition: transform 0.2s;"
                                                    onmouseover="this.style.transform='translateY(-2px)'"
                                                    onmouseout="this.style.transform='translateY(0)'">
                                                    <i class="ph ph-pencil-simple"></i>
                                                </button>
                                                <button onclick="showDeleteModal(<?php echo safe($movie, 'id'); ?>, '<?php echo addslashes(safe($movie, 'title')); ?>')" style="background: linear-gradient(135deg, #c62828, #b71c1c); color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px; transition: transform 0.2s;"
                                                    onmouseover="this.style.transform='translateY(-2px)'"
                                                    onmouseout="this.style.transform='translateY(0)'">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                                <button onclick="showViewModal(<?php echo safe($movie, 'id'); ?>)" style="background: linear-gradient(135deg, #2C1E1C, #1F1514); color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px; transition: transform 0.2s;"
                                                    onmouseover="this.style.transform='translateY(-2px)'"
                                                    onmouseout="this.style.transform='translateY(0)'">
                                                    <i class="ph ph-eye"></i>
                                                </button>
                                                <button onclick="showFeatureModal(<?php echo safe($movie, 'id'); ?>, '<?php echo addslashes(safe($movie, 'title')); ?>')" style="background: linear-gradient(135deg, #ff9800, #ff5722); color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px; transition: transform 0.2s;"
                                                    onmouseover="this.style.transform='translateY(-2px)'"
                                                    onmouseout="this.style.transform='translateY(0)'">
                                                    <i class="ph ph-star"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div id="addMovieModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1001; align-items: center; justify-content: center; animation: fadeIn 0.3s ease; padding: 20px; overflow-y: auto;">
    <div style="background: linear-gradient(145deg, #ffffff, #f5f5f5); width: 95%; max-width: 900px; border-radius: 20px; padding: 40px 30px 30px; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(170, 43, 43, 0.3); border: 1px solid rgba(255,255,255,0.1);">

        <div style="position: absolute; top: 0; left: 0; right: 0; background: linear-gradient(135deg, #4caf50, #2e7d32); border-radius: 20px 20px 0 0; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="color: white; margin: 0; font-size: 24px; display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="ph ph-plus-circle" style="font-size: 20px;"></i>
                </div>
                Add New Movie
            </h2>
            <button onclick="closeModal('addMovieModal')" style="background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-size: 20px; transition: background 0.3s;"
                onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div style="margin-top: 70px; display: flex; gap: 30px;">
            <div style="flex: 1; min-width: 250px;">
                <div style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(170, 43, 43, 0.08); border: 1px solid #e0e0e0;">
                    <div style="position: relative; margin-bottom: 25px;">
                        <img id="addMoviePosterPreview" src="https://dummyimage.com/400x600/ffffff/fff" alt="Movie Poster Preview" style="width: 100%; height: 300px; object-fit: cover; border-radius: 12px; box-shadow: 0 8px 25px rgba(170, 43, 43, 0.15);">
                        <div style="position: absolute; bottom: 15px; left: 15px; background: rgba(76, 175, 80, 0.7); color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px; backdrop-filter: blur(5px);">
                            <i class="ph ph-image"></i> Poster Preview
                        </div>
                    </div>

                    <div style="background: #e8f5e9; border-radius: 12px; padding: 20px; border: 2px dashed #81c784; transition: border-color 0.3s;"
                        onmouseover="this.style.borderColor='#4caf50'"
                        onmouseout="this.style.borderColor='#81c784'">
                        <label style="display: block; text-align: center; cursor: pointer;">
                            <div style="color: #4caf50; font-size: 36px; margin-bottom: 10px;">
                                <i class="ph ph-upload-simple"></i>
                            </div>
                            <span style="color: #4caf50; font-weight: bold; margin-bottom: 8px; display: block;">Upload Poster</span>
                            <span style="color: #81c784; font-size: 13px; display: block;">Click to upload or drag & drop</span>
                            <span style="color: #81c784; font-size: 12px; margin-top: 5px; display: block;">JPG, PNG up to 5MB</span>
                            <input type="file" id="addPosterFile" accept="image/*" style="display: none;" onchange="previewAddImage(this)">
                        </label>
                    </div>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                        <label style="display: block; margin-bottom: 8px; color: #37474f; font-weight: 600; font-size: 14px;">Or use URL:</label>
                        <input type="text" id="addPosterUrl" placeholder="https://example.com/poster.jpg"
                            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px;"
                            onchange="updateAddPosterFromUrl()">
                    </div>
                </div>
            </div>

            <div style="flex: 2;">
                <form id="addMovieForm" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(170, 43, 43, 0.08); border: 1px solid #e0e0e0;">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #e8f5e9; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #4caf50;">
                                    <i class="ph ph-film-script"></i>
                                </div>
                                Movie Title *
                            </label>
                            <input type="text" id="addTitle" name="title" style="width: 100%; padding: 12px 15px; border: 2px solid #c8e6c9; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #f1f8e9;"
                                onfocus="this.style.borderColor='#4caf50'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(76, 175, 80, 0.1)'"
                                onblur="this.style.borderColor='#c8e6c9'; this.style.background='#f1f8e9'; this.style.boxShadow='none'" required>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #e8f5e9; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #4caf50;">
                                    <i class="ph ph-tag"></i>
                                </div>
                                Genre *
                            </label>
                            <select id="addGenre" name="genre" style="width: 100%; padding: 12px 15px; border: 2px solid #c8e6c9; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #f1f8e9; appearance: none;"
                                onfocus="this.style.borderColor='#4caf50'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(76, 175, 80, 0.1)'"
                                onblur="this.style.borderColor='#c8e6c9'; this.style.background='#f1f8e9'; this.style.boxShadow='none'" required>
                                <option value="">Select Genre</option>
                                <option value="Action">Action</option>
                                <option value="Adventure">Adventure</option>
                                <option value="Comedy">Comedy</option>
                                <option value="Drama">Drama</option>
                                <option value="Horror">Horror</option>
                                <option value="Romance">Romance</option>
                                <option value="Sci-Fi">Sci-Fi</option>
                                <option value="Thriller">Thriller</option>
                                <option value="Animation">Animation</option>
                                <option value="Documentary">Documentary</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            <div style="background: #e8f5e9; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #4caf50;">
                                <i class="ph ph-note"></i>
                            </div>
                            Synopsis *
                        </label>
                        <textarea id="addSynopsis" name="synopsis" rows="4" style="width: 100%; padding: 12px 15px; border: 2px solid #c8e6c9; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #f1f8e9; resize: vertical;"
                            onfocus="this.style.borderColor='#4caf50'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(76, 175, 80, 0.1)'"
                            onblur="this.style.borderColor='#c8e6c9'; this.style.background='#f1f8e9'; this.style.boxShadow='none'" required></textarea>
                        <p style="color: #81c784; font-size: 12px; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
                            <i class="ph ph-info"></i> Brief description of the movie plot
                        </p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #e8f5e9; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #4caf50;">
                                    <i class="ph ph-currency-circle-dollar"></i>
                                </div>
                                Price (Rp) *
                            </label>
                            <input type="number" id="addPrice" name="price" min="0" step="1000" placeholder="50000" style="width: 100%; padding: 12px 15px; border: 2px solid #c8e6c9; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #f1f8e9;"
                                onfocus="this.style.borderColor='#4caf50'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(76, 175, 80, 0.1)'"
                                onblur="this.style.borderColor='#c8e6c9'; this.style.background='#f1f8e9'; this.style.boxShadow='none'" required>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #e8f5e9; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #4caf50;">
                                    <i class="ph ph-clock"></i>
                                </div>
                                Duration *
                            </label>
                            <input type="text" id="addDuration" name="duration" placeholder="e.g., 2h 15m" style="width: 100%; padding: 12px 15px; border: 2px solid #c8e6c9; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #f1f8e9;"
                                onfocus="this.style.borderColor='#4caf50'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(76, 175, 80, 0.1)'"
                                onblur="this.style.borderColor='#c8e6c9'; this.style.background='#f1f8e9'; this.style.boxShadow='none'" required>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #e8f5e9; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #4caf50;">
                                    <i class="ph ph-trend-up"></i>
                                </div>
                                Status *
                            </label>
                            <select id="addStatus" name="status" style="width: 100%; padding: 12px 15px; border: 2px solid #c8e6c9; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #f1f8e9; appearance: none;"
                                onfocus="this.style.borderColor='#4caf50'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(76, 175, 80, 0.1)'"
                                onblur="this.style.borderColor='#c8e6c9'; this.style.background='#f1f8e9'; this.style.boxShadow='none'" required>
                                <option value="showing">🎬 Now Showing</option>
                                <option value="coming_soon">⏳ Coming Soon</option>
                                <option value="archived">📦 Archived</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; padding-top: 25px; border-top: 1px solid #eee;">
                        <button type="button" onclick="closeModal('addMovieModal')"
                            style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0); color: #666; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="ph ph-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit"
                            style="background: linear-gradient(135deg, #4caf50, #2e7d32); color: white; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(76, 175, 80, 0.4)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="ph ph-plus-circle"></i>
                            Add Movie
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="editMovieModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1001; align-items: center; justify-content: center; animation: fadeIn 0.3s ease; padding: 20px; overflow-y: auto;">
    <div style="background: linear-gradient(145deg, #ffffff, #f5f5f5); width: 95%; max-width: 900px; border-radius: 20px; padding: 40px 30px 30px; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(170, 43, 43, 0.3); border: 1px solid rgba(255,255,255,0.1);">

        <div style="position: absolute; top: 0; left: 0; right: 0; background: linear-gradient(135deg, #aa2b2b, #d32f2f); border-radius: 20px 20px 0 0; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="color: white; margin: 0; font-size: 24px; display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="ph ph-pencil-simple" style="font-size: 20px;"></i>
                </div>
                Edit Movie Details
            </h2>
            <button onclick="closeModal('editMovieModal')" style="background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-size: 20px; transition: background 0.3s;"
                onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div style="position: absolute; top: 15px; right: 70px; background: #d32f2f; color: white; padding: 8px 16px; border-radius: 25px; font-weight: bold; font-size: 14px; box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);">
            Movie ID: <span id="editMovieIdValue"></span>
        </div>

        <div style="margin-top: 70px; display: flex; gap: 30px;">
            <div style="flex: 1; min-width: 250px;">
                <div style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(170, 43, 43, 0.08); border: 1px solid #e0e0e0;">
                    <div style="position: relative; margin-bottom: 25px;">
                        <img id="editMoviePoster" src="" alt="Movie Poster" style="width: 100%; height: 300px; object-fit: cover; border-radius: 12px; box-shadow: 0 8px 25px rgba(170, 43, 43, 0.15);">
                        <div style="position: absolute; bottom: 15px; left: 15px; background: rgba(170, 43, 43, 0.7); color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px; backdrop-filter: blur(5px);">
                            <i class="ph ph-image"></i> Poster
                        </div>
                    </div>

                    <div style="background: #fff5f5; border-radius: 12px; padding: 20px; border: 2px dashed #ff8a80; transition: border-color 0.3s;"
                        onmouseover="this.style.borderColor='#d32f2f'"
                        onmouseout="this.style.borderColor='#ff8a80'">
                        <label style="display: block; text-align: center; cursor: pointer;">
                            <div style="color: #d32f2f; font-size: 36px; margin-bottom: 10px;">
                                <i class="ph ph-upload-simple"></i>
                            </div>
                            <span style="color: #d32f2f; font-weight: bold; margin-bottom: 8px; display: block;">Update Poster</span>
                            <span style="color: #ff8a80; font-size: 13px; display: block;">Click to upload or drag & drop</span>
                            <span style="color: #ff8a80; font-size: 12px; margin-top: 5px; display: block;">JPG, PNG up to 5MB</span>
                            <input type="file" id="editPosterFile" accept="image/*" style="display: none;" onchange="previewEditImage(this)">
                        </label>
                    </div>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                        <h3 id="editMovieTitle" style="color: #d32f2f; margin: 0; font-size: 18px; text-align: center; font-weight: 600;"></h3>
                    </div>
                </div>
            </div>

            <div style="flex: 2;">
                <form id="editMovieForm" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(170, 43, 43, 0.08); border: 1px solid #e0e0e0;">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">

                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #ffebee; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d32f2f;">
                                    <i class="ph ph-film-script"></i>
                                </div>
                                Movie Title
                            </label>
                            <div style="position: relative;">
                                <input type="text" id="editTitle" name="title" style="width: 100%; padding: 14px 14px 14px 45px; border: 2px solid #ffcdd2; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #fff5f5;"
                                    onfocus="this.style.borderColor='#d32f2f'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(211, 47, 47, 0.1)'"
                                    onblur="this.style.borderColor='#ffcdd2'; this.style.background='#fff5f5'; this.style.boxShadow='none'" required>
                                <div style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #d32f2f;">
                                    <i class="ph ph-textbox"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #ffebee; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d32f2f;">
                                    <i class="ph ph-tag"></i>
                                </div>
                                Genre
                            </label>
                            <div style="position: relative;">
                                <input type="text" id="editGenre" name="genre" style="width: 100%; padding: 14px 14px 14px 45px; border: 2px solid #ffcdd2; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #fff5f5;"
                                    onfocus="this.style.borderColor='#d32f2f'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(211, 47, 47, 0.1)'"
                                    onblur="this.style.borderColor='#ffcdd2'; this.style.background='#fff5f5'; this.style.boxShadow='none'" required>
                                <div style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #d32f2f;">
                                    <i class="ph ph-film-strip"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            <div style="background: #ffebee; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d32f2f;">
                                <i class="ph ph-note"></i>
                            </div>
                            Synopsis
                        </label>
                        <div style="position: relative;">
                            <textarea id="editSynopsis" name="synopsis" rows="4" style="width: 100%; padding: 14px 14px 14px 45px; border: 2px solid #ffcdd2; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #fff5f5; resize: vertical;"
                                onfocus="this.style.borderColor='#d32f2f'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(211, 47, 47, 0.1)'"
                                onblur="this.style.borderColor='#ffcdd2'; this.style.background='#fff5f5'; this.style.boxShadow='none'"></textarea>
                            <div style="position: absolute; left: 15px; top: 15px; color: #d32f2f;">
                                <i class="ph ph-align-left"></i>
                            </div>
                        </div>
                        <p style="color: #ff8a80; font-size: 12px; margin-top: 8px; display: flex; align-items: center; gap: 5px;">
                            <i class="ph ph-info"></i> Brief description of the movie plot
                        </p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">

                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #ffebee; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d32f2f;">
                                    <i class="ph ph-currency-circle-dollar"></i>
                                </div>
                                Price (Rp)
                            </label>
                            <div style="position: relative;">
                                <input type="number" id="editPrice" name="price" style="width: 100%; padding: 14px 14px 14px 45px; border: 2px solid #ffcdd2; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #fff5f5;"
                                    onfocus="this.style.borderColor='#d32f2f'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(211, 47, 47, 0.1)'"
                                    onblur="this.style.borderColor='#ffcdd2'; this.style.background='#fff5f5'; this.style.boxShadow='none'" required>
                                <div style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #d32f2f;">
                                    <i class="ph ph-money"></i>
                                </div>
                                <div style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #d32f2f; font-weight: bold;">
                                    IDR
                                </div>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #ffebee; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d32f2f;">
                                    <i class="ph ph-clock"></i>
                                </div>
                                Duration
                            </label>
                            <div style="position: relative;">
                                <input type="text" id="editDuration" name="duration" placeholder="e.g., 2h 15m" style="width: 100%; padding: 14px 14px 14px 45px; border: 2px solid #ffcdd2; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #fff5f5;"
                                    onfocus="this.style.borderColor='#d32f2f'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(211, 47, 47, 0.1)'"
                                    onblur="this.style.borderColor='#ffcdd2'; this.style.background='#fff5f5'; this.style.boxShadow='none'">
                                <div style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #d32f2f;">
                                    <i class="ph ph-timer"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; margin-bottom: 10px; color: #37474f; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                <div style="background: #ffebee; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d32f2f;">
                                    <i class="ph ph-trend-up"></i>
                                </div>
                                Status
                            </label>
                            <div style="position: relative;">
                                <select id="editStatus" name="status" style="width: 100%; padding: 14px 14px 14px 45px; border: 2px solid #ffcdd2; border-radius: 10px; font-size: 15px; transition: all 0.3s; background: #fff5f5; appearance: none; cursor: pointer;"
                                    onfocus="this.style.borderColor='#d32f2f'; this.style.background='white'; this.style.boxShadow='0 0 0 3px rgba(211, 47, 47, 0.1)'"
                                    onblur="this.style.borderColor='#ffcdd2'; this.style.background='#fff5f5'; this.style.boxShadow='none'">
                                    <option value="showing" style="padding: 10px;">🎬 Now Showing</option>
                                    <option value="coming_soon" style="padding: 10px;">⏳ Coming Soon</option>
                                    <option value="archived" style="padding: 10px;">📦 Archived</option>
                                </select>
                                <div style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #d32f2f;">
                                    <i class="ph ph-bar-chart"></i>
                                </div>
                                <div style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #d32f2f; pointer-events: none;">
                                    <i class="ph ph-caret-down"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; padding-top: 25px; border-top: 1px solid #eee;">
                        <button type="button" onclick="closeModal('editMovieModal')"
                            style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0); color: #666; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="ph ph-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit"
                            style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(170, 43, 43, 0.4)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="ph ph-floppy-disk"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="deleteMovieModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1001; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
    <div style="background: linear-gradient(145deg, #ffffff, #f5f5f5); width: 90%; max-width: 500px; border-radius: 20px; padding: 0; overflow: hidden; box-shadow: 0 25px 50px rgba(170, 43, 43, 0.3); border: 1px solid rgba(255,255,255,0.1);">
        <div style="background: linear-gradient(135deg, #c62828, #b71c1c); padding: 30px; text-align: center; position: relative;">
            <div style="position: absolute; top: 5px; left: 50%; transform: translateX(-50%); background: #c62828; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 20px rgba(198, 40, 40, 0.4);">
                <i class="ph ph-warning-circle" style="font-size: 24px; color: white;"></i>
            </div>
            <h2 style="color: white; margin: 20px 0 10px 0; font-size: 26px;">Confirm Deletion</h2>
            <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 15px;">Permanent action - cannot be undone</p>
        </div>

        <div style="padding: 40px 30px 30px; text-align: center;">

            <div style="background: #ffebee; border-radius: 15px; padding: 20px; margin-bottom: 30px; border-left: 5px solid #c62828;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="background: #c62828; width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="ph ph-film-slate" style="font-size: 24px; color: white;"></i>
                    </div>
                    <div style="text-align: left; flex: 1;">
                        <h4 id="deleteMovieTitle" style="color: #333; margin: 0 0 5px 0; font-size: 18px;"></h4>
                        <p style="color: #666; margin: 0; font-size: 14px;">
                            Movie ID: <span id="deleteMovieId" style="font-weight: bold; color: #c62828;"></span>
                        </p>
                    </div>
                </div>
            </div>

            <div style="background: #ffebee; border-radius: 12px; padding: 20px; margin-bottom: 30px; text-align: left;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="color: #c62828; font-size: 20px; margin-top: 2px;">
                        <i class="ph ph-info"></i>
                    </div>
                    <div>
                        <h4 style="color: #c62828; margin: 0; font-size: 15px;">Confirm Deletion?</h4>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: center; gap: 15px;">
                <button onclick="closeModal('deleteMovieModal')"
                    style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0); color: #666; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="ph ph-x-circle"></i>
                    Cancel
                </button>
                <button onclick="processDelete()"
                    style="background: linear-gradient(135deg, #c62828, #b71c1c); color: white; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(198, 40, 40, 0.4)'"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="ph ph-trash-simple"></i>
                    Delete Permanently
                </button>
            </div>

            <p style="color: #ff8a80; font-size: 12px; margin-top: 25px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="ph ph-lock-key"></i>
                This action requires administrator confirmation
            </p>
        </div>
    </div>
</div>

<div id="viewMovieModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1001; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
    <div style="background: linear-gradient(145deg, #ffffff, #f5f5f5); width: 95%; max-width: 800px; border-radius: 20px; padding: 0; overflow: hidden; box-shadow: 0 25px 50px rgba(170, 43, 43, 0.3); border: 1px solid rgba(255,255,255,0.1); max-height: 90vh; overflow-y: auto;">

        <div style="background: linear-gradient(135deg, #c62828, #c62828); padding: 25px 30px; position: relative;">
            <button onclick="closeModal('viewMovieModal')" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-size: 20px; transition: background 0.3s;"
                onmouseover="this.style.background='rgba(255,255,255,0.3)'"
                onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                <i class="ph ph-x"></i>
            </button>

            <h2 style="color: white; margin: 0; font-size: 24px; display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <i class="ph ph-eye" style="font-size: 20px;"></i>
                </div>
                Movie Details
            </h2>
        </div>

        <div style="padding: 30px;">
            <div style="display: flex; gap: 30px;">
                <div style="flex: 1; min-width: 250px;">
                    <div style="position: relative; margin-bottom: 25px;">
                        <img id="viewMoviePoster" src="" alt="Movie Poster" style="width: 100%; height: 350px; object-fit: cover; border-radius: 15px; box-shadow: 0 15px 35px rgba(170, 43, 43, 0.2);">
                        <div style="position: absolute; bottom: -15px; right: -15px; background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; padding: 12px 20px; border-radius: 12px; font-weight: bold; font-size: 14px; box-shadow: 0 8px 20px rgba(170, 43, 43, 0.4);">
                            MOVIE DETAILS
                        </div>
                    </div>

                    <div style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(170, 43, 43, 0.08); border: 1px solid #e0e0e0;">
                        <h4 style="color: #333; margin: 0 0 20px 0; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                            <div style="background: #ffebee; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d32f2f;">
                                <i class="ph ph-chart-bar"></i>
                            </div>
                            Movie Statistics
                        </h4>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div style="text-align: center;">
                                <div style="background: #ffebee; border-radius: 10px; padding: 12px; margin-bottom: 8px;">
                                    <i class="ph ph-identification-card" style="color: #d32f2f; font-size: 20px;"></i>
                                </div>
                                <span style="color: #666; font-size: 12px; display: block;">Movie ID</span>
                                <span id="viewMovieId" style="color: #d32f2f; font-weight: bold; font-size: 16px;">-</span>
                            </div>

                            <div style="text-align: center;">
                                <div style="background: #ffebee; border-radius: 10px; padding: 12px; margin-bottom: 8px;">
                                    <i class="ph ph-calendar" style="color: #d32f2f; font-size: 20px;"></i>
                                </div>
                                <span style="color: #666; font-size: 12px; display: block;">Status</span>
                                <span id="viewMovieStatus" style="color: #d32f2f; font-weight: bold; font-size: 14px;">SHOWING</span>
                            </div>

                            <div style="text-align: center;">
                                <div style="background: #ffebee; border-radius: 10px; padding: 12px; margin-bottom: 8px;">
                                    <i class="ph ph-ticket" style="color: #d32f2f; font-size: 20px;"></i>
                                </div>
                                <span style="color: #666; font-size: 12px; display: block;">Price</span>
                                <span id="viewMoviePrice" style="color: #d32f2f; font-weight: bold; font-size: 16px;">Rp 0</span>
                            </div>

                            <div style="text-align: center;">
                                <div style="background: #ffebee; border-radius: 10px; padding: 12px; margin-bottom: 8px;">
                                    <i class="ph ph-star" style="color: #d32f2f; font-size: 20px;"></i>
                                </div>
                                <span style="color: #666; font-size: 12px; display: block;">Featured</span>
                                <span id="viewMovieFeatured" style="color: #d32f2f; font-weight: bold; font-size: 16px;">No</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="flex: 2;">
                    <div style="margin-bottom: 25px;">
                        <h3 id="viewMovieTitle" style="color: #d32f2f; margin: 0 0 15px 0; font-size: 28px; font-weight: 700;"></h3>

                        <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
                            <span id="viewMovieGenre" style="background: linear-gradient(135deg, #ffcdd2, #ff8a80); color: #d32f2f; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                <i class="ph ph-tag"></i>
                                <span id="viewMovieGenreText"></span>
                            </span>

                            <span id="viewMovieDuration" style="background: linear-gradient(135deg, #ffcdd2, #ff8a80); color: #d32f2f; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                <i class="ph ph-clock"></i>
                                <span id="viewMovieDurationText"></span>
                            </span>
                        </div>
                    </div>

                    <div style="background: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(170, 43, 43, 0.08); border: 1px solid #e0e0e0;">
                        <h4 style="color: #333; margin: 0 0 15px 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                            <div style="background: #ffebee; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #d32f2f;">
                                <i class="ph ph-book-open-text"></i>
                            </div>
                            Synopsis
                        </h4>
                        <p id="viewMovieSynopsis" style="color: #546e7a; line-height: 1.7; margin: 0; font-size: 15px;"></p>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button onclick="closeModal('viewMovieModal')"
                            style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0); color: #666; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(0,0,0,0.1)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="ph ph-x-circle"></i>
                            Close
                        </button>
                        <button onclick="showEditModal(currentViewingId)"
                            style="background: linear-gradient(135deg, #aa2b2b, #d32f2f); color: white; border: none; padding: 12px 28px; border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: all 0.3s;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(170, 43, 43, 0.4)'"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="ph ph-pencil-simple"></i>
                            Edit Movie
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="featureMovieModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; animation: fadeIn 0.3s ease;">
    
    <div style="background: linear-gradient(145deg, #ffffff, #f5f5f5); width: 95%; max-width: 750px; border-radius: 15px; overflow: hidden; box-shadow: 0 25px 50px rgba(170, 43, 43, 0.3); border: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column;">

        <div style="background: linear-gradient(135deg, #ff9800, #ff5722); padding: 15px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="ph ph-crown-simple" style="font-size: 20px; color: white;"></i>
                </div>
                <div>
                    <h2 style="color: white; margin: 0; font-size: 20px; font-weight: bold;">Feature Movie</h2>
                    <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 13px;">Promote to main spotlight</p>
                </div>
            </div>
            <button onclick="closeModal('featureMovieModal')" style="background: rgba(255,255,255,0.2); border: none; width: 32px; height: 32px; border-radius: 8px; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i class="ph ph-x" style="font-size: 18px;"></i>
            </button>
        </div>

        <div style="padding: 25px; display: flex; flex-wrap: wrap; gap: 25px;">

            <div style="flex: 1; min-width: 260px;">
                <div style="background: white; border-radius: 15px; padding: 20px; border: 2px solid #ffe0b2; position: relative; height: 100%; box-sizing: border-box; text-align: center;">
                    
                    <div style="position: absolute; top: -10px; left: 15px; background: #ff9800; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold;">
                        <i class="ph ph-star"></i> PREVIEW
                    </div>

                    <div style="width: 110px; height: 160px; background: #f5f5f5; border-radius: 10px; overflow: hidden; margin: 10px auto 15px auto; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                        <img id="featureMoviePoster" src="" alt="Poster" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://dummyimage.com/400x600/ffffff/fff'">
                    </div>

                    <h4 id="featureMovieTitle" style="color: #333; margin: 0 0 8px 0; font-size: 18px; font-weight: 600; line-height: 1.3;"></h4>
                    
                    <div style="display: flex; justify-content: center; gap: 6px; margin-bottom: 12px; flex-wrap: wrap;">
                        <span id="featureMovieGenre" style="background: #fff3e0; color: #ff9800; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 500;"></span>
                        <span id="featureMovieDuration" style="background: #fff3e0; color: #ff9800; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 500;"></span>
                    </div>

                    <div style="border-top: 1px dashed #ddd; padding-top: 10px; margin-top: 10px; font-size: 13px;">
                        <p style="color: #666; margin: 0 0 5px 0;">ID: <span id="featureMovieId" style="font-weight: bold; color: #ff9800;"></span></p>
                        <p id="featureMoviePrice" style="color: #d32f2f; margin: 0; font-weight: bold;"></p>
                    </div>
                </div>
            </div>

            <div style="flex: 1.3; min-width: 280px; display: flex; flex-direction: column; gap: 20px;">

                <div>
                    <h4 style="color: #333; margin: 0 0 10px 0; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                        <i class="ph ph-calendar" style="color: #ff9800;"></i> Select Duration
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <button class="feature-period-btn active" data-days="7" onclick="selectFeaturePeriod(this, 7)" style="background: linear-gradient(135deg, #ff9800, #ff5722); color: white; border: none; padding: 12px 5px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 13px;">7 Days</button>
                        <button class="feature-period-btn" data-days="14" onclick="selectFeaturePeriod(this, 14)" style="background: #f5f5f5; color: #666; border: 1px solid #ddd; padding: 12px 5px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 13px;">14 Days</button>
                        <button class="feature-period-btn" data-days="30" onclick="selectFeaturePeriod(this, 30)" style="background: #f5f5f5; color: #666; border: 1px solid #ddd; padding: 12px 5px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 13px;">30 Days</button>
                    </div>
                </div>

                <div style="background: #fff8e1; border-radius: 12px; padding: 15px;">
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <i class="ph ph-check-circle" style="color: #4caf50; font-size: 18px;"></i>
                        <div>
                            <span style="font-size: 13px; font-weight: 600; color: #333;">Homepage Hero Section</span>
                            <div style="font-size: 12px; color: #666;">Will replace: <span id="currentFeaturedMovie" style="font-weight: bold; color: #e65100;"><?php echo safe($heroMovie, 'title'); ?></span></div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: auto;">
                    <button onclick="processFeature()" style="width: 100%; background: linear-gradient(135deg, #ff9800, #ff5722); color: white; border: none; padding: 14px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(255, 87, 34, 0.3);">
                        <i class="ph ph-crown-simple" style="font-size: 18px;"></i>
                        Feature This Movie
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    @media (max-width: 768px) {

        #addMovieModal>div,
        #editMovieModal>div,
        #viewMovieModal>div,
        #featureMovieModal>div,
        #deleteMovieModal>div {
            width: 95% !important;
            margin: 10px !important;
            padding: 15px !important;
        }

        #addMovieModal>div>div,
        #editMovieModal>div>div,
        #viewMovieModal>div>div {
            flex-direction: column !important;
            gap: 20px !important;
        }

        #addMovieModal>div>div>div,
        #editMovieModal>div>div>div,
        #viewMovieModal>div>div>div {
            min-width: 100% !important;
        }
    }

    .feature-period-btn.active {
        background: linear-gradient(135deg, #ff9800, #ff5722) !important;
        color: white !important;
        border: none !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3) !important;
    }
</style>

<script>
    let currentViewingId = null;
    let currentFeatureMovieId = null;
    let selectedFeatureDays = 7;

    function openAddMovieModal() {
        // Reset form
        document.getElementById('addMovieForm').reset();
        document.getElementById('addMoviePosterPreview').src = 'https://dummyimage.com/400x600/ffffff/fff';
        document.getElementById('addPosterUrl').value = '';

        const modal = document.getElementById('addMovieModal');
        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.3s ease';
    }

    function previewAddImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('addMoviePosterPreview').src = e.target.result;
                document.getElementById('addMoviePosterPreview').style.animation = 'pulse 0.5s ease';
                setTimeout(() => {
                    document.getElementById('addMoviePosterPreview').style.animation = '';
                }, 500);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function updateAddPosterFromUrl() {
        const url = document.getElementById('addPosterUrl').value;
        if (url && url.startsWith('http')) {
            document.getElementById('addMoviePosterPreview').src = url;
        }
    }

    document.getElementById('addMovieForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'add_movie');

        const fileInput = document.getElementById('addPosterFile');
        if (fileInput.files.length > 0) {
            formData.append('poster', fileInput.files[0]);
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Adding...';
        submitBtn.disabled = true;

        fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        closeModal('addMovieModal');
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('System error occurred', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    function showEditModal(movieId) {
        const movies = <?php echo json_encode($nowShowing); ?>;
        const movie = movies.find(m => m.id == movieId) || {};

        const posterSrc = getPoster(movie.poster || '');
        document.getElementById('editMoviePoster').src = posterSrc;
        document.getElementById('editMovieTitle').textContent = movie.title || 'No Title';
        document.getElementById('editMovieIdValue').textContent = movie.id || 'N/A';
        document.getElementById('editTitle').value = movie.title || '';
        document.getElementById('editGenre').value = movie.genre || '';
        document.getElementById('editSynopsis').value = movie.synopsis || '';
        document.getElementById('editPrice').value = movie.price || '';
        document.getElementById('editDuration').value = movie.duration || '2h 0m';
        document.getElementById('editStatus').value = movie.status || 'showing';

        const modal = document.getElementById('editMovieModal');
        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.3s ease';
    }

    function previewEditImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('editMoviePoster').src = e.target.result;
                document.getElementById('editMoviePoster').style.animation = 'pulse 0.5s ease';
                setTimeout(() => {
                    document.getElementById('editMoviePoster').style.animation = '';
                }, 500);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('editMovieForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const movieId = document.getElementById('editMovieIdValue').textContent;

        const formData = new FormData(this);
        formData.append('action', 'edit_movie');
        formData.append('id', movieId);

        const fileInput = document.getElementById('editPosterFile');
        if (fileInput.files.length > 0) {
            formData.append('poster', fileInput.files[0]);
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Saving...';
        submitBtn.disabled = true;

        fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        closeModal('editMovieModal');
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('System error occurred', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    function showDeleteModal(movieId, movieTitle) {
        const movies = <?php echo json_encode($nowShowing); ?>;
        const movie = movies.find(m => m.id == movieId) || {};

        document.getElementById('deleteMovieId').textContent = movieId;
        document.getElementById('deleteMovieTitle').textContent = movieTitle;

        const modal = document.getElementById('deleteMovieModal');
        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.3s ease';
    }

    function processDelete() {
        const movieId = document.getElementById('deleteMovieId').textContent;
        const movieTitle = document.getElementById('deleteMovieTitle').textContent;

        const deleteBtn = document.querySelector('#deleteMovieModal button[onclick="processDelete()"]');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Deleting...';
        deleteBtn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'delete_movie');
        formData.append('id', movieId);

        fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(`"${movieTitle}" has been permanently removed!`, 'error');

                    setTimeout(() => {
                        closeModal('deleteMovieModal');
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                    deleteBtn.innerHTML = originalText;
                    deleteBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Connection error occurred', 'error');
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            });
    }

    function showViewModal(movieId) {
        currentViewingId = movieId;

        const movies = <?php echo json_encode($nowShowing); ?>;
        const movie = movies.find(m => m.id == movieId) || {};

        const posterSrc = getPoster(movie.poster || '');
        document.getElementById('viewMoviePoster').src = posterSrc;
        document.getElementById('viewMovieId').textContent = movie.id || 'N/A';
        document.getElementById('viewMovieTitle').textContent = movie.title || 'No Title';
        document.getElementById('viewMovieGenreText').textContent = movie.genre || 'No Genre';
        document.getElementById('viewMovieDurationText').textContent = movie.duration || '2h 0m';
        document.getElementById('viewMoviePrice').textContent = `Rp ${parseInt(movie.price || 0).toLocaleString('id-ID')}`;
        document.getElementById('viewMovieSynopsis').textContent = movie.synopsis || 'No synopsis available.';

        const status = movie.status || 'showing';
        let statusText = 'SHOWING';
        if (status === 'coming_soon') statusText = 'COMING SOON';
        else if (status === 'archived') statusText = 'ARCHIVED';
        document.getElementById('viewMovieStatus').textContent = statusText;

        const heroMovie = <?php echo json_encode($heroMovie); ?>;
        const isFeatured = (heroMovie && heroMovie.id == movieId);
        document.getElementById('viewMovieFeatured').textContent = isFeatured ? 'Yes' : 'No';
        document.getElementById('viewMovieFeatured').style.color = isFeatured ? '#4caf50' : '#d32f2f';

        const modal = document.getElementById('viewMovieModal');
        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.3s ease';
    }

    function showFeatureModal(movieId, movieTitle) {
        currentFeatureMovieId = movieId;

        const movies = <?php echo json_encode($nowShowing); ?>;
        const movie = movies.find(m => m.id == movieId) || {};

        document.getElementById('featureMovieId').textContent = movieId;
        document.getElementById('featureMovieTitle').textContent = movieTitle || movie.title || 'Unknown';
        document.getElementById('featureMovieGenre').textContent = movie.genre || 'Unknown';
        document.getElementById('featureMovieDuration').textContent = movie.duration || '2h 0m';
        document.getElementById('featureMoviePrice').textContent = `Rp ${parseInt(movie.price || 0).toLocaleString('id-ID')}`;

        const posterSrc = getPoster(movie.poster || '');
        document.getElementById('featureMoviePoster').src = posterSrc;
        document.getElementById('featureMoviePoster').onerror = function() {
            this.src = 'https://dummyimage.com/400x600/ffffff/fff';
        };

        document.querySelectorAll('.feature-period-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = '#f5f5f5';
            btn.style.color = '#666';
            btn.style.border = '1px solid #ddd';
        });
        document.querySelector('.feature-period-btn[data-days="7"]').classList.add('active');
        document.querySelector('.feature-period-btn[data-days="7"]').style.background = 'linear-gradient(135deg, #ff9800, #ff5722)';
        document.querySelector('.feature-period-btn[data-days="7"]').style.color = 'white';
        document.querySelector('.feature-period-btn[data-days="7"]').style.border = 'none';
        selectedFeatureDays = 7;

        const modal = document.getElementById('featureMovieModal');
        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.3s ease';
    }

    function selectFeaturePeriod(button, days) {
        document.querySelectorAll('.feature-period-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = '#f5f5f5';
            btn.style.color = '#666';
            btn.style.border = '1px solid #ddd';
        });

        button.classList.add('active');
        button.style.background = 'linear-gradient(135deg, #ff9800, #ff5722)';
        button.style.color = 'white';
        button.style.border = 'none';
        button.style.transform = 'translateY(-2px)';
        button.style.boxShadow = '0 5px 15px rgba(255, 152, 0, 0.3)';

        selectedFeatureDays = days;
    }

    function processFeature() {
        const movieId = document.getElementById('featureMovieId').textContent;
        const movieTitle = document.getElementById('featureMovieTitle').textContent;

        const featureBtn = document.querySelector('#featureMovieModal button[onclick="processFeature()"]');
        const originalText = featureBtn.innerHTML;
        featureBtn.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> Processing...';
        featureBtn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'feature_movie');
        formData.append('movie_id', movieId);

        fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification(`"${movieTitle}" is now the Featured Movie!`, 'success');
                    setTimeout(() => {
                        closeModal('featureMovieModal');
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                    featureBtn.innerHTML = originalText;
                    featureBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Connection failed', 'error');
                featureBtn.innerHTML = originalText;
                featureBtn.disabled = false;
            });
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.animation = 'fadeIn 0.3s ease reverse';
        setTimeout(() => {
            modal.style.display = 'none';
            modal.style.animation = '';
        }, 300);
    }

    function getPoster(filename) {
        if (!filename) return 'https://dummyimage.com/400x600/ffffff/fff';
        if (filename.startsWith('http')) return filename;
        return 'uploads/' + filename;
    }

    function refreshMovies() {
        location.reload();
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; 
        background: ${type === 'success' ? 'linear-gradient(135deg, #4caf50, #2e7d32)' : 'linear-gradient(135deg, #c62828, #b71c1c)'}; 
        color: white; padding: 15px 25px; 
        border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        z-index: 1002; animation: slideIn 0.3s ease;
        display: flex; align-items: center; gap: 10px;
        max-width: 400px;
        word-wrap: break-word;
    `;
        notification.innerHTML = `
        <i class="ph ${type === 'success' ? 'ph-check-circle' : 'ph-warning-circle'}" style="font-size: 20px;"></i>
        <div style="flex: 1;">
            <strong>${type === 'success' ? 'Success!' : 'Deleted!'}</strong><br>
            ${message}
        </div>
    `;

        const oldNotification = document.querySelector('div[style*="position: fixed; top: 20px; right: 20px;"]');
        if (oldNotification) oldNotification.remove();

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'fadeIn 0.3s ease reverse';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    window.onclick = function(event) {
        const modals = ['addMovieModal', 'editMovieModal', 'deleteMovieModal', 'viewMovieModal', 'featureMovieModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                closeModal(modalId);
            }
        });
    };

    document.onkeydown = function(event) {
        if (event.key === 'Escape') {
            const modals = ['addMovieModal', 'editMovieModal', 'deleteMovieModal', 'viewMovieModal', 'featureMovieModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal.style.display === 'flex') {
                    closeModal(modalId);
                }
            });
        }
    };
</script>
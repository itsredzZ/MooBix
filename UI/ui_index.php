<?php
// --- BAGIAN 1: BACKEND & KONEKSI DATABASE ---
session_start();

// Konfigurasi Database
$host = 'localhost';
$dbname = 'db_moobix';
$user = 'root';
$pass = ''; // Default XAMPP biasanya kosong

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Gagal: " . $e->getMessage());
}

// Mengambil Data Hero Movie (Film Terbaru/Unggulan)
$stmtHero = $pdo->query("SELECT * FROM movies ORDER BY id DESC LIMIT 1");
$heroMovie = $stmtHero->fetch(PDO::FETCH_ASSOC);

// Fallback jika database kosong (Data Dummy)
if (!$heroMovie) {
    $heroMovie = [
        'title' => 'OPPENHEIMER',
        'genre' => 'Biography',
        'duration' => '3h 0min',
        'rating' => '8.9',
        'airing_date' => '14:00', // Tambah ini biar tidak error
        'price' => 50000,         // Tambah ini biar tidak error
        'poster' => 'https://image.tmdb.org/t/p/original/8RpDCSfKTPA8HOxAsj2vqF8w946.jpg' // GANTI JADI POSTER
    ];
}

// Mengambil Data 'Now Showing'
$stmtList = $pdo->query("SELECT * FROM movies");
$nowShowing = $stmtList->fetchAll(PDO::FETCH_ASSOC);

// Fallback dummy
if (empty($nowShowing)) {
    $nowShowing = [
        [
            'title' => 'Barbie', 
            'rating' => '7.5', 
            'genre' => 'Comedy', 
            'poster' => 'https://image.tmdb.org/t/p/original/iuFNMS8U5cb6xfzi51Dbkovj7vM.jpg' // GANTI JADI POSTER
        ],
        [
            'title' => 'Dune 2', 
            'rating' => '8.8', 
            'genre' => 'Sci-Fi', 
            'poster' => 'https://image.tmdb.org/t/p/original/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg' // GANTI JADI POSTER
        ]
    ];
}
// ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineTix - MooBix</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Dancing+Script:wght@700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <link rel="stylesheet" href="ui_style.css">
</head>
<body>

    <header id="navbar">
        <div class="logo">CINETIX THEATER</div>
        
        <nav class="main-nav">
            <a href="#hero-section">NEWEST HIT</a>
            <a href="#schedule-section">MORE FILMS</a>
        </nav>

        <div class="login-area">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search film...">
            </div>

            <button class="login-btn" onclick="toggleModal('loginModal', true)">LOGIN</button>
        </div>
    </header>

    <section class="hero" id="hero-section">
        <div class="hero-container">
            <div class="hero-text">
                <span class="tagline">Now Showing &mdash; The Masterpiece</span>
                <h1 class="hero-title"><?php echo htmlspecialchars($heroMovie['title']); ?></h1>
                
                <div class="hero-details">
                    <span><?php echo htmlspecialchars($heroMovie['genre']); ?></span> &bull; 
                    <span>Jam: <?php echo htmlspecialchars($heroMovie['airing_date']); ?></span> &bull; 
                    <span>Tiket: Rp <?php echo number_format($heroMovie['price']); ?></span>
                </div>

                <button class="btn-primary" onclick="toggleModal('bookingModal', true)">GET TICKET</button>
            </div>

            <div class="hero-poster">
                <div class="poster-frame-hero">
                    <img src="uploads/<?php echo htmlspecialchars($heroMovie['poster']); ?>" alt="Poster" referrerpolicy="no-referrer">
                </div>
            </div>
        </div>
    </section>

    <div class="divider"><span>MORE TO EXPLORE</span></div>

    <section class="now-showing" id="schedule-section">
        <div class="section-header">
            <span>Today's Selection</span>
            <h2>NOW SHOWING</h2>
        </div>
        
        <div class="slider-wrapper">
            <button class="nav-btn" onclick="scrollMovies(-300)"><i class="ph ph-caret-left"></i></button>

            <div class="cards-container" id="movieList">
                <?php foreach($nowShowing as $movie): ?>
                    <div class="movie-card">
                        <div class="poster-frame">
                            <img src="uploads/<?php echo htmlspecialchars($movie['poster']); ?>" alt="Poster">
                        </div>
                        <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
            
                        <small>Genre: <?php echo htmlspecialchars($movie['genre']); ?></small>
            
                        <button class="btn-primary" style="padding: 5px 15px; font-size: 0.9rem; margin-top:10px; width:100%;" onclick="toggleModal('bookingModal', true)">Book Now</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="nav-btn" onclick="scrollMovies(300)"><i class="ph ph-caret-right"></i></button>
        </div>
    </section>

    <div class="modal-overlay" id="loginModal">
        <div class="ticket-modal">
            <span class="close-modal" onclick="toggleModal('loginModal', false)">&times;</span>
            
            <div id="login-view">
                <h2>LOG IN</h2>
                <form action="login_process.php" method="POST">
                    <div class="input-group">
                        <label>Email / ID</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" class="btn-login-submit">ENTER</button>
                </form>
                <p class="text" style="cursor:pointer; text-decoration:underline;" onclick="switchForm('signup')">Create an Account</p>
            </div>

            <div id="signup-view" style="display:none;">
                <h2>SIGN UP</h2>
                <form action="register_process.php" method="POST">
                    <div class="input-group"><label>Name</label><input type="text" name="fullname"></div>
                    <div class="input-group"><label>Email</label><input type="email" name="email"></div>
                    <div class="input-group"><label>Password</label><input type="password" name="password"></div>
                    <button type="submit" class="btn-login-submit">REGISTER</button>
                </form>
                <p class="text" style="cursor:pointer; text-decoration:underline;" onclick="switchForm('login')">Back to Login</p>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="bookingModal">
        <div class="ticket-modal" style="width: 500px;">
            <span class="close-modal" onclick="toggleModal('bookingModal', false)">&times;</span>
            <h2>SELECT SEATS</h2>
            
            <div class="seat-container">
                <div class="screen">SCREEN</div>
                <div class="row">
                    <div class="seat"></div><div class="seat"></div><div class="seat occupied"></div><div class="seat"></div>
                    <div class="seat"></div><div class="seat"></div><div class="seat"></div><div class="seat"></div>
                </div>
                <div class="row">
                    <div class="seat"></div><div class="seat"></div><div class="seat"></div><div class="seat"></div>
                    <div class="seat occupied"></div><div class="seat occupied"></div><div class="seat"></div><div class="seat"></div>
                </div>
                 <div class="row">
                    <div class="seat"></div><div class="seat"></div><div class="seat"></div><div class="seat"></div>
                    <div class="seat"></div><div class="seat"></div><div class="seat"></div><div class="seat"></div>
                </div>
            </div>

            <p class="text">
                Selected: <b id="count" style="color:#aa2b2b;">0</b> seats <br>
                Total Price: <b>Rp <span id="total">0</span></b>
            </p>
            <button class="btn-primary" style="width:100%; margin-top:15px;">CONFIRM & PAY</button>
        </div>
    </div>

    <script src="ui_script.js"></script>
</body>
</html>
<?php
//http://localhost/moobix/ui/ui_index.php
$heroMovie = [
    'title' => 'OPPENHEIMER',
    'genre' => 'Biography',
    'duration' => '3h 0min',
    'rating' => '8.9',
    'image' => 'https://image.tmdb.org/t/p/original/8RpDCSfKTPA8HOxAsj2vqF8w946.jpg'
];

// Data untuk Slider (Now Showing)
$nowShowing = [
    [
        'title' => 'Barbie',
        'image' => 'https://image.tmdb.org/t/p/original/iuFNMS8U5cb6xfzi51Dbkovj7vM.jpg',
        'rating' => '7.5'
    ],
    [
        'title' => 'Dune: Part Two',
        'image' => 'https://image.tmdb.org/t/p/original/1pdfLvkbY9ohJlCjQH2CZjjYVvJ.jpg',
        'rating' => '8.8'
    ],
    [
        'title' => 'Interstellar',
        'image' => 'https://image.tmdb.org/t/p/original/gEU2QniL6E8ahMcafCUyGdjxXAr.jpg',
        'rating' => '8.7'
    ],
    [
        'title' => 'The Batman',
        'image' => 'https://image.tmdb.org/t/p/original/74xTEgt7R36Fpooo50r9T25onhq.jpg',
        'rating' => '7.9'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineVintage Premium Dashboard</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Dancing+Script:wght@700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link rel="stylesheet" href="ui_style.css">
    
    <style>
        .movie-card {
            min-width: 200px;
            margin: 0 10px;
            transition: transform 0.3s;
            cursor: pointer;
        }
        .movie-card:hover { transform: scale(1.05); }
        .movie-card img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .movie-card h3 {
            margin-top: 10px;
            font-size: 1.1rem;
            color: #fff; /* Sesuaikan dengan tema */
        }
    </style>
</head>
<body>

    <header id="navbar">
        <div class="logo">THE OLD THEATER</div>
        
        <nav class="main-nav">
            <a href="#hero-section">NEWEST HIT</a>
            <a href="#schedule-section">MORE FILMS</a>
        </nav>

        <div class="login-area">
            <button class="login-btn" onclick="toggleModal(true)">LOGIN / SIGN UP</button>
        </div>
    </header>

    <section class="hero" id="hero-section">
        <div class="hero-container">
            
            <div class="hero-text">
                <span class="tagline">Now Showing &mdash; The Masterpiece</span>
                
                <h1 class="hero-title"><?php echo $heroMovie['title']; ?></h1>
                
                <div class="hero-details">
                    <span><?php echo $heroMovie['genre']; ?></span> &bull; 
                    <span><?php echo $heroMovie['duration']; ?></span> &bull; 
                    <span>IMDB <?php echo $heroMovie['rating']; ?></span>
                </div>

                <button class="btn-primary">GET TICKET</button>
            </div>

            <div class="hero-poster">
                <div class="poster-frame-hero">
                    <img src="<?php echo $heroMovie['image']; ?>" alt="<?php echo $heroMovie['title']; ?> Poster" referrerpolicy="no-referrer">
                </div>
            </div>

        </div>
    </section>

    <div class="divider">
        <span>MORE TO EXPLORE</span>
    </div>

    <section class="now-showing" id="schedule-section">
        <div class="section-header">
            <span>Today's Selection</span>
            <h2>NOW SHOWING</h2>
        </div>
        
        <div class="slider-wrapper">
            <button class="nav-btn" onclick="scrollMovies(-350)"><i class="ph ph-caret-left"></i></button>

            <div class="cards-container" id="movieList">
                
                <?php foreach($nowShowing as $movie): ?>
                    <div class="movie-card">
                        <img src="<?php echo $movie['image']; ?>" alt="<?php echo $movie['title']; ?>">
                        <h3><?php echo $movie['title']; ?></h3>
                        <small>Rating: <?php echo $movie['rating']; ?></small>
                    </div>
                <?php endforeach; ?>

            </div>

            <button class="nav-btn" onclick="scrollMovies(350)"><i class="ph ph-caret-right"></i></button>
        </div>
    </section>

    <div class="modal-overlay" id="loginModal">
        <div class="ticket-modal">
            <span class="close-modal" onclick="toggleModal(false)">&times;</span>
            
            <div id="login-view">
                <h2>LOG IN</h2>
                <form action="login_process.php" method="POST">
                    <div class="input-group">
                        <label>Member ID / Email</label>
                        <input type="text" name="username" placeholder="USER123" required>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="******" required>
                    </div>
                    <button type="submit" class="btn-login-submit">LOGIN</button>
                </form>
                <p style="margin-top: 15px; font-size: 0.8rem; color:#666;">
                    Don't have an account? 
                    <b onclick="switchForm('signup')" style="cursor:pointer; color:#1a1a1a; text-decoration:underline;">Sign up!</b>
                </p>
            </div>

            <div id="signup-view" style="display: none;">
                <h2>SIGN UP</h2>
                <form action="register_process.php" method="POST">
                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" placeholder="John Doe" required>
                    </div>
                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="john@email.com" required>
                    </div>
                    <div class="input-group">
                        <label>Create Password</label>
                        <input type="password" name="password" placeholder="******" required>
                    </div>
                    <button type="submit" class="btn-login-submit">REGISTER</button>
                </form>
                <p style="margin-top: 15px; font-size: 0.8rem; color:#666;">
                    Already a member? 
                    <b onclick="switchForm('login')" style="cursor:pointer; color:#1a1a1a; text-decoration:underline;">Log in</b>
                </p>
            </div>

        </div>
    </div>

    <script src="ui_script.js"></script>
</body>
</html>
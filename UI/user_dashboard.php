<?php
// This file is included by index.php when user is not admin
// Variables from index.php: $heroMovie, $nowShowing, $isLoggedIn, $userName, etc.
?>

<main id="user-dashboard">
    
    <section class="hero" id="hero-section" style="background-image: linear-gradient(to right, rgba(31, 21, 20, 0.95) 20%, rgba(44, 30, 28, 0.7) 100%), url('<?php echo getPoster(safe($heroMovie, 'poster')); ?>');">
        <div class="hero-container">
            <div class="hero-text">
                <span class="tagline">Now Showing &mdash; The Masterpiece</span>
                <h1 class="hero-title"><?php echo safe($heroMovie, 'title'); ?></h1>
                <div class="hero-details">
                    <span><?php echo safe($heroMovie, 'genre'); ?></span> &bull; 
                    <span><?php echo safe($heroMovie, 'duration'); ?></span> &bull; 
                    <span>Rp <?php echo number_format((int)safe($heroMovie, 'price', 0), 0, ',', '.'); ?></span>
                </div>
                <button class="btn-primary" onclick="openBookingFlow(
                    '<?php echo addslashes(safe($heroMovie, 'title')); ?>', 
                    '<?php echo addslashes(getPoster(safe($heroMovie, 'poster'))); ?>', 
                    '<?php echo addslashes($heroMovie['synopsis']); ?>',
                    <?php echo (int)safe($heroMovie, 'price', 0); ?>,
                    '<?php echo addslashes(safe($heroMovie, 'duration')); ?>',  '<?php echo addslashes(safe($heroMovie, 'rating', '0.0')); ?>'
                )">GET TICKET</button>
            </div>
            <div class="hero-poster">
                <div class="poster-frame-hero">
                    <img src="<?php echo getPoster(safe($heroMovie, 'poster')); ?>" alt="Poster" onerror="this.src='https://via.placeholder.com/400x600?text=No+Image'">
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
                <?php foreach($nowShowing as $movie): 
                    // --- 1. SIAPKAN DATA ---
                    $jsTitle = addslashes($movie['title']); 
                    $cleanSynopsis = preg_replace('/\s+/', ' ', $movie['synopsis'] ?? '');
                    $jsSynopsis = addslashes($cleanSynopsis);
                    
                    $jsPoster = getPoster(safe($movie, 'poster'));
                    $jsPrice = (int)safe($movie, 'price', 0);
                    $jsDuration = htmlspecialchars($movie['duration'] ?? '-'); 
                    
                    // AMBIL RATING DARI DATABASE (PENTING!)
                    $jsRating = htmlspecialchars($movie['rating'] ?? '0.0');
                ?>
                <div class="movie-card">
                    <div class="poster-frame">
                        <img src="<?php echo $jsPoster; ?>" alt="Poster" onerror="this.src='https://via.placeholder.com/300x450?text=No+Image'">
                        
                        <div class="rating-badge-poster">
                            <i class="ph ph-star-fill"></i> <?php echo $jsRating; ?>
                        </div>

                        <div class="poster-overlay">
                            <button class="btn-book-now" onclick="openBookingFlow(
                                '<?php echo $jsTitle; ?>', 
                                '<?php echo $jsPoster; ?>', 
                                '<?php echo $jsSynopsis; ?>', 
                                <?php echo $jsPrice; ?>, 
                                '<?php echo $jsDuration; ?>',
                                '<?php echo $jsRating; ?>' 
                            )">
                                <i class="ph ph-ticket"></i> BOOK NOW
                            </button>
                        </div>
                    </div>
                    
                    <h3><?php echo safe($movie, 'title'); ?></h3>
                    <small><?php echo safe($movie, 'genre'); ?></small>
                    
                    <div class="movie-card-footer">
                        <span class="movie-price">Rp <?php echo number_format($jsPrice, 0, ',', '.'); ?></span>
                        <span class="movie-duration" style="font-size:0.85rem; color:#ccc;">
                            <?php echo $jsDuration; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="nav-btn" onclick="scrollMovies(300)"><i class="ph ph-caret-right"></i></button>
        </div>
    </section>
</main>
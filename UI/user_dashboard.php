<?php
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

                <?php
                // --- SOLUSI ANTI ERROR: SIAPKAN DATA DALAM ARRAY ---
                // Kita bungkus semua data dalam array PHP, lalu ubah jadi JSON
                // JSON otomatis menangani Enter, Kutip Dua, Kutip Satu, dll.
                $heroData = [
                    'id' => (int)$heroMovie['id'],
                    'title' => safe($heroMovie, 'title'),
                    'poster' => getPoster(safe($heroMovie, 'poster')),
                    'synopsis' => safe($heroMovie, 'synopsis'),
                    'price' => (int)safe($heroMovie, 'price', 0),
                    'duration' => safe($heroMovie, 'duration'),
                    'rating' => safe($heroMovie, 'rating')
                ];
                ?>

                <script>
                    var heroMovieData = <?php echo json_encode($heroData); ?>;
                </script>

                <button class="btn-primary" 
                        style="position: relative; z-index: 100; cursor: pointer;"
                        onclick="openBookingFlow(
                            heroMovieData.id, 
                            heroMovieData.title, 
                            heroMovieData.poster, 
                            heroMovieData.synopsis, 
                            heroMovieData.price, 
                            heroMovieData.duration, 
                            heroMovieData.rating
                        )">
                    <i class="ph ph-ticket"></i> GET TICKET
                </button>
            </div>

            <div class="hero-poster">
                <div class="poster-frame-hero">
                    <img src="<?php echo getPoster(safe($heroMovie, 'poster')); ?>" alt="Poster" onerror="this.src='https://dummyimage.com/400x600/ffffff/fff'">
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
                <?php foreach ($nowShowing as $movie):
                    $jsTitle = addslashes($movie['title']);
                    $cleanSynopsis = preg_replace('/\s+/', ' ', $movie['synopsis'] ?? '');
                    $jsSynopsis = addslashes($cleanSynopsis);

                    $jsPoster = getPoster(safe($movie, 'poster'));
                    $jsPrice = (int)safe($movie, 'price', 0);
                    $jsDuration = htmlspecialchars($movie['duration'] ?? '-');

                    $jsRating = htmlspecialchars($movie['rating'] ?? '0.0');
                ?>
                    <div class="movie-card">
                        <div class="poster-frame">
                            <img src="<?php echo $jsPoster; ?>" alt="Poster" onerror="this.src='https://via.placeholder.com/300x450?text=No+Image'">

                            <div class="rating-badge-poster" style="display: flex; align-items: center; gap: 4px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="gold" viewBox="0 0 256 256">
                                    <path d="M234.5,114.38l-45.1,39.36,13.51,58.6a16,16,0,0,1-23.84,17.34l-51.11-30.15-51.1,30.15a16,16,0,0,1-23.84-17.34L66.61,153.8,21.5,114.38a16,16,0,0,1,9.11-28.06l59.46-5.15,23.21-55.36a15.95,15.95,0,0,1,29.44,0h0L166,81.17l59.44,5.15a16,16,0,0,1,9.11,28.06Z"></path>
                                </svg>

                                <span><?php echo $jsRating; ?></span>
                            </div>

                            <div class="poster-overlay">
                                <button class="btn-book-now" onclick="openBookingFlow(
                                    <?php echo $movie['id']; ?>,   '<?php echo $jsTitle; ?>',
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

                        <?php
                        $judulMovie = safe($movie, 'title');
                        $isLong = (strlen($judulMovie) > 50) ? 'title-small' : '';
                        ?>

                        <h3 class="<?php echo $isLong; ?>">
                            <?php echo $judulMovie; ?>
                        </h3>

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
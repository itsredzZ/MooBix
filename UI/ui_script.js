// --- 1. DATA FILM ---
const movies = [
    { title: "Super Mario Bros", img: "https://image.tmdb.org/t/p/w500/qNBAXBIQlnOThrVvA6mA2B5ggV6.jpg" },
    { title: "Fast X", img: "https://image.tmdb.org/t/p/w500/fiVW06jE7z9YnO4trhaMEdclSiC.jpg" },
    { title: "John Wick 4", img: "https://image.tmdb.org/t/p/w500/vZloFAK7NmvMGKE7VkF5UHaz0I.jpg" },
    { title: "Avatar: Water", img: "https://image.tmdb.org/t/p/w500/t6HIqrRAclMCA60NsSmeqe9RmNV.jpg" },
    { title: "Spider-Man: ATSV", img: "https://image.tmdb.org/t/p/w500/8Vt6mWEReuy4Of61Lnj5Xj704m8.jpg" },
    { title: "The Flash", img: "https://image.tmdb.org/t/p/w500/rktDFPbfHfUbArZ6OOOKsXcv0Bm.jpg" },
    { title: "The Nun II", img: "https://image.tmdb.org/t/p/w500/5gzzkR7y3zdL8CSAU2SEh2SnZlW.jpg" },
];

// --- 2. RENDER KARTU FILM ---
const container = document.getElementById('movieList');

movies.forEach(movie => {
    container.innerHTML += `
        <div class="movie-card">
            <div class="poster-frame">
                <img src="${movie.img}" alt="${movie.title}" loading="lazy" referrerpolicy="no-referrer">
            </div>
            <div class="card-title">${movie.title}</div>
        </div>
    `;
});

// --- 3. LOGIKA SCROLL SLIDER ---
function scrollMovies(amount) {
    container.scrollBy({ left: amount, behavior: 'smooth' });
}

// --- 4. LOGIKA MODAL (LOGIN & SIGNUP SWITCH) ---
const modal = document.getElementById('loginModal');
const loginView = document.getElementById('login-view');
const signupView = document.getElementById('signup-view');

function toggleModal(show) {
    if(show) {
        modal.classList.add('active');
        switchForm('login'); // Reset ke tampilan login saat dibuka
    } else {
        modal.classList.remove('active');
    }
}

function switchForm(type) {
    if (type === 'signup') {
        loginView.style.display = 'none';
        signupView.style.display = 'block';
    } else {
        loginView.style.display = 'block';
        signupView.style.display = 'none';
    }
}

// Tutup modal jika klik di luar kotak
modal.addEventListener('click', (e) => {
    if (e.target === modal) toggleModal(false);
});
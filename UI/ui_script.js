/* ui_script.js */

// =========================================
// 1. LOGIKA SCROLL SLIDER (Now Showing)
// =========================================
function scrollMovies(amount) {
    const container = document.getElementById('movieList');
    if (container) {
        // Scroll horizontal dengan efek smooth
        container.scrollBy({ left: amount, behavior: 'smooth' });
    }
}

// =========================================
// 2. LOGIKA MODAL (Login & Booking)
// =========================================

// Fungsi Buka/Tutup Modal
function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Flex untuk memusatkan konten, None untuk sembunyi
        modal.style.display = show ? 'flex' : 'none';

        // Jika membuka modal login, reset tampilan ke form login awal
        if (show && modalId === 'loginModal') {
            switchForm('login');
        }
    }
}

// Event Listener: Tutup modal jika user klik area gelap (overlay)
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = "none";
    }
}

// =========================================
// 3. LOGIKA SWITCH FORM (Login <-> Signup)
// =========================================
function switchForm(type) {
    const loginView = document.getElementById('login-view');
    const signupView = document.getElementById('signup-view');

    if (loginView && signupView) {
        if (type === 'signup') {
            loginView.style.display = 'none';
            signupView.style.display = 'block';
        } else {
            loginView.style.display = 'block';
            signupView.style.display = 'none';
        }
    }
}

// =========================================
// 4. LOGIKA VISUAL SEAT SELECTION
// =========================================
const seatContainer = document.querySelector('.seat-container');
const countSpan = document.getElementById('count');
const totalSpan = document.getElementById('total');
const TICKET_PRICE = 50000; // Harga Tiket (Bisa dinamis via PHP jika perlu)

if (seatContainer) {
    seatContainer.addEventListener('click', (e) => {
        // Cek 1: Apakah yang diklik adalah kursi?
        // Cek 2: Apakah kursi tersebut TIDAK terisi (not occupied)?
        if (e.target.classList.contains('seat') && !e.target.classList.contains('occupied')) {
            
            // Toggle class 'selected' (Hijau <-> Abu)
            e.target.classList.toggle('selected');
            
            // Update hitungan harga
            updateBookingInfo();
        }
    });
}

function updateBookingInfo() {
    // Cari semua kursi yang punya class 'seat' DAN 'selected' di dalam row
    const selectedSeats = document.querySelectorAll('.row .seat.selected');
    
    // Hitung jumlah array elemen yang ditemukan
    const count = selectedSeats.length;

    // Update tampilan HTML
    if (countSpan) countSpan.innerText = count;
    if (totalSpan) totalSpan.innerText = (count * TICKET_PRICE).toLocaleString('id-ID');
}

// =========================================
// 5. LOGIKA LIVE SEARCH (Filter Film)
// =========================================
const searchInput = document.getElementById('searchInput');
// Mengambil semua elemen kartu film
const movieCards = document.querySelectorAll('.movie-card');

if (searchInput) {
    searchInput.addEventListener('keyup', (e) => {
        // Ambil text input user & ubah ke huruf kecil (lowercase)
        const term = e.target.value.toLowerCase();

        movieCards.forEach(card => {
            // Ambil judul film dari tag <h3> di dalam kartu
            const titleElement = card.querySelector('h3');
            
            if (titleElement) {
                const title = titleElement.textContent.toLowerCase();

                // Logic pencarian (Partial Match)
                if (title.includes(term)) {
                    // Jika cocok, reset display ke default CSS (agar layout flex/grid tetap rapi)
                    card.style.display = ''; 
                } else {
                    // Jika tidak cocok, sembunyikan
                    card.style.display = 'none';
                }
            }
        });
    });
}
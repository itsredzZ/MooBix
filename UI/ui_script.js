// ================= VARIABLES =================
let currentMovie = {};
let selectedDate = null;
let selectedTime = null;
let ticketQty = 1;

// ================= 1. BOOKING FLOW LOGIC =================

/**
 * Fungsi Utama: Membuka alur pemesanan tiket
 * TRIGGER: Tombol "GET TICKET" atau "BOOK NOW"
 */
function openBookingFlow(title, poster, synopsis, price, duration) {
    // --- CEK LOGIN (SATPAM) ---
    // Menggunakan variabel global PHP_DATA yang dikirim dari file PHP
    if (typeof PHP_DATA !== 'undefined' && !PHP_DATA.isLoggedIn) {
        alert("Silakan login terlebih dahulu untuk memesan tiket!");
        toggleModal('loginModal', true);
        return; // Stop, jangan lanjut buka modal booking
    }

    // Simpan data film yang dipilih
    currentMovie = { 
        title: title, 
        poster: poster, 
        synopsis: synopsis, 
        price: parseInt(price), 
        duration: duration 
    };
    
    // Isi data ke dalam elemen HTML Modal Step 1
    document.getElementById('modalTitle').innerText = title;
    const imgElement = document.getElementById('modalPoster');
    if(imgElement) imgElement.src = poster || 'https://via.placeholder.com/300x450?text=No+Image';
    
    document.getElementById('modalSynopsis').innerText = synopsis;
    document.getElementById('modalDuration').innerText = duration || '2h 0min';
    
    // Reset tampilan ke awal
    resetBookingSteps();
    toggleModal('bookingModal', true);
}

function resetBookingSteps() {
    // Tampilkan Step 1, Sembunyikan yang lain
    toggleDisplay('step-info', true);
    toggleDisplay('step-schedule', false);
    toggleDisplay('step-confirm', false);
    toggleDisplay('timeSlots', false);
    toggleDisplay('seatMapArea', false);

    // Reset variabel
    selectedDate = null;
    selectedTime = null;
    ticketQty = 1;
    
    const qtyDisplay = document.getElementById('qtyDisplay');
    if(qtyDisplay) qtyDisplay.innerText = ticketQty;
    
    // Hapus seleksi visual (warna merah/tombol aktif)
    document.querySelectorAll('.date-item').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.time-btn').forEach(el => el.classList.remove('selected'));
    
    resetSeats();
}

function proceedToSchedule() {
    toggleDisplay('step-info', false);
    toggleDisplay('step-schedule', true);
}

function backToSeats() {
    toggleDisplay('step-confirm', false);
    toggleDisplay('step-schedule', true);
}

// --- DATE & TIME SELECTION ---

function selectDate(element, dateValue) {
    // Reset seleksi tanggal sebelumnya
    document.querySelectorAll('.date-item').forEach(el => el.classList.remove('selected'));
    
    // Pilih tanggal baru
    element.classList.add('selected');
    selectedDate = dateValue;

    // Tampilkan jam tayang dengan animasi scroll
    const timeSlots = document.getElementById('timeSlots');
    if(timeSlots) {
        timeSlots.style.display = 'block';
        timeSlots.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function selectTime(element, time) {
    // Reset seleksi jam sebelumnya
    document.querySelectorAll('.time-btn').forEach(el => el.classList.remove('selected'));
    
    // Pilih jam baru
    element.classList.add('selected');
    selectedTime = time;

    // Tampilkan peta kursi
    const seatMap = document.getElementById('seatMapArea');
    if(seatMap) {
        seatMap.style.display = 'block';
        seatMap.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // Reset harga total saat jam berubah
        updateTotal();
    }
}

// ================= 2. SEAT LOGIC =================

function updateQty(change) {
    let newQty = ticketQty + change;
    // Limit minimal 1, maksimal 8 tiket
    if (newQty < 1) newQty = 1;
    if (newQty > 8) newQty = 8;
    
    ticketQty = newQty;
    document.getElementById('qtyDisplay').innerText = ticketQty;
    
    // Jika qty berubah, reset kursi yang dipilih agar user memilih ulang sesuai jumlah baru
    resetSeats();
}

// Initialize Seat Listeners (Dijalankan saat DOM Load)
document.addEventListener('DOMContentLoaded', () => {
    initializeSeatSelection();
});

function initializeSeatSelection() {
    document.querySelectorAll('.seat').forEach(seat => {
        // Hapus listener lama biar gak double, lalu pasang yang baru
        seat.removeEventListener('click', handleSeatClick);
        seat.addEventListener('click', handleSeatClick);
    });
}

function handleSeatClick() {
    // Cek kursi occupied (sudah dibooking orang lain)
    if (this.classList.contains('occupied')) {
        alert('Maaf, kursi ini sudah terisi!');
        return;
    }

    // Logika Toggle (Pilih / Hapus Pilih)
    if (this.classList.contains('selected')) {
        // Unselect
        this.classList.remove('selected');
    } else {
        // Select
        const currentSelected = document.querySelectorAll('.seat.selected').length;
        if (currentSelected >= ticketQty) {
            alert(`Anda hanya memesan ${ticketQty} tiket. Silakan tambah jumlah tiket jika ingin memilih lebih banyak kursi.`);
            return;
        }
        this.classList.add('selected');
    }
    
    updateTotal();
}

function resetSeats() {
    document.querySelectorAll('.seat.selected').forEach(s => s.classList.remove('selected'));
    updateTotal();
}

function updateTotal() {
    const selectedCount = document.querySelectorAll('.seat.selected').length;
    const priceToUse = currentMovie.price || 0;
    const total = selectedCount * priceToUse;
    
    const totalEl = document.getElementById('totalPrice');
    if(totalEl) totalEl.innerText = formatRupiah(total);
}

// ================= 3. CONFIRMATION & PAYMENT =================

function showConfirmation() {
    const selectedSeats = document.querySelectorAll('.seat.selected');
    
    // Validasi 1: Belum pilih kursi
    if (selectedSeats.length === 0) {
        alert("Silakan pilih kursi terlebih dahulu!");
        return;
    }

    // Validasi 2: Jumlah kursi tidak sesuai tiket
    if (selectedSeats.length !== ticketQty) {
        alert(`Anda memesan ${ticketQty} tiket, tapi baru memilih ${selectedSeats.length} kursi.`);
        return;
    }

    // Validasi 3: Belum pilih tanggal/jam
    if (!selectedDate || !selectedTime) {
        alert("Silakan pilih tanggal dan waktu terlebih dahulu!");
        return;
    }

    // Format Tanggal Cantik (Senin, 12 Agustus 2024)
    const dateObj = new Date(selectedDate);
    const formattedDate = dateObj.toLocaleDateString('id-ID', { 
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' 
    });

    // Ambil nomor kursi & Urutkan (A1, A2, B1...)
    const seatNumbers = Array.from(selectedSeats).map(s => s.innerText);
    seatNumbers.sort((a, b) => {
        const rowA = a.charAt(0);
        const rowB = b.charAt(0);
        const numA = parseInt(a.substring(1));
        const numB = parseInt(b.substring(1));
        if (rowA !== rowB) return rowA.localeCompare(rowB);
        return numA - numB;
    });

    const seatNames = seatNumbers.join(', ');
    const totalText = document.getElementById('totalPrice').innerText;

    // Isi Modal Konfirmasi
    document.getElementById('confMovie').innerText = currentMovie.title;
    document.getElementById('confDate').innerText = formattedDate;
    document.getElementById('confTime').innerText = selectedTime;
    document.getElementById('confSeats').innerText = seatNames;
    document.getElementById('confTotal').innerText = totalText;

    // Pindah ke Step Konfirmasi
    toggleDisplay('step-schedule', false);
    toggleDisplay('step-confirm', true);
}

// Enhanced Payment Processing
function processPayment() {
    const payButton = document.querySelector('.btn-pay-now');
    const originalText = payButton.innerHTML;
    
    // Show processing state
    payButton.innerHTML = '<i class="ph ph-circle-notch ph-spin"></i> PROCESSING...';
    payButton.disabled = true;
    
    // Simulate payment processing
    setTimeout(() => {
        // Success animation
        payButton.classList.add('payment-success');
        
        setTimeout(() => {
            // Show success message
            const confirmBox = document.querySelector('.confirm-box');
            confirmBox.innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--success-green), var(--success-dark)); 
                         color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; 
                         margin: 0 auto 20px; font-size: 2.5rem;">
                        <i class="ph ph-check"></i>
                    </div>
                    <h3 style="color: var(--success-green); margin-bottom: 15px; font-family: var(--font-head);">
                        PAYMENT SUCCESSFUL!
                    </h3>
                    <p style="color: #555; margin-bottom: 10px;">
                        Booking ID: <strong>CTX${Math.floor(Math.random() * 10000)}</strong>
                    </p>
                    <p style="color: #666; font-size: 0.9rem;">
                        Your tickets have been sent to your email. Please arrive 15 minutes before showtime.
                    </p>
                </div>
            `;
            
            // Update buttons
            const buttonsContainer = document.querySelector('.confirm-buttons');
            buttonsContainer.innerHTML = `
                <button class="btn-primary" onclick="closeBookingAndReset()" style="min-width: 200px;">
                    <i class="ph ph-check"></i> DONE
                </button>
            `;
            
        }, 500);
        
    }, 1500); // Simulate 1.5 second processing time
}

// Close booking modal and reset
function closeBookingAndReset() {
    // Close modal
    toggleModal('bookingModal', false);
    
    // Reset all steps after a delay
    setTimeout(() => {
        resetBookingFlow();
    }, 500);
}

// Reset booking flow
function resetBookingFlow() {
    // Reset steps
    document.getElementById('step-info').style.display = 'block';
    document.getElementById('step-schedule').style.display = 'none';
    document.getElementById('step-seats').style.display = 'none';
    document.getElementById('step-confirm').style.display = 'none';
    
    // Reset selections
    document.getElementById('qtyDisplay').textContent = '1';
    document.getElementById('totalPrice').textContent = '0';
    
    // Reset seat selections
    document.querySelectorAll('.seat.selected').forEach(seat => {
        seat.classList.remove('selected');
    });
    
    // Reset time selection if using the old version
    document.querySelectorAll('.time-btn.selected').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    document.querySelectorAll('.date-item.selected').forEach(item => {
        item.classList.remove('selected');
    });
}

// ================= 4. ADMIN FUNCTIONS =================

function openAdminModal(type) {
    const contentDiv = document.getElementById('adminModalContent');
    if(!contentDiv) return;

    let html = '';
    
    // Logic konten modal berdasarkan tipe tombol yang diklik
    switch(type) {
        case 'addMovie':
            html = `
                <h2 style="margin-bottom: 20px;">Add New Movie</h2>
                <div class="input-group"><label>Movie Title</label><input type="text" id="newTitle"></div>
                <div class="input-group"><label>Genre</label><input type="text" id="newGenre"></div>
                <div class="input-group"><label>Duration</label><input type="text" id="newDuration" placeholder="e.g. 2h 30m"></div>
                <div class="input-group"><label>Price</label><input type="number" id="newPrice"></div>
                <div class="input-group"><label>Poster URL</label><input type="text" id="newPoster"></div>
                <button class="btn-primary" onclick="addNewMovie()" style="width:100%; margin-top:20px;">Save Movie</button>
            `;
            break;
        case 'editMovies':
            html = `<h2>Edit Movies</h2><p>Pilih film dari tabel utama lalu klik icon pensil.</p>`;
            break;
        default:
            html = `<h2>Admin Menu</h2><p>Fitur <b>${type}</b> sedang dalam pengembangan.</p>`;
    }

    contentDiv.innerHTML = html;
    toggleModal('adminModal', true);
}

function addNewMovie() {
    const title = document.getElementById('newTitle').value;
    if(!title) { alert("Judul film tidak boleh kosong"); return; }
    
    alert(`Film "${title}" berhasil ditambahkan (Simulasi Database)`);
    toggleModal('adminModal', false);
}

function editMovie(id) {
    alert(`Membuka form edit untuk Movie ID: ${id}`);
    // Di real app, ini akan fetch data film lalu buka modal edit
}

function confirmDelete(id, title) {
    if(confirm(`Apakah Anda yakin ingin menghapus film "${title}"?`)) {
        alert(`Film "${title}" telah dihapus.`);
        // Di real app, panggil AJAX delete disini
        location.reload();
    }
}

function viewDetails(id) {
    alert(`Melihat detail Movie ID: ${id}`);
}

function featureMovie(id) {
    if(confirm("Jadikan film ini sebagai Featured Movie (Highlight Utama)?")) {
        alert("Film berhasil di-set sebagai Featured.");
    }
}

function refreshMovies() {
    location.reload();
}

// ================= 5. UTILITY & HELPER FUNCTIONS =================

// Helper untuk Toggle Display (Show/Hide)
function toggleDisplay(elementId, show) {
    const el = document.getElementById(elementId);
    if(el) el.style.display = show ? 'block' : 'none';
}

// Helper untuk Toggle Modal (Flex/None)
function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = show ? 'flex' : 'none';
        // Kunci scroll body saat modal terbuka
        document.body.style.overflow = show ? 'hidden' : 'auto';
    }
}

// Helper Format Rupiah
function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID').format(amount);
}

// Menutup modal saat klik di luar area konten (Overlay)
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = "none";
        document.body.style.overflow = 'auto';
    }
}

// Tombol Close (X) di Modal
document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', function() {
        const modal = this.closest('.modal-overlay');
        if(modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
});

// Search Filter (Client Side)
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase().trim();
        const movieCards = document.querySelectorAll('.movie-card');
        
        movieCards.forEach(card => {
            const title = card.querySelector('h3').innerText.toLowerCase();
            const genre = card.querySelector('small').innerText.toLowerCase();
            const isVisible = title.includes(term) || genre.includes(term);
            card.style.display = isVisible ? '' : 'none';
        });
    });
}

// Profile Dropdown Toggle
document.addEventListener('DOMContentLoaded', () => {
    const profileTrigger = document.querySelector('.profile-trigger');
    if (profileTrigger) {
        profileTrigger.addEventListener('click', function(e) {
            e.stopPropagation(); // Mencegah event bubbling
            const dropdown = this.parentElement.querySelector('.dropdown-menu');
            if(dropdown) {
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            }
        });
        
        // Tutup dropdown jika klik di tempat lain
        document.addEventListener('click', () => {
            const dropdown = document.querySelector('.dropdown-menu');
            if (dropdown) dropdown.style.display = 'none';
        });
    }
});

// Horizontal Scroll Slider (Tombol Kiri/Kanan di List Film)
function scrollMovies(amount) {
    const movieList = document.getElementById('movieList');
    if (movieList) {
        movieList.scrollBy({ left: amount, behavior: 'smooth' });
    }
}
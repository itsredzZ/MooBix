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
function openBookingFlow(id, title, poster, synopsis, price, duration, rating) {
  // Debugging: Cek di Console browser apakah data masuk
  console.log("Judul:", title);
  console.log("Rating diterima:", rating);

  // --- CEK LOGIN (SATPAM) ---
  // Menggunakan variabel global PHP_DATA yang dikirim dari file PHP
  if (typeof PHP_DATA !== "undefined" && !PHP_DATA.isLoggedIn) {
    alert("Silakan login terlebih dahulu untuk memesan tiket!");
    toggleModal("loginModal", true);
    return; // Stop, jangan lanjut buka modal booking
  }
  // --- ISI DATA MOVIE & TAMPILKAN MODAL ---
  currentMovie = {
    id: id, // <--- INI PENTING UNTUK DATABASE
    title: title,
    poster: poster,
    synopsis: synopsis,
    price: parseInt(price),
    duration: duration,
    rating: rating,
  };

  // 2. Update Modal Elements
  document.getElementById("modalTitle").innerText = title;

  const imgEl = document.getElementById("modalPoster");
  if (imgEl) imgEl.src = poster;

  document.getElementById("modalSynopsis").innerText = synopsis;

  // Update Duration (Hanya teksnya, ikon aman di HTML)
  // Update Duration (Hanya teksnya)
  // ... (kode sebelumnya) ...

  // Update Duration (Pembersih Super Kuat)
  const durEl = document.getElementById("modalDuration");
  if (durEl) {
    // Logika: Hapus semua karakter NON-ASCII (Emoji, simbol aneh, dll)
    // Hanya menyisakan Huruf, Angka, Spasi, dan tanda baca dasar
    let cleanDuration = duration.replace(/[^\x00-\x7F]/g, "").trim();

    // Opsional: Hapus spasi ganda jika ada
    cleanDuration = cleanDuration.replace(/\s+/g, " ");

    durEl.innerText = cleanDuration;
  }

  // ... (kode selanjutnya) ...

  // Update Rating (Hanya angkanya)
  const rateEl = document.getElementById("modalRating");
  if (rateEl) {
    // Cek jika rating valid, jika tidak set 0.0
    rateEl.innerText = rating && rating !== "null" ? rating : "0.0";
  }

  // 3. Show Modal
  if (typeof resetBookingSteps === "function") resetBookingSteps();

  // Toggle logic (using your existing toggle function or manual style)
  const modal = document.getElementById("bookingModal");
  if (modal) {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }
}

function resetBookingSteps() {
  // Tampilkan Step 1, Sembunyikan yang lain
  toggleDisplay("step-info", true);
  toggleDisplay("step-schedule", false);
  toggleDisplay("step-confirm", false);
  toggleDisplay("timeSlots", false);
  toggleDisplay("seatMapArea", false);

  // Reset variabel
  selectedDate = null;
  selectedTime = null;
  ticketQty = 1;

  const qtyDisplay = document.getElementById("qtyDisplay");
  if (qtyDisplay) qtyDisplay.innerText = ticketQty;

  // Hapus seleksi visual (warna merah/tombol aktif)
  document
    .querySelectorAll(".date-item")
    .forEach((el) => el.classList.remove("selected"));
  document
    .querySelectorAll(".time-btn")
    .forEach((el) => el.classList.remove("selected"));

  resetSeats();
}

function proceedToSchedule() {
  toggleDisplay("step-info", false);
  toggleDisplay("step-schedule", true);
}

function backToSeats() {
  toggleDisplay("step-confirm", false);
  toggleDisplay("step-schedule", true);
}

// --- DATE & TIME SELECTION ---

function selectDate(element, dateValue) {
    // 1. Reset tampilan tombol tanggal (Visual)
    document
        .querySelectorAll(".date-item")
        .forEach((el) => el.classList.remove("selected"));

    // 2. Tandai tanggal baru sebagai terpilih
    element.classList.add("selected");
    selectedDate = dateValue;

    // 3. Pastikan Slot Waktu Terlihat
    const timeSlots = document.getElementById("timeSlots");
    if (timeSlots) {
        timeSlots.style.display = "block";
    }

    // === FITUR BARU: DISABLE JAM YANG SUDAH LEWAT ===
    const now = new Date(); // Waktu user saat ini

    document.querySelectorAll(".time-btn").forEach((btn) => {
        // Ambil teks jam, misal "14:00"
        const timeText = btn.innerText.trim();
        
        // Buat objek Date untuk jadwal tayang (YYYY-MM-DD + T + HH:mm:00)
        // Contoh: "2023-12-25T14:00:00"
        const scheduleTime = new Date(`${selectedDate}T${timeText}:00`);

        // Bandingkan dengan waktu sekarang
        if (scheduleTime < now) {
            // SUDAH LEWAT: Matikan tombol
            btn.classList.add("disabled");
            btn.style.opacity = "0.4";
            btn.style.pointerEvents = "none"; // Tidak bisa diklik
            btn.style.cursor = "not-allowed";
            btn.style.border = "1px solid #ccc";
            
            // Jika tombol ini tadinya terpilih, batalkan pilihannya
            if (btn.classList.contains("selected")) {
                btn.classList.remove("selected");
                selectedTime = null; // Reset variable global
            }
        } else {
            // BELUM LEWAT: Hidupkan tombol
            btn.classList.remove("disabled");
            btn.style.opacity = "1";
            btn.style.pointerEvents = "auto"; // Bisa diklik lagi
            btn.style.cursor = "pointer";
            btn.style.border = ""; // Reset border ke CSS asli
        }
    });

    // === LOGIKA REFRESH MAP (YANG TADI) ===
    
    // Cek: Apakah User sudah memilih jam? (Dan jamnya masih valid/tidak didisable)
    if (selectedTime) {
        // A. Reset pilihan kursi user (Harga jadi 0) 
        resetSeats(); 

        // B. Ambil Data Kursi dari Database
        fetch(
            `/Project_TekWeb/MooBix/Booking-Logic/get_booked_seats.php?movie_id=${currentMovie.id}&date=${selectedDate}&time=${selectedTime}`
        )
        .then((response) => response.json())
        .then((occupiedSeats) => {
            // Bersihkan status merah lama
            document.querySelectorAll(".seat").forEach((seat) => {
                seat.classList.remove("occupied");
            });

            // Pasang status merah baru
            occupiedSeats.forEach((seatNum) => {
                const seatEl = Array.from(document.querySelectorAll(".seat")).find(
                    (el) => el.innerText === seatNum
                );
                if (seatEl) {
                    seatEl.classList.add("occupied");
                }
            });

            // Pastikan peta kursi tetap terbuka
            const seatMap = document.getElementById("seatMapArea");
            if (seatMap) {
                seatMap.style.display = "block";
            }
        })
        .catch((error) => {
            console.error("Error updating seats:", error);
        });

    } else {
        // Kalau jam belum dipilih atau jam yang dipilih ternyata sudah lewat (otomatis ter-unselect)
        // Sembunyikan peta kursi biar user pilih jam lagi yang valid
        const seatMap = document.getElementById("seatMapArea");
        if (seatMap) {
            seatMap.style.display = "none";
        }

        // Scroll ke area jam
        if (timeSlots) {
             timeSlots.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }
    }
}

function selectTime(element, time) {
  // 1. Reset seleksi jam sebelumnya
  document
    .querySelectorAll(".time-btn")
    .forEach((el) => el.classList.remove("selected"));

  // 2. Pilih jam baru
  element.classList.add("selected");
  selectedTime = time;

  // Opsional: Beri indikator loading jika koneksi lambat
  // document.getElementById('seatMapArea').style.opacity = '0.5';

  // 3. Ambil data kursi terisi
  fetch(
    `/Project_TekWeb/MooBix/Booking-Logic/get_booked_seats.php?movie_id=${currentMovie.id}&date=${selectedDate}&time=${time}`
  )
    .then((response) => response.json())
    .then((occupiedSeats) => {
      // A. Reset semua kursi dulu (Hapus status occupied & selected dari sesi sebelumnya)
      document.querySelectorAll(".seat").forEach((seat) => {
        seat.classList.remove("occupied", "selected");
      });

      // B. Tandai kursi yang occupied dari database
      occupiedSeats.forEach((seatNum) => {
        // Cari elemen kursi berdasarkan teks (misal "A1")
        const seatEl = Array.from(document.querySelectorAll(".seat")).find(
          (el) => el.innerText === seatNum
        );
        if (seatEl) {
          seatEl.classList.add("occupied");
        }
      });

      // C. Update Total Harga (karena kursi terreset, harga jadi 0)
      updateTotal();

      // D. BARU TAMPILKAN PETA KURSI DISINI (Agar sinkron)
      const seatMap = document.getElementById("seatMapArea");
      if (seatMap) {
        seatMap.style.display = "block";
        seatMap.scrollIntoView({ behavior: "smooth", block: "start" });
        // seatMap.style.opacity = '1'; // Balikkan opacity jika pakai loading
      }
    })
    .catch((error) => {
      console.error("Error fetching seats:", error);
      alert("Gagal memuat data kursi. Periksa koneksi internet Anda.");
    });
}

// ================= 2. SEAT LOGIC =================

function updateQty(change) {
  let newQty = ticketQty + change;
  // Limit minimal 1, maksimal 8 tiket
  if (newQty < 1) newQty = 1;
  if (newQty > 8) newQty = 8;

  ticketQty = newQty;
  document.getElementById("qtyDisplay").innerText = ticketQty;

  // Jika qty berubah, reset kursi yang dipilih agar user memilih ulang sesuai jumlah baru
  resetSeats();
}

// Initialize Seat Listeners (Dijalankan saat DOM Load)
document.addEventListener("DOMContentLoaded", () => {
  initializeSeatSelection();
});

function initializeSeatSelection() {
  document.querySelectorAll(".seat").forEach((seat) => {
    // Hapus listener lama biar gak double, lalu pasang yang baru
    seat.removeEventListener("click", handleSeatClick);
    seat.addEventListener("click", handleSeatClick);
  });
}

function handleSeatClick() {
  // Cek kursi occupied (sudah dibooking orang lain)
  if (this.classList.contains("occupied")) {
    alert("Maaf, kursi ini sudah terisi!");
    return;
  }

  // Logika Toggle (Pilih / Hapus Pilih)
  if (this.classList.contains("selected")) {
    // Unselect
    this.classList.remove("selected");
  } else {
    // Select
    const currentSelected = document.querySelectorAll(".seat.selected").length;
    if (currentSelected >= ticketQty) {
      alert(
        `Anda hanya memesan ${ticketQty} tiket. Silakan tambah jumlah tiket jika ingin memilih lebih banyak kursi.`
      );
      return;
    }
    this.classList.add("selected");
  }

  updateTotal();
}

function resetSeats() {
  document
    .querySelectorAll(".seat.selected")
    .forEach((s) => s.classList.remove("selected"));
  updateTotal();
}

function updateTotal() {
  const selectedCount = document.querySelectorAll(".seat.selected").length;
  const priceToUse = currentMovie.price || 0;
  const total = selectedCount * priceToUse;

  const totalEl = document.getElementById("totalPrice");
  if (totalEl) totalEl.innerText = formatRupiah(total);
}

// ================= 3. CONFIRMATION & PAYMENT =================

function showConfirmation() {
  const selectedSeats = document.querySelectorAll(".seat.selected");

  // Validasi 1: Belum pilih kursi
  if (selectedSeats.length === 0) {
    alert("Silakan pilih kursi terlebih dahulu!");
    return;
  }

  // Validasi 2: Jumlah kursi tidak sesuai tiket
  if (selectedSeats.length !== ticketQty) {
    alert(
      `Anda memesan ${ticketQty} tiket, tapi baru memilih ${selectedSeats.length} kursi.`
    );
    return;
  }

  // Validasi 3: Belum pilih tanggal/jam
  if (!selectedDate || !selectedTime) {
    alert("Silakan pilih tanggal dan waktu terlebih dahulu!");
    return;
  }

  // Format Tanggal Cantik (Senin, 12 Agustus 2024)
  const dateObj = new Date(selectedDate);
  const formattedDate = dateObj.toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });

  // Ambil nomor kursi & Urutkan (A1, A2, B1...)
  const seatNumbers = Array.from(selectedSeats).map((s) => s.innerText);
  seatNumbers.sort((a, b) => {
    const rowA = a.charAt(0);
    const rowB = b.charAt(0);
    const numA = parseInt(a.substring(1));
    const numB = parseInt(b.substring(1));
    if (rowA !== rowB) return rowA.localeCompare(rowB);
    return numA - numB;
  });

  const seatNames = seatNumbers.join(", ");
  const totalText = document.getElementById("totalPrice").innerText;

  // Isi Modal Konfirmasi
  document.getElementById("confMovie").innerText = currentMovie.title;
  document.getElementById("confDate").innerText = formattedDate;
  document.getElementById("confTime").innerText = selectedTime;
  document.getElementById("confSeats").innerText = seatNames;
  document.getElementById("confTotal").innerText = totalText;

  // Pindah ke Step Konfirmasi
  toggleDisplay("step-schedule", false);
  toggleDisplay("step-confirm", true);
}

// Enhanced Payment Processing
// ui_script.js

function processPayment() {
  const payButton = document.querySelector(".btn-pay-now");

  // Validasi sederhana
  if (
    !selectedDate ||
    !selectedTime ||
    document.querySelectorAll(".seat.selected").length === 0
  ) {
    alert("Mohon lengkapi data pemesanan (Tanggal, Jam, Kursi).");
    return;
  }

  // Ambil data kursi & harga
  const selectedSeatsElements = document.querySelectorAll(".seat.selected");
  const seatNumbers = Array.from(selectedSeatsElements).map((s) => s.innerText);
  const totalPriceClean = parseInt(
    document.getElementById("totalPrice").innerText.replace(/[^0-9]/g, "")
  );

  // Siapkan Data untuk dikirim ke Backend
  const bookingData = {
    movie_id: currentMovie.id, // ID dari Langkah 1 tadi
    show_date: selectedDate,
    show_time: selectedTime,
    seats: seatNumbers,
    total_price: totalPriceClean,
  };

  // UI Loading
  payButton.innerHTML =
    '<i class="ph ph-circle-notch ph-spin"></i> MEMPROSES...';
  payButton.disabled = true;

  // KIRIM KE DATABASE (process_booking.php)
  fetch("/Project_TekWeb/MooBix/Booking-Logic/process_booking.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(bookingData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // === SUKSES! ===

        // 1. Tampilkan Pesan Konfirmasi
        const confirmBox = document.querySelector(".confirm-box");
        confirmBox.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <h3 style="color: green;">PEMBAYARAN BERHASIL!</h3>
                <p>Kode Booking: <strong>${data.booking_code}</strong></p>
                <button class="btn-primary" onclick="window.location.href='my_tickets.php'">LIHAT TIKET SAYA</button>
            </div>
        `;

        // 2. HILANGKAN TOMBOL BAYAR SEKARANG (Tambahkan Kode Ini)
        if (payButton) {
            const buttonContainer = payButton.parentElement; // Ambil elemen pembungkus tombol
            if (buttonContainer) {
                buttonContainer.style.display = "none"; // Hilangkan pembungkusnya (otomatis isinya hilang semua)
            } else {
                // Jaga-jaga kalau tidak punya pembungkus, kita hide manual
                payButton.style.display = "none";
            }
        }

        // 3. Hilangkan container tombol (Opsional, biar lebih bersih)
        const btnContainer = document.querySelector(".confirm-buttons");
        if (btnContainer) {
          btnContainer.style.display = "none";
        }
      } else {
        // === GAGAL (Kursi diambil orang / error validasi) ===
        alert("Gagal: " + data.message);

        // Reset tombol agar bisa dicoba lagi
        payButton.innerHTML = "Bayar Sekarang";
        payButton.disabled = false;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan koneksi.");

      // Reset tombol agar bisa dicoba lagi
      payButton.innerHTML = "Bayar Sekarang";
      payButton.disabled = false;
    });
}

// Reset booking flow
function resetBookingFlow() {
  // Reset steps
  document.getElementById("step-info").style.display = "block";
  document.getElementById("step-schedule").style.display = "none";
  document.getElementById("step-seats").style.display = "none";
  document.getElementById("step-confirm").style.display = "none";

  // Reset selections
  document.getElementById("qtyDisplay").textContent = "1";
  document.getElementById("totalPrice").textContent = "0";

  // Reset seat selections
  document.querySelectorAll(".seat.selected").forEach((seat) => {
    seat.classList.remove("selected");
  });

  // Reset time selection if using the old version
  document.querySelectorAll(".time-btn.selected").forEach((btn) => {
    btn.classList.remove("selected");
  });

  document.querySelectorAll(".date-item.selected").forEach((item) => {
    item.classList.remove("selected");
  });
}

// ================= 4. ADMIN FUNCTIONS =================

function openAdminModal(type) {
  const contentDiv = document.getElementById("adminModalContent");
  if (!contentDiv) return;

  let html = "";

  // Logic konten modal berdasarkan tipe tombol yang diklik
  switch (type) {
    case "addMovie":
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
    case "editMovies":
      html = `<h2>Edit Movies</h2><p>Pilih film dari tabel utama lalu klik icon pensil.</p>`;
      break;
    default:
      html = `<h2>Admin Menu</h2><p>Fitur <b>${type}</b> sedang dalam pengembangan.</p>`;
  }

  contentDiv.innerHTML = html;
  toggleModal("adminModal", true);
}

function addNewMovie() {
  const title = document.getElementById("newTitle").value;
  if (!title) {
    alert("Judul film tidak boleh kosong");
    return;
  }

  alert(`Film "${title}" berhasil ditambahkan (Simulasi Database)`);
  toggleModal("adminModal", false);
}

function editMovie(id) {
  alert(`Membuka form edit untuk Movie ID: ${id}`);
  // Di real app, ini akan fetch data film lalu buka modal edit
}

function confirmDelete(id, title) {
  if (confirm(`Apakah Anda yakin ingin menghapus film "${title}"?`)) {
    alert(`Film "${title}" telah dihapus.`);
    // Di real app, panggil AJAX delete disini
    location.reload();
  }
}

function viewDetails(id) {
  alert(`Melihat detail Movie ID: ${id}`);
}

function featureMovie(id) {
  if (confirm("Jadikan film ini sebagai Featured Movie (Highlight Utama)?")) {
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
  if (el) el.style.display = show ? "block" : "none";
}

// Helper untuk Toggle Modal (Flex/None)
function toggleModal(modalId, show) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = show ? "flex" : "none";
    // Kunci scroll body saat modal terbuka
    document.body.style.overflow = show ? "hidden" : "auto";
  }
}

// Helper Format Rupiah
function formatRupiah(amount) {
  return new Intl.NumberFormat("id-ID").format(amount);
}

// Menutup modal saat klik di luar area konten (Overlay)
window.onclick = function (event) {
  if (event.target.classList.contains("modal-overlay")) {
    event.target.style.display = "none";
    document.body.style.overflow = "auto";
  }
};

// Tombol Close (X) di Modal
document.querySelectorAll(".close-modal").forEach((btn) => {
  btn.addEventListener("click", function () {
    const modal = this.closest(".modal-overlay");
    if (modal) {
      modal.style.display = "none";
      document.body.style.overflow = "auto";
    }
  });
});

// Search Filter (Client Side)
const searchInput = document.getElementById("searchInput");
if (searchInput) {
  searchInput.addEventListener("input", (e) => {
    const term = e.target.value.toLowerCase().trim();
    const movieCards = document.querySelectorAll(".movie-card");

    movieCards.forEach((card) => {
      const title = card.querySelector("h3").innerText.toLowerCase();
      const genre = card.querySelector("small").innerText.toLowerCase();
      const isVisible = title.includes(term) || genre.includes(term);
      card.style.display = isVisible ? "" : "none";
    });
  });
}

// Profile Dropdown Toggle
document.addEventListener("DOMContentLoaded", () => {
  const profileTrigger = document.querySelector(".profile-trigger");
  if (profileTrigger) {
    profileTrigger.addEventListener("click", function (e) {
      e.stopPropagation(); // Mencegah event bubbling
      const dropdown = this.parentElement.querySelector(".dropdown-menu");
      if (dropdown) {
        dropdown.style.display =
          dropdown.style.display === "block" ? "none" : "block";
      }
    });

    // Tutup dropdown jika klik di tempat lain
    document.addEventListener("click", () => {
      const dropdown = document.querySelector(".dropdown-menu");
      if (dropdown) dropdown.style.display = "none";
    });
  }
});

// Horizontal Scroll Slider (Tombol Kiri/Kanan di List Film)
function scrollMovies(amount) {
  const movieList = document.getElementById("movieList");
  if (movieList) {
    movieList.scrollBy({ left: amount, behavior: "smooth" });
  }
}

// ================= 6. MY TICKETS & HISTORY FUNCTIONS =================

// Download Ticket
function downloadTicket(bookingCode) {
  alert(
    `Download tiket dengan kode: ${bookingCode}\nFitur ini akan mengunduh file PDF tiket Anda.`
  );
  // Di sini bisa diimplementasikan AJAX untuk generate PDF
}

// Print Ticket
function printTicket() {
  window.print();
}

// Proceed Payment (untuk pending tickets)
function proceedPayment(ticketId) {
  if (confirm("Lanjutkan pembayaran untuk tiket ini?")) {
    alert(`Mengarahkan ke halaman pembayaran untuk tiket ID: ${ticketId}`);
    // window.location.href = `payment.php?id=${ticketId}`;
  }
}

// Filter transactions by time period
function filterTransactions(period) {
  const items = document.querySelectorAll(".history-item");
  const filterBtns = document.querySelectorAll(".filter-btn");
  const now = new Date();

  // Update active button
  filterBtns.forEach((btn) => btn.classList.remove("active"));
  event.target.classList.add("active");

  items.forEach((item) => {
    const showDateStr = item.getAttribute("data-show-date");
    const showDate = new Date(showDateStr);
    let show = true;

    switch (period) {
      case "this_month":
        show =
          showDate.getMonth() === now.getMonth() &&
          showDate.getFullYear() === now.getFullYear();
        break;
      case "last_month":
        const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        show =
          showDate.getMonth() === lastMonth.getMonth() &&
          showDate.getFullYear() === lastMonth.getFullYear();
        break;
      case "this_year":
        show = showDate.getFullYear() === now.getFullYear();
        break;
      case "all":
      default:
        show = true;
    }

    item.style.display = show ? "" : "none";
  });
}

// Write Review
function writeReview(transactionId, movieTitle) {
  const review = prompt(
    `Tulis review untuk film "${movieTitle}" (1-5 bintang):`
  );
  if (review !== null && review.trim() !== "") {
    const rating = parseInt(review);
    if (rating >= 1 && rating <= 5) {
      alert(
        `Terima kasih! Review Anda untuk "${movieTitle}" telah disimpan: ${rating}/5 bintang.`
      );
      // Di sini bisa diimplementasikan AJAX untuk save review
    } else {
      alert("Harap beri rating 1-5 bintang!");
    }
  }
}

// Download Receipt
function downloadReceipt(bookingCode) {
  alert(
    `Download struk untuk kode: ${bookingCode}\nStruk akan diunduh dalam format PDF.`
  );
  // Di sini bisa diimplementasikan AJAX untuk generate PDF receipt
}

// ================= 7. EDIT PROFILE FUNCTIONS =================

// Password toggle
function setupPasswordToggle() {
  const toggleButtons = document.querySelectorAll(".toggle-password");
  toggleButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const input = this.parentElement.querySelector("input");
      if (input.type === "password") {
        input.type = "text";
        this.innerHTML = '<i class="ph ph-eye-slash"></i>';
      } else {
        input.type = "password";
        this.innerHTML = '<i class="ph ph-eye"></i>';
      }
    });
  });
}

// Form validation for profile
function setupProfileValidation() {
  const profileForm = document.getElementById("profileForm");
  const passwordForm = document.getElementById("passwordForm");

  if (profileForm) {
    profileForm.addEventListener("submit", function (e) {
      const usernameInput = this.querySelector('input[name="username"]');
      const emailInput = this.querySelector('input[name="email"]');
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      // Validasi username
      if (usernameInput.value.trim().length < 3) {
        e.preventDefault();
        alert("Username minimal 3 karakter!");
        usernameInput.focus();
        return;
      }

      // Validasi email
      if (!emailRegex.test(emailInput.value)) {
        e.preventDefault();
        alert("Format email tidak valid!");
        emailInput.focus();
        return;
      }

      // Tampilkan loading
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.innerHTML =
        '<i class="ph ph-circle-notch ph-spin"></i> Menyimpan...';
      submitBtn.disabled = true;
    });

    // Real-time username validation
    const usernameInput = document.querySelector('input[name="username"]');
    if (usernameInput) {
      usernameInput.addEventListener("input", function () {
        const username = this.value;
        const errorSpan = document.getElementById("username-error");

        if (username.length > 0 && username.length < 3) {
          errorSpan.textContent = "Username minimal 3 karakter";
          errorSpan.style.color = "#ff6b6b";
        } else if (username.includes(" ")) {
          errorSpan.textContent = "Username tidak boleh mengandung spasi";
          errorSpan.style.color = "#ff6b6b";
        } else if (username.length >= 3) {
          errorSpan.textContent = "✓ Username valid";
          errorSpan.style.color = "#28a745";
        } else {
          errorSpan.textContent = "";
        }
      });
    }

    // Real-time email validation
    const emailInput = document.querySelector('input[name="email"]');
    if (emailInput) {
      emailInput.addEventListener("input", function () {
        const email = this.value;
        const errorSpan = document.getElementById("email-error");
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email.length > 0 && !emailRegex.test(email)) {
          errorSpan.textContent = "Format email tidak valid";
          errorSpan.style.color = "#ff6b6b";
        } else if (emailRegex.test(email)) {
          errorSpan.textContent = "✓ Format email valid";
          errorSpan.style.color = "#28a745";
        } else {
          errorSpan.textContent = "";
        }
      });
    }
  }

  if (passwordForm) {
    passwordForm.addEventListener("submit", function (e) {
      const currentPass = this.querySelector('input[name="current_password"]');
      const newPass = this.querySelector('input[name="new_password"]');
      const confirmPass = this.querySelector('input[name="confirm_password"]');

      // Validasi panjang password baru
      if (newPass.value.length < 6) {
        e.preventDefault();
        alert("Password baru minimal 6 karakter!");
        newPass.focus();
        return;
      }

      // Validasi konfirmasi password
      if (newPass.value !== confirmPass.value) {
        e.preventDefault();
        alert("Password baru tidak cocok!");
        confirmPass.focus();
        return;
      }

      // Validasi password saat ini tidak boleh sama dengan yang baru
      if (currentPass.value === newPass.value) {
        e.preventDefault();
        alert("Password baru tidak boleh sama dengan password saat ini!");
        newPass.focus();
        return;
      }

      // Tampilkan loading
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.innerHTML =
        '<i class="ph ph-circle-notch ph-spin"></i> Mengubah...';
      submitBtn.disabled = true;
    });

    // Real-time password strength checker
    const newPasswordInput = document.querySelector(
      'input[name="new_password"]'
    );
    const confirmPasswordInput = document.querySelector(
      'input[name="confirm_password"]'
    );

    if (newPasswordInput && confirmPasswordInput) {
      newPasswordInput.addEventListener("input", function () {
        const password = this.value;
        const strengthSpan = document.getElementById("password-strength");

        if (password.length === 0) {
          strengthSpan.textContent = "";
        } else if (password.length < 6) {
          strengthSpan.textContent = "⚠️ Lemah (minimal 6 karakter)";
          strengthSpan.style.color = "#ff6b6b";
        } else if (password.length < 8) {
          strengthSpan.textContent = "⚠️ Sedang";
          strengthSpan.style.color = "#ffc107";
        } else {
          strengthSpan.textContent = "✓ Kuat";
          strengthSpan.style.color = "#28a745";
        }

        // Cek match dengan konfirmasi password
        checkPasswordMatch();
      });

      confirmPasswordInput.addEventListener("input", checkPasswordMatch);

      function checkPasswordMatch() {
        const newPass = newPasswordInput.value;
        const confirmPass = confirmPasswordInput.value;
        const matchSpan = document.getElementById("password-match");

        if (confirmPass.length === 0) {
          matchSpan.textContent = "";
        } else if (newPass === confirmPass && newPass.length >= 6) {
          matchSpan.textContent = "✓ Password cocok";
          matchSpan.style.color = "#28a745";
        } else if (newPass !== confirmPass && confirmPass.length > 0) {
          matchSpan.textContent = "✗ Password tidak cocok";
          matchSpan.style.color = "#ff6b6b";
        } else {
          matchSpan.textContent = "";
        }
      }
    }
  }
}

// ================= 8. HOVER EFFECTS FOR TICKETS & HISTORY =================
function setupTicketHoverEffects() {
  const ticketItems = document.querySelectorAll(".ticket-item, .history-item");
  ticketItems.forEach((item) => {
    item.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-8px)";
      this.style.boxShadow = "0 15px 40px rgba(0,0,0,0.6)";
    });

    item.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
      this.style.boxShadow = "0 10px 30px rgba(0,0,0,0.5)";
    });
  });
}

// ================= 9. SEARCH FUNCTIONALITY =================
function setupSearch() {
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase();
      const items = document.querySelectorAll(".ticket-item, .history-item");

      items.forEach((item) => {
        const title =
          item
            .querySelector(".movie-title, .history-title")
            ?.textContent.toLowerCase() || "";
        const code =
          item
            .querySelector(".ticket-code, .history-code")
            ?.textContent.toLowerCase() || "";
        const genre =
          item
            .querySelector(".movie-genre, .history-genre")
            ?.textContent.toLowerCase() || "";

        if (
          title.includes(searchTerm) ||
          code.includes(searchTerm) ||
          genre.includes(searchTerm)
        ) {
          item.style.display = "";
        } else {
          item.style.display = "none";
        }
      });
    });
  }
}

// ================= 10. INITIALIZE ALL FEATURES =================
document.addEventListener("DOMContentLoaded", function () {
  // Profile dropdown untuk semua halaman
  setupProfileDropdown();

  // Search functionality
  setupSearch();

  // Ticket/history hover effects
  setupTicketHoverEffects();

  // Profile page specific
  if (document.getElementById("profileForm")) {
    setupPasswordToggle();
    setupProfileValidation();
  }

  // Booking flow (jika ada di halaman)
  if (document.querySelector(".seat")) {
    initializeSeatSelection();
  }
});

// ================= 11. PROFILE DROPDOWN HANDLER =================
function setupProfileDropdown() {
  const profileTrigger = document.querySelector(".profile-trigger");
  if (profileTrigger) {
    profileTrigger.addEventListener("click", function (e) {
      e.stopPropagation();
      const dropdown = this.parentElement.querySelector(".dropdown-menu");
      if (dropdown) {
        const isVisible = dropdown.style.display === "block";
        document.querySelectorAll(".dropdown-menu").forEach((d) => {
          d.style.display = "none";
        });
        dropdown.style.display = isVisible ? "none" : "block";
      }
    });
  }

  // Close dropdown ketika klik di luar
  document.addEventListener("click", function () {
    document.querySelectorAll(".dropdown-menu").forEach((dropdown) => {
      dropdown.style.display = "none";
    });
  });

  // Prevent dropdown close ketika klik di dalam dropdown
  document.querySelectorAll(".dropdown-menu").forEach((dropdown) => {
    dropdown.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  });
}

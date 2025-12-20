let currentMovie = {};
let selectedDate = null;
let selectedTime = null;
let ticketQty = 1;
let bookingTimer;
let currentTransactionId = null;

function openBookingFlow(id, title, poster, synopsis, price, duration, rating) {
  console.log("Judul:", title);
  console.log("Rating diterima:", rating);

  if (typeof PHP_DATA !== "undefined" && !PHP_DATA.isLoggedIn) {
    alert("Silakan login terlebih dahulu untuk memesan tiket!");
    toggleModal("loginModal", true);
    return; 
  }
  
  currentMovie = {
    id: id, 
    title: title,
    poster: poster,
    synopsis: synopsis,
    price: parseInt(price),
    duration: duration,
    rating: rating,
  };

  document.getElementById("modalTitle").innerText = title;

  const imgEl = document.getElementById("modalPoster");
  if (imgEl) imgEl.src = poster;

  document.getElementById("modalSynopsis").innerText = synopsis;

  const durEl = document.getElementById("modalDuration");
  if (durEl) {
    let cleanDuration = duration.replace(/[^\x00-\x7F]/g, "").trim();
    cleanDuration = cleanDuration.replace(/\s+/g, " ");
    durEl.innerText = cleanDuration;
  }

  const rateEl = document.getElementById("modalRating");
  if (rateEl) {
    rateEl.innerText = rating && rating !== "null" ? rating : "0.0";
  }

  if (typeof resetBookingSteps === "function") resetBookingSteps();

  const modal = document.getElementById("bookingModal");
  if (modal) {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
  }
}

function resetBookingSteps() {
  toggleDisplay("step-info", true);
  toggleDisplay("step-schedule", false);
  toggleDisplay("step-confirm", false);
  toggleDisplay("timeSlots", false);
  toggleDisplay("seatMapArea", false);

  selectedDate = null;
  selectedTime = null;
  ticketQty = 1;

  const qtyDisplay = document.getElementById("qtyDisplay");
  if (qtyDisplay) qtyDisplay.innerText = ticketQty;

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
    if (confirm("Batalkan pesanan ini dan pilih kursi ulang?")) {
        document.querySelectorAll(".seat").forEach(s => s.classList.remove("selected", "occupied"));
        
        cancelCurrentBooking().then((data) => {
            if (data && data.success) {
                toggleDisplay("step-confirm", false);
                toggleDisplay("step-schedule", true);
                const activeTimeBtn = document.querySelector(".time-btn.selected");
                if (activeTimeBtn) selectTime(activeTimeBtn, selectedTime);
            }
        });
    }
}

function selectDate(element, dateValue) {
  document
    .querySelectorAll(".date-item")
    .forEach((el) => el.classList.remove("selected"));

  element.classList.add("selected");
  selectedDate = dateValue;

  const timeSlots = document.getElementById("timeSlots");
  if (timeSlots) {
    timeSlots.style.display = "block";
  }

  const now = new Date(); 

  document.querySelectorAll(".time-btn").forEach((btn) => {
    const timeText = btn.innerText.trim();
    const scheduleTime = new Date(`${selectedDate}T${timeText}:00`);

    if (scheduleTime < now) {
      btn.classList.add("disabled");
      btn.style.opacity = "0.4";
      btn.style.pointerEvents = "none"; 
      btn.style.cursor = "not-allowed";
      btn.style.border = "1px solid #ccc";

      if (btn.classList.contains("selected")) {
        btn.classList.remove("selected");
        selectedTime = null; 
      }
    } else {
      btn.classList.remove("disabled");
      btn.style.opacity = "1";
      btn.style.pointerEvents = "auto"; 
      btn.style.cursor = "pointer";
      btn.style.border = ""; 
    }
  });

  if (selectedTime) {
    resetSeats();
    fetch(
      `../Booking-Logic/get_booked_seats.php?movie_id=${currentMovie.id}&date=${selectedDate}&time=${selectedTime}`
    )
      .then((response) => response.json())
      .then((occupiedSeats) => {
        document.querySelectorAll(".seat").forEach((seat) => {
          seat.classList.remove("occupied");
        });

        occupiedSeats.forEach((seatNum) => {
          const seatEl = Array.from(document.querySelectorAll(".seat")).find(
            (el) => el.innerText === seatNum
          );
          if (seatEl) {
            seatEl.classList.add("occupied");
          }
        });

        const seatMap = document.getElementById("seatMapArea");
        if (seatMap) {
          seatMap.style.display = "block";
        }
      })
      .catch((error) => {
        console.error("Error updating seats:", error);
      });
  } else {
    const seatMap = document.getElementById("seatMapArea");
    if (seatMap) {
      seatMap.style.display = "none";
    }

    if (timeSlots) {
      timeSlots.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
  }
}

function selectTime(element, time) {
  document
    .querySelectorAll(".time-btn")
    .forEach((el) => el.classList.remove("selected"));

  element.classList.add("selected");
  selectedTime = time;

  fetch(
    `../Booking-Logic/get_booked_seats.php?movie_id=${currentMovie.id}&date=${selectedDate}&time=${time}`
  )
    .then((response) => response.json())
    .then((occupiedSeats) => {
      document.querySelectorAll(".seat").forEach((seat) => {
        seat.classList.remove("occupied", "selected");
      });

      occupiedSeats.forEach((seatNum) => {
        const seatEl = Array.from(document.querySelectorAll(".seat")).find(
          (el) => el.innerText === seatNum
        );
        if (seatEl) {
          seatEl.classList.add("occupied");
        }
      });

      updateTotal();

      const seatMap = document.getElementById("seatMapArea");
      if (seatMap) {
        seatMap.style.display = "block";
        seatMap.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    })
    .catch((error) => {
      console.error("Error fetching seats:", error);
      alert("Gagal memuat data kursi. Periksa koneksi internet Anda.");
    });
}

function updateQty(change) {
  let newQty = ticketQty + change;
  if (newQty < 1) newQty = 1;
  if (newQty > 8) newQty = 8;

  ticketQty = newQty;
  document.getElementById("qtyDisplay").innerText = ticketQty;

  resetSeats();
}

document.addEventListener("DOMContentLoaded", () => {
  initializeSeatSelection();
});

function initializeSeatSelection() {
  document.querySelectorAll(".seat").forEach((seat) => {
    seat.removeEventListener("click", handleSeatClick);
    seat.addEventListener("click", handleSeatClick);
  });
}

function handleSeatClick() {
  if (this.classList.contains("occupied")) {
    alert("Maaf, kursi ini sudah terisi!");
    return;
  }

  if (this.classList.contains("selected")) {
    this.classList.remove("selected");
  } else {
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

function showConfirmation() {
  const selectedSeats = document.querySelectorAll(".seat.selected");

  if (selectedSeats.length === 0) {
    alert("Silakan pilih kursi terlebih dahulu!");
    return;
  }

  if (selectedSeats.length !== ticketQty) {
    alert(
      `Anda memesan ${ticketQty} tiket, tapi baru memilih ${selectedSeats.length} kursi.`
    );
    return;
  }

  if (!selectedDate || !selectedTime) {
    alert("Silakan pilih tanggal dan waktu terlebih dahulu!");
    return;
  }

  const dateObj = new Date(selectedDate);
  const formattedDate = dateObj.toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });

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

  document.getElementById("confMovie").innerText = currentMovie.title;
  document.getElementById("confDate").innerText = formattedDate;
  document.getElementById("confTime").innerText = selectedTime;
  document.getElementById("confSeats").innerText = seatNames;
  document.getElementById("confTotal").innerText = totalText;

  toggleDisplay("step-schedule", false);
  toggleDisplay("step-confirm", true);

  const bookingData = {
    movie_id: currentMovie.id,
    show_date: selectedDate,
    show_time: selectedTime,
    seats: Array.from(document.querySelectorAll(".seat.selected")).map(
      (s) => s.innerText
    ),
    total_price: parseInt(
      document.getElementById("totalPrice").innerText.replace(/[^0-9]/g, "")
    ),
  };

fetch("../Booking-Logic/reserve_seats.php", {
    method: "POST",
    headers: {
        "Content-Type": "application/json" 
    },
    body: JSON.stringify(bookingData),
})
.then(async (res) => {
    const contentType = res.headers.get("content-type");
    if (!contentType || !contentType.includes("application/json")) {
        const text = await res.text();
        throw new Error("Server mengembalikan format non-JSON: " + text);
    }
    return res.json();
})
.then((data) => {
    if (data.success) {
        currentTransactionId = data.transaction_id;
        startTimer(600); // Mulai timer 10 menit
        toggleDisplay("step-schedule", false);
        toggleDisplay("step-confirm", true);
    } else {
        alert("Gagal mengunci kursi: " + data.message);
    }
})
.catch((err) => {
    console.error("Kesalahan koneksi/sistem:", err);
    alert("Terjadi kesalahan sistem. Cek konsol (F12) untuk detail.");
});
}

function startTimer(duration) {
  let timer = duration,
    minutes,
    seconds;
  clearInterval(bookingTimer);

  bookingTimer = setInterval(function () {
    minutes = parseInt(timer / 60, 10);
    seconds = parseInt(timer % 60, 10);

    minutes = minutes < 10 ? "0" + minutes : minutes;
    seconds = seconds < 10 ? "0" + seconds : seconds;

    const display = document.getElementById("timerDisplay");
    if (display) display.innerText = minutes + ":" + seconds;

    if (--timer < 0) {
      clearInterval(bookingTimer);
      alert("Waktu habis! Kursi dilepaskan otomatis.");
      cancelCurrentBooking().then(() => {
        window.location.reload(); 
      });
    }
  }, 1000);
}

function cancelCurrentBooking() {
    if (!currentTransactionId) return Promise.resolve({success: true});

    return fetch("../Booking-Logic/cancel_pending.php", { 
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ transaction_id: currentTransactionId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log("Booking berhasil dibatalkan.");
            currentTransactionId = null;
            clearInterval(bookingTimer);
        } else {
            console.error("Gagal hapus di DB:", data.message);
        }
        return data; 
    });
}

function processPayment() {
  const payButton = document.querySelector(".btn-pay-now");

  if (!currentTransactionId) {
    alert("Sesi booking tidak valid. Silakan pilih ulang.");
    return;
  }

  payButton.innerHTML =
    '<i class="ph ph-circle-notch ph-spin"></i> MEMPROSES...';
  payButton.disabled = true;

  fetch("../Booking-Logic/process_booking.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ transaction_id: currentTransactionId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        clearInterval(bookingTimer);

        const timerCont = document.getElementById("timerContainer");
        if (timerCont) timerCont.style.display = "none";

        const confirmBox = document.querySelector(".confirm-box");
        confirmBox.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <h3 style="color: green;">PEMBAYARAN BERHASIL!</h3>
                <p>Kode Booking: <strong>${data.booking_code}</strong></p>
                <button class="btn-primary" onclick="window.location.href='my_tickets.php'">LIHAT TIKET SAYA</button>
            </div>
        `;
        document.getElementById("timerContainer").style.display = "none";
        payButton.parentElement.style.display = "none";
      } else {
        alert("Gagal: " + data.message);
        payButton.innerHTML = "PAY NOW";
        payButton.disabled = false;
      }
    });
}

function resetBookingFlow() {
  document.getElementById("step-info").style.display = "block";
  document.getElementById("step-schedule").style.display = "none";
  document.getElementById("step-seats").style.display = "none";
  document.getElementById("step-confirm").style.display = "none";

  document.getElementById("qtyDisplay").textContent = "1";
  document.getElementById("totalPrice").textContent = "0";

  document.querySelectorAll(".seat.selected").forEach((seat) => {
    seat.classList.remove("selected");
  });

  document.querySelectorAll(".time-btn.selected").forEach((btn) => {
    btn.classList.remove("selected");
  });

  document.querySelectorAll(".date-item.selected").forEach((item) => {
    item.classList.remove("selected");
  });
}

function openAdminModal(type) {
  const contentDiv = document.getElementById("adminModalContent");
  if (!contentDiv) return;

  let html = "";

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
}

function confirmDelete(id, title) {
  if (confirm(`Apakah Anda yakin ingin menghapus film "${title}"?`)) {
    alert(`Film "${title}" telah dihapus.`);
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

function toggleDisplay(elementId, show) {
  const el = document.getElementById(elementId);
  if (el) el.style.display = show ? "block" : "none";
}

function toggleModal(modalId, show) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = show ? "flex" : "none";
    document.body.style.overflow = show ? "hidden" : "auto";
  }
}

function formatRupiah(amount) {
  return new Intl.NumberFormat("id-ID").format(amount);
}

window.onclick = function (event) {
  if (event.target.classList.contains("modal-overlay")) {
    event.target.style.display = "none";
    document.body.style.overflow = "auto";
  }
};

document.querySelectorAll(".close-modal").forEach((btn) => {
  btn.addEventListener("click", function () {
    const modal = this.closest(".modal-overlay");
    if (modal) {
      modal.style.display = "none";
      document.body.style.overflow = "auto";
    }
  });
});

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

document.addEventListener("DOMContentLoaded", () => {
  const profileDropdown = document.querySelector(".profile-dropdown");
  const profileTrigger = document.querySelector(".profile-trigger");

  if (profileTrigger && profileDropdown) {
    profileTrigger.addEventListener("click", function (e) {
      e.stopPropagation(); 
      profileDropdown.classList.toggle("active");
    });

    document.addEventListener("click", (e) => {
      if (!profileDropdown.contains(e.target)) {
        profileDropdown.classList.remove("active");
      }
    });
  }
});

function scrollMovies(amount) {
  const movieList = document.getElementById("movieList");
  if (movieList) {
    movieList.scrollBy({ left: amount, behavior: "smooth" });
  }
}

function filterTransactions(period) {
  const items = document.querySelectorAll(".history-item");
  const filterBtns = document.querySelectorAll(".filter-btn");
  const now = new Date();

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
    } else {
      alert("Harap beri rating 1-5 bintang!");
    }
  }
}

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

function setupProfileValidation() {
  const profileForm = document.getElementById("profileForm");
  const passwordForm = document.getElementById("passwordForm");

  if (profileForm) {
    profileForm.addEventListener("submit", function (e) {
      const usernameInput = this.querySelector('input[name="username"]');
      const emailInput = this.querySelector('input[name="email"]');
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (usernameInput.value.trim().length < 3) {
        e.preventDefault();
        alert("Username minimal 3 karakter!");
        usernameInput.focus();
        return;
      }

      if (!emailRegex.test(emailInput.value)) {
        e.preventDefault();
        alert("Format email tidak valid!");
        emailInput.focus();
        return;
      }

      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.innerHTML =
        '<i class="ph ph-circle-notch ph-spin"></i> Menyimpan...';
      submitBtn.disabled = true;
    });

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

      if (newPass.value.length < 6) {
        e.preventDefault();
        alert("Password baru minimal 6 karakter!");
        newPass.focus();
        return;
      }

      if (newPass.value !== confirmPass.value) {
        e.preventDefault();
        alert("Password baru tidak cocok!");
        confirmPass.focus();
        return;
      }

      if (currentPass.value === newPass.value) {
        e.preventDefault();
        alert("Password baru tidak boleh sama dengan password saat ini!");
        newPass.focus();
        return;
      }

      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.innerHTML =
        '<i class="ph ph-circle-notch ph-spin"></i> Mengubah...';
      submitBtn.disabled = true;
    });

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

document.addEventListener("DOMContentLoaded", function () {
  setupProfileDropdown();

  setupSearch();

  setupTicketHoverEffects();

  if (document.getElementById("profileForm")) {
    setupPasswordToggle();
    setupProfileValidation();
  }

  if (document.querySelector(".seat")) {
    initializeSeatSelection();
  }
});

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

  document.addEventListener("click", function () {
    document.querySelectorAll(".dropdown-menu").forEach((dropdown) => {
      dropdown.style.display = "none";
    });
  });

  document.querySelectorAll(".dropdown-menu").forEach((dropdown) => {
    dropdown.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  });
}

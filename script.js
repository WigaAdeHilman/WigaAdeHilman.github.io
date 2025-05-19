const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');

hamburger.addEventListener('click', () => {
  navLinks.classList.toggle('active');
});

$(document).ready(function () {
  $('#contactForm').on('submit', function (e) {
    e.preventDefault(); // Cegah submit form

    // Ambil nilai
    let name = $('#name').val().trim();
    let email = $('#email').val().trim();
    let phone = $('#phone').val().trim();
    let message = $('#message').val().trim();

    // Validasi
    let isValid = true;
    let errorMessage = "";

    // Validasi Nama
    if (name === "" || name.length > 50) {
      isValid = false;
      errorMessage += "Nama harus diisi dan maksimal 50 karakter.\n";
    }

    // Validasi Email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email) || email.length > 50) {
      isValid = false;
      errorMessage += "Email tidak valid dan maksimal 50 karakter.\n";
    }

    // Validasi Nomor HP
    const phoneRegex = /^[0-9]{10,15}$/;
    if (!phoneRegex.test(phone)) {
      isValid = false;
      errorMessage += "Nomor HP harus angka dan panjang 10-15 digit.\n";
    }

    // Validasi Pesan
    if (message === "" || message.length > 200) {
      isValid = false;
      errorMessage += "Pesan wajib diisi dan maksimal 200 karakter.\n";
    }

    // Tampilkan hasil
    if (!isValid) {
      alert(errorMessage);
    } else {
      alert("Formulir berhasil dikirim!");
      // Bisa tambahkan proses kirim ke server di sini
      $('#contactForm')[0].reset(); // Reset form
    }
  });
});

$('#name, #email, #phone, #message').removeClass('error');
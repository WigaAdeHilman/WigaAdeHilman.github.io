const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');

hamburger.addEventListener('click', () => {
  navLinks.classList.toggle('active');
});

$(document).ready(function () {
  $('#contactForm').on('submit', function (e) {
    e.preventDefault(); // Cegah submit form

    // Ambil nilai dari form
    let name = $('#name').val().trim();
    let email = $('#email').val().trim();
    let phone = $('#phone').val().trim();
    let message = $('#message').val().trim();

    // Reset error classes
    $('#name, #email, #phone, #message').removeClass('error');

    // Validasi client-side
    let isValid = true;
    let errorMessage = "";

    // Validasi Nama
    if (name === "" || name.length > 50) {
      isValid = false;
      errorMessage += "Nama harus diisi dan maksimal 50 karakter.\n";
      $('#name').addClass('error');
    }

    // Validasi Email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email) || email.length > 50) {
      isValid = false;
      errorMessage += "Email tidak valid dan maksimal 50 karakter.\n";
      $('#email').addClass('error');
    }

    // Validasi Nomor HP
    const phoneRegex = /^[0-9]{10,15}$/;
    if (!phoneRegex.test(phone)) {
      isValid = false;
      errorMessage += "Nomor HP harus angka dan panjang 10-15 digit.\n";
      $('#phone').addClass('error');
    }

    // Validasi Pesan
    if (message === "" || message.length > 200) {
      isValid = false;
      errorMessage += "Pesan wajib diisi dan maksimal 200 karakter.\n";
      $('#message').addClass('error');
    }

    // Tampilkan hasil validasi client-side
    if (!isValid) {
      showMessage(errorMessage, 'error');
      return;
    }

    // Jika validasi berhasil, kirim data ke server
    sendContactForm(name, email, phone, message);
  });

  function sendContactForm(name, email, phone, message) {
    // Show loading state
    const submitBtn = $('#contactForm button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.text('Mengirim...').prop('disabled', true);

    // Prepare data
    const formData = {
      name: name,
      email: email,
      phone: phone,
      message: message
    };

    // Send AJAX request
    $.ajax({
      url: 'process_contact.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(formData),
      dataType: 'json',
      success: function(response) {
        if(response.success) {
          showMessage(response.message, 'success');
          $('#contactForm')[0].reset(); // Reset form
        } else {
          showMessage(response.message, 'error');
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', error);
        console.error('Response:', xhr.responseText);
        showMessage('Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.', 'error');
      },
      complete: function() {
        // Reset button state
        submitBtn.text(originalText).prop('disabled', false);
      }
    });
  }

  function showMessage(message, type) {
    const messageDiv = $('#responseMessage');
    messageDiv.removeClass('success error');
    messageDiv.addClass(type);
    messageDiv.text(message);
    messageDiv.show();

    // Auto hide after 5 seconds
    setTimeout(function() {
      messageDiv.fadeOut();
    }, 5000);
  }
});
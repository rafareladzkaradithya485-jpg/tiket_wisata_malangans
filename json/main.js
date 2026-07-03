document.addEventListener('DOMContentLoaded', () => {

    // --- DEKLARASI VARIABEL DI AWAL ---
    const formBayar = document.querySelector('form');
    const inputJumlah = document.getElementById('jumlah');
    const btnBayar = document.querySelector('button[name="bayar"]');
    const navbar = document.querySelector('.navbar');
    const logoutBtn = document.querySelector('a[href="logout.php"]');
    const cards = document.querySelectorAll('.glass-card'); // <-- Dipindahkan ke atas agar tidak error

    // --- 1. VALIDASI & ANIMASI SUBMIT FORM ---
    if (formBayar && inputJumlah) {
        inputJumlah.addEventListener('change', () => {
            if (inputJumlah.value < 1) {
                inputJumlah.value = 1;
                if (typeof updateTotal === 'function') {
                    updateTotal(1);
                }
            }
        });

        formBayar.addEventListener('submit', () => {
            if (btnBayar) {
                btnBayar.innerHTML = `<span class="flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 mr-3 text-white" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>`;
                btnBayar.classList.add('opacity-80', 'cursor-not-allowed');
                btnBayar.disabled = true;
            }
        });
    }

    // --- 2. ANIMASI ENTRY & HOVER UNTUK GLASS CARD ---
    if (cards.length > 0) {
        cards.forEach((card, index) => {
            // Efek Awal Muncul (Fade In & Slide Up)
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = `all 0.6s ease-out ${index * 0.1}s, border-color 0.3s ease, box-shadow 0.3s ease`;
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);

            // Efek Hover Interaktif
            card.addEventListener('mouseenter', () => {
                card.style.borderColor = 'rgba(59, 130, 246, 0.5)'; // Biru Tailwind
                card.style.boxShadow = '0 0 20px rgba(59, 130, 246, 0.2)';
            });
            card.addEventListener('mouseleave', () => {
                card.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                card.style.boxShadow = 'none';
            });
        });
    }

    // --- 3. EFEK NAVBAR SAAT DI-SCROLL ---
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(0, 42, 251, 0.9)';
                navbar.style.backdropFilter = 'blur(10px)';
                navbar.style.padding = '10px 0';
                navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.5)';
            } else {
                navbar.style.background = 'transparent';
                navbar.style.backdropFilter = 'none';
                navbar.style.padding = '20px 0'; // Disesuaikan dari 100px agar tidak terlalu meloncat jaraknya
                navbar.style.boxShadow = 'none';
            }
        });
    }

    // --- 4. KONFIRMASI LOGOUT ---
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    }
});
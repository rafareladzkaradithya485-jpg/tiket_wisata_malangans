
document.addEventListener('DOMContentLoaded', () => {

    const formBayar = document.querySelector('form');
    const inputJumlah = document.getElementById('jumlah');
    const btnBayar = document.querySelector('button[name="bayar"]');

    if (formBayar && inputJumlah) {
        inputJumlah.addEventListener('change', () => {
            if (inputJumlah.value < 1) {
                inputJumlah.value = 1;
                updateTotal(1);
            }
        });

        formBayar.addEventListener('submit', () => {
            btnBayar.innerHTML = `<span class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 mr-3 text-white" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            </span>`;
            btnBayar.classList.add('opacity-80', 'cursor-not-allowed');
            btnBayar.disabled = true;
        });
    }

    // 3. Animasi Hover tambahan untuk Glass Card agar lebih interaktif
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.borderColor = 'rgba(59, 130, 246, 0.5)'; // Warna biru Tailwind
            card.style.boxShadow = '0 0 20px rgba(59, 130, 246, 0.2)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            card.style.boxShadow = 'none';
        });
    });
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(0, 42, 251, 0.9)';
            navbar.style.backdropFilter = 'blur(10px)';
            navbar.style.padding = '10px 0';
            navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.5)';
        } else {
            navbar.style.background = 'transparent';
            navbar.style.padding = '100px 0';
            navbar.style.boxShadow = 'none';
        }
    });

    const logoutBtn = document.querySelector('a[href="logout.php"]');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    }

    const cards = document.querySelectorAll('.glass-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `all 0.6s ease-out ${index * 0.1}s`;
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });
});


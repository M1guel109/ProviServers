document.addEventListener("DOMContentLoaded", function () {
  const navbarCollapse = document.getElementById("navbarNav");
  const navbarToggler = document.querySelector(".navbar-toggler");
  const navLinks = document.querySelectorAll(".navbar-nav .nav-link");
  const navList = navbarCollapse.querySelector(".navbar-nav");

  if (!navbarCollapse || !navList || !navbarToggler) return;

  // ===== Funciones para abrir/cerrar =====
  function openMenu() {
    navbarCollapse.classList.add('show');
    navbarToggler.classList.add('collapsed');
    navbarToggler.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    navList.classList.add("show-items");
  }

  function closeMenu() {
    navbarCollapse.classList.remove('show');
    navbarToggler.classList.remove('collapsed');
    navbarToggler.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    navList.classList.remove("show-items");
  }

  // ===== Evento del botón hamburguesa =====
  navbarToggler.addEventListener('click', (e) => {
    e.stopPropagation();
    if (navbarCollapse.classList.contains('show')) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  // ===== Cerrar al hacer clic en la X (pseudo-elemento) =====
  navbarCollapse.addEventListener('click', (e) => {
    // Detectamos si el clic fue en la X (esquina superior derecha)
    const rect = navbarCollapse.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    // Área donde está la X (30px desde arriba, 30px desde derecha, radio 20px)
    const closeBtnX = rect.width - 60; // 30px from right
    const closeBtnY = 30;
    
    // Si el clic está en el área del botón X (círculo de 40px)
    if (navbarCollapse.classList.contains('show')) {
      if (x > rect.width - 70 && x < rect.width - 30 && y > 10 && y < 50) {
        closeMenu();
        e.preventDefault();
        e.stopPropagation();
      }
    }
  });

  // ===== Cerrar al hacer clic en cualquier link del menú =====
  navLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      if (!link.classList.contains('btn')) {
        closeMenu();
      }
    });
  });

  // ===== Cerrar con tecla ESC =====
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && navbarCollapse.classList.contains('show')) {
      closeMenu();
    }
  });

  // ===== Cerrar al hacer clic fuera del menú =====
  document.addEventListener('click', (e) => {
    if (navbarCollapse.classList.contains('show')) {
      if (!navbarCollapse.contains(e.target) && !navbarToggler.contains(e.target)) {
        closeMenu();
      }
    }
  });

  // ===== Eventos de Bootstrap collapse =====
  navbarCollapse.addEventListener("shown.bs.collapse", function () {
    navList.classList.add("show-items");
  });

  navbarCollapse.addEventListener("hidden.bs.collapse", function () {
    navList.classList.remove("show-items");
  });

  // ===== Actualizar link activo al hacer scroll =====
  const sections = document.querySelectorAll("section[id]");
  const navbarHeight = document.querySelector(".navbar").offsetHeight;

  function updateActiveLink() {
    let index = sections.length;
    while (--index && window.scrollY + navbarHeight < sections[index].offsetTop) {}
    
    navLinks.forEach((link) => link.classList.remove("active"));
    const activeLink = document.querySelector(`.navbar .nav-link[href="#${sections[index]?.id}"]`);
    if (activeLink) activeLink.classList.add("active");
  }

  updateActiveLink();
  window.addEventListener("scroll", updateActiveLink);

  // ===== Scroll suave =====
  navLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      if (link.getAttribute('href')?.startsWith('#')) {
        e.preventDefault();
        const target = document.querySelector(link.getAttribute("href"));
        if (!target) return;

        closeMenu();

        setTimeout(() => {
          window.scrollTo({
            top: target.offsetTop - navbarHeight,
            behavior: "smooth",
          });
        }, 300);
      }
    });
  });
});
document.addEventListener("DOMContentLoaded", function () {
  const navbarCollapse = document.getElementById("navbarNav");
  const navList = navbarCollapse.querySelector(".navbar-nav");

  if (!navbarCollapse || !navList) return;

  navbarCollapse.addEventListener("shown.bs.collapse", function () {
    navList.classList.add("show-items");
  });

  navbarCollapse.addEventListener("hidden.bs.collapse", function () {
    navList.classList.remove("show-items");
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const sections = document.querySelectorAll("section[id]");
  const navLinks = document.querySelectorAll(".navbar .nav-link");
  const navbarHeight = document.querySelector(".navbar").offsetHeight;

  function updateActiveLink() {
    let index = sections.length;

    while (--index && window.scrollY + navbarHeight < sections[index].offsetTop) {}
    
    navLinks.forEach((link) => link.classList.remove("active"));
    const activeLink = document.querySelector(`.navbar .nav-link[href="#${sections[index].id}"]`);
    if (activeLink) activeLink.classList.add("active");
  }

  updateActiveLink();
  window.addEventListener("scroll", updateActiveLink);

  // Scroll suave
  navLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const target = document.querySelector(link.getAttribute("href"));
      if (!target) return;

      window.scrollTo({
        top: target.offsetTop - navbarHeight,
        behavior: "smooth",
      });
    });
  });
});


document.addEventListener("DOMContentLoaded", () => {
  const images = document.querySelectorAll(".hero-carousel img");
  let current = 0;

  function changeImage() {
    images[current].classList.remove("active");
    current = (current + 1) % images.length;
    images[current].classList.add("active");
  }

  setInterval(changeImage, 5000); // cambia cada 5 segundos
});


document.addEventListener("DOMContentLoaded", () => {
  const toggleButtons = document.querySelectorAll(".toggle-btn");
  const prices = document.querySelectorAll(".plan-price");

  // Valores originales y anuales
  const planData = {
    mensual: [
      { price: "Gratis", period: "/Mensual" },
      { price: "25.000", period: "/Mensual" },
      { price: "49.000", period: "/Mensual" },
    ],
    anual: [
      { price: "0", period: "/Anual" },
      { price: "250.000", period: "/Anual" },
      { price: "490.000", period: "/Anual" },
    ],
  };

  toggleButtons.forEach((btn, index) => {
    btn.addEventListener("click", () => {
      // Cambiar estado activo
      toggleButtons.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");

      const tipo = btn.textContent.trim().toLowerCase(); // mensual o anual

      // Cambiar precios en pantalla
      prices.forEach((priceEl, i) => {
        const price = priceEl.querySelector(".price");
        const period = priceEl.querySelector(".period");
        price.textContent = planData[tipo][i].price;
        period.textContent = planData[tipo][i].period;
      });
    });
  });
});



document.addEventListener("DOMContentLoaded", function () {
  const cards = document.querySelectorAll(".testimonial-card");
  const prevBtn = document.querySelector(".arrow-btn.prev");
  const nextBtn = document.querySelector(".arrow-btn.next");
  let currentIndex = 0;

  function showTestimonial(index) {
    cards.forEach((card, i) => {
      card.classList.toggle("active", i === index);
    });
  }

  prevBtn.addEventListener("click", () => {
    currentIndex = (currentIndex - 1 + cards.length) % cards.length;
    showTestimonial(currentIndex);
  });

  nextBtn.addEventListener("click", () => {
    currentIndex = (currentIndex + 1) % cards.length;
    showTestimonial(currentIndex);
  });

  setInterval(() => {
    currentIndex = (currentIndex + 1) % cards.length;
    showTestimonial(currentIndex);
  }, 5000);
  
  showTestimonial(currentIndex);
});

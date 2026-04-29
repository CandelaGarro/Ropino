document.addEventListener("DOMContentLoaded", () => {
  const mensajes = document.querySelectorAll(".flash-message");

  mensajes.forEach((msg) => {
    const duracion = msg.classList.contains("flash-error") ? 6000 : 4000;

    setTimeout(() => {
      msg.style.opacity = "0";
      msg.style.transform = "translateY(-10px)";

      setTimeout(() => {
        msg.remove();
      }, 300);
    }, duracion);
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const menuToggle = document.getElementById("menu-toggle");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.getElementById("sidebar-overlay");

  if (menuToggle && sidebar && overlay) {
    function abrirMenu() {
      sidebar.classList.add("abierto");
      overlay.classList.add("activo");
      menuToggle.classList.add("activo");
      menuToggle.setAttribute("aria-expanded", "true");
      overlay.setAttribute("aria-hidden", "false");
      sidebar.setAttribute("aria-hidden", "false");
      document.body.classList.add("menu-open");
    }

    function cerrarMenu() {
      sidebar.classList.remove("abierto");
      overlay.classList.remove("activo");
      menuToggle.classList.remove("activo");
      menuToggle.setAttribute("aria-expanded", "false");
      overlay.setAttribute("aria-hidden", "true");
      sidebar.setAttribute("aria-hidden", window.innerWidth <= 1000 ? "true" : "false");
      document.body.classList.remove("menu-open");
    }

    menuToggle.addEventListener("click", function () {
      if (sidebar.classList.contains("abierto")) {
        cerrarMenu();
      } else {
        abrirMenu();
      }
    });

    overlay.addEventListener("click", cerrarMenu);

    sidebar.querySelectorAll(".menu a").forEach((link) => {
      link.addEventListener("click", function () {
        if (window.innerWidth <= 1000) {
          cerrarMenu();
        }
      });
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && sidebar.classList.contains("abierto")) {
        cerrarMenu();
      }
    });

    window.addEventListener("resize", function () {
      if (window.innerWidth > 1000) {
        cerrarMenu();
      }
    });

    sidebar.setAttribute("aria-hidden", window.innerWidth <= 1000 ? "true" : "false");
  }

  const slides = document.querySelectorAll(".slider-comida-slide");
  const dots = document.querySelectorAll(".slider-dot");
  const prevBtn = document.getElementById("slider-comida-prev");
  const nextBtn = document.getElementById("slider-comida-next");

  if (slides.length) {
    let currentIndex = 0;
    let autoplay;

    function showSlide(index) {
      slides.forEach((slide, i) => {
        slide.classList.toggle("active", i === index);
      });

      dots.forEach((dot, i) => {
        dot.classList.toggle("active", i === index);
      });

      currentIndex = index;
    }

    function nextSlide() {
      const next = (currentIndex + 1) % slides.length;
      showSlide(next);
    }

    function prevSlide() {
      const prev = (currentIndex - 1 + slides.length) % slides.length;
      showSlide(prev);
    }

    function startAutoplay() {
      autoplay = setInterval(nextSlide, 4000);
    }

    function resetAutoplay() {
      clearInterval(autoplay);
      startAutoplay();
    }

    if (prevBtn) {
      prevBtn.addEventListener("click", function () {
        prevSlide();
        resetAutoplay();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener("click", function () {
        nextSlide();
        resetAutoplay();
      });
    }

    dots.forEach((dot) => {
      dot.addEventListener("click", function () {
        const index = parseInt(this.dataset.index, 10);
        showSlide(index);
        resetAutoplay();
      });
    });

    showSlide(0);
    startAutoplay();
  }

  const homeHeroSlides = document.querySelectorAll(".home-hero__slide");
  const homeHeroDots = document.querySelectorAll(".home-hero-dot");

  if (homeHeroSlides.length) {
    let currentHeroIndex = 0;
    let heroAutoplay;

    function showHeroSlide(index) {
      homeHeroSlides.forEach((slide, i) => {
        slide.classList.toggle("active", i === index);
      });

      homeHeroDots.forEach((dot, i) => {
        dot.classList.toggle("active", i === index);
      });

      currentHeroIndex = index;
    }

    function nextHeroSlide() {
      const next = (currentHeroIndex + 1) % homeHeroSlides.length;
      showHeroSlide(next);
    }

    function startHeroAutoplay() {
      heroAutoplay = setInterval(nextHeroSlide, 5000);
    }

    function resetHeroAutoplay() {
      clearInterval(heroAutoplay);
      startHeroAutoplay();
    }

    homeHeroDots.forEach((dot) => {
      dot.addEventListener("click", function () {
        const index = parseInt(this.dataset.index, 10);
        showHeroSlide(index);
        resetHeroAutoplay();
      });
    });

    showHeroSlide(0);
    startHeroAutoplay();
  }

  const tableWraps = document.querySelectorAll(".tabla-wrap");

  tableWraps.forEach((wrap) => {
    if (wrap.nextElementSibling?.classList.contains("tabla-scrollbar")) {
      return;
    }

    const scrollbar = document.createElement("div");
    scrollbar.className = "tabla-scrollbar";

    const thumb = document.createElement("div");
    thumb.className = "tabla-scrollbar__thumb";

    scrollbar.appendChild(thumb);
    wrap.insertAdjacentElement("afterend", scrollbar);

    let dragging = false;
    let startX = 0;
    let startLeft = 0;

    function getMaxScroll() {
      return Math.max(0, wrap.scrollWidth - wrap.clientWidth);
    }

    function updateScrollbar() {
      const maxScroll = getMaxScroll();
      const visible = maxScroll > 1;

      scrollbar.classList.toggle("activa", visible);

      if (!visible) {
        thumb.style.width = "0";
        thumb.style.transform = "translateX(0)";
        return;
      }

      const trackWidth = scrollbar.clientWidth;
      const thumbWidth = Math.max(52, Math.round((wrap.clientWidth / wrap.scrollWidth) * trackWidth));
      const maxThumbLeft = Math.max(0, trackWidth - thumbWidth);
      const thumbLeft = maxScroll ? (wrap.scrollLeft / maxScroll) * maxThumbLeft : 0;

      thumb.style.width = `${thumbWidth}px`;
      thumb.style.transform = `translateX(${thumbLeft}px)`;
    }

    function syncWrapFromThumb(clientX) {
      const rect = scrollbar.getBoundingClientRect();
      const thumbWidth = thumb.offsetWidth;
      const maxThumbLeft = Math.max(0, rect.width - thumbWidth);
      const rawLeft = startLeft + (clientX - startX);
      const thumbLeft = Math.min(Math.max(0, rawLeft), maxThumbLeft);
      const maxScroll = getMaxScroll();

      wrap.scrollLeft = maxThumbLeft ? (thumbLeft / maxThumbLeft) * maxScroll : 0;
    }

    thumb.addEventListener("pointerdown", (event) => {
      dragging = true;
      startX = event.clientX;
      startLeft = thumb.getBoundingClientRect().left - scrollbar.getBoundingClientRect().left;
      thumb.classList.add("arrastrando");
      thumb.setPointerCapture(event.pointerId);
      event.preventDefault();
    });

    thumb.addEventListener("pointermove", (event) => {
      if (!dragging) {
        return;
      }

      syncWrapFromThumb(event.clientX);
    });

    function stopDragging(event) {
      if (!dragging) {
        return;
      }

      dragging = false;
      thumb.classList.remove("arrastrando");

      if (event?.pointerId !== undefined && thumb.hasPointerCapture(event.pointerId)) {
        thumb.releasePointerCapture(event.pointerId);
      }
    }

    thumb.addEventListener("pointerup", stopDragging);
    thumb.addEventListener("pointercancel", stopDragging);

    scrollbar.addEventListener("click", (event) => {
      if (event.target === thumb) {
        return;
      }

      const rect = scrollbar.getBoundingClientRect();
      const clickRatio = rect.width ? (event.clientX - rect.left) / rect.width : 0;
      wrap.scrollLeft = clickRatio * getMaxScroll();
    });

    wrap.addEventListener("scroll", updateScrollbar, { passive: true });
    window.addEventListener("resize", updateScrollbar);

    if (typeof ResizeObserver !== "undefined") {
      const observer = new ResizeObserver(updateScrollbar);
      observer.observe(wrap);

      const table = wrap.querySelector("table");
      if (table) {
        observer.observe(table);
      }
    }

    updateScrollbar();
  });
});

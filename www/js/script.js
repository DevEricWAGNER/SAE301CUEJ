// Menu
function animMenu() {
  const btnHamburger = document.querySelector(".hamburger");
  const menu = document.querySelector(".nav-links");

  btnHamburger.addEventListener("click", () => {
    menu.classList.toggle("active");
    btnHamburger.classList.toggle("active");
  });
}

animMenu();

function subMenu() {
  const menuItems = document.querySelectorAll(".nav-links li");

  let activeMenuItem = null;

  menuItems.forEach(function (menuItem) {
    menuItem.addEventListener("click", function () {
      // Hide any visible submenus
      let visibleSubmenus = document.querySelectorAll(
        '.submenu:not([style*="display: none"])'
      );
      visibleSubmenus.forEach(function (submenu) {
        submenu.style.display = "none";
      });

      // Show the submenu for the clicked menu item (if it exists)
      let submenu = this.querySelector(".submenu");
      if (submenu) {
        // If the clicked menu item is the currently active one, hide its submenu
        if (this === activeMenuItem) {
          submenu.style.display = "none";
        } else {
          submenu.style.display = "flex";
        }
      }

      // Deselect the previously active menu item (if it exists)
      if (activeMenuItem) {
        activeMenuItem.classList.remove("active");
      }

      // Select the current menu item
      this.classList.add("active");
      activeMenuItem = this;
    });
  });
}

subMenu();

// Section FAQ
const toggles = document.querySelectorAll(".faq-toggle");

toggles.forEach((toggle) => {
  toggle.addEventListener("click", () => {
    toggle.parentNode.classList.toggle("active");
  });
});

// SLIDER
const swiper = new Swiper(".swiper", {
  loop: true,
  spaceBetween: 20,

  // Navigation arrows
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },

  // Responsive
  breakpoints: {
    320: {
      slidesPerView: 1,
    },
    600: {
      slidesPerView: 2,
    },
    900: {
      slidesPerView: 3,
    },
  },

  on: {
    init: function () {
      checkArrow();
    },
    resize: function () {
      checkArrow();
    },
  },
});

// Fonction qui permet de supprimer les flèche directionnelle au format téléphone
function checkArrow() {
  let swiperPrev = document.querySelector(".swiper-button-prev");
  let swiperNext = document.querySelector(".swiper-button-next");
  if (window.innerWidth > 900) {
    swiperPrev.style.display = "block";
    swiperNext.style.display = "block";
  } else {
    swiperPrev.style.display = "none";
    swiperNext.style.display = "none";
  }
}

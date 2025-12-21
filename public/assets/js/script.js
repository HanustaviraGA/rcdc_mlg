document.addEventListener("DOMContentLoaded", function () {
    // const servicesButton = document.getElementById("services-dropdown-button");
    // const servicesMenu = document.getElementById("services-dropdown-menu");

    // Toggle dropdown visibility on click for mobile/tablet view
    // servicesButton.addEventListener("click", function () {
    //     if (window.innerWidth < 1024) {
    //         servicesMenu.classList.toggle("hidden");
    //     }
    // });

    // Menu services ketika mode mobile, dan ketika di klik memunculkan submenu nya
    const dropdowns = document.getElementById("menu-services");
    const btn = dropdowns.querySelector(".dropdown-btn");
    const menu = dropdowns.querySelector(".dropdown-menu-content");
    const icon = btn.querySelector("svg");

    btn.addEventListener("click", () => {
        menu.classList.toggle("hidden");
        menu.classList.toggle("lg:opacity-0");
        menu.classList.toggle("lg:invisible");
        menu.classList.toggle("lg:scale-95");

        icon.classList.toggle("rotate-180");
    });

    // Swiper untuk brand
    new Swiper(".autoplay-swiper", {
        autoplay: {
            delay: 2000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        slidesPerView: 3,
        spaceBetween: 4,
        centeredSlides: false,
        breakpoints: {
            640: {
                slidesPerView: 4,
                spaceBetween: 4,
            },
            1024: {
                slidesPerView: 6,
                spaceBetween: 2,
            },
            1280: {
                slidesPerView: 7,
                spaceBetween: 2,
            },
        },
    });

    // Swiper untuk Our Services
    new Swiper(".swiper-cards", {
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        spaceBetween: 24,
        centeredSlides: false,
        navigation: {
            nextEl: ".swiper-button-next-custom",
            prevEl: ".swiper-button-prev-custom",
        },
        breakpoints: {
            0: {
                slidesPerView: 1,
            },
            640: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
                autoplay: {
                    enabled: false,
                },
            },
        },
    });

    // Swiper untuk card pada halamana career
    new Swiper(".swiper-scrollbar", {
        loop: true, // Mengaktifkan loop
        slidesPerView: 3, // Menampilkan 3 card sekaligus
        spaceBetween: 20, // Jarak antar card
        navigation: {
            nextEl: ".swiper-button-next", // Tombol untuk navigasi ke slide berikutnya
            prevEl: ".swiper-button-prev", // Tombol untuk navigasi ke slide sebelumnya
        },
        breakpoints: {
            1024: {
                slidesPerView: 3,
            },
            768: {
                slidesPerView: 2,
            },
            640: {
                slidesPerView: 1,
            },
            390: {
                slidesPerView: 1,
            },
            360: {
                slidesPerView: 1,
            },
        },
    });

    // Swiper untuk halaman fyp agency bagian Project Selesai
    new Swiper(".mySwiper", {
        loop: true,
        spaceBetween: 30,
        slidesPerView: 1,
        grabCursor: true,
        simulateTouch: true,
        touchRatio: 1,
        touchAngle: 45,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".mySwiper-button-next",
            prevEl: ".mySwiper-button-prev",
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    });

    // Dropdown untuk menu lainnya di header halaman news atau blog
    const dropdownContainers = document.querySelectorAll(".dropdown-container");
    if (dropdownContainers.length > 0) {
        dropdownContainers.forEach((container) => {
            const dropdownToggle = container.querySelector(".dropdown-toggle");
            const dropdownMenu = container.querySelector(".dropdown-menu");

            if (dropdownToggle && dropdownMenu) {
                dropdownToggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const isVisible =
                        !dropdownMenu.classList.contains("opacity-0");

                    if (isVisible) {
                        dropdownMenu.classList.add("opacity-0", "invisible");
                        dropdownToggle
                            .querySelector("svg")
                            .classList.remove("rotate-180");
                    } else {
                        dropdownContainers.forEach((other) => {
                            if (other !== container) {
                                other
                                    .querySelector(".dropdown-menu")
                                    ?.classList.add("opacity-0", "invisible");
                                other
                                    .querySelector(".dropdown-toggle svg")
                                    ?.classList.remove("rotate-180");
                            }
                        });
                        dropdownMenu.classList.remove("opacity-0", "invisible");
                        dropdownToggle
                            .querySelector("svg")
                            .classList.add("rotate-180");
                    }
                });
            }
        });

        document.addEventListener("click", function (e) {
            dropdownContainers.forEach((container) => {
                if (!container.contains(e.target)) {
                    container
                        .querySelector(".dropdown-menu")
                        ?.classList.add("opacity-0", "invisible");
                    container
                        .querySelector(".dropdown-toggle svg")
                        ?.classList.remove("rotate-180");
                }
            });
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                dropdownContainers.forEach((container) => {
                    container
                        .querySelector(".dropdown-menu")
                        ?.classList.add("opacity-0", "invisible");
                    container
                        .querySelector(".dropdown-toggle svg")
                        ?.classList.remove("rotate-180");
                });
            }
        });
    }
});

// Mobile Menu Toggle
const overlayMobileMenu = document.getElementById("overlay-mobile-menu");
const sidebarMenu = document.getElementById("sidebar-menu");
const mobileMenuButton = document.getElementById("mobile-menu-button");
const closeMenuButton = document.getElementById("close-menu-button");

mobileMenuButton.addEventListener("click", function () {
    overlayMobileMenu.classList.remove("hidden");

    setTimeout(() => {
        sidebarMenu.classList.remove("-translate-x-full");
        sidebarMenu.classList.add("translate-x-0");
    }, 10);
});

const overlayMobileMenuClose = (event) => {
    event.stopPropagation();

    sidebarMenu.classList.remove("translate-x-0");
    sidebarMenu.classList.add("-translate-x-full");

    setTimeout(() => {
        overlayMobileMenu.classList.add("hidden");
    }, 500);
};

closeMenuButton.addEventListener("click", (event) => {
    overlayMobileMenuClose(event);
});

overlayMobileMenu.addEventListener("click", (event) => {
    overlayMobileMenuClose(event);
});

// Prevent bubbling when clicking inside the sidebar
sidebarMenu.addEventListener("click", (event) => {
    event.stopPropagation();
});

// Dropdown Menu Toggle
// const dropdownMenu = document.querySelectorAll(".dropdown-menu");
// dropdownMenu.forEach((menu) => {
//     menu.addEventListener("click", function (e) {
//         e.stopPropagation();
//         const dropdownContent = this.querySelector("div");
//         dropdownContent.classList.toggle("hidden");
//     });
// });

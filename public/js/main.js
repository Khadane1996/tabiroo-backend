document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".menu-item");
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            const target = this.dataset.tab;

            // Retirer la classe active de tous les onglets et contenus
            tabs.forEach(t => t.classList.remove("active"));
            contents.forEach(c => c.classList.remove("active"));

            // Ajouter la classe active à l'élément sélectionné
            this.classList.add("active");
            document.querySelector(`.tab-content[data-content="${target}"]`).classList.add("active");
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector(".temoignages");
    const prevBtn = document.querySelector(".prev");
    const nextBtn = document.querySelector(".next");
    const cardWidth = document.querySelector(".temoin").offsetWidth + 10; // Largeur d'un témoignage + marge

    function updateButtons() {
        prevBtn.classList.toggle("disabled", container.scrollLeft <= 0);
        nextBtn.classList.toggle("disabled", container.scrollLeft + container.clientWidth >= container.scrollWidth);
    }

    nextBtn.addEventListener("click", function () {
        // Vérifier si on est sur mobile (largeur du témoignage = 100%)
        const isMobile = window.innerWidth <= 767;
        const scrollAmount = isMobile ? cardWidth : cardWidth * 2;
        container.scrollBy({ left: scrollAmount, behavior: "smooth" });
    });

    prevBtn.addEventListener("click", function () {
        // Vérifier si on est sur mobile (largeur du témoignage = 100%)
        const isMobile = window.innerWidth <= 767;
        const scrollAmount = isMobile ? cardWidth : cardWidth * 2;
        container.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    });

    container.addEventListener("scroll", updateButtons);
    updateButtons();
    
});


// PAGINATION
document.addEventListener("DOMContentLoaded", function () {
    const articles = document.querySelectorAll(".articles");
    const pageButtons = document.querySelectorAll(".page-number");
    const prevButton = document.querySelector(".prev-page");
    const nextButton = document.querySelector(".next-page");

    const articlesPerPage = 6; 
    let currentPage = 1;
    const totalPages = Math.ceil(articles.length / articlesPerPage);

    function showPage(page) {
        currentPage = page;

        // Cacher tous les articles
        articles.forEach((article) => {
            article.style.display = "none";
        });

        // Afficher seulement les articles de la page actuelle
        const startIndex = (page - 1) * articlesPerPage;
        const endIndex = startIndex + articlesPerPage;

        for (let i = startIndex; i < endIndex && i < articles.length; i++) {
            articles[i].style.display = "block";
        }

        // Mettre à jour les boutons de pagination
        pageButtons.forEach((btn, index) => {
            btn.classList.toggle("active", index + 1 === page);
            btn.style.backgroundColor = index + 1 === page ? "#cc6b2f" : "#f0f0f0";
        });

        // Désactiver les boutons si nécessaire
        prevButton.disabled = (currentPage === 1);
        nextButton.disabled = (currentPage === totalPages);
    }

    // Gestion des clics sur les boutons de pagination
    pageButtons.forEach((btn, index) => {
        btn.addEventListener("click", () => showPage(index + 1));
    });

    prevButton.addEventListener("click", () => {
        if (currentPage > 1) showPage(currentPage - 1);
    });

    nextButton.addEventListener("click", () => {
        if (currentPage < totalPages) showPage(currentPage + 1);
    });

    // Afficher la première page au chargement
    showPage(1);
});



//overlay

// Accordéon FAQ

document.addEventListener('DOMContentLoaded', function() {
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const item = btn.parentElement;
            item.classList.toggle('active');
        });
    });
});

// Menu Hamburger

document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger-menu');
    const nav = document.querySelector('header nav');
    
    if (hamburger && nav) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            nav.classList.toggle('active');
        });
        
        // Fermer le menu quand on clique sur un lien
        const navLinks = nav.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('active');
                nav.classList.remove('active');
            });
        });
        
        // Fermer le menu quand on clique en dehors
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !nav.contains(e.target)) {
                hamburger.classList.remove('active');
                nav.classList.remove('active');
            }
        });
    }
});

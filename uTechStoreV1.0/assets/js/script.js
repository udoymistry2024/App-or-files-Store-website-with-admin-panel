document.addEventListener('DOMContentLoaded', () => {
    const categoryTabsContainer = document.getElementById('categoryTabs');
    const appGrid = document.getElementById('appGrid');
    const searchInput = document.getElementById('searchInput');
    const modal = document.getElementById('appModal');
    const modalBody = document.getElementById('modalBody');
    const closeButton = document.querySelector('.close-button');

    let allApps = [];
    let categories = [];

    // Fetch categories and then apps
    async function initialize() {
        await fetchCategories();
        await fetchApps();
        if (allApps.length > 0) {
            displayApps(allApps);
        } else {
            appGrid.innerHTML = '<p style="text-align: center; width: 100%; grid-column: 1 / -1;">No apps found.</p>';
        }
    }

    // Fetch all categories from the server
    async function fetchCategories() {
        try {
            const response = await fetch('api/get_apps.php?action=getCategories');
            categories = await response.json();
            renderCategoryTabs();
        } catch (error) {
            console.error('Error fetching categories:', error);
        }
    }

    // Render category tabs in the navigation
    function renderCategoryTabs() {
        categoryTabsContainer.innerHTML = '<div class="tab active" data-id="all">All</div>';
        categories.forEach(category => {
            const tab = document.createElement('div');
            tab.className = 'tab';
            tab.dataset.id = category.id;
            tab.textContent = category.name;
            categoryTabsContainer.appendChild(tab);
        });
    }

    // Fetch all apps from the server
    async function fetchApps() {
        try {
            const response = await fetch('api/get_apps.php?action=getApps');
            allApps = await response.json();
        } catch (error) {
            console.error('Error fetching apps:', error);
        }
    }

    // Display apps in the grid
    function displayApps(apps) {
        appGrid.innerHTML = '';
        if (apps.length === 0) {
            appGrid.innerHTML = '<p style="text-align: center; width: 100%; grid-column: 1 / -1;">No apps found for this category.</p>';
            return;
        }
        apps.forEach(app => {
            const card = document.createElement('div');
            card.className = 'app-card';
            card.dataset.appId = app.id;
            const imagePath = app.image1 ? app.image1 : `https://via.placeholder.com/220x150?text=${encodeURIComponent(app.title)}`;
            card.innerHTML = `
                <img src="${imagePath}" alt="${app.title}">
                <div class="app-info">
                    <h3>${app.title}</h3>
                </div>
            `;
            card.addEventListener('click', () => openModal(app.id));
            appGrid.appendChild(card);
        });
    }

    // Filter apps based on category ID
    function filterByCategory(categoryId) {
        if (categoryId === 'all') {
            displayApps(allApps);
        } else {
            const filteredApps = allApps.filter(app => app.category_id == categoryId);
            displayApps(filteredApps);
        }
    }
    
    // Filter apps based on search query
    function filterBySearch(query) {
        const lowerCaseQuery = query.toLowerCase();
        const activeTabId = document.querySelector('.tab.active').dataset.id;
        
        let appsToFilter = allApps;
        if (activeTabId !== 'all') {
            appsToFilter = allApps.filter(app => app.category_id == activeTabId);
        }

        const filteredApps = appsToFilter.filter(app => 
            app.title.toLowerCase().includes(lowerCaseQuery) ||
            app.description.toLowerCase().includes(lowerCaseQuery)
        );
        displayApps(filteredApps);
    }

    // Handle clicking on category tabs
    categoryTabsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('tab')) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            e.target.classList.add('active');
            filterBySearch(searchInput.value); // Re-filter with search after changing tab
        }
    });
    
    // Handle search input
    searchInput.addEventListener('input', () => {
        filterBySearch(searchInput.value);
    });

    // --- openModal ফাংশনে পরিবর্তন আনা হয়েছে ---
    function openModal(appId) {
        const app = allApps.find(a => a.id == appId);
        if (!app) return;

        // ছবির জন্য HTML তৈরি করা
        let imagesHTML = '';
        if (app.image1) imagesHTML += `<img src="${app.image1}" alt="Preview 1">`;
        if (app.image2) imagesHTML += `<img src="${app.image2}" alt="Preview 2">`;
        if (app.image3) imagesHTML += `<img src="${app.image3}" alt="Preview 3">`;
        
        // যদি ছবি থাকে, তাহলে গ্যালারির div তৈরি করা
        const galleryContainerHTML = imagesHTML ? `<div class="image-gallery">${imagesHTML}</div>` : '';

        // ভিডিওর জন্য HTML তৈরি করা
        let videoHTML = '';
        if (app.youtube_link) {
            const videoIdMatch = app.youtube_link.match(/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
            if (videoIdMatch && videoIdMatch[1]) {
                videoHTML = `<div class="video-container"><iframe src="https://www.youtube.com/embed/${videoIdMatch[1]}" frameborder="0" allowfullscreen></iframe></div>`;
            }
        }
        
        // মডালের সম্পূর্ণ HTML কোড তৈরি করা, যেখানে ভিডিও এবং ছবি উভয়ই দেখানো হবে
        modalBody.innerHTML = `
            <h2>${app.title}</h2>
            ${videoHTML}
            ${galleryContainerHTML}
            <div class="description-wrapper">
                <p class="description-text">${app.description.replace(/\n/g, '<br>')}</p>
                <button class="toggle-description-btn">Show More</button>
            </div>
            <a href="${app.file_path}" class="download-button" download>Download</a>
        `;
        modal.style.display = 'block';

        // "Show More" বাটনের জন্য লজিক
        const descText = modalBody.querySelector('.description-text');
        const toggleBtn = modalBody.querySelector('.toggle-description-btn');

        if (descText.scrollHeight > descText.clientHeight) {
            toggleBtn.style.display = 'block';
        }

        toggleBtn.addEventListener('click', () => {
            descText.classList.toggle('expanded');
            toggleBtn.textContent = descText.classList.contains('expanded') ? 'Show Less' : 'Show More';
        });
    }

    // Close modal
    closeButton.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target == modal) {
            modal.style.display = 'none';
        }
    });

    initialize();
});
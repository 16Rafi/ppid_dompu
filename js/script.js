// Sticky Header
window.addEventListener('scroll', function() {
    const header = document.getElementById('header');
    if (!header) return;
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// AJAX Form Submission for Permohonan & Keberatan
document.addEventListener('DOMContentLoaded', function() {
    function bindAjaxForm(formId, errorMessage) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('.btn-submit');
            const originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.textContent = 'Mengirim...';
                submitBtn.disabled = true;
            }

            const formData = new FormData(this);
            const requestUrl = this.action || window.location.href;

            fetch(requestUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                },
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const alerts = doc.querySelectorAll('.alert');

                document.querySelectorAll('.alert').forEach(alert => alert.remove());

                if (alerts.length > 0) {
                    alerts.forEach(alert => {
                        const formContainer = document.querySelector('.permohonan-container');
                        if (formContainer) {
                            formContainer.insertBefore(alert, formContainer.firstChild);
                        }
                    });

                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    const successAlert = doc.querySelector('.alert-success');
                    if (successAlert) {
                        setTimeout(() => {
                            this.reset();
                        }, 2000);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const formContainer = document.querySelector('.permohonan-container');
                if (formContainer) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-error';
                    errorDiv.innerHTML = `<h3>X Terjadi Kesalahan</h3><p>${errorMessage}</p>`;
                    formContainer.insertBefore(errorDiv, formContainer.firstChild);
                }
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            });
        });
    }

    bindAjaxForm('permohonanForm', 'Terjadi kesalahan saat mengirim permohonan. Silakan coba lagi.');
    bindAjaxForm('keberatanForm', 'Terjadi kesalahan saat mengirim keberatan. Silakan coba lagi.');
});

// Public page table search/sort (template pages)
document.addEventListener('DOMContentLoaded', function() {
    const tableWrappers = document.querySelectorAll('.page-table-wrapper');
    if (!tableWrappers.length) return;

    tableWrappers.forEach(wrapper => {
        const table = wrapper.querySelector('table.page-table');
        if (!table) return;

        const enableSearch = wrapper.dataset.search === '1';
        const enableSort = wrapper.dataset.sort === '1';

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody ? tbody.querySelectorAll('tr') : []);

        if (enableSearch) {
            const searchInput = wrapper.querySelector('.page-table-search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const q = this.value.toLowerCase();
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(q) ? '' : 'none';
                    });
                });
            }
        }

        if (enableSort) {
            const headers = table.querySelectorAll('thead th');
            headers.forEach((th, index) => {
                th.classList.add('sortable');
                let asc = true;
                th.addEventListener('click', function() {
                    const sorted = rows.slice().sort((a, b) => {
                        const aText = (a.children[index]?.textContent || '').trim();
                        const bText = (b.children[index]?.textContent || '').trim();
                        if (!aText && !bText) return 0;
                        if (!isNaN(aText) && !isNaN(bText)) {
                            return asc ? (Number(aText) - Number(bText)) : (Number(bText) - Number(aText));
                        }
                        return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
                    });
                    sorted.forEach(r => tbody.appendChild(r));
                    asc = !asc;
                });
            });
        }
    });
});

// DIP table search & sort
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('dipTable');
    const searchInput = document.getElementById('dipSearch');
    if (!table || !searchInput) return;

    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    let rows = Array.from(tbody.querySelectorAll('tr'));

    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });

    const headers = table.querySelectorAll('thead th');
    headers.forEach((th, index) => {
        th.classList.add('sortable');
        let asc = true;
        th.addEventListener('click', function() {
            rows = Array.from(tbody.querySelectorAll('tr'));
            const sorted = rows.slice().sort((a, b) => {
                const aText = (a.children[index]?.textContent || '').trim();
                const bText = (b.children[index]?.textContent || '').trim();
                if (!aText && !bText) return 0;
                if (th.dataset.sort === 'number') {
                    return asc ? (Number(aText) - Number(bText)) : (Number(bText) - Number(aText));
                }
                return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });
            sorted.forEach(r => tbody.appendChild(r));
            asc = !asc;
        });
    });
});

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    // Toggle mobile menu
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            // Animate hamburger menu
            const spans = mobileMenuToggle.querySelectorAll('span');
            spans.forEach((span, index) => {
                if (navMenu.classList.contains('active')) {
                    if (index === 0) span.style.transform = 'rotate(45deg) translate(5px, 5px)';
                    if (index === 1) span.style.opacity = '0';
                    if (index === 2) span.style.transform = 'rotate(-45deg) translate(7px, -6px)';
                } else {
                    span.style.transform = '';
                    span.style.opacity = '';
                }
            });
        });
    }

    // Close mobile menu when clicking on a link
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (!navMenu || !mobileMenuToggle) return;
            navMenu.classList.remove('active');
            const spans = mobileMenuToggle.querySelectorAll('span');
            spans.forEach(span => {
                span.style.transform = '';
                span.style.opacity = '';
            });
        });
    });

    // Handle dropdown menus on mobile
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const navItem = this.parentElement;
                const dropdownMenu = navItem.querySelector('.dropdown-menu');
                
                // Close other dropdowns
                document.querySelectorAll('.nav-item').forEach(item => {
                    if (item !== navItem) {
                        item.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                navItem.classList.toggle('active');
            }
        });
    });

    // Set active nav link based on current page
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const currentPath = window.location.pathname;
    
    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        
        // Remove existing active class first
        link.classList.remove('active');
        
        // For admin pages, use exact path matching
        if (currentPath.includes('/admin/')) {
            // Extract project name from current path
            const pathParts = currentPath.split('/');
            const projectName = pathParts[1]; // Gets 'ppid_dompu'
            
            // Exact match for current path
            let convertedPath;
            if (linkHref === 'index.php') {
                // For current directory index.php
                convertedPath = currentPath.substring(0, currentPath.lastIndexOf('/')) + '/index.php';
            } else {
                // For relative paths with ../
                convertedPath = '/' + projectName + linkHref.replace('../', '/admin/');
            }
            
            if (currentPath === convertedPath) {
                link.classList.add('active');
            }
            // Special case for dashboard
            else if (currentPath.includes('/admin/dashboard.php') && linkHref.includes('dashboard.php')) {
                link.classList.add('active');
            }
            // Special case for laporan
            else if (currentPath.includes('/admin/laporan.php') && linkHref.includes('laporan.php')) {
                link.classList.add('active');
            }
        }
        // For non-admin pages, use original logic
        else if (linkHref === currentPage) {
            link.classList.add('active');
        }
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add animation to elements when they come into view
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    const animateElements = document.querySelectorAll('.website-card, .news-card');
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Initialize carousel for news section (if needed)
    initNewsCarousel();
    
    // Initialize external websites carousel (if needed)
    initWebsitesCarousel();
});

// News Carousel Function
function initNewsCarousel() {
    const newsGrid = document.querySelector('.news-grid');
    const newsCards = document.querySelectorAll('.news-card');
    
    if (!newsGrid || newsCards.length === 0) return;
    
    if (newsCards.length > 3 && window.innerWidth <= 768) {
        let currentIndex = 0;
        
        // Create carousel controls
        const carouselContainer = document.createElement('div');
        carouselContainer.className = 'news-carousel-container';
        
        const prevBtn = document.createElement('button');
        prevBtn.className = 'carousel-btn prev';
        prevBtn.innerHTML = '&#10094;';
        
        const nextBtn = document.createElement('button');
        nextBtn.className = 'carousel-btn next';
        nextBtn.innerHTML = '&#10095;';
        
        carouselContainer.appendChild(prevBtn);
        carouselContainer.appendChild(newsGrid);
        carouselContainer.appendChild(nextBtn);
        
        newsGrid.parentElement.replaceChild(carouselContainer, newsGrid);
        
        // Carousel functionality
        function showSlide(index) {
            const cardWidth = newsCards[0].offsetWidth + 32; // Including gap
            newsGrid.style.transform = `translateX(-${index * cardWidth}px)`;
        }
        
        prevBtn.addEventListener('click', () => {
            currentIndex = Math.max(0, currentIndex - 1);
            showSlide(currentIndex);
        });
        
        nextBtn.addEventListener('click', () => {
            const maxIndex = Math.max(0, newsCards.length - 1);
            currentIndex = Math.min(maxIndex, currentIndex + 1);
            showSlide(currentIndex);
        });
    }
}

// External Websites Carousel Function
function initWebsitesCarousel() {
    const track = document.querySelector('.websites-track');
    const items = document.querySelectorAll('.carousel-item');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const carousel = document.querySelector('.websites-carousel');
    
    if (!track || items.length === 0) return;
    
    let currentIndex = 0;
    const itemsPerView = window.innerWidth <= 768 ? 1 : 3;
    const totalItems = items.length;
    const maxIndex = Math.max(0, totalItems - itemsPerView);
    
    function updateCarousel() {
        const itemWidth = window.innerWidth <= 768 ? 
            items[0].offsetWidth + 16 : // Mobile gap
            350 + 24; // Desktop: Fixed card width + gap
        const offset = currentIndex * itemWidth;
        track.style.transform = `translateX(-${offset}px)`;
        
        // Update button states
        if (prevBtn) prevBtn.disabled = false;
        if (nextBtn) nextBtn.disabled = false;
    }
    
    function nextSlide() {
        if (currentIndex >= maxIndex) {
            currentIndex = 0; // Loop back to start
        } else {
            currentIndex += itemsPerView;
            if (currentIndex > maxIndex) currentIndex = maxIndex;
        }
        updateCarousel();
    }
    
    function prevSlide() {
        if (currentIndex <= 0) {
            currentIndex = maxIndex; // Loop to end
        } else {
            currentIndex -= itemsPerView;
            if (currentIndex < 0) currentIndex = 0;
        }
        updateCarousel();
    }
    
    if (prevBtn) prevBtn.addEventListener('click', prevSlide);
    if (nextBtn) nextBtn.addEventListener('click', nextSlide);
    
    // Auto-play functionality
    let autoplayInterval = setInterval(nextSlide, 5000);
    
    // Pause on hover
    if (carousel) {
        carousel.addEventListener('mouseenter', () => clearInterval(autoplayInterval));
        carousel.addEventListener('mouseleave', () => {
            autoplayInterval = setInterval(nextSlide, 5000);
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', () => {
        updateCarousel();
    });
    
    // Initialize
    setTimeout(updateCarousel, 100); // Small delay to ensure DOM is ready
}

// Utility function to check if element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Add loading animation for images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('load', function() {
            this.style.opacity = '1';
        });
        
        // Set initial state
        img.style.opacity = '0';
        img.style.transition = 'opacity 0.3s ease';
        
        // If image is already loaded
        if (img.complete) {
            img.style.opacity = '1';
        }
    });
});

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
            
            // Remove error class on input
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('error');
                }
            });
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// Show notification helper
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Handle window resize
window.addEventListener('resize', function() {
    // Reset mobile menu on desktop
    if (window.innerWidth > 768) {
        const navMenu = document.querySelector('.nav-menu');
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        
        if (navMenu && navMenu.classList.contains('active') && mobileMenuToggle) {
            navMenu.classList.remove('active');
            
            const spans = mobileMenuToggle.querySelectorAll('span');
            spans.forEach(span => {
                span.style.transform = '';
                span.style.opacity = '';
            });
        }
    }
});

// Page-specific helpers
window.toggleForm = function() {
    const form = document.getElementById('newsForm');
    if (!form) return;
    const isHidden = window.getComputedStyle(form).display === 'none';
    form.style.display = isHidden ? 'block' : 'none';
};

function generateSlugFromTitle(value) {
    return value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (slugInput.dataset.edited === 'true' || slugInput.dataset.autoGenerated === 'false') {
                return;
            }
            slugInput.value = generateSlugFromTitle(this.value);
            slugInput.dataset.autoGenerated = 'true';
        });

        slugInput.addEventListener('input', function() {
            this.dataset.edited = 'true';
            this.dataset.autoGenerated = 'false';
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('scan_identitas');
    const label = document.querySelector('.file-upload-label');
    if (fileInput && label) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || '';
            if (fileName) {
                label.textContent = fileName;
                label.style.color = '#093A5A';
            } else {
                label.textContent = 'Klik untuk upload file (PDF, JPG, PNG maks. 2MB)';
                label.style.color = '#7392A8';
            }
        });
    }

    const form = document.getElementById('permohonanForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '#e0e0e0';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (!document.body.classList.contains('admin-dashboard')) return;
    const sidebar = document.getElementById('sidebar');
    const header = document.querySelector('.content-header');
    if (!sidebar || !header) return;

    if (window.innerWidth <= 768 && !header.querySelector('.sidebar-toggle')) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'sidebar-toggle';
        toggleBtn.innerHTML = '☰';
        toggleBtn.style.cssText = 'background: #093A5A; color: #FCFDFD; border: none; padding: 0.5rem; border-radius: 4px; cursor: pointer; margin-right: 1rem;';
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        header.insertBefore(toggleBtn, header.firstChild);
    }
});

// Admin menus page
let menuOrderChanged = false;
let draggedElement = null;
let menuLastPageId = '';
let menuLastManualUrl = '';

function markMenuOrderChanged() {
    menuOrderChanged = true;
    const saveBtn = document.getElementById('saveOrderBtn');
    if (saveBtn) saveBtn.classList.remove('hidden');
}

function updateDraggedParent(listElement) {
    if (!draggedElement || !listElement) return;
    const parentMenuItem = listElement.closest('.menu-item');
    const newParentId = parentMenuItem ? parseInt(parentMenuItem.dataset.id || '0', 10) : 0;
    draggedElement.dataset.parent = String(newParentId);
}

function findPageIdBySlug(pageSelect, slug) {
    if (!pageSelect || !slug) return '';
    for (const option of pageSelect.options) {
        if (option.dataset.slug === slug) {
            return option.value;
        }
    }
    return '';
}

window.showAddMenuModal = function() {
    const modalTitle = document.getElementById('modalTitle');
    const menuForm = document.getElementById('menuForm');
    const menuId = document.getElementById('menuId');
    const menuModal = document.getElementById('menuModal');
    const linkType = document.getElementById('linkType');
    const pageSelect = document.getElementById('pageSelect');
    const manualUrlInput = document.getElementById('manualUrlInput');
    if (!modalTitle || !menuForm || !menuId || !menuModal) return;
    modalTitle.textContent = 'Tambah Menu';
    menuForm.reset();
    menuId.value = '';
    menuLastPageId = '';
    menuLastManualUrl = '';
    if (linkType) linkType.value = 'page';
    if (pageSelect) pageSelect.value = '';
    if (manualUrlInput) manualUrlInput.value = '';
    window.toggleLinkFields();
    menuModal.style.display = 'block';
};

window.editMenu = function(id, name, url, parentId, isActive) {
    const modalTitle = document.getElementById('modalTitle');
    const menuId = document.getElementById('menuId');
    const menuName = document.getElementById('menuName');
    const parentMenu = document.getElementById('parentMenu');
    const isActiveInput = document.getElementById('isActive');
    const menuModal = document.getElementById('menuModal');
    const linkType = document.getElementById('linkType');
    const manualUrlInput = document.getElementById('manualUrlInput');
    const pageSelect = document.getElementById('pageSelect');

    if (!modalTitle || !menuId || !menuName || !parentMenu || !isActiveInput || !menuModal) return;

    modalTitle.textContent = 'Edit Menu';
    menuId.value = id;
    menuName.value = name;
    parentMenu.value = parentId;
    isActiveInput.checked = !!isActive;

    if (linkType && manualUrlInput && pageSelect) {
        const templateMatch = url.match(/template\.php\?slug=([^&]+)/);
        const legacyMatch = url.match(/(?:^|\/)pages\/([^\/]+)\.php/);
        const slug = templateMatch ? decodeURIComponent(templateMatch[1]) : (legacyMatch ? legacyMatch[1] : '');

        if (slug) {
            const pageId = findPageIdBySlug(pageSelect, slug);
            menuLastPageId = pageId || '';
            menuLastManualUrl = '';
            linkType.value = 'page';
            window.toggleLinkFields();
            if (pageId) {
                pageSelect.value = pageId;
            }
        } else {
            menuLastManualUrl = url || '';
            menuLastPageId = pageSelect.value || '';
            linkType.value = 'manual';
            window.toggleLinkFields();
            manualUrlInput.value = menuLastManualUrl;
        }
    }

    menuModal.style.display = 'block';
};

window.closeMenuModal = function() {
    const menuModal = document.getElementById('menuModal');
    if (!menuModal) return;
    menuModal.style.display = 'none';
};

window.toggleLinkFields = function() {
    const linkType = document.getElementById('linkType');
    const pageSelect = document.getElementById('pageSelect');
    const manualUrl = document.getElementById('manualUrl');
    const manualUrlInput = document.getElementById('manualUrlInput');
    const pageSelectDiv = document.getElementById('pageSelectDiv');
    if (!linkType || !manualUrl) return;

    if (linkType.value === 'page') {
        if (manualUrlInput) {
            menuLastManualUrl = manualUrlInput.value || menuLastManualUrl;
        }
        if (pageSelectDiv) {
            pageSelectDiv.style.display = 'block';
        } else if (pageSelect) {
            pageSelect.style.display = 'block';
        }
        manualUrl.style.display = 'none';
        if (pageSelect && menuLastPageId) {
            pageSelect.value = menuLastPageId;
        }
    } else {
        if (pageSelect) {
            menuLastPageId = pageSelect.value || menuLastPageId;
        }
        if (pageSelectDiv) {
            pageSelectDiv.style.display = 'none';
        } else if (pageSelect) {
            pageSelect.style.display = 'none';
        }
        manualUrl.style.display = 'block';
        if (manualUrlInput) {
            manualUrlInput.value = menuLastManualUrl;
        }
    }
};

function initDragAndDrop() {
    const menuList = document.getElementById('menuList');
    if (!menuList) return;

    const menuItems = menuList.querySelectorAll('.menu-item');
    if (menuItems.length === 0) return;

    menuItems.forEach(item => {
        const handle = item.querySelector('.drag-handle');
        if (handle) {
            handle.draggable = true;
            handle.addEventListener('dragstart', function(e) {
                draggedElement = item;
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', item.dataset.id || '');
            });
            handle.addEventListener('dragend', function() {
                item.classList.remove('dragging');
                draggedElement = null;
            });
        }

        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.classList.add('drag-over');
            return false;
        });

        item.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        item.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();

            this.classList.remove('drag-over');

            if (!draggedElement || draggedElement === this) return false;

            const targetList = this.parentNode;
            if (!targetList) return false;

            if (draggedElement.contains(targetList)) return false;

            const siblings = [...targetList.querySelectorAll(':scope > .menu-item')];
            const draggedIndex = siblings.indexOf(draggedElement);
            const targetIndex = siblings.indexOf(this);

            if (draggedIndex < targetIndex) {
                targetList.insertBefore(draggedElement, this.nextSibling);
            } else {
                targetList.insertBefore(draggedElement, this);
            }

            updateDraggedParent(targetList);
            markMenuOrderChanged();

            return false;
        });
    });

    const lists = [menuList, ...menuList.querySelectorAll('.child-menus')];
    lists.forEach(list => {
        list.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        list.addEventListener('dragleave', function(e) {
            if (e.target === this) {
                this.classList.remove('drag-over');
            }
        });

        list.addEventListener('drop', function(e) {
            e.preventDefault();
            if (!draggedElement) return;

            if (e.target.closest('.menu-item')) return;
            if (draggedElement.contains(this)) return;

            this.classList.remove('drag-over');
            this.appendChild(draggedElement);
            updateDraggedParent(this);
            markMenuOrderChanged();
        });
    });
}

function collectMenuOrder(list, parentId, menuData) {
    const items = list.querySelectorAll(':scope > .menu-item');
    items.forEach((item, index) => {
        const id = parseInt(item.dataset.id || '0', 10);
        menuData.push({ id, parent: parentId, order: index + 1 });
        const childList = item.querySelector(':scope > .child-menus');
        if (childList) {
            collectMenuOrder(childList, id, menuData);
        }
    });
}

window.saveMenuOrder = function() {
    const menuList = document.getElementById('menuList');
    const saveBtn = document.getElementById('saveOrderBtn');
    if (!menuList || !saveBtn) return;

    const menuData = [];
    collectMenuOrder(menuList, 0, menuData);

    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = 'Menyimpan...';
    saveBtn.disabled = true;

    fetch('reorder.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ menus: menuData })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showSuccessMessage('Urutan menu berhasil disimpan!');
                menuOrderChanged = false;
                saveBtn.classList.add('hidden');
            } else {
                showErrorMessage('Gagal menyimpan urutan menu: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error details:', error);
            showErrorMessage('Gagal menyimpan urutan menu. Silakan coba lagi.');
        })
        .finally(() => {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
};

function initAdminMenusIfNeeded() {
    if (!document.body.classList.contains('admin-menus')) return;
    initDragAndDrop();
}

document.addEventListener('click', function(event) {
    if (!document.body.classList.contains('admin-menus')) return;
    const btn = event.target.closest('.js-edit-menu');
    if (!btn) return;
    const id = parseInt(btn.dataset.id || '0', 10);
    const name = btn.dataset.name || '';
    const url = btn.dataset.url || '';
    const parentId = parseInt(btn.dataset.parent || '0', 10);
    const isActive = parseInt(btn.dataset.active || '0', 10);
    window.editMenu(id, name, url, parentId, isActive);
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminMenusIfNeeded);
} else {
    initAdminMenusIfNeeded();
}
// Admin permohonan page
window.showStatusModal = function(permohonanId, currentStatus) {
    const permohonanInput = document.getElementById('permohonanId');
    const currentStatusInput = document.getElementById('currentStatus');
    const statusModal = document.getElementById('statusModal');
    if (!permohonanInput || !currentStatusInput || !statusModal) return;

    permohonanInput.value = permohonanId;
    const statusText = currentStatus ? currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1) : '';
    currentStatusInput.value = statusText;
    statusModal.style.display = 'block';
};

window.closeStatusModal = function() {
    const statusModal = document.getElementById('statusModal');
    if (!statusModal) return;
    statusModal.style.display = 'none';
};

window.showDetail = function(permohonanId) {
    const detailContent = document.getElementById('detailContent');
    const detailModal = document.getElementById('detailModal');
    if (!detailContent || !detailModal) return;

    fetch('detail.php?id=' + permohonanId)
        .then(response => response.text())
        .then(html => {
            detailContent.innerHTML = html;
            detailModal.style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading detail:', error);
            detailContent.innerHTML = '<p>Gagal memuat detail.</p>';
            detailModal.style.display = 'block';
        });
};

window.closeDetailModal = function() {
    const detailModal = document.getElementById('detailModal');
    if (!detailModal) return;
    detailModal.style.display = 'none';
};

document.addEventListener('click', function(event) {
    if (!document.body.classList.contains('admin-permohonan')) return;
    const statusModal = document.getElementById('statusModal');
    const detailModal = document.getElementById('detailModal');
    const statusBtn = event.target.closest('.js-status-permohonan');
    if (statusBtn) {
        const permohonanId = parseInt(statusBtn.dataset.id || '0', 10);
        const currentStatus = statusBtn.dataset.status || '';
        window.showStatusModal(permohonanId, currentStatus);
        return;
    }

    if (statusModal && event.target === statusModal) {
        statusModal.style.display = 'none';
    }
    if (detailModal && event.target === detailModal) {
        detailModal.style.display = 'none';
    }
});

// Admin keberatan page
window.showKeberatanStatusModal = function(keberatanId, currentStatus) {
    const keberatanInput = document.getElementById('keberatanId');
    const currentStatusInput = document.getElementById('keberatanCurrentStatus');
    const statusModal = document.getElementById('keberatanStatusModal');
    if (!keberatanInput || !currentStatusInput || !statusModal) return;

    keberatanInput.value = keberatanId;
    const statusText = currentStatus ? currentStatus.charAt(0).toUpperCase() + currentStatus.slice(1) : '';
    currentStatusInput.value = statusText;
    statusModal.style.display = 'block';
};

window.closeKeberatanStatusModal = function() {
    const statusModal = document.getElementById('keberatanStatusModal');
    if (!statusModal) return;
    statusModal.style.display = 'none';
};

window.showKeberatanDetail = function(keberatanId) {
    const detailContent = document.getElementById('keberatanDetailContent');
    const detailModal = document.getElementById('keberatanDetailModal');
    if (!detailContent || !detailModal) return;

    fetch('detail.php?id=' + keberatanId)
        .then(response => response.text())
        .then(html => {
            detailContent.innerHTML = html;
            detailModal.style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading detail:', error);
            detailContent.innerHTML = '<p>Gagal memuat detail.</p>';
            detailModal.style.display = 'block';
        });
};

window.closeKeberatanDetailModal = function() {
    const detailModal = document.getElementById('keberatanDetailModal');
    if (!detailModal) return;
    detailModal.style.display = 'none';
};

document.addEventListener('click', function(event) {
    if (!document.body.classList.contains('admin-keberatan')) return;
    const statusModal = document.getElementById('keberatanStatusModal');
    const detailModal = document.getElementById('keberatanDetailModal');

    if (statusModal && event.target === statusModal) {
        statusModal.style.display = 'none';
    }
    if (detailModal && event.target === detailModal) {
        detailModal.style.display = 'none';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (!document.body.classList.contains('admin-keberatan')) return;
    document.querySelectorAll('.js-status-keberatan').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = parseInt(this.dataset.id || '0', 10);
            const status = this.dataset.status || '';
            window.showKeberatanStatusModal(id, status);
        });
    });
    document.querySelectorAll('.js-detail-keberatan').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = parseInt(this.dataset.id || '0', 10);
            window.showKeberatanDetail(id);
        });
    });
});

// Admin page builder (blocks)
function initPageBuilder() {
    const blocksContainer = document.getElementById('pageBlocks');
    const form = document.getElementById('pageForm');
    if (!blocksContainer || !form) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const statusEl = document.getElementById('pageSaveStatus');
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const pageId = document.getElementById('pageId')?.value || '';

    let blockCounter = 0;

    function setStatus(message, isError = false) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.style.color = isError ? '#dc3545' : '#666';
    }

    function normalizeAssetPath(path) {
        if (!path) return '';
        if (/^https?:\/\//i.test(path) || path.startsWith('/')) return path;
        return '../../' + path.replace(/^\/+/, '');
    }

    function createBlockShell(type, title) {
        const block = document.createElement('div');
        block.className = 'block-item';
        block.dataset.type = type;

        const header = document.createElement('div');
        header.className = 'block-header';
        const titleEl = document.createElement('div');
        titleEl.className = 'block-title';
        titleEl.textContent = title;
        const actions = document.createElement('div');
        actions.className = 'block-actions';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'Hapus';
        removeBtn.addEventListener('click', () => block.remove());

        actions.appendChild(removeBtn);
        header.appendChild(titleEl);
        header.appendChild(actions);
        block.appendChild(header);

        return block;
    }

    function addTextBlock(data = {}) {
        const block = createBlockShell('text', 'Blok Teks');
        const field = document.createElement('div');
        field.className = 'block-field';
        field.innerHTML = '<label>Konten</label><textarea class="block-text" rows="5"></textarea>';
        block.appendChild(field);
        block.querySelector('.block-text').value = data.content || '';
        blocksContainer.appendChild(block);
    }

    function addLinkBlock(data = {}) {
        const block = createBlockShell('link', 'Blok Link');
        const row = document.createElement('div');
        row.className = 'block-row';
        row.innerHTML = `
            <div class="block-field">
                <label>Label</label>
                <input type="text" class="block-link-label">
            </div>
            <div class="block-field">
                <label>URL</label>
                <input type="text" class="block-link-url" placeholder="https://... atau /path">
            </div>
        `;
        block.appendChild(row);

        const targetField = document.createElement('div');
        targetField.className = 'block-field';
        targetField.innerHTML = `
            <label>Target</label>
            <select class="block-link-target">
                <option value="_self">Tab sama</option>
                <option value="_blank">Tab baru</option>
            </select>
        `;
        block.appendChild(targetField);

        block.querySelector('.block-link-label').value = data.label || '';
        block.querySelector('.block-link-url').value = data.url || '';
        block.querySelector('.block-link-target').value = data.target || '_self';
        blocksContainer.appendChild(block);
    }

    function addFileBlock(data = {}) {
        const block = createBlockShell('file', 'Blok File');
        const fileFieldName = `block_file_${blockCounter++}`;
        block.dataset.fileField = fileFieldName;
        if (data.file_path) {
            block.dataset.existingPath = data.file_path;
            block.dataset.existingName = data.file_name || '';
        }

        const field = document.createElement('div');
        field.className = 'block-field';
        field.innerHTML = `
            <label>Upload File (PDF/JPG/PNG)</label>
            <input type="file" class="block-file-input" name="${fileFieldName}">
            <div class="block-preview"></div>
        `;
        block.appendChild(field);

        const preview = field.querySelector('.block-preview');
        if (data.file_name) {
            preview.textContent = `File saat ini: ${data.file_name}`;
        }

        blocksContainer.appendChild(block);
    }

    function addImageBlock(data = {}) {
        const block = createBlockShell('image', 'Blok Gambar');
        const fileFieldName = `block_image_${blockCounter++}`;
        block.dataset.fileField = fileFieldName;
        if (data.image_path) {
            block.dataset.existingPath = data.image_path;
        }

        const field = document.createElement('div');
        field.className = 'block-field';
        field.innerHTML = `
            <label>Upload Gambar (JPG/PNG)</label>
            <input type="file" class="block-image-input" name="${fileFieldName}">
            <div class="block-preview"></div>
        `;
        block.appendChild(field);

        const row = document.createElement('div');
        row.className = 'block-row';
        row.innerHTML = `
            <div class="block-field">
                <label>Alt Text</label>
                <input type="text" class="block-image-alt">
            </div>
            <div class="block-field">
                <label>Caption</label>
                <input type="text" class="block-image-caption">
            </div>
        `;
        block.appendChild(row);

        block.querySelector('.block-image-alt').value = data.alt_text || '';
        block.querySelector('.block-image-caption').value = data.caption || '';

        const preview = field.querySelector('.block-preview');
        if (data.image_path) {
            const img = document.createElement('img');
            img.src = normalizeAssetPath(data.image_path);
            img.style.maxWidth = '200px';
            img.style.marginTop = '0.5rem';
            preview.appendChild(img);
        }

        blocksContainer.appendChild(block);
    }

    function parseCellValue(value) {
        const raw = (value || '').toString().trim();
        if (!raw) {
            return { type: 'text', text: '' };
        }
        const match = raw.match(/^(link|file|image|img):\s*(.+)$/i);
        if (!match) {
            return { type: 'text', text: raw };
        }
        const type = match[1].toLowerCase();
        const parts = match[2].split('|').map(p => p.trim());
        if ((type === 'link') && parts.length >= 2) {
            return { type: 'link', text: parts[0], url: parts[1] };
        }
        if ((type === 'file') && parts.length >= 2) {
            return { type: 'file', text: parts[0], path: parts[1] };
        }
        if ((type === 'image' || type === 'img') && parts.length >= 2) {
            return { type: 'image', text: parts[0], path: parts[1] };
        }
        return { type: 'text', text: raw };
    }

    function toCellToken(cell) {
        const text = (cell.text || '').trim();
        if (cell.type === 'link' && cell.url) {
            return `link:${text || 'Link'}|${cell.url}`;
        }
        if (cell.type === 'file' && cell.path) {
            return `file:${text || 'File'}|${cell.path}`;
        }
        if (cell.type === 'image' && cell.path) {
            return `image:${text || 'Gambar'}|${cell.path}`;
        }
        return text;
    }

    function updateCellPreview(cellEl) {
        const preview = cellEl.querySelector('.cell-preview');
        if (!preview) return;
        const type = cellEl.dataset.type || 'text';
        const url = cellEl.dataset.url || '';
        const path = cellEl.dataset.path || '';
        const label = cellEl.querySelector('.cell-text')?.value || '';

        preview.innerHTML = '';

        if (type === 'link' && url) {
            const a = document.createElement('a');
            a.href = url;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            a.textContent = label || url;
            preview.appendChild(a);
        } else if (type === 'file' && path) {
            const a = document.createElement('a');
            a.href = normalizeAssetPath(path);
            a.textContent = label || path.split('/').pop();
            a.download = '';
            preview.appendChild(a);
        } else if (type === 'image' && path) {
            const img = document.createElement('img');
            img.src = normalizeAssetPath(path);
            img.alt = label || '';
            img.style.maxWidth = '120px';
            img.style.height = 'auto';
            preview.appendChild(img);
        }
    }

    function setCellType(cellEl, type, meta = {}) {
        cellEl.dataset.type = type;
        if (type === 'link') {
            cellEl.dataset.url = meta.url || cellEl.dataset.url || '';
            cellEl.dataset.path = '';
        }
        if (type === 'file' || type === 'image') {
            cellEl.dataset.path = meta.path || cellEl.dataset.path || '';
            cellEl.dataset.url = '';
        }
        if (type === 'text') {
            cellEl.dataset.url = '';
            cellEl.dataset.path = '';
        }
        updateCellPreview(cellEl);
    }

    function uploadTableAsset(file, kind) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', kind);
        return fetch('upload_cell.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken
            },
            body: formData
        }).then(res => res.json());
    }

    function createCellEditor(cellData) {
        const cell = document.createElement('div');
        cell.className = 'cell-editor';
        cell.dataset.type = cellData.type || 'text';
        cell.dataset.url = cellData.url || '';
        cell.dataset.path = cellData.path || '';

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'cell-text';
        input.placeholder = 'Isi sel';
        input.value = cellData.text || '';
        cell.appendChild(input);

        const toolbar = document.createElement('div');
        toolbar.className = 'cell-toolbar';

        const btnText = document.createElement('button');
        btnText.type = 'button';
        btnText.textContent = 'Teks';
        btnText.addEventListener('click', () => setCellType(cell, 'text'));

        const btnLink = document.createElement('button');
        btnLink.type = 'button';
        btnLink.textContent = 'Link';
        btnLink.addEventListener('click', () => {
            const url = prompt('Masukkan URL link:');
            if (url) {
                setCellType(cell, 'link', { url });
            }
        });

        const btnFile = document.createElement('button');
        btnFile.type = 'button';
        btnFile.textContent = 'File';
        btnFile.addEventListener('click', () => {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.pdf,.jpg,.jpeg,.png';
            fileInput.addEventListener('change', () => {
                const file = fileInput.files[0];
                if (!file) return;
                uploadTableAsset(file, 'file')
                    .then(res => {
                        if (!res.success) {
                            alert(res.message || 'Gagal upload file');
                            return;
                        }
                        if (!input.value.trim()) {
                            input.value = res.name || file.name;
                        }
                        setCellType(cell, 'file', { path: res.path });
                    })
                    .catch(() => alert('Gagal upload file'));
            });
            fileInput.click();
        });

        const btnImage = document.createElement('button');
        btnImage.type = 'button';
        btnImage.textContent = 'Gambar';
        btnImage.addEventListener('click', () => {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.jpg,.jpeg,.png';
            fileInput.addEventListener('change', () => {
                const file = fileInput.files[0];
                if (!file) return;
                uploadTableAsset(file, 'image')
                    .then(res => {
                        if (!res.success) {
                            alert(res.message || 'Gagal upload gambar');
                            return;
                        }
                        if (!input.value.trim()) {
                            input.value = res.name || file.name;
                        }
                        setCellType(cell, 'image', { path: res.path });
                    })
                    .catch(() => alert('Gagal upload gambar'));
            });
            fileInput.click();
        });

        toolbar.appendChild(btnText);
        toolbar.appendChild(btnLink);
        toolbar.appendChild(btnFile);
        toolbar.appendChild(btnImage);
        cell.appendChild(toolbar);

        const preview = document.createElement('div');
        preview.className = 'cell-preview';
        cell.appendChild(preview);

        input.addEventListener('input', () => updateCellPreview(cell));
        updateCellPreview(cell);

        return cell;
    }

    function buildTableEditor(headers, rowsData) {
        const editor = document.createElement('div');
        editor.className = 'table-editor';

        const controls = document.createElement('div');
        controls.className = 'table-editor-controls';

        const addColBtn = document.createElement('button');
        addColBtn.type = 'button';
        addColBtn.textContent = '+ Kolom';

        const addRowBtn = document.createElement('button');
        addRowBtn.type = 'button';
        addRowBtn.textContent = '+ Baris';

        controls.appendChild(addColBtn);
        controls.appendChild(addRowBtn);
        editor.appendChild(controls);

        const table = document.createElement('table');
        table.className = 'table-editor-table';

        const thead = document.createElement('thead');
        const headRow = document.createElement('tr');
        const controlTh = document.createElement('th');
        controlTh.className = 'table-control-cell';
        controlTh.textContent = 'Aksi';
        headRow.appendChild(controlTh);

        function refreshColumnIndexes() {
            const headerCells = headRow.querySelectorAll('th.table-data-cell');
            headerCells.forEach((th, idx) => {
                th.dataset.colIndex = String(idx);
            });
        }

        function removeColumn(colIndex) {
            const dataColumns = headRow.querySelectorAll('th.table-data-cell');
            if (dataColumns.length <= 1) return;
            const target = [...dataColumns].find(th => parseInt(th.dataset.colIndex, 10) === colIndex);
            if (!target) return;
            target.remove();
            table.querySelectorAll('tbody tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                const dataCellIndex = colIndex + 1;
                if (cells[dataCellIndex]) {
                    cells[dataCellIndex].remove();
                }
            });
            refreshColumnIndexes();
        }

        function createHeaderCell(label) {
            const th = document.createElement('th');
            th.className = 'table-data-cell';

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'table-header-input';
            input.value = label;

            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'table-col-delete';
            deleteBtn.textContent = 'Hapus';
            deleteBtn.addEventListener('click', () => {
                const colIndex = parseInt(th.dataset.colIndex || '0', 10);
                removeColumn(colIndex);
            });

            const wrapper = document.createElement('div');
            wrapper.className = 'table-header-wrap';
            wrapper.appendChild(input);
            wrapper.appendChild(deleteBtn);
            th.appendChild(wrapper);

            return th;
        }

        headers.forEach((headerText) => {
            headRow.appendChild(createHeaderCell(headerText));
        });

        refreshColumnIndexes();
        thead.appendChild(headRow);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');
        rowsData.forEach((row) => {
            const tr = document.createElement('tr');
            const actionTd = document.createElement('td');
            actionTd.className = 'table-control-cell';
            const rowDeleteBtn = document.createElement('button');
            rowDeleteBtn.type = 'button';
            rowDeleteBtn.className = 'table-row-delete';
            rowDeleteBtn.textContent = 'Hapus';
            rowDeleteBtn.addEventListener('click', () => tr.remove());
            actionTd.appendChild(rowDeleteBtn);
            tr.appendChild(actionTd);

            row.forEach((cellData) => {
                const td = document.createElement('td');
                td.appendChild(createCellEditor(cellData));
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
        table.appendChild(tbody);
        editor.appendChild(table);

        addColBtn.addEventListener('click', () => {
            const colCount = headRow.querySelectorAll('th.table-data-cell').length + 1;
            const th = createHeaderCell(`Kolom ${colCount}`);
            headRow.appendChild(th);
            tbody.querySelectorAll('tr').forEach((tr) => {
                const td = document.createElement('td');
                td.appendChild(createCellEditor({ type: 'text', text: '' }));
                tr.appendChild(td);
            });
            refreshColumnIndexes();
        });

        addRowBtn.addEventListener('click', () => {
            const tr = document.createElement('tr');
            const actionTd = document.createElement('td');
            actionTd.className = 'table-control-cell';
            const rowDeleteBtn = document.createElement('button');
            rowDeleteBtn.type = 'button';
            rowDeleteBtn.className = 'table-row-delete';
            rowDeleteBtn.textContent = 'Hapus';
            rowDeleteBtn.addEventListener('click', () => tr.remove());
            actionTd.appendChild(rowDeleteBtn);
            tr.appendChild(actionTd);

            const colCount = headRow.querySelectorAll('th.table-data-cell').length;
            for (let i = 0; i < colCount; i += 1) {
                const td = document.createElement('td');
                td.appendChild(createCellEditor({ type: 'text', text: '' }));
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        });

        return editor;
    }

    function addTableBlock(data = {}) {
        const block = createBlockShell('table', 'Blok Tabel');
        const headers = data.table?.headers?.length ? data.table.headers : ['Kolom 1', 'Kolom 2'];
        const rows = data.table?.rows?.length
            ? data.table.rows.map(r => r.map(cell => parseCellValue(cell)))
            : [
                [parseCellValue(''), parseCellValue('')],
                [parseCellValue(''), parseCellValue('')]
            ];

        const editor = buildTableEditor(headers, rows);
        block.appendChild(editor);

        const row = document.createElement('div');
        row.className = 'block-row';
        row.innerHTML = `
            <div class="block-field">
                <label><input type="checkbox" class="block-table-search" checked> Aktifkan pencarian</label>
            </div>
            <div class="block-field">
                <label><input type="checkbox" class="block-table-sort" checked> Aktifkan sort</label>
            </div>
        `;
        block.appendChild(row);

        block.querySelector('.block-table-search').checked = data.table?.enable_search !== 0;
        block.querySelector('.block-table-sort').checked = data.table?.enable_sort !== 0;

        blocksContainer.appendChild(block);
    }

    function collectBlocks() {
        const blocks = [];
        const items = blocksContainer.querySelectorAll('.block-item');
        items.forEach(item => {
            const type = item.dataset.type;
            if (type === 'text') {
                blocks.push({
                    type: 'text',
                    content: item.querySelector('.block-text')?.value || ''
                });
                return;
            }
            if (type === 'link') {
                blocks.push({
                    type: 'link',
                    label: item.querySelector('.block-link-label')?.value || '',
                    url: item.querySelector('.block-link-url')?.value || '',
                    target: item.querySelector('.block-link-target')?.value || '_self'
                });
                return;
            }
            if (type === 'file') {
                blocks.push({
                    type: 'file',
                    file_field: item.dataset.fileField || '',
                    existing_path: item.dataset.existingPath || '',
                    existing_name: item.dataset.existingName || ''
                });
                return;
            }
            if (type === 'image') {
                blocks.push({
                    type: 'image',
                    file_field: item.dataset.fileField || '',
                    existing_path: item.dataset.existingPath || '',
                    alt_text: item.querySelector('.block-image-alt')?.value || '',
                    caption: item.querySelector('.block-image-caption')?.value || ''
                });
                return;
            }
            if (type === 'table') {
                const headers = [];
                item.querySelectorAll('th.table-data-cell .table-header-input').forEach(input => {
                    const val = (input.value || '').trim();
                    headers.push(val || 'Kolom');
                });

                const rows = [];
                item.querySelectorAll('tbody tr').forEach(tr => {
                    const row = [];
                    tr.querySelectorAll('td .cell-editor').forEach(cellEl => {
                        const cellData = {
                            type: cellEl.dataset.type || 'text',
                            text: cellEl.querySelector('.cell-text')?.value || '',
                            url: cellEl.dataset.url || '',
                            path: cellEl.dataset.path || ''
                        };
                        row.push(toCellToken(cellData));
                    });
                    rows.push(row);
                });

                blocks.push({
                    type: 'table',
                    headers,
                    rows,
                    enable_search: item.querySelector('.block-table-search')?.checked ? 1 : 0,
                    enable_sort: item.querySelector('.block-table-sort')?.checked ? 1 : 0
                });
            }
        });
        return blocks;
    }

    document.querySelectorAll('[data-add-block]').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.addBlock;
            if (type === 'text') addTextBlock();
            if (type === 'link') addLinkBlock();
            if (type === 'file') addFileBlock();
            if (type === 'image') addImageBlock();
            if (type === 'table') addTableBlock();
        });
    });

    if (pageId) {
        fetch(`api.php?action=load&page_id=${pageId}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                if (data.blocks && data.blocks.length > 0) {
                    data.blocks.forEach(block => {
                        if (block.type === 'text') addTextBlock(block);
                        if (block.type === 'link') addLinkBlock(block);
                        if (block.type === 'file') addFileBlock(block);
                        if (block.type === 'image') addImageBlock(block);
                        if (block.type === 'table') addTableBlock(block);
                    });
                } else if (data.legacy_content) {
                    addTextBlock({ content: data.legacy_content });
                }
            });
    } else {
        addTextBlock();
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        setStatus('Menyimpan...');

        const blocks = collectBlocks();
        const formData = new FormData();
        formData.append('action', 'save');
        formData.append('page_id', pageId);
        formData.append('title', titleInput?.value || '');
        formData.append('slug', slugInput?.value || '');
        formData.append('blocks', JSON.stringify(blocks));

        // Files are already in the form DOM and will be picked up by FormData if appended explicitly
        const fileInputs = blocksContainer.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            if (input.name && input.files.length > 0) {
                formData.append(input.name, input.files[0]);
            }
        });

        fetch('api.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    setStatus(data.message || 'Gagal menyimpan halaman', true);
                    return;
                }
                setStatus('Berhasil disimpan. Mengalihkan...');
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(() => setStatus('Gagal menyimpan halaman', true));
    });
}

function initPageBuilderIfNeeded() {
    if (document.body.classList.contains('admin-pages-create') || document.body.classList.contains('admin-pages-edit')) {
        initPageBuilder();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageBuilderIfNeeded);
} else {
    initPageBuilderIfNeeded();
}

// Admin DIP form helpers
document.addEventListener('DOMContentLoaded', function() {
    if (!document.body.classList.contains('admin-dip')) return;

    const form = document.getElementById('dipForm');
    const resetBtn = document.getElementById('dipResetBtn');
    const currentFileInfo = document.getElementById('currentFileInfo');

    function resetForm() {
        if (!form) return;
        form.reset();
        const dipId = document.getElementById('dipId');
        const existingFileId = document.getElementById('existingFileId');
        if (dipId) dipId.value = '';
        if (existingFileId) existingFileId.value = '';
        if (currentFileInfo) currentFileInfo.textContent = '';
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', resetForm);
    }

    document.querySelectorAll('.js-edit-dip').forEach(btn => {
        btn.addEventListener('click', function() {
            const dipId = document.getElementById('dipId');
            const existingFileId = document.getElementById('existingFileId');
            if (dipId) dipId.value = this.dataset.id || '';
            if (existingFileId) existingFileId.value = this.dataset.fileId || '';

            const judul = document.getElementById('judul');
            const ringkasan = document.getElementById('ringkasan');
            const kategori = document.getElementById('kategori');
            const tahun = document.getElementById('tahun');
            const status = document.getElementById('status_publikasi');

            if (judul) judul.value = this.dataset.judul || '';
            if (ringkasan) ringkasan.value = this.dataset.ringkasan || '';
            if (kategori) kategori.value = this.dataset.kategori || '';
            if (tahun) tahun.value = this.dataset.tahun || '';
            if (status) status.value = this.dataset.status || 'published';

            if (currentFileInfo) {
                const fileName = this.dataset.fileName || '';
                currentFileInfo.textContent = fileName ? `File saat ini: ${fileName}` : '';
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
});



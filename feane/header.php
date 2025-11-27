<!-- ==================== HEADER ==================== -->
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="header_section <?php echo ($pageType === 'detail') ? 'header_detail' : ''; ?>">
    <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container">

            <!-- LEFT: Menu icon + Logo -->
            <div class="header-left d-flex align-items-center">
                <!-- Sidebar toggle -->
                <i id="sidebarToggle" class="fa fa-bars sidebar-toggle"></i>

                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <img src="images/Book.png" alt="Logo" style="height: 48px; margin-right:10px;">
                    <span style="font-weight: bold; font-size: 20px; color: #fff;">
                        Cửa Hàng<br><small style="font-size:14px; color: #ffc107;">Sách</small>
                    </span>
                </a>
            </div>

            <!-- CENTER: Search -->
            <div class="header-center">
                <div class="search-box">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text" id="header-search" class="search-input" placeholder="Tìm kiếm sách...">
                    <ul class="search-suggestions" id="search-suggestions"></ul>
                </div>
            </div>

            <!-- RIGHT: User + Cart Icon -->
            <div class="header-right user_option">
                <!-- Cart Icon (Trigger Right Sidebar) -->
                <button id="cartSidebarToggle" class="cart-icon-btn" title="Giỏ hàng">
                    <i class="fa fa-shopping-cart"></i>
                    <span id="cart-badge" class="cart-badge">0</span>
                </button>

                <?php if (isset($_SESSION['hoten'])): ?>
                    <div class="user-dropdown">
                        <div class="user-dropdown-trigger">
                            <i class="fa fa-user-circle text-warning" style="font-size:18px;"></i>
                            Xin chào, <b><?php echo htmlspecialchars($_SESSION['hoten']); ?></b>
                        </div>
                        <div class="user-dropdown-menu">
                            <a href="yeuthich.php" class="dropdown-item">Yêu thích</a>
                            <a href="lichsu_donhang.php" class="dropdown-item">Lịch sử mua hàng</a>
                            <hr>
                            <a href="dangxuat.php" class="dropdown-item text-danger">Đăng xuất</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="dangnhap.php" class="btn btn-outline-warning fw-bold"
                        style="border-radius:25px; padding:6px 20px;">
                        <i class="fa fa-user mr-2"></i> Đăng nhập
                    </a>
                <?php endif; ?>
            </div>

        </nav>
    </div>
</header>

<!-- ==================== SIDEBAR ==================== -->
<!-- SIDEBAR YOUTUBE STYLE -->
<div id="sidebar" class="yt-sidebar">
    <div class="yt-section">
        <a href="index.php" class="yt-item">
            <i class="fa fa-home"></i> Trang chủ
        </a>
        <a href="menu.php" class="yt-item">
            <i class="fa fa-book"></i> Kho sách
        </a>
        <a href="about.php" class="yt-item">
            <i class="fa fa-info-circle"></i> Giới thiệu
        </a>
    </div>
    <div class="yt-section">
        <a href="yeuthich.php" class="yt-item">
            <i class="fa fa-heart"></i> Yêu thích
        </a>
        <a href="lichsu_donhang.php" class="yt-item">
            <i class="fa fa-history"></i> Lịch sử mua hàng
        </a>
    </div>
</div>

<!-- OVERLAY -->
<div id="sidebarOverlay" class="yt-overlay"></div>

<!-- ==================== RIGHT SIDEBAR (CART) ==================== -->
<div id="cartSidebar" class="cart-sidebar">
    <div class="cart-sidebar-header">
        <h3>Giỏ Hàng</h3>
        <button id="cartSidebarClose" class="cart-sidebar-close">
            <i class="fa fa-times"></i>
        </button>
    </div>
    <div id="cartSidebarContent" class="cart-sidebar-content">
        <p class="text-center text-muted" style="padding: 20px;">Giỏ hàng trống</p>
    </div>
    <div class="cart-sidebar-footer">
        <div class="cart-total">
            <strong>Tổng cộng:</strong>
            <span id="cartTotal">0₫</span>
        </div>
        <a href="book.php" class="btn btn-warning w-100 mt-2">
            <i class="fa fa-shopping-cart"></i> Xem giỏ hàng
        </a>
    </div>
</div>

<!-- CART SIDEBAR OVERLAY -->
<div id="cartSidebarOverlay" class="cart-sidebar-overlay"></div>


<script>
    window.addEventListener("scroll", function() {
        const header = document.querySelector(".header_section");
        if (window.scrollY > 10) header.classList.add("scrolled");
        else header.classList.remove("scrolled");
    });
</script>

<script>
    // SEARCH HEADER JS (GIỮ NGUYÊN)
    const searchBox = document.querySelector(".search-box");
    const searchInput = document.querySelector("#header-search");
    const searchSuggestions = document.querySelector("#search-suggestions");
    const searchIcon = document.querySelector(".search-icon");

    searchIcon.addEventListener("mouseenter", (e) => {
        e.stopPropagation();
        searchBox.classList.add("active");
        searchInput.focus();
    });

    searchInput.addEventListener("input", () => {
        const keyword = searchInput.value.trim();

        if (keyword.length === 0) {
            searchSuggestions.style.display = "none";
            return;
        }

        fetch("search_suggest.php?keyword=" + encodeURIComponent(keyword))
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    searchSuggestions.style.display = "none";
                    return;
                }

                const keywordLower = keyword.toLowerCase();

                data.sort((a, b) => {
                    const aStart = a.tensach.toLowerCase().startsWith(keywordLower) ? 0 : 1;
                    const bStart = b.tensach.toLowerCase().startsWith(keywordLower) ? 0 : 1;
                    return aStart - bStart;
                });

                searchSuggestions.innerHTML = data
                    .map(item => `
                        <li data-id="${item.idsach}">
                            <b>${item.tensach}</b><br>
                            <small>${item.tentacgia}</small>
                        </li>
                    `)
                    .join("");

                searchSuggestions.style.display = "block";
            });
    });

    searchSuggestions.addEventListener("click", (e) => {
        const li = e.target.closest("li");
        if (!li) return;

        const title = li.querySelector("b").textContent;

        searchInput.value = title;
        searchSuggestions.style.display = "none";

        window.location.href = "menu.php?keyword=" + encodeURIComponent(title);
    });

    searchSuggestions.addEventListener("mousedown", (e) => {
        e.preventDefault();
    });

    searchInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();

            const keyword = searchInput.value.trim();
            if (keyword !== "") {
                window.location.href = "menu.php?keyword=" + encodeURIComponent(keyword);
            }
        }
    });

    document.addEventListener("click", (e) => {
        if (!searchBox.contains(e.target)) {
            searchBox.classList.remove("active");
            searchSuggestions.style.display = "none";
        }
    });
</script>

<script>
    // SIDEBAR (GIỮ NGUYÊN)
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");
    const toggleBtn = document.getElementById("sidebarToggle");

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");

        toggleBtn.classList.toggle("fa-bars");
        toggleBtn.classList.toggle("fa-times");
    });

    overlay.addEventListener("click", () => {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");

        toggleBtn.classList.add("fa-bars");
        toggleBtn.classList.remove("fa-times");
    });
</script>

<script>
    // ==================== RIGHT SIDEBAR CART JS ====================
    const cartSidebarToggle = document.getElementById('cartSidebarToggle');
    const cartSidebar = document.getElementById('cartSidebar');
    const cartSidebarClose = document.getElementById('cartSidebarClose');
    const cartSidebarOverlay = document.getElementById('cartSidebarOverlay');
    const cartBadge = document.getElementById('cart-badge');

    // Open cart sidebar
    cartSidebarToggle.addEventListener('click', () => {
        cartSidebar.classList.add('active');
        cartSidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        updateCartSidebarDisplay();
    });

    // Close cart sidebar
    function closeCartSidebar() {
        cartSidebar.classList.remove('active');
        cartSidebarOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    cartSidebarClose.addEventListener('click', closeCartSidebar);
    cartSidebarOverlay.addEventListener('click', closeCartSidebar);

    // Update cart sidebar display
    function updateCartSidebarDisplay() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartContent = document.getElementById('cartSidebarContent');
        const cartTotal = document.getElementById('cartTotal');

        if (cart.length === 0) {
            cartContent.innerHTML = '<p class="text-center text-muted" style="padding: 20px;">Giỏ hàng trống</p>';
            cartTotal.textContent = '0₫';
            cartBadge.textContent = '0';
            return;
        }

        let total = 0;
        let html = '<div class="cart-items">';

        cart.forEach((item, index) => {
            const itemTotal = item.dongia * item.soluong;
            total += itemTotal;

            html += `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <h6>${item.tensach}</h6>
                        <p class="text-muted"><small>Giá: ${item.dongia.toLocaleString()}₫</small></p>
                        <div class="cart-item-controls">
                            <button class="qty-btn qty-minus" data-index="${index}" title="Giảm">
                                <i class="fa fa-minus"></i>
                            </button>
                            <input type="number" class="qty-input" value="${item.soluong}" min="1" data-index="${index}" readonly>
                            <button class="qty-btn qty-plus" data-index="${index}" title="Tăng">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                        <p class="cart-item-price">${itemTotal.toLocaleString()}₫</p>
                    </div>
                    <button class="cart-item-remove" data-index="${index}" title="Xóa">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            `;
        });

        html += '</div>';
        cartContent.innerHTML = html;
        cartTotal.textContent = total.toLocaleString() + '₫';
        cartBadge.textContent = cart.length;

        // Add event listeners for quantity buttons
        document.querySelectorAll('.qty-minus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.dataset.index);
                decreaseQty(index);
            });
        });

        document.querySelectorAll('.qty-plus').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.dataset.index);
                increaseQty(index);
            });
        });

        // Add event listeners for remove buttons
        document.querySelectorAll('.cart-item-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = e.currentTarget.dataset.index;
                removeFromCart(parseInt(index));
            });
        });
    }

    // Increase quantity
    function increaseQty(index) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        if (cart[index]) {
            cart[index].soluong += 1;
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartSidebarDisplay();
        }
    }

    // Decrease quantity
    function decreaseQty(index) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        if (cart[index]) {
            if (cart[index].soluong > 1) {
                cart[index].soluong -= 1;
            } else {
                cart.splice(index, 1);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartSidebarDisplay();
        }
    }

    // Update badge on page load
    document.addEventListener('DOMContentLoaded', () => {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        cartBadge.textContent = cart.length;
    });

    // Listen for cart changes from other pages
    window.addEventListener('storage', (e) => {
        if (e.key === 'cart') {
            const cart = JSON.parse(e.newValue) || [];
            cartBadge.textContent = cart.length;
            if (cartSidebar.classList.contains('active')) {
                updateCartSidebarDisplay();
            }
        }
    });

    // Listen for cart changes from same page (menu.php)
    window.addEventListener('cartUpdated', () => {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        cartBadge.textContent = cart.length;
        if (cartSidebar.classList.contains('active')) {
            updateCartSidebarDisplay();
        }
    });
</script>

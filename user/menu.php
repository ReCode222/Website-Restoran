<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Restoran Makan Mania</title>
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="../index.php">
                <img src="../assets/logo.png" alt="Logo Restoran" class="nav-logo">
                <span>Makan Mania</span>
            </a>
        </div>
        <div class="nav-categories">
            <button class="category-btn" data-category="1">Makanan</button>
            <button class="category-btn" data-category="2">Minuman</button>
            <button class="category-btn" data-category="3">Snack</button>
        </div>
        <div class="nav-cart">
            <button class="cart-btn" onclick="showCart()">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-total">Rp 0</span>
            </button>
        </div>
    </nav>

    <main class="menu-container">
        <div class="menu-grid">
            <!-- Menu items will be loaded here -->
        </div>
    </main>

    <!-- Cart Modal -->
    <div class="cart-modal" id="cartModal">
        <div class="cart-content">
            <h2>Pesanan Anda</h2>
            <div class="cart-items">
                <!-- Cart items will be loaded here -->
            </div>
            <div class="cart-total-section">
                <span>Total:</span>
                <span class="cart-total">Rp 0</span>
            </div>
            <div class="cart-buttons">
                <button class="btn-checkout" onclick="checkout()">Checkout</button>
                <button class="btn-close" onclick="hideCart()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        let cart = [];
        let totalPrice = 0;

        // Load menu items
        function loadMenu(categoryId = null) {
            // Update active category button
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('active');
                if (categoryId && btn.dataset.category === categoryId.toString()) {
                    btn.classList.add('active');
                }
            });

            // Load menu items
            const url = categoryId ? 
                `../proses/load-menu.php?category=${categoryId}` : 
                '../proses/load-menu.php';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const menuGrid = document.querySelector('.menu-grid');
                        menuGrid.innerHTML = '';

                        data.data.forEach(item => {
                            const menuItem = document.createElement('div');
                            menuItem.className = 'menu-item';
                            const cartItem = cart.find(ci => ci.id === item.id);
                            const quantity = cartItem ? cartItem.quantity : 0;
                            
                            menuItem.innerHTML = `
                                <img src="${item.image_url}" alt="${item.name}">
                                <div class="menu-info">
                                    <h3>${item.name}</h3>
                                    <p>${item.description}</p>
                                    <div class="menu-price">
                                        <span class="price-tag">${item.price_formatted}</span>
                                        ${quantity > 0 ? `
                                            <div class="quantity-controls">
                                                <button onclick="decreaseMenuQuantity(${item.id})">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span>${quantity}</span>
                                                <button onclick="increaseMenuQuantity(${item.id})">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        ` : `
                                            <button class="add-to-cart-btn" onclick="addToCart(${JSON.stringify(item).replace(/"/g, '&quot;')})">
                                                <i class="fas fa-shopping-cart"></i>
                                                Tambahkan ke Keranjang
                                            </button>
                                        `}
                                    </div>
                                </div>
                            `;
                            menuGrid.appendChild(menuItem);
                        });
                    }
                });
        }

        // Add item to cart
        function addToCart(item) {
            const existingItem = cart.find(cartItem => cartItem.id === item.id);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    ...item,
                    quantity: 1
                });
            }

            updateCartTotal();
            loadMenu(document.querySelector('.category-btn.active')?.dataset.category || 1);
        }

        // Update cart total
        function updateCartTotal() {
            totalPrice = cart.reduce((total, item) => {
                return total + (item.price * item.quantity);
            }, 0);

            const formattedTotal = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(totalPrice);

            document.querySelectorAll('.cart-total').forEach(el => {
                el.textContent = formattedTotal;
            });
        }

        // Show cart modal
        function showCart() {
            const cartItems = document.querySelector('.cart-items');
            cartItems.innerHTML = '';

            cart.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <div class="cart-item-info">
                        <span class="cart-item-name">${item.name}</span>
                        <div class="cart-item-quantity">
                            <button onclick="decreaseQuantity(${item.id})">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span>${item.quantity}</span>
                            <button onclick="increaseQuantity(${item.id})">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="delete-btn" onclick="removeFromCart(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <span class="cart-item-price">${new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }).format(item.price * item.quantity)}</span>
                `;
                cartItems.appendChild(cartItem);
            });

            document.getElementById('cartModal').style.display = 'flex';
        }

        // Hide cart modal
        function hideCart() {
            document.getElementById('cartModal').style.display = 'none';
        }

        // Checkout function
        function checkout() {
            if (cart.length === 0) {
                alert('Keranjang belanja masih kosong!');
                return;
            }

            // Kirim data pesanan ke server
            fetch('../proses/process-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart: cart,
                    totalPrice: totalPrice
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                if (data.status === 'success') {
                    // Menggunakan id bukan orderNumber
                    window.location.href = '../index.php?id=' + data.orderId;
                } else {
                    alert('Terjadi kesalahan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pesanan');
            });
        }

        // Initial load
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', () => loadMenu(btn.dataset.category));
        });
        loadMenu(1); // Load makanan by default

        // Tambah fungsi untuk mengurangi quantity
        function decreaseQuantity(itemId) {
            const item = cart.find(item => item.id === itemId);
            if (item) {
                item.quantity--;
                if (item.quantity <= 0) {
                    removeFromCart(itemId);
                } else {
                    updateCartTotal();
                    showCart(); // Refresh tampilan cart
                }
            }
        }

        // Tambah fungsi untuk menambah quantity
        function increaseQuantity(itemId) {
            const item = cart.find(item => item.id === itemId);
            if (item) {
                item.quantity++;
                updateCartTotal();
                showCart(); // Refresh tampilan cart
            }
        }

        // Tambah fungsi untuk menghapus item
        function removeFromCart(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            updateCartTotal();
            showCart(); // Refresh tampilan cart
        }

        // Add new functions for menu quantity controls
        function increaseMenuQuantity(itemId) {
            const item = cart.find(item => item.id === itemId);
            if (item) {
                item.quantity++;
                updateCartTotal();
                loadMenu(document.querySelector('.category-btn.active')?.dataset.category || 1);
            }
        }

        function decreaseMenuQuantity(itemId) {
            const item = cart.find(item => item.id === itemId);
            if (item) {
                item.quantity--;
                if (item.quantity <= 0) {
                    cart = cart.filter(item => item.id !== itemId);
                }
                updateCartTotal();
                loadMenu(document.querySelector('.category-btn.active')?.dataset.category || 1);
            }
        }
    </script>
    
    <a href="../index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>
</body>
</html>

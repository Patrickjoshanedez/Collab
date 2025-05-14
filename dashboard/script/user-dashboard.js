document.addEventListener("DOMContentLoaded", function () {
    // Fetch products and render them
    fetchProducts();

    function fetchProducts() {
        fetch("user-dashboard-validate.php")
            .then((response) => response.json())
            .then((products) => {
                renderProducts(products);
            })
            .catch((error) => {
                console.error("Error fetching products:", error);
            });
    }

    function renderProducts(products) {
        const productGrid = document.getElementById("productGrid");
        productGrid.innerHTML = ""; // Clear existing products

        products.forEach((product) => {
            const productCard = document.createElement("a");
            productCard.href = `product_detail.php?id=${product.id}`;
            productCard.className =
                "product-card bg-gray-200 dark:bg-gray-600 p-4 rounded flex flex-col items-center";
            productCard.dataset.category = product.category;

            productCard.innerHTML = `
                <img src="/Collab/assets/images/${product.image}" class="h-24 object-contain mb-2" alt="${product.name}">
                <span class="font-semibold">${product.name}</span>
                <span class="mt-1">₱${parseFloat(product.price).toFixed(2)}</span>
                <div class="flex items-center mt-2">
                    ${renderStars(product.avg_rating)}
                    <span class="ml-2 text-gray-700">${product.avg_rating} / 5</span>
                    <span class="ml-4 text-sm text-gray-600">(${product.review_count} reviews)</span>
                </div>
            `;

            productGrid.appendChild(productCard);
        });
    }

    function renderStars(avgRating) {
        let stars = "";
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(avgRating)) {
                stars += '<span class="text-yellow-500 text-xl">&#9733;</span>'; // Full star
            } else {
                stars += '<span class="text-gray-300 text-xl">&#9733;</span>'; // Empty star
            }
        }
        return stars;
    }
});
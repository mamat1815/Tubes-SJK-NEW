<?php
session_start();
require_once 'config/config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = $_POST['query'] ?? '';
    $type = $_POST['type'] ?? 'all';
    $minPrice = $_POST['min_price'] ?? 0;
    $maxPrice = $_POST['max_price'] ?? 1000000;
    $minRating = $_POST['min_rating'] ?? 0;
  
    $products = [];
    $sellers = [];

    if ($type === 'product' || $type === 'all') {
        $productQuery = "SELECT p.productId, p.name, p.price, p.rate, MIN(pi.imagePath) as imagePath 
                         FROM products p
                         LEFT JOIN product_images pi ON p.productId = pi.productId
                         WHERE p.price BETWEEN :minPrice AND :maxPrice 
                         AND p.rate >= :minRating
                         AND (:query = '' OR p.name LIKE :query)
                         GROUP BY p.productId
                         LIMIT 10";
        $stmtProduct = $pdo->prepare($productQuery);
        $stmtProduct->execute([
            'query' => $query ? "%$query%" : '',
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'minRating' => $minRating
        ]);
        $products = $stmtProduct->fetchAll();
    }
    
    if ($type === 'service' || $type === 'all') {
        $sellerQuery = "SELECT s.sellerId, u.fullName, s.rate, s.img 
                        FROM sellers s
                        JOIN users u ON s.userId = u.id
                        WHERE s.rate >= :minRating
                        AND (:query = '' OR u.fullName LIKE :query)
                        LIMIT 10";
        $stmtSeller = $pdo->prepare($sellerQuery);
        $stmtSeller->execute([
            'query' => $query ? "%$query%" : '',
            'minRating' => $minRating
        ]);
        $sellers = $stmtSeller->fetchAll();
    }

    if (empty($products) && empty($sellers)) {
        echo '<p class="text-center mt-4">No results found.</p>';
        exit;
    }

    if (!empty($products)) {
        echo '<h4>Products</h4><div class="row row-cols-1 row-cols-md-3 g-4">';
        foreach ($products as $product) {
            echo '<div class="col">
                    <a href="product-detail.php?id=' . htmlspecialchars($product['productId']) . '" class="text-decoration-none">
                        <div class="card h-100">
                            <img src="' . htmlspecialchars($product['imagePath'] ?? 'https://via.placeholder.com/150') . '" class="card-img-top" alt="Product Image">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($product['name']) . '</h5>
                                <p class="card-text">
                                    <strong>Price:</strong> Rp' . number_format($product['price'], 0, ',', '.') . '<br>
                                    <strong>Rating:</strong> ' . $product['rate'] . ' ⭐
                                </p>
                            </div>
                        </div>
                    </a>
                  </div>';
        }
        echo '</div>';
    }

    if (!empty($sellers)) {
        echo '<h4>Top Sellers</h4><div class="row row-cols-1 row-cols-md-3 g-4">';
        foreach ($sellers as $seller) {
            echo '<div class="col">
                    <a href="seller-detail.php?id=' . htmlspecialchars($seller['sellerId']) . '" class="text-decoration-none">
                        <div class="card h-100">
                            <img src="' . htmlspecialchars($seller['img'] ?? 'https://via.placeholder.com/150') . '" class="card-img-top" alt="Seller Image">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($seller['fullName']) . '</h5>
                                <p class="card-text">
                                    <strong>Rating:</strong> ' . number_format($seller['rate'], 1) . ' ⭐
                                </p>
                            </div>
                        </div>
                    </a>
                  </div>';
        }
        echo '</div>';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achats</title>
 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"  crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/0444f7d0d8.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   
</head>
<body>
    <?php require 'assets/components/navbar.php';?>

    <div class="container-fluid p-3">

        <!-- Jumbotron -->
        <div class="row ms-3 me-3">
            <div class="col-12 jb mx-auto mt-2 rounded p-5 jb d-flex flex-column justify-content-center align-items-center" >
                <h1 class="text-center">
                    We connect people to <br> bring projects to life
                </h1>
                <h5 class="text-center">
                    Find high quality of products and talent <br> what we provide
                </h5>

                <div class="input-group rounded mt-4">
                    <input type="text" class="form-control rounded" id="liveSearch" placeholder="Search..." aria-label="Search" aria-describedby="search-button">
                </div>

                <!-- Filters Section -->
                <div class="accordion mt-3 w-100 d-md-none" id="filterAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFilters">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="true" aria-controls="collapseFilters">
                        Filters
                            </button>
                        </h2>
                        <div id="collapseFilters" class="accordion-collapse collapse gap-3" aria-labelledby="headingFilters" data-bs-parent="#filterAccordion">
                            <div class="accordion-body gap-3">
                                <select class="form-select mb-3" id="searchType" name="searchTypeMobile">
                                    <option value="all" selected>All</option>
                                    <option value="product">Products</option>
                                    <option value="service">Services</option>
                                </select>
                                <input type="number" class="form-control mb-3" id="minPrice" placeholder="Min Price">
                                <input type="number" class="form-control mb-3" id="maxPrice" placeholder="Max Price">
                                <input type="number" class="form-control mb-3" id="minRating" placeholder="Min Rating">
                            </div>
                        </div>
                        
                    </div>
                </div>

                <div class="d-none d-md-flex mt-3 w-100 justify-content-between gap-3">
                    <select class="form-select" id="searchType" name="searchTypeDesktop">
                        <option value="all" selected>All</option>
                        <option value="product">Products</option>
                        <option value="service">Services</option>
                    </select>
                    <input type="number" class="form-control" id="minPrice" placeholder="Min Price">
                    <input type="number" class="form-control" id="maxPrice" placeholder="Max Price">
                    <input type="number" class="form-control" id="minRating" placeholder="Min Rating">
                </div>
            </div>

                <!-- Inline Filters for Larger Screens -->

        </div>

        <!-- Top Sellers -->
        <?php require 'assets/components/landing.php';?>
        <div id="searchResults" class="mt-4 ms-5 me-5"></div>

    </div>

    <script>
        $(document).ready(function() {
            $('#searchType').on('change', performSearch);

            function getActiveFilter() {
                let activeFilter = 'all'; // Default ke "all"
                $('select[name^="searchType"]:visible').each(function() {
                    activeFilter = $(this).val();
                });
                return activeFilter;
            }

            function performSearch() {
                let query = $('#liveSearch').val();
                let type = getActiveFilter(); // Gunakan filter yang aktif
                let minPrice = $('#minPrice').val() || 0;
                let maxPrice = $('#maxPrice').val() || 1000000;
                let minRating = $('#minRating').val() || 0;

                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { 
                        query: query, 
                        type: type, 
                        min_price: minPrice, 
                        max_price: maxPrice, 
                        min_rating: minRating 
                    },
                    success: function(data) {
                        $('#searchResults').html(data).fadeIn();
                        $('#top-sellers').fadeOut();
                        $('#products').fadeOut();
                    }
                });
            }

            $('#liveSearch, #searchType, #minPrice, #maxPrice, #minRating').on('input change', performSearch);


            $(document).on('click', function(e) {
                if ($(e.target).is('#home')) {
                    window.location.href = 'index.php';
                } else if (!$(e.target).closest('#liveSearch, #searchResults').length) {
                    $('#searchResults').fadeOut();
                    $('#top-sellers').fadeIn();
                    $('#products').fadeIn();
                }
            });
        });
    </script><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>


</body>
</html>

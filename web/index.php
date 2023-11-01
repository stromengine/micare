<?php
switch (@parse_url($_SERVER['REQUEST_URI'])['path']) {
    case '/':
        require 'index.php';
        break;
    case '/login':
        require 'services/login.php';
        break;
    case '/getCrpSale':
        require 'services/getCrpSale.php';
        break;
    case '/getCrpsBySeniorCrp':
        require 'services/getCrpsBySeniorCrp.php';
        break;
    case '/getFollowups':
        require 'services/getFollowups.php';
        break;
    case '/getMwras':
        require 'services/getMwras.php';
        break;
    case '/getProducts':
        require 'services/getProducts.php';
        break;
    case (strpos($_SERVER['REQUEST_URI'], '/getProductsByDistrict.php') === 0):
        // Handle 'getProductsByDistrict.php?district_id=' here
        // You can extract the 'district_id' query parameter from the request and include the file.
        // For example, using $_GET['district_id']
        // Make sure to validate and sanitize user input.
        require 'services/getProductsByDistrict.php';
        break;
    case '/getUserProfile':
        require 'services/getUserProfile.php';
        break;
    default:
        http_response_code(404);
        exit('Not Found');
}

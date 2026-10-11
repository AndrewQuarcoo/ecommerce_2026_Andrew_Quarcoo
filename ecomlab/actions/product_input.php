<?php
/**
 * product_input.php — shared input handling for the two product Actions.
 *
 * add_product_action.php and update_product_action.php validate exactly the
 * same seven fields against exactly the same DB column limits, so the rules
 * live here once instead of being copy-pasted and drifting apart.
 *
 * This is Action-layer code: reading $_POST is an Action's job. It performs
 * no SQL and prints nothing.
 *
 * It is an include, not an endpoint. actions/ has to stay web-reachable
 * because the forms POST into it, so this file refuses to run when it is
 * requested directly rather than included.
 */

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    http_response_code(404);
    exit;
}

/**
 * Read, sanitise and validate the product form fields (everything except the
 * image, which the two actions treat differently).
 *
 * @return array ['errors' => string[], 'old' => array, plus one key per field]
 */
function read_product_input()
{
    $errors = [];

    $cat      = filter_input(INPUT_POST, 'product_cat',   FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $brand    = filter_input(INPUT_POST, 'product_brand', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $title    = trim(strip_tags($_POST['product_title'] ?? ''));
    $priceRaw = trim($_POST['product_price'] ?? '');
    $desc     = trim(strip_tags($_POST['product_desc'] ?? ''));
    $keywords = trim(strip_tags($_POST['product_keywords'] ?? ''));

    if (!$cat) {
        $errors[] = 'Please choose a category.';
    }
    if (!$brand) {
        $errors[] = 'Please choose a brand.';
    }
    // products.product_title is VARCHAR(200).
    if ($title === '' || mb_strlen($title) > 200) {
        $errors[] = 'Product title is required (max 200 characters).';
    }
    // product_price is DECIMAL(10,2): at most 99,999,999.99 and never negative.
    if ($priceRaw === '' || !is_numeric($priceRaw)) {
        $errors[] = 'Price is required and must be a number.';
    } elseif ((float) $priceRaw < 0) {
        $errors[] = 'Price cannot be negative.';
    } elseif ((float) $priceRaw > 99999999.99) {
        $errors[] = 'Price is too large.';
    }
    // product_desc is VARCHAR(500), product_keywords VARCHAR(100).
    if (mb_strlen($desc) > 500) {
        $errors[] = 'Description is too long (max 500 characters).';
    }
    if (mb_strlen($keywords) > 100) {
        $errors[] = 'Keywords are too long (max 100 characters).';
    }

    return [
        'errors'   => $errors,
        'cat'      => $cat,
        'brand'    => $brand,
        'title'    => $title,
        'price'    => is_numeric($priceRaw) ? round((float) $priceRaw, 2) : 0,
        'desc'     => $desc === '' ? null : $desc,
        'keywords' => $keywords === '' ? null : $keywords,
        // Echoed back into the form so a rejected submission is not retyped.
        'old'      => [
            'product_cat'      => $cat,
            'product_brand'    => $brand,
            'product_title'    => $title,
            'product_price'    => $priceRaw,
            'product_desc'     => $desc,
            'product_keywords' => $keywords,
        ],
    ];
}

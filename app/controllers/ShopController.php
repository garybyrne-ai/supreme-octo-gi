<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\CodeShopRepository;

final class ShopController extends Controller
{
    private CodeShopRepository $shop;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->shop = new CodeShopRepository();
    }

    public function index(): void
    {
        $this->render('pages/code-shop', [
            'title' => 'Digital Code Shop | Crest Web Media',
            'metaDescription' => 'Buy secure PHP website packages, WordPress plugins, boilerplates and custom UI snippets from Crest Web Media.',
            'products' => $this->shop->products(),
        ]);
    }

    public function show(string $slug): void
    {
        $product = $this->shop->findBySlug($slug);
        if ($product === null) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Product Not Found']);
            return;
        }

        $this->render('pages/code-product', [
            'title' => $product['title'] . ' | Digital Code Shop',
            'metaDescription' => $product['summary'],
            'product' => $product,
            'csrf' => Security::csrfToken(),
            'snippetSrcdoc' => $product['product_type'] === 'snippet' ? $this->shop->safeSnippetSrcdoc($product) : '',
        ]);
    }
}

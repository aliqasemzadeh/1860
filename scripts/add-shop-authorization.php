<?php

declare(strict_types=1);

$base = dirname(__DIR__);

function patch(string $path, array $replacements): void
{
    global $base;

    $fullPath = str_starts_with($path, $base)
        ? $path
        : $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);

    $content = file_get_contents($fullPath);
    $original = $content;

    foreach ($replacements as $search => $replace) {
        if (!str_contains($content, $search)) {
            if (str_contains($content, $replace)) {
                continue;
            }
            throw new RuntimeException("Search not found in {$path}:\n" . substr($search, 0, 120));
        }
        $content = str_replace($search, $replace, $content);
    }

    if ($content === $original) {
        echo "SKIP (already patched): {$path}\n";
        return;
    }

    file_put_contents($fullPath, $content);
    echo "OK: {$path}\n";
}

function auth(string $permission): string
{
    return "        \$this->authorize('{$permission}');\n\n";
}

// Dashboard
patch('app/Livewire/Panel/Shop/Dashboard/Index.php', [
    "    public function render()\n    {\n        return view('livewire.panel.shop.dashboard.index');" =>
        "    public function render()\n    {\n" . auth('shop_dashboard_index') . "        return view('livewire.panel.shop.dashboard.index');",
]);

// Product Index
patch('app/Livewire/Panel/Shop/Product/Index.php', [
    "    public function delete(int \$id): void\n    {\n        \$product" =>
        "    public function delete(int \$id): void\n    {\n" . auth('shop_product_delete') . "        \$product",
    "    public function render()\n    {\n        return view('livewire.panel.shop.product.index');" =>
        "    public function render()\n    {\n" . auth('shop_product_index') . "        return view('livewire.panel.shop.product.index');",
]);

// Product Create
patch('app/Livewire/Panel/Shop/Product/Create.php', [
    "    public function create(): void\n    {\n        \$validated" =>
        "    public function create(): void\n    {\n" . auth('shop_product_create') . "        \$validated",
]);

// Product Edit
patch('app/Livewire/Panel/Shop/Product/Edit.php', [
    "    public function edit(): void\n    {\n        if (! isset(\$this->product))" =>
        "    public function edit(): void\n    {\n" . auth('shop_product_edit') . "        if (! isset(\$this->product))",
    "    public function removeBackground(): void\n    {\n        if (! isset(\$this->product)" =>
        "    public function removeBackground(): void\n    {\n" . auth('shop_product_edit') . "        if (! isset(\$this->product)",
]);

// Product Attributes
patch('app/Livewire/Panel/Shop/Product/Attributes.php', [
    "    public function mount(int \$id): void\n    {\n        \$this->productId = \$id;" =>
        "    public function mount(int \$id): void\n    {\n" . auth('shop_product_attributes_index') . "        \$this->productId = \$id;",
    "    public function save(): void\n    {\n        \$this->product = Product::with('category.attributes')" =>
        "    public function save(): void\n    {\n" . auth('shop_product_attributes_edit') . "        \$this->product = Product::with('category.attributes')",
    "    public function render()\n    {\n        \$attributes = \$this->product" =>
        "    public function render()\n    {\n" . auth('shop_product_attributes_index') . "        \$attributes = \$this->product",
]);

// Product Colors
$colorsManage = auth('shop_product_colors_manage');
patch('app/Livewire/Panel/Shop/Product/Colors.php', [
    "    public function addColor(): void\n    {\n        if (! \$this->product" =>
        "    public function addColor(): void\n    {\n{$colorsManage}        if (! \$this->product",
    "    public function removeColor(int \$colorId): void\n    {\n        if (! \$this->product" =>
        "    public function removeColor(int \$colorId): void\n    {\n{$colorsManage}        if (! \$this->product",
]);

// Product Warranties
$warrantiesManage = auth('shop_product_warranties_manage');
patch('app/Livewire/Panel/Shop/Product/Warranties.php', [
    "    public function addWarranty(): void\n    {\n        if (! \$this->product" =>
        "    public function addWarranty(): void\n    {\n{$warrantiesManage}        if (! \$this->product",
    "    public function removeWarranty(int \$warrantyId): void\n    {\n        if (! \$this->product" =>
        "    public function removeWarranty(int \$warrantyId): void\n    {\n{$warrantiesManage}        if (! \$this->product",
]);

// Price Fetchers
$priceFetchersManage = auth('shop_product_price_fetchers_manage');
patch('app/Livewire/Panel/Shop/Product/PriceFetchers.php', [
    "    public function addPriceFetcher(): void\n    {\n        if (! \$this->product" =>
        "    public function addPriceFetcher(): void\n    {\n{$priceFetchersManage}        if (! \$this->product",
    "    public function removePriceFetcher(int \$priceFetcherId): void\n    {\n        if (! \$this->product" =>
        "    public function removePriceFetcher(int \$priceFetcherId): void\n    {\n{$priceFetchersManage}        if (! \$this->product",
    "    public function fetchPrice(int \$priceFetcherId): void\n    {\n        if (! \$this->product" =>
        "    public function fetchPrice(int \$priceFetcherId): void\n    {\n{$priceFetchersManage}        if (! \$this->product",
]);

// Product Images
$imagesManage = auth('shop_product_images_manage');
patch('app/Livewire/Panel/Shop/Product/ProductImages.php', [
    "    public function openWizard(): void\n    {\n        if (\$this->product) {" =>
        "    public function openWizard(): void\n    {\n{$imagesManage}        if (\$this->product) {",
    "    public function removeImage(int \$index): void\n    {" =>
        "    public function removeImage(int \$index): void\n    {\n{$imagesManage}",
    "    public function removeProductImage(int \$imageId): void\n    {" =>
        "    public function removeProductImage(int \$imageId): void\n    {\n{$imagesManage}",
    "    public function removeBackground(int \$imageId): void\n    {" =>
        "    public function removeBackground(int \$imageId): void\n    {\n{$imagesManage}",
    "    public function save(): void\n    {" =>
        "    public function save(): void\n    {\n{$imagesManage}",
]);

// Product Wizard
$wizardCreate = auth('shop_product_wizard_create');
patch('app/Livewire/Panel/Shop/Product/ProductWizard.php', [
    "    public function nextStep(): void\n    {\n        if (\$this->step === 1) {" =>
        "    public function nextStep(): void\n    {\n{$wizardCreate}        if (\$this->step === 1) {",
    "    public function fetchProductInfo(): void\n    {\n        \$this->is_fetching = true;" =>
        "    public function fetchProductInfo(): void\n    {\n{$wizardCreate}        \$this->is_fetching = true;",
    "    public function createProduct(): void\n    {\n        \$this->validate([" =>
        "    public function createProduct(): void\n    {\n{$wizardCreate}        \$this->validate([",
]);

// Product Image Wizard
$imageWizard = auth('shop_product_image_wizard');
patch('app/Livewire/Panel/Shop/Product/ProductImageWizard.php', [
    "    public function fetchImages(): void\n    {" =>
        "    public function fetchImages(): void\n    {\n{$imageWizard}",
    "    public function updateImageName(string \$imageId, string \$name): void\n    {" =>
        "    public function updateImageName(string \$imageId, string \$name): void\n    {\n{$imageWizard}",
    "    public function removeImage(string \$imageId): void\n    {" =>
        "    public function removeImage(string \$imageId): void\n    {\n{$imageWizard}",
    "    public function save(): void\n    {" =>
        "    public function save(): void\n    {\n{$imageWizard}",
]);

// Create Product Image Wizard
$productCreate = auth('shop_product_create');
patch('app/Livewire/Panel/Shop/Product/CreateProductImageWizard.php', [
    "    public function fetchImages(): void\n    {\n        \$this->validate([" =>
        "    public function fetchImages(): void\n    {\n{$productCreate}        \$this->validate([",
    "    public function confirmSelection(): void\n    {\n        if (! \$this->selectedImageUrl) {" =>
        "    public function confirmSelection(): void\n    {\n{$productCreate}        if (! \$this->selectedImageUrl) {",
]);

// Product Pricing
patch('app/Livewire/Panel/Shop/Product/Pricing/Index.php', [
    "    public function mount(int \$productId)\n    {" =>
        "    public function mount(int \$productId)\n    {\n" . auth('shop_product_pricing_index'),
    "    public function render()\n    {\n        return view('livewire.panel.shop.product.pricing.index');" =>
        "    public function render()\n    {\n" . auth('shop_product_pricing_index') . "        return view('livewire.panel.shop.product.pricing.index');",
]);

patch('app/Livewire/Panel/Shop/Product/Pricing/Create.php', [
    "    public function create()\n    {" =>
        "    public function create()\n    {\n" . auth('shop_product_pricing_create'),
]);

patch('app/Livewire/Panel/Shop/Product/Pricing/Edit.php', [
    "    public function update()\n    {" =>
        "    public function update()\n    {\n" . auth('shop_product_pricing_edit'),
]);

patch('app/Livewire/Panel/Shop/Product/Pricing/History.php', [
    "    public function mount(int \$productId)\n    {" =>
        "    public function mount(int \$productId)\n    {\n" . auth('shop_product_pricing_history'),
    "    public function render()\n    {\n        return view('livewire.panel.shop.product.pricing.history');" =>
        "    public function render()\n    {\n" . auth('shop_product_pricing_history') . "        return view('livewire.panel.shop.product.pricing.history');",
]);

// Orders
patch('app/Livewire/Panel/Shop/Order/Index.php', [
    "    public function render()\n    {\n        return view('livewire.panel.shop.order.index');" =>
        "    public function render()\n    {\n" . auth('shop_order_index') . "        return view('livewire.panel.shop.order.index');",
]);

patch('app/Livewire/Panel/Shop/Order/View.php', [
    "    public function assignData(int \$id): void\n    {\n        \$this->order = Order::query()" =>
        "    public function assignData(int \$id): void\n    {\n" . auth('shop_order_view') . "        \$this->order = Order::query()",
    "    public function render()\n    {\n        return view('livewire.panel.shop.order.view');" =>
        "    public function render()\n    {\n" . auth('shop_order_view') . "        return view('livewire.panel.shop.order.view');",
]);

// Setting Management CRUD helpers
function crudIndex(string $entity, string $permissionPrefix): void
{
    patch("app/Livewire/Panel/Shop/SettingManagement/{$entity}/Index.php", [
        "    public function delete(int \$id): void\n    {\n        \$" =>
            "    public function delete(int \$id): void\n    {\n" . auth("{$permissionPrefix}_delete") . "        \$",
        "    public function render()\n    {\n        return view(" =>
            "    public function render()\n    {\n" . auth("{$permissionPrefix}_index") . "        return view(",
    ]);
}

function crudCreate(string $entity, string $permissionPrefix): void
{
    patch("app/Livewire/Panel/Shop/SettingManagement/{$entity}/Create.php", [
        "    public function create(): void\n    {\n        \$validated" =>
            "    public function create(): void\n    {\n" . auth("{$permissionPrefix}_create") . "        \$validated",
    ]);
}

function crudEdit(string $entity, string $permissionPrefix): void
{
    patch("app/Livewire/Panel/Shop/SettingManagement/{$entity}/Edit.php", [
        "    public function edit(): void\n    {\n        if (! isset(\$this->" =>
            "    public function edit(): void\n    {\n" . auth("{$permissionPrefix}_edit") . "        if (! isset(\$this->",
    ]);
}

foreach (['Category' => 'shop_setting_category', 'Brand' => 'shop_setting_brand', 'Color' => 'shop_setting_color', 'Warranty' => 'shop_setting_warranty', 'Unit' => 'shop_setting_unit', 'AttributeGroup' => 'shop_setting_attribute_group', 'Attribute' => 'shop_setting_attribute'] as $entity => $prefix) {
    crudIndex($entity, $prefix);
    crudCreate($entity, $prefix);
    crudEdit($entity, $prefix);
}

// Category Attributes
patch('app/Livewire/Panel/Shop/SettingManagement/Category/Attributes.php', [
    "    public function mount(int \$id): void\n    {\n        \$this->categoryId = \$id;" =>
        "    public function mount(int \$id): void\n    {\n" . auth('shop_setting_category_attributes') . "        \$this->categoryId = \$id;",
    "    public function save(): void\n    {\n        \$this->category = Category::findOrFail(\$this->categoryId);" =>
        "    public function save(): void\n    {\n" . auth('shop_setting_category_attributes') . "        \$this->category = Category::findOrFail(\$this->categoryId);",
    "    public function toggleAttribute(int \$attributeId): void\n    {\n        if (in_array(\$attributeId, \$this->selectedAttributes)) {" =>
        "    public function toggleAttribute(int \$attributeId): void\n    {\n" . auth('shop_setting_category_attributes') . "        if (in_array(\$attributeId, \$this->selectedAttributes)) {",
    "    public function render()\n    {\n        \$allAttributes = Attribute::query()" =>
        "    public function render()\n    {\n" . auth('shop_setting_category_attributes') . "        \$allAttributes = Attribute::query()",
]);

// Attribute Option
patch('app/Livewire/Panel/Shop/SettingManagement/Attribute/Option/Index.php', [
    "    public function mount(int \$attributeId): void\n    {\n        \$this->attributeId = \$attributeId;" =>
        "    public function mount(int \$attributeId): void\n    {\n" . auth('shop_setting_attribute_option_index') . "        \$this->attributeId = \$attributeId;",
    "    public function delete(int \$id): void\n    {\n        \$option" =>
        "    public function delete(int \$id): void\n    {\n" . auth('shop_setting_attribute_option_delete') . "        \$option",
    "    public function render()\n    {\n        return view('livewire.panel.shop.setting-management.attribute.option.index');" =>
        "    public function render()\n    {\n" . auth('shop_setting_attribute_option_index') . "        return view('livewire.panel.shop.setting-management.attribute.option.index');",
]);

patch('app/Livewire/Panel/Shop/SettingManagement/Attribute/Option/Create.php', [
    "    public function create(): void\n    {\n        \$validated" =>
        "    public function create(): void\n    {\n" . auth('shop_setting_attribute_option_create') . "        \$validated",
]);

patch('app/Livewire/Panel/Shop/SettingManagement/Attribute/Option/Edit.php', [
    "    public function edit(): void\n    {\n        if (! isset(\$this->attributeOption))" =>
        "    public function edit(): void\n    {\n" . auth('shop_setting_attribute_option_edit') . "        if (! isset(\$this->attributeOption))",
]);

// Shipping
foreach (['Method' => 'shop_shipping_method', 'Zone' => 'shop_shipping_zone', 'Rate' => 'shop_shipping_rate'] as $entity => $prefix) {
    patch("app/Livewire/Panel/Shop/Shipping/{$entity}/Index.php", [
        "    public function delete(int \$id): void\n    {\n        \$" =>
            "    public function delete(int \$id): void\n    {\n" . auth("{$prefix}_delete") . "        \$",
        "    public function render()\n    {\n        return view(" =>
            "    public function render()\n    {\n" . auth("{$prefix}_index") . "        return view(",
    ]);

    patch("app/Livewire/Panel/Shop/Shipping/{$entity}/Create.php", [
        "    public function create(): void\n    {\n        \$validated" =>
            "    public function create(): void\n    {\n" . auth("{$prefix}_create") . "        \$validated",
    ]);

    patch("app/Livewire/Panel/Shop/Shipping/{$entity}/Edit.php", [
        "    public function edit(): void\n    {\n        if (! isset(\$this->" =>
            "    public function edit(): void\n    {\n" . auth("{$prefix}_edit") . "        if (! isset(\$this->",
    ]);
}

// Administrator Option Index
patch('app/Livewire/Panel/Administrator/SettingManagement/Option/Index.php', [
    "    public function render()\n    {\n        return view('livewire.panel.administrator.setting-management.option.index');" =>
        "    public function render()\n    {\n" . auth('administrator_setting_option_index') . "        return view('livewire.panel.administrator.setting-management.option.index');",
]);

echo "All PHP patches applied.\n";

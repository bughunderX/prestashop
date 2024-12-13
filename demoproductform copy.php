<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

use PrestaShop\Module\DemoProductForm\Entity\CustomCombination;
use PrestaShop\Module\DemoProductForm\Entity\CustomProduct;
use PrestaShop\Module\DemoProductForm\Entity\ShippingRule;
use PrestaShop\Module\DemoProductForm\Form\Modifier\CombinationFormModifier;
use PrestaShop\Module\DemoProductForm\Form\Modifier\ProductFormModifier;
use PrestaShop\Module\DemoProductForm\Install\Installer;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\ValueObject\CombinationId;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use Symfony\Component\Templating\EngineInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class DemoProductForm extends Module
{
    public function __construct()
    {
        $this->name = 'demoproductform';
        $this->author = 'PrestaShop';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->trans('DemoProductForm', [], 'Modules.Demoproductform.Config');
        $this->description = $this->trans('DemoProductForm module description', [], 'Modules.Demoproductform.Config');
    }

    /**
     * @return bool
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $installer = new Installer();
        $this -> createShippingRulesTable();
        $this->registerHook('displayHeader');
        $this->registerHook('actionCarrierProcess');
        return $installer->install($this);
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        $installer = new Installer();
        $this -> uninstallDatabase();
        return $installer->uninstall($this);
    }
    private function createShippingRulesTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'shipping_rules` (
            `id_shipping_rule` INT(11) NOT NULL AUTO_INCREMENT,
            `id_product` INT(11) UNSIGNED NOT NULL,
            `shipping_country` VARCHAR(2) NOT NULL,
            `shipping_start_rate` DECIMAL(10, 2) NOT NULL,
            `shipping_extra_rate` DECIMAL(10, 2) NOT NULL,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_shipping_rule`),
            UNIQUE KEY `uniq_id_product` (`id_product`),  -- Add unique constraint
            FOREIGN KEY (`id_product`) REFERENCES `' . _DB_PREFIX_ . 'product` (`id_product`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';
    
        return Db::getInstance()->execute($sql);
    }
        private function uninstallDatabase()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'shipping_rules`';
        return Db::getInstance()->execute($sql);
    }
    /**
     * @see https://devdocs.prestashop.com/8/modules/creation/module-translation/new-system/#translating-your-module
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }
    /**
     * Hook for saving shipping rates when the product is saved.
     *
     * @param array $params
     */
    public function hookActionProductSave($params)
    {
        // var_dump('Testing hookActionProductSave trigger');
        PrestaShopLogger::addLog('Product save action triggered.');
        // Retrieve the product object from the params
        $product = $params['product'];

        // Get the custom shipping data (use Tools::getValue to fetch form values)
        $shippingCountry = Tools::getValue('shipping_country');
        $shippingStartRate = Tools::getValue('shipping_start_rate');
        $shippingExtraRate = Tools::getValue('shipping_extra_rate');
        $productData = Tools::getValue('product');
        // var_dump($product);
        $shippingCountry = $productData['shipping']['shipping_country'];
        $shippingStartRate = $productData['shipping']['shipping_start_rate'];
        $shippingExtraRate = $productData['shipping']['shipping_extra_rate'];
        // Debugging: Log shipping fields
        PrestaShopLogger::addLog('Shipping Country: ' . $shippingCountry);
        PrestaShopLogger::addLog('Shipping Start Rate: ' . $shippingStartRate);
        PrestaShopLogger::addLog('Shipping Extra Rate: ' . $shippingExtraRate);
        if (!$shippingCountry || !$shippingStartRate || !$shippingExtraRate) {
            return; // Skip saving if the data is missing
        }

        // Save the shipping data to the database
        $this->saveShippingRules($product, $shippingCountry, $shippingStartRate, $shippingExtraRate);
    }

    /**
     * Save shipping rules to the database.
     *
     * @param Product $product
     * @param string $shippingCountry
     * @param float $shippingStartRate
     * @param float $shippingExtraRate
     */
    private function saveShippingRules(Product $product, $shippingCountry, $shippingStartRate, $shippingExtraRate)
    {
        $idProduct = (int) $product->id;
        $db = Db::getInstance();
        
        // Check if a row with the same id_product and shipping_country exists
        $existingRule = $db->getRow('
            SELECT `id_shipping_rule`
            FROM `' . _DB_PREFIX_ . 'shipping_rules`
            WHERE `id_product` = ' . (int) $idProduct . ' AND `shipping_country` = "' . pSQL($shippingCountry) . '"
        ');
    
        if ($existingRule) {
            // Row exists, perform an UPDATE
            $db->update('shipping_rules', [
                'shipping_start_rate' => (float) $shippingStartRate,
                'shipping_extra_rate' => (float) $shippingExtraRate,
                'date_upd' => date('Y-m-d H:i:s')
            ], 'id_shipping_rule = ' . (int) $existingRule['id_shipping_rule']);
        } else {
            // Row doesn't exist, perform an INSERT
            $db->insert('shipping_rules', [
                'id_product' => $idProduct,
                'shipping_country' => pSQL($shippingCountry),
                'shipping_start_rate' => (float) $shippingStartRate,
                'shipping_extra_rate' => (float) $shippingExtraRate,
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            ]);
        }
    }
    /**
     * Modify product form builder
     *
     * @param array $params
     */
    public function hookActionProductFormBuilderModifier(array $params): void
    {
        
        PrestaShopLogger::addLog('Testing hookActionProductSave trigger');
        /** @var ProductFormModifier $productFormModifier */
        $productFormModifier = $this->get(ProductFormModifier::class);
        $productId = isset($params['id']) ? new ProductId((int) $params['id']) : null;
        if( $productId ){
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'shipping_rules` WHERE `id_product` = ' . $params['id'];
            $shippingRules = Db::getInstance()->executeS($sql);
            $shippingRule = new ShippingRule();
            $shippingRule->hydrate($shippingRules[0]);  // Hydrate the ShippingRule object with the fetched data
            $productFormModifier->modify($productId, $params['form_builder'], $shippingRule);
        }
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        $cart = $params['cart']; // Get cart object
        var_dump('hookDisplayShoppingCartFooter');
        var_dump($cart);
        die();
        // $countryId = (int)$cart->id_address_delivery; // Get the destination address
        // $shippingRules = $this->getShippingRules($cart); // Calculate shipping rules based on cart content
        
        // Add custom shipping fee to the cart summary
        // $this->context->smarty->assign('custom_shipping_fee', $shippingRules['total']);
        
        // return $this->display(__FILE__, 'views/templates/hook/custom_shipping_fee.tpl');
    }

    public function hookDisplayShoppingCart($params)
    {
        $cart = $params['cart']; // Get cart object
        var_dump('hookDisplayShoppingCart');
        var_dump($cart);
        die();
        // $countryId = (int)$cart->id_address_delivery; // Get the destination address
        // $shippingRules = $this->getShippingRules($cart); // Calculate shipping rules based on cart content
        
        // Add custom shipping fee to the cart summary
        // $this->context->smarty->assign('custom_shipping_fee', $shippingRules['total']);
        
        return '<p>Thank you for adding a product to your cart!</p>';
    }
    public function hookActionCarrierProcess($params)
    {
        $cart = $params['cart'];
        var_dump('hookActionCartSave');
        var_dump("RRRRRRRRRRRRRRRRRRRRRR");
        return '<p>Thank you for adding a product to your cart!</p>';
        var_dump($cart); exit;
    }
    public function hookActionCartSave($params)
    {
        $cart = $params['cart']; // Access the cart object
        var_dump('hookActionCartSave');
        var_dump($cart);
        die();

        // You can now access and modify the cart's contents, for example:
        // $this->updateShippingFees($cart);
    }

    public function hookActionCartUpdate($params)
    {
        $cart = $params['cart']; // Access the cart object
        var_dump('hookActionCartUpdate');
        var_dump($cart);
        die();

        // Call a method to update shipping fees
        // $this->updateShippingFees($cart);
    }
    public function hookDisplayHeader($params)
    {
        var_dump('hhhh');
        die();
        // Add a custom JavaScript file to monitor cart updates
        $this->context->controller->addJS($this->_path . 'views/js/cart-monitor.js');
    }
    public function hookDisplayCartModalContent($params)
    {
        $cart = $params['cart'];
        var_dump($cart);
        die();
        return '<p>Thank you for adding a product to your cart!</p>';
    }
    public function hookActionCartUpdateQuantityBefore(array $params)
    {
        die(json_encode([
            'errors' => 'whatever.. due to how the frontend js is done, this won\'t be shown anyways',
            'hasError' => true,
        ]));
    }
    public function hookActionProductUpdate($params) {
        
        $cart = $params['cart'];
        var_dump($cart);
        die();
    }
    /**
     * Hook to display configuration related to the module in the Modules extra tab in product page.
     *
     * @param array $params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminProductsExtra(array $params): string
    {
        $productId = $params['id_product'];
        $customProduct = new CustomProduct($productId);

        /** @var EngineInterface $twig */
        $twig = $this->get('twig');

        return $twig->render('@Modules/demoproductform/views/templates/admin/extra_module.html.twig', [
            'customProduct' => $customProduct,
        ]);
    }

    /**
     * Hook that modifies the combination form structure.
     *
     * @param array $params
     */
    public function hookActionCombinationFormFormBuilderModifier(array $params): void
    {
        /** @var CombinationFormModifier $productFormModifier */
        $productFormModifier = $this->get(CombinationFormModifier::class);
        $combinationId = isset($params['id']) ? new CombinationId((int) $params['id']) : null;

        $productFormModifier->modify($combinationId, $params['form_builder']);
    }

    /**
     * Hook called after form is submitted and combination is updated, custom data is updated here.
     *
     * @param array $params
     */
    public function hookActionAfterUpdateCombinationFormFormHandler(array $params): void
    {
        $combinationId = $params['form_data']['id'];
        $customCombination = new CustomCombination($combinationId);
        $customCombination->custom_field = $params['form_data']['demo_module_custom_field'] ?? '';
        $customCombination->custom_price = $params['form_data']['custom_tab']['custom_price'] ?? 0.0;

        if (empty($customCombination->id)) {
            // If custom is not found it has not been created yet, so we force its ID to match the combination ID
            $customCombination->id = $combinationId;
            $customCombination->force_id = true;
            $customCombination->add();
        } else {
            $customCombination->update();
        }
    }
}

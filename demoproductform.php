<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class DemoProductForm extends Module
{
    public function __construct()
    {
        $this->name = 'demoproductform';
        $this->author = 'Your Name';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->trans('Demo Product Form', [], 'Modules.Demoproductform.Config');
        $this->description = $this->trans('Module for managing shipping rules for products.', [], 'Modules.Demoproductform.Config');
    }

    public function install()
    {
        return parent::install()
            && $this->createShippingRulesTable()
            && $this->registerHook('displayAdminProductsExtra');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->dropShippingRulesTable();
    }

    private function createShippingRulesTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'shipping_rules` (
            `id_product` INT(11) NOT NULL,
            `shipping_country` VARCHAR(2) NOT NULL,
            `shipping_start_rate` DECIMAL(10, 2) NOT NULL,
            `shipping_extra_rate` DECIMAL(10, 2) NOT NULL,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_product`, `shipping_country`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return Db::getInstance()->execute($sql);
    }

    private function dropShippingRulesTable()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'shipping_rules`';
        return Db::getInstance()->execute($sql);
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $productId = $params['id_product'];
        // $customProduct = new CustomProduct($productId);
    
        // Fetch all countries
        $countries = Country::getCountries($this->context->language->id);
        // var_dump($countries);
        // exit;
        $shippingRules = Db::getInstance()->executeS('
            SELECT * FROM `' . _DB_PREFIX_ . 'shipping_rules`
            WHERE `id_product` = ' . $productId
        );

        // $this->context->smarty->assign([
        //     'shippingRules' => $shippingRules,
        //     'updateShippingRuleUrl' => $this->context->link->getAdminLink('AdminShippingRule') . '&ajax=1&action=UpdateShippingRule',
        //     'deleteShippingRuleUrl' => $this->context->link->getAdminLink('AdminShippingRule') . '&ajax=1&action=DeleteShippingRule',
        // ]);

        // // return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/extra_module.html.twig');
        // $twig = $this->get('twig');

        // return $twig->render('@Modules/demoproductform/views/templates/admin/extra_module.html.twig', [
        //     // 'customProduct' => $customProduct,
        //     'shippingRules' => $shippingRules,
        //     'countries' => $countries,
        // ]);
        $this->context->smarty->assign([
            'shippingRules' => $shippingRules,
            'id_product' => $productId,
            'countries' => $countries,
            'addShippingRuleUrl' => $this->context->link->getAdminLink('AdminShippingRule') . '&ajax=1&action=AddShippingRule',
            'updateShippingRuleUrl' => $this->context->link->getAdminLink('AdminShippingRule') . '&ajax=1&action=UpdateShippingRule',
            'deleteShippingRuleUrl' => $this->context->link->getAdminLink('AdminShippingRule') . '&ajax=1&action=DeleteShippingRule',
            'js_file_url' => $this->context->link->getBaseLink() . 'modules/demoproductform/views/assets/js/shipping_rules.js',
        ]);
    
        // Render the .tpl file
        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/extra_module.tpl');
    }
}

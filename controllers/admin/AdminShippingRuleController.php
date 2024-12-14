<?php

class AdminShippingRuleController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    public function postProcess()
    {
        parent::postProcess();

        // Ensure this is an AJAX request
        if (Tools::isSubmit('ajax')) {
            $action = Tools::getValue('action');

            switch ($action) {
                case 'AddShippingRule':
                    $this->addShippingRule();
                    break;

                case 'UpdateShippingRule':
                    $this->updateShippingRule();
                    break;

                case 'DeleteShippingRule':
                    $this->deleteShippingRule();
                    break;

                default:
                    $this->returnJson(['success' => false, 'message' => 'Unknown action']);
            }
        }
    }
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
    private function addShippingRule()
    {
        $data = json_decode(Tools::file_get_contents('php://input'), true);
    
        if (!$data) {
            $this->returnJson(['success' => false, 'message' => 'Invalid data']);
        }
    
        $idProduct = (int)$data['id_product'];
        $country = pSQL($data['country']);
        $startRate = (float)$data['start_rate'];
        $extraRate = (float)$data['extra_rate'];
        
        if (!$idProduct || !$country || !$startRate || !$extraRate) {
            $this->returnJson(['success' => false, 'message' => 'Invalid input data']);
        }
    
        // Check if the row already exists
        $exists = Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'shipping_rules`
            WHERE `id_product` = ' . $idProduct . '
              AND `shipping_country` = "' . $country . '"
        ');
    
        if ($exists) {
            $this->returnJson([
                'success' => false,
                'message' => 'Duplicate rule: this country already exists for the product.',
            ]);
        }
    
        // If not, insert the new rule
        $result = Db::getInstance()->insert('shipping_rules', [
            'id_product' => $idProduct,
            'shipping_country' => $country,
            'shipping_start_rate' => $startRate,
            'shipping_extra_rate' => $extraRate,
        ]);
    
        $this->returnJson([
            'success' => (bool)$result,
            'message' => $result ? 'Rule added successfully' : 'Error adding rule',
        ]);
    }
    
    

    private function updateShippingRule()
    {
        $idProduct = (int)Tools::getValue('id_product');
        $country = pSQL(Tools::getValue('shipping_country'));
        $startRate = (float)Tools::getValue('start_rate');
        $extraRate = (float)Tools::getValue('extra_rate');
        $this->returnJson(['success' => true, 'message' => true ? 'update' : 'Error adding rule update']);

        if (!$idProduct || !$country || !$startRate || !$extraRate) {
            $this->returnJson(['success' => false, 'message' => 'Invalid input data']);
        }

        $result = Db::getInstance()->update('shipping_rules', [
            'shipping_start_rate' => $startRate,
            'shipping_extra_rate' => $extraRate,
        ], '`id_product` = ' . $idProduct . ' AND `shipping_country` = "' . $country . '"');

        $this->returnJson(['success' => (bool)$result, 'message' => $result ? 'Rule updated successfully' : 'Error updating rule']);
    }

    private function deleteShippingRule()
    {
        $data = json_decode(Tools::file_get_contents('php://input'), true);
    
        if (!$data) {
            $this->returnJson(['success' => false, 'message' => 'Invalid data']);
        }
    
        $idProduct = (int)$data['id_product'];
        $country = pSQL($data['shipping_country']);

        if (!$idProduct || !$country) {
            $this->returnJson(['success' => false, 'message' => 'Invalid input data']);
        }

        $result = Db::getInstance()->delete('shipping_rules', '`id_product` = ' . $idProduct . ' AND `shipping_country` = "' . $country . '"');

        $this->returnJson(['success' => (bool)$result, 'message' => $result ? 'Rule deleted successfully' : 'Error deleting rule']);
    }

    private function returnJson($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

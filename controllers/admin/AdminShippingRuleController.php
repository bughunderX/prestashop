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
    private function addShippingRule()
    {
        $data = json_decode(Tools::file_get_contents('php://input'), true);
    
        if (!$data) {
            $this->returnJson(['success' => false, 'message' => 'Invalid data']);
        }
    
        $idProduct = (int)$data['id_product'];
        $country = (int)($data['country']);
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
              AND `id_country` = "' . $country . '"
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
            'id_country' => $country,
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
        $data = json_decode(Tools::file_get_contents('php://input'), true);
    
        if (!$data) {
            $this->returnJson(['success' => false, 'message' => 'Invalid data']);
        }
    
        $idProduct = (int)$data['id_product'];
        $country = (int)($data['shipping_country']);
        $startRate = (float)($data['start_rate']);
        $extraRate = (float)($data['extra_rate']);

        if (!$idProduct || !$country || !$startRate || !$extraRate) {
            $this->returnJson(['success' => false, 'message' => 'Invalid input data']);
        }
        $result = Db::getInstance()->update('shipping_rules', [
            'shipping_start_rate' => $startRate,
            'shipping_extra_rate' => $extraRate,
        ], '`id_product` = ' . $idProduct . ' AND `id_country` = "' . $country . '"');

        $this->returnJson(['success' => (bool)$result, 'message' => $result ? 'Rule updated successfully' : 'Error updating rule']);
    }

    private function deleteShippingRule()
    {
        $data = json_decode(Tools::file_get_contents('php://input'), true);
    
        if (!$data) {
            $this->returnJson(['success' => false, 'message' => 'Invalid data']);
        }
    
        $idProduct = (int)$data['id_product'];
        $country = (int)($data['shipping_country']);

        if (!$idProduct || !$country) {
            $this->returnJson(['success' => false, 'message' => 'Invalid input data']);
        }

        $result = Db::getInstance()->delete('shipping_rules', '`id_product` = ' . $idProduct . ' AND `id_country` = "' . $country . '"');

        $this->returnJson(['success' => (bool)$result, 'message' => $result ? 'Rule deleted successfully' : 'Error deleting rule']);
    }

    private function returnJson($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

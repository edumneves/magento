<?php
class Av5_Correios_Adminhtml_UpdateController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		$this->loadLayout()->renderLayout();
	}
	
	public function postAction() {
		try {
			$service = $this->getRequest()->getPost('formulario');
			$model = Mage::getModel('av5_correios/updater');
			$result = $model->update($service['servico_id']);
			Mage::getSingleton('adminhtml/session')->addSuccess("Serviço " . $service['servico_id'] . " atualizado. Sucesso: " . $result['success'] . " - Erros: " . $result['errors']);
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*');
	}
	
	public function populateAction() {
		try {
			$model = Mage::getModel('av5_correios/updater');
			$model->populate();
			Mage::getSingleton('adminhtml/session')->addSuccess("Banco de dados populado com sucesso!");
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*');
	}
}
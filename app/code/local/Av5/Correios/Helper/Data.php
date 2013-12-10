<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Shipping (Frete)
 * @package    Av5_Correios
 * @copyright  Copyright (c) 2013 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
class Av5_Correios_Helper_Data extends Mage_Core_Helper_Abstract {
	
	/**
	 * Formata um CEP informado
	 *
	 * @param string $zipcode
	 * @return boolean|Ambigous <string, mixed>
	 */
	public function _formatZip($zipcode) {
		$new = trim($zipcode);
		$new = preg_replace('/[^0-9\s]/', '', $new);
		 
		if(!preg_match("/^[0-9]{7,8}$/", $new)){
			return false;
		} elseif(preg_match("/^[0-9]{7}$/", $new)){ // tratamento para CEP com 7 dígitos
			$new = "0" . $new;
		}
	
		return $new;
	}
	
	public function services() {
		$model = Mage::getModel("av5_correios/updater");
		return $model->toUpdate();
	}
	
	public function getServiceName($srv){
		$model = Mage::getModel("av5_correios/updater");
		return $model->getServiceName($srv);
	}
	
}
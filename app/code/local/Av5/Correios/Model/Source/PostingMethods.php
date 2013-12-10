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

class Av5_Correios_Model_Source_PostingMethods
{

    public function toOptionArray()
    {
        return array(
            array('value'=>40010, 'label'=>Mage::helper('adminhtml')->__('Sedex Sem Contrato')),
            array('value'=>40096, 'label'=>Mage::helper('adminhtml')->__('Sedex Com Contrato')),
            array('value'=>81019, 'label'=>Mage::helper('adminhtml')->__('E-Sedex Com Contrato')),
            array('value'=>41106, 'label'=>Mage::helper('adminhtml')->__('PAC Sem Contrato')),
            array('value'=>41068, 'label'=>Mage::helper('adminhtml')->__('PAC Com Contrato')),
            array('value'=>40215, 'label'=>Mage::helper('adminhtml')->__('Sedex 10')),
            array('value'=>40290, 'label'=>Mage::helper('adminhtml')->__('Sedex HOJE')),
            array('value'=>40045, 'label'=>Mage::helper('adminhtml')->__('Sedex a Cobrar')),
        );
    }

}
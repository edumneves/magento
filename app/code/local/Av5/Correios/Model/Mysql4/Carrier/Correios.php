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

/**
 * Av5_Correios_Model_Mysql4_Carrier_Correios
 *
 * @category   Shipping
 * @package    Av5_Correios
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */
class Av5_Correios_Model_Mysql4_Carrier_Correios extends Mage_Core_Model_Mysql4_Abstract {
    
	/**
	 * Valores padrão para popular a tabela de preços
	 * Vetor de vetores com as seguintes posições:
	 * 0: Região atendida
	 * 1: CEP destino inicial
	 * 2: CEP destino final
	 * 3: CEP destino referência
	 */
	protected $_defaultData = array(
		array('SP - Capital',1,9999999,4811210),
		array('SP - Interior',10000000,19999999,14801000),
		array('RJ - Capital',20000000,24799999,20270270),
		array('RJ - Interior',24800001,28999999,24800001),
		array('ES - Capital',29000000,29184999,29060370),
		array('ES - Interior',29185000,29999999,29200250),
		array('MG - Capital',30000000,34999999,30190000),
		array('MG - Interior',35000000,39999999,35930075),
		array('BA - Capital',40000000,43849999,40110010),
		array('BA - Interior',43850000,48999999,44260000),
		array('SE - Capital',49000000,49099999,49027000),
		array('SE - Interior',49100000,49999999,49220000),
		array('PE - Capital',50000000,54999999,50610360),
		array('PE - Interior',55000000,56999999,55805000),
		array('AL - Capital',57000000,57099999,57046270),
		array('AL - Interior',57100000,57999999,57230000),
		array('PB - Capital',58000000,58099999,58011040),
		array('PB - Interior',58100000,58999999,58428720),
		array('RN - Capital',59000000,59099000,59030380),
		array('RN - Interior',59100000,59999999,59140840),
		array('CE - Capital',60000000,61699999,60165082),
		array('CE - Interior',61700000,63999999,61930000),
		array('PI - Capital',64000000,64099999,64001280),
		array('PI - Interior',64100000,64999999,64310000),
		array('MA - Capital',65000000,65099000,65026260),
		array('MA - Interior',65100000,65999999,65275000),
		array('PA - Capital',66000000,67999999,66010902),
		array('PA - Interior',68000000,68899999,68385000),
		array('AP - Capital',68900000,68929999,68901100),
		array('AP - Interior',68930000,68999999,68970000),
		array('AM - Capital',69000000,69099000,69020210),
		array('AM - Interior',69100000,69299000,69110000),
		array('RR - Capital',69300000,69339999,69312450),
		array('RR - Interior',69340000,69399999,69340000),
		array('AM - Interior',69400000,69899999,69470000),
		array('AC - Capital',69900000,69920999,69906380),
		array('AC - Interior',69921000,69999999,69921000),
		array('DF - Capital',70000000,73699999,70040902),
		array('GO - Interior',73700000,76799999,75044450),
		array('RO - Capital',76800000,76834999,76829684),
		array('RO - Interior',76835000,76999999,76870762),
		array('TO - Capital',77000000,77299999,77020116),
		array('TO - Interior',77300000,77999999,77818550),
		array('MT - Capital',78000000,78169999,78020010),
		array('MT - Interior',78170000,78899999,78307000),
		array('MS - Capital',79000000,79124999,79002400),
		array('MS - Interior',79125000,79999999,79200000),
		array('PR - Capital',80000000,83729999,80010000),
		array('PR - Interior',83730000,87999999,84015070),
		array('SC - Capital',88000000,88139999,88010500),
		array('SC - Interior',88140000,89999999,88220000),
		array('RS - Capital',90000000,94999999,90450090),
		array('RS - Interior',95000000,99999999,95680000)
	);
	
	/**
	 * Construtor da classe
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 */
	protected function _construct(){
        $this->_init('av5_correios_shipping/correios', 'id');
    }
	
    /**
     * Recupera os preços de frete baseado no request do usuário
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return multitype:unknown
     */
    public function getRates(Mage_Shipping_Model_Rate_Request $request) {
        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

		$postcode = Mage::helper('av5_correios')->_formatZip($request->getDestPostcode());
        $table = Mage::getSingleton('core/resource')->getTableName('av5_correios_shipping/correios');
		
        $pkgWeight = ceil($request->getPackageWeight());
        
		$searchString = " AND (cep_destino_ini <= '" . $postcode . "' AND cep_destino_fim >= '" . $postcode . "') AND peso = '" . $pkgWeight . "'";
		
		
		$select = $read->select()->from($table);
		$select->where(
				$read->quoteInto(" ( servico in (?) ) ", $request->getPostingMethods()).
				$searchString
			);
		
		$newdata=array();
		$row = $read->fetchAll($select);
		if (!empty($row))
		{
			foreach ($row as $data) {
				$newdata[]=$data;
			}
		}
		return $newdata;
    }
    
    /**
     * Lista os registros desatualizados com base nos serviços, frequencia e limite
     * @param array $postMethods
     * @param int $frequency
     * @param int $limit
     * @return array
     */
    public function listServices($postMethods, $frequency, $limit) {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correios_shipping/correios');
		
    	//Mage::log("AV5 Correios - PostMethods: " . var_export($postMethods,true));
    	
    	$select = $read->select()->from($table);
    	$select->where("(lastupdate IS NULL OR lastupdate < SUBDATE(NOW(),".$frequency.")) AND servico in (".$postMethods.")");
    	$select->limit($limit);
    	
    	return $read->fetchAll($select);
    }
    
    /**
     * Lista os serviços que precisam de atualizam jundo com a quantidade de registros
     * desatualizados
     * @param arra $postMethods
     * @param int $frequency
     * @return array
     */
    public function toUpdate($postMethods, $frequency) {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correios_shipping/correios');
    	
    	$select = $read->select()->from($table,array("servico","count(valor) as total"));
    	$select->where("(lastupdate IS NULL OR lastupdate < SUBDATE(NOW(),".$frequency.")) AND servico in (".$postMethods.")");
    	$select->group("servico");

    	return $read->fetchAll($select);
    }
    
    /**
     * Retorna a quantidade de registros desatualizados de um serviço
     * @param string $postMethod
     * @param int $frequency
     * @return array
     */
    public function hasToUpdate($postMethod, $frequency) {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correios_shipping/correios');
    	 
    	$select = $read->select()->from($table,array("servico","count(valor) as total"));
    	$select->where("(lastupdate IS NULL OR lastupdate < SUBDATE(NOW(),".$frequency.")) AND servico = ".$postMethod);
    	$select->group("servico");
    
    	return $read->fetchRow($select);
    }
    
    /**
     * Verifica se o serviço está presente no banco de dados
     * @param string $service
     * @return boolean
     */
    public function isPopulated($service) {
    	$read = $this->_getReadAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correios_shipping/correios');
    	 
    	$select = $read->select()->from($table,array("count(valor) as total"));
    	$select->where("servico = ".$service);
    	
    	$result = $read->fetchRow($select);
    	if (!$result['total']) {
    		return false;
    	}
    	
    	return true;
    }
    
    /**
     * Atualiza o serviço informado com os dados recebidos
     * @param int $id
     * @param array $data
     */
    public function updateService($id, $data) {
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correios_shipping/correios');

    	$rows = $write->update($table, $data, "id = " . $id);
    }
    
    /**
     * Popula o banco de dados com os dados padrão para os serviços informados
     * @param array $services
     * @param double $maxWeight
     * @param string $from
     */
    public function populate($services, $maxWeight, $from) {
    	$read = $this->_getReadAdapter();
    	$write = $this->_getWriteAdapter();
    	$table = Mage::getSingleton('core/resource')->getTableName('av5_correios_shipping/correios');
    	
    	foreach ($services as $service) {
    		$select = $read->select()->from($table,array("count(id) as total"));
    		$select->where("servico = ".$service[0]);
    		$result = $read->fetchRow($select);
    		
    		if ($result['total'] > 0) 
    			continue;
    		
    		foreach ($this->_defaultData as $record) {
    			for($weight = 1; $weight <= $maxWeight; $weight++) {
	    			try {
	    				$write->insert($table, array(
		    				'servico' 			=> $service[0],
		    				'nome'				=> $service[1],
		    				'regiao'			=> $record[0],
		    				'prazo'				=> $service[2],
		    				'peso'				=> $weight,
		    				'valor'				=> '0.00',
		    				'cep_origem'		=> $from,
		    				'cep_destino_ini'	=> $record[1],
		    				'cep_destino_fim'	=> $record[2],
		    				'lastupdate'		=> 'NULL',
		    				'cep_destino_ref'	=> $record[3]
		    			));
	    			} catch (Exception $e) {
	    				Mage::log("AV5_Correios Erro: " . $e->getMessage() . " > Serviço: " . $service[1] . "(" . $service[0] . ") - CEP:" . $record[1] . " a " . $record[2] . " - Peso: " . $weight);
	    			}
    			}
    		}
    	}
    }
}

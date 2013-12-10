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
 * Av5_Correios_Model_Updater
 *
 * @category   Shipping
 * @package    Av5_Correios
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */

class Av5_Correios_Model_Updater extends Varien_Object {
	
	/**
	 * Propriedades da classe 
	 */
	protected $_code				= "av5_correios";
	protected $_from				= NULL; // CEP de origem
	protected $_wsUrl				= NULL; // URL do webservice
	protected $_login				= NULL; // Login do Webservice (Contrato)
	protected $_password			= NULL; // Senha do webservice (Contrato)
	protected $_defHeight			= NULL; // Altura padrão de pacotes
	protected $_defWidth			= NULL; // Comprimento padrão de pacotes
	protected $_defDepth			= NULL; // Largura padrão de pacotes
	protected $_maxWeight			= NULL; // Peso máximo permitido
	protected $_updateFrequency		= NULL; // Frequencia de atualização da tabela
	protected $_postingMethods		= NULL; // Serviços de postagem
	protected $_limitRecords		= NULL; // Número de registros atualizados por iteração
	protected $_ownerHands			= NULL; // Entrega em mãos próprias
	protected $_receivedWarning		= NULL; // Aviso de recebimento
	protected $_declaredValue		= NULL; // Valor declarado
	protected $_initiated			= false; // Controla se as variáveis já foram inicializadas
	
	/**
	 * Inicializa as propriedades da classe
	 */
	protected function _init() {
		if (!$this->_initiated) {
			$this->_wsUrl = $this->getConfigData('url_ws_correios');
			$this->_login = $this->getConfigData('login');
			$this->_password = $this->getConfigData('password');
			$this->_defHeight = $this->getConfigData('default_height');
			$this->_defWidth = $this->getConfigData('default_width');
			$this->_defDepth = $this->getConfigData('default_depth');
			$this->_maxWeight = $this->getConfigData('max_weight');
			$this->_updateFrequency = $this->getConfigData('update_frequency');
			$this->_postingMethods = $this->getConfigData('posting_methods');
			$this->_limitRecords = $this->getConfigData('limit_records');
			$this->_ownerHands = ($this->getConfigData('owner_hands')) ? 'S' : 'N';
			$this->_receivedWarning = ($this->getConfigData('received_warning')) ? 'S' : 'N';
			$this->_declaredValue = $this->getConfigData('declared_value');
			$this->_from = Mage::helper('av5_correios')->_formatZip(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
			$this->_initiated = true;
		}
	}
	
	/**
	 * Recupera configurações do módulo
	 * @param string $field
	 * @return boolean, mixed, string, NULL
	 */
	public function getConfigData($field)
	{
		if (empty($this->_code)) {
			return false;
		}
		$path = 'carriers/'.$this->_code.'/'.$field;
		return Mage::getStoreConfig($path, $this->getStore());
	}
	
	/**
	 * Executa atualização de tabela de preços do serviço informado
	 * @param string $services
	 */
	public function update($services=null) {
		$this->_init();
		
		$model = Mage::getResourceModel('av5_correios_shipping/carrier_correios');
		
		if (!$services) {
			$services = $this->_postingMethods;
		}
		
		$totalSuccess = $totalErrors = 0;
		foreach($model->listServices($services, $this->_updateFrequency, $this->_limitRecords) as $row) {
			$cep_origem = trim(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
			$cep_destino = $row['cep_destino_ref'];
			$peso = $row['peso'];
			$url_d = $this->_wsUrl."&nCdEmpresa=".$this->_login."&sDsSenha=".$this->_password."&nCdFormato=1&nCdServico=".$row['servico']."&nVlComprimento=".$this->_defWidth."&nVlAltura=".$this->_defHeight."&nVlLargura=".$this->_defDepth."&sCepOrigem=".$cep_origem."&sCdMaoPropria=".$this->_ownerHands."&sCdAvisoRecebimento=".$this->_receivedWarning."&nVlValorDeclarado=".$this->_declaredValue."&nVlPeso=".$peso."&sCepDestino=".$cep_destino;
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url_d);
			curl_setopt($ch, CURLOPT_HEADER, 0);
		
			ob_start();
			curl_exec($ch);
			curl_close($ch);
			$content = ob_get_contents();
			ob_end_clean();
			
			try {
				$xml = new SimpleXMLElement($content);
			} catch(Exception $e) {
				Mage::log("AV5_Correios Erro: " . $e->getMessage() . " - CEP: " . $cep_destino . ' para ' . $row['servico'] . ':' . $row['nome']);
				$totalErrors++;
				continue;
			}
			
			if($xml) {
				foreach($xml->cServico as $servico) {
					if ($servico->Erro == "0") {
						try {
							$data = array();
							$data['valor'] = str_replace(",",".",$servico->Valor);
							$data['prazo'] = $servico->PrazoEntrega;
							$data['lastupdate'] = date('Y-m-d H:i:s');
							$model->updateService($row['id'],$data);
							Mage::log('AV5_Correios_Updater: CEP ' . $cep_destino . ' para ' . $row['servico'] . ':' . $row['nome'] . ' atualizado');
							$totalSuccess++;
						} catch (Exception $e) {
							Mage::log("AV5_Correios Erro: " . $e->getMessage() . " - CEP: " . $cep_destino . ' para ' . $row['servico'] . ':' . $row['nome']);
							$totalErrors++;
						}
					} else {
						Mage::log("AV5_Correios Erro: " . $servico->MsgErro." – ".$row['cep_destino_ini']." – ".$row['cep_destino_fim']." – ".$row['cep_dest_ref']." : " . $url_d);
						$totalErrors++;
					}
	  			}
			} else {
				Mage::log("AV5_Correios Erro: Correios fora do ar.");
				$totalErrors++;
			}
		}
		return array("success" => $totalSuccess, "errors" => $totalErrors);
	}
	
	/**
	 * Retorna a lista de serviços que precisam de atualização
	 * @return array
	 */
	public function toUpdate() {
		$this->_init();
		$model = Mage::getResourceModel('av5_correios_shipping/carrier_correios');
		return $model->toUpdate($this->_postingMethods, $this->_updateFrequency);
	}
	
	public function getServiceName($service) {
		list($name, $days) = explode(',', $this->getConfigData('serv_' . $service));
		return $name;
	}
	
	public function stillUpdate($service) {
		$model = Mage::getResourceModel('av5_correios_shipping/carrier_correios');
		$result = $model->hasToUpdate($service, $this->_updateFrequency);
		
		if ($result['total'] > 0) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Popula a tabela de preços com os dados básicos para os serviços selecionados
	 */
	public function populate() {
		$this->_init();
		$model = Mage::getResourceModel('av5_correios_shipping/carrier_correios');
		
		$postingMethods = explode(',', $this->_postingMethods);
		$methods = array();
		
		if (is_array($postingMethods)) {
			foreach ($postingMethods as $method) {
				list($name, $days) = explode(',', $this->getConfigData('serv_' . $method));
				$methods[] = array($method,$name,$days);
			}
		} else {
			list($name, $days) = explode(',', $this->getConfigData('serv_' . $postingMethods));
			$methods[] = array($postingMethods,$name,$days);
		}
		
		$model->populate($methods, $this->_maxWeight, $this->_from);
	}
}
<?
/*
	IPOLIML_CACHE_TIME - ����� ���� � ��������
	IPOLIML_NOCACHE    - ���� ����� - �� ������������ ���

	onCompabilityBefore - ������� �������� [���������������]
	onCalculate - ���������� ������� [���������������]
*/
use \Ipolh\IML\Bitrix\Adapter\cargo as BitrixCargo;
use \Ipolh\IML\Bitrix\Entity\RequestTimeout;

cmodule::includeModule('sale');
IncludeModuleLangFile(__FILE__);

class CDeliveryIML extends imlHelper{
	public static $courierPrice = 250;
	public static $pickupPrice  = 120;
    /**
     * @var bool|array
     */
    public static $profiles     = false;// key => (bool) nal (T)/ beznal (F)
	public static $hasPVZ       = false;//������ �� ���

	public static $date         = false;
	public static $price        = false;

	public static $orderWeight  = false;
	public static $orderPrice   = false;
	public static $orderGabs    = false;

	public static $nalPayChosen = false; // ���� �� �������� ����������� ������ (checkNal)
	public static $psChecks     = false; // ���� �� �������� ��������� ��������� ������� (checkPS)

	//���������
	public static $city = '';
	public static $addressCity = '';

	public static $payerType = false;
	public static $paysystem = false;

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												������� ������� ���
		== Init ==  == GetConfig ==  == SetSettings ==  == GetSettings ==  == Compability ==  == Calculate ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	public static function Init(){
		return array(
			/* Basic description */
			"SID" => "iml",
			"NAME" => "IML",
			"DESCRIPTION" => GetMessage('IPOLIML_DELIV_DESCR'),
			"DESCRIPTION_INNER" => GetMessage('IPOLIML_DELIV_DESCRINNER'),
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),
			"HANDLER" => __FILE__,

			/* Handler methods */
			"DBGETSETTINGS" => array("CDeliveryIML", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliveryIML", "SetSettings"),
			"GETCONFIG" => array("CDeliveryIML", "GetConfig"),

			"COMPABILITY" => array("CDeliveryIML", "Compability"),
			"CALCULATOR" => array("CDeliveryIML", "Calculate"),

			/* List of delivery profiles */
			"PROFILES" => array(
				"courier" => array(
					"TITLE" => GetMessage('IPOLIML_DELIV_COURIER_TITLE'),
					"DESCRIPTION" => GetMessage('IPOLIML_DELIV_COURIER_DESCR'),

					"RESTRICTIONS_WEIGHT" => array(0,25000),
					"RESTRICTIONS_SUM" => array(0),

					"RESTRICTIONS_MAX_SIZE" => "1000",
					"RESTRICTIONS_DIMENSIONS_SUM" => "1500",
				),
				"pickup" => array(
					"TITLE" => GetMessage('IPOLIML_DELIV_PICKUP_TITLE'),
					"DESCRIPTION" => GetMessage('IPOLIML_DELIV_PICKUP_DESCR'),

					"RESTRICTIONS_WEIGHT" => array(0,20000),
					"RESTRICTIONS_SUM" => array(0),
					"RESTRICTIONS_MAX_SIZE" => "1000",
					"RESTRICTIONS_DIMENSIONS_SUM" => "1500"
				)
			)
		);
	}

	public static function GetConfig(){
        // Account
        $arActiveAccs = \Ipolh\IML\AuthHandler::callAccounts();
        $arAccs = array(false => GetMessage("IPOLIML_DELCONFIG_DEFAULT"));
        foreach ($arActiveAccs as $id => $arActiveAcc) {
            if($arActiveAcc['LABEL']) {
                $arAccs[$id] = $arActiveAcc['LOGIN']." ({$arActiveAcc['LABEL']})";
            }else{
                $arAccs[$id] = $arActiveAcc['LOGIN'];
            }
        }

		$arConfig = array(
			"CONFIG_GROUPS" => array(
				"additional" => GetMessage('IPOLIML_DELIV_CONFGROUP_ADD'),
				"price"      => GetMessage('IPOLIML_DELIV_CONFGROUP_PAY'),
			),

			"CONFIG" => array(
			    "account" => array(
                    'TITLE'   => GetMessage('IPOLIML_DELCONFIG_ACCOUNT'),
                    'TYPE'    => 'DROPDOWN',
                    'DEFAULT' => false,
                    'GROUP'   => "additional",
                    'VALUES'  => $arAccs
                ),
                "UID" => array(
                    'TITLE'   => GetMessage('IPOLIML_DELCONFIG_UID'),
                    'TYPE'    => 'STRING',
                    'DEFAULT' => '',
                    'GROUP'   => "additional",
                ),
				"courier_price_native" => array(
					"TYPE"    => "STRING",
					"DEFAULT" => self::$courierPrice,
					"TITLE"   => GetMessage('IPOLIML_DELIV_CONF_CPN')." (".COption::GetOptionString("sale", "default_currency", "RUB").')',
					"GROUP"   => "price",
				),
				"courier_free_native" => array(
					"TYPE"    => "STRING",
					"DEFAULT" => "",
					"TITLE"   => GetMessage('IPOLIML_DELIV_CONF_CFN')." (".COption::GetOptionString("sale", "default_currency", "RUB").') ',
					"GROUP"   => "price",
				),
				"pickup_price_native" => array(
					"TYPE"    => "STRING",
					"DEFAULT" => self::$pickupPrice,
					"TITLE"   => GetMessage('IPOLIML_DELIV_CONF_PPN')." (".COption::GetOptionString("sale", "default_currency", "RUB").')',
					"GROUP"   => "price",
				),
				"pickup_free_native" => array(
					"TYPE"    => "STRING",
					"DEFAULT" => "",
					"TITLE"   => GetMessage('IPOLIML_DELIV_CONF_PFN')." (".COption::GetOptionString("sale", "default_currency", "RUB").')',
					"GROUP"   => "price",
				),
				"courier_price_other" => array(
					"TYPE"    => "STRING",
					"DEFAULT" => self::$courierPrice,
					"TITLE"   => GetMessage('IPOLIML_DELIV_CONF_CPO')." (".COption::GetOptionString("sale", "default_currency", "RUB").')',
					"GROUP"   => "price",
				),
				"courier_free_other" => array(
					"TYPE"    => "STRING",
					"DEFAULT" => "",
					"TITLE"   => GetMessage('IPOLIML_DELIV_CONF_CFO')." (".COption::GetOptionString("sale", "default_currency", "RUB").') ',
					"GROUP"   => "price",
				),
				"pickup_price_other" => array(
					"TYPE"    => "STRING",
					"DEFAULT" => self::$pickupPrice,
					"TITLE"   => GetMessage('IPOLIML_DELIV_CONF_PPO')." (".COption::GetOptionString("sale", "default_currency", "RUB").')',
					"GROUP"   => "price",
				),
				"pickup_free_other" => array(
					"TYPE"    => "STRING",
					"DEFAULT" => "",
					"TITLE"   => GetMessage('IPOLIML_DELIV_CONF_PFO')." (".COption::GetOptionString("sale", "default_currency", "RUB").')',
					"GROUP"   => "price",
				),
			),
		);

		return $arConfig;
	}

	public static function SetSettings($arSettings){
		if(!is_numeric($arSettings['courier_price_native']))
			$arSettings['courier_price_native'] = self::$courierPrice;
		if(!is_numeric($arSettings['pickup_price_native']))
			$arSettings['pickup_price_native'] = self::$pickupPrice;
		if(!is_numeric($arSettings['courier_price_other']))
			$arSettings['courier_price_other'] = self::$courierPrice;
		if(!is_numeric($arSettings['pickup_price_other']))
			$arSettings['pickup_price_other'] = self::$pickupPrice;

		foreach(array('courier_free_native','pickup_free_native','courier_free_other','pickup_free_other') as $name)
			if(!is_numeric($arSettings[$name]))
				$arSettings[$name] = '';

		return serialize($arSettings);
	}

	public static function GetSettings($strSettings){
		return unserialize($strSettings);
	}

	public static function Compability($arOrder, $arConfig){
		if(!self::isLogged())
			return false;

		$arKeys = array();
		try{
            $obCargo = self::getCargo($arOrder['ITEMS']);

            self::$orderWeight = $arOrder['WEIGHT'];
            self::$orderPrice  = $arOrder['PRICE'];
            self::$orderGabs   = $obCargo->getCargo()->getDimensions();

            self::getIMLCity($arOrder['LOCATION_TO']);

            $arProfiles = array();

            if(self::$city){
                self::defineProfiles();
                if(!self::$profiles || !count(self::$profiles))
                    self::reCheckProfiles(); // ���� ����� �� ������ ��-�� ��������
// TODO: av ����� ���� false ���� ������ - ��������� ��������: ���� !av - �� ���������, ���� ������
                if(is_array(self::$profiles)){
                    foreach (self::$profiles as $profile => $av)
                    {
                        $arProfiles []= $profile;
                    }
                }
            }

            //foreach(self::$profiles as $profile => $nal){ // closed before Mayorov's answer
            foreach($arProfiles as $profile){
                $deliveryTerms = self::calculateDelivery($profile,$arOrder['LOCATION_FROM'],$arOrder['LOCATION_TO'],$arConfig);

                if($deliveryTerms['SUCCESS']){
                    $arKeys []= $profile;
                }
            }

            $ifPrevent=true;

            foreach(GetModuleEvents(self::$MODULE_ID, "onCompabilityBefore", true) as $arEvent)
                $ifPrevent = ExecuteModuleEventEx($arEvent,Array($arOrder,$arConfig,&$arKeys));

            if(is_array($ifPrevent)) {
                $newKeys = array();
                foreach($ifPrevent as $val) {
                    if(in_array($val, $arKeys))
                        $newKeys[] = $val;
                }
                $arKeys = $newKeys;
            }

            if(!$ifPrevent) return array();

            if(!COption::GetOptionString(self::$MODULE_ID,'pvzPicker',false) && in_array('pickup',$arKeys))
                unset($arKeys['pickup']);

            // ����������� FrontEnd (��� ���������������� ����������)
            if($_POST['CurrentStep'] > 1 && $_POST['CurrentStep'] < 4 && in_array('pickup',$arKeys))
                self::pickupLoader();

            \Ipolh\IML\Bitrix\Admin\Logger::compability($arKeys);
		}catch (\Exception $e){
		    $arKeys = array();
        }

		return $arKeys;
	}

	public static function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false){//������� ���������
		if(!self::$city)
			self::getIMLCity($arOrder['LOCATION_TO']);

		try{
            $obCargo = self::getCargo($arOrder['ITEMS']);

            self::$orderWeight = $arOrder['WEIGHT'];
            self::$orderPrice  = $arOrder['PRICE'];
            self::$payerType   = $arOrder['PERSON_TYPE_ID'];
            self::$orderGabs   = $obCargo->getCargo()->getDimensions();

            $deliveryTerms = self::calculateDelivery($profile,$arOrder['LOCATION_FROM'],$arOrder['LOCATION_TO'],$arConfig);

            if(!$deliveryTerms['price'])
                $deliveryTerms['price']=0;

            if($deliveryTerms['SUCCESS']){
                if(COption::GetOptionString(self::$MODULE_ID,'labelDays','N') != 'N' && $deliveryTerms['term'] != "-")
                {
                    if($deliveryTerms['term'] > 4 && $deliveryTerms['term'] < 21 || $deliveryTerms['term'] == 0)
                        $deliveryTerms['term'] .= ' '.GetMessage('IPOLIML_LD_days');
                    else{
                        $lst = $deliveryTerms['term'] % 10;
                        if($lst == 1)
                            $deliveryTerms['term'] .= ' '.GetMessage('IPOLIML_LD_day');
                        elseif($lst < 5)
                            $deliveryTerms['term'] .= ' '.GetMessage('IPOLIML_LD_daya');
                        else
                            $deliveryTerms['term'] .= ' '.GetMessage('IPOLIML_LD_days');
                    }
                    if(COption::GetOptionString(self::$MODULE_ID,'labelDays','N') == 'A')
                        $deliveryTerms['term'] = GetMessage('IPOLIML_LD_term').": ".$deliveryTerms['term'];
                }

                self::$date = date('d.m.Y',mktime()+86400 * $deliveryTerms['term']);

                if(
                    !self::$nalPayChosen ||
                    self::$profiles[$profile]
                ){
                    if(COption::GetOptionString(self::$MODULE_ID,'forceRoundDelivery','N') == 'Y'){
                        $deliveryTerms['price'] = round($deliveryTerms['price']);
                    }

                    $arReturn = array(
                        "RESULT"  => "OK",
                        "VALUE"   => $deliveryTerms['price'],
                        "TRANSIT" => (string)$deliveryTerms['term']
                    );
                } else{
                    $arReturn = array(
                        "RESULT" => "ERROR",
                        "TEXT"   => GetMessage("IPOLIML_DELIV_ERROR_NONAL"),
                    );
                }
            }else{
                $arReturn = array(
                    "RESULT" => "ERROR",
                    "TEXT"   => $deliveryTerms['ERROR'],
                );
            }
        } catch (\Exception $e) {
            $arReturn = array(
                "RESULT" => "ERROR",
                "TEXT" => 'Exception: '.$e->getMessage()
            );
		}

		foreach(GetModuleEvents(self::$MODULE_ID, "onCalculate", true) as $arEvent){
			ExecuteModuleEventEx($arEvent,Array(&$arReturn,$profile,$arConfig,$arOrder));
		}

		\Ipolh\IML\Bitrix\Admin\Logger::calculate(array('Profile' => $profile, 'Result'=> $arReturn));

		self::$price[$profile] = $arReturn['VALUE'];
		return $arReturn;
	}


    /**
     * @param $arItems
     * @return BitrixCargo
     */
    public static function getCargo($arItems)
    {
        $obCargo = new BitrixCargo(new \Ipolh\IML\Bitrix\Entity\defaultGabarites());
        $obCargo->set($arItems);
        return $obCargo;
    }


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												������ ���������
		== Init ==  == GetConfig ==  == SetSettings ==  == GetSettings ==  == Compability ==  == Calculate ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

		// ����� �������

			// Calcs the delivery via server or table as options says
	public static function calculateDelivery($profile,$locationFrom,$locationTo,$arConfig)
	{
		$countType     = COption::GetOptionString(self::$MODULE_ID,'countType','S');
		$deadServTable = (COption::GetOptionString(self::$MODULE_ID,'serverToTable','Y') == 'Y');

		/*
			SUCCESS - T/F
			ERROR - string
			price
			term
		*/
		$deliveryTerms = array('SUCCESS' => false);

		if($countType != 'T'){
			$deliveryTerms = self::calculateServer($profile,$locationTo,self::$orderWeight,self::$orderGabs,self::$orderPrice,$arConfig);
		}

		if(
			$countType == 'T'
			||
			(
				$deadServTable && !$deliveryTerms['SUCCESS'] && $deliveryTerms['CASE'] == 'DEADSERV'
			)
		){
			$deliveryTerms = self::calculateTable($profile,$locationFrom,$locationTo,self::$orderWeight,$arConfig);
		}

		return $deliveryTerms;
	}

		// ���������, ����� ���� ������� ����� ������� ���, ������, � ��� �����
	public static function defineProfiles($city=false,$skipRestricts = true,$define=true){
		if(!self::$city)
			self::$city = $city;
		if(!self::$orderPrice)
			self::$orderPrice = 1000;
		if(!self::$orderWeight)
			self::$orderWeight = 1000;
		$servises = self::getReference('service');
		$regions  = self::getReference('region');
		$PVZ      = self::getReference('PVZ');
		$blockedServices = ($skipRestricts) ? array() : self::getCityRestricts(self::$city);
		$killedServices  = unserialize(COption::GetOptionString(self::$MODULE_ID,'blockedServices','a:{}'));
		$arProfServises = array();
		$sW = round(self::$orderWeight/1000);
		$arAllowed = array();
		$arPrepared = array('courier'=>false,'pickup'=>false);

		if(array_key_exists(self::toUpper(self::$city),$regions)){
			$arPrepared['courier'] = true;

			if(
			    array_key_exists(self::toUpper(self::$city),$PVZ) &&
                self::toUpper(self::$city) === self::toUpper(self::$addressCity)
            )
				$arPrepared['pickup'] = true;
		}

		foreach($servises as $sCode => $descr){
			if(
				in_array($sCode,$blockedServices) ||
				array_key_exists($sCode,$killedServices) ||
				$sW < $descr['WeightMIN'] ||
				$sW > $descr['WeightMAX'] ||
				self::$orderPrice  < $descr['ValuatedAmountMIN'] ||
				self::$orderPrice  > $descr['ValuatedAmountMAX'] ||
				$sCode == GetMessage('IPOLIML_POST')
			)
				continue;

			$mode = 'courier';
			if(strpos(trim($sCode),GetMessage('IPOLIML_S')) === 0)
				$mode = 'pickup';
			if($arPrepared[$mode]){
				if(!array_key_exists($mode,$arProfServises))
					$arProfServises[$mode] = false;
				if($descr['AmountMAX'] > 0)
					$arProfServises[$mode] = true;
			}
			$arAllowed[]=$sCode;
		}

		if($define){
			self::$profiles = $arProfServises;
		} else {
			return $arProfServises;
		}
	}

		// ������������, �� ���� - ������ �������� ������
	public static function reCheckProfiles(){
		if(strpos(self::$city,GetMessage('IPOLIML_LANG_YO_S')) !== false){
			self::$city = str_replace(GetMessage('IPOLIML_LANG_YO_S'),GetMessage('IPOLIML_LANG_E_S'),self::$city);
			self::defineProfiles();
		}
	}

		// �������� �������� ����������� ������
	public static function checkCity($city,$showProfs=false){
		if(!self::$orderWeight)
			self::$orderWeight = 1000;
		if(!self::$orderPrice)
			self::$orderPrice = 0;
		self::defineProfiles($city);
		if(!self::$profiles || !count(self::$profiles))
			return false;
		elseif($showProfs)
			return self::$profiles;
		else
			return true;
	}

		// ����������� ����������� ������
	public static function getCityRestricts($city=false){
		$restricts = self::getReference('exceptionSR');
		if(
			COption::GetOptionString(self::$MODULE_ID,'turnOffRestrictsOS','N') != 'Y' &&
			array_key_exists(self::toUpper($city),$restricts)
		)
			return $restricts[self::toUpper($city)];
		else
			return array();
	}

		// ������ ����� ������

    /**
     * array: LOCATION_TO, WEIGHT, PROFILE
     * @var array|bool
     */
	public static $serviceData = false;

			// ���������� � ������ ����
	public static function calculateServer($profile,$locationTo,$weight,$gabs,$price=0,$arConfig=false){
		self::$serviceData['LOCATION_TO'] = $locationTo;
		self::$serviceData['WEIGHT']      = round($weight / 100)/10;
		self::$serviceData['GABS']        = (\COption::GetOptionString(self::$MODULE_ID,'mindGabarites','N') == 'Y') ? $gabs : false;
		self::$serviceData['GABS']        = (\COption::GetOptionString(self::$MODULE_ID,'mindGabarites','N') == 'Y') ? $gabs : false;
		self::$serviceData['ORDER_PRICE'] = $price;

		if(!array_key_exists('SERVICE',self::$serviceData) || !self::$serviceData['SERVICE'])
			self::$serviceData['PROFILE'] = $profile;

        $getDefaultAuth = true;
		if($arConfig && $arConfig['account'] && array_key_exists('VALUE',$arConfig['account'])){
            if($arConfig['account']['VALUE'] !== 0){
                $account = \Ipolh\IML\AuthHandler::getById($arConfig['account']['VALUE']);
                if($account){
                    $getDefaultAuth = false;
                }
            }
        }
        if($getDefaultAuth){
		    $account = \Ipolh\IML\AuthHandler::getDefaultAuth();
        }

		$deliveryTerms = self::getServerPrice($account);
		if(!array_key_exists('ERROR',$deliveryTerms)){
			$dT = strtotime($deliveryTerms['term']);
            $deliveryTerms['term'] = $deliveryTerms['term'] ? ceil((strtotime($deliveryTerms['term']) - mktime())/86400) : "-";
		}

		return $deliveryTerms;
	}
			// ���������������� ������ �������
	public static function getServerPrice($account=false)
    {
		if(!self::$serviceData)
			return array('ERROR' => 'No data for counting.');

		if(!array_key_exists('SERVICE', self::$serviceData) && !array_key_exists('PROFILE',self::$serviceData))
			return array('ERROR' => 'No service or profile to count delivery price.');

        if (!RequestTimeout::isDeliveryRequestAvailable(self::$serviceData['PROFILE']))
        {
            return array('SUCCESS' => false, GetMessage('IPOLIML_DELIV_ERROR_SERVERDOWN') . " (0)", 'CASE' => 'DEADSERV');
        }

		if(!$account){
		    $account = \Ipolh\IML\AuthHandler::getDefaultAuth();
        }

		$accurate = true;

        $service = true;
		if(!array_key_exists('SERVICE',self::$serviceData) || !self::$serviceData['SERVICE']){
			$ps = self::checkPS();
			if(count($ps)>1) {
                $accurate = false;
            }
			$paymentType = array_pop($ps);
			switch($paymentType."%".self::$serviceData['PROFILE']){
				case 'nal%courier'  : $service = '24KO'; break;
				case 'nal%pickup'   : $service = 'C24KO'; break;
				case 'bnal%courier' : $service = '24'; break;
				case 'bnal%pickup'  : $service = 'C24'; break;
			}
		}else{
			$service = self::$serviceData['SERVICE'];
			$paymentType = (strpos($service,'KO')) ? 'nal' : 'bnal';
		}
		self::$psChecks = $paymentType;

		if(!$service)
			return array('ERROR' => 'No service or profile to count delivery price.');

		if(self::$city){
		    $regionTo  = self::$city;
		    $addressTo = self::$addressCity;
        } else {
		    $arRegion = self::getIMLCity(self::$serviceData['LOCATION_TO'],true);

		    if($arRegion){
		        $regionTo  = $arRegion['region'];
		        $addressTo = $arRegion['city'];
            } else {
                return array('SUCCESS' => false, 'ERROR' => GetMessage('IPOLIML_DELIV_ERROR_NOCITY'),'CASE' => 'UNKNOWNCITY');
            }
        }

		$regionTo = self::toUpper($regionTo);

		$content =array(
			'Job'        => $service,
			'RegionFrom' => imldriver::adequateRegion(self::toUpper(COption::GetOptionString(self::$MODULE_ID,'departure',false))),
			'RegionTo'   => imldriver::adequateRegion($regionTo,true),
			'Volume'     => 1,
			'Weigth'     => (self::$serviceData['WEIGHT']) ? self::$serviceData['WEIGHT'] : COption::GetOptionString(self::$MODULE_ID,'defaultWeight',1)
		);

		if(
		    array_key_exists('ORDER_PRICE',self::$serviceData) &&
            self::$serviceData['ORDER_PRICE'] &&
            \Ipolh\IML\option::get('mindDeclaredValue') == 'Y'
        ){
            $content['declaredValue'] = self::$serviceData['ORDER_PRICE'];
        }

		if($addressTo){
		    $content['deliveryAddress'] = $addressTo;
        }

		if(array_key_exists('GABS',self::$serviceData) && self::$serviceData['GABS']){
		    $content['depth']  = self::$serviceData['GABS']['L'] / 10;
		    $content['width']  = self::$serviceData['GABS']['W'] / 10;
		    $content['height'] = self::$serviceData['GABS']['H'] / 10;
        }

		$testVar = null;

		if(strpos($service,'C24') === 0){
			if(!array_key_exists('PVZ',self::$serviceData) || !self::$serviceData['PVZ'] || self::$serviceData['PVZ'] == 'false'){
				$oId = false;
				switch(true){
					case array_key_exists('ID',$_REQUEST)       : $oId = $_REQUEST['ID']; break;
					case array_key_exists('id',$_REQUEST)       : $oId = $_REQUEST['id']; break;
					case array_key_exists('order_id',$_REQUEST) : $oId = $_REQUEST['order_id']; break;
					case (array_key_exists('action',$_REQUEST) && $_REQUEST['action'] == 'changeDeliveryService') :
						$oId = $_REQUEST['formData']['order_id'];
						break;
				}
					// ��� ��� - �������� ������� �� �������� ������ ��� request'�
				$predict = self::getChosenPVZ($oId,$regionTo,($paymentType==='nal'));

                $testVar = $predict;

                switch($predict['RESULT'])
                {
					case 'APROX' :
					    $accurate = false;
					case 'OK'	 :
					    self::$serviceData['PVZ'] = $predict['VALUE'];
					    break;
					case 'ERROR' :
					    return $predict;
				}
			}
            $content['SpecialCode'] = self::$serviceData['PVZ'];
        }

        // ReceiveDate
		$ReceiveDate = mktime(0,0,0,date('m'),date('d'),date('Y'));

		$arDelays = array(
			"delay" => unserialize(COption::GetOptionString(self::$MODULE_ID,'addHold','a:0:{}')),
			"commonDelay" => intval(COption::GetOptionString(self::$MODULE_ID,'commonHold',''))
		);

		if(!self::checkToday()){
			$ReceiveDate += 86400;
		}
		if($arDelays["commonDelay"]){
			$ReceiveDate += $arDelays["commonDelay"] * 86400;
		}
		if(array_key_exists($regionTo,$arDelays["delay"])){
			$ReceiveDate += $arDelays["delay"][$regionTo] * 86400;
		}
		$content['ReceiveDate'] = date('Y-m-d\TH:i',$ReceiveDate);

		// Delivery Request Timeout
        $timeout = intval(\Ipolh\IML\option::get('requestTimeout'));
        if($timeout <= 0) $timeout = 6;

        $cache = new Ipolh\IML\Bitrix\Entity\cache();
		$cachename = "calculate|".serialize($content).$account['LOGIN'];
		if($cache->checkCache($cachename)){
			$code   = 200;
			$result = $cache->getCache($cachename);
		}else{
			// $ch = curl_init("http://api.iml.ru/Json/GetPrice");
			$ch = curl_init("http://api.iml.ru/v5/GetPrice");
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Request Timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

            curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(self::zajsonit($content)));
			//curl_setopt($ch, CURLOPT_USERPWD, COption::GetOptionString(self::$MODULE_ID,'logIml').":".COption::GetOptionString(self::$MODULE_ID,'pasIml'));
			curl_setopt($ch, CURLOPT_USERPWD, $account['LOGIN'].":".$account['PASSWORD']);
			curl_setopt($ch, CURLOPT_SSLVERSION, 3);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			\Ipolh\IML\Bitrix\Admin\Logger::calculation(array('Request' => $content, 'Response'=> json_decode($response, true)));

			if($code == 200){
				$result = json_decode($response, true); // ��������� �������
				if(!array_key_exists('Code',$result)){
					$cache->setCache($cachename,$result);
				}
			}
		}

        if ($result['Price'] != 0)
        {
            $price = $result['Price'];
        }
        else
        {
            $pickupDeliveryPriceType = Ipolh\IML\option::get('pickupDeliveryPriceType');

            switch ($pickupDeliveryPriceType)
            {
                case 'min':
                    $price = $result['PriceMin'];
                    break;
                case 'average':
                    $price = ($result['PriceMin'] + $result['PriceMax']) / 2;
                    break;
                case 'max':
                    $price = $result['PriceMax'];
                    break;
            }
        }

        if ($code && $code != 0)
        {
            RequestTimeout::resetDeliveryRequestAvailability(self::$serviceData['PROFILE']);
        }

        if (!$code || $code == 0) {
	        RequestTimeout::setDeliveryRequestUnavailable(self::$serviceData['PROFILE']);
            return array('SUCCESS' => false, 'ERROR' => GetMessage('IPOLIML_DELIV_ERROR_SERVERDOWN')." ($code)",'CASE' => 'DEADSERV');
        }
		elseif ($code != 200)
			return array('SUCCESS' => false, GetMessage('IPOLIML_DELIV_ERROR_SERVERDOWN')." ($code)", 'CASE' => 'IML');
		elseif (array_key_exists('Code',$result))
			return array('SUCCESS' => false, 'ERROR' => self::zaDEjsonit($result['Mess']." (".$result['Code'].")"), 'CASE' => 'IML');
		else
			return array('SUCCESS' => true, 'price' => $price, 'term' => $result['DeliveryDate']);
	}

    static function resetLastRequestTime(){
	    RequestTimeout::resetRequestAvailability();
    }

    static function resetLastDeliveryRequestTime($data){
        RequestTimeout::resetDeliveryRequestAvailability($data['profile']);
    }

    /**
     * ����������� ���������� ���
     * @param false $oId
     * @param false $regionTo
     * @param bool $nal
     * @return array
     */
    public static function getChosenPVZ($oId = false, $regionTo = false, $nal = true)
    {
		CModule::IncludeModule('sale');

		$chosen = false;
		$propVal = false;
		$arList = CDeliveryIML::getListFile();

		if (array_key_exists('SelfDelivery', $arList)
            && array_key_exists($regionTo, $arList['SelfDelivery']))
		{
            // got PVZ from request
			if (array_key_exists('pvz', $_REQUEST)
                && $_REQUEST['pvz']
                && $_REQUEST['pvz'] != 'false')
			{
				$chosen = $_REQUEST['pvz'];
			}
			else
            {
                // no request - trying to find in orderProp
				if ($oId)
				{
					$order = CSaleOrder::GetById($oId);
					$personType = $order['PERSON_TYPE_ID'];
				}
				else
                {
                    $personType = $_REQUEST['PERSON_TYPE'];
                }

				$prop = COption::GetOptionString(self::$MODULE_ID, 'pvzPicker', 'ADDRESS');

				if ($prop)
				{
					if ($oId)
					{
						$propVal = CSaleOrderPropsValue::GetList(array(), array('ORDER_ID' => $oId, 'CODE' => $prop))->Fetch();

						if($propVal)
                        {
                            $propVal = $propVal['VALUE'];
                        }
					}
					else
                    {
						$prop = CSaleOrderProps::GetList(array(), array('CODE' => $prop, 'PERSON_TYPE' => $personType))->Fetch();

						if (array_key_exists('ORDER_PROP_'.$prop['ID'], $_REQUEST)
                            && $_REQUEST['ORDER_PROP_' . $prop['ID']])
                        {
                            $propVal = $_REQUEST['ORDER_PROP_' . $prop['ID']];
                        }
						elseif (array_key_exists('order', $_REQUEST)
                            && array_key_exists('ORDER_PROP_' . $prop['ID'], $_REQUEST['order'])
                            && $_REQUEST['order']['ORDER_PROP_' . $prop['ID']])
                        {
                            $propVal = $_REQUEST['order']['ORDER_PROP_' . $prop['ID']];
                        }
					}

					if ($propVal && strpos($propVal, '#L'))
					{
						$propVal = trim(substr($propVal, strpos($propVal, '#L') + 2));

						if (array_key_exists($propVal, $arList['SelfDelivery'][$regionTo]))
						{
                            // choose from property only if that won't make troubles because of paysystem
							if (!$nal
                                || $arList['SelfDelivery'][$regionTo][$propVal]['CASH']
                                || $arList['SelfDelivery'][$regionTo][$propVal]['CARD'])
                            {
                                $chosen = $propVal;
                            }
						}
					}
				}
			}

			if ($chosen)
            {
                return array('RESULT' => 'OK', 'VALUE' => $chosen);
            }
			else
            {
                return array('RESULT' => 'NOT_SELECTED');
            }
		}
		else
        {
            return array('RESULT' => 'ERROR', 'ERROR' => GetMessage('IPOLIML_DELIV_ERROR_NOPVZINREG'));
        }
	}

		// ������ ����� �������
			// ���������� � ������ ����
	public static function calculateTable($profile,$locationFrom,$locationTo,$weight,$arConfig){
		$deliveryTerms = array('SUCCESS' => false);
		$ps = self::checkPS();
		$arProfiles = self::defineProfiles(false,false,false);

		if(
			array_key_exists($profile,$arProfiles) &&
			(
				// checking nal/bnal
				in_array('bnal',$ps)
				||
				(
					in_array('nal',$ps) && $arProfiles[$profile]
				)
			)
		){
			$deliveryTerms['price'] = self::getTablePrice(
				$profile,
				$arConfig,
				array('LOCATION_TO'=>$locationTo,'LOCATION_FROM'=>$locationFrom,'PRICE'=>self::$orderPrice)
			);

			$dT   = self::countDelivTime($locationTo);
			$dT   = (self::checkToday()) ? $dT[0] : $dT[1];
			$deliveryTerms['term'] = date('d',$dT-mktime())+31*(date('m',$dT-mktime())-1);

			$deliveryTerms['SUCCESS'] = true;
		} else {
			$deliveryTerms['ERROR'] = 'table';
		}

		return $deliveryTerms;
	}

			// ���������������� �������
	public static function getTablePrice($profile,$arConfig,$arOrder){
		$region = 'native';
		if(!self::isNative($arOrder['LOCATION_TO'],$arOrder['LOCATION_FROM']))
			$region = 'other';
		if(is_numeric($arConfig[$profile.'_free_'.$region]['VALUE']) && $arOrder['PRICE'] >= $arConfig[$profile.'_free_'.$region]['VALUE'])
			$price = 0;
		else
			$price = (is_numeric($arConfig[$profile.'_price_'.$region]['VALUE']))?$arConfig[$profile.'_price_'.$region]['VALUE']:$arConfig[$profile.'_price_'.$region]['DEFAULT'];
		return $price;
	}

			// ��������: �������� �� ����� "������"
	public static function isNative($to,$from){
		$return = true;
		if($to != $from){
			if(
				method_exists('CSaleLocation','isLocationProEnabled') &&
				CSaleLocation::isLocationProEnabled()
			){
				if(strlen($from) == 10)
					$from = CSaleLocation::getLocationIDbyCODE($from);
				$fromCity = Bitrix\Sale\Location\LocationTable::getList(array('filter'=>array('=ID'=>$from)))->fetch();
				if($fromCity['TYPE_ID'] == 7 && $fromCity['PARENT_ID'])
					$fromCity = Bitrix\Sale\Location\LocationTable::getList(array('filter'=>array('=ID'=>$fromCity['PARENT_ID'])))->fetch();

				if(strlen($to) == 10)
					$to = CSaleLocation::getLocationIDbyCODE($to);
				$toCity = Bitrix\Sale\Location\LocationTable::getList(array('filter'=>array('=ID'=>$to)))->fetch();
				if($toCity['TYPE_ID'] == 7 && $toCity['PARENT_ID'])
					$toCity = Bitrix\Sale\Location\LocationTable::getList(array('filter'=>array('=ID'=>$toCity['PARENT_ID'])))->fetch();

				if($fromCity['ID'] != $toCity['ID'])
					$return = false;
			}
			else
				$return = false;
		}
		return $return;
	}

			// ������ ������
	public static function countDelivTime($city,$orDate=false){
		$cache = new Ipolh\IML\Bitrix\Entity\cache();
		$cachename = "terms|$city|".date("d.m.Y")."|".$orDate;
		if($cache->checkCache($cachename)){
			$arDelivs = $cache->getCache($cachename);
		}else{
			if(
				!file_exists($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/'.self::$MODULE_ID.'/city.json') ||
				!file_exists($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/'.self::$MODULE_ID.'/holidays.json')
			)
				return false;

			if(is_numeric($city)){
				$city = CSaleLocation::GetByID($city);
				$city = $city['CITY_NAME_LANG'];
			}
			$city = self::toUpper($city);

			if(!$orDate)
				$orDate = mktime();

			$cityAr=self::zaDEjsonit(json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/'.self::$MODULE_ID.'/city.json'),true));
			$holidays=self::zaDEjsonit(json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/'.self::$MODULE_ID.'/holidays.json'),true));

			$depCity=COption::GetOptionString(self::$MODULE_ID,'departure',GetMessage('IPOLIML_FRNT_MOSCOWCAPITAL'));
			if(!$depCity)
				return false;

			$mas=array_merge(array('time' => $cityAr[$depCity]),$holidays);
			$mas['settings'] = array(
				"time"  => COption::GetOptionString(self::$MODULE_ID,'timeSend','18').':00',
				"delay" => unserialize(COption::GetOptionString(self::$MODULE_ID,'addHold','a:0:{}')),
				"commonDelay" => intval(COption::GetOptionString(self::$MODULE_ID,'commonHold',''))
			);

			if (!array_key_exists($city,$mas["time"]))
				$mas["time"][$city]=1;

			$dayOfDelive = mktime(0,0,0,date('m',$orDate),date('d',$orDate),date('Y',$orDate));// ���� ��������

		//������������ "�������" � "�����������"
			$holydays=array();

			$startDate = strtotime(date('Y-m-d').", 0:00")-(24*3600*(date('N')-6));
			$one_day = 24*3600;
			$days_to_plus = 7*24*3600;
			for($i=1;$i<=5;$i++){
				if(is_array($mas['deSat']) && !in_array(date('d.m.Y',$startDate),$mas['deSat']))//�� ������� ���������
					$holydays['sat'][] = $startDate;
				if(is_array($mas['deSun']) && !in_array(date('d.m.Y',$startDate+$one_day),$mas['deSun'])){//�� ������� ���������
					$holydays['sat'][] = $startDate+$one_day;
					$holydays['sun'][] = $startDate+$one_day;
				}
				$startDate += $days_to_plus;
			}
			//����������� ��������� (����������� �� �������� ������ � �����������)
			if(array_key_exists('days',$mas) && is_array($mas['days']))
			{
				if(array_key_exists('sat',$mas['days']) && is_array($mas['days']['sat']) && count($mas['days']['sat'])){
					foreach($mas['days']['sat'] as $datstr)
						$holydays['sat'][]=strtotime($datstr);
				}
				if(array_key_exists('sun',$mas['days']) && is_array($mas['days']['sun']) && count($mas['days']['sun'])){
					foreach($mas['days']['sun'] as $datstr){
						$holydays['sat'][]=strtotime($datstr);
						$holydays['sun'][]=strtotime($datstr);
					}
				}
			}
	// echo "����� ".date("d.m.Y D",$dayOfDelive)."<br>";
		//���� �������� ������

			$arDelivs = array($dayOfDelive);

			//���� �� ���������� ������� - ����� ����������?
			$dayOfDelive+=$one_day;
			While(1){
				if(in_array($dayOfDelive, $holydays['sat']))//��������� �������� �� ���
					$dayOfDelive+=$one_day;//���� �������� �������� ����
				else
					break;
			}

			$arDelivs[] = $dayOfDelive;
			if(in_array($dayOfDelive,$holydays['sat']))
				$dayOfDelive[0] = $dayOfDelive[1];


			foreach($arDelivs as $key => $dayOfDelive){
				$startDeliv = $dayOfDelive;
		// echo "�������� ".date("d.m.Y D",$dayOfDelive)."<br>";
			//���� ������ ������
				$dayOfDelive+=$mas["time"][$city]*$one_day;

				// �����������
				foreach($holydays['sun'] as $day)
					if($startDeliv < $day && $dayOfDelive > $day)
						$dayOfDelive+=$one_day;
				if (array_key_exists($city,$mas["settings"]["delay"]))  //���� ���� ��� ����� [�������������� �����]
					$dayOfDelive+=$mas["settings"]["delay"][$city]*$one_day;
				// ���� ���� ����� �����
				if($mas['settings']["commonDelay"] > 0)
					$dayOfDelive+=$mas['settings']["commonDelay"]*$one_day;
		// echo "����� ".date("d.m.Y D",$dayOfDelive)."<br>";
			//����� �� ����� ������ �������?
				While(1){
					if(in_array($dayOfDelive, $holydays['sun']))
						$dayOfDelive+=$one_day;
					else
						break;
				}
				$arDelivs[$key] = $dayOfDelive;
		// echo "���� ".date("d.m.Y D",$dayOfDelive)."<br>";
			}
			$cache->setCache($cachename,$arDelivs);
		}
		// return $dayOfDelive; //mkTime-�����
		return $arDelivs; //mkTime-�����
	}

			// ����� �� ��������� ������ ������� �� �����_�����_������_��������
	public static function checkToday($orDate = false){
		$time = COption::GetOptionString(self::$MODULE_ID,'timeSend','18').':00:00';
		if($time==':00:00')
			$time='18:00:00';
		$timeToSend['H']=date('H', strtotime($time));
		$timeToSend['i']=date('i', strtotime($time));

		if(!$orDate)
			$orDate = mktime();
		//�� ������� ��������� ������
		return (mktime($timeToSend['H'],$timeToSend['i']) > mktime(date('H',$orDate),date('i',$orDate)));
	}

		// ������

	public static function getDeliveryId($profile,$sep=":"){
		$profiles = array();
		if(self::isConverted()){
			$dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
				 'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
				 'filter' => array('CODE' => 'iml:'.$profile)
			));
			while($dPS = $dTS->Fetch())
				$profiles[]=$dPS['ID'];
		}else
			$profiles = array('iml'.$sep.'pickup');
		return $profiles;
	}

	public static function getIMLCity($id,$noSetup = false){
        $city = \Ipolh\IML\Bitrix\Handler\Locations::getByBitrixId($id);

//		$cityId = self::getNormalCity($id);
//		$cityId = ($cityId) ? $cityId : $id;
//		$city = CSaleLocation::GetByID($cityId); //�� ������ �����, ���� ��� ���

        $linker = new \Ipolh\IML\Bitrix\Controller\regionCity();
        $arFind = $linker->getCity($city);

        if($arFind['NAME'] === GetMessage('IPOLIML_LBL_OREL_YO')){
            $arFind['NAME'] = GetMessage('IPOLIML_LBL_OREL_YE');
        }

        if($arFind){
            if($noSetup) {
                return array(
                    'region'  => $arFind['REGION_IML'],
                    'city' => $arFind['NAME']
                );
            } else {
                self::$city        = $arFind['REGION_IML'];
                self::$addressCity = $arFind['NAME'];
            }

            return true;
        }

        return false;
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												�������� ���� ����� � ����������� �����
		== checkNalD2P ==  == checkNalP2D ==  == checkPS ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	public static function checkNalD2P(&$arResult,$arUserResult,$arParams=array()){
		$profile = self::defineDelivery($arUserResult['DELIVERY_ID']);
		if(
			$arParams['DELIVERY_TO_PAYSYSTEM'] == 'd2p' &&
			$profile &&
			COption::GetOptionString(self::$MODULE_ID,"hideNal","Y") == 'Y'
		){

			$arBesnalPaySys = unserialize(COption::GetOptionString(self::$MODULE_ID,'paySystems','a:{}'));
			if(!self::$profiles[$profile]){
				foreach($arResult['PAY_SYSTEM'] as $id => $payDescr) {
                    if (!in_array($payDescr['ID'], $arBesnalPaySys))
                        unset($arResult['PAY_SYSTEM'][$id]);
                }
                sort($arResult['PAY_SYSTEM']);
			}
		}
		if($arParams['DELIVERY_TO_PAYSYSTEM'] == 'd2p' && $arUserResult['PAY_SYSTEM_ID']){
			self::$paysystem = $arUserResult['PAY_SYSTEM_ID'];
		}
	}

	public static function checkNalP2D(&$arResult,$arUserResult,$arParams=array()){
		if($arParams['DELIVERY_TO_PAYSYSTEM'] == 'p2d'){
			if(
				count($arParams) &&
				COption::GetOptionString(self::$MODULE_ID,"hideNal","Y") == 'Y'
			){
				$arBesnalPaySys = unserialize(COption::GetOptionString(self::$MODULE_ID,'paySystems','a:{}'));
				if(!in_array($arUserResult['PAY_SYSTEM_ID'],$arBesnalPaySys))
					self::$nalPayChosen = true;
			}
			self::$paysystem = $arUserResult['PAY_SYSTEM_ID'];
		}
	}

		// �������� ��������� ������: ���� ������ - ������ ������ ��������
	public static function checkPS($CPS=false){
		if(!$CPS){
			if(array_key_exists('PAY_SYSTEM_ID',$_REQUEST)){
				$CPS = $_REQUEST['PAY_SYSTEM_ID'];
			} elseif(array_key_exists('order',$_REQUEST) && is_array($_REQUEST['order']) && array_key_exists('PAY_SYSTEM_ID',$_REQUEST['order'])){
				$CPS = $_REQUEST['order']['PAY_SYSTEM_ID'];
			} else{
				$CPS = false;
			}
		}

		$bNalPSyS = unserialize(COption::GetOptionString(self::$MODULE_ID,'paySystems','a:{}'));

		if($CPS)
			$arRet[] = (in_array($CPS,$bNalPSyS)) ? 'bnal' : 'nal';
		else
			$arRet = array('bnal','nal');

		return $arRet;
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												������ ���������� ������
		== countDelivery ==  == cntDelivsOld ==  == cntDelivsConverted ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	public static function countDelivery($arOrder){
		cmodule::includeModule('sale');
		if($arOrder['action']) $arOrder['cityTo'] = self::zaDEjsonit($arOrder['cityTo']);
		$arOrder['cityTo'] = CSaleLocation::getList(array(),array('CITY_NAME'=>$arOrder['cityTo']))->Fetch();
		if($arOrder['cityTo']){
			$_SESSION['IPOLIML_city'] = $arOrder['cityTo']['ID'];
			$arOrder['cityTo'] = $arOrder['cityTo']['ID'];
		}
		$arOrder['cityFrom'] = CSaleLocation::getList(array(),array("CITY_NAME" => COption::getOptionString(self::$MODULE_ID,'departure')))->Fetch();
		if($arOrder['cityFrom'])
			$arOrder['cityFrom'] = $arOrder['cityFrom']['ID'];

		$arProfiles = (self::isConverted()) ? self::cntDelivsConverted($arOrder) : self::cntDelivsOld($arOrder);

		$arReturn = array(
				'courier' => ($arProfiles['courier']['calc']) ? $arProfiles['courier']['calc'] : 'no',
				'pickup'  => ($arProfiles['pickup']['calc'])  ? $arProfiles['pickup']['calc']  : 'no',
				'date'    => CDeliveryIML::$date
			);

		if($arOrder['action'])
			echo json_encode(self::zajsonit($arReturn));
		else
			return $arReturn;
	}

	public static function cntDelivsOld($arOrder){//������ ���� � ��������� �������� ��� �������
		$pseudoOrder = array(
			"LOCATION_TO"   => $arOrder['cityTo'],
			"LOCATION_FROM" => $arOrder['cityFrom'],
			"PRICE"         => $arOrder['price'],
			"WEIGHT"        => $arOrder['weight']
		);

		if(array_key_exists('gabs',$arOrder) && is_array($arOrder['gabs'])){
            $pseudoOrder['ITEMS'] = array(
                \Ipolh\IML\Bitrix\Tools::makeSimpleGood(array(
                    'QUANTITY'   => 1,
                    'WIDTH'  => $arOrder['gabs']['W'],
                    'HEIGHT' => $arOrder['gabs']['H'],
                    'LENGTH' => $arOrder['gabs']['L'],
                    'WEIGHT' => $arOrder['weight'],
                    'PRICE'  => $arOrder['price'],
                    'BASE_PRICE' => $arOrder['price'],
                    'ID' => 'test'
                ))
            );
        }


		$arHandler = CSaleDeliveryHandler::GetBySID('iml')->Fetch();
		$arProfiles = CSaleDeliveryHandler::GetHandlerCompability($pseudoOrder,$arHandler);

		foreach($arProfiles as $profName => $someArray){
			self::$serviceData['SERVICE'] = ($profName == 'pickup') ? 'C24' : '24';
			if(
				(!array_key_exists('pay',$arOrder) || $arOrder['pay'] != 'bnal') &&
				(!array_key_exists('PAY_SYSTEM_ID',$arOrder) || !empty(array_diff(self::checkPS($arOrder['PAY_SYSTEM_ID']),array('bnal'))))
			)
				self::$serviceData['SERVICE'] .= 'KO';

			$calc = CSaleDeliveryHandler::CalculateFull('iml',$profName,$pseudoOrder,"RUB");
			if($calc['RESULT'] != 'ERROR')
				$arProfiles[$profName]['calc'] = ($calc['VALUE'])?CCurrencyLang::CurrencyFormat($calc['VALUE'],'RUB',true):GetMessage("IPOLIML_FREEDELIV");
		}

		return $arProfiles;
	}

	public static function cntDelivsConverted($arOrder){
		$basket = Bitrix\Sale\Basket::create(SITE_ID);
		$basketItem = Bitrix\Sale\BasketItem::create($basket,self::$MODULE_ID,1);

		$arGood = array(
			"QUANTITY"   => 1,
			"PRICE"      => ($arOrder['price'])  ? $arOrder['price']  : self::$orderPrice,
			"WEIGHT"     => ($arOrder['weight']) ? $arOrder['weight'] : self::$orderWeight,
			"DIMENSIONS" => (array_key_exists('gabs',$arOrder) && is_array($arOrder['gabs'])) ? serialize(array(
			    'WIDTH'  => $arOrder['gabs']['W'],
                'HEIGHT' => $arOrder['gabs']['H'],
                'LENGTH' => $arOrder['gabs']['L']
            )) : 'a:3:{s:5:"WIDTH";i:0;s:6:"HEIGHT";i:0;s:6:"LENGTH";i:0;}',
			'DELAY'=>'N','CAN_BUY'=>'Y','CURRENCY'=>'RUB','RESERVED'=>'N','NAME'=>'testGood','SUBSCRIBE'=>'N'
		);

		$basketItem->initFields($arGood);
		$basket->addItem($basketItem);

		$order = Bitrix\Sale\Order::create(SITE_ID);
		$order->setBasket($basket);
		$propertyCollection = $order->getPropertyCollection();
		$locVal = CSaleLocation::getLocationCODEbyID($arOrder['cityTo']);
		$arProps = array();
		foreach($propertyCollection as $property){
			$arProperty = $property->getProperty();
			if($arProperty["TYPE"] == 'LOCATION')
				$arProps[$arProperty["ID"]] = $locVal;
		}
		$propertyCollection->setValuesFromPost(array('PROPERTIES'=>$arProps),array());

		if($arOrder['PERSON_TYPE_ID']){
			$order->setField('PERSON_TYPE_ID',$arOrder['PERSON_TYPE_ID']);
			if(!self::$payerType)
				self::$payerType = $arOrder['PERSON_TYPE_ID'];
		}

		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem();
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		$shipment->setField('CURRENCY', $order->getCurrency());
		foreach ($order->getBasket() as $item){
			$shipmentItem = $shipmentItemCollection->createItem($item);
			$shipmentItem->setQuantity($item->getQuantity());
		}

		if($arOrder['PAY_SYSTEM_ID']){
			$paymentCollection = $order->getPaymentCollection();
			$payment = $paymentCollection->createItem();
			$psService = \Bitrix\Sale\PaySystem\Manager::getObjectById($arOrder['PAY_SYSTEM_ID']);
			$paymentFields = array(
				'PAY_SYSTEM_ID' => $arOrder['PAY_SYSTEM_ID'],
				'COMPANY_ID' => 0,
				'PAY_VOUCHER_NUM' => '',
				'PAY_RETURN_NUM' => '',
				'PAY_RETURN_COMMENT' => '',
				'COMMENTS' => '',
				'PAY_SYSTEM_NAME' => ($psService) ? $psService->getField('NAME') : ''
			);
			$payment->setFields($paymentFields);
			$payment->setField('SUM', $order->getPrice());

			if(!self::$paysystem){
				self::$paysystem = $arOrder['PAY_SYSTEM_ID'];
			}
		}


		$arShipments = array();
		$arDeliveryServiceAll = Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);

		if(array_key_exists('DELIVERY',$arOrder) && $arOrder['DELIVERY'] && !self::defineDelivery($arOrder['DELIVERY']))
			$arOrder['DELIVERY'] = false;

		foreach($arDeliveryServiceAll as $id => $deliveryObj){
			if(
				$deliveryObj->isProfile()  &&
				method_exists($deliveryObj->getParentService(),'getSid') &&
				$deliveryObj->getParentService()->getSid() == 'iml'
			){
				$profName = self::defineDelivery($id);
				if(array_key_exists('DELIVERY',$arOrder) && $arOrder['DELIVERY'] && $arOrder['DELIVERY'] != $id) continue;
				$resCalc = Bitrix\Sale\Delivery\Services\Manager::calculateDeliveryPrice($shipment,$id);
				if(
					$resCalc->isSuccess() &&
					(
						!array_key_exists($profName,$arShipments) ||
						!array_key_exists('calc',$arShipments[$profName])
					)
				){
					$arShipments[$profName]['calc'] = ($resCalc->getDeliveryPrice()) ? CCurrencyLang::CurrencyFormat($resCalc->getDeliveryPrice(),'RUB',true):GetMessage("IPOLIML_FREEDELIV");
				}
			}
		}

		return $arShipments;
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												������ � ���
		== pickupLoader ==  == onBufferContent ==  == no_json ==  == cntPVZ ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

		// what delivery was selected
	public static  $selDeliv = '';

		// including PVZ-widjet
	public static function pickupLoader($arResult,$arUR,$arParams=array()){
		if(!self::isActive()) return;
		self::$orderWeight = $arResult['ORDER_WEIGHT'];
		self::$orderPrice  = $arResult['ORDER_PRICE'];
		try{
            $obCargo = self::getCargo($arResult['BASKET_ITEMS']);

            self::$orderGabs   = $obCargo->getCargo()->getDimensions();
        }catch (\Exception $e){
            self::$orderGabs = false;
        }

        self::getIMLCity($arUR['DELIVERY_LOCATION']);

		self::$selDeliv = $arUR['DELIVERY_ID'];
		if(!is_array($arParams))
			$arParams = array();
		if($_REQUEST['is_ajax_post'] != 'Y' && $_REQUEST["AJAX_CALL"] != 'Y' && !$_REQUEST["ORDER_AJAX"]){
			if(COption::GetOptionString(self::$MODULE_ID,'noYmaps','N') || defined('BX_YMAP_SCRIPT_LOADED') || defined('IPOL_YMAPS_LOADED'))
				$arParams['NOMAPS'] = 'Y';
			elseif(!array_key_exists('NOMAPS',$arParams) || $arParams['NOMAPS'] != 'Y')
				define('IPOL_YMAPS_LOADED',true);

			if(!array_key_exists('PAYER',$arParams) && $arUR['PERSON_TYPE_ID']){
				$arParams['PAYER'] = $arUR['PERSON_TYPE_ID'];
			} else {
				$arParams['PAYER'] = false;
			}
			if(!array_key_exists('PAYSYSTEM',$arParams) && $arUR['PAY_SYSTEM_ID']){
				$arParams['PAYSYSTEM'] = $arUR['PAY_SYSTEM_ID'];
			} else {
				$arParams['PAYSYSTEM'] = false;
			}

			$arParams['NO_POSTOMAT'] = COption::GetOptionString(self::$MODULE_ID,'noPostomat','N');
			$arParams['FILTERSOFF']  = COption::GetOptionString(self::$MODULE_ID,'FILTERSOFF','N');

			$filtered = COption::GetOptionString(self::$MODULE_ID,'FILTERDEFAULT','');
			if($filtered){
                $arParams['FILTERDEFAULT'] = unserialize($filtered);
            }

			$GLOBALS['APPLICATION']->IncludeComponent("ipol:ipol.imlPickup", "order", array_merge($arParams,array("LOAD_ACTUAL_PVZ"=>'Y')),false);
		}
	}

	// Function is called before putting html in browser - adding data 4 widjet
	public static function onBufferContent(&$content) {
		if(self::$city && self::isActive()){
			$noJson = self::no_json($content);
			if(($_REQUEST['is_ajax_post'] == 'Y' || $_REQUEST["AJAX_CALL"] == 'Y' || $_REQUEST["ORDER_AJAX"]) && $noJson){
				$content .= '<input type="hidden" id="iml_city"   name="iml_city"   value=\''.self::$city.'\' />';//��������� �����
				$content .= '<input type="hidden" id="iml_dostav"   name="iml_dostav"   value=\''.self::$selDeliv.'\' />';//��������� ��������� ������� ��������
				$content .= '<input type="hidden" id="iml_checkPS"   name="iml_checkPS"   value=\''.self::$psChecks.'\' />';//��������� ��� ��������� �������
				$content .= '<input type="hidden" id="iml_payer" name="iml_payer" value=\''.self::$payerType.'\' />';//��������� �����������
				$content .= '<input type="hidden" id="iml_paysystem" name="iml_paysystem" value=\''.self::$paysystem.'\' />';//��������� ��������� �������
			}elseif(($_REQUEST['soa-action'] == 'refreshOrderAjax' || $_REQUEST['action'] == 'refreshOrderAjax') && !$noJson)
				$content = substr($content,0,strlen($content)-1).',"iml":{"city":"'.self::zajsonit(self::$city).'","dostav":"'.self::$selDeliv.'","checkPS":"'.self::$psChecks.'","payer":"'.self::$payerType.'","paysystem":"'.self::$paysystem.'"}}';
		}
	}
	public static function no_json($wat){
		return is_null(json_decode(self::zajsonit($wat),true));
	}

	function cntPVZ($params){
		unset($params['action']);
		$params = self::zaDEjsonit($params);
		$result = self::countDelivery($params);
		echo json_encode(self::zajsonit(array(
			'city'  => $params['cityTo'],
			'pvz'   => $params['pvz'],
			'price' => $result['pickup']
		)));
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												���������� ���������� ������ ��� ���
		== noPVZOldTemplate ==  == noPVZNewTemplate ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	public static function noPVZOldTemplate(&$arResult,&$arUserResult){
		if(
			$arUserResult['CONFIRM_ORDER'] == 'Y' &&
			COption::GetOptionString(self::$MODULE_ID,'noPVZnoOrder','N') == 'Y' &&
			self::defineDelivery($arUserResult['DELIVERY_ID']) == 'pickup' &&
			self::isActive()
		){
			if($propAddr = COption::GetOptionString(self::$MODULE_ID,'pvzPicker','')){
				$checked = 1;
				$props = CSaleOrderProps::GetList(array(),array('CODE' => $propAddr));
				while($prop=$props->Fetch()){
					if(array_key_exists($prop['ID'],$arUserResult['ORDER_PROP'])){
						if(strpos($arUserResult['ORDER_PROP'][$prop['ID']],'#L') === false && $checked != 2)
							$checked = 0;
						else
							$checked = 2;
					}
				}
				if($checked === 0)
				{
					$arResult['ERROR'] []= GetMessage('IPOLIML_DELIV_ERROR_NOPVZ');
				}
			}
		}
	}

	public static function noPVZNewTemplate($entity,$values){
		if(
            !$entity->getId() &&
            (!defined('ADMIN_SECTION') || ADMIN_SECTION === false) &&
            self::isActive() &&
			COption::GetOptionString(self::$MODULE_ID,'noPVZnoOrder','N') == 'Y' &&
			cmodule::includeModule('sale')
        ) {
			if($propAddr = COption::GetOptionString(self::$MODULE_ID,'pvzPicker','')){
				$props = CSaleOrderProps::GetList(array(),array('CODE' => $propAddr));
				$arPVZPropsIds = array();
				while($element=$props->Fetch()){
					$arPVZPropsIds []= $element['ID'];
				}
				if(!empty($arPVZPropsIds)){
					$orderProps = $entity->getPropertyCollection()->getArray();
					$checked = 1;
					foreach($orderProps['properties'] as $propVals){
						if(in_array($propVals['ID'],$arPVZPropsIds)){
							if(strpos($propVals['VALUE'][0],'#L') === false && $checked != 2)
								$checked = 0;
							else
								$checked = 2;
						}
					}
					if($checked == 0){
						$shipmentCollection = $entity->getShipmentCollection();
						foreach ($shipmentCollection as $something => $shipment) {
							if ($shipment->isSystem())
								continue;

							$delivery = self::defineDelivery($shipment->getField('DELIVERY_ID'));
							if ($delivery === 'pickup') {
								return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError(GetMessage('IPOLIML_DELIV_ERROR_NOPVZ'), 'code'), 'sale');
							}
						}
					}
				}
            }
		}
	}
}
?>
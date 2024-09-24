<?
/*****************************************
*** Delivery handler for Dalli-Service ***
*****************************************/
CModule::IncludeModule('sale');
CModule::IncludeModule('dalliservicecom.delivery');
IncludeModuleLangFile(__FILE__);
CJSCore::Init(array('dalliservicecom'));?>
<?class CDeliveryDalliServicecom
{
    const MODULE_ID = 'dalliservicecom.delivery';
    static $pvzArr = array();
    static $region = '';
    static $city = '';

    function Init()
    {
        return array(
            /* Basic description */
            'SID' => 'dalli_service',
            'NAME' => GetMessage('DS_HANDLER_NAME'),
            'DESCRIPTION' => GetMessage('DS_HANDLER_DESCR'),
            'DESCRIPTION_INNER' => GetMessage('DS_HANDLER_DESCR'),
            'BASE_CURRENCY' => 'RUB',
            'HANDLER' => __FILE__,
            /* Handler methods */
            'DBGETSETTINGS' => array('CDeliveryDalliServicecom', 'GetSettings'),
            'DBSETSETTINGS' => array('CDeliveryDalliServicecom', 'SetSettings'),
            'GETCONFIG' => array('CDeliveryDalliServicecom', 'GetConfig'),
            'COMPABILITY' => array('CDeliveryDalliServicecom', 'Compability'),
            'CALCULATOR' => array('CDeliveryDalliServicecom', 'Calculate'),
            /* List of delivery profiles */
            'PROFILES' => array(
                'dalli_courier' => array(
                    'TITLE' => GetMessage('DS_COURIER_NAME'),
                    'DESCRIPTION' => GetMessage('DS_COURIER_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'dalli_express' => array(
                    'TITLE' => GetMessage('DS_COURIER_EXPRESS'),
                    'DESCRIPTION' => GetMessage('DS_COURIER_EXPRESS'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'dalli_pvz' => array(
                    'TITLE' => GetMessage('DS_PVZ_NAME'),
                    'DESCRIPTION' => GetMessage('DS_PVZ_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'dalli_cfo' => array(
                    'TITLE' => GetMessage('DS_COURIER_CFO'),
                    'DESCRIPTION' => GetMessage('DS_COURIER_CFO'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'sdek_courier' => array(
                    'TITLE' => GetMessage('SDEK_COURIER_NAME'),
                    'DESCRIPTION' => GetMessage('SDEK_COURIER_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'sdek_pvz' => array(
                    'TITLE' => GetMessage('SDEK_PVZ_NAME'),
                    'DESCRIPTION' => GetMessage('SDEK_PVZ_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'boxberry_pvz' => array(
                    'TITLE' => GetMessage('BOXBERRY_PVZ_NAME'),
                    'DESCRIPTION' => GetMessage('BOXBERRY_PVZ_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                '5post_pvz' => array(
                    'TITLE' => GetMessage('5POST_PVZ_NAME'),
                    'DESCRIPTION' => GetMessage('5POST_PVZ_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'pickpoint_pvz' => array(
                    'TITLE' => GetMessage('PICKPOINT_PVZ_NAME'),
                    'DESCRIPTION' => GetMessage('PICKPOINT_PVZ_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'pochta_19_1' => array(
                    'TITLE' => GetMessage('POCHTA_19_1_NAME'),
                    'DESCRIPTION' => GetMessage('POCHTA_19_1_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'pochta_19_2' => array(
                    'TITLE' => GetMessage('POCHTA_19_2_NAME'),
                    'DESCRIPTION' => GetMessage('POCHTA_19_2_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'pochta_19_3' => array(
                    'TITLE' => GetMessage('POCHTA_19_3_NAME'),
                    'DESCRIPTION' => GetMessage('POCHTA_19_3_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                ),
                'pochta_19_4' => array(
                    'TITLE' => GetMessage('POCHTA_19_4_NAME'),
                    'DESCRIPTION' => GetMessage('POCHTA_19_4_DESCR'),
                    'RESTRICTIONS_WEIGHT' => array(),
                    'RESTRICTIONS_SUM' => array()
                )
            )
        );
    }

    function GetConfig()
    {
        $arConfig = array(
            "CONFIG_GROUPS" => array(),
            "CONFIG" => array(),
        );
        return $arConfig;
    }

    function GetSettings($strSettings)
    {
        return unserialize($strSettings);
    }

    function SetSettings($arSettings)
    {
        foreach ($arSettings as $key => $value) {
            if (strlen($value) > 0)
                $arSettings[$key] = $value;
            else
                unset($arSettings[$key]);
        }

        return serialize($arSettings);
    }

    function Compability($arOrder, $arConfig)
    {
        if (!$GLOBALS['arrDalliProfiles']):
            global $arrDalliProfiles;
            $token = COption::GetOptionString(self::MODULE_ID, 'DALLI_TOKEN');
            if (DalliservicecomDelivery::checkToken($token) == false)
                return array();
            $arrDalliProfiles = array();
            $arLocationTo = CSaleLocation::GetByID($arOrder['LOCATION_TO']);
            self::$region = $arLocationTo["REGION_NAME"];
            $db_vars = CSaleLocation::GetList(
                array(
                    "CITY_NAME_LANG" => "ASC"
                ),
                array("LID" => LANGUAGE_ID, "CODE" => (string)$arOrder['LOCATION_TO']),
                false,
                false,
                array()
            );
            while ($vars = $db_vars->Fetch()) {
                self::$city = ($vars['CITY_NAME']);
            }
            if (strpos(self::$region, GetMessage('MOSKOW_REGION')) !== false || strpos(self::$region, GetMessage('LENINGRAD_REGION')) !== false
                || $arLocationTo["CITY_NAME"] == GetMessage('MOSKOW') || $arLocationTo["CITY_NAME"] == GetMessage('LENINGRAD') || $arLocationTo["CITY_NAME"] == GetMessage('ZELENOGRAD'))
                $arrDalliProfiles[] = 'dalli_courier';  //$arLocationTo["CITY_NAME"] - название ближайшего крупного города, а self::$city - точное название нас. пункта, например Пушкин
            if(self::$city == GetMessage('MOSKOW') ){
                $arrDalliProfiles[] = 'dalli_express';
            }
            $res = DalliservicecomDeliveryDB::GetPvzList(array('town' => self::$city, 'partner' => 'DS'));
            if ($res->SelectedRowsCount() > 0) {
                while ($point = $res->Fetch()) {
                    $pvzArr['DS']['points'][] = $point;
                }
                $arrDalliProfiles[] = 'dalli_pvz';
                if (!in_array('dalli_courier', $arrDalliProfiles)) {
                    $arrDalliProfiles[] = 'dalli_cfo';
                }

            }
            $res = DalliservicecomDeliveryDB::GetPvzList(array('town' => self::$city, 'partner' => 'SDEK'));
            if ($res->SelectedRowsCount() > 0) {
                while ($point = $res->Fetch()) {
                    $point['description'] = preg_replace('@((https?//)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@', '', $point['description']);
                    $pvzArr['SDEK']['points'][] = $point;
                }

                $arrDalliProfiles[] = 'sdek_courier';
                $arrDalliProfiles[] = 'sdek_pvz';
            }
            $arrDalliProfiles[] = 'pochta_19_1';
            $arrDalliProfiles[] = 'pochta_19_2';
            $arrDalliProfiles[] = 'pochta_19_3';
            $arrDalliProfiles[] = 'pochta_19_4';

            $res = DalliservicecomDeliveryDB::GetPvzList(array('town' => self::$city, 'partner' => 'BOXBERRY'));
            if ($res->SelectedRowsCount() > 0) {
                while ($point = $res->Fetch()) {
                    $point['Street'] = str_replace('"', '', $point['Street']);
                    $pvzArr['BOXBERRY']['points'][] = $point;
                }

                $arrDalliProfiles[] = 'boxberry_pvz';
            }
            $res = DalliservicecomDeliveryDB::GetPvzList(array('town' => self::$city.' '.GetMessage('TOWN'), 'partner' => '5POST'));
            if ($res->SelectedRowsCount() > 0) {
                while ($point = $res->Fetch()) {
                    $point['Street'] = str_replace('"', '', $point['Street']);
                    $pvzArr['5POST']['points'][] = $point;
                }

                $arrDalliProfiles[] = '5post_pvz';
            }
            $res = DalliservicecomDeliveryDB::GetPvzList(array('town' => self::$city, 'partner' => 'PICKPOINT'));
            if ($res->SelectedRowsCount() > 0) {
                while ($point = $res->Fetch()) {
                    $pvzArr['PICKPOINT']['points'][] = $point;
                }
                $arrDalliProfiles[] = 'pickpoint_pvz';
            }
            self::$pvzArr = $pvzArr;
        endif;
        return $GLOBALS['arrDalliProfiles'];

    }

    function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
    {
        $token = COption::GetOptionString(self::MODULE_ID, 'DALLI_TOKEN');
        $separateAddr = COption::GetOptionString(self::MODULE_ID, 'SEPARATE_ADDR') == 'Y' ? true : false;
        $autoDefineZone = COption::GetOptionString(self::MODULE_ID, 'AUTO_DEFINE_ZONE') == 'Y' ? true : false;
        $arLocationTo = CSaleLocation::GetByID($arOrder['LOCATION_TO'], 'ru');
        $pvzArr = self::$pvzArr;
        $linkShowPvz = '';
        $partner = '';
        $type = '';
        $city = '';
        $paySystemId = isset($_POST['order']['PAY_SYSTEM_ID']) ? $_POST['order']['PAY_SYSTEM_ID'] : $_POST['PAY_SYSTEM_ID'];
        $payTypeCash = unserialize(COption::GetOptionString(self::MODULE_ID, 'PAYTYPE_CASH'));
        $payTypeCard = unserialize(COption::GetOptionString(self::MODULE_ID, 'PAYTYPE_CARD'));
        $payTypeNo = unserialize(COption::GetOptionString(self::MODULE_ID, 'PAYTYPE_NO'));

        $weight = $arOrder['WEIGHT'] / 1000;
        if ($weight == '' || $weight == 0)
            $weight = COption::GetOptionString(self::MODULE_ID, 'WEIGHT_DEFAULT');
        foreach ($arOrder['ITEMS'] as $item) {
            $width += $item['DIMENSIONS']['WIDTH'];
            $length += $item['DIMENSIONS']['LENGTH'];
            $height += $item['DIMENSIONS']['HEIGHT'];
        }
        if(!$width) {
            $width = 1;
        }
        else{
            $width /= 10;  //мм в см
        }
        if(!$length) {
            $length = 1;
        }
        else{
            $length /= 10;
        }
        if(!$height) {
            $height = 1;
        }
        else{
            $height /= 10;
        }
        $price = $arOrder['PRICE'];
        $x2 = '';
        if (in_array($profile, ['dalli_courier', 'dalli_cfo'])) {
            $partner = 'DS';
            $type = 'KUR';
        } elseif ($profile == 'dalli_pvz') {
            $partner = 'DS';
            $type = 'PVZ';
        } elseif ($profile == 'sdek_courier') {
            $partner = 'SDEK';
            $type = 'KUR';
        } elseif ($profile == 'sdek_pvz') {
            $partner = 'SDEK';
            $type = 'PVZ';
        } elseif ($profile == 'boxberry_pvz') {
            $partner = 'BOXBERRY';
            $type = 'PVZ';
        } elseif ($profile == '5post_pvz') {
            $partner = '5POST';
            $type = 'PVZ';
        } elseif ($profile == 'pickpoint_pvz') {
            $partner = 'PICKPOINT';
            $type = 'PVZ';
        } elseif (($profile === 'pochta_19_1') || ($profile === 'pochta_19_2') || ($profile === 'pochta_19_3') || ($profile === 'pochta_19_4')) {
            $partner = 'RUPOST';
            $type = 'KUR';
        } elseif ($profile == 'dalli_express'){
            $partner = 'DS';
            $type = 'KUR';
            $x2 = '<output>x2</output>';
        }
            if ($autoDefineZone && $partner == 'DS' && $type == 'KUR' && $profile != 'dalli_express') {
                if ($separateAddr) {
                    $propStreet = COption::GetOptionString(self::MODULE_ID, 'STREET');
                    $propHouse = COption::GetOptionString(self::MODULE_ID, 'HOUSE');
                    $addrProps = array();

                    $dbProps = CSaleOrderProps::GetList(
                        array("SORT" => "ASC"),
                        array("CODE" => array($propStreet, $propHouse)),
                        false,
                        false,
                        array()
                    );

                    while ($prop = $dbProps->Fetch()) {
                        $addrProps[$prop['CODE']] = $prop['ID'];
                    }

                    $city = $arLocationTo["CITY_NAME"] . ', ' . ($_POST['ORDER_PROP_' . $addrProps['STREET']] ? $_POST['ORDER_PROP_' . $addrProps['STREET']] : $_POST['order']['ORDER_PROP_' . $addrProps['STREET']]) . ', ' . ($_POST['ORDER_PROP_' . $addrProps['HOUSE']] ? $_POST['ORDER_PROP_' . $addrProps['HOUSE']] : $_POST['order']['ORDER_PROP_' . $addrProps['HOUSE']]);
                } else {
                    $dbProps = CSaleOrderProps::GetList(
                        array("SORT" => "ASC"),
                        array("CODE" => array('ADDRESS')),
                        false,
                        false,
                        array()
                    );

                    if ($prop = $dbProps->Fetch()) {
                        $city = $arLocationTo["CITY_NAME"] . ', ' . ($_POST['ORDER_PROP_' . $prop['ID']] ? $_POST['ORDER_PROP_' . $prop['ID']] : $_POST['order']['ORDER_PROP_' . $prop['ID']]);
                    }
                }
            } else {
                $db_vars = CSaleLocation::GetList(
                    array(
                        "CITY_NAME_LANG" => "ASC"
                    ),
                    array("LID" => LANGUAGE_ID, "CODE" => (string)$arOrder['LOCATION_TO']),
                    false,
                    false,
                    array()
                );
                while ($vars = $db_vars->Fetch()) {
                    $city = ($vars['CITY_NAME']);
                }
            }
            $region = $arLocationTo["REGION_NAME"];


            $cashServices = ($partner == "DS" && $price > 0 && in_array($paySystemId, $payTypeCard) ? "YES" : "NO");

            if (in_array($paySystemId, $payTypeNo)) {
                $price = 0;
            }
            if((COption::GetOptionString(self::MODULE_ID, 'WITHOUT_TAX')==='YES')&&($partner==='DS'))
            {
                $tax = '<withouttax>YES</withouttax>';
            }
            else
            {
                $tax="";
            }
            if($partner==='RUPOST'){
                $xml_data = "<?xml version='1.0' encoding='UTF-8'?>
            <deliverycost>
            <auth token='" . $token . "'></auth>
            <partner>RUPOST</partner>
            <to>$city</to>
            <weight>".(!empty($weight)?$weight:'0.1')."</weight>
            <price>$price</price>
            <inshprice>" . $price . "</inshprice>
            <length>$length</length>
            <width>$width</width>
            <height>$width</height>
            <output>x2</output>
            <typedelivery>KUR</typedelivery>
            </deliverycost>";
                $arResult = DalliservicecomDelivery::send_xml($xml_data);
                $type = substr($profile, -1, 1);
                foreach($arResult['deliverycost']['#']['price'] as $delivery){
                    if($type==$delivery['@']['type']) {
                        $arResult['deliverycost']['@']['price'] = $delivery['@']['price'];
                        $arResult['deliverycost']['@']['delivery_period'] = $delivery['@']['delivery_period'];

                    }
                }
            }
            else {//$region
                if(!isset($region)){
                    $region = $arLocationTo['CITY_NAME'];
                }
                $xml_data = <<<EOD
                <?xml version="1.0" encoding="UTF-8"?>
                <deliverycost>
                    <auth token="$token"></auth>
                    <partner>$partner</partner>
                    <townto>$city</townto>
                    <oblname>$region</oblname>
                    <weight>$weight</weight>
                    <price>$price</price>
                    <inshprice>$price</inshprice>
					<cashservices>$cashServices</cashservices>
                    <length>$length</length>
                    <width>$width</width>
                    <height>$height</height>
                    $x2
                    <typedelivery>$type</typedelivery>
                    $tax
                </deliverycost>
EOD;
            $arResult = DalliservicecomDelivery::send_xml($xml_data);}
            if($profile == 'dalli_express'){
                foreach($arResult['deliverycost']['#']['price'] as $delivery){
                    if($delivery['@']['service']==2) {
                        $arResult['deliverycost']['@']['price'] = $delivery['@']['price'];
                        $arResult['deliverycost']['@']['delivery_period'] = $delivery['@']['delivery_period'];
                    }
                }
            }
            if ((int)$arResult['deliverycost']['@']['error'] > 0 || (int)$arResult['request']['@']['error'] > 0) {
                $res = array(
                    'RESULT' => 'ERROR',
                    'TEXT' => GetMessage('ERROR_MESS')
                );
            } else {
                $deliveryPrice = $arResult['deliverycost']['@']['price'];


                switch ($profile) {
                    case 'dalli_courier':
                        if (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'DS_COURIER_COST_DEFAULT'))) > 0) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'DS_COURIER_COST_DEFAULT');
                        } elseif (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'DS_COURIER_COST_MIN'))) > 0 && COption::GetOptionString(self::MODULE_ID, 'DS_COURIER_COST_MIN') > $deliveryPrice) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'DS_COURIER_COST_MIN');
                        }
                        break;
                    case 'dalli_pvz':
                        if (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'DS_PVZ_COST_DEFAULT'))) > 0) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'DS_PVZ_COST_DEFAULT');
                        } elseif (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'DS_PVZ_COST_MIN'))) > 0 && COption::GetOptionString(self::MODULE_ID, 'DS_PVZ_COST_MIN') > $deliveryPrice) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'DS_PVZ_COST_MIN');
                        }
                        break;
                    case 'dalli_cfo':
                        if (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'DS_CFO_COST_DEFAULT'))) > 0) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'DS_CFO_COST_DEFAULT');
                        } elseif (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'DS_CFO_COST_MIN'))) > 0 && COption::GetOptionString(self::MODULE_ID, 'DS_CFO_COST_MIN') > $deliveryPrice) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'DS_CFO_COST_MIN');
                        }
                        break;
                    case 'sdek_courier':
                        if (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'SDEK_COURIER_COST_DEFAULT'))) > 0) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'SDEK_COURIER_COST_DEFAULT');
                        } elseif (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'SDEK_COURIER_COST_MIN'))) > 0 && COption::GetOptionString(self::MODULE_ID, 'SDEK_COURIER_COST_MIN') > $deliveryPrice) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'SDEK_COURIER_COST_MIN');
                        }
                        break;
                    case 'sdek_pvz':
                        if (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'SDEK_PVZ_COST_DEFAULT'))) > 0) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'SDEK_PVZ_COST_DEFAULT');
                        } elseif (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'SDEK_PVZ_COST_MIN'))) > 0 && COption::GetOptionString(self::MODULE_ID, 'SDEK_PVZ_COST_MIN') > $deliveryPrice) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'SDEK_PVZ_COST_MIN');
                        }
                        break;
                    case 'boxberry_pvz':
                        if (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'BOXBERRY_PVZ_COST_DEFAULT'))) > 0) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'BOXBERRY_PVZ_COST_DEFAULT');
                        } elseif (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'BOXBERRY_PVZ_COST_MIN'))) > 0 && COption::GetOptionString(self::MODULE_ID, 'BOXBERRY_PVZ_COST_MIN') > $deliveryPrice) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'BOXBERRY_PVZ_COST_MIN');
                        }
                        break;
                    case '5post_pvz':
                        if (strlen(trim(COption::GetOptionString(self::MODULE_ID, '5POST_PVZ_COST_DEFAULT'))) > 0) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, '5POST_PVZ_COST_DEFAULT');
                        } elseif (strlen(trim(COption::GetOptionString(self::MODULE_ID, '5POST_PVZ_COST_MIN'))) > 0 && COption::GetOptionString(self::MODULE_ID, '5POST_PVZ_COST_MIN') > $deliveryPrice) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, '5POST_PVZ_COST_MIN');
                        }
                        break;
                    case 'pickpoint_pvz':
                        if (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'PICKPOINT_PVZ_COST_DEFAULT'))) > 0) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'PICKPOINT_PVZ_COST_DEFAULT');
                        } elseif (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'PICKPOINT_PVZ_COST_MIN'))) > 0 && COption::GetOptionString(self::MODULE_ID, 'PICKPOINT_PVZ_COST_MIN') > $deliveryPrice) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'PICKPOINT_PVZ_COST_MIN');
                        }
                        break;
                    case 'dalli_express':
                        if (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'DS_EXPRESS_COST_DEFAULT'))) > 0) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'DS_EXPRESS_COST_DEFAULT');
                        } elseif (strlen(trim(COption::GetOptionString(self::MODULE_ID, 'DS_EXPRESS_COST_MIN'))) > 0 && COption::GetOptionString(self::MODULE_ID, 'DS_EXPRESS_COST_MIN') > $deliveryPrice) {
                            $deliveryPrice = (float)COption::GetOptionString(self::MODULE_ID, 'DS_EXPRESS_COST_MIN');
                        }
                        break;
                }

                $deliveryPeriod = DalliservicecomDelivery::deliveryPeriodUnify($arResult['deliverycost']['@']['delivery_period'], $partner, $profile);
                $showSuccessPvzWrite = 'N';
                if (COption::GetOptionString(self::MODULE_ID, 'SHOW_SUCCESS_PVZ_WRITE') == 'Y')
                    $showSuccessPvzWrite = 'Y';
                $choosePvz = GetMessage('DS_CHOOZE_PVZ');
                if (count($pvzArr[$partner]['points']) > 0 && (in_array($profile, ['dalli_pvz', 'sdek_pvz', 'boxberry_pvz', '5post_pvz', 'pickpoint_pvz']))) {
                    $pvzArr[$partner]['deliveryPrice'] = $deliveryPrice;
                    if (strpos($pvzArr[$partner]['deliveryPrice'], GetMessage('RUB')) === false)
                        $pvzArr[$partner]['deliveryPrice'] .= ' ' . GetMessage('RUB');
                    $pvzArr[$partner]['deliveryPeriod'] = $deliveryPeriod;

                    $pvzArrJson = CUtil::PhpToJSObject($pvzArr[$partner]);
                    $linkShowPvz = "</br><a class = " . COption::GetOptionString('dalliservicecom.delivery', 'CHOOSE_PVZ_CLASS') . " title='$choosePvz' onclick=\"showWidget($pvzArrJson,'$partner', '$showSuccessPvzWrite')\" href='javascript:void(0)'>$choosePvz</a>";
                }

                //Dalli worktime
                $dally_time_def = COption::GetOptionString(self::MODULE_ID, 'TIME_DEFAULT');
                $dally_time_prop_id = COption::GetOptionString(self::MODULE_ID, 'TIME_PROP_ID');

                if ($dally_time_prop_id) {
                    if ($dally_time_def) {
                        $dth1 = (int)substr($dally_time_def, 0, 2);
                        $dth2 = (int)substr($dally_time_def, 6, 2);
                    }

                   /* $dalli_hours = '<div style="padding-top: 5px; color: #8d8d8d">Время работы:</div>';

                    for ($i = 0; $i <= 9; $i++) {
                        $current_time1 = str_pad(($i + 9), 2, '0', STR_PAD_LEFT) . ':00';
                        $current_time2 = str_pad(($i + 13), 2, '0', STR_PAD_LEFT) . ':00';

                        $dalli_hours1 .= '<option ' . ($dth1 == ($i + 9) ? 'selected' : '') . ' value="' . ($i + 1) . '">' . $current_time1 . '</option>';
                        $dalli_hours2 .= '<option ' . ($dth2 == ($i + 13) ? 'selected' : '') . ' value="' . ($i + 1) . '">' . $current_time2 . '</option>';
                    }

                    $profile = $arResult['deliverycost']['@']['partner'];

                    $dalli_hours .= '<select onchange="ds_time_change_min();" id="time_min" name="time_min">' . $dalli_hours1 . '</select>';
                    $dalli_hours .= ' &mdash; ';
                    $dalli_hours .= '<select onchange="ds_time_change_max();" id="time_max" name="time_max">' . $dalli_hours2 . '</select>';
                    $dalli_hours .= '<input type="hidden" name="time_prop_id" id="time_prop_id" value="' . $dally_time_prop_id . '" >';
                    $dalli_hours .= '<input type="hidden" name="time_def" id="time_def" value="' . $dally_time_def . '" >';*/
                }

                $res = array(
                'RESULT' => 'OK',
                'VALUE' => $deliveryPrice,
                'TRANSIT' => "$deliveryPeriod $dalli_hours $linkShowPvz"
            );
            }
            return $res;
        }
}

AddEventHandler('sale', 'onSaleDeliveryHandlersBuildList', array('CDeliveryDalliServicecom', 'Init'));

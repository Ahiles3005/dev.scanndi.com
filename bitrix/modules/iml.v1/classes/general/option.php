<?php

namespace Ipolh\IML;


use Ipolh\IML\Bitrix\Handler\statuses;
use Ipolh\IML\Bitrix\Tools;

class option extends abstractGeneral
{
    // optionsControll
    public static $ABYSS = array();

    public static function get($option)
    {
        $self = \COption::GetOptionString(self::$MODULE_ID,$option,self::getDefault($option));
        if(
            unserialize($self) &&
            self::checkMultiple($option)
        )
            $self = unserialize($self);
        return $self;
    }

    public static function set($option,$val,$doSerialise = false)
    {
        if($doSerialise){
            $val = serialize($val);
        }
        $self = \COption::SetOptionString(self::$MODULE_ID,$option,$val);
    }

    public function getDefault($option)
    {
        $opt = self::collection();
        if(array_key_exists($option,$opt))
            return $opt[$option]['default'];
        return false;
    }

    public static function checkMultiple($option)
    {
        $opt = self::collection();
        if(array_key_exists($option,$opt) && array_key_exists('multiple',$opt[$option]))
            return $opt[$option]['multiple'];
        return false;
    }

    public static function toOptions($helpMakros = false)
    {
        if(!$helpMakros)
            $helpMakros = "<a href='#' class='".self::$MODULE_LBL."PropHint' onclick='return ".self::$MODULE_LBL."setups.popup(\"pop-#CODE#\", this);'></a>";

        $arOptions = array();
        foreach(self::collection() as $optCode => $optVal){
            if(!array_key_exists('group',$optVal) || !$optVal['group'])
                continue;

            if (!array_key_exists($optVal['group'], $arOptions))
                $arOptions[$optVal['group']] = array();

            $name = ($optVal['hasHint'] == 'Y') ? " ".str_replace('#CODE#',$optCode,$helpMakros) : '';

            $arDescription = array($optCode,Tools::getMessage("OPT_{$optCode}").$name,$optVal['default'],array($optVal['type']));

            $arOptions[$optVal['group']][] = $arDescription;
        }

        return $arOptions;
    }

    public static function collection()
    {
        // name - always IPOLIML_OPT_<code>
        $arOptions = array(
            // logData
            'logIml' => array(
                'group'   => 'logData',
                'hasHint' => 'N',
                'default' => false,
                'type'    => 'text'
            ),
            'pasIml' => array(
                'group'   => 'logData',
                'hasHint' => 'N',
                'default' => false,
                'type'    => 'password'
            ),
            'logged' => array(
                'group'   => 'logData',
                'hasHint' => 'N',
                'default' => false,
                'type'    => 'text'
            ),

            // common
            'isTest' => array(
                'group'   => 'common',
                'hasHint' => 'N',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'strName' => array(
                'group'   => 'common',
                'hasHint' => 'N',
                'default' => '',
                'type'    => 'text'
            ),
            'delReqOrdr' => array(
                'group'   => 'common',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'prntActOrdr' => array(
                'group'   => 'common',
                'hasHint' => 'Y',
                'default' => 'O',
                'type'    => 'selectbox'
            ),
            'orderIdMode' => array(
                'group'   => 'common',
                'hasHint' => 'Y',
                'default' => \imldriver::defiDefON(),
                'type'    => 'checkbox'
            ),
            'showInOrders' => array(
                'group'   => 'common',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => 'selectbox'
            ),
            'noVats' => array(
                'group'   => 'common',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => 'checkbox'
            ),

            // dimensionsDef
            'lengthD' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => '400',
                'type'    => 'text'
            ),
            'widthD' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => '300',
                'type'    => 'text'
            ),
            'heightD' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => '200',
                'type'    => 'text'
            ),
            'defaultWeight' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => '1',
                'type'    => 'text'
            ),
            'defMode' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => 'O',
                'type'    => 'selectbox'
            ),

            // status
            'setDeliveryId' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'markPayed' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'statusOK' => array(
                'group'   => 'status',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => 'selectbox'
            ),
            'statusFAIL' => array(
                'group'   => 'status',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => 'selectbox'
            ),
            'statusSTORE' => array(
                'group'   => 'status',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => 'selectbox'
            ),
            'statusCORIER' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => false,
                'type'    => 'selectbox'
            ),
            'statusPVZ' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => false,
                'type'    => 'selectbox'
            ),
            'statusDELIVD' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => false,
                'type'    => 'selectbox'
            ),
            'statusOTKAZ' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => false,
                'type'    => 'selectbox'
            ),

            // orderParams
            'departure' => array( // TODO: departure add default
                'group'   => 'orderParams',
                'hasHint' => 'Y',
                'default' => Tools::getMessage("BGMSC"),
                'type'    => 'selectbox'
            ),
            'selectDeparture' => array(
                'group'   => 'orderParams',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'name' => array(
                'group'   => 'orderParams',
                'hasHint' => 'N',
                'default' => "#PROP_FIO#",
                'type'    => 'text'
            ),
            'city' => array(
                'group'   => 'orderParams',
                'hasHint' => 'N',
                'default' => "#PROP_LOCATION#",
                'type'    => 'text'
            ),
            'line' => array(
                'group'   => 'orderParams',
                'hasHint' => 'N',
                'default' => "#PROP_ADDRESS#",
                'type'    => 'text'
            ),
            'postCode' => array(
                'group'   => 'orderParams',
                'hasHint' => 'N',
                'default' => "#PROP_ZIP#",
                'type'    => 'text'
            ),
            'telephone1' => array(
                'group'   => 'orderParams',
                'hasHint' => 'N',
                'default' => "#PROP_PHONE#",
                'type'    => 'text'
            ),
            'email' => array(
                'group'   => 'orderParams',
                'hasHint' => 'N',
                'default' => "#PROP_EMAIL#",
                'type'    => 'text'
            ),
            'comment' => array(
                'group'   => 'orderParams',
                'hasHint' => 'N',
                'default' => "#USER_DESCRIPTION#",
                'type'    => 'text'
            ),

            // itemProps
            'loadGoods' => array(
                'group'   => 'itemProps',
                'hasHint' => 'Y',
                'default' => "Y",
                'type'    => 'checkbox'
            ),
            'VATRate' => array(
                'group'   => 'itemProps',
                'hasHint' => 'N',
                'default' => "NONDS",
                'type'    => 'selectbox'
            ),
            'NDSUseCatalog' => array(
                'group'   => 'itemProps',
                'hasHint' => 'Y',
                'default' => "N",
                'type'    => 'checkbox'
            ),
            'articul' => array(
                'group'   => 'itemProps',
                'hasHint' => 'Y',
                'default' => "ARTNUMBER",
                'type'    => 'text'
            ),
            'barcode' => array(
                'group'   => 'itemProps',
                'hasHint' => 'N',
                'default' => "",
                'type'    => 'text'
            ),

            // basket
            'noPVZnoOrder' => array(
                'group'   => 'basket',
                'hasHint' => 'N',
                'default' => "N",
                'type'    => 'checkbox'
            ),
            'hideNal' => array(
                'group'   => 'basket',
                'hasHint' => 'N',
                'default' => "Y",
                'type'    => 'checkbox'
            ),
            'noPostomat' => array(
                'group'   => 'basket',
                'hasHint' => 'N',
                'default' => "N",
                'type'    => 'checkbox'
            ),
            'pvzID' => array(
                'group'   => 'basket',
                'hasHint' => 'N',
                'default' => "",
                'type'    => 'text'
            ),
            'pvzPicker' => array(
                'group'   => 'basket',
                'hasHint' => 'N',
                'default' => "ADDRESS",
                'type'    => 'text'
            ),
            'autoSelOne' => array(
                'group'   => 'basket',
                'hasHint' => 'N',
                'default' => "N",
                'type'    => 'checkbox'
            ),
            'labelDays' => array(
                'group'   => 'basket',
                'hasHint' => 'N',
                'default' => "N",
                'type'    => 'selectbox'
            ),
            'noYmaps' => array(
                'group'   => 'basket',
                'hasHint' => 'N',
                'default' => "N",
                'type'    => 'checkbox'
            ),
            'FILTERSOFF' => array(
                'group'   => 'basket',
                'hasHint' => 'Y',
                'default' => "N",
                'type'    => 'checkbox'
            ),
            'FILTERDEFAULT' => array(
                'group'   => 'basket',
                'hasHint' => 'Y',
                'default' => "Y",
                'multiple' => true,
                'type'    => 'selectbox'
            ),

            // deliverySys
            'countType' => array(
                'group'   => 'deliverySys',
                'hasHint' => 'Y',
                'default' => "S",
                'type'    => 'selectbox'
            ),
            'serverToTable' => array(
                'group'   => 'deliverySys',
                'hasHint' => 'Y',
                'default' => "N",
                'type'    => 'checkbox'
            ),
            'mindGabarites' => array(
                'group'   => 'deliverySys',
                'hasHint' => 'Y',
                'default' => "N",
                'type'    => 'checkbox'
            ),
            'forceRoundDelivery' => array(
                'group'   => 'deliverySys',
                'hasHint' => 'Y',
                'default' => "N",
                'type'    => 'checkbox'
            ),
            'mindDeclaredValue' => array(
                'group'   => 'deliverySys',
                'hasHint' => 'Y',
                'default' => "Y",
                'type'    => 'checkbox'
            ),
            'pickupDeliveryPriceType' => array(
                'group' => 'deliverySys',
                'hasHint' => 'N',
                'default' => 'Min',
                'type' => 'selectbox'
            ),

            // paySystems
            'paySystems' => array(
                'group'   => 'paySystems',
                'hasHint' => 'N',
                'default' => "",
                'type'    => 'selectbox'
            ),

            // termsDeliv
            'timeSend' => array(
                'group'   => 'termsDeliv',
                'hasHint' => 'Y',
                'default' => "",
                'type'    => 'selectbox'
            ),
            'commonHold' => array(
                'group'   => 'termsDeliv',
                'hasHint' => 'Y',
                'default' => "",
                'type'    => 'text'
            ),
            'addHold' => array(
                'group'   => 'termsDeliv',
                'hasHint' => 'N',
                'default' => "",
                'type'    => 'special'
            ),

            // addingService
            'services' => array(
                'group'   => 'addingService',
                'hasHint' => 'Y',
                'default' => Tools::getMessage('OPTION_DEFSERVICES'),
                'type'    => 'special'
            ),
            'blockedServices' => array(
                'group'   => 'addingService',
                'hasHint' => 'Y',
                'default' => Tools::getMessage('OPTION_DEFSERVICES'),
                'type'    => 'special'
            ),

            // service
            'lasyLoadShtrih' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => 'checkbox'
            ),
            'last' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => 'text'
            ),
            'schet' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => '0',
                'type'    => 'text'
            ),
            'getOutLst' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => '0',
                'type'    => 'text'
            ),
            'lstShtPr' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => '0',
                'type'    => 'text'
            ),
            'turnOffRestrictsOS' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'debugMode' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'timeoutRollback' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => '6',
                'type'    => array("text",1)
            ),
            'requestTimeout' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => '6',
                'type'    => array("text",1)
            ),

            // debug
            'debug_widget' => array(
                'group'   => 'debug',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_startLogging' => array(
                'group'   => 'debug',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'debug_fileMode' => array(
                'group'   => 'debug',
                'hasHint' => 'Y',
                'default' => 'w',
                'type'    => 'selectbox'
            ),

            // debug_events
            'debug_calculation' => array(
                'group'   => 'debug_events',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'debug_turnOffWidget' => array(
                'group'   => 'debug_events',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_compability' => array(
                'group'   => 'debug_events',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_calculate' => array(
                'group'   => 'debug_events',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_orderSend' => array(
                'group'   => 'debug_events',
                'hasHint' => 'N',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'debug_statusCheck' => array(
                'group'   => 'debug_events',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
        );

        return $arOptions;
    }


    public static function getSelectVals($code)
    {
        $arVals = false;

        switch($code){
            case 'prntActOrdr'     :
                $arVals = array("O" => Tools::getMessage("OTHR_ACTSORDRS"),"A" => Tools::getMessage("OTHR_ACTSONLY"));
                break;
            case 'showInOrders'     :
                $arVals = array("Y" => Tools::getMessage("OTHR_ALWAYS"),"N" => Tools::getMessage("OTHR_DELIVERY"));
                break;
            case 'defMode'    :
                $arVals = array("O" => Tools::getMessage("LABEL_forOrder"),"G" => Tools::getMessage("LABEL_forGood"));
                break;

            case 'statusOK'     :
            case 'statusFAIL'   :
            case 'statusSTORE'  :
            case 'statusCORIER' :
            case 'statusPVZ'    :
            case 'statusDELIVD' :
            case 'statusOTKAZ'  :
                if(array_key_exists('statuses',self::$ABYSS)){
                    $arVals = self::$ABYSS['statuses'];
                }else {
                    $arVals = array(0 => '');
                    $arVals = array_merge($arVals,statuses::getOrderStatuses());
                    self::$ABYSS['statuses'] = $arVals;
                }
                break;
            case 'VATRate'     :
                $arVals = array(
                    "NONDS" => Tools::getMessage("SIGN_NONDS"),
                    "0"     => '0%',
                    "10"    => '10%',
                    "18"    => '18%',
                    "20"    => '20%',
                );
                break;
            case 'labelDays'     :
                $arVals = array(
                    "N" => Tools::getMessage('OPT_labelDays_NONE'),
                    "D" => Tools::getMessage('OPT_labelDays_DAY'),
                    'A' => Tools::getMessage('OPT_labelDays_ALL')
                );
                break;
            case 'countType'     :
                $arVals = array(
                    "T" => Tools::getMessage('OPT_countType_TABLE'),
                    "S" => Tools::getMessage('OPT_countType_SERVER'),
                );
                break;
            case 'paySystems'     :
                $paySysS=\CSalePaySystem::GetList(array(),array('ACTIVE'=>'Y'));
                while($paySys=$paySysS->Fetch()) {
                    $arVals[$paySys['ID']] = $paySys['NAME'];

                    if(
                        self::get('paySystems') == 'Y' &&
                        (
                            strpos(strtolower($paySys['NAME']),Tools::getMessage("PIECE_cash1")) !== false ||
                            strpos(strtolower($paySys['NAME']),Tools::getMessage("PIECE_cash2")) !== false ||
                            strpos(strtolower($paySys['NAME']),Tools::getMessage("PIECE_cash3")) !== false ||
                            strpos(strtolower($paySys['NAME']),Tools::getMessage("PIECE_cash4")) !== false
                        )
                    ){
                        if(!array_key_exists('paySystemsDefaults',self::$ABYSS))
                            self::$ABYSS['paySystemsDefaults'] = array();
                        self::$ABYSS['paySystemsDefaults'] []= $paySys['ID'];
                    }

                }
                break;
            case 'debug_fileMode'     :
                $arVals = array(
                    "w" => Tools::getMessage('OPT_debug_fileMode_w'),
                    "a" => Tools::getMessage('OPT_debug_fileMode_a'),
                );
                break;
            case 'FILTERDEFAULT' :
                $arVals = array(
                    'paynal'  => Tools::getMessage('OPT_LBL_paynal'),
                    'paycard' => Tools::getMessage('OPT_LBL_paycard'),
                    'fitting' => Tools::getMessage('OPT_LBL_fitting'),
                );
            break;
            case 'departure' :
                $arVals = \imlHelper::getListFile()['Region'];
                break;
            case 'pickupDeliveryPriceType':
                $arVals = array(
                    'min' => Tools::getMessage("OPT_PickupDeliveryPriceType_Min"),
                    'average' => Tools::getMessage("OPT_PickupDeliveryPriceType_Average"),
                    'max' => Tools::getMessage("OPT_PickupDeliveryPriceType_Max")
                );
                break;
        }

        return $arVals;
    }
}
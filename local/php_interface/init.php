<?
use Bitrix\Main; 
use Bitrix\Main\Loader;
use Bitrix\Catalog\SubscribeTable;
use Bitrix\Catalog\Product\SubscribeManager;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main\Diag\Debug;

Loader::includeModule("highloadblock");
Loader::includeModule("sale");
Loader::includeModule("catalog");
$arLibs = [
    $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/defines.php',
];
global $ListAllCountries;
$ListAllCountries = include $_SERVER['DOCUMENT_ROOT'].'/local/templates/template/country.php';
foreach($arLibs as $lib){
    if(file_exists($lib)){
        require_once($lib);
    }
}
\Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'DDS\\Tools' => '/local/php_interface/include/DDSShopAPI/classes/tools.php',
    'DDS\\Basketclass' => '/local/php_interface/include/DDSShopAPI/classes/basket.php',
    'DDS\\Bonus' => '/local/php_interface/include/DDSShopAPI/classes/bonus.php',
    'DDS\\Date' => '/local/php_interface/include/DDSShopAPI/classes/date.php',
    'PHPInterface\\ComponentHelper' => '/local/php_interface/include/DDSShopAPI/classes/ComponentHelper.php',
]);
//Функция отвечающая за вывод "хлебных-крошек" bitrix:breadcrumb
  function ShowNavChain($template = 'bread')
  {
    global $APPLICATION;
    $APPLICATION->IncludeComponent("bitrix:breadcrumb",
      $template,
      Array(
      "START_FROM" => "0",
      "PATH" => "",
      "SITE_ID" => "s1"
    )
  );
  }


AddEventHandler("iblock", "OnBeforeIBlockElementUpdate","LogUpdate"); 
AddEventHandler("iblock", "OnBeforeIBlockElementAdd","LogUpdate"); 
function LogUpdate(&$arFields) { 
    if ($_REQUEST['mode']=='import') {
		//$logOrder = Bitrix\Main\Diag\Debug::writeToFile("Начало массива в функции DoNotAdd", "", "/local/log_update_1c.txt");
		//$logOrder = Bitrix\Main\Diag\Debug::writeToFile($arFields, "", "/local/log_update_1c.txt");
		//$logOrder = Bitrix\Main\Diag\Debug::writeToFile("Конец массива в функции DoNotAdd", "", "/local/log_update_1c.txt"); 
    }
}



AddEventHandler("main", "OnBeforeEventAdd", array("MyClass", "OnBeforeEventAddHandler"));
class MyClass
{
    static function OnBeforeEventAddHandler(&$event, &$lid, &$arFields)
    {
       
        if($event == 'SALE_NEW_ORDER' && empty($arFields['ORDER_ID'])) {

            return false;
        }
    }
}
AddEventHandler("sale", "OnBasketAdd", array("BasketUser", "onBasketUserAddOrUpdate"));
AddEventHandler("sale", "OnBasketUpdate", array("BasketUser", "onBasketUserAddOrUpdate"));
AddEventHandler("sale", "OnBeforeBasketDelete", array("BasketUser", "onBasketDelete"));
AddEventHandler("sale", "OnSaleComponentOrderCreated", array("BasketUser", "deleteAllStoragesReminder"));
AddEventHandler("sale", "OnSaleBasketItemRefreshData", Array("basketItemsChange", "onBasketItemGetChange"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", array("BasketUser", "changeStoreAvailableHighload"));
AddEventHandler("main", "OnBeforeUserUpdate", Array("UserActivation", "OnBeforeUserUpdateHandler"));
Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleBasketBeforeSaved',
    'myFunctionBasketUpdate'
);
function myFunctionBasketUpdate(Main\Event $event)
{
    $basket = $event->getParameter("ENTITY");
    $basketItems = $basket->getBasketItems();
	foreach($basketItems as $basketItem){
		$IdProducts[] =$basketItem->getField('PRODUCT_ID');
	}

	$res = CIBlockElement::GetList([], ["IBLOCK_ID"=>29,"ID"=>$IdProducts, "ACTIVE"=>"Y"], false, [], ["ID","NAME","PROPERTY_CML2_LINK"]);
	while($ob = $res->GetNextElement()){ 
		$arFields = $ob->GetFields();  
		$db_props = CIBlockElement::GetProperty(27,$arFields['PROPERTY_CML2_LINK_VALUE'], "sort", "asc", Array("CODE"=>"CML2_ARTICLE")); 
		if($ar_props = $db_props->Fetch()){
			$ProductId[$arFields['ID']]=$ar_props['VALUE'];
		}
		$db_props2 = CIBlockElement::GetProperty(27,$arFields['PROPERTY_CML2_LINK_VALUE'], "sort", "asc", Array("CODE"=>"TSVET_DLYA_SAYTA")); 
		if($ar_props2 = $db_props2->Fetch()){
			$ProductId2[$arFields['ID']]=$ar_props2;
		}
	}
	foreach($basketItems as $basketItem){
		$setColor='';
		if($ProductId2[$basketItem->getField('PRODUCT_ID')]){
		$setColor='('.$ProductId2[$basketItem->getField('PRODUCT_ID')]['VALUE_ENUM'].')';
		}
		if($ProductId[$basketItem->getField('PRODUCT_ID')]){
			$pos = strripos($basketItem->getField('NAME'), 'артикул:');
			if ($pos === false) {
				//$basketItem->setField('NAME', $basketItem->getField('NAME').' - (артикул:'.$ProductId[$basketItem->getField('PRODUCT_ID')].')');
				$basketItem->setField('NAME', $basketItem->getField('NAME').' - артикул:'.$ProductId[$basketItem->getField('PRODUCT_ID')].' '.$setColor);
			}
		}
	}
}
AddEventHandler('main', 'OnBeforeEventSend', Array("MyForm", "my_OnBeforeEventSend"));
class MyForm
{
   static function my_OnBeforeEventSend(&$arFields, &$arTemplate)
   {
		if($arTemplate['EVENT_NAME']=='SALE_NEW_ORDER'){
			if (CModule::IncludeModule("catalog"))
			{
				$store_id=['18','19','20',"17"];
				$dbResult3 = CCatalogStore::GetList(
				array('SORT' => 'ASC'),
				array('ACTIVE' => 'Y','ID'=>$store_id),
				false,
				false,array()
				);
				while($store3 = $dbResult3->GetNext()){
					$arFields['ORDER_LIST']=str_replace("[ID склада: ".$store3['ID']."]", "[".$store3['TITLE']."]", $arFields['ORDER_LIST']);
					$arFields['ORDER_LIST']=str_replace("артикул:", "", $arFields['ORDER_LIST']);
				}
			}
			//AddMessage2Log($arFields);
			//AddMessage2Log($arTemplate);
		}
   }
}
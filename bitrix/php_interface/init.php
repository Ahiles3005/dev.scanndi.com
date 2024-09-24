<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/functions.php");

AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("MyClass", "OnAfterIBlockElementUpdateHandler"));
// Высчитывает средний рейтинг и количество отзывов товара
class MyClass
{
    function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        if($arFields['IBLOCK_ID'] == 10)
        {
        	$product_id = array_shift( $arFields['PROPERTY_VALUES'][28] )['VALUE'];

        	if(is_numeric($product_id))
        	{
        		CModule::IncludeModule('iblock');

				$arSelect = Array("ID", "NAME", "PROPERTY_PRODUCT_ID", "PROPERTY_RATING");
				$arFilter = Array("IBLOCK_ID"=>10, "PROPERTY_PRODUCT_ID"=>$product_id, "ACTIVE"=>"Y");
				$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
				
				$rating = 0;
				$count = 0;

				while($ob = $res->GetNextElement())
				{
					$count++;
					$secElements = $ob->GetFields();					
					$rating += $secElements['PROPERTY_RATING_VALUE'];
				}

				if($count > 0)
				{
					$rating = round( $rating / $count );
				}

				CIBlockElement::SetPropertyValuesEx($product_id, 1, array('RATING' => $rating));
				CIBlockElement::SetPropertyValuesEx($product_id, 1, array('REVIEW_COUNT' => $count));

				CIBlock::clearIBlockTagCache(1);
        	}
        }
    }
}

// Добавление минимальной цены для карточки товара
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("UpdateMinimalPrice", "OnAfterIBlockElementUpdateHandler"));

class UpdateMinimalPrice
{
    // Создаем обработчик события "OnAfterIBlockElementUpdate"
    function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
    	// Если инфоблок - торговых предложений
    	if($arFields['IBLOCK_ID'] == 1)
        {

        	$IBLOCK_ID = $arFields['IBLOCK_ID']; 

			$arSelect = Array("ID", "NAME", "CATALOG_GROUP_1");
			$arFilter = Array("IBLOCK_ID"=>intval($IBLOCK_ID), "ACTIVE"=>"Y");
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($ob = $res->GetNextElement())
			{

			    $arFields = $ob->GetFields();
			    $min_price = 0;

			    $action = '';

			    $productID = $arFields['ID'];     
			    

			    $Offers = CCatalogSKU::getOffersList(
			        $productID,
			        $IBLOCK_ID
			    );

			    if(!empty($Offers[$productID]))
			    {
			        foreach($Offers[$productID] as $offer)
			        {
			            $ar_res = CCatalogProduct::GetOptimalPrice($offer['ID']);

			            if($min_price == 0)
			            {
			                $min_price = $ar_res['DISCOUNT_PRICE'];
			            }

			            if($min_price > floatval($ar_res['DISCOUNT_PRICE']) )
			            {
			                $min_price = $ar_res['DISCOUNT_PRICE'];
			            }

			            if($ar_res['PRICE']['PRICE'] > $ar_res['DISCOUNT_PRICE'])
			            {
			                $action = 4;
			            }
			        }
			    }
			    else
			    {
			        $ar_res = CCatalogProduct::GetOptimalPrice($arFields['ID']);

			        $min_price = $ar_res['DISCOUNT_PRICE'];

			        if($ar_res['PRICE']['PRICE'] > $ar_res['DISCOUNT_PRICE'])
			        {
			            $action = 4;
			        }
			    }

			    $arProperty = Array(
			        "ACTION"=> $action,
			        "MINIMAL_CARD_PRICE" => $min_price,
			    );

			    CIBlockElement::SetPropertyValuesEx($arFields['ID'], $IBLOCK_ID, $arProperty);   
			}

	      
    	}
    }
}

 
// Регистрируем обработчик методами D7
use Bitrix\Main;
use Bitrix\Sale;
Main\EventManager::getInstance()->addEventHandler(
	'sale',
	'OnSaleOrderBeforeSaved',
	'OnSaleComponentHandler'
);

function OnSaleComponentHandler(Main\Event $event)
{
	$order = $event->getParameter("ENTITY");
	// Получаем объект заказа
	$commentbitrix24 = "";
	$cityb24 = "Город: ";
	$streetb24 = "Улица: ";
	$houseb24 = "Дом: ";
	$floorb24 = "Этаж: ";
	$flatb24 = "Квартира: ";
	$deliverydate = "Дата доставки: ";
	$propertyCollection = $order->getPropertyCollection();
	// Свойства товара в корзине, коллекция объектов Sale\BasketPropertyItem

	$propertys = $propertyCollection->getArray();
	// Массив свойств ['properties' => [..], 'groups' => [..] ];

	// Получаем адрес пользователя
	$address = "Адрес: ";
	foreach ($propertys["properties"] as $location)
	{
		if($location["CODE"] === "CITY")
		{
			$address .= $location["VALUE"][0] . " ";
		}
		elseif($location["CODE"] === "STREET" 
			   || $location["CODE"] === "HOUSE"|| $location["CODE"] === "FLOOR" || $location["CODE"] === "FLAT")
		{
			$address .= $location["VALUE"][0] . " ";
		}
	}
	$commentbitrix24 = $address;
	// Получаем дополнительные параметры,DELIVERY_DATE
	//которые необходимо поместить в комментарий менеджера

	foreach ($propertys["properties"] as $location)
	{
		if($location["CODE"] === "CITY")
		{
			$cityb24 .= $location["VALUE"][0] . " ";
		}
	}
	foreach ($propertys["properties"] as $location)
	{
		if($location["CODE"] === "STREET")
		{
			$streetb24 .= $location["VALUE"][0] . " ";
		}
	}
	foreach ($propertys["properties"] as $location)
	{
		if($location["CODE"] === "HOUSE")
		{
			$houseb24 .= $location["VALUE"][0] . " ";
		}
	}
	foreach ($propertys["properties"] as $location)
	{
		if($location["CODE"] === "FLOOR")
		{
			$floorb24 .= $location["VALUE"][0] . " ";
		}
	}
	foreach ($propertys["properties"] as $location)
	{
		if($location["CODE"] === "FLAT")
		{
			$flatb24 .= $location["VALUE"][0] . " ";
		}
	}
	foreach ($propertys["properties"] as $location)
	{
		if($location["CODE"] === "DELIVERY_DATE")
		{
			$deliverydate .= $location["VALUE"][0] . " ";
		}
	}


	$commentbitrix24 .= $cityb24;
	$commentbitrix24 .= $streetb24;
	$commentbitrix24 .= $houseb24;
	$commentbitrix24 .= $floorb24;
	$commentbitrix24 .= $flatb24;
	$commentbitrix24 .= $deliverydate;
 // Получаем конечную строку комментария

// Устанавливаем сформированный комментарий менеджера для заказа
$order->setField("COMMENTS", $commentbitrix24);
}


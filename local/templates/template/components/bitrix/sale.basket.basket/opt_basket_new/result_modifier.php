<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
use Bitrix\Main;
CModule::IncludeModule("catalog");

$defaultParams = array(
	'TEMPLATE_THEME' => 'blue'
);
$arParams = array_merge($defaultParams, $arParams);
unset($defaultParams);

$arParams['TEMPLATE_THEME'] = (string)($arParams['TEMPLATE_THEME']);
if ('' != $arParams['TEMPLATE_THEME'])
{
	$arParams['TEMPLATE_THEME'] = preg_replace('/[^a-zA-Z0-9_\-\(\)\!]/', '', $arParams['TEMPLATE_THEME']);
	if ('site' == $arParams['TEMPLATE_THEME'])
	{
		$templateId = (string)Main\Config\Option::get('main', 'wizard_template_id', 'eshop_bootstrap', SITE_ID);
		$templateId = (preg_match("/^eshop_adapt/", $templateId)) ? 'eshop_adapt' : $templateId;
		$arParams['TEMPLATE_THEME'] = (string)Main\Config\Option::get('main', 'wizard_'.$templateId.'_theme_id', 'blue', SITE_ID);
	}
	if ('' != $arParams['TEMPLATE_THEME'])
	{
		if (!is_file($_SERVER['DOCUMENT_ROOT'].$this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css'))
			$arParams['TEMPLATE_THEME'] = '';
	}
}
if ('' == $arParams['TEMPLATE_THEME']){
	$arParams['TEMPLATE_THEME'] = 'blue';
}












$arResult['BASKET2'] = [];



foreach($arResult["GRID"]["ROWS"] as $grid){
	$IdOffer[$grid['PRODUCT_ID']]=$grid['PRODUCT_ID'];
	$arResult['ELEMENTS'][$grid['PRODUCT_ID']][$grid['PROPS_ALL']['SKLAD']['VALUE']]=$grid;
	$arResult['PRICE'][$grid['PROPS_ALL']['SKLAD']['VALUE']]+=$grid['PRICE']*$grid['QUANTITY'];
	/*
	if($grid['PROPS_ALL']['SKLAD']['VALUE']!='19'){
		$nineteen+=$grid['QUANTITY'];
	}
	*/
}
/*
if($nineteen>0){
	foreach($arResult["GRID"]["ROWS"] as $grid){
		$arResult['PRICE'][$grid['PROPS_ALL']['SKLAD']['VALUE']]+=$grid['PRICE']*$grid['QUANTITY']/100*90;
	}
}else{
	foreach($arResult["GRID"]["ROWS"] as $grid){
		$arResult['PRICE'][$grid['PROPS_ALL']['SKLAD']['VALUE']]+=$grid['PRICE']*$grid['QUANTITY'];
	}
}
*/


$res = CIBlockElement::GetList([], ["IBLOCK_ID"=>29,"ID"=>$IdOffer, "ACTIVE"=>"Y"], false, [], ["ID","NAME","PROPERTY_CML2_LINK"]);
while($ob = $res->GetNextElement()){ 
	$arFields = $ob->GetFields();  
	$ProductId[$arFields['PROPERTY_CML2_LINK_VALUE']]=$arFields['PROPERTY_CML2_LINK_VALUE'];

}

/*
$id_n_b = explode(",", $arParams['ELEMENT_ID']);
if($id_n_b){
	foreach($id_n_b as $ob){
		$ProductId[$ob] = $ob;
	}
}
*/
if(count($_SESSION['SAVE_BASKET'])>0){
	foreach($_SESSION['SAVE_BASKET'] as $ob){
		$ProductId[$ob] = $ob;
	}
}


$res = CIBlockElement::GetList(
	['ID'=> 'ASC'],
	["IBLOCK_ID"=>27,"ID"=>$ProductId, "ACTIVE"=>"Y"],
	false,
	false,
	["ID", "IBLOCK_ID", "NAME","PREVIEW_PICTURE","DETAIL_PAGE_URL","PROPERTY_CML2_ARTICLE"]
);
while($ob = $res->GetNextElement()){ 
	$arFields = $ob->GetFields();  
	$offerIds[]=$arFields['ID'];
	$arResult['MAIN_ITEM'][$arFields['ID']]=$arFields;
}


$arResult['OFFERS'] = CCatalogSKU::getOffersList(
	$offerIds,
	0,
	['ACTIVE' => 'Y'],
	['ID',"NAME"],
	[]
);
foreach($arResult['OFFERS'] as $off){
	foreach($off as $off2){
		$StoreIdOffer[]=$off2['ID'];
	}
}


foreach (TYPE_STORE as $itemType){
	foreach ($itemType['STORE_ID'] as $id){
		$store_id[] = $id;
	}
}
$dbResult3 = CCatalogStore::GetList(
array('SORT' => 'ASC'),
array('ACTIVE' => 'Y','PRODUCT_ID' =>$StoreIdOffer ,'ID'=>$store_id),
false,
false,array("ID","TITLE","ACTIVE","PRODUCT_AMOUNT","DESCRIPTION","ELEMENT_ID")
);
while($store3 = $dbResult3->GetNext()){
	$arResult['STORE'][$store3['ELEMENT_ID']][$store3['ID']]=$store3;
	$arResult['STORE_PRICE'][$store3['ID']]=$store3;
}



//цена первого тп
foreach($arResult['OFFERS'] as $key_off1=>$off1){
	$off1_cnt=0;foreach($off1 as $off2){$off1_cnt++;
		if($off1_cnt==1){
			$ret_sku = GetCatalogProductPrice($off2['ID'], 94);
			if ($ret_sku['PRICE']){
				$arResult['OfferPrice94'][$key_off1]=$ret_sku['PRICE']*1;
			}
			$ret_sku2 = GetCatalogProductPrice($off2['ID'], 95);
			if ($ret_sku2['PRICE']){
				$arResult['OfferPrice95'][$key_off1]=$ret_sku2['PRICE']*1;
			}
		}
	}
}
?>





















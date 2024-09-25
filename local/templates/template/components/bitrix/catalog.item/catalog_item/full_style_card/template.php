<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $item
 * @var array $actualItem
 * @var array $minOffer
 * @var array $itemIds
 * @var array $price
 * @var array $measureRatio
 * @var bool $haveOffers
 * @var bool $showSubscribe
 * @var array $morePhoto
 * @var bool $showSlider
 * @var string $imgTitle
 * @var string $productTitle
 * @var string $buttonSizeClass
 * @var CatalogSectionComponent $component
 */
if (is_array($arParams['FAVORITES']) && in_array($item['ID'], $arParams['FAVORITES'])) {
    $fav_act = 'compfavdelete';
    $fav_img = SITE_TEMPLATE_PATH . '/images/setFavorite.svg';
    $fav_class = 'fav_1';
    $span_class = '';
} else {
    $fav_act = 'compfav';
    $fav_img = SITE_TEMPLATE_PATH . '/images/favorite.svg';
    $fav_class = 'fav_2';
    $span_class = 'active';
}

if ($item['PREVIEW_PICTURE']['SRC']) {
    $picture = CFile::ResizeImageGet($item['PREVIEW_PICTURE']['ID'],
        ['width' => 450, 'height' => 527],
        BX_RESIZE_IMAGE_PROPORTIONAL_ALT
    )['src'];
}elseif($item['DETAIL_PICTURE']['SRC']){
	$picture = CFile::ResizeImageGet($item['DETAIL_PICTURE']['ID'],
	            ['width' => 450, 'height' => 527],
	            BX_RESIZE_IMAGE_PROPORTIONAL_ALT
	        )['src'];

} else {
    $picture = SITE_TEMPLATE_PATH . '/images/no-photo.png';
}

?>
<div class="main-new__item goods-more__item" id="<?= $itemIds['ID'];?>">
    <a href="<?= $item['DETAIL_PAGE_URL'];?>">
        <div class="image-box">
            <img class="list_img" src="<?= $picture;?>" alt="<?=$item['PROPERTIES']['CML2_TRAITS']['VALUE'][0]?> <?= $item['NAME'];?>">
            <span class="main-new__favorite likefav <?= $span_class;?>" data-action="<?= $fav_act;?>" data-add="FAVORITES" data-id="<?= $item['ID'];?>">
				<img src="<?= $fav_img;?>" class="<?= $fav_class;?>" >
			</span>
        </div>
        <span class="main-new__desc"><?=$item['PROPERTIES']['CML2_TRAITS']['VALUE'][0]?> <?= $item['NAME'];?></span>

<?//блок цветов
	if($item['PROPERTIES']['CML2_ARTICLE']['VALUE']!=''):
?>
<style>
.arcolor {margin:2px;width: 15px;height: 15px;border: 1px solid #555;float: left;cursor:pointer;}
.arcolor div {width: 6px;height: 6px;border-radius: 50%;margin: 3px auto;}
</style>
<?
$cres = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>37, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y"), false, Array(), Array("ID", "IBLOCK_ID", "NAME", "PROPERTY_COLOR"));
while($cob = $cres->GetNextElement()){ 
	$carFields = $cob->GetFields();  
	$arColor[$carFields['NAME']]='#'.$carFields['PROPERTY_COLOR_VALUE'];
}
$arSelect = Array("ID", "IBLOCK_ID", "NAME", "PROPERTY_CML2_ARTICLE","PROPERTY_TSVET_DLYA_SAYTA","DETAIL_PAGE_URL");
$arFilter = Array("IBLOCK_ID"=>27, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "PROPERTY_CML2_ARTICLE" => $item['PROPERTIES']['CML2_ARTICLE']['VALUE']);
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
while($ob = $res->GetNextElement()){ 
	$arFields = $ob->GetFields();  
	//print_r($arFields);
	$tsvet=explode('/',$arFields['PROPERTY_TSVET_DLYA_SAYTA_VALUE']);
	$tsvet[0]=mb_strtolower($tsvet[0]);
	$tsvet[1]=mb_strtolower($tsvet[1]);
	if($arFields['PROPERTY_TSVET_DLYA_SAYTA_VALUE']!=''){
	?>
	<div style="background:<?=$arColor[$tsvet[0]]?>;" title="<?=$arFields['PROPERTY_TSVET_DLYA_SAYTA_VALUE']?>" class="arcolor" onClick="window.location.href='<?=$arFields['DETAIL_PAGE_URL']?>'">
		<?if($tsvet[1]!='') {
		?>
			<div style="background:<?=$arColor[$tsvet[1]]?>;"></div>
		<?}?>
	</div>
	<?
	}
}
endif;
?>
        <span class="main-new__price"><?= $item['OFFERS'][0]['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] ?: $item['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] ;?></span>
        <?if ($item['PROPERTIES']['NEW_ITEM']['VALUE'] == 'Да') {?>
          <div class="main-new__sale">
              <span class="main-new__sale-text">NEW</span>
          </div>
        <?}?>
    </a>
</div>
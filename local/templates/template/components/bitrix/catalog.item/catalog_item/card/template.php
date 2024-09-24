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
//
//if ($item['PREVIEW_PICTURE']['SRC']) {
//	$picture = CFile::ResizeImageGet($item['PREVIEW_PICTURE']['ID'],
//	            ['width' => 450, 'height' => 527],
//	            BX_RESIZE_IMAGE_PROPORTIONAL_ALT
//	        )['src'];
//
//}elseif($item['DETAIL_PICTURE']['SRC']){
//	$picture = CFile::ResizeImageGet($item['DETAIL_PICTURE']['ID'],
//	            ['width' => 450, 'height' => 527],
//	            BX_RESIZE_IMAGE_PROPORTIONAL_ALT
//	        )['src'];
//
//} else {
//    $picture = SITE_TEMPLATE_PATH . '/images/no-photo.png';
//}

/*
if ($item['PREVIEW_PICTURE']['SRC']) {
    $picture = CFile::ResizeImageGet($item['PREVIEW_PICTURE']['ID'],
        ['width' => 288, 'height' => 455],
        BX_RESIZE_IMAGE_PROPORTIONAL_ALT
    )['src'];

}elseif($item['DETAIL_PICTURE']['SRC']){
    $picture = CFile::ResizeImageGet($item['DETAIL_PICTURE']['ID'],
        ['width' => 288, 'height' => 455],
        BX_RESIZE_IMAGE_PROPORTIONAL_ALT
    )['src'];

} else {
    $picture = SITE_TEMPLATE_PATH . '/images/no-photo.png';
}
*/

/*echo '<pre style="display:none;">';
print_r($item['PROPERTIES']);
echo '</pre>';*/

if ($item['PREVIEW_PICTURE']['ID']) {
	$picture = CFile::ResizeImageGet($item['PREVIEW_PICTURE']['ID'], array("width" => 800, "height" => 800))['src'];
} elseif ($item['DETAIL_PICTURE']['ID']) {
	$picture = CFile::ResizeImageGet($item['DETAIL_PICTURE']['ID'], array("width" => 800, "height" => 800))['src'];
} else {
	$picture = SITE_TEMPLATE_PATH . '/images/no-photo.png';
}

$class_search = '';
$array_location = explode('/', $APPLICATION->GetCurPage());
if (in_array('search', $array_location)) {
	$class_search = 'search_page';
}

$slickOn = !empty($item['PROPERTIES']['MORE_PHOTO']['VALUE']);
?>
<div class="<?= $arParams['ITEM_CLASS'] ?: 'main-new__item' ?> <?= $class_search ?>" id="<?= $itemIds['ID']; ?>">
	<a href="<?= $item['DETAIL_PAGE_URL']; ?>" onclick="return false">
		<div class="image-box <?= $slickOn ? 'slick_list' : '' ?>">
			<div>
				<img class="list_img" onclick="window.location.href='<?= $item['DETAIL_PAGE_URL']; ?>'" src="<?= $picture; ?>" alt="<?= $item['PROPERTIES']['CML2_TRAITS']['VALUE'][0] ?> <?= $item['NAME']; ?>">
				<span class="main-new__favorite likefav <?= $span_class; ?>" data-action="<?= $fav_act; ?>" data-add="FAVORITES" data-id="<?= $item['ID']; ?>">
					<img src="<?= $fav_img; ?>" class="<?= $fav_class; ?>">
				</span>
			</div>

			<? if (!empty($item['PROPERTIES']['MORE_PHOTO']['VALUE'])) : ?>
				<? foreach ($item['PROPERTIES']['MORE_PHOTO']['VALUE'] as $key => $img) : ?>
					<?if($key < 4):?>
					<div>
						<img class="list_img"  onclick="window.location.href='<?= $item['DETAIL_PAGE_URL']; ?>'" src="<?= CFile::ResizeImageGet($img, array("width" => 800, "height" => 800))['src']; ?>" alt="<?= $item['PROPERTIES']['CML2_TRAITS']['VALUE'][0] ?> <?= $item['NAME']; ?>">
						<span class="main-new__favorite likefav <?= $span_class; ?>" data-action="<?= $fav_act; ?>" data-add="FAVORITES" data-id="<?= $item['ID']; ?>">
							<img src="<?= $fav_img; ?>" class="<?= $fav_class; ?>">
						</span>
					</div>
					<?endif?>
				<? endforeach; ?>
			<? endif; ?>

		</div>
		<p class="main-new__desc"><?= $item['PROPERTIES']['CML2_TRAITS']['VALUE'][0] ?> <?= $item['NAME']; ?></p>
	</a>

	<div style="float:left;">
		<? //блок цветов
		if ($item['PROPERTIES']['CML2_ARTICLE']['VALUE'] != '') :
		?>
			
			<?
			$cres = CIBlockElement::GetList(array(), array("IBLOCK_ID" => 37, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"), false, array(), array("ID", "IBLOCK_ID", "NAME", "PROPERTY_COLOR"));
			while ($cob = $cres->GetNextElement()) {
				$carFields = $cob->GetFields();
				$arColor[$carFields['NAME']] = '#' . $carFields['PROPERTY_COLOR_VALUE'];
			}
			$arSelect = array("ID", "IBLOCK_ID", "NAME", "PROPERTY_CML2_ARTICLE", "PROPERTY_TSVET_DLYA_SAYTA", "DETAIL_PAGE_URL");
			$arFilter = array("IBLOCK_ID" => 27, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "PROPERTY_CML2_ARTICLE" => $item['PROPERTIES']['CML2_ARTICLE']['VALUE']);
			$res = CIBlockElement::GetList(array(), $arFilter, false, array("nPageSize" => 50), $arSelect);
			while ($ob = $res->GetNextElement()) {
				$arFields = $ob->GetFields();
				//print_r($arFields);
				$tsvet = explode('/', $arFields['PROPERTY_TSVET_DLYA_SAYTA_VALUE']);
				$tsvet[0] = mb_strtolower($tsvet[0]);
				$tsvet[1] = mb_strtolower($tsvet[1]);
				if ($arFields['PROPERTY_TSVET_DLYA_SAYTA_VALUE'] != '') {
			?>
					<div style="background:<?= $arColor[$tsvet[0]] ?>;" title="<?= $arFields['PROPERTY_TSVET_DLYA_SAYTA_VALUE'] ?>" class="arcolor" onClick="window.location.href='<?= $arFields['DETAIL_PAGE_URL'] ?>'">
						<? if ($tsvet[1] != '') {
						?>
							<div style="background:<?= $arColor[$tsvet[1]] ?>;"></div>
						<? } ?>
					</div>
		<?
				}
			}
		endif;
		?>
	</div>
	<p class="main-new__price"><?if($item['OFFERS'][0]['MIN_PRICE']['DISCOUNT_DIFF'] > 0):?><span class="new_price"><?=$item['OFFERS'][0]['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] ?: $item['MIN_PRICE']['PRINT_DISCOUNT_VALUE'];?></span><span class="old_price"><?=$item['OFFERS'][0]['MIN_PRICE']['PRINT_VALUE'] ?: $item['MIN_PRICE']['PRINT_VALUE'];?> </span> <?else:?><?= $item['OFFERS'][0]['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] ?: $item['MIN_PRICE']['PRINT_DISCOUNT_VALUE']; ?><?endif;?></p>
	
	<?
								echo '<pre style="display:none;">';
								print_r($item['OFFERS'][0]['MIN_PRICE']);
								echo '</pre>';
								?>
	<? if ($item['PROPERTIES']['STICKER']['VALUE']) { ?>
		<div class="main-new__sale">
			<span class="main-new__sale-text"><?= $item['PROPERTIES']['STICKER']['VALUE'][0] ?></span>
		</div>
	<? } ?>
</div>
 <?if ($slickOn) :?>
<script>
	$(document).ready(function() {
		$('.slick_list:not(.slick-slider)').slick({
			dots: false,
			infinite: false,
			slidesToShow: 1,
			slidesToScroll: 1,
			infinite: false,
			arrows:false,
			/*prevArrow: '<div class="prev"><img src="/sn.svg"></div>',
			nextArrow: '<div class="next"><img src="/sn.svg"></div>',*/
			responsive: [{
				breakpoint: 767,
				settings: {
					slidesToShow: 1,
					slidesToScroll: 1,
					arrows: false,
					dots: true
				}
			}]
		});
	});
</script>
<?endif;?>
<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);

$haveOffers = !empty($arResult['OFFERS']);
$sizeIsSelected = false;
if (in_array($arResult['ID'], $arParams['FAVORITES'])) {
    $fav_action = 'compfavdelete';
    $fav_class = 'active';
} else {
    $fav_action = 'compfav';
    $fav_class = '';
}

?>
<?/*<script type="module">
import PhotoSwipeLightbox from '/local/templates/template/js/photoswipe-lightbox.esm.min.js';
const lightbox = new PhotoSwipeLightbox({
  gallery: '#my-gallery',
  children: 'a',
  pswpModule: () => import('/local/templates/template/js/photoswipe.esm.min.js')
});
lightbox.init();
</script>
<link rel="stylesheet" href="/local/templates/template/js/photoswipe.css">
*/?>
    <div class="catalog-page__wrap">



<?if ($arResult['CURRENT_ITEM']['GALLERY']){?>

<div class="catalog-page__images" id="my-gallery">

	<div class="slick_detail ">
	<?$cnt=0;
	foreach ($arResult['CURRENT_ITEM']['GALLERY'] as $photoIndex => $arPhoto){$cnt++;?>
		<div class="catalog-page__images-item">
			<a href="<?=CFile::GetPath($arResult['CURRENT_ITEM']['GALLERY_LARGE'][$cnt])?>" class="catalog-page__images-item" data-fancybox="gallery">
				<img src="<?= $arPhoto ?>" alt="<?= sprintf('%s - Фото %s', $arResult['NAME'], $photoIndex + 1) ?>">
			</a>
			<?
			if ($photoIndex ==0) {
				if($arResult['PROPERTIES']['STICKER']['VALUE']){?>
					<?foreach ($arResult['PROPERTIES']['STICKER']['VALUE'] as $arSticker){?>
						<div class="main-new__new">
							<?= $arSticker ?>
						</div>
					<?}?>
				<?}?>
			<?}?>
		</div>
	<?}?>
	</div>

</div>

<?}else{?>

	<div class="catalog-page__images">
		<div class="catalog-page__images-item">
			<div class="pinch-zoom">
				<div href="<?= SITE_TEMPLATE_PATH ?>/images/no-photo.png" class="catalog-page__images-a">
					<img src="<?= SITE_TEMPLATE_PATH ?>/images/no-photo.png" alt="<?= $arResult['NAME'] ?>">
				</div>
			</div>
			<?
			if ($arResult['PROPERTIES']['STICKER']['VALUE']) { ?>
				<?
				foreach ($arResult['PROPERTIES']['STICKER']['VALUE'] as $arSticker) { ?>
					<div class="main-new__new">
						<?= $arSticker ?>
					</div>
				<?
				} ?>
			<?
			} ?>
		</div>

	</div>

<?}?>



		<?/*
        <div class="catalog-page__images-mobile catalog-page__images">
            <?
            if ($arResult['CURRENT_ITEM']['GALLERY']) { ?>
                <?
                foreach ($arResult['CURRENT_ITEM']['GALLERY'] as $photoIndex => $arPhoto) { ?>
                    <div class="catalog-page__images-item">
                        <div class="pinch-zoom">
                            <div href="<?= $arPhoto ?>" class="catalog-page__images-a">
                                <img src="<?= $arPhoto ?>"
                                     alt="<?= sprintf('%s - Фото %s', $arResult['NAME'], $photoIndex + 1) ?>">
                            </div>
                        </div>
                        <?
                        if ($photoIndex == 0) { ?>
                            <?
                            if ($arResult['PROPERTIES']['STICKER']['VALUE']) { ?>
                                <?
                                foreach ($arResult['PROPERTIES']['STICKER']['VALUE'] as $arSticker) { ?>
                                    <div class="main-new__new">
                                        <?= $arSticker ?>
                                    </div>
                                <?
                                } ?>
                            <?
                            } ?>
                        <?
                        } ?>
                    </div>
                <?
                } ?>
            <?
            } else { ?>
                <div class="catalog-page__images-item">
                    <div class="pinch-zoom">
                        <div href="<?= SITE_TEMPLATE_PATH ?>/images/no-photo.png" class="catalog-page__images-a">
                            <img src="<?= SITE_TEMPLATE_PATH ?>/images/no-photo.png" alt="<?= $arResult['NAME'] ?>">
                        </div>
                    </div>
                    <?
                    if ($arResult['PROPERTIES']['STICKER']['VALUE']) { ?>
                        <?
                        foreach ($arResult['PROPERTIES']['STICKER']['VALUE'] as $arSticker) { ?>
                            <div class="main-new__new">
                                <?= $arSticker ?>
                            </div>
                        <?
                        } ?>
                    <?
                    } ?>
                </div>
            <?
            } ?>
        </div>
		*/?>





        <div class="catalog-page__control-wrapper">
            <div class="catalog-page__controls">
                <?
                $helper = new PHPInterface\ComponentHelper($component);
                $helper->deferredCall('ShowNavChain', array('bread'));
                ?>
                <h1 class="catalog-page__title"><?=$arResult['PROPERTIES']['CML2_TRAITS']['VALUE'][0]?> <?= $arResult['NAME'] ?></h1>

<?//блок цветов
	if($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']!=''):
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
$arFilter = Array("IBLOCK_ID"=>27, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "PROPERTY_CML2_ARTICLE" => $arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']);
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
			<div style="float: left;width: 100%;margin-bottom: 20px;"></div>
                <div class="catalog-page__fullprice">
              <?if($arResult['CURRENT_ITEM']['MIN_PRICE']["DISCOUNT_DIFF"] > 0):?> <span class="new_price"><?= $arResult['CURRENT_ITEM']['MIN_PRICE']['PRINT_DISCOUNT_VALUE']?></span><?else:?><?=$arResult['CURRENT_ITEM']['MIN_PRICE']['PRINT_VALUE']  ?><?endif;?>  <?if($arResult['CURRENT_ITEM']['MIN_PRICE']["DISCOUNT_DIFF"] > 0):?> <span class="old_price"><?= $arResult['CURRENT_ITEM']['MIN_PRICE']['PRINT_VALUE_VAT']?></span><?endif;?> 
         
                    <?//= ($arResult['CURRENT_ITEM']['MIN_PRICE']["DISCOUNT_DIFF"] > 0) ? $arResult['CURRENT_ITEM']['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] : $arResult['CURRENT_ITEM']['MIN_PRICE']['PRINT_VALUE'] ?>
                </div>
				<a class="find_ws_price" href="#opt_price" data-fancybox="">
					Узнать оптовую цену
				</a>


                <div class="catalog-page__size">
                    <?
                    if ($arResult['OFFERS']) { ?>
                        <div class="catalog-page__size-box 111 ">
                            <?
                            foreach ($arResult['OFFERS'] as $arOffer) {
                                $active = $arResult['CURRENT_ITEM']['ID'] == $arOffer['ID'] ?>
                                <div class="catalog-page__size-item <?
                                if ($active) { ?>catalog-page__size-item_active<?
                                } ?>" data-id="<?= $arOffer['ID'] ?>">
                                    <span><?= $arOffer['NAME'] ?></span>
                                </div>
                                <?
                                if ($active) {
                                    $sizeIsSelected = true;
                                } ?>
                            <?
                            } ?>
                        </div>
                    <?
                    } ?>
                    <?
                    /*if($arResult['TQ_OFFERS_PROPS']['SIZE']['VALUE']){*/ ?><!--
                        <div class="catalog-page__size-box">
                            <?
                    /*foreach ($arResult['TQ_OFFERS_PROPS']['SIZE']['VALUE'] as $arSize){*/ ?>
                                <div class="catalog-page__size-item <?
                    /*if($arSize['ACTIVE']){*/ ?>catalog-page__size-item_active<?
                    /*}*/ ?>" data-id="<?
                    /*=$arSize['ID']*/ ?>">
                                    <span><?
                    /*=$arSize['VALUE']*/ ?></span>
                                </div>
                              <?
                    /*if($arSize['ACTIVE']){
                                                      $sizeIsSelected = true;
                                                  }*/ ?>
                            <?
                    /*}*/ ?>
                        </div>
                    --><?
                    /*} Рабочий вариант с инфоблоком продукция*/ ?>
                    <span class="catalog-page__size-item-about" data-fancybox data-src="#size-guid">
                      Справочник по размерам
                    </span>

                    <div style="display: none;" id="size-guid">
                        <div class="size-guid__modal">
                            <div class="size-guid__title">
                                Справочник по размерам
                            </div>
                            <div class="table-responsive">
                                <?
                                $APPLICATION->IncludeComponent(
                                    "bitrix:main.include",
                                    "",
                                    array(
                                        "AREA_FILE_SHOW" => "file",
                                        "AREA_FILE_SUFFIX" => "inc",
                                        "EDIT_TEMPLATE" => "",
                                        "PATH" => "/local/include/card/tablesize.php"
                                    )
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?
                /*if($arResult['TQ_OFFERS_PROPS']['COLOR']['VALUE']){*/ ?><!--
                    <div class="catalog-page__color">
                        <div class="color__title">
                                        <span class="color__item">
                                            <?
                /*=$arResult['TQ_OFFERS_PROPS']['COLOR']['NAME']*/ ?>
                                        </span>
                            <?
                /* foreach ($arResult['TQ_OFFERS_PROPS']['COLOR']['VALUE'] as $arColor) {
                                                if($arColor['ACTIVE']) {*/ ?>
                                  <span class="color__select"><?
                /*=$arColor['VALUE']*/ ?></span>
                                <?
                /*}
                                            }*/ ?>
                        </div>
                        <div class="catalog-page__color-box">
                          <?
                /* foreach ($arResult['TQ_OFFERS_PROPS']['COLOR']['VALUE'] as $arColor) {*/ ?>
                            <div class="catalog-page__color-item  <?
                /*if($arColor['ACTIVE']) {*/ ?>catalog-page__color-item_active<?
                /*}*/ ?> " data-name="<?
                /*=$arColor['VALUE']*/ ?>" data-id="<?
                /*=$arColor['ID']*/ ?>">
                                <div class="catalog-page__color-inner " style="background: #<?
                /*=$arColor['XML_ID']*/ ?>;"></div>
                            </div>
                          <?
                /*}*/ ?>
                        </div>
                    </div>
                --><?
                /*} Рабочий вариант с инфоблоком продукция*/ ?>
                <div class="catalog-page__buttons">
                    <?
                    if (!$sizeIsSelected && $haveOffers) { ?>
                        <div class="catalog-page__submit button-big">
                            Добавить в корзину
                            <span class="catalog-page__choose">Выберите размер</span>
                        </div>
                    <?
                    } elseif ($arResult['CURRENT_ITEM']['CAN_BUY']) { ?>
                        <button class="catalog-page__submit button-big" data-id="<?= $arResult['CURRENT_ITEM']['ID'] ?>"
                                data-action="add2basket" data-product="<?= $arResult['ID'] ?>">
                            Добавить в корзину
                        </button>
                    <?
                    } ?>
                    <div class="catalog-page__favorite  likefav  <?= $fav_class ?>">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/images/icons/favorite.svg" class="fav_1" alt="setFavorite">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/images/icons/favorite_active.svg" class="fav_2"
                             alt="unsetFavorite">
                        <a href="javascript:void(0)" class="catalog-page__favorite-text"
                           data-id="<?= $arResult['ID'] ?>" data-action="<?= $fav_action ?>" data-add="FAVORITES"
                           data-type="CARD">
                            В избранное
                        </a>
                    </div>
                </div>
                <?
                if ($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']) { ?>
                    <div class="catalog-page__vendor">
                        <span class="catalog-page__vendor-title">
                            Артикул
                        </span>
                        <?= $arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'] ?>
                    </div>
                <?
                } ?>
                <div class="catalog-page__desc">
                    <?= $arResult['DETAIL_TEXT'] ?>
                    <br/>
                    <?
                    if ($arResult['CURRENT_ITEM']['PROPERTIES']['CML2_ARTICLE']['VALUE']) { ?>
                        Арт. <?= $arResult['CURRENT_ITEM']['PROPERTIES']['CML2_ARTICLE']['VALUE'] ?>
                    <?
                    } ?>
                </div>

                <?
                 $cnt = 0;
                if ($arResult['DISPLAY_PROPERTIES']) { 

                    $cnt = 0;
                    foreach ($arResult['DISPLAY_PROPERTIES'] as $code => $arProperty) {
                        if (empty($arProperty['DISPLAY_VALUE'])) {
                            continue;
                        }
                        $cnt++;

                    } 
               
                } ?>
                

                <?
                if ($cnt > 0) { ?>
                   <div class="catalog-page__info-item dshow delivey_item">
                   <div class="catalog-page__info-title-box">
                                <span class="catalog-page__info-title">
										ХАРАКТЕРИСТИКИ
                                    </span>
                                <span class="catalog-page__info-control">+</span>
                            </div>
                            <div class="catalog-page__info-elements">
                            <?
                    $cnt = 0;
                    foreach ($arResult['DISPLAY_PROPERTIES'] as $code => $arProperty) {
                        if (empty($arProperty['DISPLAY_VALUE'])) {
                            continue;
                        }
                        $cnt++;
                        ?>
                        <div class="catalog-page__info-item ">
                            <!--div class="catalog-page__info-title-box">
                                    <span class="catalog-page__info-title">
										<?= $arProperty['NAME'] ?>
                                    </span>
                                <button class="catalog-page__info-control"><?= $cnt <= 20 ? "-" : "+"; ?></button>
                            </div>
                            <div class="catalog-page__info-elements"
                                 style="<?= $cnt <= 20 ? "overflow: hidden; display: block;" : ""; ?>">
                                 <span class="catalog-page__info-element">
                                     <?
                                     if (is_array($arProperty['DISPLAY_VALUE'])) { ?>
                                         <?= implode(', ', $arProperty['DISPLAY_VALUE']) ?>
                                         <?
                                     } else { ?>
                                         <?= $arProperty['DISPLAY_VALUE'] ?>
                                         <?
                                     } ?>
                                 </span>
                            </div-->
							<table border=0>
								<tr>
									<td><?= $arProperty['NAME'] ?></td>
									<td>
                                 		<span class="catalog-page__info-element">
                                     		<?
                                     		if (is_array($arProperty['DISPLAY_VALUE'])) { ?>
                                     		    <?= implode(', ', $arProperty['DISPLAY_VALUE']) ?>
                                     		    <?
                                    		 } else { ?>
                                    		     <?= $arProperty['DISPLAY_VALUE'] ?>
                                     		    <?
                                    		 } ?>
                                 		</span>
									</td>
								</tr>
							</table>
                        </div>
                        <?
                    } ?>

                            </div>
                            </div>
                    <?
                } ?>
                <?/*
            <div class="catalog-page__info">
                <?
                if ($arResult['DISPLAY_PROPERTIES']) { ?>
                    <?
                    $cnt = 0;
                    foreach ($arResult['DISPLAY_PROPERTIES'] as $code => $arProperty) {
                        if (empty($arProperty['DISPLAY_VALUE'])) {
                            continue;
                        }
                        $cnt++;
                        ?>
                        <div class="catalog-page__info-item <?= $cnt > 0 ? 'dhide' : 'dshow'; ?>">
                            <!--div class="catalog-page__info-title-box">
                                    <span class="catalog-page__info-title">
										<?= $arProperty['NAME'] ?>
                                    </span>
                                <button class="catalog-page__info-control"><?= $cnt <= 20 ? "-" : "+"; ?></button>
                            </div>
                            <div class="catalog-page__info-elements"
                                 style="<?= $cnt <= 20 ? "overflow: hidden; display: block;" : ""; ?>">
                                 <span class="catalog-page__info-element">
                                     <?
                                     if (is_array($arProperty['DISPLAY_VALUE'])) { ?>
                                         <?= implode(', ', $arProperty['DISPLAY_VALUE']) ?>
                                         <?
                                     } else { ?>
                                         <?= $arProperty['DISPLAY_VALUE'] ?>
                                         <?
                                     } ?>
                                 </span>
                            </div-->
							<table border=0>
								<tr>
									<td><?= $arProperty['NAME'] ?></td>
									<td>
                                 		<span class="catalog-page__info-element">
                                     		<?
                                     		if (is_array($arProperty['DISPLAY_VALUE'])) { ?>
                                     		    <?= implode(', ', $arProperty['DISPLAY_VALUE']) ?>
                                     		    <?
                                    		 } else { ?>
                                    		     <?= $arProperty['DISPLAY_VALUE'] ?>
                                     		    <?
                                    		 } ?>
                                 		</span>
									</td>
								</tr>
							</table>
                        </div>
                        <?
                    } ?>
                    <?
                } ?>
                

                <?
                if ($cnt > 0) { ?>
				<!--div class="show_more_detail">ХАРАКТЕРИСТИКИ</div-->
                            <div class="catalog-page__info-title-box show_more_detail">
                                    <span class="catalog-page__info-title">
										ХАРАКТЕРИСТИКИ
                                    </span>
                                <button class="catalog-page__info-control">+</button>
                            </div>
                    <?
                } ?>


            </div>
           */ ?>
            <div class="catalog-page__info-item dshow delivey_item">
                    <div class="catalog-page__info-title-box">
                                <span class="catalog-page__info-title">
                                        Доставка и оплата
                                </span>
                        <span class="catalog-page__info-control">+</span>
                    </div>
                    <div class="catalog-page__info-elements">
                            <span class="catalog-page__info-element">
                                <?
                                $APPLICATION->IncludeComponent(
                                    "bitrix:main.include",
                                    "",
                                    array(
                                        "AREA_FILE_SHOW" => "file",
                                        "AREA_FILE_SUFFIX" => "inc",
                                        "EDIT_TEMPLATE" => "",
                                        "PATH" => "/local/include/card/delivery.php"
                                    )
                                ); ?>
                            </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?
$helper->saveCache();
?>



<div class="sotrudnichestvo-container" id="opt_price">
	<div class="S_title">
		Уважаемые Партнеры!
	</div>
	<div class="S_description">
		Мы приглашаем к сотрудничеству Предпринимателей и Компании, которые ведут торговую деятельность.
		Условия по работе и каталоги с оптовыми ценами Вы можете получить отправив заявку со следующими данными:
	</div>
	<form class="s_form form_order_feedback">
		<input type="hidden" class="code_country" name="CODE_COUNTRY" value="">
		<input type="hidden" name="ID_TOVARA" value="<?=$arResult['ID']?>">
		<input type="hidden" name="NAME_TOVARA" value="<?=$arResult['NAME']?>">
		<div class="in_block">
			<div class="inn_name"><span style="color:red">* </span>Имя</div>
			<input type="text" name="NAME" placeholder="Ваше имя" required>
		</div>
		<div class="in_block">
			<div class="inn_name"><span style="color:red">* </span>Фамилия</div>
			<input type="text" name="SECOND_NAME" placeholder="Ваша фамилия" required>
		</div>
		<div class="in_block">
			<div class="inn_name"><span style="color:red">* </span>Город</div>
			<input type="text" name="CITY" placeholder="Ваш Город" required>
		</div>
		<div class="in_block">
			<div class="inn_name"><span style="color:red">* </span>Телефон</div>

<div class="iti iti--allow-dropdown iti--separate-dial-code">
	<div class="iti__flag-container">
		<div class="iti__selected-flag" >
			<?foreach($arParams['COUNTRY'] as $country){
				if($country['CURRENT']){?>
					<div class="iti__flag iti__<?=$country['CODE_COUNTRY']?>"></div>
			<div class="iti__selected-dial-code">+<?=$country['PHONE_CODE']?></div>
					<div class="iti__arrow"></div>
				<?}?>
			<?}?>
		</div>
		<ul class="iti__country-list iti__hide">
			<?foreach($arParams['COUNTRY'] as $country){?>
			<li class="iti__country iti__standard <?=$country['CURRENT']?"iti__highlight":"";?>" data-phone-code="<?=$country['PHONE_CODE']?>" data-code-country="<?=$country['CODE_COUNTRY']?>">
				<div class="iti__flag-box">
					<div class="iti__flag iti__<?=$country['CODE_COUNTRY']?>"></div>
				</div>
				<span class="iti__country-name"><?=$country['COUNTRY']?></span>
				<span class="iti__dial-code">+<?=$country['PHONE_CODE']?></span>
			</li>
			<?}?>
		</ul>
	
	</div>
	<input type="text" class="my_mask" name="PHONE" required="" style="padding-left: 90px;" placeholder="">
</div>
			<?/*
			<input type="text" class="my_mask" name="PHONE" placeholder="912 345-67-89" required>
			*/?>
		</div>
		<div class="in_block">
			<div class="inn_name">E-mail</div>
			<input type="email" name="EMAIL" placeholder="Ваш E-mail">
		</div>
		<div class="in_block">
			<div class="inn_name">ИНН</div>
			<input type="text" name="INN" placeholder="Ваш ИНН">
		</div>
		<div class="after_send"></div>
		<button>Отправить</button>
		<div class="reg_f">Поля отмеченные <span style="color:red">*</span> обязательные для заполнения</div>
	</form>
</div>

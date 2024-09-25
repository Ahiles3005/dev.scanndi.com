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


<div class="catalog-page__wrap">


    <?
    if ($arResult['CURRENT_ITEM']['GALLERY']) { ?>
        <div class="slick_detail catalog-page__images">
            <?
            $cnt = 0;
            foreach ($arResult['CURRENT_ITEM']['GALLERY'] as $photoIndex => $arPhoto) {
                $cnt++; ?>
                <div class="catalog-page__images-item">
                    <img src="<?= $arPhoto ?>" alt="<?= sprintf('%s - Фото %s', $arResult['NAME'], $photoIndex + 1) ?>">
                    <?
                    if ($photoIndex == 0) {
                        if ($arResult['PROPERTIES']['STICKER']['VALUE']) {
                            ?>
                            <?
                            foreach ($arResult['PROPERTIES']['STICKER']['VALUE'] as $arSticker) {
                                ?>
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
        </div>

        <?
    } else { ?>

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

        <?
    } ?>


    <div class="catalog-page__control-wrapper">
        <div class="catalog-page__controls">
            <?
            $helper = new PHPInterface\ComponentHelper($component);
            $helper->deferredCall('ShowNavChain', array('bread'));
            ?>
            <h1 class="catalog-page__title"><?=$arResult['PROPERTIES']['CML2_TRAITS']['VALUE'][0]?> - <?= $arResult['NAME'] ?></h1>

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
            <?
            if ($arResult['OFFERS']) { ?>
                <?
                $cp = 0;
                foreach ($arResult['OFFERS'] as $offPrice) {
                    $cp++;
                    $cp2 = 0;
                    foreach ($offPrice["PRICES"] as $keyPrice => $priceEl) {
                        $cp2++;
                        if ($cp2 == 1) {
                            $namePrice = 'Цена опт:';
                            $classPrice = '';
                        } else {
                            $namePrice = 'Цена РРЦ:';
                            $classPrice = '2';
                        }
                        ?>
                        <div class="catalog-page__fullprice<?= $classPrice ?>">
                            <?= $namePrice ?> <?if($priceEl["DISCOUNT_DIFF"] > 0):?> <span class="new_price"><?= $priceEl['PRINT_DISCOUNT_VALUE']?></span><?else:?><?=$priceEl['PRINT_VALUE'] ?><?endif;?>  <?if($priceEl["DISCOUNT_DIFF"] > 0):?> <span class="old_price"><?= $priceEl['PRINT_VALUE_VAT']?></span><?endif;?> 
                        </div>
                        <?
                    }
                    if ($cp == 1) {
                        break;
                    }
                } ?>
                <?
            } else { ?>
                <div class="catalog-page__fullprice">
                    <?= ($arResult['CURRENT_ITEM']['MIN_PRICE']["DISCOUNT_DIFF"] > 0) ? $arResult['CURRENT_ITEM']['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] : $arResult['CURRENT_ITEM']['MIN_PRICE']['PRINT_VALUE'] ?>
                </div>
                <?
            } ?>


            <form class="opt_basket_form">
                <div class="catalog-page__size">


                    <?
                    /*TP OPT START*/ ?>
                    <?
                    if ($arResult['OFFERS']) { ?>
                        <div class="opt_offer_list">
                            <div class="all_Descript_store">
                                <div class="title_zag_l">Склад:</div>
                                <div class="title_zag_r">Доставка:</div>
                            </div>
                            <?
                            foreach (TYPE_STORE as $itemType) { ?>
                                <div class="all_Descript_store all_Descript_store_custome">
                                    <div class="title_zag_l"><?= $itemType['TITLE'] ?></div>
                                    <div class="title_zag_r"><?= $itemType['DESCRIPTION'] ?></div>
                                </div>
                                <?
                            } ?>

                            <?
                            $count = 0;
/*
                            ?>
                            <pre style="display:none;">
							<? print_r($arParams['BASKET']) ?>
							<? print_r(TYPE_STORE) ?>
							</pre>
                            <?  */

                            foreach ($arResult['OFFERS'] as $key => $arOffer) {
                                $count++; ?>
                                <div class="all_param_item">
                                    <div class="opt_offer_item opt_offer_item_bg" data-id="<?= $arOffer['ID'] ?>">
                                        <span><?= $arOffer['NAME'] ?></span>
                                    </div>
                                    <?
                                    foreach (TYPE_STORE as $typeId => $itemType) {
                                        $amount = 0;
                                        foreach($itemType['STORE_ID'] as $storeId){
                                            $amount += $arResult['StoreList'][$arOffer['ID']][$storeId]['PRODUCT_AMOUNT'];
                                        }
                                    //foreach ($arResult['StoreList'][$arOffer['ID']] as $opt_store) {

                                        $class_in = "";
                                        $placeholder = "";
                                        $readonly = "";
                                        if ($arParams['BASKET'][$typeId][$arOffer['ID']]['QUANTITY']) {

                                            $placeholder = $arParams['BASKET'][$typeId][$arOffer['ID']]['QUANTITY'];

                                            if ($arParams['BASKET'][$typeId][$arOffer['ID']]['QUANTITY'] == $amount) {

                                                $class_in = "in_basket_item in_basket_item_max";
                                                $readonly = 'readonly';
                                            } else {

                                                $class_in = "in_basket_item";
                                            }
                                        } else {

                                            $placeholder = $amount > 10 ? '>10' : $amount;
                                        }
                                        ?>


                                        <div class="opt_offer_item opt_offer_item_count_sclad <?= $class_in ?>"
                                             data-id="<?= $arOffer['ID'] ?>"
                                             data-store-id="<?= $typeId ?>"
                                        >	Доступно&nbsp;<?= $placeholder ?>
                                            <input type="hidden" name="ID[]" value="<?= $arOffer['ID'] ?>">
                                            <input type="hidden" name="STORE[]" value="<?= $typeId ?>">

                                            <input type="number" name="CNT[]"
                                                   value="<?= $arParams['BASKET'][$typeId][$arOffer['ID']]['QUANTITY'] ?>"
                                                   class="inoffer_js"
                                                   max="<?= $amount ?>"
                                                   placeholder="0<?//= $placeholder ?>"
                                                <?
                                                //=$readonly
                                                ?>

                                            >

                                        </div>
                                        <?
                                    } ?>
                                </div>

                                <?
                            } ?>
                        </div>


                        <?
                        /*
                        <div class="opt_offer_list">
                        <?
                        $count=0;foreach ($arResult['OFFERS'] as $key=>$arOffer) {$count++;?>
                            <div class="all_param_item">
                                <div class="opt_offer_item opt_offer_item_bg" data-id="<?= $arOffer['ID'] ?>">
                                    <span><?=$arOffer['NAME']?></span>
                                </div>
                                <?$num=0;
                                foreach($arResult['StoreList'][$arOffer['ID']] as $opt_store){$num++;
                                    $top_css='';
                                    if($num==1){
                                        $top_css=$num*40;
                                    }else{
                                        $top_css=$num*40+25*($num-1);
                                    }
                                    ?>
                                    <div class="title_zag_l" style="top:<?=$top_css?>px"><?=$key==0?$opt_store['TITLE']:''?></div>
                                    <div class="title_zag_r" style="top:<?=$top_css?>px"><?=count($arResult['OFFERS'])==$count?$opt_store['DESCRIPTION']:''?></div>
                                    <div class="opt_offer_item"
                                        data-id="<?= $arOffer['ID'] ?>"
                                        data-store-id="<?= $opt_store['ID'] ?>"
                                        >
                                        <input type="hidden" name="ID[]" value="<?= $arOffer['ID'] ?>">
                                        <input type="hidden" name="STORE[]" value="<?= $opt_store['ID'] ?>">
                                        <input type="number" name="CNT[]" class="inoffer_js" max="<?=$opt_store['PRODUCT_AMOUNT']?>" placeholder="<?=$opt_store['PRODUCT_AMOUNT']>10?'>10':$opt_store['PRODUCT_AMOUNT'];?>">
                                    </div>
                                <?}?>
                            </div>
                        <?}?>
                        </div>
                                            */ ?>
                        <?
                    } ?>
                    <?
                    /*TP OPT END*/ ?>


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


                <div class="catalog-page__buttons allow_basket_js">
                    <div class="catalog-page__submit button-big  ">
                        Добавить в корзину
                        <span class="catalog-page__choose">Выберите размер</span>
                    </div>
                </div>
            </form>
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


<div class="inform-catalog-page" id="inform_basket" style="display: none;">
    <div class="inform-catalog-page__box">
		<span class="auth__close close_tov_inbasket_js">
			<img src="/local/templates/template/images/icons/close.svg" alt="close">
		</span>
        <span class="inform-catalog-page__title">Товар добавлен в корзину</span>
        <div class="inform-catalog-page__goods">
            <img src="/upload/resize_cache/iblock/b85/86_116_1/b85c8bf7ebbb447431875b2edb8ca7a4.jpg" alt="image"
                 class="img_popup_js inform-catalog-page__goods-image" id="img-popup">
            <div class="inform-catalog-page__goods-about-box">
                <div class="inform-catalog-page__goods-title" id="title-popup-ru"><?= $arResult['NAME'] ?></div>
            </div>
        </div>
        <div class="inform-catalog-page__controls">
            <a href="/cart/" class="button-big">оформить заказ</a>
            <span class="inform-catalog-page__close close_tov_inbasket_js">продолжить покупки</span>
        </div>
    </div>
</div>
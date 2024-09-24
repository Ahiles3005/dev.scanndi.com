<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Sale\DiscountCouponsManager;
?>
<?/*
ВЕРНУТЬ СКИДКУ
1)раскомментировать в блоке sklad_info_name
2)в файле компонента script.js расскоментировать обытие с классом percend_pay
3)Удалить строки "Обнулить дисконт" 56-60
4)Расскоментировать строки 50-55
5)Расскоментировать строки 64-68
*/?>



<div class="main__empty main__basket-emptymain__empty">
	<div class="basket basket_js">
	<?if(!$_GET['ORDER_ID']){?>


		<?if(count($arResult["GRID"]["ROWS"])>0){?>

<?/*Корзина*/?>
<h1 class="basket_title">Оформление заказа</h1>
<div class="basket_main_opt">
<?
/*Количество товара не из склада 1*/
foreach ($arResult['OFFERS'] as $key1=>$arItem){ 
	foreach($arItem as $key_off=>$off){
		foreach($arResult['STORE'][$off['ID']] as $store){
			if($store['ID']!='19'){
				$nineteen+=$arResult['ELEMENTS'][$off['ID']][$store['ID']]['QUANTITY'];
			}
			$final_price_test+=$arResult['ELEMENTS'][$off['ID']][$store['ID']]['QUANTITY']*$arResult['ELEMENTS'][$off['ID']][$store['ID']]['PRICE'];

		}
	}
}

$Discount='0';

if($final_price_test>=5000000){
	$Discount='7';
}elseif($final_price_test>=3000000){
	$Discount='5';
}elseif($final_price_test>=2000000){
	$Discount='3';
}elseif($final_price_test>=1000000){
	$Discount='2';
}elseif($final_price_test>=500000){
	$Discount='1';
}

/*
$default_discount='100';
$DD=['30','50','70','100'];
$OnePersend=$final_price_test/100;//один процент от суммы
$curent_discount_percend=$default_discount/10;
*/
/*Обнулить дисконт*/
$DD=['30','50','70','100'];
$default_discount='100';
$curent_discount_percend=0;
/*Обнулить дисконт*/


$first_discount=0;
/*
if($nineteen){
	$first_discount=10;
}
*/
function calc_percent($value, $percent) {
	return $value * ($percent / 100); 
}


$final_price='';
$final_quantity='';


foreach ($arResult['OFFERS'] as $key1=>$arItem){ 
	$file = CFile::ResizeImageGet($arResult['MAIN_ITEM'][$key1]['PREVIEW_PICTURE'], array('width'=>100, 'height'=>150), BX_RESIZE_IMAGE_PROPORTIONAL, true);
	$full_summ_offer='';
	$full_quantity_offer='';
	?>
	<div class="basket__item_opt basket__item_opt_js">
		<div class="artnumber"><?=$arResult['MAIN_ITEM'][$key1]['NAME']?></div>
		<div class="info_store_left">
			<div class="top_item_name">
				<?if($arResult['OfferPrice94'][$key1]){?>
					<div class="tin tin_1" data-price="<?=$arResult['OfferPrice95'][$key1]?>">Базовая: <br> <?=number_format($arResult['OfferPrice95'][$key1], 0, ' ', ' ')?> руб.</div>
				<?}?>
				<div class="tin tin_2">С учетом скидок: <br> <span>
					<?echo number_format(round($arResult['OfferPrice95'][$key1]-calc_percent($arResult['OfferPrice95'][$key1], $first_discount)-calc_percent($arResult['OfferPrice95'][$key1], $curent_discount_percend)-calc_percent($arResult['OfferPrice95'][$key1], $Discount)), 0, ' ', ' ');?> руб.
				</span></div>
				<?if($arResult['OfferPrice94'][$key1]){?>
					<div class="tin tin_3">РРЦ: <br> <?=number_format($arResult['OfferPrice94'][$key1], 0, ' ', ' ')?> руб.</div>
				<?}?>
			</div>
			<div class="aimg" href="<?=$arResult['MAIN_ITEM'][$key1]['DETAIL_PAGE_URL']?>">
				<img src="<?=$file['src']?>" alt="<?=$arResult['MAIN_ITEM'][$key1]['NAME']?>" class="inform-catalog-page__goods-image">
				<?foreach($arItem as $key2=>$offer){?>
				<div class="opt_offer_item opt_offer_item_bg" data-id="<?=$key2?>">
					<div class="item_size"><?=$offer['NAME']?></div>
				</div>
				<?}?>
			</div>
		</div>
		<div class="info_store_right">
			<div class="aimg aimg_mob">
				<?foreach($arItem as $key3=>$offer){?>
				<div class="opt_offer_item opt_offer_item_bg opt_offer_item_mob" data-id="<?=$key3?>">
					<div class="item_size"><?=$offer['NAME']?></div>
				</div>
				<?}?>
			</div>
			<?foreach (TYPE_STORE as $typeId => $itemType) {?>
				<div class="sklad_info sklad_info<?=$typeId?>">
					<div class="sklad_info_name">
						<?=$itemType['TITLE']?><?/*=$store_p['ID']!='19'?"<span> - 10%</span>":"";*/?>
					</div>
					<div class="sklad_info_description">
						<?=$itemType['DESCRIPTION']?>
					</div>
				</div>
			<?}?>
<?foreach($arItem as $key_off=>$off){?>
<div class="all_param_item">

	<?/*
		<pre><?print_r('ERROR_TEST')?></pre>
		<div class="opt_offer_item opt_offer_item_js" >
			<div class="minus minus_pa">-</div>
			<input  type="number"  value=""  name="CNT"   class="basket_store_quantity inoffer_js in_number_js basket_store_quantity_pa"    placeholder="0" readonly>
			<div class="plus plus_pa">+</div>
		</div>

*/?>

		<?
        foreach (TYPE_STORE as $typeId => $itemType) {
        //foreach($arResult['STORE'][$off['ID']] as $store){
            $amount = 0;
            foreach($itemType['STORE_ID'] as $storeId){
                $amount += $arResult['STORE'][$off['ID']][$storeId]['PRODUCT_AMOUNT'];
            }
        ?>

			<div class="opt_offer_item opt_offer_item_js" >
				<div class="minus <?=$store["PRODUCT_AMOUNT"]!=0?"minus_js":"minus_pa";?>">-</div>
	
				<input  type="number"  value="<?=$arResult['ELEMENTS'][$off['ID']][$typeId]['QUANTITY']?>"  name="CNT" data-del-id="<?=$arResult['ELEMENTS'][$key_off][$typeId]['ID']?>" data-id="<?=$key_off?>" data-store="<?=$typeId?>" class="basket_store_quantity inoffer_js in_number_js <?=$arResult['ELEMENTS'][$off['ID']][$typeId]['QUANTITY']>0?"color_change":"";?> <?=$amount==0?"basket_store_quantity_pa":"";?>"   max="<?=$amount?>" placeholder="<?=$amount>=10?">10":$amount;?>" <?=$amount==0?"readonly":"";?>>
	
				<div class="plus <?=$amount!=0?"plus_js":"plus_pa";?>">+</div>
			</div>
			<?
			$full_summ_offer+=$arResult['ELEMENTS'][$off['ID']][$typeId]['QUANTITY']*$arResult['ELEMENTS'][$off['ID']][$typeId]['PRICE'];
			$full_quantity_offer+=$arResult['ELEMENTS'][$off['ID']][$typeId]['QUANTITY'];
			$final_price+=$arResult['ELEMENTS'][$off['ID']][$typeId]['QUANTITY']*$arResult['ELEMENTS'][$typeId][$store['ID']]['PRICE'];
			$final_quantity+=$arResult['ELEMENTS'][$off['ID']][$typeId]['QUANTITY'];
			?>

		<?}?>


</div>
<?}?>

<div class="all_param_item all_param_item_store">
	<?
    foreach (TYPE_STORE as $typeId => $itemType) {
	?>
		<div class="sklad_info">
			<div class="itogo">
				Итого (без скидок): <br><span class="srote_info_item_price<?=$typeId?>">0 руб.</span><br>
				Итого (шт): <span class="srote_info_item_quantity<?=$typeId?>">0</span>
			</div>
		</div>
	<?}?>
</div>

		</div>
<div style="width: 100%;    display: inline-block;">
		<div class="delete_all_item basket__delete_opt_js" data-action="delete" data-id="<?=$arResult['MAIN_ITEM'][$key1]['ID']?>">
			Удалить из заказа
		</div>
</div>
		<div class="top_item_name">
			<div class="itogo">
				Итого по всем складам: <span class="current_price_js" data-price="<?=$arResult['OfferPrice95'][$key1]?>"><?=number_format($full_summ_offer, 0, ' ', ' ')?></span> руб.<br>
				Итого (шт) по всем складам: <span class="count_opt_js"><?=$full_quantity_offer?></span>
			</div>
		</div>
	</div>
	<?}?>
	<div class="all_summ">
		<div class="info_store_left">
			<div class="top_item_name">
				<div class="itogo">
					Итого по всем складам: <span class="full_price_js"><?=number_format($final_price, 0, ' ', ' ')?></span> руб.<br>
					Итого (шт) по всем складам: <span class="full_quantity_js"><?=$final_quantity?></span>
				</div>
			</div>
		</div>

		<div class="info_store_right">
			<div class="all_param_item all_param_item_all_store">

				<?
                foreach (TYPE_STORE as $typeId => $itemType) {
				?>
				<div class="sklad_info">
					<div class="itogo">
				Итого склад <?=$itemType['TITLE']?>: <br>
                        <span class="all_rote_info_item_price<?=$typeId?>">0 руб.</span><br>
				Итого (шт): <span class="all_srote_info_item_quantity<?=$typeId?>">0</span>

					</div>
				</div>
				<?}?>

			</div>
		</div>


	</div>
</div>


<?/*Промокод*/?>
			<div class="basket__names order-checkout" id="order_form_div">
				<div class="bx_order_make">
					<form>
						<div id="order_form_content_opt">
							<div class="basket__names_opt">
								<div class="names__item basket__register">
									<input name="PROMO" placeholder="Промокод" value="<?=$_GET['PROMO']?>">
									<input type="submit" value="Применить промо-код">
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>


<?/*Оформление*/?>
			<div id="order_form_div">
				<div class="bx_order_make">
					<form class="opt_order_ajax">
						<div id="tq_error"></div>
						<div id="order_form_content_opt">

							<input type="hidden" name="EMAIL" value="<?=$arParams['USER']['EMAIL']?>">
							<input type="hidden" name="NAME" value="<?=$arParams['USER']['NAME']?mb_substr($arParams['USER']['NAME'], 0, 1).".":"";?><?=$arParams['USER']['SECOND_NAME']?mb_substr($arParams['USER']['SECOND_NAME'], 0, 1).".":"";?><?=$arParams['USER']['LAST_NAME']?$arParams['USER']['LAST_NAME']:"";?>">
							<input type="hidden" name="PHONE" value="<?=$arParams['USER']['PERSONAL_PHONE']?>">

							<input type="hidden" name="PERSON_TYPE" value="2">
							<input type="hidden" name="PAY_SYSTEM_ID" value="2">
							<input type="hidden" name="DELIVERY_ID" value="9">
							<div class="basket__names_opt">
								<div class="lk_show_block">
									Заказ от: <?=$arParams['USER']['NAME']?mb_substr($arParams['USER']['NAME'], 0, 1).".":"";?><?=$arParams['USER']['SECOND_NAME']?mb_substr($arParams['USER']['SECOND_NAME'], 0, 1).".":"";?><?=$arParams['USER']['LAST_NAME']?$arParams['USER']['LAST_NAME']:"";?>
								</div>
								<div class="names__item basket__register">
									<label class="names__label basket__label">
									<textarea name="COMMENT" id="comment" placeholder="Комментарий к заказу" class="basket__comment"></textarea>
									Комментарий
									</label>
								</div><?print_r($parFields);?>
								<button id="sendzak" class="button-large basket__info-button tq_save_order">
									оформить заказ
								</button>
								<div class="basket__info-rules">Завершая оформление заказа, я соглашаюсь с <a href="/privacy_policy/" class="basket__info-rules-link">правилами обработки информации.</a></div>
							</div>
							<div class="bx_ordercart basket__info-wrapper_opt">
								<div class="basket__info_OPT ">
									<div class="basket__info_OPT_js">
										<?foreach($arResult['PRICE'] as $store_key=>$s){?>
										<div class="basket__info-item">
											<span class="basket__info-title">Итого: <?=$arResult['STORE_PRICE'][$store_key]['TITLE']?></span>
											<span class="basket__info-price tq_basket_sum"><?=number_format($s, 0, ' ', ' ')?> руб.</span>
										</div>
										<?}?>
									</div>
									<div class="basket__info-item">
										<span class="basket__info-title">Итого:</span>
										<span class="basket__info-price tq_total "><span class="full_price_js"><?=number_format($final_price, 0, ' ', ' ')?></span> руб.</span>
									</div>
<?

//скидка по промокоду
$Promo_Discount=0;
if($_GET['PROMO']!='') {
	$pres = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>38, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "PROPERTY_PROMO_CODE" => $_GET['PROMO']), false, Array(), Array("ID", "IBLOCK_ID", "NAME", "PROPERTY_PROMO_CODE","PROPERTY_COUNT","PROPERTY_DISCOUNT_PROC","PROPERTY_DISCOUNT_FIX"));
	while($pob = $pres->GetNextElement()){ 
		$parFields = $pob->GetFields();  
		//print_r($parFields);
		$Promo_Discount=$parFields['PROPERTY_DISCOUNT_PROC_VALUE'];
		if($Promo_Discount==0) $Promo_Discount=round(($parFields['PROPERTY_DISCOUNT_FIX_VALUE']/$final_price_test)*100,0);
	}
}

?>
<div class="basket__info-item">
	<span class="basket__info-title">Скидка на сумму заказа:</span>
	<span class="basket__info-price tq_total percend_css"><span><?=$Discount?>%</span></span>
</div>
<div class="basket__info-item">
	<span class="basket__info-title">Скидка по промокоду:</span>
	<span class="basket__info-price tq_total percend_css"><span><?=$Promo_Discount?>%</span></span>
	<?$Discount+=$Promo_Discount;//увеличим общую скидку на промо?>
</div>
<?if(count($DD)>0){?>
<div class="basket__info-item">
	<span class="basket__info-title">Размер вносимой предоплаты %:</span>
	<span class="basket__info-price tq_delivery_price">
		<?foreach($DD as $skidka){?>
			<div class="percend_pay <?=$skidka==$default_discount?"active":""?>"><?=$skidka?></div>
		<?}?>
		<input type="hidden" class="in_percend_pay" name="PERCEND_PAY" value="<?=$default_discount?>">
	</span>
</div>
<?}?>
<div class="basket__info-item">
	<span class="basket__info-title">Cкидка за предоплату</span>
	<span class="basket__info-price tq_basket_sum percend_css"><span><?=$curent_discount_percend?>%</span></span>
</div>
<div class="basket__info-item">
	<span class="basket__info-title">Сумма предоплаты</span>
	<input type="hidden" class="in_full_price " name="FULL_PRICE" 
	data-discount1="<?=$first_discount?>" 
	data-discount2="<?=$Discount?>" 
	data-discount3="<?=$curent_discount_percend?>" 
	value="<?=$final_price?>">
	<span class="basket__info-price tq_basket_sum pred_price_js  ">
		<span class="from_sale_price_js"><?=number_format(round($final_price-calc_percent($final_price, $curent_discount_percend)), 0, ' ', ' ')?></span> руб.
	</span>
</div>
<div class="basket__info-item">
	<span class="basket__info-title">Общая скидка:</span>
<input type="hidden" name="FULL_PERCEND" class="full_percend_order" value="<?=$curent_discount_percend+$Discount+$first_discount?>">
<input type="hidden" name="SALE_PERCEND" class="sale_percend_order" value="<?=$curent_discount_percend?>">

<input type="hidden" name="SUMMA_PREDOPLATU" class="summa_predoplatu_order" value="<?=number_format(round($final_price-calc_percent($final_price, $curent_discount_percend)), 0, ' ', ' ')?> .руб">

	<span class="basket__info-price tq_total tq_total_last percend_css"><span><?=$curent_discount_percend+$Discount+$first_discount?>%</span></span>
</div>
<div class="basket__info-item">
	<span class="basket__info-title">Итого с учетом скидок:</span>
	<span class="basket__info-price tq_basket_sum tq_basket_sum_all">
		<?=number_format(round($final_price-calc_percent($final_price, $first_discount)-calc_percent($final_price, $curent_discount_percend)-calc_percent($final_price, $Discount)), 0, ' ', ' ')?> руб.
	</span>
</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		<?}else{?>
			<div class="empty_text">Ваша корзина пуста</div>
			<div class="empty_main"><a href="/">Нажмите здесь</a>, чтобы продолжить покупки</div>
		<?unset($_SESSION['SAVE_BASKET'])?>
		<?}?>

	<?}else{?>

		<?if (!($arOrder = CSaleOrder::GetByID($_GET['ORDER_ID']))){?>
		
		<table class="sale_order_full_table_opt">
			<tbody>
				<tr>
					<td>
						Заказ с кодом "<?=$_GET['ORDER_ID']?>" не найден
					</td>
				</tr>
			</tbody>
		</table>
		
		<?}else{?>

		<table class="sale_order_full_table_opt">
			<tbody>
				<tr>
					<td>

						Ваш заказ <b>№<?=$arOrder['ID']?></b> от <?=FormatDate("d F Y", strtotime($arOrder['DATE_INSERT']))?> успешно создан.
						<br><br>
						Вы можете следить за выполнением своего заказа в <a href="/personal/">
						Персональном разделе сайта</a>. <br>Обратите внимание, что для входа в этот 
						раздел вам необходимо будет ввести логин и пароль пользователя сайта.
					</td>
				</tr>
			</tbody>
		</table>
		
		<?}?>

	<?}?>

	</div>
</div>

<div class="loading_gif">
	<img src="/images/loading.gif">
<div>
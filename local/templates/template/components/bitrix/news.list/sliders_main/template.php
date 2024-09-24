<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

?>

<?foreach($arResult["ITEMS"] as $arItem){
	if($arItem['PROPERTIES']['TYPE']['VALUE']){
		$Slider[$arItem['PROPERTIES']['TYPE']['VALUE_XML_ID']][]=$arItem;
	}
}?>

<?
  function LogPrint($text)
  {
    $text=json_encode($text, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if(!preg_match("/\n$/s", $text))
      $text.="\n";
    $hf=fopen("/home/s/scanndi/scanndifinland.ru/public_html/local/templates/template/components/bitrix/news.list/sliders_main/log", "ab");
    fwrite($hf, $text);
    fclose($hf);
  }
//  LogPrint($Slider['top'][0]['PROPERTIES']['XTEXT']['VALUE']);
//  LogPrint($Slider['mobile']);
?>

<?if(count($Slider['video'])>0){?>
<div class="main-top">
<?foreach($Slider['video'] as $video){?>
	<video class="main-top__video main-top__image_mobile" loop="" autoplay="" playsinline="" muted="" src="<?= CFile::GetPath($video['PROPERTIES']['VIDEO']['VALUE']);?>" poster=""></video>
<?}?>
</div>
<?}?>

<?if(count($Slider['top'])>0){?>
<div class="top_slider_items top_slider_items_desktop top_slider_items_js">
	<?foreach($Slider['top'] as $top){?>
	<div class="top_slider_item">
    <a href="<?=$top['PROPERTIES']['LINK']['VALUE']?$top['PROPERTIES']['LINK']['VALUE']:"javascript:void(0);";?>">
			<img src="<?=CFile::GetPath($top['PROPERTIES']['IMG']['VALUE']);?>" alt="<?=$top['NAME']?>">
      <div class='xtext'><?=$top['PROPERTIES']['XTEXT']['VALUE']['TEXT']?>.</div>
		</a>
	</div>
	<?}?>
</div>

<?}?>

<?if(count($Slider['mobile'])>0){?>
<div class="top_slider_items top_slider_items_mobile top_slider_items_js">
    <?foreach($Slider['mobile'] as $top){?>
        <div class="top_slider_item">
            <a href="<?=$top['PROPERTIES']['LINK']['VALUE']?$top['PROPERTIES']['LINK']['VALUE']:"javascript:void(0);";?>">
                <img src="<?=CFile::GetPath($top['PROPERTIES']['IMG']['VALUE']);?>" alt="<?=$top['NAME']?>">

            </a>
            <?/*<div class='xtext xtext_mobile'>
                <?=$top['PROPERTIES']['XTEXT']['~VALUE']['TEXT']?>

                <a class="xtext_button_mobile" href="<?=$top['PROPERTIES']['LINK']['VALUE'];?>">Купить</a>
            </div>*/?>
        </div>
    <?}?>
</div>
<?}?>


<?if(count($Slider['center'])>0){?>
<div class="center_slider_items center_slider_items_js">
	<?foreach($Slider['center'] as $center){?>
	<div class="center_slider_item">
		<a href="<?=$center['PROPERTIES']['LINK']['VALUE']?$center['PROPERTIES']['LINK']['VALUE']:"javascript:void(0);";?>">
			<img src="<?=CFile::GetPath($center['PROPERTIES']['IMG']['VALUE']);?>" alt="<?=$center['NAME']?>">
		</a>
	</div>
	<?}?>
</div>
<?}?>

<?if(count($Slider['bottom'])>0){?>
<div class="bottom_slider_items bottom_slider_items_js">
	<?foreach($Slider['bottom'] as $bottom){?>
	<div class="bottom_slider_item">
		<a href="<?=$bottom['PROPERTIES']['LINK']['VALUE']?$bottom['PROPERTIES']['LINK']['VALUE']:"javascript:void(0);";?>">
			<img src="<?=CFile::GetPath($bottom['PROPERTIES']['IMG']['VALUE']);?>" alt="<?=$bottom['NAME']?>">
		</a>
	</div>
	<?}?>
</div>
<?}?>

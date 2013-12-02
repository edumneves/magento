<?php
class Infortis_UltraSlideshow_Block_Slideshow extends Mage_Core_Block_Template
{	
	/**
	 * Get array of static block identifiers. Blocks will be displayed as slides.
	 *
	 * @return array
	 */
	public function getStaticBlockIds()
	{
		$blockIdsString = Mage::helper('ultraslideshow')->getCfg('general/blocks');
		$blockIds = explode(",", str_replace(" ", "", $blockIdsString));
		return $blockIds;
	}
	
	/**
	 * Get content of the static block which contains additional banners for the slideshow.
	 *
	 * @return string
	 */
	public function getBanners()
	{
		$bid = Mage::helper('ultraslideshow')->getCfg('banners/banners');
		return $this->getLayout()->createBlock('cms/block')->setBlockId($bid)->toHtml();
	}
}
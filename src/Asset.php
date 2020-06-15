<?php

	namespace mallka\ztree;

	use yii\web\AssetBundle;

	class Asset extends AssetBundle
	{

		public $css
			= [
				'css/zTreeStyle/zTreeStyle.css',
			];
		public $js
			= [
				'js/jquery.ztree.core.js',
				'js/jquery.ztree.excheck.js',
			];

		public $depends
			= [
				'yii\web\JqueryAsset',
			];

		public function init()
		{
			$this->sourcePath = __DIR__ . '/resources';
			parent::init();
		}
	}

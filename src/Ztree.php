<?php

	namespace mallka\ztree;

	use http\Exception\InvalidArgumentException;
	use yii\bootstrap4\InputWidget;
	use yii\helpers\Html;
	use yii\web\JsExpression;

	class Ztree extends InputWidget
	{

		/**
		 * 实例化对象id
		 *
		 * @var string
		 */
		public $eleId = 'treeDemo';

		/**
		 * @var array  选中的值，[1,2,3]
		 */
		public $selVal = [];

		/**
		 * 当用户在ztree上操作时，ztree实时得到选中后的id集合，以逗号分隔组合起来的字符串，
		 * 将该字符串存储在该id值里。
		 * 如果允许选中，则该值建议设置
		 *
		 * @var string
		 */
		public $selValEleId = '';

		/**
		 * @var  ztree成功的callback函数
		 */
		private $onSuccFunc;

		/**
		 * @var ztree的callback函数
		 */
		private $onCheckFunc;

		/**
		 * @var radio/checkbox的是否只读
		 */
		private $readOnlyfunc;

		/**
		 * 有Radio或者checkbox时，是否只读。
		 *
		 * @var bool
		 */
		public $readOnly = false;

		/**
		 * @var  数据源
		 * 数据可以为数组，或者model的数据集(数组形式)
		 * 例如：
		 * 1、[
		 * 'data'=>[
		 * ['id'=>1, 'pId'=>0, 'name'=>'目录1'],
		 * ['id'=>2, 'pId'=>1, 'name'=>'目录2'],
		 * ['id'=>3, 'pId'=>1, 'name'=>'目录3'],
		 * ['id'=>4, 'pId'=>1, 'name'=>'目录4'],
		 * ['id'=>5, 'pId'=>2, 'name'=>'目录5'],
		 * ['id'=>6, 'pId'=>3, 'name'=>'目录6']
		 * ]
		 * ]
		 *
		 * 2、array(
		 * 'data'=>Model::model()->findAll()
		 * )
		 *
		 * 3、异步请求，需要先设置data_ajax为true，此时data属性等于ztree的async配置中的url值对象
		 * [
		 * enable: true,
		 * type: "get",
		 * url: "../product-category/ajaxnode",
		 * autoParam: ["id", "name"]
		 * ]
		 *
		 * yii2的控制器action方法一般如此写：
		 *
		 * #返回值应该每一行有 id\pId\name三个值，否则出问题
		 * function actionAjaxnode()
		 * {
		 * $db = new Query;
		 * $cat = $db->from('product_category')->orderBy('id asc')->all();
		 * for($v=1;$v<count($cat);$v++){
		 * $pid = $cat[$v]['parent_id'];
		 * $cat[$v]['pId'] = $pid;
		 * }
		 * return json_encode($cat);
		 * }
		 *
		 *
		 */
		public $data;

		/**
		 * @var bool  决定了$data是否是异步数据
		 */
		public $data_ajax = false;

		/**
		 * @var bool 多选
		 */
		public $checkbox = false;

		/**
		 * @var bool 单选
		 */
		public $radio = false;

		/**
		 * Css hack，控制ztree的布局。一级分类横向展示
		 *
		 * @var boolean
		 */
		public $align;

		public function init()
		{
			if ($this->onCheckFunc == null)
				$this->onCheckFunc = 'onCheck' . time();

			if ($this->onSuccFunc == null)
				$this->onSuccFunc = 'onSucc' . time();

			if ($this->readOnlyfunc == null)
				$this->readOnlyfunc = 'readOnly' . time();

			if ($this->align == true)
				$this->align = new JsExpression("\$('.ztree >li').css('float','left');$('.ztree >li').css('display','inline');");

			//如果是作为model的widget方式传进来。
			if (isset($this->model) && isset($this->attribute)) {
				$attr         = $this->attribute;
				$this->selVal = $this->model->$attr;
			}

			if ($this->hasModel()) {
				$attr         = $this->attribute;
				$val          = $this->model->{$attr};
				$this->selVal = $this->model->$attr;
				if (strpos($this->selVal, ','))
					$this->selVal = explode(',', $this->selVal);
				$this->eleId       = strtolower($this->model->formName()) . '-' . $this->attribute . '-ztree';
				$this->selValEleId = strtolower($this->model->formName()) . '-' . $this->attribute;
			}

		}

		public function run()
		{
			$view = $this->getView();
			Asset::register($view);

			$js = $this->_render();
			$this->getView()->registerJs($js, \yii\web\View::POS_END);

			//input
			$input = '';
			if ($this->hasModel()) {
				$attr  = $this->attribute;
				$val   = $this->model->{$attr};
				$opt   = [
					'id'    => strtolower($this->model->formName()) . '-' . $this->attribute,
					'class' => 'form-control',
				];
				$input = Html::input('hidden', $this->model->formName() . '[' . $this->attribute . ']', $val, $opt);
			}

			$str
				= <<< E
			 $input
			 <ul id="$this->eleId" class="ztree"></ul>
E;
			return $str;

		}

		/**
		 * 1.获取节点是否多选/单选/或无任何操作
		 *
		 * @return string
		 */
		public function _checkStyleSetting()
		{
			if ($this->checkbox || $this->radio) {
				if ($this->checkbox && $this->radio) {
					throw  new InvalidArgumentException('单选和多选只能二选一');
				}

				if ($this->checkbox) {
					$str
						= <<<Eof
check: {
	enable: true,
	chkboxType : { "Y" : "ps", "N" : "ps" },
},
				
Eof;
					return $str;
				}elseif ($this->radio) {
					$str
						= <<<Eof
check: {
	enable: true,
	chkStyle:'radio',
	radioType:'all',
	chkboxType : { "Y" : "ps", "N" : "ps" },
},

Eof;
					return $str;
				}

			}

			return '';
		}

		/**
		 * 2.获取数据显示方式的设置
		 *
		 * @return string
		 */
		public function _getDataSetting()
		{
			$str
				= <<< EOF
data: {
	simpleData: {
		enable: true
		},
},
	
EOF;
			return $str;
		}

		/**
		 * 3.获取ajax异步获取的配置
		 *
		 * @return string
		 */
		public function _getUrlSetting()
		{
			if ($this->data_ajax) {
				$str
					= <<<EOF
async: {
	enable: true,
		type: "get",
		url: "{$this->data}",
		autoParam: ["id", "name"]
	},
EOF;
				return $str;
			}else {
				return '';
			}

		}

		public function onSyncSuccess()
		{
			$selValStr = '';
			if ($this->radio) {
				if (!empty($this->selVal))
					$selValStr = '[' . $this->selVal[0] . ']';
			}elseif ($this->checkbox) {
				if (!empty($this->selVal) )
				{
					if(is_array($this->selVal)){
						$selValStr = '[' . implode(',', $this->selVal) . ']';
					}
					else{
						$selValStr = '[' . $this->selVal. ']';

					}
				}

			}

			$readonly = $this->readOnly ? " $this->readOnlyfunc();" : '';

			$str
				= <<< EOF
	function {$this->onSuccFunc} () {
		var zTree = $.fn.zTree.getZTreeObj("{$this->eleId}");
		var allcatid="$selValStr";
		if(allcatid!=''){
			var allcatarr = JSON.parse(allcatid);
			for(var i=0;i<allcatarr.length;i++){
				var nodechecked = zTree.getNodeByParam("id", allcatarr[i]);
				zTree.checkNode(nodechecked,true);
			}
			
		}
		zTree.expandAll(true);
		
		$readonly

	}
EOF;

			return $str;
		}

		public function _onCheck()
		{
			$str
				= <<< EOF
	function {$this->onCheckFunc} () {

		var zTree=$.fn.zTree.getZTreeObj("{$this->eleId}");
        nodes=zTree.getCheckedNodes(true),
        v="";
        for(var i=0;i<nodes.length;i++){
			if(nodes[i].isParent!=true){
				v+=nodes[i].id + ",";
			}
        }
		v=v.substring(0,v.length-1);
		//alert(v);
		$('#{$this->selValEleId}').val(v);
	}
	
EOF;

			return $str;

		}

		public function _callback()
		{
			$str
				= <<< EOF
			callback: {
				onAsyncSuccess: {$this->onSuccFunc}  ,
				onCheck: {$this->onCheckFunc}
			}
EOF;

			return $str;

		}

		public function _readOnly()
		{
			$str
				= <<<EOF
			function $this->readOnlyfunc (){
				 var treeObj = $.fn.zTree.getZTreeObj('$this->eleId');
			     var node = treeObj.getNodes(); 
				 var nodes = treeObj.transformToArray(node); 

				for(i=0;i<nodes.length;i++){
    				var nodechecked = treeObj.getNodeByParam("id", nodes[i].id);
    				treeObj.setChkDisabled(nodechecked,true);
    			}
			}
EOF;
			return $str;

		}

		public function _render()
		{
			//初始化设置
			$temp = "var setting = {";
			$temp .= $this->_checkStyleSetting();
			$temp .= $this->_getUrlSetting();
			$temp .= $this->_getDataSetting();
			$temp .= $this->_callback();
			$temp .= '};';

			//实例化常规函数
			$temp .= "\r\n" . $this->onSyncSuccess();
			$temp .= "\r\n" . $this->_onCheck();
			if ($this->readOnly)
				$temp .= "\r\n" . $this->_readOnly();

			$align = $this->align ? $this->align : '';

			//渲染
			if (!$this->data_ajax) {
				$readonly = $this->readOnly ? " $this->readOnlyfunc();" : '';
				$data     = json_encode($this->data);
				$str
						  = <<< EOF
				\$(document).ready(function(e){
					\$.fn.zTree.init($("#$this->eleId"), setting,$data);
					$readonly
					$align
				});
EOF;

			}else {
				$str
					= <<< EOF
				\$(document).ready(function(e){
					\$.fn.zTree.init($("#$this->eleId"), setting);
					$align
				});
EOF;
			}

			return $temp . "\r\n" . $str;

		}

	}

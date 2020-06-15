<?php

	namespace mallka\ztree;

	use http\Exception\InvalidArgumentException;
	use Yii;
	use yii\base\InvalidConfigException;
	use yii\db\ActiveRecord;


	class Ztree extends \yii\base\Widget
	{

		/**
		 * 实例化对象id
		 *
		 * @var string
		 */
		public $eleId='treeDemo';

		/**
		 * @var array  选中的值，[1,2,3]
		 */
		public $selVal=[];

		/**
		 * 当用户在ztree上操作时，ztree实时得到选中后的id集合，以逗号分隔组合起来的字符串，
		 * 将该字符串存储在该id值里。
		 * 如果允许选中，则该值建议设置
		 * @var string
		 */
		public $selValEleId='';

		/**
		 * @var  ztree成功的callback函数
		 */
		public $onSuccFunc;

		/**
		 * @var ztree的callback函数
		 */
		public $onCheckFunc;



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

		public function init()
		{

			if($this->onCheckFunc==null)
				$this->onCheckFunc='onCheck'.time();

			if($this->onSuccFunc==null)
				$this->onSuccFunc = 'onSucc'.time();




			//可选值时，设置指定值为选中值
			if($this->checkbox ||$this->radio)
			{

			}


		}

		public function run()
		{
			$view = $this->getView();
			Asset::register($view);


			$view = $this->getView();
			JqgridAsset::register($view);

			return $this->_render();

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
			if ($this->data_ajax == false) {
				return $str;
			}else {
				return '';
			}

		}

		/**
		 * 3.获取ajax异步获取的配置
		 *
		 * @return string
		 */
		public function _getUrlSetting()
		{
			$str= <<<EOF
async: {
	enable: true,
		type: "get",
		url: "{$this->data}",
		autoParam: ["id", "name"]
	},
EOF;
			return $this->data_ajax ? $str : '';

		}



		public function onSyncSuccess()
		{
			if($this->radio){
				if(!empty($this->selVal))
					$selValStr = '['.$this->selVal[0].']';
			}
			elseif ($this->checkbox)
			{
				if(!empty($this->selVal))
					$selValStr = '['.implode(',',$this->selVal).']';

			}


			$str= <<< EOF
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

	}
EOF;

		}


		public function _onCheck()
		{
			$str =<<< EOF
	function {$this->onCheckFunc} () {

		var zTree=$.fn.zTree.getZTreeObj("treeDemo"),
        nodes=zTree.getCheckedNodes(true),
        v="";
        for(var i=0;i<nodes.length;i++){
			if(nodes[i].isParent!=true){
				v+=nodes[i].id + ",";
			}
        }
		v=v.substring(0,v.length-1);
		//alert(v);
		$('{$this->selValEleId}').val(v);
	}
	
EOF;

		}

		public function _callback()
		{
			$str= <<< EOF
			callback: {
				onAsyncSuccess: {$this->onSuccFunc}  ,
				onCheck: {$this->onCheckFunc}
			}
EOF;

			return $str;

		}


		public function _render()
		{


			//初始化设置
			$temp="var setting = {";
			$temp.=$this->_checkStyleSetting();
			$temp.=$this->_getDataSetting();
			$temp.=$this->_getDataSetting();
			$temp.=$this->_callback();
			$temp.='};';

			//实例化常规函数
			$temp.="\r\n".$this->onSyncSuccess();
			$temp.="\r\n".$this->_onCheck();

			//渲染
			if($this->data_ajax)
			{
				$data = json_encode($this->data);
				$str =<<< EOF
				\$(document).ready(function(e){
					\$.fn.zTree.init($("$this->eleId"), setting,$data);
				}
EOF;

			}
			else{
				$str =<<< EOF
				\$(document).ready(function(e){
					\$.fn.zTree.init($("$this->eleId"), setting);
				}
EOF;
			}


			return $temp."\r\n".$str;

		}

	}

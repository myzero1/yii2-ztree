Ztree widget for  yii2
=============
因封装过于简陋，仅满足mallka内部使用。

已支持
------------
- 字段搜索
- 字段排序
- 表头分组
- 多选
- 单元格渲染
- 行编辑（待测试）
- jqrid标准支持
- 分组(待优化成更容易的测试)
- 汇总
- 滚动表格
  

安装
------------

Composer 安装

```
php composer.phar require --prefer-dist yiirise/yii2-risegrid "dev-master"
```

或在composer.json加入以下配置

```
"mallka/yii2-risegrid": "dev-master"
```

然后 composer update 一下


用法
-----
所有mk_开头的参数都是插件封装所需的开关之类的，非mk_开头的，直接映射为jqgrid的配置项。
有一些jqgrid变量、event是本widget尚未囊括在内的，可以在mk_js里设置。




```php
<?= \mallka\risegrid\RiseGrid::widget([
      #整个jqGrid的ID，该值将得到：
      #选中行的变量：list2_lastsel;
      #jqgrid的实例：{$this->render_id}_grid;
      #jqgrid的实例：jqgrid ，如果一个页面有多个jqgrid的话，该值永远指向最后一个
      'render_id' => 'list2',

      #分类和按钮组的渲染id
      'pager'     => 'list2_page',

      #数据加载的网址
      'url'       => \yii\helpers\Url::to([ 'user-backend/ajax_search' ]),

      #单元格提交的网址，该值不能与editurl共存
      #'cellurl'=>\yii\helpers\Url::to(['user-backend/ajax_searchaaaa']),

      #行编辑提交的网址，该值不能和cellurl共存
      #'editurl'=>\yii\helpers\Url::to(['user-backend/ajax_searchaaaa']),

      'mk_language'      => 'zh-CN',

      //使用某ActiveRecord 实例													
      #'mk_model'=>$userBackend,

      //或者自定义
      'mk_model'         => [
          //full
          [
              'label'         => '测试以下',

              //we suggest name = index,
              'name'          => 'username',
              'index'         => 'username',

              //default width is 40
              'width'         => 40,
              'align'         => 'left',

              #是否主键
              'key'           => false,

              #隐藏设置
              'hidden'        => false,
              'hidedlg'       => false,

              #显示的渲染设置
              'formatter'     => "",
              'formatoptions' => '',

              #编辑配置
              'editable'      => false,
              'edittype'      => '',
              'editoptions'   => '',
          ],

          //base
          [
              'label'     => '基本',
              'name'      => 'id',
              'index'     => 'id',
              'formatter' => 'yesno',
          ],

          [
              'label'         => '时间',
              'name'          => 'created_at',
              'index'         => 'created_at',
              'formatter'     => 'date',
              'formatoptions' => "{srcformat:'u',newformat:'Y-m-d H:i:s'}",
          ],

      ],

      #额外指定key,建议填写
      'mk_key'           => 'id',

      #隐藏渲染的字段
      'mk_hidden_column' => [ 'username' ],

      #不要渲染的字段
      'mk_remove_column' => [ 'id' ],

      'mk_top_search'   => false,

      //表格底部的html代码，注意单双引号的转意
      'mk_append'       => "Hel\'lo",

      //jqgrid内置模块的重写
      'mk_extra'        => new \yii\web\JsExpression("
            //标题，提示，关闭按钮，model参数, 替换掉自带的提示组件。
            info_dialog: function (caption, content, c_b, modalopt) {
                layer.alert(content, {
                    icon: 2,
                    skin: 'layer-ext-moon'
                })
            },

      "),

      //扩展按钮
      'mk_button_extra' => [
          new \yii\web\JsExpression('{
                    caption: "Adddd",
                    buttonicon: "ui-icon-add",
                    onClickButton: function () {
                        alert("Adding Row");
                    },
                    position: "last"
              }'),
          new \yii\web\JsExpression('{
                    caption: "delete",
                    buttonicon: "ui-icon-add",
                    onClickButton: function () {
                        alert("Adding Row");
                    },
                    position: "last"
              }'),
          new \yii\web\JsExpression('{
                    caption: "Hiii",
                    buttonicon: "ui-icon-add",
                    onClickButton: function () {
                        alert("Adding Row");
                    },
                    position: "last"
              }'),
      ],

      //jqgrid实例化后外面的代码
      'mk_js_outside'   => new \yii\web\JsExpression("
        function ooo(){alert(1)};

      "),
      //jqgird实例化内部代码，可以是jqgird的配置，也可以是各类事件
      'mk_js'           => new \yii\web\JsExpression("
            cellEdit:true,

      "),
      //css内容会被压缩后输出
      'mk_css'          => ".table{background-color:red}",

      //字段的渲染响应函数
      'mk_formatter'    => new \yii\web\JsExpression("
        /**可以存放很多函数**/
        function yesno(value) {return value+1;}
      "),

  ]); ?>
```



单元格表单类型（内置）
----

文本框：

```editable : true```

文本框(带排序)：

```editable : true,sorttype : "date"```

文本框（大小限制）：

```editable : true,editoptions : {size : "20",maxlength : "30"}```

多选框(YES是选中的值，No是没选的，可以自定义)：

```editable : true,edittype : "checkbox",editoptions : {value : "Yes:No"}```


下拉框

```editable : true,edittype : "select",editoptions : {value : "FE:FedEx;IN:InTime;TN:TNT;AR:ARAMEX"}```

文本框

```editable : true,edittype : "textarea",editoptions : {rows : "2",cols : "10"}```

自定义组件渲染：
```
editable : true,edittype : "customr",
editoptions :
{
    custom_element:my_input,    //自定义输入控件，一个js函数，返回一个html
     custom_value:mycheck       //自定义获取值的方法，经常用来验证数据是否正确
 }
 
要额外注入所需函数
function my_input(value, options) {
 return $("<input type='text' size='10' style='background-color: red;' value='"+value+"'/>");
}
function my_value(value) {
 return "My value: "+value.val();
}
```

TBC,

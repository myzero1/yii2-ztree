Ztree widget for  yii2
=============
**因封装过于简陋，仅满足mallka内部使用。
Please do not use for your product beacuse we still not test enough!** 


支持：
=============
- 单选tree
- 多选tree
- 无输入框tree
- 3种数据源



引入包：
```
composer require mallka/yii2-ztree "dev-master"

```

#### 基本使用(Ajax)
```
    <?=\mallka\ztree\Ztree::widget([
       'eleId'=>'treeDemo222',
       'data'=>\yii\helpers\Url::to(['product-category/ajaxnode'],true),
       'data_ajax'=>true,             //ajax加载数据模式 
       'radio'=>true,                 //chebox或者raido
       'selValEleId'=>"alltypeid",    //选中的值存储在这个id的value里
       'selVal'=>[956,957],           //初始值
       'readOnly'=>true,              //checkbox/radio是否可选
])?>
```

对应的action方法应该返回id、pid、name的结构，如
```php
function actionAjaxnode()
 {
     $db = new Query;
     $cat = $db->from('product_category')->orderBy('id asc')->all();
     for($v=1;$v<count($cat);$v++){
         $pid = $cat[$v]['parent_id'];
         $cat[$v]['pId'] = $pid;
     }
     return json_encode($cat);
 }
```





#### 基本使用2(静态数组)
```php
	<?=\mallka\ztree\Ztree::widget([
       'eleId'=>'treeDemo222',
       'selValEleId'=>"alltypeid",
       'data'=>[           //没有dat_ajax,改为以下格式数组
           ['id'=>1, 'pId'=>0, 'name'=>'目录1'],
           ['id'=>2, 'pId'=>1, 'name'=>'目录2'],
           ['id'=>3, 'pId'=>1, 'name'=>'目录3'],
           ['id'=>4, 'pId'=>1, 'name'=>'目录4'],
           ['id'=>5, 'pId'=>2, 'name'=>'目录5'],
           ['id'=>6, 'pId'=>3, 'name'=>'目录6']
       ],
       'radio'=>true,
       'selVal'=>[956,957],
        'readOnly'=>true,
	])?>
```

#### 基本使用3（model）
```php
	<?=\mallka\ztree\Ztree::widget([
       'eleId'=>'treeDemo222',
       'selValEleId'=>"alltypeid",
       'data'=>Model::find()->where(xxxx)->all(),
       'radio'=>true,
       'selVal'=>[956,957],
       'readOnly'=>true,
   ])?>
```


#### 基本使用4（ActiveForm） 
注意，字段的值必需是tree的一个id，或多个id，多个id用英文逗号分隔。
```php
<?= $form->field($model, 'kind_value')->widget(
    \mallka\ztree\Ztree::class,
    [
    
               'data'=>\yii\helpers\Url::to(['product-category/ajaxnode'],true),
               'data_ajax'=>true,
               'checkbox'=>true,
               //'readOnly'=>true,
    ]
) ?>



```

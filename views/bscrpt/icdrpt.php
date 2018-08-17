<?php
$this->breadcrumbs=array(
	'ICD'=>array('reports/index'),
	'Reports',
);

?>
<br />
<?php echo $this->renderPartial('_fmake', array('model'=>$model)); ?>
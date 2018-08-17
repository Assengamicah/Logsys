<?php
use yii\helpers\Html;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
    <b> Reports Management</b>  
    <div class="dtree">

		<p><a href="javascript: d.openAll();">open all</a> | <a href="javascript: d.closeAll();">close all</a></p>
		<script type="text/javascript">

			d = new dTree('d');

			d.add(0,-1,'Reports Central');
			<?php echo $trees; ?>
			document.write(d);
		</script>
</div>
  

<?php
use yii\helpers\Html;
use kartik\date\DatePicker;
?>
<br />
<center>
	<?php echo $form = Html::beginForm(); ?>   
	<table class='table table-bordered table-gray'><thead><tr><td colspan="4" align="center"><b><?php echo $title; ?></b></td></tr></thead>
		<tr>
			<td>
				<b>From:</b>
			</td>
			<td>
				<?php echo DatePicker::widget([
					'name' => 'from', 
					'options' => ['placeholder' => 'From'],
					'pluginOptions' => [
						'format' => 'dd/mm/yyyy',
						'todayHighlight' => true
					]
				]); ?>

			</td>
			<td align="right">
				<b>To:</b>
			</td>
			<td>
				<?php echo DatePicker::widget([
					'name' => 'to', 
					'options' => ['placeholder' => 'To'],
					'pluginOptions' => [
						'format' => 'dd/mm/yyyy',
						'todayHighlight' => true
					]
				]); ?>
			</td>
		</tr>
		<tr>
			<td colspan='4'><center>
				<input type="submit" name="btnReport" class="btn primary" value="Get Report"  />&nbsp;
				<input type="hidden" name="txtNPar" value="2"  />
				<input type="hidden" name="txtFunctname" value="<?php echo $fname ?>"  />
			</center></td>
		</tr>
	</table>
	<?php echo Html::endForm(); ?>
</center>
<br />
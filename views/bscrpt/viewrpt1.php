<?php
use yii\helpers\Html;
use kartik\date\DatePicker;
?>

<?php echo $form = Html::beginForm(); ?>   
<table class='table table-bordered table-gray'>
	<thead>
		<tr>
			<td colspan="6" ><b><center><?php echo $title; ?></center></b></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><b>Client:</b></td>
			<td><?php echo Html::dropDownList('txtPar1','txtPar1',$this->context->getClients()); ?></td>
			<td><b>From:</b></td>
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
			<td><b>To:</b></td>
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
			<td colspan = '6'><center><input type="submit" name="btnReport" class="btn primary" value="Get Report"  />
			<input type="hidden" name="txtNPar" value="3"  />
			<input type="hidden" name="txtOpt" value="pdf"  />
			<input type="hidden" name="txtFunctname" value="<?php echo $fname ?>"  />
			</center></td>
		</tr>
	</tbody>
</table>
<?php echo Html::endForm(); ?>


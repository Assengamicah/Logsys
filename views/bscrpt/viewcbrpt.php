<?php
use yii\helpers\Html;
//use kartik\widgets\DatePicker;
use dosamigos\datepicker\DatePicker;
?>
<br />
<center>
<?php echo $form = Html::beginForm(); ?>   
<table class='table table-bordered table-gray'><thead><tr><td colspan="4" align="center"><b><?php echo $title; ?></b></td></tr></thead>
<tr>
<td><b>From:</b></td><td>
<?php echo DatePicker::widget(['name'=>'from','template' => '{addon}{input}', 'clientOptions' => ['autoclose' => true,'format' => 'dd/mm/yyyy']]); ?>

</td>
<td align="right"><b>To:</b></td><td>
<?php echo DatePicker::widget(['name'=>'to','template' => '{addon}{input}', 'clientOptions' => ['autoclose' => true,'format' => 'dd/mm/yyyy']]); ?>
</td></tr>
<tr><td><b>Supplier:</b></td>
<td><?php echo Html::dropDownList('txtOpt','txtOpt',$this->context->getSuppliers()); ?>&nbsp;
<input type="hidden" name="txtNPar" value="2"  />
<input type="hidden" name="txtFunctname" value="<?php echo $fname ?>"  /></td><td colspan='2'>
<input type="submit" name="btnReport" class="btn primary" value="Get Report"  />&nbsp;</td></tr>
</table>
<?php echo Html::endForm(); ?>
</center>
<br />
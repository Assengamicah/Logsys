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
<tr><td><b>Report Option:</b></td>
<td><?php echo Html::dropDownList('txtPar1','txtPar1',['All Barcodes'=>'All Barcodes','Used Barcodes'=>'Used Barcodes','Unused Barcodes'=>'Unused Barcodes']); ?>&nbsp;
<input type="hidden" name="txtNPar" value="1"  />
<input type="hidden" name="txtOpt" value="pdf"  />
<input type="hidden" name="txtFunctname" value="<?php echo $fname ?>"  /></td><td colspan='2'>
<input type="submit" name="btnReport" class="btn primary" value="Get Report"  />&nbsp;</td></tr>
</table>
<?php echo Html::endForm(); ?>
</center>
<br />
<?php
$this->breadcrumbs=array(
	'ICD'=>array('reports/index'),
	'Reports',
);

?>
<br />
<center>
<?php echo CHtml::beginForm(); ?>   
<table width="56%"><tr><td colspan="2" align="center"><b><?php echo $title; ?></b></td></tr>
<tr bgcolor="ABE1FE">
<td align="center"><b>Vessel Name:</b>&nbsp;
<?php echo CHtml::dropDownList('txtPar1','txtPar1',$this->getShip(),array('prompt'=>'Select')); ?>
&nbsp;&nbsp;&nbsp;<b>View Option:</b>&nbsp;
<?php echo CHtml::dropDownList('txtOpt','txtOpt',array('Web'=>'Web','Excel'=>'Excel')); ?>&nbsp;
<input type="hidden" name="txtNPar" value="1"  />
<input type="hidden" name="txtFunctname" value="<?php echo $fname ?>"  />
<input type="submit" name="btnReport" value="Get Report"  />&nbsp;</td></tr>
</table>
<?php echo CHtml::endForm(); ?>
</center>
<br />
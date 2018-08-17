<?php
$this->breadcrumbs=array(
	'ICD'=>array('reports/index'),
	'Reports',
);

?>
<br />
<center>
<?php echo CHtml::beginForm(); ?>   
<table width="60%"><tr><td colspan="2" align="center"><b><?php echo $title; ?></b></td></tr>
<tr bgcolor="ABE1FE">
<td align="center"><b>For The Date:</b>&nbsp;
<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
    				'name'=>'txtPar1',
                    'options'=>array(
        			'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
					'dateFormat'=>'dd/mm/yy',
   					 ),
    				'htmlOptions'=>array(
        			'style'=>'width:132px;vertical-align:top'
   				 ),
				)); 
				?>
&nbsp;&nbsp;&nbsp;<b>View Option:</b>&nbsp;
<?php echo CHtml::dropDownList('txtOpt','txtOpt',array('Web'=>'Web','Excel'=>'Excel')); ?>&nbsp;
<input type="hidden" name="txtNPar" value="1"  />
<input type="hidden" name="txtFunctname" value="<?php echo $fname ?>"  />
<input type="submit" name="btnReport" value="Get Report"  />&nbsp;</td></tr>
</table>
<?php echo CHtml::endForm(); ?>
</center>
<br />
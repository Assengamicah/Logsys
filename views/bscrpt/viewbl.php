<?php
$this->breadcrumbs=array(
	'ICD'=>array('reports/index'),
	'Reports',
);

?>
<br />
<center>
<?php echo CHtml::beginForm(); ?>   
<table width="64%"><tr><td colspan="2" align="center"><b><?php echo $title; ?></b></td></tr>
<tr bgcolor="ABE1FE">
<td align="center"><b>BL #:</b>&nbsp;
<?php echo CHtml::textField('from'); ?>
</td>
<td align="right"><b>Seal Cut Date:</b>&nbsp;
<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
    				'name'=>'to',
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
<input type="hidden" name="txtNPar" value="2"  />
<input type="hidden" name="txtFunctname" value="<?php echo $fname ?>"  />
<input type="submit" name="btnReport" value="Get Document"  />&nbsp;</td></tr>
</table>
<?php echo CHtml::endForm(); ?>
</center>
<br />
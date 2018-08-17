<center>
<?php 
use barcode\barcode\BarcodeGenerator as BarcodeGenerator; 
    $i = 1;
	foreach($items as $item)
	{ ?>
      <div id="showBarcode_<?php echo $i; ?>"></div>
     <?php  
		$optionsArray = [
			'elementId'=> 'showBarcode_'.$i, 
				'value'=> $item[0], 
				'type'=>'code128',
				
                  			   
			];
    echo BarcodeGenerator::widget($optionsArray).'<font size=-2>'.$item[1].' ('.$item[2].''.$item[3].')'."</font><br />&nbsp<span style='page-break-after :always;'></span>";
	
	$i++;
	}
?>

</center>

 $(document).ready(function(){
	
    $('#squeeze4-bno').change(function() {	
    var a=$('#squeeze4-bno :selected').val(); // "city1city2choose iofoo"
        if(a == 8)
		{
		 $("#squeeze4-cbm").prop('disabled', true);
		 $("#squeeze4-cbm").val("");
		 $("#squeeze4-quantity").prop('disabled', false);
		}
		else
		{
			$("#squeeze4-quantity").prop('disabled', true);
		    $("#squeeze4-quantity").val("");
			$("#squeeze4-cbm").prop('disabled', false);
		}
     
});

 
 });
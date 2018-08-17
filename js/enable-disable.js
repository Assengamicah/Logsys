 $(document).ready(function(){
	
    $('#orderitems-gid').change(function() {	
    var a=$(':selected').val(); // "city1city2choose iofoo"
    var exp = a.split(":");
    if (exp[1] == 'CBM') {

        $("#orderitems-cbm").prop('disabled', false);
    }
     else {
        $("#orderitems-cbm").prop('disabled', true);
		$("#orderitems-cbm").val("");
     }
});
 
 });




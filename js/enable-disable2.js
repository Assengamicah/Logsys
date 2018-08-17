 $(document).ready(function(){
	var a=$(':selected').val(); // "city1city2choose iofoo"
    var exp = a.split(":");
    if (exp[1] == 'CBM') {

        $("#squeeze2-cbm").prop('disabled', false);
    }
     else {
        $("#squeeze2-cbm").prop('disabled', true);
		$("#squeeze2-cbm").val("");
     }
    
	$('#squeeze2-iid').change(function() {	
    var a=$(':selected').val(); // "city1city2choose iofoo"
    var exp = a.split(":");
    if (exp[1] == 'CBM') {

        $("#squeeze2-cbm").prop('disabled', false);
    }
     else {
        $("#squeeze2-cbm").prop('disabled', true);
		$("#squeeze2-cbm").val("");
     }
});
 
 });




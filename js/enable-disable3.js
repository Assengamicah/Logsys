 $(document).ready(function(){
	var a=$(':selected').val(); // "city1city2choose iofoo"
    var exp = a.split(":");
    if (exp[1] == 'CBM') {

        $("#squeeze3-cbm").prop('disabled', false);
    }
     else {
        $("#squeeze3-cbm").prop('disabled', true);
		$("#squeeze3-cbm").val("");
     }
    
	$('#squeeze3-iid').change(function() {	
    var a=$(':selected').val(); // "city1city2choose iofoo"
    var exp = a.split(":");
    if (exp[1] == 'CBM') {

        $("#squeeze3-cbm").prop('disabled', false);
    }
     else {
        $("#squeeze3-cbm").prop('disabled', true);
		$("#squeeze3-cbm").val("");
     }
});
 
 });




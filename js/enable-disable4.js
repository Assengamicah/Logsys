 $(document).ready(function(){
	
    $('#jobdocuments-hascharges').change(function() {	
    var c=$(':selected').val(); // "city1city2choose iofoo"
   // var exp = a.split(":");
    if (c == 'YES') {

        $("#orderitems-cbm").prop('disabled', false);
    }
     else {
        $("#orderitems-cbm").prop('disabled', true);
		$("#orderitems-cbm").val("");
     }
});
 
 });




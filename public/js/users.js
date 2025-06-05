$(".js-select-retailer").on("click",function(e){
	// $.ajax({
	// 	url: "/ajax/select-retailer.php",
	// 	type: "POST",
	// 	success: function(result)
	// 	{
	// 		var retaileri = JSON.parse(result);
	// 		if(retaileri.length > 0) {
	// 			console.log(retaileri);
	// 			var html = "<select class='form-control selectpicker' id='select-retailer' name='select-retailer'>";
	// 			$.each(retaileri,function(key,value){
	// 				html = html + "<option value='"+value['id']+"'>"+value['nume']+"</option>";
	// 			})
	// 			html = html + "<select>";
	// 			html = html + "<button class='btn btn-primary mt-3 js-add-retailer-connection'>Adauga</button>";

	// 		//	$("#retailer-modal .modal-body").html(html);
	// 		}
			

			
	// 	}
	// });		
	$("#retailer-modal").modal('toggle');
})
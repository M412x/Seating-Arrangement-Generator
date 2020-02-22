$(document).ready(function(){
	var i=1;
	$('#add').click(function(){
		i++;
		$('#dynamic_field').append('<tr id="row'+i+'"><td><input type="text" name="grades[]" placeholder="Grade Level" class="form-control name_list" /></td><td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">Delete</button></td></tr>');
	});

	$(document).on('click', '.btn_remove', function(){
		var button_id = $(this).attr("id");
		$('#row'+button_id+'').remove();
	});
});

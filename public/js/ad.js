var counter = $('#annonce_images .row').length;

$('#add_image').click(function(){


counter=counter + 1;

var tmpl = $('#annonce_images').data('prototype');


tmpl = tmpl.replace(/__name__/g,counter)
//console.log(tmpl);

$('#annonce_images').append(tmpl);

deleteblock();

});


	function deleteblock(){

	$('.del_image').click(function(){

	var bloc = $(this).data('bloc');


	$('#'+bloc).remove();




	})


};


deleteblock();
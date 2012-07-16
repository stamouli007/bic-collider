<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php //die(phpinfo()); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript" ></script>
<script>
$(document).ready(function(e) {
    
	var i = 1;
	
	$('form').submit(function(e) {
		$('.returned-value, .field-used span').html('');
		var searchTerm = '',
			otherField = '';
		
		if(i == 1) {
			var searchTerm = $('.search-one').val();
			var otherField = $('.search-two').val();
			$('.field-used span').html(i);
			i++;
		}else {
			var searchTerm = $('.search-two').val();
			var otherField = $('.search-one').val();
			$('.field-used span').html(i);
			i--;
		}
		
		$.ajax({
          url: 'search.php?s='+searchTerm+'&otherField='+otherField+'&details=true',
          data: '',
          dataType: 'html',
          type: 'post',
          success: function (j) {
			   $('.returned-value').html(j);
          }
		});
		
		return false;
	});
	
});

</script>

<style>
.returned-value {
	width:600px;
	margin-top:10px;
}
</style>

<title>Seach Form</title>
</head>

<body>

<form id="form-one">
<input type="text" class="search-one" />
<input type="text" class="search-two" />
<input type="submit" value="Search" />

</form>
<br />

<div class="field-used">
<p>Used Field: <span></span></p>
</div>

<div class="returned-value" id="one">
</div>


</body>
</html>
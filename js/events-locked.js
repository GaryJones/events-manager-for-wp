document.addEventListener('DOMContentLoaded', function(){ 
	var publish_button = document.getElementById( 'publish' );
	publish_button.value = em4wp_publish_text;
	publish_button.disabled = true;

	var trash_button = document.getElementById( 'delete-action' );
	trash_button.innerHTML = '<a href=\"'+em4wp_source_post+'\" class=\"submitdelete deletion\">'+em4wp_locked_text+'</span>';
	//.style.display = 'none';
}, false);
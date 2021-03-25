document.addEventListener('DOMContentLoaded', function() { //wait till page is ready
    const min_letters = 2; //minimal characters to start searching
    var autocomplete_field = document.getElementById('sz-search-field');//our search field id here
    var awesomplete_field = new Awesomplete(autocomplete_field); //creating a new instanse of autocomplete widget	
    autocomplete_field.addEventListener('keyup', function() { //start doing smth after user presses a key
        var user_input = this.value;  // Just let's use another variable for developer clarity
        if ( user_input.length >= min_letters ) { // Check if there are enough letters in the field	
			fetch(   szsearch.search_url + user_input, { //we are using Fetch API to make request/process the response
				method: 'GET',
				headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': szsearch.nonce, //Check the nonce we generated before
					  }
			})
			.then( response => {
				  if (response.status !== 200) { //If smth is not right
					console.log('Problem! Status Code: ' +
					  response.status);
					return;
				  }	
				response.json().then( posts => { //We found something!	  
				var results = []; //Let's extact information that we need: Post ID, Permalink and Title
					for(var key in posts) {
						var valueToPush = {}; 
						valueToPush["label"] = posts[key].label; //passing post title	
						valueToPush["value"] = { id: posts[key].ID, permalink: posts[key].permalink}; //we are passing post ID and permalink here
						results.push(valueToPush);
					}
				awesomplete_field.list = results;  // Update the Awesomplete list
				awesomplete_field.evaluate();  // And tell Awesomplete that we've done so
				})
				.catch(function(err) {
					console.log('No results');
				})
				})
				.catch(function(err) {
					console.log('Error: ', err);
				});
        }
    });
	awesomplete_field.replace = function(suggestion) {
		this.input.value = suggestion.value.permalink; //Replace search input with permalink in the field 
		document.getElementById("sz_result_permalink").value = suggestion.value.permalink; //Assign permalink value to the hidden field
		document.getElementById("sz_result_id").value = suggestion.value.id; //Assign post ID value to the hidden field
    };
});
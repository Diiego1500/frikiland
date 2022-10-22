$('#filter_post_button').click(function (){
    selectElement = document.querySelector('#selected_post_type');
    output = selectElement.value;
    window.location.href = Routing.generate('filter_posts', {filter:output})
});
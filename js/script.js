$(document).ready(function() {
    let page = 1;
    $('#loadMore').click(function() {
        page++;
        $.ajax({
            url: 'fetch_products.php',
            type: 'POST',
            data: { page: page },
            success: function(data) {
                if ($.trim(data) !== '') {
                    $('.product-grid').append(data);
                } else {
                    $('#loadMore').text('No more products').prop('disabled', true);
                }
            }
        });
    });
});
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


$(function(){
    $(".category-checkbox").change(function(){
      // Collect all checked category values
      var selected = $(".category-checkbox:checked").map(function(){ 
        return $(this).val(); 
      }).get();
      // If no filter is checked, show all products
      if (selected.length === 0) {
        $(".product-card").show();
        return;
      }
      // Otherwise, show only cards whose data-category is in the selected list
      $(".product-card").each(function(){
        var cat = $(this).data("category");
        if (selected.includes(cat)) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    });
  });


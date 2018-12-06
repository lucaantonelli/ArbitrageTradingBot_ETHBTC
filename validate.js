$(document).ready(function() {
    $('.res').each(function() {
      var val = $(this).text();
      if (val <= 0) {
        $(this).css('background-color', 'rgb(239,83,80)');
      } else {
        $(this).prepend("+");
        $(this).css('background-color', 'rgb(156,204,101)');
      }
    });
    $('.per').each(function() {
      var val = $(this).text();
      if (val <= 0) {
        $(this).css('background-color', 'rgb(239,83,80)');
      } else {
        $(this).css('background-color', 'rgb(156,204,101)');
      }
      $(this).append("%");
    });
});



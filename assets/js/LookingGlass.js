/**
 * LookingGlass jQuery file
 */
$(document).ready(function() {
  // onclick, set user IP to input value
  $('#userip').click(function () {
    $('#host').val($('#userip').text());
  });
  // form submit
  $('#networktest').submit(function() {
    // define vars
    var host = $('input[name=host]').val();
    var csrf = $('input[name=csrf]').val();
    var data = 'cmd=' + $('select[name=cmd]').val() + '&host=' + host + '&csrf=' + csrf;
    // quick validation
    if (host == '') {
        $('#hosterror').addClass('has-error');
    }
    // submit form
    else {
      // disable submit button + blank response
      $('#submit').attr('disabled', 'true').text(Loading);
      $('#response').html();

      // call async request
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'ajax.php?' + data, true);
      xhr.send(null);
      var timer;
      timer = window.setInterval(function() {
        // on completion
        if (xhr.readyState == XMLHttpRequest.DONE) {
            window.clearTimeout(timer);
            $('#submit').removeAttr('disabled').text(RunTest);
        }

        // show/hide results
        if (xhr.responseText == '') {
          $('#response').hide();
        } else {
          $('#hosterror').removeClass('has-error');
          $('#response, #results').show();
        }

        // output response
        if (xhr.responseText == 'Unauthorized request') {
          $('#results').hide();
          $('#hosterror').addClass('has-error');
        } else {
          $('#response').html(xhr.responseText.replace(/<br \/> +/g, '<br />'));
        }
      }, 500);
    }

    // cancel default behavior
    return false;
  });
});


/**
 * Light/Dark Mode
 */
$("input[id='lightSwitch']").on("change", function() {
  if ($("html").attr("data-bs-theme") == 'light') {
    $("html").attr("data-bs-theme", "dark");
    HtmlTheme = "dark";
  } 
  else if ($("html").attr("data-bs-theme") == "dark") {
    $("html").attr("data-bs-theme", "light");
    HtmlTheme = "light";
  }

  $.ajax({
    type : 'GET',
    url : 'ajax.php',
    data: {
        theme : HtmlTheme,
    },
    success : function(data) {
      // alert(HtmlTheme);
    },
    error : function(XMLHttpRequest, textStatus, errorThrown) {
      alert ("Error Occured!");}
    });

});
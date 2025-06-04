function getUrlParameter(name) {
      name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
      var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
      var results = regex.exec(location.search);
      return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Check for error or success messages in URL parameters
    window.onload = function() {
      const error = getUrlParameter('error');
      const success = getUrlParameter('success');
      
      if (error) {
        const errorDiv = document.getElementById('error-messages');
        errorDiv.innerHTML = decodeURIComponent(error);
        errorDiv.style.display = 'block';
      }
      
      if (success) {
        const successDiv = document.getElementById('success-message');
        successDiv.innerHTML = decodeURIComponent(success);
        successDiv.style.display = 'block';
      }
    };
   document.addEventListener('DOMContentLoaded', function () {
      // Get modal elements
      const createClassBtn = document.getElementById('create-class-btn');
      const modalOverlay = document.getElementById('create-class-modal');
      const modalCloseBtn = document.getElementById('modal-close-btn');
      const modalCancelBtn = document.getElementById('modal-cancel-btn');
      const createClassForm = document.getElementById('create-class-form');
      const classGrid = document.getElementById('class-grid');

      // Function to show the modal
      function showModal() {
        modalOverlay.style.display = 'flex';
      }

      // Function to hide the modal
      function hideModal() {
        modalOverlay.style.display = 'none';
        createClassForm.reset(); // Reset form fields when modal is hidden
      }

      // Event listeners to open and close the modal
      createClassBtn.addEventListener('click', showModal);
      modalCloseBtn.addEventListener('click', hideModal);
      modalCancelBtn.addEventListener('click', hideModal);
      modalOverlay.addEventListener('click', function (event) {
        if (event.target === modalOverlay) {
          hideModal();
        }
      });

      // Handle the form submission
      createClassForm.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        // Get form data
        const formData = new FormData(createClassForm);
        const className = formData.get('class_name');
        const classCode = formData.get('class_code');

        // --- BACKEND INTEGRATION POINT ---
        // Use the Fetch API to send the data to your backend script
        // Replace 'path/to/create_class.php' with your actual backend endpoint.
        fetch('path/to/create_class.php', {
          method: 'POST',
          body: formData
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // If the backend confirms success:
              // 1. Hide the modal
              hideModal();

              // 2. Remove the "No classes" message if it exists
              const noClassesMsg = document.getElementById('no-classes-message');
              if (noClassesMsg) {
                noClassesMsg.remove();
              }

              // 3. Create and append the new class card
              const newCard = document.createElement('article');
              newCard.className = 'course-card';
              // Use the data returned from the server (e.g., new class ID)
              newCard.setAttribute('data-course-id', data.class_id);
              newCard.innerHTML = `
              <div class="course-card-header color-2"> <!-- You can randomize the color -->
                  <h3 class="course-title"><a href="class_details.php?id=${data.class_id}">${escapeHTML(className)}</a></h3>
                  <div class="course-code">Code: ${escapeHTML(classCode)}</div>
              </div>
              <div class="course-card-content">
                  <p class="course-instructor"><?php echo htmlspecialchars($_SESSION["fullname"]); ?></p>
              </div>
            `;
              classGrid.appendChild(newCard);

            } else {
              // Handle server-side errors (e.g., display an error message)
              alert('Error: ' + data.message);
            }
          })
          .catch(error => {
            // Handle network errors
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
          });
      });

      // Helper function to prevent XSS attacks
      function escapeHTML(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
      }
    });
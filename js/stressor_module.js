// Update the plot selector according to checkboxes checked
function updateLink() {
    var checkboxes = document.querySelectorAll('.stressor-list-group input[type=checkbox]:checked');
    var values = Array.from(checkboxes).map(cb => cb.value);

    var baseUrl = "/stressor-plot/";
    var baseUrl_download = "/download_excel_full/";
    var queryString = values.length > 0 ? values.join(",") : "";

    var link = document.getElementById("stressor-plot-button");
    link.href = baseUrl + queryString;

    var d_btn = document.getElementById("stressor-download-buttons");
    var d_btn_full = document.getElementById("stressor-download-full");

    // Enable or disable the link
    if (values.length === 0) {
        link.classList.add('bdisabled');
        d_btn.classList.add('bdisabled');
        link.removeAttribute('href'); // Remove href attribute when disabled
        d_btn_full.removeAttribute('href');
    } else {
        link.classList.remove('bdisabled');
        d_btn.classList.remove('bdisabled');
        link.setAttribute('href', baseUrl + queryString); // Re-add href attribute when enabled
        d_btn_full.setAttribute('href', baseUrl_download + queryString);
    }
}

// Add event listeners to checkboxes
document.querySelectorAll('.stressor-list-group input[type=checkbox]').forEach(checkbox => {
    checkbox.addEventListener('change', updateLink);
});

// Initialize the link on page load
updateLink();

// Select all checkboxes or deselect from list view page
function toggleCheckboxes(selectAll) {
    var checkboxes = document.querySelectorAll('.stressor-list-group input[type=checkbox]');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = selectAll;
    });
    updateLink();
}



/* When the user clicks on the button,
toggle between hiding and showing the dropdown content */
function dropdownButton() {
    document.getElementById("myDropdown").classList.toggle("show");
}
  
  // Close the dropdown menu if the user clicks outside of it
  window.onclick = function(event) {
    if (!event.target.matches('.dropbtn')) {
      var dropdowns = document.getElementsByClassName("dropdown-content");
      var i;
      for (i = 0; i < dropdowns.length; i++) {
        var openDropdown = dropdowns[i];
        if (openDropdown.classList.contains('show')) {
          openDropdown.classList.remove('show');
        }
      }
    }
  }